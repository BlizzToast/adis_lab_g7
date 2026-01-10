<?php
declare(strict_types=1);

namespace App\Models;

/**
 * Post Model - Handles all message/post-related database operations with Redis caching
 */
class Post extends Model
{
    protected string $table = "messages";
    protected string $primaryKey = "id";
    private ?\Redis $redis = null;
    private const REDIS_TIMELINE_KEY = 'posts:timeline';
    private const REDIS_POST_PREFIX = 'post:';
    private const POST_TTL = 3600; // 3600 seconds -> 1 hour TTL for individual posts
    private const PAGE_SIZE = 10;

    /**
     * Constructor - Initialize Redis connection and cache
     */
    public function __construct($database = null)
    {
        parent::__construct($database);
        $this->initializeRedis();
    }

    /**
     * Initialize Redis connection
     */
    private function initializeRedis(): void
    {
        try {
            $this->redis = new \Redis();
            $redisHost = $_ENV['REDIS_HOST'] ?? $_SERVER['REDIS_HOST'] ?? 'localhost';
            $redisPort = (int)($_ENV['REDIS_PORT'] ?? $_SERVER['REDIS_PORT'] ?? 6379);
            
            // Use persistent connection for better performance
            $this->redis->pconnect($redisHost, $redisPort, 2.0, 'roary_redis');
            
            // Timeline initialization is now done lazily in getPostsByPage()
        } catch (\Exception $e) {
            error_log("Redis connection failed: " . $e->getMessage());
            $this->redis = null;
        }
    }

    /**
     * Populate the Redis timeline cache from SQLite
     * Only caches the most recent N posts (configurable) to reduce memory footprint
     */
    private function populateTimelineCache(int $limit = 500): void
    {
        if (!$this->redis) return;

        try {
            // Only cache the most recent posts (IDs + timestamps) - rest will fall back to DB
            $sql = "SELECT id, created_at FROM {$this->table} ORDER BY created_at DESC LIMIT :limit";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
            $stmt->execute();
            $posts = $stmt->fetchAll();

            if (!empty($posts)) {
                // Use pipeline for better performance
                $this->redis->multi(\Redis::PIPELINE);
                foreach ($posts as $post) {
                    $this->redis->zAdd(
                        self::REDIS_TIMELINE_KEY,
                        (float)$post['created_at'],
                        (string)$post['id']
                    );
                }
                $this->redis->exec();
                error_log("Populated Redis timeline cache with " . count($posts) . " recent posts (limit: $limit)");
            }
        } catch (\Exception $e) {
            error_log("Failed to populate timeline cache: " . $e->getMessage());
        }
    }

    /**
     * Create the messages table if it doesn't exist
     */
    public function createTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS messages (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL,
            content TEXT NOT NULL,
            created_at INTEGER NOT NULL,
            FOREIGN KEY (username) REFERENCES users(username) ON DELETE CASCADE
        )";

        $this->pdo->exec($sql);

        // Create index for performance (read-heavy operations)
        $this->pdo->exec(
            "CREATE INDEX IF NOT EXISTS idx_messages_created_at ON messages(created_at DESC)",
        );
    }

    /**
     * Create a new post
     */
    public function createPost(string $username, string $content): ?int
    {
        // Validate content
        $errors = $this->validatePostData($content);
        if (!empty($errors)) {
            return null;
        }

        $timestamp = time();

        
        try {
            // Insert post into SQLite
            $postId = $this->insert([
                "username" => $username,
                "content" => $content,
                "created_at" => $timestamp,
            ]);

            if ($postId && $this->redis) {
                // Add to Redis timeline
                $this->redis->zAdd(
                    self::REDIS_TIMELINE_KEY,
                    (float)$timestamp,
                    (string)$postId
                );

                // Get user avatar and cache the post directly (avoid extra DB call)
                $userSql = "SELECT avatar FROM users WHERE username = :username";
                $user = $this->db->fetchOne($userSql, ["username" => $username]);
                
                $post = [
                    'id' => $postId,
                    'username' => $username,
                    'content' => $content,
                    'created_at' => $timestamp,
                    'avatar' => $user['avatar'] ?? null
                ];
                
                $this->redis->setex(
                    self::REDIS_POST_PREFIX . $postId,
                    self::POST_TTL,
                    json_encode($post)
                );
            }

            return $postId;
        } catch (\PDOException $e) {
            error_log("Failed to create post: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get post with user information
     */
    public function getPostWithUser(int $id): ?array
    {
        $sql = "SELECT m.*, u.avatar
                FROM {$this->table} m
                JOIN users u ON m.username = u.username
                WHERE m.id = :id";

        return $this->db->fetchOne($sql, ["id" => $id]);
    }

    /**
     * Get all posts with user information
     */
    public function getAllPosts(int $limit = 100, int $offset = 0): array
    {
        $sql = "SELECT m.id, m.username, m.content, m.created_at, u.avatar
                FROM {$this->table} m
                JOIN users u ON m.username = u.username
                ORDER BY m.created_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(":limit", $limit, \PDO::PARAM_INT);
        $stmt->bindValue(":offset", $offset, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Get posts by username
     */
    public function getPostsByUser(string $username, int $limit = 50): array
    {
        $sql = "SELECT m.id, m.username, m.content, m.created_at, u.avatar
                FROM {$this->table} m
                JOIN users u ON m.username = u.username
                WHERE m.username = :username
                ORDER BY m.created_at DESC
                LIMIT :limit";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(":username", $username, \PDO::PARAM_STR);
        $stmt->bindValue(":limit", $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Get posts by page using Redis cache
     * (next n posts after a given post ID)
     */
    public function getPostsByPage(int $page = 1, int $pageSize = self::PAGE_SIZE): array
    {
        $page = max(1, $page);
        $start = ($page - 1) * $pageSize;
        $end = $start + $pageSize - 1;

        // Try to get post IDs from Redis timeline
        $postIds = [];
        if ($this->redis) {
            try {
                // Get post IDs from sorted set (highest score/timestamp first)
                // Check if timeline exists, populate if empty on first page
                if ($page === 1 && !$this->redis->exists(self::REDIS_TIMELINE_KEY)) {
                    $this->populateTimelineCache();
                }
                
                $postIds = $this->redis->zRevRange(
                    self::REDIS_TIMELINE_KEY,
                    $start,
                    $end
                );
            } catch (\Exception $e) {
                error_log("Redis ZREVRANGE failed: " . $e->getMessage());
            }
        }

        // If Redis failed or returned nothing, fall back to SQLite
        if (empty($postIds)) {
            $posts = $this->getAllPosts($pageSize, $start);
            
            // Cache the fetched posts for future requests (even if beyond timeline limit)
            if (!empty($posts) && $this->redis) {
                try {
                    $this->redis->multi(\Redis::PIPELINE);
                    foreach ($posts as $post) {
                        $this->redis->setex(
                            self::REDIS_POST_PREFIX . $post['id'],
                            self::POST_TTL,
                            json_encode($post)
                        );
                    }
                    $this->redis->exec();
                } catch (\Exception $e) {
                    error_log("Failed to cache fallback posts: " . $e->getMessage());
                }
            }
            
            return $posts;
        }

        // Use MGET to fetch all posts in a single Redis call
        $cacheKeys = array_map(fn($id) => self::REDIS_POST_PREFIX . $id, $postIds);
        $cachedPosts = [];
        $missingIds = [];

        if ($this->redis) {
            try {
                $cachedPosts = $this->redis->mGet($cacheKeys);
            } catch (\Exception $e) {
                error_log("Redis MGET failed: " . $e->getMessage());
                // Fall back to SQLite
                return $this->getAllPosts($pageSize, $start);
            }
        }

        // Process cached results and identify missing posts
        $posts = [];
        foreach ($postIds as $index => $postId) {
            $cachedData = $cachedPosts[$index] ?? false;
            if ($cachedData !== false) {
                $post = json_decode($cachedData, true);
                if ($post) {
                    $posts[] = $post;
                    continue;
                }
            }
            $missingIds[] = (int)$postId;
        }

        // Fetch missing posts from SQLite and update cache
        if (!empty($missingIds)) {
            $missingPosts = $this->getPostsByIds($missingIds);
            
            if (!empty($missingPosts) && $this->redis) {
                // Cache missing posts
                try {
                    $this->redis->multi(\Redis::PIPELINE);
                    foreach ($missingPosts as $post) {
                        $this->redis->setex(
                            self::REDIS_POST_PREFIX . $post['id'],
                            self::POST_TTL,
                            json_encode($post)
                        );
                    }
                    $this->redis->exec();
                } catch (\Exception $e) {
                    error_log("Failed to cache posts: " . $e->getMessage());
                }
            }

            // Merge and sort all posts
            $posts = array_merge($posts, $missingPosts);
            usort($posts, fn($a, $b) => $b['created_at'] <=> $a['created_at']);
        }

        return $posts;
    }

    /**
     * Get posts by IDs (for cache misses)
     */
    private function getPostsByIds(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "SELECT m.id, m.username, m.content, m.created_at, u.avatar
                FROM {$this->table} m
                JOIN users u ON m.username = u.username
                WHERE m.id IN ($placeholders)
                ORDER BY m.created_at DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($ids);

        return $stmt->fetchAll();
    }

    /**
     * Search posts by content
     */
    public function searchPosts(string $query, int $limit = 50): array
    {
        $sql = "SELECT m.id, m.username, m.content, m.created_at, u.avatar
                FROM {$this->table} m
                JOIN users u ON m.username = u.username
                WHERE m.content LIKE :query
                ORDER BY m.created_at DESC
                LIMIT :limit";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(":query", "%" . $query . "%", \PDO::PARAM_STR);
        $stmt->bindValue(":limit", $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Get posts with pagination info
     */
    public function getPaginatedPosts(int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;
        $posts = $this->getAllPosts($perPage, $offset);
        $totalPosts = $this->count();
        $totalPages = ceil($totalPosts / $perPage);

        return [
            "posts" => $posts,
            "pagination" => [
                "current_page" => $page,
                "per_page" => $perPage,
                "total_posts" => $totalPosts,
                "total_pages" => $totalPages,
                "has_prev" => $page > 1,
                "has_next" => $page < $totalPages,
            ],
        ];
    }

    /**
     * Validate post data
     */
    public function validatePostData(string $content): array
    {
        $errors = [];

        if (empty(trim($content))) {
            $errors["content"] = "Post content cannot be empty";
        } elseif (strlen($content) > 10000) {
            $errors["content"] = "Post content cannot exceed 10,000 characters";
        }

        return $errors;
    }

    /**
     * Format post for display
     */
    public function formatPost(array $post): array
    {
        // Convert timestamp to readable format if needed
        if (isset($post["created_at"])) {
            $post["created_at_formatted"] = date(
                "Y-m-d H:i:s",
                $post["created_at"],
            );
            $post["created_at_relative"] = $this->getRelativeTime(
                $post["created_at"],
            );
        }

        return $post;
    }

    /**
     * Get relative time string (e.g., "2 hours ago")
     */
    private function getRelativeTime(int $timestamp): string
    {
        $diff = time() - $timestamp;

        if ($diff < 60) {
            return "just now";
        } elseif ($diff < 3600) {
            $minutes = floor($diff / 60);
            return $minutes . " minute" . ($minutes > 1 ? "s" : "") . " ago";
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return $hours . " hour" . ($hours > 1 ? "s" : "") . " ago";
        } elseif ($diff < 604800) {
            $days = floor($diff / 86400);
            return $days . " day" . ($days > 1 ? "s" : "") . " ago";
        } else {
            return date("Y-m-d", $timestamp);
        }
    }
}

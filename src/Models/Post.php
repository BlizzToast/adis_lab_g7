<?php
declare(strict_types=1);

namespace App\Models;

/**
 * Post Model - Handles all message/post-related database operations
 */
class Post extends Model
{
    protected string $table = "messages";
    protected string $primaryKey = "id";

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

        // Insert post
        try {
            return $this->insert([
                "username" => $username,
                "content" => $content,
                "created_at" => time(),
            ]);
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

<?php
declare(strict_types=1);

/**
 * Posts API Endpoint
 * Handles CRUD operations for posts/messages
 */

// Set headers for JSON API
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Define base path
define('BASE_PATH', __DIR__ . '/..');

// Load configuration
$config = require BASE_PATH . '/config/config.php';

// Configure session to match main app
session_name($config['session']['name']);
ini_set('session.cookie_httponly', $config['session']['httponly'] ? '1' : '0');
ini_set('session.cookie_path', $config['session']['path']);
ini_set('session.gc_maxlifetime', (string)$config['session']['lifetime']);

// Start session
session_start();

// Autoload classes
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = BASE_PATH . '/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

use App\Models\Post;
use App\Models\Database;

// Helper function to check authentication
function isAuthenticated(): bool {
    return isset($_SESSION['username']) && !empty($_SESSION['username']);
}

// Helper function to send JSON response
function sendResponse(int $statusCode, $data, string $message = ''): void {
    http_response_code($statusCode);
    echo json_encode([
        'success' => $statusCode >= 200 && $statusCode < 300,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

// Helper function to get JSON input
function getJsonInput(): ?array {
    $input = file_get_contents('php://input');
    return $input ? json_decode($input, true) : null;
}

// Check authentication for non-GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET' && !isAuthenticated()) {
    sendResponse(401, null, 'Authentication required');
}

// Initialize Database and Post model
$database = Database::getInstance($config['database']);
$postModel = new Post($database);

// Route based on HTTP method
try {
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            // Get posts with pagination support
            $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
            $pageSize = 10;
            
            // Get paginated posts
            $posts = $postModel->getPostsByPage($page, $pageSize);
            
            sendResponse(200, $posts, 'Posts retrieved successfully');
            break;

        case 'POST':
            // Create new post
            $input = getJsonInput();
            
            if (!$input || empty($input['content'])) {
                sendResponse(400, null, 'Content is required');
            }
            
            $username = $_SESSION['username'] ?? 'anonymous';
            $content = trim($input['content']);
            
            if (strlen($content) < 1 || strlen($content) > 280) {
                sendResponse(400, null, 'Content must be between 1 and 280 characters');
            }
            
            $postId = $postModel->createPost($username, $content);
            
            if ($postId) {
                $post = $postModel->find($postId);
                sendResponse(201, $post, 'Post created successfully');
            } else {
                sendResponse(500, null, 'Failed to create post');
            }
            break;

        case 'PUT':
            // Update post
            $input = getJsonInput();
            
            if (!$input || empty($input['id']) || empty($input['content'])) {
                sendResponse(400, null, 'ID and content are required');
            }
            
            $postId = (int)$input['id'];
            $post = $postModel->find($postId);
            
            if (!$post) {
                sendResponse(404, null, 'Post not found');
            }
            
            // Check if user owns the post
            if ($post['username'] !== $_SESSION['username']) {
                sendResponse(403, null, 'You can only edit your own posts');
            }
            
            $content = trim($input['content']);
            
            if (strlen($content) < 1 || strlen($content) > 280) {
                sendResponse(400, null, 'Content must be between 1 and 280 characters');
            }
            
            $success = $postModel->update($postId, ['content' => $content]);
            
            if ($success) {
                $updatedPost = $postModel->find($postId);
                sendResponse(200, $updatedPost, 'Post updated successfully');
            } else {
                sendResponse(500, null, 'Failed to update post');
            }
            break;

        case 'DELETE':
            // Delete post
            $input = getJsonInput();
            
            if (!$input || empty($input['id'])) {
                sendResponse(400, null, 'ID is required');
            }
            
            $postId = (int)$input['id'];
            $post = $postModel->find($postId);
            
            if (!$post) {
                sendResponse(404, null, 'Post not found');
            }
            
            // Check if user owns the post or is admin
            $isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'];
            if ($post['username'] !== $_SESSION['username'] && !$isAdmin) {
                sendResponse(403, null, 'You can only delete your own posts');
            }
            
            $success = $postModel->delete($postId);
            
            if ($success) {
                sendResponse(200, ['id' => $postId], 'Post deleted successfully');
            } else {
                sendResponse(500, null, 'Failed to delete post');
            }
            break;

        default:
            sendResponse(405, null, 'Method not allowed');
    }
} catch (Exception $e) {
    error_log('API Error: ' . $e->getMessage());
    sendResponse(500, null, 'Internal server error');
}

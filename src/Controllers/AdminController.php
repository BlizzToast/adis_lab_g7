<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;
use App\Models\Post;
use App\Models\Database;

/**
 * AdminController - Admin panel for user management
 */
class AdminController extends Controller
{
    private User $userModel;
    private Post $postModel;

    public function __construct($request, $response, $session, $config)
    {
        parent::__construct($request, $response, $session, $config);
        $this->userModel = new User();
        $this->postModel = new Post();

        // Ensure admin user exists with environment password
        $this->userModel->createAdminUser();
    }

    /**
     * Check if user is admin, redirect if not
     */
    private function requireAdmin(): void
    {
        if (!$this->session->has("username") || !$this->session->isAdmin()) {
            $this->session->flash("error", "Admin access required");
            $this->redirect("/login");
        }
    }

    /**
     * Show admin dashboard with stats
     */
    public function index(): void
    {
        $this->requireAdmin();

        // Get statistics
        $userCount = $this->userModel->count();
        $postCount = $this->postModel->count();
        $dbPath = $this->config["database"]["path"];
        $dbSize = file_exists($dbPath)
            ? $this->formatBytes(filesize($dbPath))
            : "N/A";

        // Get all users for management
        $users = $this->userModel->all(["id" => "DESC"], 100);

        $data = [
            "title" => "Admin Panel - Roary",
            "userCount" => $userCount,
            "postCount" => $postCount,
            "dbSize" => $dbSize,
            "dbPath" => $dbPath,
            "users" => $users,
            "isLoggedIn" => $this->session->has("username"),
            "username" => $this->session->get("username"),
            "csrf_token" => $this->session->getCsrfToken(),
            "messages" => $this->getFlashMessages(),
        ];

        $this->render("admin/index", $data);
    }

    /**
     * Impersonate a user
     */
    public function impersonate(): void
    {
        $this->requireAdmin();

        if ($this->request->getMethod() !== "POST") {
            $this->redirect("/admin");
            return;
        }

        // Validate CSRF token
        if (!$this->validateCsrf()) {
            $this->session->flash("error", "Invalid request");
            $this->redirect("/admin");
            return;
        }

        $username = $this->request->post("username", "");

        if (empty($username)) {
            $this->session->flash("error", "Invalid username");
            $this->redirect("/admin");
            return;
        }

        // Check if user exists
        if (!$this->userModel->exists("username", $username)) {
            $this->session->flash("error", "User not found");
            $this->redirect("/admin");
            return;
        }

        // Don't impersonate yourself
        if ($username === $this->session->get("username")) {
            $this->session->flash("error", "Cannot impersonate yourself");
            $this->redirect("/admin");
            return;
        }

        // Store original admin username for logging
        $adminUsername = $this->session->get("username");

        // Check if target user is admin
        $isTargetAdmin = $this->userModel->isAdmin($username);

        // Login as the target user
        $this->session->login($username, $isTargetAdmin);
        $this->session->flash("success", "Now impersonating $username");

        error_log("[ADMIN] Admin $adminUsername impersonating user: $username");

        $this->redirect("/");
    }

    /**
     * Delete a user
     */
    public function deleteUser(): void
    {
        $this->requireAdmin();

        if ($this->request->getMethod() !== "POST") {
            $this->redirect("/admin");
            return;
        }

        // Validate CSRF token
        if (!$this->validateCsrf()) {
            $this->session->flash("error", "Invalid request");
            $this->redirect("/admin");
            return;
        }

        $userId = (int) $this->request->post("user_id", 0);

        if ($userId <= 0) {
            $this->session->flash("error", "Invalid user ID");
            $this->redirect("/admin");
            return;
        }

        // Don't allow deleting the admin user
        $user = $this->userModel->find($userId);
        if ($user && $user["username"] === "admin") {
            $this->session->flash("error", "Cannot delete admin user");
            $this->redirect("/admin");
            return;
        }

        // Delete user and their posts
        if ($this->userModel->deleteUser($userId)) {
            $this->session->flash("success", "User deleted successfully");
            error_log(
                "[ADMIN] User deleted: ID $userId by " .
                    $this->session->get("username"),
            );
        } else {
            $this->session->flash("error", "Failed to delete user");
        }

        $this->redirect("/admin");
    }

    /**
     * Reset database with test data
     */
    public function reset(): void
    {
        $this->requireAdmin();

        if ($this->request->getMethod() !== "POST") {
            $this->redirect("/admin");
            return;
        }

        // Validate CSRF token
        if (!$this->validateCsrf()) {
            $this->session->flash("error", "Invalid request");
            $this->redirect("/admin");
            return;
        }

        try {
            // Reset database
            $this->resetDatabase();
            $this->session->flash(
                "success",
                "Database reset successfully with test data!",
            );

            error_log(
                "[ADMIN] Database reset by " . $this->session->get("username"),
            );
        } catch (\Exception $e) {
            $this->session->flash(
                "error",
                "Failed to reset database: " . $e->getMessage(),
            );
            error_log("Database reset failed: " . $e->getMessage());
        }

        $this->redirect("/admin");
    }

    /**
     * Reset database with test data
     */
    private function resetDatabase(): void
    {
        $config = $this->config["database"];

        // Delete existing database
        if (file_exists($config["path"])) {
            unlink($config["path"]);
        }

        // Reset the database singleton
        Database::reset();

        // Create fresh database connection
        $db = Database::getInstance($config);
        $this->userModel = new User($db);
        $this->postModel = new Post($db);

        // Create tables
        $this->userModel->createTable();
        $this->postModel->createTable();

        // Create admin user with environment password
        $this->userModel->createAdminUser();

        // Test users (non-admin)
        $testUsers = [
            [
                "username" => "alice",
                "password" => "TestPass1234",
                "avatar" => "👩",
            ],
            [
                "username" => "bob",
                "password" => "TestPass1234",
                "avatar" => "👨",
            ],
            [
                "username" => "charlie",
                "password" => "TestPass1234",
                "avatar" => "🧑",
            ],
            [
                "username" => "david",
                "password" => "TestPass1234",
                "avatar" => "👦",
            ],
            [
                "username" => "eve",
                "password" => "TestPass1234",
                "avatar" => "👧",
            ],
            [
                "username" => "PenguinPete",
                "password" => "TestPass1234",
                "avatar" => "🐧",
            ],
            [
                "username" => "CoolCat67",
                "password" => "TestPass1234",
                "avatar" => "🐱",
            ],
        ];

        // Create test users (not admins)
        foreach ($testUsers as $userData) {
            $this->userModel->createUser(
                $userData["username"],
                $userData["password"],
                $userData["avatar"],
                false, // not admin
            );
        }

        // Sample posts
        $samplePosts = [
            ["user" => "alice", "content" => "Welcome to Roary! 🎉"],
            [
                "user" => "bob",
                "content" =>
                    "Just discovered penguins can't actually roar... 🐧",
            ],
            [
                "user" => "charlie",
                "content" =>
                    "Why do programmers prefer dark mode? Because light attracts bugs! 🐛",
            ],
            [
                "user" => "CoolCat67",
                "content" => "There's no place like 127.0.0.1 🏠",
            ],
            ["user" => "eve", "content" => "404: Motivation not found 😴"],
            [
                "user" => "admin",
                "content" => "Welcome everyone! This is the admin speaking 👑",
            ],
            [
                "user" => "PenguinPete",
                "content" => "Linux users: I use Arch BTW 🐧",
            ],
            [
                "user" => "alice",
                "content" =>
                    "Git commit -m 'Fixed the bug'... Git commit -m 'Actually fixed it this time'",
            ],
            [
                "user" => "bob",
                "content" =>
                    "Coffee: Turning programmers into code since 1991 ☕",
            ],
            [
                "user" => "david",
                "content" =>
                    "My code doesn't have bugs, it has random features ✨",
            ],
        ];

        // Create posts with random timestamps
        $currentTime = time();
        $dayAgo = $currentTime - 86400;

        foreach ($samplePosts as $index => $post) {
            $randomTime = rand($dayAgo, $currentTime - $index * 3600);
            $sql =
                "INSERT INTO messages (username, content, created_at) VALUES (:username, :content, :created_at)";
            $stmt = $this->postModel->pdo->prepare($sql);
            $stmt->execute([
                "username" => $post["user"],
                "content" => $post["content"],
                "created_at" => $randomTime,
            ]);
        }
    }

    /**
     * Format bytes to human readable
     */
    private function formatBytes(int $bytes): string
    {
        $units = ["B", "KB", "MB", "GB"];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . " " . $units[$i];
    }
}

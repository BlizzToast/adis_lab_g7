<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Post;
use App\Models\User;

/**
 * PostController - Handles post/message creation and display
 */
class PostController extends Controller
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
    }

    /**
     * Display all posts (feed)
     */
    public function index(): void
    {
        // Get page number for pagination
        $page = (int) $this->request->get("page", 1);
        $page = max(1, $page);

        // Get posts with pagination
        $result = $this->postModel->getPaginatedPosts($page, 20);
        $posts = $result["posts"];
        $pagination = $result["pagination"];

        // Format posts for display
        $posts = array_map([$this->postModel, "formatPost"], $posts);

        $data = [
            "title" => "Roary - Feed",
            "posts" => $posts,
            "pagination" => $pagination,
            "csrf_token" => $this->session->getCsrfToken(),
            "isLoggedIn" => $this->session->has("username"),
            "username" => $this->session->get("username"),
            "messages" => $this->getFlashMessages(),
        ];

        $this->render("post/feed", $data);
    }

    /**
     * Create a new post
     */
    public function create(): void
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
     * Show a single post
     */
    public function show(array $params): void
    {
        $postId = (int) ($params["id"] ?? 0);

        if ($postId <= 0) {
            $this->error(404, "Post not found");
            return;
        }

        $post = $this->postModel->getPostWithUser($postId);

        if (!$post) {
            $this->error(404, "Post not found");
            return;
        }

        // Format post for display
        $post = $this->postModel->formatPost($post);

        $data = [
            "title" => "Post by " . $post["username"],
            "post" => $post,
            "csrf_token" => $this->session->getCsrfToken(),
            "isLoggedIn" => $this->session->has("username"),
            "username" => $this->session->get("username"),
            "canDelete" =>
                $this->session->get("username") === $post["username"],
            "messages" => $this->getFlashMessages(),
        ];

        $this->render("post/show", $data);
    }

    /**
     * Delete a post
     */
    public function delete(array $params): void
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

        $postId = (int) ($params["id"] ?? 0);

        if ($postId <= 0) {
            $this->error(404, "Post not found");
            return;
        }

        // Get the post
        $post = $this->postModel->getPostWithUser($postId);

        if (!$post) {
            $this->error(404, "Post not found");
            return;
        }

        // Check if user owns the post
        if ($post["username"] !== $this->session->get("username")) {
            $this->error(403, "You are not authorized to delete this post");
            return;
        }

        // Delete the post
        if ($this->postModel->delete($postId)) {
            $this->session->flash("success", "Post deleted successfully");
            error_log(
                "Post deleted by {$this->session->get(
                    "username",
                )} (ID: $postId)",
            );
        } else {
            $this->session->flash("error", "Failed to delete post");
        }

        $this->redirect("/");
    }

    /**
     * Show posts by a specific user
     */
    public function userPosts(array $params): void
    {
        $username = $params["username"] ?? "";

        if (empty($username)) {
            $this->error(404, "User not found");
            return;
        }

        // Check if user exists
        $user = $this->userModel->findBy("username", $username);

        if (!$user) {
            $this->error(404, "User not found");
            return;
        }

        // Get user's posts
        $posts = $this->postModel->getPostsByUser($username, 50);

        // Format posts for display
        $posts = array_map([$this->postModel, "formatPost"], $posts);

        $data = [
            "title" => "Posts by $username",
            "posts" => $posts,
            "profileUser" => $user,
            "postCount" => count($posts),
            "csrf_token" => $this->session->getCsrfToken(),
            "isLoggedIn" => $this->session->has("username"),
            "username" => $this->session->get("username"),
            "messages" => $this->getFlashMessages(),
        ];

        $this->render("post/user-posts", $data);
    }

    /**
     * Search posts
     */
    public function search(): void
    {
        $query = $this->request->get("q", "");

        if (empty(trim($query))) {
            $this->redirect("/");
            return;
        }

        // Search posts
        $posts = $this->postModel->searchPosts($query, 50);

        // Format posts for display
        $posts = array_map([$this->postModel, "formatPost"], $posts);

        $data = [
            "title" => "Search Results",
            "query" => $query,
            "posts" => $posts,
            "resultCount" => count($posts),
            "csrf_token" => $this->session->getCsrfToken(),
            "isLoggedIn" => $this->session->has("username"),
            "username" => $this->session->get("username"),
            "messages" => $this->getFlashMessages(),
        ];

        $this->render("post/search", $data);
    }

    /**
     * Get recent posts (AJAX endpoint)
     */
    public function recent(): void
    {
        if (!$this->request->isAjax()) {
            $this->error(403, "Invalid request");
            return;
        }

        $hours = (int) $this->request->get("hours", 24);
        $since = time() - $hours * 3600;

        $sql = "SELECT m.id, m.username, m.content, m.created_at, u.avatar
                FROM messages m
                JOIN users u ON m.username = u.username
                WHERE m.created_at > :since
                ORDER BY m.created_at DESC
                LIMIT 20";

        $stmt = $this->postModel->pdo->prepare($sql);
        $stmt->execute(["since" => $since]);
        $posts = $stmt->fetchAll();

        // Format posts for display
        $posts = array_map([$this->postModel, "formatPost"], $posts);

        $this->json([
            "success" => true,
            "posts" => $posts,
            "count" => count($posts),
        ]);
    }

    /**
     * Handle post submission from home page
     */
    public function handlePost(): void
    {
        $this->create();
    }
}

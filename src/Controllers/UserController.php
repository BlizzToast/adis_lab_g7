<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;

/**
 * UserController - Handles user authentication and registration
 */
class UserController extends Controller
{
    private User $userModel;
    private \App\Models\Post $postModel;

    public function __construct($request, $response, $session, $config)
    {
        parent::__construct($request, $response, $session, $config);
        $this->userModel = new User();
        $this->postModel = new \App\Models\Post();
        $this->userModel->createTable();
    }

    /**
     * Show login form
     */
    public function showLogin(): void
    {
        $this->requireGuest();

        $data = [
            "title" => "Login - Roary",
            "errors" => $this->session->flash("errors") ?? [],
            "old" => $this->session->flash("old") ?? [],
            "csrf_token" => $this->session->getCsrfToken(),
            "messages" => $this->getFlashMessages(),
        ];

        $this->render("user/login", $data);
    }

    /**
     * Handle login request
     */
    public function login(): void
    {
        $this->requireGuest();

        if ($this->request->getMethod() !== "POST") {
            $this->redirect("/login");
            return;
        }

        // Validate CSRF token
        if (!$this->validateCsrf()) {
            $this->session->flash(
                "error",
                "Invalid request. Please try again.",
            );
            $this->redirect("/login");
            return;
        }

        // Get input data
        $username = $this->request->post("username", "");
        $password = $this->request->post("password", "");

        // Store input for potential redirect back
        $this->session->flash("old", ["username" => $username]);

        // Validate input
        $errors = [];
        if (empty($username)) {
            $errors["username"] = "Username is required";
        }
        if (empty($password)) {
            $errors["password"] = "Password is required";
        }

        if (!empty($errors)) {
            $this->session->flash("errors", $errors);
            $this->redirect("/login");
            return;
        }

        // Authenticate user
        $user = $this->userModel->authenticate($username, $password);

        if ($user) {
            // Login successful - check if admin
            $isAdmin = $this->userModel->isAdmin($username);
            $this->session->login($username, $isAdmin);
            $this->session->flash(
                "success",
                "Welcome back, " . $username . "!",
            );

            // Log the successful login
            error_log(
                "User logged in: $username" . ($isAdmin ? " (ADMIN)" : ""),
            );

            // Redirect to home or intended page
            $intended = $this->session->get("intended_url", "/");
            $this->session->remove("intended_url");
            $this->redirect($intended);
        } else {
            // Login failed
            error_log("Login failed for user: $username");
            $this->session->flash("errors", [
                "login" => "Invalid username or password",
            ]);
            $this->redirect("/login");
        }
    }

    /**
     * Show registration form
     */
    public function showRegister(): void
    {
        $this->requireGuest();

        $data = [
            "title" => "Register - Roary",
            "errors" => $this->session->flash("errors") ?? [],
            "old" => $this->session->flash("old") ?? [],
            "csrf_token" => $this->session->getCsrfToken(),
            "messages" => $this->getFlashMessages(),
        ];

        $this->render("user/register", $data);
    }

    /**
     * Handle registration request
     */
    public function register(): void
    {
        $this->requireGuest();

        if ($this->request->getMethod() !== "POST") {
            $this->redirect("/register");
            return;
        }

        // Validate CSRF token
        if (!$this->validateCsrf()) {
            $this->session->flash(
                "error",
                "Invalid request. Please try again.",
            );
            $this->redirect("/register");
            return;
        }

        // Get input data
        $username = $this->request->post("username", "");
        $password = $this->request->post("password", "");
        $confirmPassword = $this->request->post("confirmPassword", "");
        $avatar = $this->request->post("avatar", "🐧");

        // Store input for potential redirect back
        $this->session->flash("old", [
            "username" => $username,
            "avatar" => $avatar,
        ]);

        // Validate input
        $errors = $this->userModel->validateUserData($username, $password);

        // Check password confirmation
        if ($password !== $confirmPassword) {
            $errors["confirmPassword"] = "Passwords do not match";
        }

        // Check if username already exists (case-insensitive)
        if (
            empty($errors["username"]) &&
            $this->userModel->findByUsernameIgnoreCase($username) !== null
        ) {
            $errors["username"] = "Username already taken";
        }

        if (!empty($errors)) {
            $this->session->flash("errors", $errors);
            $this->redirect("/register");
            return;
        }

        // Create user
        $userId = $this->userModel->createUser($username, $password, $avatar);

        if ($userId) {
            // Registration successful - auto login (never as admin for new registrations)
            $this->session->login($username, false);
            $this->session->flash(
                "success",
                "Registration successful! Welcome to Roary!",
            );

            // Log the registration
            error_log("New user registered: $username (ID: $userId)");

            $this->redirect("/");
        } else {
            // Registration failed
            error_log("Registration failed for user: $username");
            $this->session->flash(
                "error",
                "Registration failed. Please try again.",
            );
            $this->redirect("/register");
        }
    }

    /**
     * Handle logout request
     */
    public function logout(): void
    {
        if ($this->session->has("username")) {
            $username = $this->session->get("username");
            $this->session->logout();
            error_log("User logged out: $username");
            $this->session->flash(
                "success",
                "You have been logged out successfully.",
            );
        }

        $this->redirect("/login");
    }

    /**
     * Show user profile
     */
    public function profile(array $params = []): void
    {
        $this->requireAuth();

        $username = $this->session->get("username");
        $user = $this->userModel->findBy("username", $username);

        if (!$user) {
            $this->error(404, "User not found");
            return;
        }

        // Get user statistics
        $stats = $this->userModel->getUserStats($user["id"]);
        $postCount = $this->postModel->count(["username" => $username]);

        $data = [
            "title" => "Profile - " . $user["username"],
            "user" => $user,
            "stats" => $stats,
            "postCount" => $postCount,
            "csrf_token" => $this->session->getCsrfToken(),
            "errors" => $this->session->flash("errors") ?? [],
            "messages" => $this->getFlashMessages(),
        ];

        $this->render("user/profile", $data);
    }

    /**
     * Update username
     */
    public function updateUsername(): void
    {
        $this->requireAuth();

        if ($this->request->getMethod() !== "POST") {
            $this->redirect("/profile");
            return;
        }

        if (!$this->validateCsrf()) {
            $this->session->flash("error", "Invalid request");
            $this->redirect("/profile");
            return;
        }

        $currentUsername = $this->session->get("username");

        // Protect admin account from username changes
        if ($currentUsername === "admin") {
            $this->session->flash(
                "error",
                "Admin account username cannot be changed",
            );
            $this->redirect("/profile");
            return;
        }

        $user = $this->userModel->findBy("username", $currentUsername);
        $newUsername = $this->request->post("username", "");

        if (!$user) {
            $this->error(404, "User not found");
            return;
        }

        // Validate new username
        $errors = [];
        if (empty($newUsername)) {
            $errors["username"] = "Username is required";
        } elseif (!preg_match('/^[a-zA-Z0-9]+$/', $newUsername)) {
            $errors["username"] =
                "Username must contain only letters and numbers";
        } elseif (strlen($newUsername) < 3) {
            $errors["username"] = "Username must be at least 3 characters long";
        } elseif (strlen($newUsername) > 50) {
            $errors["username"] = "Username must not exceed 50 characters";
        } elseif (
            $newUsername !== $currentUsername &&
            $this->userModel->findByUsernameIgnoreCase($newUsername) !== null
        ) {
            $errors["username"] = "Username already taken";
        }

        if (!empty($errors)) {
            $this->session->flash("errors", $errors);
            $this->redirect("/profile");
            return;
        }

        if ($this->userModel->updateUsername($user["id"], $newUsername)) {
            // Update session with new username
            $isAdmin = $this->session->isAdmin();
            $this->session->login($newUsername, $isAdmin);
            $this->session->flash("success", "Username updated successfully");
        } else {
            $this->session->flash("error", "Failed to update username");
        }

        $this->redirect("/profile");
    }

    /**
     * Update password
     */
    public function updatePassword(): void
    {
        $this->requireAuth();

        if ($this->request->getMethod() !== "POST") {
            $this->redirect("/profile");
            return;
        }

        if (!$this->validateCsrf()) {
            $this->session->flash("error", "Invalid request");
            $this->redirect("/profile");
            return;
        }

        $username = $this->session->get("username");

        // Protect admin account from password changes via web UI
        if ($username === "admin") {
            $this->session->flash(
                "error",
                "Admin account password cannot be changed via web interface",
            );
            $this->redirect("/profile");
            return;
        }

        $user = $this->userModel->findBy("username", $username);
        $currentPassword = $this->request->post("current_password", "");
        $newPassword = $this->request->post("new_password", "");
        $confirmPassword = $this->request->post("confirm_password", "");

        if (!$user) {
            $this->error(404, "User not found");
            return;
        }

        $errors = [];

        // Verify current password
        if (!$this->userModel->verifyCredentials($username, $currentPassword)) {
            $errors["current_password"] = "Current password is incorrect";
        }

        // Validate new password
        if (strlen($newPassword) < 12) {
            $errors["new_password"] =
                "Password must be at least 12 characters long";
        }

        if ($newPassword !== $confirmPassword) {
            $errors["confirm_password"] = "Passwords do not match";
        }

        if (!empty($errors)) {
            $this->session->flash("errors", $errors);
            $this->redirect("/profile");
            return;
        }

        if ($this->userModel->updatePassword($user["id"], $newPassword)) {
            $this->session->flash("success", "Password updated successfully");
        } else {
            $this->session->flash("error", "Failed to update password");
        }

        $this->redirect("/profile");
    }

    /**
     * Delete user account
     */
    public function deleteAccount(): void
    {
        $this->requireAuth();

        if ($this->request->getMethod() !== "POST") {
            $this->redirect("/profile");
            return;
        }

        if (!$this->validateCsrf()) {
            $this->session->flash("error", "Invalid request");
            $this->redirect("/profile");
            return;
        }

        $username = $this->session->get("username");

        // Protect admin account from deletion
        if ($username === "admin") {
            $this->session->flash("error", "Admin account cannot be deleted");
            $this->redirect("/profile");
            return;
        }

        $user = $this->userModel->findBy("username", $username);

        if (!$user) {
            $this->error(404, "User not found");
            return;
        }

        // Verify password before deletion
        $password = $this->request->post("password");
        if (!$this->userModel->verifyCredentials($username, $password)) {
            $this->session->flash("error", "Invalid password");
            $this->redirect("/profile");
            return;
        }

        // Delete account
        if ($this->userModel->deleteUser($user["id"])) {
            $this->session->logout();
            $this->session->flash("success", "Account deleted successfully");
            error_log("User account deleted: $username");
            $this->redirect("/");
        } else {
            $this->session->flash("error", "Failed to delete account");
            $this->redirect("/profile");
        }
    }
}

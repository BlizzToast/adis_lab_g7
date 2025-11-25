<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Session class for centralized session management
 */
class Session
{
    private static ?Session $instance = null;
    private array $config;
    private bool $started = false;

    private function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * Get the singleton instance
     */
    public static function getInstance(array $config = []): self
    {
        if (self::$instance === null) {
            self::$instance = new self($config);
        }
        return self::$instance;
    }

    /**
     * Start the session
     */
    public function start(): bool
    {
        if ($this->started) {
            return true;
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            $this->started = true;
            return true;
        }

        // Configure session settings
        if (!empty($this->config)) {
            if (isset($this->config["name"])) {
                session_name($this->config["name"]);
            }

            if (isset($this->config["lifetime"])) {
                ini_set(
                    "session.gc_maxlifetime",
                    (string) $this->config["lifetime"],
                );
                session_set_cookie_params($this->config["lifetime"]);
            }

            if (isset($this->config["path"])) {
                session_set_cookie_params(0, $this->config["path"]);
            }

            if (isset($this->config["secure"]) && $this->config["secure"]) {
                ini_set("session.cookie_secure", "1");
            }

            if (isset($this->config["httponly"]) && $this->config["httponly"]) {
                ini_set("session.cookie_httponly", "1");
            }
        }

        $this->started = session_start();
        return $this->started;
    }

    /**
     * Get a session value
     */
    public function get(string $key, $default = null)
    {
        $this->start();
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Set a session value
     */
    public function set(string $key, $value): void
    {
        $this->start();
        $_SESSION[$key] = $value;
    }

    /**
     * Check if a session key exists
     */
    public function has(string $key): bool
    {
        $this->start();
        return isset($_SESSION[$key]);
    }

    /**
     * Remove a session value
     */
    public function remove(string $key): void
    {
        $this->start();
        unset($_SESSION[$key]);
    }

    /**
     * Get all session data
     */
    public function all(): array
    {
        $this->start();
        return $_SESSION ?? [];
    }

    /**
     * Clear all session data
     */
    public function clear(): void
    {
        $this->start();
        $_SESSION = [];
    }

    /**
     * Destroy the session
     */
    public function destroy(): void
    {
        $this->start();
        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                "",
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"],
            );
        }

        session_destroy();
        $this->started = false;
    }

    /**
     * Regenerate session ID (for security)
     */
    public function regenerate(bool $deleteOld = true): bool
    {
        $this->start();
        return session_regenerate_id($deleteOld);
    }

    /**
     * Flash message functionality - set a message that will be removed after being accessed
     */
    public function flash(string $key, $value = null)
    {
        $this->start();

        if ($value === null) {
            // Get and remove flash message
            $value = $_SESSION["_flash"][$key] ?? null;
            unset($_SESSION["_flash"][$key]);
            return $value;
        }

        // Set flash message
        if (!isset($_SESSION["_flash"])) {
            $_SESSION["_flash"] = [];
        }
        $_SESSION["_flash"][$key] = $value;
    }

    /**
     * CSRF token management
     */
    public function getCsrfToken(): string
    {
        $this->start();

        if (!isset($_SESSION["csrf_token"])) {
            $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
        }

        return $_SESSION["csrf_token"];
    }

    /**
     * Validate CSRF token
     */
    public function validateCsrfToken(string $token): bool
    {
        $this->start();
        return isset($_SESSION["csrf_token"]) &&
            hash_equals($_SESSION["csrf_token"], $token);
    }

    /**
     * Login user
     */
    public function login(string $username, bool $isAdmin = false): void
    {
        $this->start();
        $_SESSION["username"] = $username;
        $_SESSION["is_admin"] = $isAdmin;
        $this->regenerate();
    }

    /**
     * Check if current user is admin
     */
    public function isAdmin(): bool
    {
        $this->start();
        return isset($_SESSION["is_admin"]) && $_SESSION["is_admin"] === true;
    }

    /**
     * Logout user
     */
    public function logout(): void
    {
        $this->destroy();
    }
}

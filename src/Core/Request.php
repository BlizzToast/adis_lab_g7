<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Request class to encapsulate HTTP request data
 */
class Request
{
    private array $get;
    private array $post;
    private array $server;
    private array $session;
    private array $cookies;
    private array $files;

    public function __construct()
    {
        $this->get = $_GET ?? [];
        $this->post = $_POST ?? [];
        $this->server = $_SERVER ?? [];
        $this->cookies = $_COOKIE ?? [];
        $this->files = $_FILES ?? [];
        $this->session = [];
    }

    /**
     * Get the request method (GET, POST, etc.)
     */
    public function getMethod(): string
    {
        return strtoupper($this->server["REQUEST_METHOD"] ?? "GET");
    }

    /**
     * Get the request URI
     */
    public function getUri(): string
    {
        $uri = $this->server["REQUEST_URI"] ?? "/";

        // Remove query string
        if ($pos = strpos($uri, "?")) {
            $uri = substr($uri, 0, $pos);
        }

        return $uri;
    }

    /**
     * Get the request path (URI without base path)
     */
    public function getPath(): string
    {
        $uri = $this->getUri();

        // Remove trailing slash except for root
        if ($uri !== "/" && substr($uri, -1) === "/") {
            $uri = rtrim($uri, "/");
        }

        return $uri;
    }

    /**
     * Get a value from GET parameters
     */
    public function get(string $key, $default = null)
    {
        return $this->get[$key] ?? $default;
    }

    /**
     * Get a value from POST data
     */
    public function post(string $key, $default = null)
    {
        return $this->post[$key] ?? $default;
    }

    /**
     * Check if a parameter exists
     */
    public function has(string $key): bool
    {
        return isset($this->post[$key]) || isset($this->get[$key]);
    }

    /**
     * Get a server variable
     */
    public function server(string $key, $default = null)
    {
        return $this->server[$key] ?? $default;
    }

    /**
     * Get a cookie value
     */
    public function cookie(string $key, $default = null)
    {
        return $this->cookies[$key] ?? $default;
    }

    /**
     * Get uploaded file information
     */
    public function file(string $key)
    {
        return $this->files[$key] ?? null;
    }

    /**
     * Check if the request is an AJAX request
     */
    public function isAjax(): bool
    {
        return strtolower($this->server("HTTP_X_REQUESTED_WITH", "")) ===
            "xmlhttprequest";
    }

    /**
     * Set session data (used by Session class)
     */
    public function setSessionData(array $session): void
    {
        $this->session = $session;
    }

    /**
     * Get session value
     */
    public function session(string $key, $default = null)
    {
        return $this->session[$key] ?? $default;
    }
}

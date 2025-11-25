<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

/**
 * Base Controller class with common functionality
 */
abstract class Controller
{
    protected Request $request;
    protected Response $response;
    protected Session $session;
    protected array $config;

    public function __construct(
        Request $request,
        Response $response,
        Session $session,
        array $config,
    ) {
        $this->request = $request;
        $this->response = $response;
        $this->session = $session;
        $this->config = $config;
    }

    /**
     * Render a view with a layout
     */
    protected function render(
        string $view,
        array $data = [],
        string $layout = "layouts/main",
    ): void {
        // Extract data
        extract($data);

        // Make session and config available in views
        $session = $this->session;
        $config = $this->config;
        $request = $this->request;

        // Build view path
        $viewPath =
            $this->config["paths"]["views"] .
            str_replace(".", "/", $view) .
            ".php";
        $layoutPath =
            $this->config["paths"]["views"] .
            str_replace(".", "/", $layout) .
            ".php";

        if (!file_exists($viewPath)) {
            throw new \RuntimeException("View file not found: $viewPath");
        }

        if (!file_exists($layoutPath)) {
            throw new \RuntimeException("Layout file not found: $layoutPath");
        }

        // Capture view content
        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        // Render layout with content
        ob_start();
        require $layoutPath;
        $output = ob_get_clean();

        // Send response
        $this->response->setBody($output);
        $this->response->send();
    }

    /**
     * Redirect to another URL
     */
    protected function redirect(string $url): void
    {
        $this->response->redirect($url);
    }

    /**
     * Send a JSON response
     */
    protected function json(array $data, int $statusCode = 200): void
    {
        $this->response->json($data, $statusCode);
    }

    /**
     * Send an error response
     */
    protected function error(int $statusCode = 500, string $message = ""): void
    {
        $this->response->error($statusCode, $message);
    }

    /**
     * Check if user is authenticated
     */
    protected function requireAuth(): void
    {
        if (!$this->session->has("username")) {
            $this->session->flash(
                "error",
                "You must be logged in to access this page",
            );
            $this->redirect("/login");
        }
    }

    /**
     * Check if user is guest (not authenticated)
     */
    protected function requireGuest(): void
    {
        if ($this->session->has("username")) {
            $this->redirect("/");
        }
    }

    /**
     * Validate CSRF token
     */
    protected function validateCsrf(): bool
    {
        if ($this->request->getMethod() !== "POST") {
            return true;
        }

        $token = $this->request->post("csrf_token");
        return $this->session->validateCsrfToken($token);
    }

    /**
     * Get all flash messages
     */
    protected function getFlashMessages(): array
    {
        return [
            "success" => $this->session->flash("success"),
            "error" => $this->session->flash("error"),
            "info" => $this->session->flash("info"),
            "warning" => $this->session->flash("warning"),
        ];
    }
}

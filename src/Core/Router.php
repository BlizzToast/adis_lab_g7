<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Router class for URL routing and dispatching
 */
class Router
{
    private array $routes = [];
    private Request $request;
    private Response $response;
    private Session $session;
    private array $config;

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
     * Register a GET route
     */
    public function get(string $path, $handler): void
    {
        $this->addRoute("GET", $path, $handler);
    }

    /**
     * Register a POST route
     */
    public function post(string $path, $handler): void
    {
        $this->addRoute("POST", $path, $handler);
    }

    /**
     * Add a route to the routes array
     */
    private function addRoute(string $method, string $path, $handler): void
    {
        // Normalize path
        $path = $this->normalizePath($path);

        $this->routes[] = [
            "method" => $method,
            "path" => $path,
            "handler" => $handler,
            "pattern" => $this->convertPathToPattern($path),
        ];
    }

    /**
     * Normalize path (ensure it starts with /)
     */
    private function normalizePath(string $path): string
    {
        if ($path === "" || $path === "/") {
            return "/";
        }

        $path = "/" . ltrim($path, "/");
        $path = rtrim($path, "/");

        return $path;
    }

    /**
     * Convert path to regex pattern for matching
     */
    private function convertPathToPattern(string $path): string
    {
        // Convert path parameters like {id} to regex groups
        $pattern = preg_replace(
            "/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/",
            '(?P<$1>[^/]+)',
            $path,
        );

        // Escape forward slashes and add delimiters
        $pattern = "#^" . $pattern . '$#';

        return $pattern;
    }

    /**
     * Dispatch the current request to the appropriate handler
     */
    public function dispatch(): void
    {
        $requestPath = $this->normalizePath($this->request->getPath());
        $requestMethod = $this->request->getMethod();

        foreach ($this->routes as $route) {
            // Check if method matches
            if ($route["method"] !== $requestMethod) {
                continue;
            }

            // Check if path matches
            if (preg_match($route["pattern"], $requestPath, $matches)) {
                // Extract parameters
                $params = array_filter(
                    $matches,
                    "is_string",
                    ARRAY_FILTER_USE_KEY,
                );

                // Execute handler
                $this->executeHandler($route["handler"], $params);
                return;
            }
        }

        // No route found - 404
        $this->response->error(404, "Page not found");
    }

    /**
     * Execute a route handler
     */
    private function executeHandler($handler, array $params = []): void
    {
        // Handler can be:
        // 1. A callable (function)
        // 2. An array [ControllerClass, 'method']
        // 3. A string 'ControllerClass@method'

        if (is_callable($handler)) {
            // Direct callable
            call_user_func(
                $handler,
                $this->request,
                $this->response,
                $this->session,
                $params,
            );
        } elseif (is_array($handler) && count($handler) === 2) {
            // Array format [Controller, 'method']
            $this->callControllerMethod($handler[0], $handler[1], $params);
        } elseif (is_string($handler) && strpos($handler, "@") !== false) {
            // String format 'Controller@method'
            [$controllerClass, $method] = explode("@", $handler, 2);
            $this->callControllerMethod($controllerClass, $method, $params);
        } else {
            throw new \RuntimeException("Invalid route handler");
        }
    }

    /**
     * Call a controller method
     */
    private function callControllerMethod(
        string $controllerClass,
        string $method,
        array $params,
    ): void {
        // Add namespace if not present
        if (strpos($controllerClass, "\\") === false) {
            $controllerClass = "App\\Controllers\\" . $controllerClass;
        }

        if (!class_exists($controllerClass)) {
            throw new \RuntimeException(
                "Controller class '$controllerClass' not found",
            );
        }

        $controller = new $controllerClass(
            $this->request,
            $this->response,
            $this->session,
            $this->config,
        );

        if (!method_exists($controller, $method)) {
            throw new \RuntimeException(
                "Method '$method' not found in controller '$controllerClass'",
            );
        }

        // Call the controller method with params
        call_user_func_array([$controller, $method], [$params]);
    }
}

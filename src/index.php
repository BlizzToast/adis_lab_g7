<?php
declare(strict_types=1);

/**
 * Front Controller - Single entry point for the application
 * Routes all requests to appropriate controllers
 */

// Error reporting for development
error_reporting(E_ALL);
ini_set("display_errors", "1");

// Define base path
define("BASE_PATH", __DIR__);

// Log server startup
error_log(
    "[" . date("Y-m-d H:i:s") . "] 🐧 Roary server started - Ready to roar!",
);
error_log(
    "[" .
        date("Y-m-d H:i:s") .
        "] Server info: PHP " .
        phpversion() .
        " | " .
        php_sapi_name(),
);
error_log("[" . date("Y-m-d H:i:s") . "] Base path: " . __DIR__);

// Simple autoloader for our classes
spl_autoload_register(function ($class) {
    // Convert namespace to file path
    $prefix = "App\\";
    $baseDir = __DIR__ . "/";

    // Check if class uses our namespace
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    // Get relative class name
    $relativeClass = substr($class, $len);

    // Replace namespace separator with directory separator
    $file = $baseDir . str_replace("\\", "/", $relativeClass) . ".php";

    // Require file if it exists
    if (file_exists($file)) {
        require $file;
    }
});

// Load configuration
$config = require __DIR__ . "/config/config.php";

// Initialize core components
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Router;

$request = new Request();
$response = new Response();
$session = Session::getInstance($config["session"]);
$session->start();

// Make session data available in request
$request->setSessionData($session->all());

// Create router
$router = new Router($request, $response, $session, $config);

// ====================================
// Define Routes
// ====================================

// Home routes
$router->get("/", "HomeController@index");
$router->get("/index", "HomeController@index");
$router->get("/index.php", "HomeController@index");
$router->post("/create-post", "HomeController@createPost");

// Authentication routes
$router->get("/login", "UserController@showLogin");
$router->post("/login", "UserController@login");
$router->get("/register", "UserController@showRegister");
$router->post("/register", "UserController@register");
$router->get("/logout", "UserController@logout");
$router->post("/logout", "UserController@logout");

// User profile routes
$router->get("/profile", "UserController@profile");
$router->post("/profile/update-username", "UserController@updateUsername");
$router->post("/profile/update-password", "UserController@updatePassword");
$router->post("/profile/delete", "UserController@deleteAccount");
$router->get("/user/{username}", "PostController@userPosts");

// Post routes
$router->get("/posts", "PostController@index");
$router->post("/post", "PostController@create");
$router->get("/post/{id}", "PostController@show");
$router->post("/post/{id}/delete", "PostController@delete");
$router->get("/search", "PostController@search");

// API routes (for AJAX)
$router->get("/api/posts/recent", "PostController@recent");

// Admin routes
$router->get("/admin", "AdminController@index");
$router->post("/admin/reset", "AdminController@reset");
$router->post("/admin/delete-user", "AdminController@deleteUser");
$router->post("/admin/impersonate", "AdminController@impersonate");

// Static asset handling for public directory
$router->get("/public/{path}", function (
    $request,
    $response,
    $session,
    $params,
) {
    $path = $params["path"] ?? "";
    $file = __DIR__ . "/public/" . $path;

    if (file_exists($file) && is_file($file)) {
        // Determine content type
        $extension = pathinfo($file, PATHINFO_EXTENSION);
        $contentTypes = [
            "css" => "text/css",
            "js" => "application/javascript",
            "json" => "application/json",
            "png" => "image/png",
            "jpg" => "image/jpeg",
            "jpeg" => "image/jpeg",
            "gif" => "image/gif",
            "svg" => "image/svg+xml",
            "ico" => "image/x-icon",
        ];

        $contentType = $contentTypes[$extension] ?? "application/octet-stream";

        $response->setHeader("Content-Type", $contentType);
        $response->setBody(file_get_contents($file));
        $response->send();
    } else {
        $response->error(404, "File not found");
    }
    exit();
});

// ====================================
// Dispatch the request
// ====================================
try {
    // Special handling for development files
    $path = $request->getPath();

    // Handle metrics.php
    if ($path === "/metrics" || $path === "/metrics.php") {
        if (file_exists(__DIR__ . "/metrics.php")) {
            require __DIR__ . "/metrics.php";
        } else {
            $response->error(404, "Metrics endpoint not found");
        }
        exit();
    }

    // Handle info.php
    if ($path === "/info" || $path === "/info.php") {
        if (file_exists(__DIR__ . "/info.php")) {
            require __DIR__ . "/info.php";
        } else {
            phpinfo();
        }
        exit();
    }

    // Handle debug.php
    if (
        $path === "/debug" ||
        $path === "/admin/debug" ||
        $path === "/admin/debug.php"
    ) {
        if (file_exists(__DIR__ . "/admin/debug.php")) {
            // Make session available for debug page
            $_SESSION = $session->all();
            require __DIR__ . "/admin/debug.php";
        } else {
            $response->error(404, "Debug page not found");
        }
        exit();
    }

    // Dispatch regular routes
    $router->dispatch();
} catch (\Exception $e) {
    // Log error
    error_log("Application error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());

    // Show error page
    if ($config["app"]["debug"] ?? false) {
        $response->setStatusCode(500);
        $response->setBody(
            "<h1>Error</h1><pre>" .
                htmlspecialchars($e->getMessage()) .
                "</pre><pre>" .
                htmlspecialchars($e->getTraceAsString()) .
                "</pre>",
        );
        $response->send();
    } else {
        $response->error(500, "Internal Server Error");
    }
}

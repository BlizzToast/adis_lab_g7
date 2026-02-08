<?php
declare(strict_types=1);

/**
 * Prometheus Metrics Endpoint
 * Expose custom application metrics in Prometheus format
 */

// Load configuration
$config = require __DIR__ . "/config/config.php";

header('Content-Type: text/plain; version=0.0.4');

// Autoloader for classes
spl_autoload_register(function ($class) {
    $prefix = "App\\";
    $baseDir = __DIR__ . "/";
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace("\\", "/", $relativeClass) . ".php";
    
    if (file_exists($file)) {
        require $file;
    }
});

use App\Models\Database;

try {
    // Get database instance
    $db = Database::getInstance($config["database"]);
    $conn = $db->getConnection();
    
    // Metric 1: Database file size in bytes
    $dbPath = $config["database"]["path"];
    $dbSize = 0;
    
    if (file_exists($dbPath)) {
        $dbSize = filesize($dbPath);
    }
    
    // Metric 2: Total number of posts
    $stmt = $conn->query("SELECT COUNT(*) as total FROM messages");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalPosts = $result ? (int)$result['total'] : 0;
    
    // Output metrics in Prometheus format
    
    // Database size metric
    echo "# HELP roary_db_size_bytes The size of the SQLite database file on disk in bytes\n";
    echo "# TYPE roary_db_size_bytes gauge\n";
    echo "roary_db_size_bytes $dbSize\n";
    echo "\n";
    
    // Total posts metric
    echo "# HELP roary_posts_total The total number of posts currently in the database\n";
    echo "# TYPE roary_posts_total counter\n";
    echo "roary_posts_total $totalPosts\n";
    
} catch (Exception $e) {
    error_log("Metrics error: " . $e->getMessage());
}

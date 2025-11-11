username TEXT NOT NULL UNIQUE,

<?php
// admin.php
session_start();

// Database configuration
define('DB_PATH', __DIR__ . '/database.db');

/**
 * Create SQLite database with initial schema
 */
function createDatabase() {
    try {
        // Create new SQLite database
        $db = new SQLite3(DB_PATH);
        
        // Create tables (example schema)
        $db->exec('
            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                email TEXT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );
        ');
        
        $db->close();
        return "Database created successfully at: " . DB_PATH;
    } catch (Exception $e) {
        return "Error creating database: " . $e->getMessage();
    }
}

// Handle form submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_db'])) {
    $message = createDatabase();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .btn { padding: 10px 20px; background: #007bff; color: white; border: none; cursor: pointer; font-size: 16px; }
        .btn:hover { background: #0056b3; }
        .message { padding: 10px; margin: 10px 0; border-radius: 4px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <h1>Admin Panel</h1>
    
    <?php if ($message): ?>
        <div class="message <?php echo strpos($message, 'Error') !== false ? 'error' : 'success'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>
    
    <form method="POST">
        <button type="submit" name="create_db" class="btn">Create Database</button>
    </form>
</body>
</html>
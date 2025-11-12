<?php

// load session bootstrap
require_once 'session_bootstrap.php';

// admin.php
session_start();

// Database configuration
define('DB_PATH', __DIR__ . '/database.db');

/**
 * Create SQLite database with initial schema (username instead of email)
 */
function createDatabase() {
    try {
        // Create new SQLite database
        $db = new SQLite3(DB_PATH);

        // Create tables (username is unique)
        $db->exec(
            "CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT NOT NULL UNIQUE,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                password_hash TEXT NOT NULL
            );"
        );

        $db->close();
        return "Database created successfully at: " . DB_PATH;
    } catch (Exception $e) {
        return "Error creating database: " . $e->getMessage();
    }
}

/**
 * Get database connection and return data for HTML view
 */
function getDatabaseForView() {
    if (!file_exists(DB_PATH)) {
        return null;
    }

    try {
        $db = new SQLite3(DB_PATH);
        $results = $db->query('SELECT * FROM users;');

        $data = [];
        while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
            $data[] = $row;
        }

        $db->close();
        return $data;
    } catch (Exception $e) {
        return null;
    }
}

function registerUser($username, $password) {
    if (!file_exists(DB_PATH)) {
        return "Database does not exist. Please create the database first.";
    }

    try {
        $db = new SQLite3(DB_PATH);

        // Hash the password
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        // Prepare and execute insert statement
        $stmt = $db->prepare('INSERT INTO users (username, password_hash) VALUES (:username, :password_hash);');
        $stmt->bindValue(':username', $username, SQLITE3_TEXT);
        $stmt->bindValue(':password_hash', $passwordHash, SQLITE3_TEXT);

        $stmt->execute();
        $db->close();

        return "User registered successfully.";
    } catch (Exception $e) {
        return "Error registering user: " . $e->getMessage();
    }
}
function login($username, $password) {
    if (!file_exists(DB_PATH)) {
        return "Database does not exist. Please create the database first.";
    }

    try {
        $db = new SQLite3(DB_PATH);

        // Prepare and execute select statement
        $stmt = $db->prepare('SELECT password_hash FROM users WHERE username = :username;');
        $stmt->bindValue(':username', $username, SQLITE3_TEXT);
        $result = $stmt->execute();

        $row = $result->fetchArray(SQLITE3_ASSOC);
        if ($row && password_verify($password, $row['password_hash'])) {
            $_SESSION['username'] = $username;
            $_SESSION['logged_in'] = true;
            return true;
        } else {
            return false;
        }

        $db->close();
    } catch (Exception $e) {
        return "Error during login: " . $e->getMessage();
    }
}

// Handle form submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_db'])) {
    $message = createDatabase();
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_user'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $message = registerUser($username, $password);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $message = login($username, $password);
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
        label { display:block; margin-top:8px; }
        input[type="text"], input[type="password"], input[type="email"] { padding:8px; width:100%; max-width:400px; box-sizing:border-box; }
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

    <!-- simple register form to add a user -->
    <form method="POST" style="margin-top: 20px;">
        <h2>Add User</h2>
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required minlength="3"><br /><br />
        <label for="password">Password (min 12 chars):</label>
        <input type="password" id="password" name="password" required minlength="12"><br /><br />
        <button type="submit" class="btn" name="register_user">Register User</button>
    </form>
    <hr />
    <section>
        
    <h2> Database View </h2>
    <?php
    $data = getDatabaseForView();
    if ($data === null): ?>
        <p>No database found. Please create the database first.</p>
    <?php elseif (empty($data)): ?>
        <p>No data available in the database.</p>
    <?php else: ?>
        <table border="1" cellpadding="5" cellspacing="0">
            <tr>
                <?php foreach (array_keys($data[0]) as $column): ?>
                    <th><?php echo htmlspecialchars($column); ?></th>
                <?php endforeach; ?>
            </tr>
            <?php foreach ($data as $row): ?>
                <tr>
                    <?php foreach ($row as $value): ?>
                        <td><?php echo htmlspecialchars($value); ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif;
    ?>

    </section>
    <hr> 
    <section>
    <h2> Login </h2>
    <p>This is for testing the login functionality. Enter username and password in the form below:</p>
    <form id="loginForm" method="POST">
        <fieldset>
            <legend>Enter your credentials</legend>

            <label for="username">Username</label>
            <input type="text" id="username" name="username" required pattern="^[a-zA-Z0-9]+$" title="Only alphanumeric characters allowed">

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required minlength="12">

            <button type="submit" class="btn btn-primary" name="login">Login</button>
        </fieldset>
</body>
</html>
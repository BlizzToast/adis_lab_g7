<?php
// Load session bootstrap (starts session and sets secure params)
require_once __DIR__ . '/session_bootstrap.php';

$error = '';
$username = '';

// SQLite database path (created by admin.php)
define('DB_PATH', __DIR__ . '/database.db');

function authenticateUser(string $username, string $password) {
    if (!file_exists(DB_PATH)) {
        return "Database not found. Please create it using admin.php.";
    }

    try {
        $db = new SQLite3(DB_PATH);
        $stmt = $db->prepare('SELECT password_hash FROM users WHERE username = :username LIMIT 1;');
        $stmt->bindValue(':username', $username, SQLITE3_TEXT);
        $res = $stmt->execute();
        $row = $res->fetchArray(SQLITE3_ASSOC);
        $db->close();

        if (!$row) {
            return 'Invalid username or password.';
        }

        if (password_verify($password, $row['password_hash'])) {
            return true;
        }

        return 'Invalid username or password.';
    } catch (Exception $e) {
        return 'Authentication error: ' . $e->getMessage();
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? '');
    $password = $_POST["password"] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Please fill in both fields.';
    } else {
        $result = authenticateUser($username, $password);
        if ($result === true) {
            // Successful login: regenerate session and set session vars
            session_regenerate_id(true);
            $_SESSION['user'] = $username;
            $_SESSION['logged_in'] = true;
            header('Location: index.php');
            exit;
        } else {
            $error = $result;
            error_log("Login failure for user '$username': $error");
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Roary</title>
    <link rel="stylesheet" href="https://unpkg.com/terminal.css@0.7.4/dist/terminal.min.css">
</head>
<body>
    <div class="container">
        <?php include __DIR__ . '/header.php'; ?>

        <main>
            <h1>Login</h1>
            <form id="loginForm" method="POST">
                <fieldset>
                    <legend>Enter your credentials</legend>

                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required pattern="^[a-zA-Z0-9]+$" title="Only alphanumeric characters allowed">

                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required minlength="12">

                    <button type="submit" class="btn btn-primary">Login</button>
                </fieldset>
            </form>

            <p>Don't have an account? <a href="register.php">Register here</a></p>
        </main>
    </div>

    <script src="user.js"></script>
</body>
</html>

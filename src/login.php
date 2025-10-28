<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST["username"] ?? "";
    $password = $_POST["password"] ?? "";
    error_log(
        "Login attempt - Username: $username, Password length: " .
            strlen($password),
    );
} ?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Rory</title>
    <link rel="stylesheet" href="https://unpkg.com/terminal.css@0.7.4/dist/terminal.min.css">
</head>
<body>
    <nav>
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="login.php">Login</a></li>
            <li><a href="register.php">Register</a></li>
        </ul>
    </nav>

    <main class="container">
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

    <script src="user.js"></script>
</body>
</html>

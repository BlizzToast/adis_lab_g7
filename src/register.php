<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST["username"] ?? "";
    $password = $_POST["password"] ?? "";
    error_log(
        "Registration attempt - Username: $username, Password length: " .
            strlen($password) . " - WARNING: No actual registration functionality implemented yet!"
    );
    error_log("WARNING: No actual registration functionality implemented yet!");
} ?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Roary</title>
    <link rel="stylesheet" href="https://unpkg.com/terminal.css@0.7.4/dist/terminal.min.css">
</head>
<body>
    <div class="container">
        <?php include __DIR__ . '/header.php'; ?>

        <main>
            <h1>Register</h1>
            <form id="registerForm" method="POST">
                <fieldset>
                    <legend>Create a new account</legend>

                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required pattern="^[a-zA-Z0-9]+$" title="Only alphanumeric characters allowed">

                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required minlength="12">

                    <label for="confirmPassword">Confirm Password</label>
                    <input type="password" id="confirmPassword" name="confirmPassword" required minlength="12">

                    <button type="submit" class="btn btn-primary">Register</button>
                </fieldset>
            </form>

            <p>Already have an account? <a href="login.php">Login here</a></p>
        </main>
    </div>

    <script src="user.js"></script>
</body>
</html>

<?php
// Load session bootstrap (starts session and sets secure params)
require_once __DIR__ . '/session_bootstrap.php';


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Roary</title>
    <link rel="stylesheet" href="https://unpkg.com/terminal.css@0.7.4/dist/terminal.min.css">
</head>
<body>
    <div class="container">
        <div class="terminal-nav">
            <div class="terminal-logo">
                <div class="logo terminal-prompt"><a href="index.php" class="no-style">Roary</a></div>
            </div>
            <nav class="terminal-menu">
                <ul>
                    <li><a class="menu-item active" href="index.php">Home</a></li>
                    <li><a class="menu-item" href="login.php">Login</a></li>
                    <li><a class="menu-item" href="register.php">Register</a></li>
                </ul>
            </nav>
        </div>

        <main>
            <h1>🐧 Roary</h1>
            <p>Where Penguins Roar and Vibes Soar!</p>
            <p>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>

            <fieldset>
                <legend>Share your thoughts</legend>
                <textarea id="postInput" placeholder="Something to roar about?" rows="4"></textarea>
                <button id="postBtn" class="btn btn-primary">ROAR IT!</button>
            </fieldset>

            <h2>Feed</h2>
            <div class="feed" id="feed"></div>
        </main>
    </div>
    <script src="roary.js"></script>
</body>
</html>

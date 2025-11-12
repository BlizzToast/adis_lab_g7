<?php
// Load session bootstrap (starts session and sets secure params)
require_once __DIR__ . '/session_bootstrap.php';
// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

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
        <?php include __DIR__ . '/header.php'; ?>

        <main>
            <h1>🐧 Roary</h1>
            <p>Where Penguins Roar and Vibes Soar!</p>
            <p>Welcome, <?php echo htmlspecialchars($_SESSION['user']); ?>!</p>

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

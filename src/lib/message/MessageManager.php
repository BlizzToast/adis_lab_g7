<?php
declare(strict_types=1);

/**
 * MessageManager - Handles message posting and retrieval
 * Messages are read-heavy, so we optimize for reading performance
 */
class MessageManager
{
    private SQLite3 $db;

    public function __construct(string $dbPath)
    {
        $this->db = new SQLite3($dbPath);
        $this->initDatabase();
    }

    // Initialize the messages database and indexes
    private function initDatabase(): void
    {
        $this->db->exec("CREATE TABLE IF NOT EXISTS messages (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL,
            content TEXT NOT NULL,
            created_at INTEGER NOT NULL,
            FOREIGN KEY (username) REFERENCES users(username) ON DELETE CASCADE
        )");
        
        $this->db->exec("CREATE INDEX IF NOT EXISTS idx_messages_created_at ON messages(created_at DESC)");
    }

    public function postMessage(string $username, string $content): bool
    {
        if (empty(trim($content)) || strlen($content) > 10000) {
            return false;
        }

        $stmt = $this->db->prepare("INSERT INTO messages (username, content, created_at) VALUES (:username, :content, :created_at)");
        $stmt->bindValue(':username', $username, SQLITE3_TEXT);
        $stmt->bindValue(':content', $content, SQLITE3_TEXT);
        $stmt->bindValue(':created_at', time(), SQLITE3_INTEGER);
        
        return $stmt->execute() !== false;
    }

    public function getAllMessages(int $limit = 100): array
    {
        $stmt = $this->db->prepare("
            SELECT m.id, m.username, m.content, m.created_at, u.avatar 
            FROM messages m 
            JOIN users u ON m.username = u.username 
            ORDER BY m.created_at DESC 
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, SQLITE3_INTEGER);
        $result = $stmt->execute();
        
        $messages = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $messages[] = $row;
        }
        
        return $messages;
    }

    public function hasMessages(): bool
    {
        return $this->db->querySingle("SELECT COUNT(*) FROM messages") > 0;
    }

    public function handlePost(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        session_start();
        
        if (!isset($_SESSION['username'])) {
            header('Location: /login');
            exit;
        }

        $content = $_POST['content'] ?? '';
        
        if (!empty(trim($content))) {
            $this->postMessage($_SESSION['username'], $content);
        }
        
        header('Location: /index');
        exit;
    }

    public function __destruct()
    {
        $this->db->close();
    }
}

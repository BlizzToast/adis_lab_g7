<?php
declare(strict_types=1);

/**
 * UserAuth - Core authentication functionality
 * Handles user registration and login operations
 */
class UserAuth
{
    private const MIN_PASSWORD_LENGTH = 12;
    private const USERNAME_PATTERN = '/^[a-zA-Z0-9]+$/';
    
    protected SQLite3 $db;

    public function __construct(string $dbPath)
    {
        $this->db = new SQLite3($dbPath);
        $this->initDatabase();
    }

    private function initDatabase(): void
    {
        $this->db->exec("CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            password_hash TEXT NOT NULL
        )");
    }

    private function validateCredentials(string $username, string $password): bool
    {
        return preg_match(self::USERNAME_PATTERN, $username) === 1 
            && strlen($password) >= self::MIN_PASSWORD_LENGTH;
    }

    public function register(string $username, string $password): bool
    {
        if (!$this->validateCredentials($username, $password)) {
            return false;
        }
        
        $passwordHash = password_hash($password, PASSWORD_ARGON2ID);
        
        $stmt = $this->db->prepare("INSERT INTO users (username, password_hash) VALUES (:username, :password_hash)");
        $stmt->bindValue(':username', $username, SQLITE3_TEXT);
        $stmt->bindValue(':password_hash', $passwordHash, SQLITE3_TEXT);
        
        return $stmt->execute() !== false;
    }

    public function login(string $username, string $password): bool
    {
        if (!$this->validateCredentials($username, $password)) {
            return false;
        }
        
        $user = $this->getUserByUsername($username);
        
        return $user && password_verify($password, $user['password_hash']);
    }

    protected function getUserByUsername(string $username): array|false
    {
        $stmt = $this->db->prepare("SELECT password_hash FROM users WHERE username = :username");
        $stmt->bindValue(':username', $username, SQLITE3_TEXT);
        $result = $stmt->execute();
        
        return $result->fetchArray(SQLITE3_ASSOC);
    }

    public function __destruct()
    {
        $this->db->close();
    }
}

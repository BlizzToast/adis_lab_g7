<?php
declare(strict_types=1);

require_once __DIR__ . '/UserAuth.php';

/**
 * UserManager - Administrative operations for user management
 * Currently used primarily by debug panel
 */
class UserManager extends UserAuth
{
    public function getAllUsers(): array
    {
        $result = $this->db->query("SELECT id, username, password_hash FROM users ORDER BY id ASC");
        $users = [];
        
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $users[] = $row;
        }
        
        return $users;
    }

    public function deleteUser(int $userId): bool
    {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id");
        $stmt->bindValue(':id', $userId, SQLITE3_INTEGER);
        
        return $stmt->execute() !== false;
    }

    public function deleteAllUsers(): bool
    {
        return $this->db->exec("DELETE FROM users") !== false;
    }

    public function getStats(): array
    {
        $totalUsers = $this->db->querySingle("SELECT COUNT(*) FROM users");
        
        return ['total_users' => (int)$totalUsers];
    }

    public function userExists(string $username): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE username = :username");
        $stmt->bindValue(':username', $username, SQLITE3_TEXT);
        $result = $stmt->execute();
        
        return $result->fetchArray(SQLITE3_NUM)[0] > 0;
    }
}

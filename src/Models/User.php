<?php
declare(strict_types=1);

namespace App\Models;

/**
 * User Model - Handles all user-related database operations
 */
class User extends Model
{
    protected string $table = "users";
    protected string $primaryKey = "id";

    /**
     * Create the users table if it doesn't exist
     */
    public function createTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL COLLATE NOCASE,
            password_hash TEXT NOT NULL,
            avatar TEXT DEFAULT '🐧',
            is_admin INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )";

        $this->pdo->exec($sql);
    }

    /**
     * Create a new user
     */
    public function createUser(
        string $username,
        string $password,
        string $avatar = "🐧",
        bool $isAdmin = false,
    ): ?int {
        // Validate input
        $errors = $this->validateUserData($username, $password);
        if (!empty($errors)) {
            return null;
        }

        // Check if username already exists (case-insensitive)
        $existingUser = $this->findByUsernameIgnoreCase($username);
        if ($existingUser !== null) {
            return null;
        }

        // Hash password
        $passwordHash = password_hash($password, PASSWORD_ARGON2ID);

        // Insert user
        try {
            return $this->insert([
                "username" => $username,
                "password_hash" => $passwordHash,
                "avatar" => $avatar,
                "is_admin" => $isAdmin ? 1 : 0,
            ]);
        } catch (\PDOException $e) {
            error_log("Failed to create user: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Verify user credentials
     */
    public function verifyCredentials(string $username, string $password): bool
    {
        // Use case-insensitive lookup
        $user = $this->findByUsernameIgnoreCase($username);

        if (!$user) {
            return false;
        }

        return password_verify($password, $user["password_hash"]);
    }

    /**
     * Authenticate user and return user data if successful
     */
    public function authenticate(string $username, string $password): ?array
    {
        if (!$this->verifyCredentials($username, $password)) {
            return null;
        }

        // Use case-insensitive lookup
        $user = $this->findByUsernameIgnoreCase($username);

        // Remove password hash from returned data
        if ($user) {
            unset($user["password_hash"]);
        }

        return $user;
    }

    /**
     * Update user avatar
     */
    public function updateAvatar(int $userId, string $avatar): bool
    {
        return $this->update($userId, ["avatar" => $avatar]);
    }

    /**
     * Update user password
     */
    public function updatePassword(int $userId, string $newPassword): bool
    {
        if (strlen($newPassword) < 12) {
            return false;
        }

        $passwordHash = password_hash($newPassword, PASSWORD_ARGON2ID);
        return $this->update($userId, ["password_hash" => $passwordHash]);
    }

    /**
     * Delete user and cascade delete their messages
     */
    public function deleteUser(int $userId): bool
    {
        $user = $this->find($userId);
        if (!$user) {
            return false;
        }

        try {
            $this->beginTransaction();

            // Delete user's messages first
            $sql = "DELETE FROM messages WHERE username = :username";
            $this->pdo
                ->prepare($sql)
                ->execute(["username" => $user["username"]]);

            // Delete user
            $this->delete($userId);

            $this->commit();
            return true;
        } catch (\PDOException $e) {
            $this->rollback();
            error_log("Failed to delete user: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Validate user data
     */
    public function validateUserData(string $username, string $password): array
    {
        $errors = [];

        // Validate username
        if (empty($username)) {
            $errors["username"] = "Username is required";
        } elseif (!preg_match('/^[a-zA-Z0-9]+$/', $username)) {
            $errors["username"] =
                "Username must contain only letters and numbers";
        } elseif (strlen($username) < 3) {
            $errors["username"] = "Username must be at least 3 characters long";
        } elseif (strlen($username) > 50) {
            $errors["username"] = "Username must not exceed 50 characters";
        }

        // Validate password
        if (empty($password)) {
            $errors["password"] = "Password is required";
        } elseif (strlen($password) < 12) {
            $errors["password"] =
                "Password must be at least 12 characters long";
        }

        return $errors;
    }

    /**
     * Search users by username
     */
    public function searchUsers(string $query, int $limit = 10): array
    {
        $sql = "SELECT id, username, avatar, created_at
                FROM {$this->table}
                WHERE username LIKE :query
                ORDER BY username ASC
                LIMIT :limit";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(":query", "%" . $query . "%", \PDO::PARAM_STR);
        $stmt->bindValue(":limit", $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Get user statistics
     */
    public function getUserStats(int $userId): array
    {
        $sql = "SELECT
                    COUNT(m.id) as message_count,
                    MAX(m.created_at) as last_message_at
                FROM users u
                LEFT JOIN messages m ON u.username = m.username
                WHERE u.id = :user_id
                GROUP BY u.id";

        $result = $this->db->fetchOne($sql, ["user_id" => $userId]);

        return [
            "message_count" => $result["message_count"] ?? 0,
            "last_message_at" => $result["last_message_at"] ?? null,
        ];
    }

    /**
     * Find user by username case-insensitive
     */
    public function findByUsernameIgnoreCase(string $username): ?array
    {
        $sql =
            "SELECT * FROM users WHERE LOWER(username) = LOWER(:username) LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(["username" => $username]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Update username
     */
    public function updateUsername(int $userId, string $newUsername): bool
    {
        // Validate username
        if (!preg_match('/^[a-zA-Z0-9]+$/', $newUsername)) {
            return false;
        }

        if (strlen($newUsername) < 3 || strlen($newUsername) > 50) {
            return false;
        }

        // Check if new username is already taken (case-insensitive)
        $existingUser = $this->findByUsernameIgnoreCase($newUsername);
        if (
            $existingUser !== null &&
            strtolower($existingUser["username"]) !== strtolower($newUsername)
        ) {
            return false;
        }

        // Update username in users table
        $sql = "UPDATE users SET username = :newUsername WHERE id = :userId";
        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute([
            "newUsername" => $newUsername,
            "userId" => $userId,
        ]);

        if ($result) {
            // Also update username in messages table
            $sql =
                "UPDATE messages SET username = :newUsername WHERE username = (SELECT username FROM users WHERE id = :userId)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                "newUsername" => $newUsername,
                "userId" => $userId,
            ]);
        }

        return $result;
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(string $username): bool
    {
        $user = $this->findBy("username", $username);
        return $user && (bool) $user["is_admin"];
    }

    /**
     * Create admin user with environment password
     */
    public function createAdminUser(): bool
    {
        $adminPassword = getenv("ADMIN_PASSWORD") ?: "admin12345678";

        // Check if admin already exists (case-insensitive)
        $existingAdmin = $this->findByUsernameIgnoreCase("admin");
        if ($existingAdmin !== null) {
            // Update password if it changed
            $passwordHash = password_hash($adminPassword, PASSWORD_ARGON2ID);
            return $this->pdo->exec(
                "UPDATE users SET password_hash = '$passwordHash' WHERE username = 'admin'",
            ) !== false;
        }

        // Create admin user
        $userId = $this->createUser("admin", $adminPassword, "👑", true);
        return $userId !== null;
    }
}

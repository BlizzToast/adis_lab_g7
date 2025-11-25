<?php
declare(strict_types=1);

namespace App\Models;

use PDO;
use PDOException;

/**
 * Database class - Singleton PDO connection manager
 */
class Database
{
    private static ?Database $instance = null;
    private ?PDO $connection = null;
    private array $config;

    private function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Get the singleton instance
     */
    public static function getInstance(array $config): self
    {
        if (self::$instance === null) {
            self::$instance = new self($config);
        }
        return self::$instance;
    }

    /**
     * Get the PDO connection
     */
    public function getConnection(): PDO
    {
        if ($this->connection === null) {
            $this->connect();
        }
        return $this->connection;
    }

    /**
     * Establish database connection
     */
    private function connect(): void
    {
        try {
            $driver = $this->config["driver"] ?? "sqlite";

            if ($driver === "sqlite") {
                $dsn = "sqlite:" . $this->config["path"];

                // Create data directory if it doesn't exist
                $dir = dirname($this->config["path"]);
                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }

                $this->connection = new PDO($dsn);
            } else {
                throw new PDOException("Unsupported database driver: $driver");
            }

            // Set PDO attributes
            if (isset($this->config["options"])) {
                foreach ($this->config["options"] as $key => $value) {
                    $this->connection->setAttribute($key, $value);
                }
            }
        } catch (PDOException $e) {
            throw new PDOException("Connection failed: " . $e->getMessage());
        }
    }

    /**
     * Execute a query and return PDOStatement
     */
    public function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Execute a query and return all results
     */
    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }

    /**
     * Execute a query and return single row
     */
    public function fetchOne(string $sql, array $params = []): ?array
    {
        $result = $this->query($sql, $params)->fetch();
        return $result ?: null;
    }

    /**
     * Execute a query and return single value
     */
    public function fetchValue(string $sql, array $params = [])
    {
        $result = $this->query($sql, $params)->fetchColumn();
        return $result !== false ? $result : null;
    }

    /**
     * Execute an INSERT, UPDATE, or DELETE query
     */
    public function execute(string $sql, array $params = []): int
    {
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }

    /**
     * Get the last insert ID
     */
    public function lastInsertId(): string
    {
        return $this->getConnection()->lastInsertId();
    }

    /**
     * Begin a transaction
     */
    public function beginTransaction(): bool
    {
        return $this->getConnection()->beginTransaction();
    }

    /**
     * Commit a transaction
     */
    public function commit(): bool
    {
        return $this->getConnection()->commit();
    }

    /**
     * Rollback a transaction
     */
    public function rollback(): bool
    {
        return $this->getConnection()->rollBack();
    }

    /**
     * Check if in transaction
     */
    public function inTransaction(): bool
    {
        return $this->getConnection()->inTransaction();
    }

    /**
     * Close the connection
     */
    public function close(): void
    {
        $this->connection = null;
    }

    /**
     * Reset the singleton instance (for database reset)
     */
    public static function reset(): void
    {
        if (self::$instance !== null) {
            self::$instance->connection = null;
            self::$instance = null;
        }
    }

    /**
     * Prevent cloning
     */
    private function __clone() {}

    /**
     * Prevent unserialization
     */
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize singleton");
    }
}

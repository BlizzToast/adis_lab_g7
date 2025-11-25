<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

/**
 * Base Model class with common database operations
 */
abstract class Model
{
    protected Database $db;
    public PDO $pdo;
    protected string $table;
    protected string $primaryKey = "id";

    public function __construct(Database $database = null)
    {
        if ($database === null) {
            // Get database config and create instance
            $config = require __DIR__ . "/../config/config.php";
            $database = Database::getInstance($config["database"]);
        }

        $this->db = $database;
        $this->pdo = $database->getConnection();
    }

    /**
     * Find a record by primary key
     */
    public function find(int $id): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id LIMIT 1";
        return $this->db->fetchOne($sql, ["id" => $id]);
    }

    /**
     * Find a record by a specific column
     */
    public function findBy(string $column, $value): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$column} = :value LIMIT 1";
        return $this->db->fetchOne($sql, ["value" => $value]);
    }

    /**
     * Get all records from the table
     */
    public function all(array $orderBy = [], int $limit = 0): array
    {
        $sql = "SELECT * FROM {$this->table}";

        if (!empty($orderBy)) {
            $orderClauses = [];
            foreach ($orderBy as $col => $dir) {
                $dir = strtoupper($dir) === "DESC" ? "DESC" : "ASC";
                $orderClauses[] = "$col $dir";
            }
            $sql .= " ORDER BY " . implode(", ", $orderClauses);
        }

        if ($limit > 0) {
            $sql .= " LIMIT $limit";
        }

        return $this->db->fetchAll($sql);
    }

    /**
     * Insert a new record
     */
    public function insert(array $data): int
    {
        $columns = array_keys($data);
        $placeholders = array_map(function ($col) {
            return ":$col";
        }, $columns);

        $sql =
            "INSERT INTO {$this->table} (" .
            implode(", ", $columns) .
            ") VALUES (" .
            implode(", ", $placeholders) .
            ")";

        $this->db->execute($sql, $data);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Update a record by primary key
     */
    public function update(int $id, array $data): bool
    {
        if (empty($data)) {
            return false;
        }

        $setClauses = [];
        foreach ($data as $column => $value) {
            $setClauses[] = "$column = :$column";
        }

        $sql =
            "UPDATE {$this->table} SET " .
            implode(", ", $setClauses) .
            " WHERE {$this->primaryKey} = :id";

        $data["id"] = $id;
        return $this->db->execute($sql, $data) > 0;
    }

    /**
     * Delete a record by primary key
     */
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";
        return $this->db->execute($sql, ["id" => $id]) > 0;
    }

    /**
     * Delete all records matching a condition
     */
    public function deleteBy(string $column, $value): int
    {
        $sql = "DELETE FROM {$this->table} WHERE {$column} = :value";
        return $this->db->execute($sql, ["value" => $value]);
    }

    /**
     * Count records in the table
     */
    public function count(array $where = []): int
    {
        $sql = "SELECT COUNT(*) FROM {$this->table}";

        if (!empty($where)) {
            $conditions = [];
            foreach ($where as $column => $value) {
                $conditions[] = "$column = :$column";
            }
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        return (int) $this->db->fetchValue($sql, $where);
    }

    /**
     * Check if a record exists
     */
    public function exists(string $column, $value): bool
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE {$column} = :value";
        return (int) $this->db->fetchValue($sql, ["value" => $value]) > 0;
    }

    /**
     * Begin a transaction
     */
    public function beginTransaction(): bool
    {
        return $this->db->beginTransaction();
    }

    /**
     * Commit a transaction
     */
    public function commit(): bool
    {
        return $this->db->commit();
    }

    /**
     * Rollback a transaction
     */
    public function rollback(): bool
    {
        return $this->db->rollback();
    }

    /**
     * Initialize the table (create if not exists)
     * Should be implemented by child classes
     */
    abstract public function createTable(): void;
}

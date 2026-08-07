<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;

/**
 * Database access layer using PDO with prepared statements.
 *
 * Supports both MySQL and SQLite. All queries MUST use prepared statements.
 */
class Database
{
    private static ?self $instance = null;
    private readonly PDO $connection;

    private function __construct()
    {
        $driver = $_ENV['DB_DRIVER'] ?? 'sqlite';

        if ($driver === 'sqlite') {
            $dbPath = $_ENV['DB_PATH'] ?? dirname(__DIR__, 2) . '/database/kangguircm.sqlite';
            // Make path absolute if relative
            if (!str_starts_with($dbPath, '/')) {
                $dbPath = dirname(__DIR__, 2) . '/' . $dbPath;
            }
            $dir = dirname($dbPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            $dsn = "sqlite:{$dbPath}";
            $this->connection = new PDO($dsn, null, null, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } else {
            $host = $_ENV['DB_HOST'] ?? '127.0.0.1';
            $port = $_ENV['DB_PORT'] ?? '3306';
            $database = $_ENV['DB_DATABASE'] ?? '';
            $username = $_ENV['DB_USERNAME'] ?? '';
            $password = $_ENV['DB_PASSWORD'] ?? '';
            $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
            $this->connection = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
            ]);
        }
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function resetInstance(): void
    {
        self::$instance = null;
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }

    /**
     * Execute a prepared statement.
     *
     * @param string $sql SQL with ? placeholders
     * @param list<mixed> $params Bound parameters
     * @return \PDOStatement
     */
    public function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Fetch a single row.
     *
     * @return array<string, mixed>|false
     */
    public function fetch(string $sql, array $params = []): array|false
    {
        return $this->query($sql, $params)->fetch();
    }

    /**
     * Fetch all rows.
     *
     * @return list<array<string, mixed>>
     */
    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }

    /**
     * Insert a row.
     *
     * @param string $table
     * @param array<string, mixed> $data
     * @return int Last insert ID
     */
    public function insert(string $table, array $data): int
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        $this->query($sql, array_values($data));
        return (int) $this->connection->lastInsertId();
    }

    /**
     * Update rows.
     *
     * @return int Number of affected rows
     */
    public function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        $set = implode(', ', array_map(fn(string $col): string => "{$col} = ?", array_keys($data)));
        $sql = "UPDATE {$table} SET {$set} WHERE {$where}";
        $stmt = $this->query($sql, array_merge(array_values($data), $whereParams));
        return $stmt->rowCount();
    }

    /**
     * Delete rows.
     *
     * @return int Number of affected rows
     */
    public function delete(string $table, string $where, array $params = []): int
    {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }

    public function beginTransaction(): bool
    {
        return $this->connection->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->connection->commit();
    }

    public function rollBack(): bool
    {
        return $this->connection->rollBack();
    }

    public function inTransaction(): bool
    {
        return $this->connection->inTransaction();
    }
}

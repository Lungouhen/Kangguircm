<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;

/**
 * Database access layer using PDO with prepared statements.
 *
 * Singleton pattern ensures a single connection per request.
 * All queries MUST use prepared statements to prevent SQL injection.
 *
 * Usage:
 *   $db = Database::getInstance();
 *   $user = $db->fetch("SELECT id, name FROM users WHERE email = ?", [$email]);
 */
class Database
{
    private static ?self $instance = null;
    private readonly PDO $connection;
    private readonly string $host;
    private readonly string $port;
    private readonly string $database;
    private readonly string $username;
    private readonly string $password;

    private function __construct()
    {
        $this->host = $_ENV['DB_HOST'] ?? '127.0.0.1';
        $this->port = $_ENV['DB_PORT'] ?? '3306';
        $this->database = $_ENV['DB_DATABASE'] ?? '';
        $this->username = $_ENV['DB_USERNAME'] ?? '';
        $this->password = $_ENV['DB_PASSWORD'] ?? '';

        $this->connect();
    }

    /**
     * Establish the PDO connection with secure defaults.
     *
     * @throws PDOException If connection fails
     */
    private function connect(): void
    {
        $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->database};charset=utf8mb4";

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_PERSISTENT         => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
        ];

        try {
            $this->connection = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            throw new PDOException("Database connection failed: " . $e->getMessage(), (int) $e->getCode());
        }
    }

    /**
     * Get the singleton database instance.
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Get the underlying PDO connection.
     */
    public function getConnection(): PDO
    {
        return $this->connection;
    }

    /**
     * Execute a prepared statement and return it.
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
     * @param string $sql SQL with ? placeholders
     * @param list<mixed> $params Bound parameters
     * @return array<string, mixed>|false
     */
    public function fetch(string $sql, array $params = []): array|false
    {
        return $this->query($sql, $params)->fetch();
    }

    /**
     * Fetch all rows.
     *
     * @param string $sql SQL with ? placeholders
     * @param list<mixed> $params Bound parameters
     * @return list<array<string, mixed>>
     */
    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }

    /**
     * Insert a row into a table.
     *
     * @param string $table Table name
     * @param array<string, mixed> $data Column => value pairs
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
     * Update rows in a table.
     *
     * @param string $table Table name
     * @param array<string, mixed> $data Column => value pairs to set
     * @param string $where WHERE clause (without "WHERE")
     * @param list<mixed> $whereParams WHERE clause parameters
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
     * Delete rows from a table.
     *
     * @param string $table Table name
     * @param string $where WHERE clause (without "WHERE")
     * @param list<mixed> $params WHERE clause parameters
     * @return int Number of affected rows
     */
    public function delete(string $table, string $where, array $params = []): int
    {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        $stmt = $this->query($sql, $params);

        return $stmt->rowCount();
    }

    /**
     * Begin a database transaction.
     */
    public function beginTransaction(): bool
    {
        return $this->connection->beginTransaction();
    }

    /**
     * Commit the current transaction.
     */
    public function commit(): bool
    {
        return $this->connection->commit();
    }

    /**
     * Rollback the current transaction.
     */
    public function rollBack(): bool
    {
        return $this->connection->rollBack();
    }

    /**
     * Check if a transaction is active.
     */
    public function inTransaction(): bool
    {
        return $this->connection->inTransaction();
    }
}

<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Contracts\RepositoryInterface;

/**
 * Base repository providing common data access operations.
 *
 * All repositories extend this class and define their specific
 * table name and column list to avoid SELECT * anti-patterns.
 */
abstract class BaseRepository implements RepositoryInterface
{
    /** @var Database */
    protected readonly Database $db;

    /** @var string Table name */
    protected string $table;

    /** @var list<string> Explicit column list (avoids SELECT *) */
    protected array $columns;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * {@inheritdoc}
     */
    public function findById(int $id): array|false
    {
        $columnList = implode(', ', $this->columns);
        return $this->db->fetch(
            "SELECT {$columnList} FROM {$this->table} WHERE id = ?",
            [$id]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function findAll(int $limit = 50, int $offset = 0): array
    {
        $columnList = implode(', ', $this->columns);
        return $this->db->fetchAll(
            "SELECT {$columnList} FROM {$this->table} ORDER BY id DESC LIMIT ? OFFSET ?",
            [$limit, $offset]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $data): int
    {
        return $this->db->insert($this->table, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function update(int $id, array $data): int
    {
        return $this->db->update($this->table, $data, 'id = ?', [$id]);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(int $id): int
    {
        return $this->db->delete($this->table, 'id = ?', [$id]);
    }

    /**
     * Get total record count.
     *
     * @param string|null $where Optional WHERE clause
     * @param list<mixed> $params Optional parameters
     */
    public function count(?string $where = null, array $params = []): int
    {
        $sql = "SELECT COUNT(*) AS count FROM {$this->table}";
        if ($where !== null) {
            $sql .= " WHERE {$where}";
        }
        return (int) $this->db->fetch($sql, $params)['count'];
    }

    /**
     * Begin a database transaction.
     */
    protected function beginTransaction(): bool
    {
        return $this->db->beginTransaction();
    }

    /**
     * Commit the current transaction.
     */
    protected function commit(): bool
    {
        return $this->db->commit();
    }

    /**
     * Rollback the current transaction.
     */
    protected function rollBack(): bool
    {
        return $this->db->rollBack();
    }
}

<?php

declare(strict_types=1);

namespace App\Contracts;

/**
 * Generic repository interface for data access layer.
 *
 * Enforces the Repository pattern for separation of concerns
 * between business logic and data persistence.
 *
 * @template T
 */
interface RepositoryInterface
{
    /**
     * Find a record by its primary key.
     *
     * @param int $id
     * @return array<string, mixed>|false
     */
    public function findById(int $id): array|false;

    /**
     * Retrieve all records with optional limit and offset.
     *
     * @param int $limit
     * @param int $offset
     * @return list<array<string, mixed>>
     */
    public function findAll(int $limit = 50, int $offset = 0): array;

    /**
     * Create a new record.
     *
     * @param array<string, mixed> $data
     * @return int The inserted record ID
     */
    public function create(array $data): int;

    /**
     * Update an existing record.
     *
     * @param int $id
     * @param array<string, mixed> $data
     * @return int Number of affected rows
     */
    public function update(int $id, array $data): int;

    /**
     * Delete a record by its primary key.
     *
     * @param int $id
     * @return int Number of affected rows
     */
    public function delete(int $id): int;
}

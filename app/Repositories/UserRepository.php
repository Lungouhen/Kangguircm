<?php

declare(strict_types=1);

namespace App\Repositories;

/**
 * User data access layer.
 *
 * Handles all database operations for the users table.
 * Never selects password column unless explicitly needed.
 */
class UserRepository extends BaseRepository
{
    protected readonly string $table = 'users';

    /** @var list<string> Safe columns (excludes password) */
    protected readonly array $columns = [
        'id', 'name', 'email', 'role_id',
        'email_verified_at', 'created_at', 'updated_at',
    ];

    /**
     * Find a user by email address.
     *
     * @param string $email
     * @return array<string, mixed>|false
     */
    public function findByEmail(string $email): array|false
    {
        return $this->db->fetch(
            "SELECT id, name, email, password, role_id FROM {$this->table} WHERE email = ?",
            [$email]
        );
    }

    /**
     * Find a user by email (without password for display purposes).
     *
     * @param string $email
     * @return array<string, mixed>|false
     */
    public function findSafeByEmail(string $email): array|false
    {
        $columnList = implode(', ', $this->columns);
        return $this->db->fetch(
            "SELECT {$columnList} FROM {$this->table} WHERE email = ?",
            [$email]
        );
    }

    /**
     * Find a user by ID with their role information.
     *
     * @param int $id
     * @return array<string, mixed>|false
     */
    public function findByIdWithRole(int $id): array|false
    {
        return $this->db->fetch(
            "SELECT u.id, u.name, u.email, u.role_id, u.created_at,
                    r.name AS role_name, r.description AS role_description
             FROM {$this->table} u
             LEFT JOIN roles r ON u.role_id = r.id
             WHERE u.id = ?",
            [$id]
        );
    }

    /**
     * Get all users (safe columns only, no passwords).
     *
     * @param int $limit
     * @param int $offset
     * @return list<array<string, mixed>>
     */
    public function findAll(int $limit = 50, int $offset = 0): array
    {
        $columnList = implode(', ', $this->columns);
        return $this->db->fetchAll(
            "SELECT {$columnList} FROM {$this->table} ORDER BY created_at DESC LIMIT ? OFFSET ?",
            [$limit, $offset]
        );
    }

    /**
     * Create a new user with hashed password.
     *
     * @param array<string, mixed> $data Must include 'password' (plaintext)
     * @return int The new user ID
     */
    public function create(array $data): int
    {
        $data['password'] = password_hash($data['password'], PASSWORD_ARGON2ID);
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        return $this->db->insert($this->table, $data);
    }

    /**
     * Update a user. Re-hashes password if provided.
     *
     * @param int $id
     * @param array<string, mixed> $data
     * @return int Number of affected rows
     */
    public function update(int $id, array $data): int
    {
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_ARGON2ID);
        }
        $data['updated_at'] = date('Y-m-d H:i:s');

        return $this->db->update($this->table, $data, 'id = ?', [$id]);
    }

    /**
     * Verify a plaintext password against a stored hash.
     *
     * @param string $password Plaintext password
     * @param string $hash Stored password hash
     */
    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
}

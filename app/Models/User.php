<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class User
{
    private Database $db;
    private string $table = 'users';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findById(int $id): array|false
    {
        return $this->db->fetch("SELECT * FROM {$this->table} WHERE id = ?", [$id]);
    }

    public function findByEmail(string $email): array|false
    {
        return $this->db->fetch("SELECT * FROM {$this->table} WHERE email = ?", [$email]);
    }

    public function create(array $data): int
    {
        $data['password'] = password_hash($data['password'], PASSWORD_ARGON2ID);
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        return $this->db->insert($this->table, $data);
    }

    public function update(int $id, array $data): int
    {
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_ARGON2ID);
        }
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        return $this->db->update($this->table, $data, 'id = ?', [$id]);
    }

    public function delete(int $id): int
    {
        return $this->db->delete($this->table, 'id = ?', [$id]);
    }

    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    public function getAll(int $limit = 50, int $offset = 0): array
    {
        return $this->db->fetchAll(
            "SELECT id, name, email, role_id, created_at FROM {$this->table} ORDER BY created_at DESC LIMIT ? OFFSET ?",
            [$limit, $offset]
        );
    }
}

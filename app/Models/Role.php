<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class Role
{
    private Database $db;
    private string $table = 'roles';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findById(int $id): array|false
    {
        return $this->db->fetch("SELECT * FROM {$this->table} WHERE id = ?", [$id]);
    }

    public function findByName(string $name): array|false
    {
        return $this->db->fetch("SELECT * FROM {$this->table} WHERE name = ?", [$name]);
    }

    public function create(array $data): int
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->db->insert($this->table, $data);
    }

    public function update(int $id, array $data): int
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->update($this->table, $data, 'id = ?', [$id]);
    }

    public function delete(int $id): int
    {
        return $this->db->delete($this->table, 'id = ?', [$id]);
    }

    public function getAll(): array
    {
        return $this->db->fetchAll("SELECT * FROM {$this->table} ORDER BY name");
    }

    public function getPermissions(int $roleId): array
    {
        $role = $this->findById($roleId);
        return $role ? json_decode($role['permissions'] ?? '[]', true) : [];
    }

    public function hasPermission(int $roleId, string $permission): bool
    {
        $permissions = $this->getPermissions($roleId);
        return in_array($permission, $permissions);
    }
}

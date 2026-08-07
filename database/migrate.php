<?php

declare(strict_types=1);

/**
 * Database Migration Runner
 * Execute: php database/migrate.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

try {
    $db = \App\Core\Database::getInstance();
    $pdo = $db->getConnection();
    
    echo "Running database migrations...\n\n";
    
    $schema = require __DIR__ . '/migrations/schema.php';
    
    // Create roles table first (needed for FK reference)
    if (isset($schema['roles'])) {
        $pdo->exec($schema['roles']);
        echo "✓ Created table: roles\n";
    }
    
    foreach ($schema as $table => $sql) {
        if ($table === 'roles') continue; // Already created
        $pdo->exec($sql);
        echo "✓ Created table: {$table}\n";
    }
    
    // Seed default roles
    $existingRoles = $pdo->query("SELECT COUNT(*) FROM roles")->fetchColumn();
    if ($existingRoles == 0) {
        $defaultRoles = [
            ['name' => 'admin', 'description' => 'Full system access', 'permissions' => json_encode(['*'])],
            ['name' => 'user', 'description' => 'Standard user access', 'permissions' => json_encode(['read', 'write_own'])],
            ['name' => 'editor', 'description' => 'Content management', 'permissions' => json_encode(['read', 'write', 'publish', 'delete_own'])],
            ['name' => 'hr_manager', 'description' => 'HR module access', 'permissions' => json_encode(['read', 'hr_manage', 'hr_approve'])],
        ];
        
        foreach ($defaultRoles as $role) {
            $role['created_at'] = date('Y-m-d H:i:s');
            $role['updated_at'] = date('Y-m-d H:i:s');
            $pdo->prepare("INSERT INTO roles (name, description, permissions, created_at, updated_at) VALUES (?, ?, ?, ?, ?)")
                ->execute(array_values($role));
        }
        echo "\n✓ Seeded default roles: admin, user, editor, hr_manager\n";
    }
    
    echo "\n✅ Migration completed successfully!\n";
    
} catch (Exception $e) {
    echo "\n❌ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}

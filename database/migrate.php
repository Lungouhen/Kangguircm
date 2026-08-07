<?php

declare(strict_types=1);

/**
 * Database Migration Runner - SQLite compatible
 * Run: php database/migrate.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Environment is set by the server wrapper or .env loader

try {
    $db = \App\Core\Database::getInstance();
    $pdo = $db->getConnection();

    echo "Running database migrations (SQLite)...\n\n";

    // Create tables using SQLite-compatible syntax
    $pdo->exec("CREATE TABLE IF NOT EXISTS roles (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL UNIQUE,
        description TEXT,
        permissions TEXT DEFAULT '[]',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    echo "✓ Created table: roles\n";

    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT NOT NULL UNIQUE,
        password TEXT NOT NULL,
        role_id INTEGER,
        email_verified_at DATETIME,
        remember_token TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE SET NULL
    )");
    echo "✓ Created table: users\n";

    $pdo->exec("CREATE TABLE IF NOT EXISTS cms_categories (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        slug TEXT NOT NULL UNIQUE,
        description TEXT,
        parent_id INTEGER,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (parent_id) REFERENCES cms_categories(id) ON DELETE SET NULL
    )");
    echo "✓ Created table: cms_categories\n";

    $pdo->exec("CREATE TABLE IF NOT EXISTS cms_posts (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        slug TEXT NOT NULL UNIQUE,
        content TEXT,
        excerpt TEXT,
        featured_image TEXT,
        author_id INTEGER NOT NULL,
        category_id INTEGER,
        status TEXT DEFAULT 'draft',
        published_at DATETIME,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE
    )");
    echo "✓ Created table: cms_posts\n";

    $pdo->exec("CREATE TABLE IF NOT EXISTS cms_pages (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        slug TEXT NOT NULL UNIQUE,
        content TEXT,
        parent_id INTEGER,
        template TEXT DEFAULT 'default',
        status TEXT DEFAULT 'draft',
        sort_order INTEGER DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (parent_id) REFERENCES cms_pages(id) ON DELETE SET NULL
    )");
    echo "✓ Created table: cms_pages\n";

    $pdo->exec("CREATE TABLE IF NOT EXISTS cms_media (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        filename TEXT NOT NULL,
        original_name TEXT NOT NULL,
        mime_type TEXT NOT NULL,
        size INTEGER NOT NULL,
        path TEXT NOT NULL,
        alt_text TEXT,
        uploaded_by INTEGER NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE
    )");
    echo "✓ Created table: cms_media\n";

    $pdo->exec("CREATE TABLE IF NOT EXISTS email_subscribers (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        email TEXT NOT NULL UNIQUE,
        name TEXT,
        status TEXT DEFAULT 'active',
        subscribed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        unsubscribed_at DATETIME,
        confirmed_at DATETIME,
        confirmation_token TEXT
    )");
    echo "✓ Created table: email_subscribers\n";

    $pdo->exec("CREATE TABLE IF NOT EXISTS email_lists (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        description TEXT,
        created_by INTEGER NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
    )");
    echo "✓ Created table: email_lists\n";

    $pdo->exec("CREATE TABLE IF NOT EXISTS email_list_subscribers (
        list_id INTEGER NOT NULL,
        subscriber_id INTEGER NOT NULL,
        added_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (list_id, subscriber_id),
        FOREIGN KEY (list_id) REFERENCES email_lists(id) ON DELETE CASCADE,
        FOREIGN KEY (subscriber_id) REFERENCES email_subscribers(id) ON DELETE CASCADE
    )");
    echo "✓ Created table: email_list_subscribers\n";

    $pdo->exec("CREATE TABLE IF NOT EXISTS email_campaigns (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        subject TEXT NOT NULL,
        template TEXT NOT NULL,
        status TEXT DEFAULT 'draft',
        scheduled_at DATETIME,
        sent_at DATETIME,
        created_by INTEGER NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
    )");
    echo "✓ Created table: email_campaigns\n";

    $pdo->exec("CREATE TABLE IF NOT EXISTS email_campaign_lists (
        campaign_id INTEGER NOT NULL,
        list_id INTEGER NOT NULL,
        PRIMARY KEY (campaign_id, list_id),
        FOREIGN KEY (campaign_id) REFERENCES email_campaigns(id) ON DELETE CASCADE,
        FOREIGN KEY (list_id) REFERENCES email_lists(id) ON DELETE CASCADE
    )");
    echo "✓ Created table: email_campaign_lists\n";

    $pdo->exec("CREATE TABLE IF NOT EXISTS email_templates (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        subject TEXT NOT NULL,
        content TEXT NOT NULL,
        created_by INTEGER NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
    )");
    echo "✓ Created table: email_templates\n";

    $pdo->exec("CREATE TABLE IF NOT EXISTS hrm_employees (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        employee_code TEXT NOT NULL UNIQUE,
        department TEXT,
        designation TEXT,
        date_of_joining DATE NOT NULL,
        date_of_birth DATE,
        phone TEXT,
        address TEXT,
        emergency_contact TEXT,
        salary REAL NOT NULL,
        bank_details TEXT,
        status TEXT DEFAULT 'active',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");
    echo "✓ Created table: hrm_employees\n";

    $pdo->exec("CREATE TABLE IF NOT EXISTS hrm_attendance (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        employee_id INTEGER NOT NULL,
        date DATE NOT NULL,
        clock_in TEXT,
        clock_out TEXT,
        status TEXT DEFAULT 'present',
        notes TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (employee_id) REFERENCES hrm_employees(id) ON DELETE CASCADE,
        UNIQUE(employee_id, date)
    )");
    echo "✓ Created table: hrm_attendance\n";

    $pdo->exec("CREATE TABLE IF NOT EXISTS hrm_leaves (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        employee_id INTEGER NOT NULL,
        leave_type TEXT NOT NULL,
        start_date DATE NOT NULL,
        end_date DATE NOT NULL,
        days_count INTEGER NOT NULL,
        reason TEXT NOT NULL,
        status TEXT DEFAULT 'pending',
        approved_by INTEGER,
        approved_at DATETIME,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (employee_id) REFERENCES hrm_employees(id) ON DELETE CASCADE,
        FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
    )");
    echo "✓ Created table: hrm_leaves\n";

    $pdo->exec("CREATE TABLE IF NOT EXISTS hrm_payroll (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        employee_id INTEGER NOT NULL,
        month INTEGER NOT NULL,
        year INTEGER NOT NULL,
        basic_salary REAL NOT NULL,
        allowances REAL DEFAULT 0,
        deductions REAL DEFAULT 0,
        net_salary REAL NOT NULL,
        payment_status TEXT DEFAULT 'pending',
        payment_date DATE,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (employee_id) REFERENCES hrm_employees(id) ON DELETE CASCADE,
        UNIQUE(employee_id, month, year)
    )");
    echo "✓ Created table: hrm_payroll\n";

    // Indexes
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_posts_slug ON cms_posts(slug)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_posts_status ON cms_posts(status)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_subscribers_status ON email_subscribers(status)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_campaigns_status ON email_campaigns(status)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_employees_status ON hrm_employees(status)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_attendance_date ON hrm_attendance(date)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_leaves_status ON hrm_leaves(status)");
    echo "✓ Created indexes\n";

    // Seed default roles
    $roleCount = (int) $pdo->query("SELECT COUNT(*) FROM roles")->fetchColumn();
    if ($roleCount === 0) {
        $insert = $pdo->prepare("INSERT INTO roles (name, description, permissions, created_at, updated_at) VALUES (?, ?, ?, ?, ?)");
        $now = date('Y-m-d H:i:s');
        $insert->execute(['admin', 'Full system access', '["*"]', $now, $now]);
        $insert->execute(['user', 'Standard user', '["read","write_own"]', $now, $now]);
        $insert->execute(['editor', 'Content management', '["read","write","publish"]', $now, $now]);
        $insert->execute(['hr_manager', 'HR module', '["read","hr_manage","hr_approve"]', $now, $now]);
        echo "\n✓ Seeded 4 default roles\n";

        // Create admin user (password: admin123)
        $hash = password_hash('admin123', PASSWORD_BCRYPT);
        $pdo->prepare("INSERT INTO users (name, email, password, role_id, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?)")
            ->execute(['Admin User', 'admin@example.com', $hash, 1, $now, $now]);
        echo "✓ Created admin user (admin@example.com / admin123)\n";

        // Seed categories
        $catInsert = $pdo->prepare("INSERT INTO cms_categories (name, slug, created_at) VALUES (?, ?, ?)");
        $catInsert->execute(['General', 'general', $now]);
        $catInsert->execute(['Technology', 'technology', $now]);
        $catInsert->execute(['News', 'news', $now]);
        echo "✓ Seeded 3 categories\n";
    }

    echo "\n✅ Migration completed successfully!\n";

} catch (Exception $e) {
    echo "\n❌ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}

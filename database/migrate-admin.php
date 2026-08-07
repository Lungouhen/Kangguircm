<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$_ENV['DB_DRIVER'] = 'sqlite';
$_ENV['DB_PATH'] = 'database/kangguircm.sqlite';
putenv('DB_DRIVER=sqlite');
putenv('DB_PATH=database/kangguircm.sqlite');

$db = new PDO('sqlite:database/kangguircm.sqlite');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Create audit_logs
$db->exec('CREATE TABLE IF NOT EXISTS audit_logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    action TEXT NOT NULL,
    entity TEXT,
    entity_id INTEGER,
    old_values TEXT,
    new_values TEXT,
    ip_address TEXT,
    user_agent TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)');
echo "✓ Created audit_logs\n";

// Create system_settings
$db->exec('CREATE TABLE IF NOT EXISTS system_settings (
    key TEXT PRIMARY KEY,
    value TEXT,
    description TEXT,
    group_name TEXT DEFAULT "general",
    is_public INTEGER DEFAULT 0,
    updated_by INTEGER,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
)');
echo "✓ Created system_settings\n";

// Create notifications
$db->exec('CREATE TABLE IF NOT EXISTS notifications (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    type TEXT NOT NULL,
    title TEXT NOT NULL,
    message TEXT NOT NULL,
    data TEXT,
    is_read INTEGER DEFAULT 0,
    read_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)');
echo "✓ Created notifications\n";

// Create activity_sessions
$db->exec('CREATE TABLE IF NOT EXISTS activity_sessions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    path TEXT NOT NULL,
    method TEXT DEFAULT "GET",
    duration_ms INTEGER,
    ip_address TEXT,
    user_agent TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)');
echo "✓ Created activity_sessions\n";

// Create indexes
$db->exec('CREATE INDEX IF NOT EXISTS idx_audit_user ON audit_logs(user_id)');
$db->exec('CREATE INDEX IF NOT EXISTS idx_audit_action ON audit_logs(action)');
$db->exec('CREATE INDEX IF NOT EXISTS idx_audit_entity ON audit_logs(entity, entity_id)');
$db->exec('CREATE INDEX IF NOT EXISTS idx_notifications_user ON notifications(user_id, is_read)');
$db->exec('CREATE INDEX IF NOT EXISTS idx_activity_path ON activity_sessions(path)');
echo "✓ Created indexes\n";

// Seed default settings
$stmt = $db->prepare('INSERT OR IGNORE INTO system_settings (key, value, description, group_name) VALUES (?, ?, ?, ?)');
$settings = [
    ['app_name', 'Multi-Module Platform', 'Application name', 'general'],
    ['app_locale', 'en', 'Default locale', 'general'],
    ['auth_session_timeout', '120', 'Session timeout in minutes', 'auth'],
    ['auth_max_login_attempts', '5', 'Max login attempts before lockout', 'auth'],
    ['cms_posts_per_page', '20', 'Default posts per page', 'cms'],
    ['hrm_work_hours_per_day', '8', 'Standard work hours', 'hrm'],
];
foreach ($settings as $s) {
    $stmt->execute($s);
}
echo "✓ Seeded " . count($settings) . " settings\n";

echo "\n✅ Admin schema migration complete!\n";

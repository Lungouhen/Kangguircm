-- Admin Panel: Additional tables for admin functionality
-- Following database-optimizer skill: proper indexes, FK constraints

-- Audit logs table (tracks all admin actions)
CREATE TABLE IF NOT EXISTS audit_logs (
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
);
CREATE INDEX IF NOT EXISTS idx_audit_user ON audit_logs(user_id);
CREATE INDEX IF NOT EXISTS idx_audit_action ON audit_logs(action);
CREATE INDEX IF NOT EXISTS idx_audit_entity ON audit_logs(entity, entity_id);
CREATE INDEX IF NOT EXISTS idx_audit_created ON audit_logs(created_at);

-- System settings table (key-value configuration)
CREATE TABLE IF NOT EXISTS system_settings (
    key TEXT PRIMARY KEY,
    value TEXT,
    description TEXT,
    group_name TEXT DEFAULT 'general',
    is_public INTEGER DEFAULT 0,
    updated_by INTEGER,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
);
CREATE INDEX IF NOT EXISTS idx_settings_group ON system_settings(group_name);

-- Notifications table
CREATE TABLE IF NOT EXISTS notifications (
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
);
CREATE INDEX IF NOT EXISTS idx_notifications_user ON notifications(user_id, is_read);
CREATE INDEX IF NOT EXISTS idx_notifications_type ON notifications(type);
CREATE INDEX IF NOT EXISTS idx_notifications_created ON notifications(created_at);

-- Activity sessions (for analytics)
CREATE TABLE IF NOT EXISTS activity_sessions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    path TEXT NOT NULL,
    method TEXT DEFAULT 'GET',
    duration_ms INTEGER,
    ip_address TEXT,
    user_agent TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
CREATE INDEX IF NOT EXISTS idx_activity_user ON activity_sessions(user_id);
CREATE INDEX IF NOT EXISTS idx_activity_path ON activity_sessions(path);
CREATE INDEX IF NOT EXISTS idx_activity_created ON activity_sessions(created_at);

-- Insert default system settings
INSERT OR IGNORE INTO system_settings (key, value, description, group_name) VALUES
    ('app_name', 'Multi-Module Platform', 'Application name', 'general'),
    ('app_locale', 'en', 'Default locale', 'general'),
    ('app_timezone', 'UTC', 'Default timezone', 'general'),
    ('auth_session_timeout', '120', 'Session timeout in minutes', 'auth'),
    ('auth_max_login_attempts', '5', 'Max login attempts before lockout', 'auth'),
    ('auth_lockout_duration', '60', 'Lockout duration in minutes', 'auth'),
    ('cms_posts_per_page', '20', 'Default posts per page', 'cms'),
    ('cms_allow_comments', '1', 'Allow comments on posts (0/1)', 'cms'),
    ('email_from_name', 'Platform', 'Email sender name', 'email'),
    ('email_from_address', 'noreply@example.com', 'Email sender address', 'email'),
    ('hrm_work_hours_per_day', '8', 'Standard work hours', 'hrm'),
    ('hrm_work_days_per_week', '5', 'Standard work days', 'hrm');

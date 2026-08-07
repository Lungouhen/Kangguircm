<?php
/**
 * CMS Module Schema Upgrade
 * 
 * Following database-optimizer skill:
 * - Proper indexes on hot columns
 * - Foreign key constraints
 * - Normalized design
 * - No SELECT * anti-pattern
 * 
 * Following backend-developer skill:
 * - RESTful-ready schema
 * - Pagination support
 * - Audit trail via revisions
 * - Caching-friendly structure
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$_ENV['DB_DRIVER'] = 'sqlite';
$_ENV['DB_PATH'] = 'database/kangguircm.sqlite';
putenv('DB_DRIVER=sqlite');
putenv('DB_PATH=database/kangguircm.sqlite');

$db = new PDO('sqlite:database/kangguircm.sqlite');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

echo "=== CMS Module Schema Upgrade ===\n\n";

// 1. Upgrade cms_posts with SEO, analytics, workflow
echo "1. Upgrading cms_posts...\n";
$db->exec("ALTER TABLE cms_posts ADD COLUMN meta_title TEXT");
echo "   ✓ Added meta_title\n";

$db->exec("ALTER TABLE cms_posts ADD COLUMN meta_description TEXT");
echo "   ✓ Added meta_description\n";

$db->exec("ALTER TABLE cms_posts ADD COLUMN og_image TEXT");
echo "   ✓ Added og_image\n";

$db->exec("ALTER TABLE cms_posts ADD COLUMN view_count INTEGER DEFAULT 0");
echo "   ✓ Added view_count\n";

$db->exec("ALTER TABLE cms_posts ADD COLUMN featured INTEGER DEFAULT 0");
echo "   ✓ Added featured\n";

$db->exec("ALTER TABLE cms_posts ADD COLUMN reading_time INTEGER DEFAULT 0");
echo "   ✓ Added reading_time\n";

$db->exec("ALTER TABLE cms_posts ADD COLUMN review_status TEXT DEFAULT 'draft'");
echo "   ✓ Added review_status (draft → in_review → approved → published)\n";

$db->exec("ALTER TABLE cms_posts ADD COLUMN reviewed_by INTEGER");
echo "   ✓ Added reviewed_by\n";

$db->exec("ALTER TABLE cms_posts ADD COLUMN reviewed_at DATETIME");
echo "   ✓ Added reviewed_at\n";

$db->exec("ALTER TABLE cms_posts ADD COLUMN allow_comments INTEGER DEFAULT 1");
echo "   ✓ Added allow_comments\n";

$db->exec("ALTER TABLE cms_posts ADD COLUMN comment_count INTEGER DEFAULT 0");
echo "   ✓ Added comment_count\n";

// Indexes for cms_posts
$db->exec("CREATE INDEX IF NOT EXISTS idx_posts_status ON cms_posts(status)");
echo "   ✓ Indexed status\n";

$db->exec("CREATE INDEX IF NOT EXISTS idx_posts_review_status ON cms_posts(review_status)");
echo "   ✓ Indexed review_status\n";

$db->exec("CREATE INDEX IF NOT EXISTS idx_posts_author ON cms_posts(author_id)");
echo "   ✓ Indexed author_id\n";

$db->exec("CREATE INDEX IF NOT EXISTS idx_posts_featured ON cms_posts(featured)");
echo "   ✓ Indexed featured\n";

$db->exec("CREATE INDEX IF NOT EXISTS idx_posts_published_at ON cms_posts(published_at)");
echo "   ✓ Indexed published_at\n";

// 2. Create cms_tags
echo "\n2. Creating cms_tags...\n";
$db->exec("CREATE TABLE IF NOT EXISTS cms_tags (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    slug TEXT NOT NULL UNIQUE,
    description TEXT,
    color TEXT DEFAULT '#3b82f6',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");
echo "   ✓ Created cms_tags\n";

$db->exec("CREATE INDEX IF NOT EXISTS idx_tags_slug ON cms_tags(slug)");
$db->exec("CREATE INDEX IF NOT EXISTS idx_tags_name ON cms_tags(name)");
echo "   ✓ Added indexes\n";

// Seed default tags
$stmt = $db->prepare("INSERT OR IGNORE INTO cms_tags (name, slug, description) VALUES (?, ?, ?)");
$tags = [
    ['Technology', 'technology', 'Tech-related posts'],
    ['Tutorial', 'tutorial', 'How-to guides'],
    ['News', 'news', 'Latest updates'],
    ['Announcement', 'announcement', 'Platform announcements'],
    ['Feature', 'feature', 'New features'],
    ['Bug Fix', 'bug-fix', 'Bug fixes and patches'],
];
foreach ($tags as $t) {
    $stmt->execute($t);
}
echo "   ✓ Seeded 6 default tags\n";

// 3. Create cms_post_tags (many-to-many)
echo "\n3. Creating cms_post_tags...\n";
$db->exec("CREATE TABLE IF NOT EXISTS cms_post_tags (
    post_id INTEGER NOT NULL,
    tag_id INTEGER NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (post_id, tag_id),
    FOREIGN KEY (post_id) REFERENCES cms_posts(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES cms_tags(id) ON DELETE CASCADE
)");
echo "   ✓ Created cms_post_tags\n";

$db->exec("CREATE INDEX IF NOT EXISTS idx_post_tags_post ON cms_post_tags(post_id)");
$db->exec("CREATE INDEX IF NOT EXISTS idx_post_tags_tag ON cms_post_tags(tag_id)");
echo "   ✓ Added indexes\n";

// 4. Create cms_revisions
echo "\n4. Creating cms_revisions...\n";
$db->exec("CREATE TABLE IF NOT EXISTS cms_revisions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    post_id INTEGER NOT NULL,
    title TEXT NOT NULL,
    content TEXT,
    excerpt TEXT,
    status TEXT,
    author_id INTEGER NOT NULL,
    change_summary TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES cms_posts(id) ON DELETE CASCADE,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE
)");
echo "   ✓ Created cms_revisions\n";

$db->exec("CREATE INDEX IF NOT EXISTS idx_revisions_post ON cms_revisions(post_id)");
$db->exec("CREATE INDEX IF NOT EXISTS idx_revisions_author ON cms_revisions(author_id)");
$db->exec("CREATE INDEX IF NOT EXISTS idx_revisions_created ON cms_revisions(created_at)");
echo "   ✓ Added indexes\n";

// 5. Create cms_comments
echo "\n5. Creating cms_comments...\n";
$db->exec("CREATE TABLE IF NOT EXISTS cms_comments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    post_id INTEGER NOT NULL,
    user_id INTEGER,
    author_name TEXT,
    author_email TEXT,
    author_ip TEXT,
    content TEXT NOT NULL,
    status TEXT DEFAULT 'pending',
    approved_by INTEGER,
    approved_at DATETIME,
    parent_id INTEGER,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES cms_posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (parent_id) REFERENCES cms_comments(id) ON DELETE CASCADE
)");
echo "   ✓ Created cms_comments\n";

$db->exec("CREATE INDEX IF NOT EXISTS idx_comments_post ON cms_comments(post_id)");
$db->exec("CREATE INDEX IF NOT EXISTS idx_comments_user ON cms_comments(user_id)");
$db->exec("CREATE INDEX IF NOT EXISTS idx_comments_status ON cms_comments(status)");
$db->exec("CREATE INDEX IF NOT EXISTS idx_comments_parent ON cms_comments(parent_id)");
$db->exec("CREATE INDEX IF NOT EXISTS idx_comments_created ON cms_comments(created_at)");
echo "   ✓ Added indexes\n";

// 6. Create cms_post_views (analytics)
echo "\n6. Creating cms_post_views...\n";
$db->exec("CREATE TABLE IF NOT EXISTS cms_post_views (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    post_id INTEGER NOT NULL,
    ip_address TEXT,
    user_agent TEXT,
    referrer TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES cms_posts(id) ON DELETE CASCADE
)");
echo "   ✓ Created cms_post_views\n";

$db->exec("CREATE INDEX IF NOT EXISTS idx_views_post ON cms_post_views(post_id)");
$db->exec("CREATE INDEX IF NOT EXISTS idx_views_created ON cms_post_views(created_at)");
echo "   ✓ Added indexes\n";

// 7. Create cms_pages_seo (SEO metadata for pages)
echo "\n7. Upgrading cms_pages with SEO fields...\n";
try {
    $db->exec("ALTER TABLE cms_pages ADD COLUMN meta_title TEXT");
    echo "   ✓ Added meta_title\n";
} catch (Exception $e) { echo "   - meta_title already exists\n"; }

try {
    $db->exec("ALTER TABLE cms_pages ADD COLUMN meta_description TEXT");
    echo "   ✓ Added meta_description\n";
} catch (Exception $e) { echo "   - meta_description already exists\n"; }

try {
    $db->exec("ALTER TABLE cms_pages ADD COLUMN og_image TEXT");
    echo "   ✓ Added og_image\n";
} catch (Exception $e) { echo "   - og_image already exists\n"; }

$db->exec("CREATE INDEX IF NOT EXISTS idx_pages_status ON cms_pages(status)");
$db->exec("CREATE INDEX IF NOT EXISTS idx_pages_parent ON cms_pages(parent_id)");
echo "   ✓ Added indexes\n";

echo "\n✅ CMS Module Schema Upgrade Complete!\n";
echo "\nNew tables: cms_tags, cms_post_tags, cms_revisions, cms_comments, cms_post_views\n";
echo "Enhanced tables: cms_posts (11 new columns), cms_pages (3 new columns)\n";
echo "Total new indexes: 17\n";

<?php
/**
 * Al Omran CMS - SQLite Setup Script
 * Run this once: php setup.php
 * Then start: php -S localhost:8000
 */

$base = dirname(__DIR__);
$db_path = $base . '/database/cms.sqlite';

echo "============================================\n";
echo "  Al Omran Blog CMS - Setup\n";
echo "============================================\n\n";

// Check for SQLite extension
if (!extension_loaded('pdo_sqlite')) {
    echo "ERROR: pdo_sqlite extension is required.\n";
    echo "Enable it in php.ini: extension=pdo_sqlite\n";
    exit(1);
}

echo "[1/3] Creating SQLite database at: {$db_path}\n";

$pdo = new PDO('sqlite:' . $db_path);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec('PRAGMA journal_mode=WAL');
$pdo->exec('PRAGMA foreign_keys=ON');

echo "[2/3] Creating tables...\n";

$pdo->exec("
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    role TEXT NOT NULL DEFAULT 'editor',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

$pdo->exec("
CREATE TABLE IF NOT EXISTS articles (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    status TEXT NOT NULL DEFAULT 'draft',
    author_id INTEGER NOT NULL,
    featured_image_id INTEGER DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    published_at DATETIME DEFAULT NULL,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE
)");

$pdo->exec("
CREATE TABLE IF NOT EXISTS article_translations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    article_id INTEGER NOT NULL,
    language TEXT NOT NULL DEFAULT 'en',
    title TEXT NOT NULL DEFAULT '',
    slug TEXT NOT NULL DEFAULT '',
    excerpt TEXT,
    content TEXT,
    seo_title TEXT,
    meta_description TEXT,
    canonical_url TEXT,
    index_status TEXT DEFAULT 'index',
    follow_status TEXT DEFAULT 'follow',
    focus_keyword TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(article_id, language),
    UNIQUE(slug, language),
    FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE
)");

$pdo->exec("
CREATE TABLE IF NOT EXISTS seo_metadata (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    article_id INTEGER NOT NULL,
    language TEXT NOT NULL DEFAULT 'en',
    seo_title TEXT,
    meta_description TEXT,
    focus_keyword TEXT,
    canonical_url TEXT,
    index_status TEXT DEFAULT 'index',
    follow_status TEXT DEFAULT 'follow',
    og_title TEXT,
    og_description TEXT,
    og_image TEXT,
    twitter_title TEXT,
    twitter_description TEXT,
    twitter_image TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(article_id, language),
    FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE
)");

$pdo->exec("
CREATE TABLE IF NOT EXISTS keywords (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    article_id INTEGER NOT NULL,
    language TEXT NOT NULL DEFAULT 'en',
    keyword TEXT NOT NULL,
    type TEXT NOT NULL DEFAULT 'secondary',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE
)");

$pdo->exec("
CREATE TABLE IF NOT EXISTS media (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    filename TEXT NOT NULL,
    original_filename TEXT NOT NULL,
    path TEXT NOT NULL,
    mime_type TEXT NOT NULL,
    size INTEGER NOT NULL DEFAULT 0,
    width INTEGER DEFAULT NULL,
    height INTEGER DEFAULT NULL,
    alt_text_en TEXT,
    alt_text_ar TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

$pdo->exec("
CREATE TABLE IF NOT EXISTS slug_history (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    article_id INTEGER NOT NULL,
    language TEXT NOT NULL,
    old_slug TEXT NOT NULL,
    new_slug TEXT NOT NULL,
    changed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE
)");

$pdo->exec("
CREATE TABLE IF NOT EXISTS redirects (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    language TEXT NOT NULL,
    old_path TEXT NOT NULL,
    new_path TEXT NOT NULL,
    status_code INTEGER NOT NULL DEFAULT 301,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

$pdo->exec("
CREATE TABLE IF NOT EXISTS careers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    status TEXT NOT NULL DEFAULT 'draft',
    author_id INTEGER NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    published_at DATETIME DEFAULT NULL,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE
)");

$pdo->exec("
CREATE TABLE IF NOT EXISTS career_translations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    career_id INTEGER NOT NULL,
    language TEXT NOT NULL DEFAULT 'en',
    title TEXT NOT NULL DEFAULT '',
    slug TEXT NOT NULL DEFAULT '',
    location TEXT,
    employment_type TEXT DEFAULT 'full-time',
    description TEXT,
    requirements TEXT,
    benefits TEXT,
    seo_title TEXT,
    meta_description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(career_id, language),
    UNIQUE(slug, language),
    FOREIGN KEY (career_id) REFERENCES careers(id) ON DELETE CASCADE
)");

$pdo->exec("
CREATE TABLE IF NOT EXISTS settings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    setting_key TEXT NOT NULL UNIQUE,
    setting_value TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

$pdo->exec("
CREATE TABLE IF NOT EXISTS analytics_events (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    article_id INTEGER DEFAULT NULL,
    career_id INTEGER DEFAULT NULL,
    event_type TEXT NOT NULL DEFAULT 'view',
    language TEXT DEFAULT NULL,
    ip_address TEXT,
    user_agent TEXT,
    referrer TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

echo "[3/3] Inserting seed data...\n";

// Admin user - username: admin, password: admin
$hash = password_hash('admin', PASSWORD_DEFAULT);
$pdo->prepare("INSERT OR IGNORE INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)")
    ->execute(['Admin', 'admin', $hash, 'admin']);

// Settings
$settings = [
    ['site_name', 'Al Omran Training & Development Center'],
    ['default_language', 'en'],
    ['supported_languages', 'en,ar'],
    ['default_index_status', 'index'],
    ['default_follow_status', 'follow'],
    ['default_og_image', ''],
    ['site_url', 'http://localhost:8000'],
    ['blog_path_en', '/en/blog'],
    ['blog_path_ar', '/blog'],
];
$set_stmt = $pdo->prepare("INSERT OR IGNORE INTO settings (setting_key, setting_value) VALUES (?, ?)");
foreach ($settings as $s) {
    $set_stmt->execute($s);
}

// Sample articles
$pdo->exec("INSERT INTO articles (status, author_id, published_at) VALUES ('published', 1, datetime('now'))");
$pdo->exec("INSERT INTO articles (status, author_id, published_at) VALUES ('draft', 1, datetime('now'))");

$art_trans = $pdo->prepare("INSERT OR IGNORE INTO article_translations (article_id, language, title, slug, excerpt, content, seo_title, meta_description, focus_keyword) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$art_trans->execute([1, 'en', 'Welcome to Al Omran Training Center', 'welcome-to-al-omran', 'Discover our comprehensive training programs.', '<h1>Welcome to Al Omran</h1><p>We offer world-class training programs in the UAE.</p>', 'Al Omran Training Center | Professional Training UAE', 'Al Omran Training Center offers certified professional training programs in the UAE.', 'training']);
$art_trans->execute([1, 'ar', 'مرحباً بكم في مركز العمران', 'مرحبا-بكم-في-مركز-العمران', 'اكتشف برامجنا التدريبية الشاملة.', '<h1>مرحباً بكم في مركز العمران</h1><p>نقدم برامج تدريبية عالمية المستوى في الإمارات.</p>', 'مركز العمران للتدريب | تدريب مهني في الإمارات', 'مركز العمران للتدريب يقدم برامج تدريبية معتمدة في الإمارات.', 'تدريب']);
$art_trans->execute([2, 'en', 'Getting Started with Digital Marketing', 'getting-started-digital-marketing', 'A beginner guide to digital marketing.', '<h1>Digital Marketing Guide</h1><p>Learn the basics of digital marketing.</p>', 'Digital Marketing Guide | Al Omran', 'Learn digital marketing basics with Al Omran Training Center.', 'digital marketing']);

// Sample career
$pdo->exec("INSERT INTO careers (status, author_id, published_at) VALUES ('published', 1, datetime('now'))");
$career_trans = $pdo->prepare("INSERT OR IGNORE INTO career_translations (career_id, language, title, slug, location, employment_type, description, requirements, benefits) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$career_trans->execute([1, 'en', 'Training Coordinator', 'training-coordinator', 'Al Ain, UAE', 'full-time', '<p>We are looking for a Training Coordinator.</p>', '<ul><li>2+ years experience</li><li>Bachelor degree</li></ul>', '<ul><li>Competitive salary</li><li>Health insurance</li></ul>']);

echo "\n============================================\n";
echo "  Setup Complete!\n";
echo "============================================\n\n";
echo "Database: {$db_path}\n\n";
echo "To start the server:\n";
echo "  cd admin\n";
echo "  php -S localhost:8000\n\n";
echo "Then open: http://localhost:8000/login.php\n\n";
echo "Login credentials:\n";
echo "  Username: admin\n";
echo "  Password: admin\n\n";

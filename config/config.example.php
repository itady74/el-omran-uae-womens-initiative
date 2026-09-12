<?php
/**
 * Al Omran Blog CMS - Configuration
 * Copy this file to config.php and update the values
 */

// Database
define('DB_HOST', 'localhost');
define('DB_NAME', 'alomran_cms');
define('DB_USER', 'your_db_user');
define('DB_PASS', 'your_db_password');
define('DB_CHARSET', 'utf8mb4');

// Application
define('APP_URL', 'https://alomran.ae/admin');
define('APP_NAME', 'Al Omran Blog CMS');
define('UPLOAD_PATH', __DIR__ . '/../uploads');
define('UPLOAD_URL', '/uploads');

// Security
define('CSRF_TOKEN_NAME', '_csrf_token');
define('SESSION_NAME', 'alomran_cms');
define('SESSION_LIFETIME', 7200); // 2 hours

// Upload limits
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp']);

// SEO defaults
define('DEFAULT_LANGUAGE', 'en');
define('SUPPORTED_LANGUAGES', ['en', 'ar']);
define('SEO_TITLE_MAX', 60);
define('SEO_DESC_MAX', 160);

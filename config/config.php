<?php
/**
 * Al Omran Blog CMS - Configuration
 */

// Database
define('DB_HOST', 'localhost');
define('DB_NAME', 'alomran_cms');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Application
define('APP_URL', 'http://localhost:8000/admin');
define('APP_NAME', 'Al Omran Blog CMS');
define('UPLOAD_PATH', dirname(__DIR__) . '/uploads');
define('UPLOAD_URL', 'http://localhost:8000/uploads');

// Security
define('CSRF_TOKEN_NAME', '_csrf_token');
define('SESSION_NAME', 'alomran_cms');
define('SESSION_LIFETIME', 7200);

// Upload limits
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024);
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp']);

// SEO defaults
define('DEFAULT_LANGUAGE', 'en');
define('SUPPORTED_LANGUAGES', ['en', 'ar']);
define('SEO_TITLE_MAX', 60);
define('SEO_DESC_MAX', 160);

<?php
/**
 * PHP Built-in Server Router
 * Usage: php -S localhost:8000 router.php
 */
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Serve existing static files directly (CSS, JS, images, etc.)
if ($uri !== '/' && file_exists(__DIR__ . $uri) && !preg_match('/\.php$/', $uri)) {
    return false;
}

// Append index.php to directory requests
if ($uri !== '/' && is_dir(__DIR__ . $uri)) {
    $uri = rtrim($uri, '/') . '/index.php';
}

// Serve PHP files
if (file_exists(__DIR__ . $uri)) {
    require __DIR__ . $uri;
    return true;
}

// 404
http_response_code(404);
echo '<h1>404 - Not Found</h1>';
return true;

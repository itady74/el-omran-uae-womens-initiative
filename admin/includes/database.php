<?php
/**
 * Database connection via PDO singleton
 * Supports MySQL and SQLite (for local development without MySQL)
 */

require_once __DIR__ . '/../../config/config.php';

function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        // Check for SQLite database first (for local dev without MySQL)
        $sqlite_path = dirname(__DIR__, 2) . '/database/cms.sqlite';

        if (file_exists($sqlite_path) && class_exists('PDO')) {
            $pdo = new PDO('sqlite:' . $sqlite_path, null, null, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            $pdo->exec('PRAGMA journal_mode=WAL');
            $pdo->exec('PRAGMA foreign_keys=ON');
        } else {
            // Fallback to MySQL
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        }
    }
    return $pdo;
}

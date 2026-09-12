<?php
/**
 * Authentication helpers
 */

require_once __DIR__ . '/database.php';

function start_session_safe(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_name(SESSION_NAME);
        session_set_cookie_params([
            'lifetime' => SESSION_LIFETIME,
            'path'     => '/',
            'httponly'  => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }
}

function login(string $email, string $password): bool
{
    $stmt = db()->prepare('SELECT id, name, email, password_hash, role FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        start_session_safe();
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        return true;
    }
    return false;
}

function logout(): void
{
    start_session_safe();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

function is_logged_in(): bool
{
    start_session_safe();
    return isset($_SESSION['user_id']);
}

function current_user(): ?array
{
    start_session_safe();
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    return [
        'id'    => $_SESSION['user_id'],
        'name'  => $_SESSION['user_name'] ?? '',
        'email' => $_SESSION['user_email'] ?? '',
        'role'  => $_SESSION['user_role'] ?? 'editor',
    ];
}

function require_auth(): void
{
    if (!is_logged_in()) {
        header('Location: ' . APP_URL . '/login.php');
        exit;
    }
}

function is_admin(): bool
{
    $u = current_user();
    return $u && $u['role'] === 'admin';
}

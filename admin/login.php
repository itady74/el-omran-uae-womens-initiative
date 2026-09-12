<?php
require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    redirect(APP_URL);
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Rate limiting
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    if (!check_rate_limit('login_' . $ip, 5, 300)) {
        $error = 'Too many failed attempts. Please try again in 5 minutes.';
    } elseif (!verify_csrf()) {
        $error = 'Invalid request.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        // Support both username and email login
        $stmt = db()->prepare('SELECT id, name, email, password_hash, role FROM users WHERE email = ? OR name = ? LIMIT 1');
        $stmt->execute([$email, $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            clear_rate_limit('login_' . $ip);
            start_session_safe();
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            redirect(APP_URL);
        } else {
            $error = 'Invalid username or password.';
        }
    }
}

$lang = require __DIR__ . '/includes/lang/en.php';
$_t = fn(string $k) => $lang[$k] ?? $k;
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= e(APP_NAME) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/admin.css">
    <style>
        body { display: flex; align-items: center; justify-content: center; min-height: 100vh; background: var(--bg); }
        .login-card { width: 100%; max-width: 400px; padding: var(--space-2xl); }
        .login-header { text-align: center; margin-bottom: var(--space-2xl); }
        .login-header svg { margin-bottom: var(--space-md); color: var(--accent); }
        .login-header h1 { font-size: 1.25rem; font-weight: 700; margin-bottom: 4px; }
        .login-header p { color: var(--text-secondary); font-size: 0.875rem; }
        .login-error { background: var(--danger-bg); color: var(--danger); border: 1px solid var(--danger-border); padding: 10px 14px; border-radius: var(--radius-md); font-size: 0.875rem; margin-bottom: var(--space-lg); }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
            <h1><?= e(APP_NAME) ?></h1>
            <p><?= $_t('login_desc') ?></p>
        </div>

        <?php if ($error): ?>
            <div class="login-error"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <?= csrf_field() ?>
            <div class="form-group">
                <label class="form-label" for="email">Username</label>
                <input type="text" id="email" name="email" class="form-input" required autofocus placeholder="admin">
            </div>
            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input type="password" id="password" name="password" class="form-input" required placeholder="admin">
            </div>
            <div class="form-group">
                <label class="form-checkbox">
                    <input type="checkbox" name="remember" value="1">
                    <span><?= $_t('remember_me') ?></span>
                </label>
            </div>
            <button type="submit" class="btn btn-primary btn-lg" style="width:100%"><?= $_t('login') ?></button>
        </form>
    </div>
</body>
</html>

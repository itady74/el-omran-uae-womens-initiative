<?php
require_once __DIR__ . '/../includes/functions.php';
require_auth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(APP_URL . '/articles/');
}

$id = (int)($_POST['id'] ?? 0);

if ($id <= 0 || !verify_csrf()) {
    redirect(APP_URL . '/articles/');
}

// Delete article and translations
db()->prepare('DELETE FROM articles WHERE id = ?')->execute([$id]);

redirect(APP_URL . '/articles/?deleted=1');

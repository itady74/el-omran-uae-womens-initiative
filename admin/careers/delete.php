<?php
require_once __DIR__ . '/../includes/functions.php';
require_auth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(APP_URL . '/careers/');
}

$id = (int)($_POST['id'] ?? 0);

if ($id <= 0 || !verify_csrf()) {
    redirect(APP_URL . '/careers/');
}

db()->prepare('DELETE FROM careers WHERE id = ?')->execute([$id]);
redirect(APP_URL . '/careers/?deleted=1');

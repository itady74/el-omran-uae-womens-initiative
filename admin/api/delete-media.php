<?php
/**
 * Media Delete API
 * DELETE /admin/api/media.php?id=X
 * or POST with delete_id
 */
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_auth();

header('Content-Type: application/json; charset=utf-8');

$id = (int)($_GET['id'] ?? $_POST['delete_id'] ?? 0);
if ($id <= 0) json_error('Invalid ID.');

$stmt = db()->prepare('SELECT path FROM media WHERE id = ?');
$stmt->execute([$id]);
$media = $stmt->fetch();

if ($media) {
    delete_file($media['path']);
    db()->prepare('DELETE FROM media WHERE id = ?')->execute([$id]);
    json_response(['success' => true]);
} else {
    json_error('Media not found.', 404);
}

<?php
/**
 * Public API - Media
 * GET /admin/api/media.php?action=list&page=1
 */
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? 'list';

try {
    $page = max(1, (int)($_GET['page'] ?? 1));
    $per_page = min(100, max(1, (int)($_GET['per_page'] ?? 24)));
    $offset = ($page - 1) * $per_page;

    $total = (int) db()->query('SELECT COUNT(*) FROM media')->fetchColumn();

    $stmt = db()->prepare("SELECT id, filename, original_filename, path, mime_type, size, width, height, alt_text_en, alt_text_ar, created_at FROM media ORDER BY created_at DESC LIMIT {$per_page} OFFSET {$offset}");
    $stmt->execute();
    $media = $stmt->fetchAll();

    $result = [];
    foreach ($media as $m) {
        $result[] = [
            'id'               => (int) $m['id'],
            'filename'         => $m['filename'],
            'original_filename'=> $m['original_filename'],
            'path'             => $m['path'],
            'url'              => UPLOAD_URL . '/' . $m['path'],
            'mime_type'        => $m['mime_type'],
            'size'             => (int) $m['size'],
            'width'            => $m['width'] ? (int) $m['width'] : null,
            'height'           => $m['height'] ? (int) $m['height'] : null,
            'alt_text_en'      => $m['alt_text_en'],
            'alt_text_ar'      => $m['alt_text_ar'],
            'created_at'       => $m['created_at'],
        ];
    }

    json_response([
        'data'  => $result,
        'total' => $total,
        'page'  => $page,
        'per_page' => $per_page,
    ]);
} catch (Exception $ex) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}

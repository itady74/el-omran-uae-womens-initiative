<?php
/**
 * Media Upload API
 * POST /admin/api/media.php?action=upload
 */
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_auth();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('POST required.', 405);
}

if (!isset($_FILES['file'])) {
    json_error('No file uploaded.');
}

$upload = upload_image('file', 'articles');
if ($upload) {
    json_response(['success' => true, 'data' => $upload]);
} else {
    json_error('Upload failed.', 500);
}

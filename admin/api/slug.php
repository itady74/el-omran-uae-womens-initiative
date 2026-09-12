<?php
/**
 * Slug API
 * POST /admin/api/slug.php  { title, lang }
 * GET /admin/api/slug.php?check=slug&lang=en
 */
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $title = trim($data['title'] ?? '');
        $lang = $data['lang'] ?? 'en';
        $exclude_id = (int)($data['exclude_id'] ?? 0);
        $type = $data['type'] ?? 'article';

        if (empty($title)) json_error('Title is required.');

        $slug = slugify($title, $lang);
        $slug = unique_slug($slug, $lang, $exclude_id, $type);

        json_response(['slug' => $slug]);
    }

    // Check slug uniqueness
    $slug_check = $_GET['check'] ?? '';
    $lang = $_GET['lang'] ?? 'en';
    $type = $_GET['type'] ?? 'article';

    if (!empty($slug_check)) {
        $table = $type === 'career' ? 'career_translations' : 'article_translations';
        $slug_col = $type === 'career' ? 'slug' : 'slug';
        $stmt = db()->prepare("SELECT id FROM {$table} WHERE {$slug_col} = ? AND language = ?");
        $stmt->execute([$slug_check, $lang]);
        $exists = (bool) $stmt->fetch();
        json_response(['slug' => $slug_check, 'exists' => $exists]);
    }

    json_error('Invalid request.');
} catch (Exception $ex) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}

<?php
/**
 * Public API - SEO Score
 * POST /admin/api/seo-score.php  { article_id, lang }
 */
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

$article_id = (int)($_GET['article_id'] ?? $_POST['article_id'] ?? 0);
$lang = $_GET['lang'] ?? $_POST['lang'] ?? 'en';

if ($article_id <= 0) json_error('Article ID is required.');

try {
    $score = calculate_seo_score($article_id, $lang);
    json_response($score);
} catch (Exception $ex) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}

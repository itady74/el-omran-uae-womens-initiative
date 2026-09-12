<?php
/**
 * SEO Health Check API
 * GET /admin/api/seo.php?action=check&article_id=X&lang=en
 * GET /admin/api/seo.php?action=check_all
 */
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? 'check';
$article_id = (int)($_GET['article_id'] ?? 0);
$lang = $_GET['lang'] ?? 'en';

try {
    if ($action === 'check' && $article_id > 0) {
        $score = calculate_seo_score($article_id, $lang);
        json_response($score);
    } elseif ($action === 'check_all') {
        $articles = db()->query('SELECT id FROM articles')->fetchAll();
        $results = [];
        foreach ($articles as $a) {
            foreach (['en', 'ar'] as $l) {
                $check = db()->prepare('SELECT id FROM article_translations WHERE article_id = ? AND language = ?');
                $check->execute([(int)$a['id'], $l]);
                if ($check->fetch()) {
                    $score = calculate_seo_score((int)$a['id'], $l);
                    $results[] = ['article_id' => (int)$a['id'], 'language' => $l, 'score' => $score['score'], 'issues' => $score['issues']];
                }
            }
        }
        json_response(['data' => $results]);
    } else {
        json_error('Invalid action.');
    }
} catch (Exception $ex) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}

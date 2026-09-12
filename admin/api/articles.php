<?php
/**
 * Public API - Articles
 * GET /admin/api/articles.php?action=list&lang=en
 * GET /admin/api/articles.php?action=get&slug=example&lang=en
 */
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? 'list';
$lang = $_GET['lang'] ?? 'en';
$slug = $_GET['slug'] ?? '';

if (!in_array($lang, ['en', 'ar'])) $lang = 'en';

try {
    switch ($action) {
        case 'list':
            $page = max(1, (int)($_GET['page'] ?? 1));
            $per_page = min(50, max(1, (int)($_GET['per_page'] ?? 20)));
            $offset = ($page - 1) * $per_page;

            $count_stmt = db()->prepare('SELECT COUNT(*) FROM articles a JOIN article_translations at ON a.id = at.article_id WHERE a.status = ? AND at.language = ?');
            $count_stmt->execute(['published', $lang]);
            $total = (int) $count_stmt->fetchColumn();

            $stmt = db()->prepare('
                SELECT a.id, at.title, at.slug, at.excerpt, at.content, at.language,
                       at.seo_title, at.meta_description, at.canonical_url,
                       at.index_status, at.follow_status,
                       a.published_at, a.featured_image_id,
                       m.path as featured_image
                FROM articles a
                JOIN article_translations at ON a.id = at.article_id
                LEFT JOIN media m ON a.featured_image_id = m.id
                WHERE a.status = ? AND at.language = ?
                ORDER BY a.published_at DESC
                LIMIT ' . (int)$per_page . ' OFFSET ' . (int)$offset
            ');
            $stmt->execute(['published', $lang]);
            $articles = $stmt->fetchAll();

            $result = [];
            foreach ($articles as $a) {
                $result[] = [
                    'id'              => (int) $a['id'],
                    'title'           => $a['title'],
                    'slug'            => $a['slug'],
                    'excerpt'         => $a['excerpt'],
                    'content'         => $a['content'],
                    'featured_image'  => $a['featured_image'] ? UPLOAD_URL . '/' . $a['featured_image'] : null,
                    'seo_title'       => $a['seo_title'],
                    'meta_description'=> $a['meta_description'],
                    'canonical_url'   => $a['canonical_url'],
                    'index_status'    => $a['index_status'],
                    'follow_status'   => $a['follow_status'],
                    'published_at'    => $a['published_at'],
                    'language'        => $a['language'],
                    'url'             => get_setting('site_url') . '/' . ($lang === 'ar' ? '' : $lang . '/') . 'blog/' . $a['slug'],
                ];
            }

            json_response([
                'data'  => $result,
                'total' => $total,
                'page'  => $page,
                'per_page' => $per_page,
            ]);
            break;

        case 'get':
            if (empty($slug)) json_error('Slug is required.');

            $stmt = db()->prepare('
                SELECT a.id, at.title, at.slug, at.excerpt, at.content, at.language,
                       at.seo_title, at.meta_description, at.canonical_url,
                       at.index_status, at.follow_status,
                       a.published_at, a.featured_image_id,
                       m.path as featured_image
                FROM articles a
                JOIN article_translations at ON a.id = at.article_id
                LEFT JOIN media m ON a.featured_image_id = m.id
                WHERE a.status = ? AND at.language = ? AND at.slug = ?
                LIMIT 1
            ');
            $stmt->execute(['published', $lang, $slug]);
            $a = $stmt->fetch();

            if (!$a) json_error('Article not found.', 404);

            json_response([
                'id'              => (int) $a['id'],
                'title'           => $a['title'],
                'slug'            => $a['slug'],
                'excerpt'         => $a['excerpt'],
                'content'         => $a['content'],
                'featured_image'  => $a['featured_image'] ? UPLOAD_URL . '/' . $a['featured_image'] : null,
                'seo_title'       => $a['seo_title'],
                'meta_description'=> $a['meta_description'],
                'canonical_url'   => $a['canonical_url'],
                'index_status'    => $a['index_status'],
                'follow_status'   => $a['follow_status'],
                'published_at'    => $a['published_at'],
                'language'        => $a['language'],
                'url'             => get_setting('site_url') . '/' . ($lang === 'ar' ? '' : $lang . '/') . 'blog/' . $a['slug'],
            ]);
            break;

        default:
            json_error('Invalid action.', 400);
    }
} catch (Exception $ex) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}

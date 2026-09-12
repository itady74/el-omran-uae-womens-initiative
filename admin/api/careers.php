<?php
/**
 * Public API - Careers
 * GET /admin/api/careers.php?action=list&lang=en
 * GET /admin/api/careers.php?action=get&slug=training-coordinator&lang=en
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

            $count_stmt = db()->prepare('SELECT COUNT(*) FROM careers c JOIN career_translations ct ON c.id = ct.career_id WHERE c.status = ? AND ct.language = ?');
            $count_stmt->execute(['published', $lang]);
            $total = (int) $count_stmt->fetchColumn();

            $stmt = db()->prepare('
                SELECT c.id, ct.title, ct.slug, ct.location, ct.employment_type,
                       ct.description, ct.requirements, ct.benefits, ct.language,
                       c.published_at
                FROM careers c
                JOIN career_translations ct ON c.id = ct.career_id
                WHERE c.status = ? AND ct.language = ?
                ORDER BY c.published_at DESC
                LIMIT ' . (int)$per_page . ' OFFSET ' . (int)$offset
            ');
            $stmt->execute(['published', $lang]);
            $careers = $stmt->fetchAll();

            $result = [];
            foreach ($careers as $c) {
                $result[] = [
                    'id'              => (int) $c['id'],
                    'title'           => $c['title'],
                    'slug'            => $c['slug'],
                    'location'        => $c['location'],
                    'employment_type' => $c['employment_type'],
                    'description'     => $c['description'],
                    'requirements'    => $c['requirements'],
                    'benefits'        => $c['benefits'],
                    'published_at'    => $c['published_at'],
                    'language'        => $c['language'],
                    'url'             => get_setting('site_url') . '/' . ($lang === 'ar' ? '' : $lang . '/') . 'careers/' . $c['slug'],
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
                SELECT c.id, ct.title, ct.slug, ct.location, ct.employment_type,
                       ct.description, ct.requirements, ct.benefits, ct.language,
                       c.published_at
                FROM careers c
                JOIN career_translations ct ON c.id = ct.career_id
                WHERE c.status = ? AND ct.language = ? AND ct.slug = ?
                LIMIT 1
            ');
            $stmt->execute(['published', $lang, $slug]);
            $c = $stmt->fetch();

            if (!$c) json_error('Career not found.', 404);

            json_response([
                'id'              => (int) $c['id'],
                'title'           => $c['title'],
                'slug'            => $c['slug'],
                'location'        => $c['location'],
                'employment_type' => $c['employment_type'],
                'description'     => $c['description'],
                'requirements'    => $c['requirements'],
                'benefits'        => $c['benefits'],
                'published_at'    => $c['published_at'],
                'language'        => $c['language'],
                'url'             => get_setting('site_url') . '/' . ($lang === 'ar' ? '' : $lang . '/') . 'careers/' . $c['slug'],
            ]);
            break;

        default:
            json_error('Invalid action.', 400);
    }
} catch (Exception $ex) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}

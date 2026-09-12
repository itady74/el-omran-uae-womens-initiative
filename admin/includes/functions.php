<?php
/**
 * Helper functions
 */

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/auth.php';

// --- Output escaping ---
function e(?string $str): string
{
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

// --- JSON response ---
function json_response(array $data, int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

function json_error(string $message, int $code = 400): void
{
    json_response(['error' => $message], $code);
}

// --- Redirect ---
function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

// --- CSRF ---
function csrf_token(): string
{
    start_session_safe();
    if (empty($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

function csrf_field(): string
{
    return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): bool
{
    $token = $_POST[CSRF_TOKEN_NAME] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    return hash_equals(csrf_token(), $token);
}

// --- Slug ---
function slugify(string $text, string $lang = 'en'): string
{
    $text = trim($text);
    if ($lang === 'ar') {
        // Allow Arabic characters, replace spaces with hyphens
        $text = preg_replace('/\s+/', '-', $text);
        $text = preg_replace('/[^\p{Arabic}\p{M}-]+/u', '', $text);
        $text = preg_replace('/-+/', '-', $text);
        return rtrim($text, '-');
    }
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return rtrim($text, '-');
}

// --- File uploads ---
function upload_image(string $input_name, string $subdir = 'articles'): ?array
{
    if (!isset($_FILES[$input_name]) || $_FILES[$input_name]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $file = $_FILES[$input_name];

    if ($file['size'] > MAX_UPLOAD_SIZE) {
        return null;
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);

    if (!in_array($mime, ALLOWED_IMAGE_TYPES, true)) {
        return null;
    }

    $ext = match ($mime) {
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        default      => 'jpg',
    };

    $filename = uniqid('img_', true) . '.' . $ext;
    $dest_dir = UPLOAD_PATH . '/' . $subdir;

    if (!is_dir($dest_dir)) {
        mkdir($dest_dir, 0755, true);
    }

    $dest = $dest_dir . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        return null;
    }

    $dims = @getimagesize($dest);

    // Save to media table
    $stmt = db()->prepare(
        'INSERT INTO media (filename, original_filename, path, mime_type, size, width, height) VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $filename,
        $file['name'],
        $subdir . '/' . $filename,
        $mime,
        $file['size'],
        $dims[0] ?? null,
        $dims[1] ?? null,
    ]);

    $media_id = (int) db()->lastInsertId();

    return [
        'id'       => $media_id,
        'filename' => $filename,
        'path'     => $subdir . '/' . $filename,
        'url'      => UPLOAD_URL . '/' . $subdir . '/' . $filename,
    ];
}

function delete_file(string $relative_path): bool
{
    $full = UPLOAD_PATH . '/' . $relative_path;
    if (file_exists($full)) {
        return unlink($full);
    }
    return false;
}

// --- SEO Score Calculator ---
function calculate_seo_score(int $article_id, string $lang = 'en'): array
{
    $total_checks = 13;
    $score = 0;
    $issues = ['critical' => [], 'warning' => [], 'passed' => []];

    // Get article translation
    $stmt = db()->prepare('SELECT * FROM article_translations WHERE article_id = ? AND language = ?');
    $stmt->execute([$article_id, $lang]);
    $t = $stmt->fetch();

    // Get SEO metadata
    $stmt2 = db()->prepare('SELECT * FROM seo_metadata WHERE article_id = ? AND language = ?');
    $stmt2->execute([$article_id, $lang]);
    $seo = $stmt2->fetch();

    // Get keywords
    $stmt3 = db()->prepare('SELECT * FROM keywords WHERE article_id = ? AND language = ?');
    $stmt3->execute([$article_id, $lang]);
    $kws = $stmt3->fetchAll();

    $primary_kw = '';
    foreach ($kws as $kw) {
        if ($kw['type'] === 'primary') {
            $primary_kw = strtolower($kw['keyword']);
            break;
        }
    }

    $title = $t['title'] ?? '';
    $meta_desc = $seo['meta_description'] ?? $t['meta_description'] ?? '';
    $seo_title = $seo['seo_title'] ?? $t['seo_title'] ?? '';
    $slug = $t['slug'] ?? '';
    $content = $t['content'] ?? '';
    $excerpt = $t['excerpt'] ?? '';
    $content_text = strip_tags($content);

    // 1. SEO Title exists
    if (!empty($seo_title)) {
        $score += (100 / $total_checks);
        $issues['passed'][] = 'SEO title is set.';
    } else {
        $issues['critical'][] = 'Add an SEO title to help search engines understand your page.';
    }

    // 2. SEO Title length
    if (!empty($seo_title) && strlen($seo_title) >= 30 && strlen($seo_title) <= SEO_TITLE_MAX) {
        $score += (100 / $total_checks);
        $issues['passed'][] = 'SEO title length is optimal.';
    } elseif (!empty($seo_title)) {
        $score += (100 / $total_checks * 0.5);
        $issues['warning'][] = strlen($seo_title) < 30
            ? 'Your SEO title is a bit short. Aim for 30-60 characters.'
            : 'Your SEO title may be too long and could be truncated in search results.';
    }

    // 3. Meta Description exists
    if (!empty($meta_desc)) {
        $score += (100 / $total_checks);
        $issues['passed'][] = 'Meta description is set.';
    } else {
        $issues['critical'][] = 'Add a meta description to help search engines understand this article.';
    }

    // 4. Meta Description length
    if (!empty($meta_desc) && strlen($meta_desc) >= 100 && strlen($meta_desc) <= SEO_DESC_MAX) {
        $score += (100 / $total_checks);
        $issues['passed'][] = 'Meta description length is optimal.';
    } elseif (!empty($meta_desc)) {
        $score += (100 / $total_checks * 0.5);
        $issues['warning'][] = strlen($meta_desc) < 100
            ? 'Your meta description is a bit short. Aim for 100-160 characters.'
            : 'Your meta description may be too long and could be truncated in search results.';
    }

    // 5. Focus keyword exists
    if (!empty($primary_kw)) {
        $score += (100 / $total_checks);
        $issues['passed'][] = 'Focus keyword is set.';
    } else {
        $issues['critical'][] = 'Set a focus keyword to track how well your content is optimized.';
    }

    // 6. Focus keyword in SEO title
    if (!empty($primary_kw) && !empty($seo_title) && str_contains(strtolower($seo_title), $primary_kw)) {
        $score += (100 / $total_checks);
        $issues['passed'][] = 'Focus keyword appears in the SEO title.';
    } elseif (!empty($primary_kw)) {
        $issues['warning'][] = 'Include your focus keyword in the SEO title for better ranking.';
    }

    // 7. Focus keyword in meta description
    if (!empty($primary_kw) && !empty($meta_desc) && str_contains(strtolower($meta_desc), $primary_kw)) {
        $score += (100 / $total_checks);
        $issues['passed'][] = 'Focus keyword appears in the meta description.';
    } elseif (!empty($primary_kw)) {
        $issues['warning'][] = 'Include your focus keyword in the meta description.';
    }

    // 8. Focus keyword in slug
    if (!empty($primary_kw) && !empty($slug) && str_contains(strtolower($slug), $primary_kw)) {
        $score += (100 / $total_checks);
        $issues['passed'][] = 'Focus keyword appears in the URL slug.';
    } elseif (!empty($primary_kw)) {
        $issues['warning'][] = 'Include your focus keyword in the URL slug.';
    }

    // 9. Slug is clean
    if (!empty($slug) && preg_match('/^[a-z0-9\-]+$/', $slug) || ($lang === 'ar' && !empty($slug))) {
        $score += (100 / $total_checks);
        $issues['passed'][] = 'URL slug looks good.';
    } elseif (!empty($slug)) {
        $issues['warning'][] = 'Consider cleaning up the URL slug for better readability.';
    }

    // 10. Content length
    $word_count = str_word_count($content_text);
    if ($word_count >= 300) {
        $score += (100 / $total_checks);
        $issues['passed'][] = 'Content length is good (' . $word_count . ' words).';
    } elseif ($word_count >= 100) {
        $score += (100 / $total_checks * 0.5);
        $issues['warning'][] = 'Content is a bit short (' . $word_count . ' words). Aim for 300+ words for better SEO.';
    } else {
        $issues['critical'][] = 'Content is too short (' . $word_count . ' words). Add more content to improve SEO.';
    }

    // 11. Heading structure (H1)
    if (preg_match('/<h1[\s>]/i', $content)) {
        $score += (100 / $total_checks);
        $issues['passed'][] = 'Content has an H1 heading.';
    } else {
        $issues['warning'][] = 'Add an H1 heading to your content for better structure.';
    }

    // 12. Images have alt text
    preg_match_all('/<img[^>]+>/i', $content, $imgs);
    if (!empty($imgs[0])) {
        $missing_alt = 0;
        foreach ($imgs[0] as $img) {
            if (!preg_match('/alt=["\'][^"\']+["\']/', $img)) {
                $missing_alt++;
            }
        }
        if ($missing_alt === 0) {
            $score += (100 / $total_checks);
            $issues['passed'][] = 'All images have alt text.';
        } else {
            $issues['warning'][] = $missing_alt . ' image(s) are missing alt text. Add descriptive alt text to improve accessibility and SEO.';
        }
    } else {
        $score += (100 / $total_checks);
        $issues['passed'][] = 'No images to check.';
    }

    // 13. Excerpt
    if (!empty($excerpt)) {
        $score += (100 / $total_checks);
        $issues['passed'][] = 'Excerpt is set.';
    } else {
        $issues['warning'][] = 'Add an excerpt to improve how your article appears in search results and social shares.';
    }

    return [
        'score'   => (int) round($score),
        'issues'  => $issues,
    ];
}

// --- Sanitize HTML content ---
function sanitize_html(string $html): string
{
    $allowed = '<p><br><strong><b><em><i><u><a><ul><ol><li><h1><h2><h3><h4><h5><h6><blockquote><pre><code><img><figure><figcaption><table><thead><tbody><tr><th><td><div><span><sup><sub><hr>';
    return strip_tags($html, $allowed);
}

// --- Slug uniqueness ---
function unique_slug(string $slug, string $lang, int $exclude_article_id = 0, string $type = 'article'): string
{
    $table = $type === 'career' ? 'career_translations' : 'article_translations';
    $id_col = $type === 'career' ? 'career_id' : 'article_id';
    $base_slug = $slug;
    $counter = 1;

    while (true) {
        $sql = "SELECT id FROM {$table} WHERE slug = ? AND language = ? AND {$id_col} != ? LIMIT 1";
        $stmt = db()->prepare($sql);
        $stmt->execute([$slug, $lang, $exclude_article_id]);
        if (!$stmt->fetch()) {
            return $slug;
        }
        $slug = $base_slug . '-' . $counter;
        $counter++;
    }
}

// --- Get setting ---
function get_setting(string $key, string $default = ''): string
{
    $stmt = db()->prepare('SELECT setting_value FROM settings WHERE setting_key = ?');
    $stmt->execute([$key]);
    $row = $stmt->fetch();
    return $row ? ($row['setting_value'] ?? $default) : $default;
}

// --- Update setting ---
function update_setting(string $key, string $value): void
{
    $pdo = db();
    // Check driver for SQLite vs MySQL compatibility
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    if ($driver === 'sqlite') {
        $stmt = $pdo->prepare('INSERT OR REPLACE INTO settings (setting_key, setting_value, updated_at) VALUES (?, ?, datetime("now"))');
        $stmt->execute([$key, $value]);
    } else {
        $stmt = $pdo->prepare('INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()');
        $stmt->execute([$key, $value]);
    }
}

// --- Rate Limiting (simple file-based for shared hosting) ---
function check_rate_limit(string $key, int $max_attempts = 5, int $window_seconds = 300): bool
{
    $cache_dir = dirname(__DIR__, 2) . '/cache';
    if (!is_dir($cache_dir)) {
        @mkdir($cache_dir, 0755, true);
    }
    $file = $cache_dir . '/rate_' . md5($key) . '.json';
    $now = time();
    $data = ['attempts' => 0, 'first_attempt' => $now];

    if (file_exists($file)) {
        $raw = @file_get_contents($file);
        $data = json_decode($raw, true) ?: $data;
        // Reset if window expired
        if ($now - ($data['first_attempt'] ?? 0) > $window_seconds) {
            $data = ['attempts' => 0, 'first_attempt' => $now];
        }
    }

    if ($data['attempts'] >= $max_attempts) {
        return false; // Rate limited
    }

    $data['attempts']++;
    @file_put_contents($file, json_encode($data));
    return true;
}

function clear_rate_limit(string $key): void
{
    $cache_dir = dirname(__DIR__, 2) . '/cache';
    $file = $cache_dir . '/rate_' . md5($key) . '.json';
    if (file_exists($file)) @unlink($file);
}

// --- Pagination ---
function paginate(string $table, string $where = '1=1', array $params = [], int $per_page = 20, int $page = 1): array
{
    $page = max(1, $page);
    $offset = ($page - 1) * $per_page;

    $count_stmt = db()->prepare("SELECT COUNT(*) as total FROM {$table} WHERE {$where}");
    $count_stmt->execute($params);
    $total = (int) $count_stmt->fetch()['total'];
    $total_pages = max(1, ceil($total / $per_page));

    $stmt = db()->prepare("SELECT * FROM {$table} WHERE {$where} ORDER BY id DESC LIMIT {$per_page} OFFSET {$offset}");
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    return [
        'data'         => $rows,
        'total'        => $total,
        'page'         => $page,
        'per_page'     => $per_page,
        'total_pages'  => $total_pages,
    ];
}

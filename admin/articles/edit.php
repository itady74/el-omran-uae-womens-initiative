<?php
require_once __DIR__ . '/../includes/functions.php';
require_auth();

$article_id = (int)($_GET['id'] ?? 0);
if ($article_id <= 0) redirect(APP_URL . '/articles/');

$current_lang = $_GET['lang'] ?? DEFAULT_LANGUAGE;
if (!in_array($current_lang, SUPPORTED_LANGUAGES)) $current_lang = DEFAULT_LANGUAGE;
$lang = require __DIR__ . '/../includes/lang/' . $current_lang . '.php';
$_t = fn(string $k) => $lang[$k] ?? $k;

// Fetch article
$stmt = db()->prepare('SELECT a.*, at.language FROM articles a LEFT JOIN article_translations at ON a.id = at.article_id WHERE a.id = ?');
$stmt->execute([$article_id]);
$article = $stmt->fetch();
if (!$article) redirect(APP_URL . '/articles/');

// Fetch translation
$stmt2 = db()->prepare('SELECT * FROM article_translations WHERE article_id = ? AND language = ?');
$stmt2->execute([$article_id, $current_lang]);
$t = $stmt2->fetch();

// Fetch SEO metadata
$stmt3 = db()->prepare('SELECT * FROM seo_metadata WHERE article_id = ? AND language = ?');
$stmt3->execute([$article_id, $current_lang]);
$seo = $stmt3->fetch();

// Fetch keywords
$stmt4 = db()->prepare('SELECT * FROM keywords WHERE article_id = ? AND language = ?');
$stmt4->execute([$article_id, $current_lang]);
$kws = $stmt4->fetchAll();
$primary_kw = '';
$secondary_kws = [];
$related_kws = [];
foreach ($kws as $kw) {
    if ($kw['type'] === 'primary') $primary_kw = $kw['keyword'];
    elseif ($kw['type'] === 'secondary') $secondary_kws[] = $kw['keyword'];
    else $related_kws[] = $kw['keyword'];
}

// SEO score
$seo_data = calculate_seo_score($article_id, $current_lang);

$page_title = $t['title'] ?: $_t('edit') . ' ' . $_t('articles');

// Handle auto-save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
    header('Content-Type: application/json');
    try {
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $title = trim($data['title'] ?? '');
        $content = $data['content'] ?? '';
        $excerpt = trim($data['excerpt'] ?? '');
        $slug = trim($data['slug'] ?? '');
        $status = in_array($data['status'] ?? '', ['draft','published','archived']) ? $data['status'] : 'draft';
        $seo_title = trim($data['seo_title'] ?? '');
        $meta_desc = trim($data['meta_description'] ?? '');
        $focus_kw = trim($data['focus_keyword'] ?? '');
        $canonical = trim($data['canonical_url'] ?? '');
        $index_status = $data['index_status'] ?? 'index';
        $follow_status = $data['follow_status'] ?? 'follow';

        if (!$t) {
            if (empty($slug) && !empty($title)) $slug = unique_slug(slugify($title, $current_lang), $current_lang, $article_id, 'article');
            db()->prepare('INSERT INTO article_translations (article_id, language, title, slug, excerpt, content) VALUES (?, ?, ?, ?, ?, ?)')
                ->execute([$article_id, $current_lang, $title, $slug, $excerpt, $content]);
        } else {
            if (empty($slug) && !empty($title)) $slug = $t['slug'] ?: unique_slug(slugify($title, $current_lang), $current_lang, $article_id, 'article');

            // Track slug change
            if (!empty($t['slug']) && $t['slug'] !== $slug) {
                db()->prepare('INSERT INTO slug_history (article_id, language, old_slug, new_slug) VALUES (?, ?, ?, ?)')
                    ->execute([$article_id, $current_lang, $t['slug'], $slug]);
                db()->prepare('INSERT INTO redirects (language, old_path, new_path, status_code) VALUES (?, ?, ?, 301)')
                    ->execute([$current_lang, '/blog/' . $t['slug'], '/blog/' . $slug]);
            }

            db()->prepare('UPDATE article_translations SET title=?, slug=?, excerpt=?, content=? WHERE article_id=? AND language=?')
                ->execute([$title, $slug, $excerpt, $content, $article_id, $current_lang]);
        }

        // Update SEO metadata
        if ($seo) {
            db()->prepare('UPDATE seo_metadata SET seo_title=?, meta_description=?, focus_keyword=?, canonical_url=?, index_status=?, follow_status=? WHERE article_id=? AND language=?')
                ->execute([$seo_title ?: $title, $meta_desc, $focus_kw, $canonical, $index_status, $follow_status, $article_id, $current_lang]);
        } else {
            db()->prepare('INSERT INTO seo_metadata (article_id, language, seo_title, meta_description, focus_keyword, canonical_url, index_status, follow_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)')
                ->execute([$article_id, $current_lang, $seo_title ?: $title, $meta_desc, $focus_kw, $canonical, $index_status, $follow_status]);
        }

        // Update keywords
        db()->prepare('DELETE FROM keywords WHERE article_id = ? AND language = ?')->execute([$article_id, $current_lang]);
        if ($focus_kw) {
            db()->prepare('INSERT INTO keywords (article_id, language, keyword, type) VALUES (?, ?, ?, ?)')
                ->execute([$article_id, $current_lang, $focus_kw, 'primary']);
        }
        // Secondary keywords
        $secondary = trim($data['secondary_keywords'] ?? '');
        if ($secondary !== '') {
            $kw_stmt = db()->prepare('INSERT INTO keywords (article_id, language, keyword, type) VALUES (?, ?, ?, ?)');
            foreach (array_map('trim', explode(',', $secondary)) as $skw) {
                if ($skw !== '') $kw_stmt->execute([$article_id, $current_lang, $skw, 'secondary']);
            }
        }
        // Related keywords
        $related = trim($data['related_keywords'] ?? '');
        if ($related !== '') {
            $kw_stmt = db()->prepare('INSERT INTO keywords (article_id, language, keyword, type) VALUES (?, ?, ?, ?)');
            foreach (array_map('trim', explode(',', $related)) as $rkw) {
                if ($rkw !== '') $kw_stmt->execute([$article_id, $current_lang, $rkw, 'related']);
            }
        }

        // Update article status & publish date
        $published_at = ($status === 'published' && $article['status'] !== 'published') ? date('Y-m-d H:i:s') : $article['published_at'];
        if ($status === 'published' && !$published_at) $published_at = date('Y-m-d H:i:s');
        db()->prepare('UPDATE articles SET status=?, published_at=COALESCE(?, published_at) WHERE id=?')
            ->execute([$status, $published_at, $article_id]);

        // Save OG/Twitter metadata
        $og_title = trim($data['og_title'] ?? '');
        $og_desc = trim($data['og_description'] ?? '');
        $tw_title = trim($data['twitter_title'] ?? '');
        $tw_desc = trim($data['twitter_description'] ?? '');
        if ($og_title || $og_desc || $tw_title || $tw_desc) {
            db()->prepare('UPDATE seo_metadata SET og_title=COALESCE(NULLIF(?,""),og_title), og_description=COALESCE(NULLIF(?,""),og_description), twitter_title=COALESCE(NULLIF(?,""),twitter_title), twitter_description=COALESCE(NULLIF(?,""),twitter_description) WHERE article_id=? AND language=?')
                ->execute([$og_title, $og_desc, $tw_title, $tw_desc, $article_id, $current_lang]);
        }

        echo json_encode(['success' => true, 'article_id' => $article_id]);
    } catch (Exception $ex) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Save failed']);
    }
    exit;
}

// Handle standard POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['auto_save'])) {
    if (!verify_csrf()) {
        $errors[] = 'Invalid request.';
    } else {
        $title = trim($_POST['title'] ?? '');
        $content = $_POST['content'] ?? '';
        $excerpt = trim($_POST['excerpt'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $status = in_array($_POST['status'] ?? '', ['draft','published','archived']) ? $_POST['status'] : 'draft';
        $seo_title = trim($_POST['seo_title'] ?? '');
        $meta_desc = trim($_POST['meta_description'] ?? '');
        $focus_kw = trim($_POST['focus_keyword'] ?? '');
        $canonical = trim($_POST['canonical_url'] ?? '');
        $index_status = $_POST['index_status'] ?? 'index';
        $follow_status = $_POST['follow_status'] ?? 'follow';

        if (empty($slug) && !empty($title)) $slug = slugify($title, $current_lang);
        $slug = unique_slug($slug, $current_lang, $article_id, 'article');

        // Track slug change
        if ($t && !empty($t['slug']) && $t['slug'] !== $slug) {
            db()->prepare('INSERT INTO slug_history (article_id, language, old_slug, new_slug) VALUES (?, ?, ?, ?)')
                ->execute([$article_id, $current_lang, $t['slug'], $slug]);
            db()->prepare('INSERT INTO redirects (language, old_path, new_path, status_code) VALUES (?, ?, ?, 301)')
                ->execute([$current_lang, '/blog/' . $t['slug'], '/blog/' . $slug]);
        }

        if ($t) {
            db()->prepare('UPDATE article_translations SET title=?, slug=?, excerpt=?, content=? WHERE article_id=? AND language=?')
                ->execute([$title, $slug, $excerpt, $content, $article_id, $current_lang]);
        } else {
            db()->prepare('INSERT INTO article_translations (article_id, language, title, slug, excerpt, content) VALUES (?, ?, ?, ?, ?, ?)')
                ->execute([$article_id, $current_lang, $title, $slug, $excerpt, $content]);
        }

        // SEO
        if ($seo) {
            db()->prepare('UPDATE seo_metadata SET seo_title=?, meta_description=?, focus_keyword=?, canonical_url=?, index_status=?, follow_status=? WHERE article_id=? AND language=?')
                ->execute([$seo_title ?: $title, $meta_desc, $focus_kw, $canonical, $index_status, $follow_status, $article_id, $current_lang]);
        } else {
            db()->prepare('INSERT INTO seo_metadata (article_id, language, seo_title, meta_description, focus_keyword, canonical_url, index_status, follow_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)')
                ->execute([$article_id, $current_lang, $seo_title ?: $title, $meta_desc, $focus_kw, $canonical, $index_status, $follow_status]);
        }

        // Keywords
        db()->prepare('DELETE FROM keywords WHERE article_id = ? AND language = ?')->execute([$article_id, $current_lang]);
        if ($focus_kw) {
            db()->prepare('INSERT INTO keywords (article_id, language, keyword, type) VALUES (?, ?, ?, ?)')
                ->execute([$article_id, $current_lang, $focus_kw, 'primary']);
        }
        // Secondary keywords
        $secondary = trim($_POST['secondary_keywords'] ?? '');
        if ($secondary !== '') {
            $kw_stmt = db()->prepare('INSERT INTO keywords (article_id, language, keyword, type) VALUES (?, ?, ?, ?)');
            foreach (array_map('trim', explode(',', $secondary)) as $skw) {
                if ($skw !== '') $kw_stmt->execute([$article_id, $current_lang, $skw, 'secondary']);
            }
        }
        // Related keywords
        $related = trim($_POST['related_keywords'] ?? '');
        if ($related !== '') {
            $kw_stmt = db()->prepare('INSERT INTO keywords (article_id, language, keyword, type) VALUES (?, ?, ?, ?)');
            foreach (array_map('trim', explode(',', $related)) as $rkw) {
                if ($rkw !== '') $kw_stmt->execute([$article_id, $current_lang, $rkw, 'related']);
            }
        }

        // Featured image
        $upload = upload_image('featured_image', 'articles');
        if ($upload) {
            db()->prepare('UPDATE articles SET featured_image_id = ? WHERE id = ?')
                ->execute([$upload['id'], $article_id]);
            if (!empty($_POST['featured_image_alt'])) {
                db()->prepare('UPDATE media SET alt_text_en = ? WHERE id = ?')
                    ->execute([$_POST['featured_image_alt'], $upload['id']]);
            }
        }

        // OG Image upload
        $og_upload = upload_image('og_image', 'og');
        $og_title = trim($_POST['og_title'] ?? '');
        $og_desc = trim($_POST['og_description'] ?? '');
        $tw_title = trim($_POST['twitter_title'] ?? '');
        $tw_desc = trim($_POST['twitter_description'] ?? '');
        $og_img_path = $og_upload ? $og_upload['path'] : '';
        if ($seo) {
            $update_sql = 'UPDATE seo_metadata SET og_title=?, og_description=?, twitter_title=?, twitter_description=?';
            $update_params = [$og_title, $og_desc, $tw_title, $tw_desc];
            if ($og_img_path) {
                $update_sql .= ', og_image=?';
                $update_params[] = $og_img_path;
            }
            $update_sql .= ' WHERE article_id=? AND language=?';
            $update_params[] = $article_id;
            $update_params[] = $current_lang;
            db()->prepare($update_sql)->execute($update_params);
        }

        // Status & publish
        $published_at = ($status === 'published' && $article['status'] !== 'published') ? date('Y-m-d H:i:s') : $article['published_at'];
        if ($status === 'published' && !$published_at) $published_at = date('Y-m-d H:i:s');
        db()->prepare('UPDATE articles SET status=?, published_at=COALESCE(?, published_at) WHERE id=?')
            ->execute([$status, $published_at, $article_id]);

        redirect(APP_URL . '/articles/edit.php?id=' . $article_id . '&lang=' . $current_lang . '&saved=1');
    }
}

$saved = isset($_GET['saved']);

include __DIR__ . '/../includes/header.php';
?>

<?php if ($saved): ?>
<script>document.addEventListener('DOMContentLoaded', () => showToast('Article saved successfully.', 'success'));</script>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" id="articleForm" action="<?= APP_URL ?>/articles/edit.php?id=<?= $article_id ?>&lang=<?= e($current_lang) ?>">
    <?= csrf_field() ?>

    <div class="editor-layout">
        <div class="editor-main">
            <div class="card">
                <div class="form-group">
                    <label class="form-label"><?= $_t('article_title') ?></label>
                    <input type="text" name="title" id="title" class="form-input" required value="<?= e($t['title'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label class="form-label"><?= $_t('article_content') ?></label>
                    <div class="editor-toolbar">
                        <button type="button" class="editor-toolbar-btn" data-cmd="bold" title="Bold"><b>B</b></button>
                        <button type="button" class="editor-toolbar-btn" data-cmd="italic" title="Italic"><i>I</i></button>
                        <button type="button" class="editor-toolbar-btn" data-cmd="underline" title="Underline"><u>U</u></button>
                        <div class="editor-toolbar-sep"></div>
                        <button type="button" class="editor-toolbar-btn" data-cmd="formatBlock" data-val="H1">H1</button>
                        <button type="button" class="editor-toolbar-btn" data-cmd="formatBlock" data-val="H2">H2</button>
                        <button type="button" class="editor-toolbar-btn" data-cmd="formatBlock" data-val="H3">H3</button>
                        <button type="button" class="editor-toolbar-btn" data-cmd="formatBlock" data-val="P">P</button>
                        <div class="editor-toolbar-sep"></div>
                        <button type="button" class="editor-toolbar-btn" data-cmd="insertUnorderedList">&#8226;</button>
                        <button type="button" class="editor-toolbar-btn" data-cmd="insertOrderedList">1.</button>
                        <button type="button" class="editor-toolbar-btn" data-cmd="formatBlock" data-val="BLOCKQUOTE">&#10077;</button>
                        <div class="editor-toolbar-sep"></div>
                        <button type="button" class="editor-toolbar-btn" data-cmd="createLink" title="Link"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg></button>
                        <button type="button" class="editor-toolbar-btn" data-cmd="viewSource" title="Source"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg></button>
                    </div>
                    <div contenteditable="true" class="editor-content rich-editor" id="editor_content" dir="<?= $current_lang === 'ar' ? 'rtl' : 'ltr' ?>"><?= $t['content'] ?? '' ?></div>
                    <textarea name="content" id="content_hidden" style="display:none"></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label"><?= $_t('article_excerpt') ?></label>
                    <textarea name="excerpt" class="form-textarea" rows="3"><?= e($t['excerpt'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label"><?= $_t('slug') ?></label>
                    <div style="display:flex;gap:var(--space-sm);align-items:center;">
                        <input type="text" name="slug" id="slug" class="form-input" data-slug-source="title" data-lang="<?= e($current_lang) ?>" value="<?= e($t['slug'] ?? '') ?>">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('slug').value=generateSlug(document.getElementById('title').value,'<?= $current_lang ?>');document.getElementById('slug').dataset.manualEdit='true';">Generate</button>
                    </div>
                    <div class="form-hint">URL: <?= e(get_setting('site_url')) ?>/<?= $current_lang === 'ar' ? '' : $current_lang . '/' ?>blog/<span id="slug_preview_value"><?= e($t['slug'] ?? '') ?></span></div>
                </div>
            </div>

            <!-- Keywords Section -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title" style="font-size:0.9375rem"><?= $_t('keyword_tracker') ?></h3>
                </div>
                <div class="form-group">
                    <label class="form-label"><?= $_t('primary_keyword') ?></label>
                    <input type="text" name="focus_keyword" class="form-input" value="<?= e($primary_kw) ?>" placeholder="<?= $_t('primary_keyword') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label"><?= $_t('secondary_keywords') ?></label>
                    <input type="text" name="secondary_keywords" class="form-input" value="<?= e(implode(', ', $secondary_kws)) ?>" placeholder="<?= $_t('secondary_keywords_placeholder') ?>">
                    <div class="form-hint"><?= $_t('comma_separated') ?></div>
                </div>
                <div class="form-group">
                    <label class="form-label"><?= $_t('related_keywords') ?></label>
                    <input type="text" name="related_keywords" class="form-input" value="<?= e(implode(', ', $related_kws)) ?>" placeholder="<?= $_t('related_keywords_placeholder') ?>">
                    <div class="form-hint"><?= $_t('comma_separated') ?></div>
                </div>

                <?php if ($primary_kw && !empty($t['content'])): ?>
                    <?php
                    $content_lower = strtolower(strip_tags($t['content'] ?? ''));
                    $title_lower = strtolower($t['title'] ?? '');
                    $seo_title_lower = strtolower($seo['seo_title'] ?? '');
                    $meta_lower = strtolower($seo['meta_description'] ?? '');
                    $slug_lower = strtolower($t['slug'] ?? '');

                    $checks = [
                        ['label' => $_t('keyword_in_title'), 'found' => str_contains($seo_title_lower, $primary_kw) || str_contains($title_lower, $primary_kw)],
                        ['label' => $_t('keyword_in_desc'), 'found' => str_contains($meta_lower, $primary_kw)],
                        ['label' => $_t('keyword_in_slug'), 'found' => str_contains($slug_lower, $primary_kw)],
                        ['label' => $_t('keyword_in_h1'), 'found' => preg_match('/<h1[^>]*>.*?' . preg_quote($primary_kw, '/') . '.*?<\/h1>/i', $t['content'] ?? '')],
                        ['label' => $_t('keyword_in_content'), 'found' => str_contains($content_lower, $primary_kw)],
                    ];
                    ?>
                    <ul class="seo-issues">
                        <?php foreach ($checks as $ck): ?>
                            <li class="seo-issue">
                                <span class="seo-issue-icon <?= $ck['found'] ? 'pass' : 'fail' ?>"><?= $ck['found'] ? '&#10003;' : '&#10007;' ?></span>
                                <span><?= $ck['label'] ?>: <?= $ck['found'] ? $_t('found') : $_t('missing') ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>

        <!-- SEO Sidebar -->
        <div class="editor-sidebar">
            <!-- SEO Score -->
            <div class="card" style="margin-bottom:var(--space-lg)">
                <div class="card-header">
                    <h3 class="card-title" style="font-size:0.9375rem"><?= $_t('seo_score') ?></h3>
                    <div id="saveStatus" style="font-size:0.75rem;color:var(--text-muted)"></div>
                </div>
                <?php
                $score = $seo_data['score'];
                $score_cls = $score >= 80 ? 'excellent' : ($score >= 60 ? 'good' : ($score >= 40 ? 'fair' : 'poor'));
                ?>
                <div class="seo-score-display">
                    <div class="seo-score-circle <?= $score_cls ?>"><?= $score ?></div>
                    <div class="seo-score-label"><?= $score >= 80 ? 'Excellent' : ($score >= 60 ? 'Good' : ($score >= 40 ? 'Needs Work' : 'Poor')) ?></div>
                </div>

                <?php if (!empty($seo_data['issues']['critical'])): ?>
                    <div style="margin-top:var(--space-md)">
                        <h4 style="font-size:0.8125rem;color:var(--danger);margin-bottom:var(--space-sm)"><?= $_t('critical_issues') ?> (<?= count($seo_data['issues']['critical']) ?>)</h4>
                        <ul class="seo-issues">
                            <?php foreach ($seo_data['issues']['critical'] as $issue): ?>
                                <li class="seo-issue"><span class="seo-issue-icon fail">&#10007;</span><span><?= e($issue) ?></span></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if (!empty($seo_data['issues']['warning'])): ?>
                    <div style="margin-top:var(--space-md)">
                        <h4 style="font-size:0.8125rem;color:var(--warning);margin-bottom:var(--space-sm)"><?= $_t('warnings') ?> (<?= count($seo_data['issues']['warning']) ?>)</h4>
                        <ul class="seo-issues">
                            <?php foreach ($seo_data['issues']['warning'] as $issue): ?>
                                <li class="seo-issue"><span class="seo-issue-icon warn">&#9888;</span><span><?= e($issue) ?></span></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if (!empty($seo_data['issues']['passed'])): ?>
                    <div style="margin-top:var(--space-md)">
                        <h4 style="font-size:0.8125rem;color:var(--success);margin-bottom:var(--space-sm)"><?= $_t('passed_checks') ?> (<?= count($seo_data['issues']['passed']) ?>)</h4>
                        <ul class="seo-issues">
                            <?php foreach ($seo_data['issues']['passed'] as $issue): ?>
                                <li class="seo-issue"><span class="seo-issue-icon pass">&#10003;</span><span><?= e($issue) ?></span></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>

            <!-- SERP Preview -->
            <div class="collapsible open" style="margin-bottom:var(--space-lg)">
                <button type="button" class="collapsible-header">
                    <?= $_t('serp_preview') ?>
                    <svg class="collapsible-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
                <div class="collapsible-body">
                    <div class="tabs" style="margin-bottom:var(--space-md)">
                        <button type="button" class="tab active" data-group="serp" data-tab="serp_desktop"><?= $_t('desktop') ?></button>
                        <button type="button" class="tab" data-group="serp" data-tab="serp_mobile"><?= $_t('mobile') ?></button>
                    </div>
                    <div id="serp_desktop" class="tab-content active" data-group="serp">
                        <div class="serp-preview">
                            <div class="serp-title" id="serp_preview_title"><?= e($seo['seo_title'] ?? $t['title'] ?? 'Page Title') ?></div>
                            <div class="serp-url"><?= e(get_setting('site_url')) ?>/blog/<?= e($t['slug'] ?? 'page-slug') ?></div>
                            <div class="serp-desc" id="serp_preview_desc"><?= e($seo['meta_description'] ?? 'Meta description will appear here...') ?></div>
                        </div>
                    </div>
                    <div id="serp_mobile" class="tab-content" data-group="serp">
                        <div class="serp-preview" style="max-width:360px">
                            <div class="serp-title" style="font-size:1rem" id="serp_preview_title_m"><?= e($seo['seo_title'] ?? $t['title'] ?? 'Page Title') ?></div>
                            <div class="serp-url"><?= e(get_setting('site_url')) ?>/blog/<?= e($t['slug'] ?? 'page-slug') ?></div>
                            <div class="serp-desc" id="serp_preview_desc_m"><?= e($seo['meta_description'] ?? 'Meta description will appear here...') ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status & Publish -->
            <div class="card" style="margin-bottom:var(--space-lg)">
                <div class="card-header">
                    <h3 class="card-title" style="font-size:0.9375rem"><?= $_t('article_status') ?></h3>
                </div>
                <div class="form-group">
                    <select name="status" class="form-select">
                        <option value="draft" <?= ($article['status'] ?? '') === 'draft' ? 'selected' : '' ?>><?= $_t('draft') ?></option>
                        <option value="published" <?= ($article['status'] ?? '') === 'published' ? 'selected' : '' ?>><?= $_t('published') ?></option>
                        <option value="archived" <?= ($article['status'] ?? '') === 'archived' ? 'selected' : '' ?>><?= $_t('archived') ?></option>
                    </select>
                </div>
                <?php if ($article['published_at']): ?>
                    <div class="form-hint" style="margin-bottom:var(--space-md)"><?= $_t('article_published') ?>: <?= date('M d, Y H:i', strtotime($article['published_at'])) ?></div>
                <?php endif; ?>
            </div>

            <!-- Featured Image -->
            <div class="card" style="margin-bottom:var(--space-lg)">
                <div class="card-header">
                    <h3 class="card-title" style="font-size:0.9375rem"><?= $_t('article_image') ?></h3>
                </div>
                <?php if ($article['featured_image_id']): ?>
                    <?php $fimg = db()->prepare('SELECT * FROM media WHERE id = ?'); $fimg->execute([$article['featured_image_id']]); $fimg = $fimg->fetch(); ?>
                    <?php if ($fimg): ?>
                        <div class="image-preview">
                            <img src="<?= UPLOAD_URL ?>/<?= e($fimg['path']) ?>" alt="">
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
                <div class="form-group" style="margin-top:var(--space-md)">
                    <input type="file" name="featured_image" class="form-input" accept="image/*">
                    <input type="text" name="featured_image_alt" class="form-input" placeholder="<?= $_t('alt_text_en') ?>" style="margin-top:var(--space-sm)" value="<?= e($fimg['alt_text_en'] ?? '') ?>">
                </div>
            </div>

            <!-- SEO Metadata -->
            <div class="collapsible open" style="margin-bottom:var(--space-lg)">
                <button type="button" class="collapsible-header">
                    <?= $_t('seo_metadata') ?>
                    <svg class="collapsible-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
                <div class="collapsible-body">
                    <div class="form-group">
                        <label class="form-label"><?= $_t('seo_title') ?></label>
                        <input type="text" name="seo_title" id="seo_title" class="form-input" data-maxlength="60" value="<?= e($seo['seo_title'] ?? '') ?>">
                        <div class="char-count">0 / 60</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><?= $_t('meta_description') ?></label>
                        <textarea name="meta_description" id="meta_description" class="form-textarea" rows="3" data-maxlength="160"><?= e($seo['meta_description'] ?? '') ?></textarea>
                        <div class="char-count">0 / 160</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><?= $_t('canonical_url') ?></label>
                        <input type="url" name="canonical_url" class="form-input" value="<?= e($seo['canonical_url'] ?? '') ?>" placeholder="https://...">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label"><?= $_t('index_status') ?></label>
                            <select name="index_status" class="form-select">
                                <option value="index" <?= ($seo['index_status'] ?? 'index') === 'index' ? 'selected' : '' ?>><?= $_t('index') ?></option>
                                <option value="noindex" <?= ($seo['index_status'] ?? '') === 'noindex' ? 'selected' : '' ?>><?= $_t('noindex') ?></option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label"><?= $_t('follow_status') ?></label>
                            <select name="follow_status" class="form-select">
                                <option value="follow" <?= ($seo['follow_status'] ?? 'follow') === 'follow' ? 'selected' : '' ?>><?= $_t('follow') ?></option>
                                <option value="nofollow" <?= ($seo['follow_status'] ?? '') === 'nofollow' ? 'selected' : '' ?>><?= $_t('nofollow') ?></option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Social Images -->
            <div class="collapsible" style="margin-bottom:var(--space-lg)">
                <button type="button" class="collapsible-header">
                    <?= $_t('social_images') ?>
                    <svg class="collapsible-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
                <div class="collapsible-body">
                    <div class="form-group">
                        <label class="form-label"><?= $_t('og_title') ?></label>
                        <input type="text" name="og_title" class="form-input" value="<?= e($seo['og_title'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label"><?= $_t('og_description') ?></label>
                        <textarea name="og_description" class="form-textarea" rows="2"><?= e($seo['og_description'] ?? '') ?></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><?= $_t('og_image') ?></label>
                        <input type="file" name="og_image" class="form-input" accept="image/*">
                    </div>
                    <div class="form-group">
                        <label class="form-label"><?= $_t('twitter_title') ?></label>
                        <input type="text" name="twitter_title" class="form-input" value="<?= e($seo['twitter_title'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label"><?= $_t('twitter_description') ?></label>
                        <textarea name="twitter_description" class="form-textarea" rows="2"><?= e($seo['twitter_description'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <div style="display:flex;gap:var(--space-sm)">
                <button type="submit" class="btn btn-primary" style="flex:1"><?= $_t('save') ?></button>
                <a href="<?= APP_URL ?>/articles/" class="btn btn-secondary"><?= $_t('back') ?></a>
            </div>
        </div>
    </div>
</form>

<script>
document.getElementById('articleForm').addEventListener('submit', function() {
    document.getElementById('content_hidden').value = document.getElementById('editor_content').innerHTML;
});

// Live SERP preview
function updateSERP() {
    const t = document.getElementById('seo_title')?.value || document.getElementById('title')?.value || 'Page Title';
    const d = document.getElementById('meta_description')?.value || 'Meta description...';
    const s = document.getElementById('slug')?.value || 'page-slug';
    const site = '<?= e(get_setting("site_url")) ?>';
    document.querySelectorAll('#serp_preview_title, #serp_preview_title_m').forEach(el => el.textContent = t);
    document.querySelectorAll('#serp_preview_desc, #serp_preview_desc_m').forEach(el => el.textContent = d);
}
document.getElementById('seo_title')?.addEventListener('input', updateSERP);
document.getElementById('meta_description')?.addEventListener('input', updateSERP);
document.getElementById('title')?.addEventListener('input', updateSERP);
document.getElementById('slug')?.addEventListener('input', function() {
    document.getElementById('slug_preview_value').textContent = this.value;
    updateSERP();
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>

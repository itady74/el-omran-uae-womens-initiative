<?php
require_once __DIR__ . '/../includes/functions.php';
require_auth();

$current_lang = $_GET['lang'] ?? DEFAULT_LANGUAGE;
if (!in_array($current_lang, SUPPORTED_LANGUAGES)) $current_lang = DEFAULT_LANGUAGE;
$lang = require __DIR__ . '/../includes/lang/' . $current_lang . '.php';
$_t = fn(string $k) => $lang[$k] ?? $k;

$page_title = $_t('new_article');
$user = current_user();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['auto_save'])) {
    if (!verify_csrf()) {
        $errors[] = 'Invalid request.';
    } else {
        $title = trim($_POST['title'] ?? '');
        $content = $_POST['content'] ?? '';
        $excerpt = trim($_POST['excerpt'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $status = in_array($_POST['status'] ?? '', ['draft','published','archived']) ? $_POST['status'] : 'draft';
        $language = in_array($_POST['language'] ?? '', ['en','ar']) ? $_POST['language'] : 'en';
        $seo_title = trim($_POST['seo_title'] ?? '');
        $meta_desc = trim($_POST['meta_description'] ?? '');
        $focus_kw = trim($_POST['focus_keyword'] ?? '');
        $canonical = trim($_POST['canonical_url'] ?? '');
        $index_status = $_POST['index_status'] ?? 'index';
        $follow_status = $_POST['follow_status'] ?? 'follow';

        if (empty($title)) $errors[] = 'Title is required.';
        if (empty($slug)) $slug = slugify($title, $language);
        $slug = unique_slug($slug, $language, 0, 'article');

        if ($title && empty($errors)) {
            $published_at = $status === 'published' ? date('Y-m-d H:i:s') : null;

            // Handle featured image upload
            $featured_image_id = null;
            $upload = upload_image('featured_image', 'articles');
            if ($upload) {
                $featured_image_id = $upload['id'];
                // Update alt text if provided
                if (!empty($_POST['featured_image_alt'])) {
                    $alt_stmt = db()->prepare('UPDATE media SET alt_text_en = ? WHERE id = ?');
                    $alt_stmt->execute([$_POST['featured_image_alt'], $featured_image_id]);
                }
            }

            $stmt = db()->prepare('INSERT INTO articles (status, author_id, featured_image_id, published_at) VALUES (?, ?, ?, ?)');
            $stmt->execute([$status, $user['id'], $featured_image_id, $published_at]);
            $article_id = (int) db()->lastInsertId();

            // Translation
            $stmt2 = db()->prepare('INSERT INTO article_translations (article_id, language, title, slug, excerpt, content) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt2->execute([$article_id, $language, $title, $slug, $excerpt, $content]);

            // SEO metadata
            $stmt3 = db()->prepare('INSERT INTO seo_metadata (article_id, language, seo_title, meta_description, focus_keyword, canonical_url, index_status, follow_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt3->execute([$article_id, $language, $seo_title ?: $title, $meta_desc, $focus_kw, $canonical, $index_status, $follow_status]);

            // Keywords
            if ($focus_kw) {
                $kw_stmt = db()->prepare('INSERT INTO keywords (article_id, language, keyword, type) VALUES (?, ?, ?, ?)');
                $kw_stmt->execute([$article_id, $language, $focus_kw, 'primary']);
            }

            redirect(APP_URL . '/articles/edit.php?id=' . $article_id . '&lang=' . $current_lang);
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= $_t('new_article') ?></h2>
    </div>

    <?php if ($errors): ?>
        <div style="background:var(--danger-bg);border:1px solid var(--danger-border);padding:12px 16px;border-radius:var(--radius-md);margin-bottom:var(--space-lg);color:var(--danger);">
            <?php foreach ($errors as $err): ?>
                <p><?= e($err) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" id="articleForm" action="<?= APP_URL ?>/articles/create.php?lang=<?= e($current_lang) ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="language" value="<?= e($current_lang) ?>">

        <div class="editor-layout">
            <div class="editor-main">
                <div class="form-group">
                    <label class="form-label"><?= $_t('article_title') ?></label>
                    <input type="text" name="title" id="title" class="form-input" required placeholder="<?= $_t('article_title') ?>" value="<?= e($_POST['title'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label class="form-label"><?= $_t('article_content') ?></label>
                    <div class="editor-toolbar">
                        <button type="button" class="editor-toolbar-btn" data-cmd="bold" title="<?= $_t('bold') ?>"><b>B</b></button>
                        <button type="button" class="editor-toolbar-btn" data-cmd="italic" title="<?= $_t('italic') ?>"><i>I</i></button>
                        <button type="button" class="editor-toolbar-btn" data-cmd="underline" title="<?= $_t('underline') ?>"><u>U</u></button>
                        <div class="editor-toolbar-sep"></div>
                        <button type="button" class="editor-toolbar-btn" data-cmd="formatBlock" data-val="H1" title="<?= $_t('heading1') ?>">H1</button>
                        <button type="button" class="editor-toolbar-btn" data-cmd="formatBlock" data-val="H2" title="<?= $_t('heading2') ?>">H2</button>
                        <button type="button" class="editor-toolbar-btn" data-cmd="formatBlock" data-val="H3" title="<?= $_t('heading3') ?>">H3</button>
                        <button type="button" class="editor-toolbar-btn" data-cmd="formatBlock" data-val="P" title="<?= $_t('paragraph') ?>">P</button>
                        <div class="editor-toolbar-sep"></div>
                        <button type="button" class="editor-toolbar-btn" data-cmd="insertUnorderedList" title="<?= $_t('unordered_list') ?>">&#8226;</button>
                        <button type="button" class="editor-toolbar-btn" data-cmd="insertOrderedList" title="<?= $_t('ordered_list') ?>">1.</button>
                        <button type="button" class="editor-toolbar-btn" data-cmd="formatBlock" data-val="BLOCKQUOTE" title="<?= $_t('quote') ?>">&#10077;</button>
                        <div class="editor-toolbar-sep"></div>
                        <button type="button" class="editor-toolbar-btn" data-cmd="createLink" title="<?= $_t('link') ?>"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg></button>
                        <button type="button" class="editor-toolbar-btn" data-cmd="viewSource" title="<?= $_t('source_code') ?>"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg></button>
                    </div>
                    <div contenteditable="true" class="editor-content rich-editor" id="editor_content" dir="<?= $current_lang === 'ar' ? 'rtl' : 'ltr' ?>"><?= $_POST['content'] ?? '' ?></div>
                    <textarea name="content" id="content_hidden" style="display:none"></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label"><?= $_t('article_excerpt') ?></label>
                    <textarea name="excerpt" class="form-textarea" rows="3" placeholder="<?= $_t('article_excerpt') ?>"><?= e($_POST['excerpt'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label"><?= $_t('slug') ?></label>
                    <div style="display:flex;gap:var(--space-sm);align-items:center;">
                        <input type="text" name="slug" id="slug" class="form-input" data-slug-source="title" data-lang="<?= e($current_lang) ?>" value="<?= e($_POST['slug'] ?? '') ?>" placeholder="<?= $_t('slug') ?>">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('slug').value=generateSlug(document.getElementById('title').value,'<?= $current_lang ?>');document.getElementById('slug').dataset.manualEdit='true';"><?= $_t('generate_slug') ?></button>
                    </div>
                    <div class="form-hint"><?= $_t('preview_url') ?>: <span id="slug_preview" style="color:var(--accent)">/blog/</span><span id="slug_preview_value"></span></div>
                </div>
            </div>

            <div class="editor-sidebar">
                <div class="card" style="margin-bottom:var(--space-lg);">
                    <div class="card-header">
                        <h3 class="card-title" style="font-size:0.9375rem"><?= $_t('article_status') ?></h3>
                    </div>
                    <div class="form-group">
                        <select name="status" class="form-select">
                            <option value="draft"><?= $_t('draft') ?></option>
                            <option value="published"><?= $_t('published') ?></option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><?= $_t('article_image') ?></label>
                        <input type="file" name="featured_image" class="form-input" accept="image/*">
                        <input type="text" name="featured_image_alt" class="form-input" placeholder="<?= $_t('alt_text_en') ?>" style="margin-top:var(--space-sm)">
                    </div>
                    <button type="submit" class="btn btn-primary" style="width:100%"><?= $_t('save') ?> <?= $_t('articles') ?></button>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title" style="font-size:0.9375rem"><?= $_t('seo_metadata') ?></h3>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><?= $_t('seo_title') ?></label>
                        <input type="text" name="seo_title" id="seo_title" class="form-input" data-maxlength="60" value="<?= e($_POST['seo_title'] ?? '') ?>" placeholder="<?= $_t('seo_title') ?>">
                        <div class="char-count">0 / 60</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><?= $_t('meta_description') ?></label>
                        <textarea name="meta_description" id="meta_description" class="form-textarea" rows="3" data-maxlength="160" placeholder="<?= $_t('meta_description') ?>"><?= e($_POST['meta_description'] ?? '') ?></textarea>
                        <div class="char-count">0 / 160</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><?= $_t('focus_keyword') ?></label>
                        <input type="text" name="focus_keyword" class="form-input" value="<?= e($_POST['focus_keyword'] ?? '') ?>" placeholder="<?= $_t('focus_keyword') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label"><?= $_t('canonical_url') ?></label>
                        <input type="url" name="canonical_url" class="form-input" value="<?= e($_POST['canonical_url'] ?? '') ?>" placeholder="https://alomran.ae/blog/...">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label"><?= $_t('index_status') ?></label>
                            <select name="index_status" class="form-select">
                                <option value="index"><?= $_t('index') ?></option>
                                <option value="noindex"><?= $_t('noindex') ?></option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label"><?= $_t('follow_status') ?></label>
                            <select name="follow_status" class="form-select">
                                <option value="follow"><?= $_t('follow') ?></option>
                                <option value="nofollow"><?= $_t('nofollow') ?></option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
// Sync contenteditable to hidden textarea before submit
document.getElementById('articleForm').addEventListener('submit', function() {
    document.getElementById('content_hidden').value = document.getElementById('editor_content').innerHTML;
});

// Slug preview
const slugInput = document.getElementById('slug');
const slugPreview = document.getElementById('slug_preview_value');
if (slugInput && slugPreview) {
    slugInput.addEventListener('input', function() {
        slugPreview.textContent = this.value;
    });
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>

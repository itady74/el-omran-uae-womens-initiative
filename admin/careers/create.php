<?php
require_once __DIR__ . '/../includes/functions.php';
require_auth();

$current_lang = $_GET['lang'] ?? DEFAULT_LANGUAGE;
if (!in_array($current_lang, SUPPORTED_LANGUAGES)) $current_lang = DEFAULT_LANGUAGE;
$lang = require __DIR__ . '/../includes/lang/' . $current_lang . '.php';
$_t = fn(string $k) => $lang[$k] ?? $k;

$page_title = $_t('new_career');
$user = current_user();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) { $errors[] = 'Invalid request.'; } else {
        $title = trim($_POST['title'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $emp_type = $_POST['employment_type'] ?? 'full-time';
        $desc = $_POST['description'] ?? '';
        $reqs = $_POST['requirements'] ?? '';
        $benefits = $_POST['benefits'] ?? '';
        $status = in_array($_POST['status'] ?? '', ['draft','published','closed']) ? $_POST['status'] : 'draft';
        $language = in_array($_POST['language'] ?? '', ['en','ar']) ? $_POST['language'] : 'en';

        if (empty($title)) $errors[] = 'Title is required.';
        if (empty($slug)) $slug = slugify($title, $language);
        $slug = unique_slug($slug, $language, 0, 'career');

        if ($title && empty($errors)) {
            $published_at = $status === 'published' ? date('Y-m-d H:i:s') : null;
            $stmt = db()->prepare('INSERT INTO careers (status, author_id, published_at) VALUES (?, ?, ?)');
            $stmt->execute([$status, $user['id'], $published_at]);
            $career_id = (int) db()->lastInsertId();

            $stmt2 = db()->prepare('INSERT INTO career_translations (career_id, language, title, slug, location, employment_type, description, requirements, benefits) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt2->execute([$career_id, $language, $title, $slug, $location, $emp_type, $desc, $reqs, $benefits]);

            redirect(APP_URL . '/careers/edit.php?id=' . $career_id);
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<?php if ($errors): ?>
    <div style="background:var(--danger-bg);border:1px solid var(--danger-border);padding:12px 16px;border-radius:var(--radius-md);margin-bottom:var(--space-lg);color:var(--danger);">
        <?php foreach ($errors as $err): ?>
            <p><?= e($err) ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<form method="POST" id="careerForm">
    <?= csrf_field() ?>
    <input type="hidden" name="language" value="<?= e($current_lang) ?>">

    <div class="editor-layout">
        <div class="editor-main">
            <div class="card">
                <div class="form-group">
                    <label class="form-label"><?= $_t('job_title') ?></label>
                    <input type="text" name="title" id="title" class="form-input" required placeholder="<?= $_t('job_title') ?>">
                </div>

                <div class="form-group">
                    <label class="form-label"><?= $_t('slug') ?></label>
                    <div style="display:flex;gap:var(--space-sm);align-items:center;">
                        <input type="text" name="slug" id="slug" class="form-input" data-slug-source="title" data-lang="<?= e($current_lang) ?>">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('slug').value=generateSlug(document.getElementById('title').value,'<?= $current_lang ?>');document.getElementById('slug').dataset.manualEdit='true';">Generate</button>
                    </div>
                    <div class="form-hint">URL: <?= e(get_setting('site_url')) ?>/<?= $current_lang === 'ar' ? '' : $current_lang . '/' ?>careers/<span id="slug_preview_value"></span></div>
                </div>

                <div class="form-group">
                    <label class="form-label"><?= $_t('description') ?></label>
                    <div class="editor-toolbar">
                        <button type="button" class="editor-toolbar-btn" data-cmd="bold" data-target="editor_desc" title="Bold"><b>B</b></button>
                        <button type="button" class="editor-toolbar-btn" data-cmd="italic" data-target="editor_desc" title="Italic"><i>I</i></button>
                        <button type="button" class="editor-toolbar-btn" data-cmd="underline" data-target="editor_desc" title="Underline"><u>U</u></button>
                        <div class="editor-toolbar-sep"></div>
                        <button type="button" class="editor-toolbar-btn" data-cmd="formatBlock" data-val="H2" data-target="editor_desc">H2</button>
                        <button type="button" class="editor-toolbar-btn" data-cmd="formatBlock" data-val="H3" data-target="editor_desc">H3</button>
                        <button type="button" class="editor-toolbar-btn" data-cmd="formatBlock" data-val="P" data-target="editor_desc">P</button>
                        <div class="editor-toolbar-sep"></div>
                        <button type="button" class="editor-toolbar-btn" data-cmd="insertUnorderedList" data-target="editor_desc">&#8226;</button>
                        <button type="button" class="editor-toolbar-btn" data-cmd="insertOrderedList" data-target="editor_desc">1.</button>
                    </div>
                    <div contenteditable="true" class="editor-content rich-editor" id="editor_desc" dir="<?= $current_lang === 'ar' ? 'rtl' : 'ltr' ?>" style="min-height:200px"></div>
                    <textarea name="description" id="content_desc" style="display:none"></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label"><?= $_t('requirements') ?></label>
                    <div class="editor-toolbar">
                        <button type="button" class="editor-toolbar-btn" data-cmd="bold" data-target="editor_reqs" title="Bold"><b>B</b></button>
                        <button type="button" class="editor-toolbar-btn" data-cmd="italic" data-target="editor_reqs" title="Italic"><i>I</i></button>
                        <div class="editor-toolbar-sep"></div>
                        <button type="button" class="editor-toolbar-btn" data-cmd="insertUnorderedList" data-target="editor_reqs">&#8226;</button>
                        <button type="button" class="editor-toolbar-btn" data-cmd="insertOrderedList" data-target="editor_reqs">1.</button>
                    </div>
                    <div contenteditable="true" class="editor-content rich-editor" id="editor_reqs" dir="<?= $current_lang === 'ar' ? 'rtl' : 'ltr' ?>" style="min-height:150px"></div>
                    <textarea name="requirements" id="content_reqs" style="display:none"></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label"><?= $_t('benefits') ?></label>
                    <div class="editor-toolbar">
                        <button type="button" class="editor-toolbar-btn" data-cmd="bold" data-target="editor_benefits" title="Bold"><b>B</b></button>
                        <button type="button" class="editor-toolbar-btn" data-cmd="italic" data-target="editor_benefits" title="Italic"><i>I</i></button>
                        <div class="editor-toolbar-sep"></div>
                        <button type="button" class="editor-toolbar-btn" data-cmd="insertUnorderedList" data-target="editor_benefits">&#8226;</button>
                        <button type="button" class="editor-toolbar-btn" data-cmd="insertOrderedList" data-target="editor_benefits">1.</button>
                    </div>
                    <div contenteditable="true" class="editor-content rich-editor" id="editor_benefits" dir="<?= $current_lang === 'ar' ? 'rtl' : 'ltr' ?>" style="min-height:150px"></div>
                    <textarea name="benefits" id="content_benefits" style="display:none"></textarea>
                </div>
            </div>
        </div>

        <div class="editor-sidebar">
            <div class="card" style="margin-bottom:var(--space-lg)">
                <div class="card-header">
                    <h3 class="card-title" style="font-size:0.9375rem"><?= $_t('article_status') ?></h3>
                </div>
                <div class="form-group">
                    <select name="status" class="form-select">
                        <option value="draft"><?= $_t('draft') ?></option>
                        <option value="published"><?= $_t('published') ?></option>
                    </select>
                </div>
            </div>

            <div class="card" style="margin-bottom:var(--space-lg)">
                <div class="card-header">
                    <h3 class="card-title" style="font-size:0.9375rem"><?= $_t('job_details') ?></h3>
                </div>
                <div class="form-group">
                    <label class="form-label"><?= $_t('location') ?></label>
                    <input type="text" name="location" class="form-input" placeholder="Al Ain, UAE">
                </div>
                <div class="form-group">
                    <label class="form-label"><?= $_t('employment_type') ?></label>
                    <select name="employment_type" class="form-select">
                        <option value="full-time"><?= $_t('full_time') ?></option>
                        <option value="part-time"><?= $_t('part_time') ?></option>
                        <option value="contract"><?= $_t('contract') ?></option>
                        <option value="internship"><?= $_t('internship') ?></option>
                    </select>
                </div>
            </div>

            <div style="display:flex;gap:var(--space-sm)">
                <button type="submit" class="btn btn-primary" style="flex:1"><?= $_t('save') ?></button>
                <a href="<?= APP_URL ?>/careers/" class="btn btn-secondary"><?= $_t('back') ?></a>
            </div>
        </div>
    </div>
</form>

<script>
document.getElementById('careerForm').addEventListener('submit', function() {
    document.getElementById('content_desc').value = document.getElementById('editor_desc').innerHTML;
    document.getElementById('content_reqs').value = document.getElementById('editor_reqs').innerHTML;
    document.getElementById('content_benefits').value = document.getElementById('editor_benefits').innerHTML;
});

const slugInput = document.getElementById('slug');
const slugPreview = document.getElementById('slug_preview_value');
if (slugInput && slugPreview) {
    slugInput.addEventListener('input', function() { slugPreview.textContent = this.value; });
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>

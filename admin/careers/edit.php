<?php
require_once __DIR__ . '/../includes/functions.php';
require_auth();

$career_id = (int)($_GET['id'] ?? 0);
if ($career_id <= 0) redirect(APP_URL . '/careers/');

$current_lang = $_GET['lang'] ?? DEFAULT_LANGUAGE;
if (!in_array($current_lang, SUPPORTED_LANGUAGES)) $current_lang = DEFAULT_LANGUAGE;
$lang = require __DIR__ . '/../includes/lang/' . $current_lang . '.php';
$_t = fn(string $k) => $lang[$k] ?? $k;

$stmt = db()->prepare('SELECT * FROM careers WHERE id = ?');
$stmt->execute([$career_id]);
$career = $stmt->fetch();
if (!$career) redirect(APP_URL . '/careers/');

$stmt2 = db()->prepare('SELECT * FROM career_translations WHERE career_id = ? AND language = ?');
$stmt2->execute([$career_id, $current_lang]);
$t = $stmt2->fetch();

$page_title = $t['title'] ?? $_t('edit') . ' ' . $_t('careers');
$errors = [];

// Handle auto-save (AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
    header('Content-Type: application/json');
    try {
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $title = trim($data['title'] ?? '');
        $slug = trim($data['slug'] ?? '');
        $location = trim($data['location'] ?? '');
        $emp_type = $data['employment_type'] ?? 'full-time';
        $desc = $data['description'] ?? '';
        $reqs = $data['requirements'] ?? '';
        $benefits = $data['benefits'] ?? '';
        $status = in_array($data['status'] ?? '', ['draft','published','closed']) ? $data['status'] : 'draft';

        if (empty($slug) && !empty($title)) $slug = slugify($title, $current_lang);
        $slug = unique_slug($slug, $current_lang, $career_id, 'career');

        if ($t) {
            db()->prepare('UPDATE career_translations SET title=?, slug=?, location=?, employment_type=?, description=?, requirements=?, benefits=? WHERE career_id=? AND language=?')
                ->execute([$title, $slug, $location, $emp_type, $desc, $reqs, $benefits, $career_id, $current_lang]);
        } else {
            db()->prepare('INSERT INTO career_translations (career_id, language, title, slug, location, employment_type, description, requirements, benefits) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)')
                ->execute([$career_id, $current_lang, $title, $slug, $location, $emp_type, $desc, $reqs, $benefits]);
        }

        $published_at = ($status === 'published' && $career['status'] !== 'published') ? date('Y-m-d H:i:s') : $career['published_at'];
        if ($status === 'published' && !$published_at) $published_at = date('Y-m-d H:i:s');
        db()->prepare('UPDATE careers SET status=?, published_at=COALESCE(?, published_at) WHERE id=?')
            ->execute([$status, $published_at, $career_id]);

        echo json_encode(['success' => true, 'career_id' => $career_id]);
    } catch (Exception $ex) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Save failed']);
    }
    exit;
}

// Handle standard POST
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

        if (empty($slug) && !empty($title)) $slug = slugify($title, $current_lang);
        $slug = unique_slug($slug, $current_lang, $career_id, 'career');

        if ($t) {
            db()->prepare('UPDATE career_translations SET title=?, slug=?, location=?, employment_type=?, description=?, requirements=?, benefits=? WHERE career_id=? AND language=?')
                ->execute([$title, $slug, $location, $emp_type, $desc, $reqs, $benefits, $career_id, $current_lang]);
        } else {
            db()->prepare('INSERT INTO career_translations (career_id, language, title, slug, location, employment_type, description, requirements, benefits) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)')
                ->execute([$career_id, $current_lang, $title, $slug, $location, $emp_type, $desc, $reqs, $benefits]);
        }

        $published_at = ($status === 'published' && $career['status'] !== 'published') ? date('Y-m-d H:i:s') : $career['published_at'];
        if ($status === 'published' && !$published_at) $published_at = date('Y-m-d H:i:s');
        db()->prepare('UPDATE careers SET status=?, published_at=COALESCE(?, published_at) WHERE id=?')
            ->execute([$status, $published_at, $career_id]);

        redirect(APP_URL . '/careers/edit.php?id=' . $career_id . '&saved=1');
    }
}

$saved = isset($_GET['saved']);

include __DIR__ . '/../includes/header.php';
?>

<?php if ($saved): ?>
<script>document.addEventListener('DOMContentLoaded', () => showToast('<?= $_t('saved_successfully') ?>', 'success'));</script>
<?php endif; ?>

<form method="POST" id="careerForm">
    <?= csrf_field() ?>

    <div class="editor-layout">
        <div class="editor-main">
            <div class="card">
                <div class="form-group">
                    <label class="form-label"><?= $_t('job_title') ?></label>
                    <input type="text" name="title" id="title" class="form-input" required value="<?= e($t['title'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label class="form-label"><?= $_t('slug') ?></label>
                    <div style="display:flex;gap:var(--space-sm);align-items:center;">
                        <input type="text" name="slug" id="slug" class="form-input" data-slug-source="title" data-lang="<?= e($current_lang) ?>" value="<?= e($t['slug'] ?? '') ?>">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('slug').value=generateSlug(document.getElementById('title').value,'<?= $current_lang ?>');document.getElementById('slug').dataset.manualEdit='true';">Generate</button>
                    </div>
                    <div class="form-hint">URL: <?= e(get_setting('site_url')) ?>/<?= $current_lang === 'ar' ? '' : $current_lang . '/' ?>careers/<span id="slug_preview_value"><?= e($t['slug'] ?? '') ?></span></div>
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
                    <div contenteditable="true" class="editor-content rich-editor" id="editor_desc" dir="<?= $current_lang === 'ar' ? 'rtl' : 'ltr' ?>" style="min-height:200px"><?= $t['description'] ?? '' ?></div>
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
                    <div contenteditable="true" class="editor-content rich-editor" id="editor_reqs" dir="<?= $current_lang === 'ar' ? 'rtl' : 'ltr' ?>" style="min-height:150px"><?= $t['requirements'] ?? '' ?></div>
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
                    <div contenteditable="true" class="editor-content rich-editor" id="editor_benefits" dir="<?= $current_lang === 'ar' ? 'rtl' : 'ltr' ?>" style="min-height:150px"><?= $t['benefits'] ?? '' ?></div>
                    <textarea name="benefits" id="content_benefits" style="display:none"></textarea>
                </div>
            </div>
        </div>

        <div class="editor-sidebar">
            <div class="card" style="margin-bottom:var(--space-lg)">
                <div class="card-header">
                    <h3 class="card-title" style="font-size:0.9375rem"><?= $_t('article_status') ?></h3>
                    <div id="saveStatus" style="font-size:0.75rem;color:var(--text-muted)"></div>
                </div>
                <div class="form-group">
                    <select name="status" id="status" class="form-select">
                        <option value="draft" <?= $career['status'] === 'draft' ? 'selected' : '' ?>><?= $_t('draft') ?></option>
                        <option value="published" <?= $career['status'] === 'published' ? 'selected' : '' ?>><?= $_t('published') ?></option>
                        <option value="closed" <?= $career['status'] === 'closed' ? 'selected' : '' ?>><?= $_t('closed') ?></option>
                    </select>
                </div>
                <?php if ($career['published_at']): ?>
                    <div class="form-hint" style="margin-bottom:var(--space-md)"><?= $_t('article_published') ?>: <?= date('M d, Y H:i', strtotime($career['published_at'])) ?></div>
                <?php endif; ?>
            </div>

            <div class="card" style="margin-bottom:var(--space-lg)">
                <div class="card-header">
                    <h3 class="card-title" style="font-size:0.9375rem"><?= $_t('job_details') ?></h3>
                </div>
                <div class="form-group">
                    <label class="form-label"><?= $_t('location') ?></label>
                    <input type="text" name="location" id="location" class="form-input" value="<?= e($t['location'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label"><?= $_t('employment_type') ?></label>
                    <select name="employment_type" id="employment_type" class="form-select">
                        <option value="full-time" <?= ($t['employment_type'] ?? '') === 'full-time' ? 'selected' : '' ?>><?= $_t('full_time') ?></option>
                        <option value="part-time" <?= ($t['employment_type'] ?? '') === 'part-time' ? 'selected' : '' ?>><?= $_t('part_time') ?></option>
                        <option value="contract" <?= ($t['employment_type'] ?? '') === 'contract' ? 'selected' : '' ?>><?= $_t('contract') ?></option>
                        <option value="internship" <?= ($t['employment_type'] ?? '') === 'internship' ? 'selected' : '' ?>><?= $_t('internship') ?></option>
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
// Sync all contenteditables to hidden textareas before submit
document.getElementById('careerForm').addEventListener('submit', function() {
    document.getElementById('content_desc').value = document.getElementById('editor_desc').innerHTML;
    document.getElementById('content_reqs').value = document.getElementById('editor_reqs').innerHTML;
    document.getElementById('content_benefits').value = document.getElementById('editor_benefits').innerHTML;
});

// Slug preview
const slugInput = document.getElementById('slug');
const slugPreview = document.getElementById('slug_preview_value');
if (slugInput && slugPreview) {
    slugInput.addEventListener('input', function() { slugPreview.textContent = this.value; });
}

// Auto-save via AJAX
let autoSaveTimer;
function autoSaveCareer() {
    clearTimeout(autoSaveTimer);
    autoSaveTimer = setTimeout(function() {
        const statusEl = document.getElementById('saveStatus');
        if (statusEl) statusEl.textContent = 'Saving...';

        document.getElementById('content_desc').value = document.getElementById('editor_desc').innerHTML;
        document.getElementById('content_reqs').value = document.getElementById('editor_reqs').innerHTML;
        document.getElementById('content_benefits').value = document.getElementById('editor_benefits').innerHTML;

        const payload = {
            title: document.getElementById('title').value,
            slug: document.getElementById('slug').value,
            location: document.getElementById('location').value,
            employment_type: document.getElementById('employment_type').value,
            description: document.getElementById('content_desc').value,
            requirements: document.getElementById('content_reqs').value,
            benefits: document.getElementById('content_benefits').value,
            status: document.getElementById('status').value
        };

        fetch(window.location.href, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-Token': CSRF_TOKEN },
            body: JSON.stringify(payload)
        }).then(r => r.json()).then(data => {
            if (data.success) {
                if (statusEl) statusEl.textContent = 'Saved ' + new Date().toLocaleTimeString();
            } else {
                if (statusEl) statusEl.textContent = 'Unable to save';
            }
        }).catch(() => {
            if (statusEl) statusEl.textContent = 'Unable to save';
        });
    }, 1500);
}

// Attach auto-save listeners
['title', 'slug', 'location', 'employment_type', 'status'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.addEventListener('input', autoSaveCareer);
    if (el) el.addEventListener('change', autoSaveCareer);
});
document.getElementById('editor_desc')?.addEventListener('input', autoSaveCareer);
document.getElementById('editor_reqs')?.addEventListener('input', autoSaveCareer);
document.getElementById('editor_benefits')?.addEventListener('input', autoSaveCareer);
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>

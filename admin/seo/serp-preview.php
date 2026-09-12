<?php
require_once __DIR__ . '/../includes/functions.php';
require_auth();

$current_lang = $_GET['lang'] ?? DEFAULT_LANGUAGE;
$lang = require __DIR__ . '/../includes/lang/' . $current_lang . '.php';
$_t = fn(string $k) => $lang[$k] ?? $k;

$page_title = $_t('serp_preview');

$articles = db()->query('
    SELECT a.id, at.title, at.slug, at.seo_title, at.meta_description, at.language
    FROM articles a
    JOIN article_translations at ON a.id = at.article_id
    ORDER BY a.updated_at DESC
    LIMIT 20
')->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= $_t('serp_preview') ?></h2>
    </div>
    <p style="color:var(--text-secondary);margin-bottom:var(--space-xl)">Preview how your articles appear in Google search results. Select an article to preview its SERP snippet.</p>

    <?php if (empty($articles)): ?>
        <div class="empty-state"><p>No articles found.</p></div>
    <?php else: ?>
        <div class="form-group">
            <label class="form-label">Select Article</label>
            <select id="articleSelect" class="form-select" style="max-width:400px">
                <option value="">-- Choose an article --</option>
                <?php foreach ($articles as $a): ?>
                    <option value="<?= $a['id'] ?>" data-title="<?= e($a['seo_title'] ?: $a['title']) ?>" data-desc="<?= e($a['meta_description'] ?? '') ?>" data-slug="<?= e($a['slug']) ?>" data-lang="<?= $a['language'] ?>">
                        [<?= strtoupper($a['language']) ?>] <?= e($a['title']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-xl);margin-top:var(--space-xl)">
            <div>
                <h3 style="font-size:0.875rem;font-weight:600;margin-bottom:var(--space-md)">Desktop Preview</h3>
                <div class="serp-preview" id="desktopPreview">
                    <div class="serp-title" id="dTitle">Article Title</div>
                    <div class="serp-url" id="dUrl"><?= e(get_setting('site_url')) ?>/blog/</div>
                    <div class="serp-desc" id="dDesc">Meta description will appear here...</div>
                </div>
            </div>
            <div>
                <h3 style="font-size:0.875rem;font-weight:600;margin-bottom:var(--space-md)">Mobile Preview</h3>
                <div class="serp-preview" style="max-width:360px" id="mobilePreview">
                    <div class="serp-title" style="font-size:1rem" id="mTitle">Article Title</div>
                    <div class="serp-url" id="mUrl"><?= e(get_setting('site_url')) ?>/blog/</div>
                    <div class="serp-desc" id="mDesc">Meta description will appear here...</div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
document.getElementById('articleSelect')?.addEventListener('change', function() {
    const opt = this.options[this.selectedIndex];
    if (!opt.value) return;
    const title = opt.dataset.title;
    const desc = opt.dataset.desc || 'No meta description set.';
    const slug = opt.dataset.slug;
    const lang = opt.dataset.lang;
    const site = '<?= e(get_setting("site_url")) ?>';
    const url = site + '/' + (lang === 'ar' ? '' : lang + '/') + 'blog/' + slug;

    document.getElementById('dTitle').textContent = title;
    document.getElementById('mTitle').textContent = title;
    document.getElementById('dUrl').textContent = url;
    document.getElementById('mUrl').textContent = url;
    document.getElementById('dDesc').textContent = desc;
    document.getElementById('mDesc').textContent = desc;
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>

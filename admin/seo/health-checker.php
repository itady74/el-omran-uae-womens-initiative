<?php
/**
 * SEO Health Checker - Standalone page with detailed per-article analysis
 */
require_once __DIR__ . '/../includes/functions.php';
require_auth();

$current_lang = $_GET['lang'] ?? DEFAULT_LANGUAGE;
if (!in_array($current_lang, SUPPORTED_LANGUAGES)) $current_lang = DEFAULT_LANGUAGE;
$lang = require __DIR__ . '/../includes/lang/' . $current_lang . '.php';
$_t = fn(string $k) => $lang[$k] ?? $k;

$page_title = $_t('seo_health');

$article_id = (int)($_GET['article_id'] ?? 0);
$lang_filter = $_GET['lang_filter'] ?? '';

// Get all articles with translations
$query = '
    SELECT a.id, a.status, at.title, at.language, at.slug
    FROM articles a
    JOIN article_translations at ON a.id = at.article_id
    ORDER BY a.updated_at DESC
';
$articles = db()->query($query)->fetchAll();

// Calculate scores for each
$seo_results = [];
foreach ($articles as $a) {
    if ($lang_filter && $a['language'] !== $lang_filter) continue;
    $score_data = calculate_seo_score((int)$a['id'], $a['language']);
    $a['score'] = $score_data['score'];
    $a['issues'] = $score_data['issues'];
    $seo_results[] = $a;
}

// Sort by score ascending (worst first)
usort($seo_results, fn($a, $b) => $a['score'] <=> $b['score']);

// Overall stats
$total_checked = count($seo_results);
$avg_score = $total_checked > 0 ? round(array_sum(array_column($seo_results, 'score')) / $total_checked) : 0;
$critical_total = 0;
$warnings_total = 0;
foreach ($seo_results as $sd) {
    $critical_total += count($sd['issues']['critical']);
    $warnings_total += count($sd['issues']['warning']);
}

include __DIR__ . '/../includes/header.php';
?>

<div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));">
    <div class="stat-card">
        <div class="stat-icon blue"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg></div>
        <div class="stat-info"><div class="stat-label"><?= $_t('avg_seo_score') ?></div><div class="stat-value"><?= $avg_score ?>/100</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg></div>
        <div class="stat-info"><div class="stat-label"><?= $_t('articles') ?></div><div class="stat-value"><?= $total_checked ?></div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg></div>
        <div class="stat-info"><div class="stat-label"><?= $_t('critical_issues') ?></div><div class="stat-value"><?= $critical_total ?></div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon amber"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg></div>
        <div class="stat-info"><div class="stat-label"><?= $_t('warnings') ?></div><div class="stat-value"><?= $warnings_total ?></div></div>
    </div>
</div>

<div class="filter-bar">
    <form method="GET" style="display:flex;gap:var(--space-sm);flex-wrap:wrap;width:100%;">
        <select name="lang_filter" class="form-select" style="width:auto;">
            <option value=""><?= $_t('all') ?> <?= $_t('language') ?></option>
            <option value="en" <?= $lang_filter==='en'?'selected':'' ?>><?= $_t('english') ?></option>
            <option value="ar" <?= $lang_filter==='ar'?'selected':'' ?>><?= $_t('arabic') ?></option>
        </select>
        <button type="submit" class="btn btn-secondary"><?= $_t('filter') ?></button>
    </form>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= $_t('seo_health') ?></h2>
    </div>

    <?php if (empty($seo_results)): ?>
        <div class="empty-state"><p><?= $_t('no_results') ?></p></div>
    <?php else: ?>
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th><?= $_t('article_title') ?></th>
                        <th><?= $_t('language') ?></th>
                        <th><?= $_t('article_status') ?></th>
                        <th><?= $_t('seo_score') ?></th>
                        <th><?= $_t('critical_issues') ?></th>
                        <th><?= $_t('warnings') ?></th>
                        <th><?= $_t('actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($seo_results as $sd):
                        $cls = $sd['score'] >= 80 ? 'published' : ($sd['score'] >= 50 ? 'draft' : 'archived');
                    ?>
                        <tr>
                            <td><strong><?= e($sd['title'] ?? 'Untitled') ?></strong><br><small style="color:var(--text-muted)">/<?= e($sd['slug'] ?? '') ?></small></td>
                            <td><span class="badge badge-<?= $sd['language']==='ar'?'published':'draft' ?>"><?= strtoupper($sd['language']) ?></span></td>
                            <td><span class="badge badge-<?= $sd['status'] ?>"><?= $_t($sd['status']) ?></span></td>
                            <td><span class="badge badge-<?= $cls ?>"><?= $sd['score'] ?>/100</span></td>
                            <td>
                                <?php if (!empty($sd['issues']['critical'])): ?>
                                    <span style="color:var(--danger)"><?= count($sd['issues']['critical']) ?></span>
                                <?php else: ?>
                                    <span style="color:var(--success)">0</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($sd['issues']['warning'])): ?>
                                    <span style="color:var(--warning)"><?= count($sd['issues']['warning']) ?></span>
                                <?php else: ?>
                                    <span style="color:var(--success)">0</span>
                                <?php endif; ?>
                            </td>
                            <td><a href="<?= APP_URL ?>/articles/edit.php?id=<?= $sd['id'] ?>&lang=<?= $sd['language'] ?>" class="btn btn-ghost btn-sm"><?= $_t('edit') ?></a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

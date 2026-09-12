<?php
require_once __DIR__ . '/../includes/functions.php';
require_auth();

$current_lang = $_GET['lang'] ?? DEFAULT_LANGUAGE;
$lang = require __DIR__ . '/../includes/lang/' . $current_lang . '.php';
$_t = fn(string $k) => $lang[$k] ?? $k;

$page_title = $_t('seo_tools');

// Get all articles with their SEO data
$articles = db()->query('
    SELECT a.id, a.status, at.title, at.slug, at.language, at.seo_title, at.meta_description, at.focus_keyword
    FROM articles a
    LEFT JOIN article_translations at ON a.id = at.article_id
    WHERE at.title IS NOT NULL
    ORDER BY a.updated_at DESC
    LIMIT 50
')->fetchAll();

$seo_data = [];
foreach ($articles as $a) {
    $score = calculate_seo_score((int)$a['id'], $a['language'] ?? 'en');
    $a['seo_score'] = $score['score'];
    $a['issues'] = $score['issues'];
    $seo_data[] = $a;
}

// Overall stats
$total_checked = count($seo_data);
$avg_score = $total_checked > 0 ? round(array_sum(array_column($seo_data, 'seo_score')) / $total_checked) : 0;
$critical_total = 0;
$warnings_total = 0;
foreach ($seo_data as $sd) {
    $critical_total += count($sd['issues']['critical']);
    $warnings_total += count($sd['issues']['warning']);
}

include __DIR__ . '/../includes/header.php';
?>

<div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));">
    <div class="stat-card">
        <div class="stat-icon blue"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg></div>
        <div class="stat-info"><div class="stat-label">Avg Score</div><div class="stat-value"><?= $avg_score ?>/100</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg></div>
        <div class="stat-info"><div class="stat-label">Articles</div><div class="stat-value"><?= $total_checked ?></div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg></div>
        <div class="stat-info"><div class="stat-label">Critical</div><div class="stat-value"><?= $critical_total ?></div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon amber"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg></div>
        <div class="stat-info"><div class="stat-label">Warnings</div><div class="stat-value"><?= $warnings_total ?></div></div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">SEO Health Overview</h2>
    </div>

    <?php if (empty($seo_data)): ?>
        <div class="empty-state"><p>No articles to analyze yet.</p></div>
    <?php else: ?>
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Language</th>
                        <th>Status</th>
                        <th>SEO Score</th>
                        <th>Issues</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($seo_data as $sd):
                        $cls = $sd['seo_score'] >= 80 ? 'published' : ($sd['seo_score'] >= 50 ? 'draft' : 'archived');
                    ?>
                        <tr>
                            <td><strong><?= e($sd['title'] ?? 'Untitled') ?></strong></td>
                            <td><span class="badge badge-<?= $sd['language']==='ar'?'published':'draft' ?>"><?= strtoupper($sd['language']) ?></span></td>
                            <td><span class="badge badge-<?= $sd['status'] ?>"><?= $sd['status'] ?></span></td>
                            <td><span class="badge badge-<?= $cls ?>"><?= $sd['seo_score'] ?>/100</span></td>
                            <td>
                                <?php if (!empty($sd['issues']['critical'])): ?>
                                    <span style="color:var(--danger)"><?= count($sd['issues']['critical']) ?> critical</span>
                                <?php endif; ?>
                                <?php if (!empty($sd['issues']['warning'])): ?>
                                    <span style="color:var(--warning);margin-left:8px"><?= count($sd['issues']['warning']) ?> warnings</span>
                                <?php endif; ?>
                            </td>
                            <td><a href="<?= APP_URL ?>/articles/edit.php?id=<?= $sd['id'] ?>&lang=<?= $sd['language'] ?>" class="btn btn-ghost btn-sm">Edit</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

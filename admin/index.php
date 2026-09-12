<?php
require_once __DIR__ . '/includes/functions.php';
require_auth();

$current_lang = $_GET['lang'] ?? (DEFAULT_LANGUAGE);
if (!in_array($current_lang, SUPPORTED_LANGUAGES)) $current_lang = DEFAULT_LANGUAGE;
$lang = require __DIR__ . '/includes/lang/' . $current_lang . '.php';
$_t = fn(string $k) => $lang[$k] ?? $k;

$page_title = $_t('dashboard');

// Stats
$total_articles = (int) db()->query('SELECT COUNT(*) FROM articles')->fetchColumn();
$published = (int) db()->query("SELECT COUNT(*) FROM articles WHERE status='published'")->fetchColumn();
$drafts = (int) db()->query("SELECT COUNT(*) FROM articles WHERE status='draft'")->fetchColumn();
$active_careers = (int) db()->query("SELECT COUNT(*) FROM careers WHERE status='published'")->fetchColumn();

// Average SEO score
$avg_seo = 0;
$articles = db()->query('SELECT id FROM articles')->fetchAll();
if ($articles) {
    $total_score = 0;
    foreach ($articles as $a) {
        $score_data = calculate_seo_score((int)$a['id'], 'en');
        $total_score += $score_data['score'];
    }
    $avg_seo = round($total_score / count($articles));
}

// Recent articles
$recent = db()->query('
    SELECT a.*, at.title, at.slug, at.language,
           u.name as author_name,
           (SELECT seo_score FROM (SELECT article_id, language,
                CASE WHEN seo_title != "" THEN 10 ELSE 0 END +
                CASE WHEN meta_description != "" THEN 10 ELSE 0 END as score
                FROM seo_metadata) sm WHERE sm.article_id = a.id AND sm.language = at.language LIMIT 1) as seo_score
    FROM articles a
    LEFT JOIN article_translations at ON a.id = at.article_id AND at.language = "' . $current_lang . '"
    LEFT JOIN users u ON a.author_id = u.id
    ORDER BY a.updated_at DESC
    LIMIT 10
')->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon blue">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        </div>
        <div class="stat-info">
            <div class="stat-label"><?= $_t('total_articles') ?></div>
            <div class="stat-value"><?= $total_articles ?></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
        </div>
        <div class="stat-info">
            <div class="stat-label"><?= $_t('published_articles') ?></div>
            <div class="stat-value"><?= $published ?></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon amber">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
        </div>
        <div class="stat-info">
            <div class="stat-label"><?= $_t('draft_articles') ?></div>
            <div class="stat-value"><?= $drafts ?></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
        </div>
        <div class="stat-info">
            <div class="stat-label"><?= $_t('active_careers') ?></div>
            <div class="stat-value"><?= $active_careers ?></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon blue">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        </div>
        <div class="stat-info">
            <div class="stat-label"><?= $_t('avg_seo_score') ?></div>
            <div class="stat-value"><?= $avg_seo ?>/100</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= $_t('recent_articles') ?></h2>
        <a href="<?= APP_URL ?>/articles/create.php" class="btn btn-primary btn-sm">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            <?= $_t('new_article') ?>
        </a>
    </div>

    <?php if (empty($recent)): ?>
        <div class="empty-state">
            <p><?= $_t('no_results') ?></p>
            <a href="<?= APP_URL ?>/articles/create.php" class="btn btn-primary"><?= $_t('new_article') ?></a>
        </div>
    <?php else: ?>
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th><?= $_t('article_title') ?></th>
                        <th><?= $_t('language') ?></th>
                        <th><?= $_t('article_status') ?></th>
                        <th><?= $_t('article_seo_score') ?></th>
                        <th><?= $_t('article_updated') ?></th>
                        <th><?= $_t('actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent as $row): ?>
                        <tr>
                            <td>
                                <strong><?= e($row['title'] ?? 'Untitled') ?></strong>
                                <br><small style="color:var(--text-muted)"><?= e($row['slug'] ?? '') ?></small>
                            </td>
                            <td><span class="badge badge-<?= $row['language'] === 'ar' ? 'published' : 'draft' ?>"><?= strtoupper($row['language'] ?? 'en') ?></span></td>
                            <td><span class="badge badge-<?= $row['status'] ?>"><?= $row['status'] ?></span></td>
                            <td>
                                <?php
                                $score = calculate_seo_score((int)$row['id'], $row['language'] ?? 'en');
                                $s = $score['score'];
                                $cls = $s >= 80 ? 'published' : ($s >= 50 ? 'draft' : 'archived');
                                ?>
                                <span class="badge badge-<?= $cls ?>"><?= $s ?>/100</span>
                            </td>
                            <td style="white-space:nowrap;color:var(--text-muted);font-size:0.8125rem"><?= date('M d, Y', strtotime($row['updated_at'])) ?></td>
                            <td>
                                <a href="<?= APP_URL ?>/articles/edit.php?id=<?= $row['id'] ?>" class="btn btn-ghost btn-sm"><?= $_t('edit') ?></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

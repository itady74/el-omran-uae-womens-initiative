<?php
require_once __DIR__ . '/../includes/functions.php';
require_auth();

$current_lang = $_GET['lang'] ?? DEFAULT_LANGUAGE;
$lang = require __DIR__ . '/../includes/lang/' . $current_lang . '.php';
$_t = fn(string $k) => $lang[$k] ?? $k;
$page_title = $_t('analytics');

// Basic CMS metrics
$total_articles = (int) db()->query('SELECT COUNT(*) FROM articles')->fetchColumn();
$published_articles = (int) db()->query("SELECT COUNT(*) FROM articles WHERE status='published'")->fetchColumn();
$total_careers = (int) db()->query('SELECT COUNT(*) FROM careers')->fetchColumn();
$total_media = (int) db()->query('SELECT COUNT(*) FROM media')->fetchColumn();
$total_views = (int) db()->query("SELECT COUNT(*) FROM analytics_events WHERE event_type='view'")->fetchColumn();

// Recent events
$recent_events = db()->query('
    SELECT ae.*, at.title as article_title
    FROM analytics_events ae
    LEFT JOIN article_translations at ON ae.article_id = at.article_id AND at.language = ae.language
    ORDER BY ae.created_at DESC
    LIMIT 20
')->fetchAll();

// Most viewed articles
$most_viewed = db()->query('
    SELECT at.title, at.language, COUNT(ae.id) as views
    FROM analytics_events ae
    LEFT JOIN article_translations at ON ae.article_id = at.article_id AND at.language = ae.language
    WHERE ae.event_type = "view"
    GROUP BY ae.article_id, ae.language
    ORDER BY views DESC
    LIMIT 10
')->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));">
    <div class="stat-card">
        <div class="stat-icon blue"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg></div>
        <div class="stat-info"><div class="stat-label"><?= $_t('total_views') ?></div><div class="stat-value"><?= number_format($total_views) ?></div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg></div>
        <div class="stat-info"><div class="stat-label"><?= $_t('published_articles') ?></div><div class="stat-value"><?= $published_articles ?></div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon amber"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg></div>
        <div class="stat-info"><div class="stat-label"><?= $_t('active_careers') ?></div><div class="stat-value"><?= $total_careers ?></div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/></svg></div>
        <div class="stat-info"><div class="stat-label"><?= $_t('media') ?></div><div class="stat-value"><?= $total_media ?></div></div>
    </div>
</div>

<?php if (!empty($most_viewed)): ?>
<div class="card">
    <div class="card-header"><h2 class="card-title"><?= $_t('most_viewed') ?></h2></div>
    <div class="table-wrapper">
        <table class="data-table">
            <thead><tr><th>Article</th><th>Language</th><th><?= $_t('views') ?></th></tr></thead>
            <tbody>
                <?php foreach ($most_viewed as $mv): ?>
                    <tr>
                        <td><strong><?= e($mv['title'] ?? 'Unknown') ?></strong></td>
                        <td><span class="badge badge-<?= $mv['language']==='ar'?'published':'draft' ?>"><?= strtoupper($mv['language']) ?></span></td>
                        <td><?= number_format($mv['views']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header"><h2 class="card-title"><?= $_t('content_activity') ?></h2></div>
    <?php if (empty($recent_events)): ?>
        <div class="empty-state"><p>No activity recorded yet. The analytics_events table is ready for your website to send view events.</p></div>
    <?php else: ?>
        <div class="table-wrapper">
            <table class="data-table">
                <thead><tr><th>Event</th><th>Article</th><th>Language</th><th>Date</th></tr></thead>
                <tbody>
                    <?php foreach ($recent_events as $ev): ?>
                        <tr>
                            <td><span class="badge badge-published"><?= e($ev['event_type']) ?></span></td>
                            <td><?= e($ev['article_title'] ?? '-') ?></td>
                            <td><?= strtoupper($ev['language'] ?? '-') ?></td>
                            <td style="color:var(--text-muted);font-size:0.8125rem"><?= date('M d, Y H:i', strtotime($ev['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

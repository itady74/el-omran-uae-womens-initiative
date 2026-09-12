<?php
require_once __DIR__ . '/../includes/functions.php';
require_auth();

$current_lang = $_GET['lang'] ?? DEFAULT_LANGUAGE;
$lang = require __DIR__ . '/../includes/lang/' . $current_lang . '.php';
$_t = fn(string $k) => $lang[$k] ?? $k;

$page_title = 'Slug Manager';

$slugs = db()->query('
    SELECT sh.*, at.title as article_title
    FROM slug_history sh
    LEFT JOIN article_translations at ON sh.article_id = at.article_id AND sh.language = at.language
    ORDER BY sh.changed_at DESC
    LIMIT 100
')->fetchAll();

$redirects = db()->query('SELECT * FROM redirects ORDER BY created_at DESC LIMIT 100')->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="tabs" style="margin-bottom:var(--space-xl)">
    <button type="button" class="tab active" data-group="slugs" data-tab="slug_history">Slug History</button>
    <button type="button" class="tab" data-group="slugs" data-tab="redirects">Redirects</button>
</div>

<div id="slug_history" class="tab-content active" data-group="slugs">
    <div class="card">
        <div class="card-header"><h2 class="card-title">Slug History</h2></div>
        <?php if (empty($slugs)): ?>
            <div class="empty-state"><p>No slug changes recorded yet.</p></div>
        <?php else: ?>
            <div class="table-wrapper">
                <table class="data-table">
                    <thead><tr><th>Article</th><th>Language</th><th>Old Slug</th><th>New Slug</th><th>Changed</th></tr></thead>
                    <tbody>
                        <?php foreach ($slugs as $s): ?>
                            <tr>
                                <td><?= e($s['article_title'] ?? 'Unknown') ?></td>
                                <td><span class="badge badge-<?= $s['language']==='ar'?'published':'draft' ?>"><?= strtoupper($s['language']) ?></span></td>
                                <td><code style="background:var(--bg);padding:2px 6px;border-radius:4px;font-size:0.8125rem"><?= e($s['old_slug']) ?></code></td>
                                <td><code style="background:var(--success-bg);padding:2px 6px;border-radius:4px;font-size:0.8125rem"><?= e($s['new_slug']) ?></code></td>
                                <td style="color:var(--text-muted);font-size:0.8125rem"><?= date('M d, Y H:i', strtotime($s['changed_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<div id="redirects" class="tab-content" data-group="slugs">
    <div class="card">
        <div class="card-header"><h2 class="card-title">301 Redirects</h2></div>
        <?php if (empty($redirects)): ?>
            <div class="empty-state"><p>No redirects created yet. Redirects are automatically created when article slugs change.</p></div>
        <?php else: ?>
            <div class="table-wrapper">
                <table class="data-table">
                    <thead><tr><th>Language</th><th>Old Path</th><th>New Path</th><th>Status</th><th>Created</th></tr></thead>
                    <tbody>
                        <?php foreach ($redirects as $r): ?>
                            <tr>
                                <td><span class="badge badge-<?= $r['language']==='ar'?'published':'draft' ?>"><?= strtoupper($r['language']) ?></span></td>
                                <td><code style="font-size:0.8125rem"><?= e($r['old_path']) ?></code></td>
                                <td><code style="font-size:0.8125rem;color:var(--success)"><?= e($r['new_path']) ?></code></td>
                                <td><span class="badge badge-published"><?= $r['status_code'] ?></span></td>
                                <td style="color:var(--text-muted);font-size:0.8125rem"><?= date('M d, Y', strtotime($r['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

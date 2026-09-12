<?php
require_once __DIR__ . '/../includes/functions.php';
require_auth();

$current_lang = $_GET['lang'] ?? DEFAULT_LANGUAGE;
$lang = require __DIR__ . '/../includes/lang/' . $current_lang . '.php';
$_t = fn(string $k) => $lang[$k] ?? $k;

$page_title = $_t('keyword_tracker');

$keywords = db()->query('
    SELECT k.*, at.title as article_title
    FROM keywords k
    LEFT JOIN article_translations at ON k.article_id = at.article_id AND k.language = at.language
    ORDER BY k.created_at DESC
    LIMIT 100
')->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= $_t('keyword_tracker') ?></h2>
    </div>

    <?php if (empty($keywords)): ?>
        <div class="empty-state">
            <p>No keywords tracked yet. Add a focus keyword when editing articles.</p>
        </div>
    <?php else: ?>
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Keyword</th>
                        <th>Type</th>
                        <th>Language</th>
                        <th>Article</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($keywords as $kw): ?>
                        <tr>
                            <td><strong><?= e($kw['keyword']) ?></strong></td>
                            <td><span class="badge badge-<?= $kw['type']==='primary'?'published':'draft' ?>"><?= $kw['type'] ?></span></td>
                            <td><span class="badge badge-<?= $kw['language']==='ar'?'published':'draft' ?>"><?= strtoupper($kw['language']) ?></span></td>
                            <td><?= e($kw['article_title'] ?? 'Unknown') ?></td>
                            <td style="color:var(--text-muted);font-size:0.8125rem"><?= date('M d, Y', strtotime($kw['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<?php
require_once __DIR__ . '/../includes/functions.php';
require_auth();

$current_lang = $_GET['lang'] ?? DEFAULT_LANGUAGE;
if (!in_array($current_lang, SUPPORTED_LANGUAGES)) $current_lang = DEFAULT_LANGUAGE;
$lang = require __DIR__ . '/../includes/lang/' . $current_lang . '.php';
$_t = fn(string $k) => $lang[$k] ?? $k;

$page_title = $_t('articles');

// Filters
$search = trim($_GET['search'] ?? '');
$status = $_GET['status'] ?? '';
$filter_lang = $_GET['filter_lang'] ?? '';

$where = '1=1';
$params = [];

if ($search !== '') {
    $where .= ' AND (at.title LIKE ? OR at.slug LIKE ?)';
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}
if (in_array($status, ['draft','published','archived'])) {
    $where .= ' AND a.status = ?';
    $params[] = $status;
}
if (in_array($filter_lang, ['en','ar'])) {
    $where .= ' AND at.language = ?';
    $params[] = $filter_lang;
}

$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 15;

// Count total
$count_sql = "SELECT COUNT(DISTINCT a.id) FROM articles a LEFT JOIN article_translations at ON a.id = at.article_id WHERE {$where}";
$count_stmt = db()->prepare($count_sql);
$count_stmt->execute($params);
$total = (int) $count_stmt->fetchColumn();
$total_pages = max(1, ceil($total / $per_page));
$offset = ($page - 1) * $per_page;

// Fetch
$sql = "SELECT a.*, at.title, at.slug, at.language, at.focus_keyword, u.name as author_name
        FROM articles a
        LEFT JOIN article_translations at ON a.id = at.article_id
        LEFT JOIN users u ON a.author_id = u.id
        WHERE {$where}
        ORDER BY a.updated_at DESC
        LIMIT {$per_page} OFFSET {$offset}";
$stmt = db()->prepare($sql);
$stmt->execute($params);
$articles = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="filter-bar">
    <form method="GET" style="display:flex;gap:var(--space-sm);flex-wrap:wrap;width:100%;">
        <input type="hidden" name="lang" value="<?= e($current_lang) ?>">
        <div class="search-input-wrap" style="flex:1;min-width:200px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" name="search" class="form-input" placeholder="<?= $_t('search') ?>..." value="<?= e($search) ?>">
        </div>
        <select name="status" class="form-select">
            <option value=""><?= $_t('all') ?> <?= $_t('article_status') ?></option>
            <option value="draft" <?= $status==='draft'?'selected':'' ?>><?= $_t('draft') ?></option>
            <option value="published" <?= $status==='published'?'selected':'' ?>><?= $_t('published') ?></option>
            <option value="archived" <?= $status==='archived'?'selected':'' ?>><?= $_t('archived') ?></option>
        </select>
        <select name="filter_lang" class="form-select">
            <option value=""><?= $_t('all') ?> <?= $_t('language') ?></option>
            <option value="en" <?= $filter_lang==='en'?'selected':'' ?>><?= $_t('english') ?></option>
            <option value="ar" <?= $filter_lang==='ar'?'selected':'' ?>><?= $_t('arabic') ?></option>
        </select>
        <button type="submit" class="btn btn-secondary"><?= $_t('filter') ?></button>
    </form>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= $_t('articles') ?> <small style="color:var(--text-muted);font-weight:400">(<?= $total ?>)</small></h2>
        <a href="<?= APP_URL ?>/articles/create.php" class="btn btn-primary btn-sm">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            <?= $_t('new_article') ?>
        </a>
    </div>

    <?php if (empty($articles)): ?>
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
                    <?php foreach ($articles as $a):
                        $score_data = calculate_seo_score((int)$a['id'], $a['language'] ?? 'en');
                        $score = $score_data['score'];
                    ?>
                        <tr>
                            <td>
                                <div style="display:flex;align-items:center;gap:var(--space-sm)">
                                    <?php if ($a['featured_image_id']): ?>
                                        <?php $img = db()->prepare('SELECT path FROM media WHERE id = ?'); $img->execute([$a['featured_image_id']]); $img = $img->fetch(); ?>
                                        <?php if ($img): ?>
                                            <img src="<?= UPLOAD_URL ?>/<?= e($img['path']) ?>" alt="" style="width:40px;height:40px;object-fit:cover;border-radius:var(--radius-sm);flex-shrink:0;">
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <div>
                                        <strong><?= e($a['title'] ?: 'Untitled') ?></strong>
                                        <br><small style="color:var(--text-muted)">/<?= e($a['slug'] ?? '') ?></small>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge badge-<?= $a['language']==='ar'?'published':'draft' ?>"><?= strtoupper($a['language'] ?? 'en') ?></span></td>
                            <td><span class="badge badge-<?= $a['status'] ?>"><?= $_t($a['status']) ?></span></td>
                            <td>
                                <?php $cls = $score >= 80 ? 'published' : ($score >= 50 ? 'draft' : 'archived'); ?>
                                <span class="badge badge-<?= $cls ?>"><?= $score ?>/100</span>
                            </td>
                            <td style="white-space:nowrap;color:var(--text-muted);font-size:0.8125rem"><?= date('M d, Y', strtotime($a['updated_at'])) ?></td>
                            <td>
                                <div class="btn-group">
                                    <a href="<?= APP_URL ?>/articles/edit.php?id=<?= $a['id'] ?>" class="btn btn-ghost btn-sm"><?= $_t('edit') ?></a>
                                    <button onclick="confirmDelete('<?= APP_URL ?>/articles/delete.php?id=<?= $a['id'] ?>', '<?= $_t('confirm_delete') ?>', '<?= csrf_token() ?>')" class="btn btn-ghost btn-sm" style="color:var(--danger)"><?= $_t('delete') ?></button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <a href="?page=<?= max(1,$page-1) ?>&<?= http_build_query(array_filter(['search'=>$search,'status'=>$status,'filter_lang'=>$filter_lang,'lang'=>$current_lang])) ?>" class="pagination-btn" <?= $page<=1?'disabled':'' ?>>&laquo;</a>
                <?php for ($i = max(1,$page-2); $i <= min($total_pages,$page+2); $i++): ?>
                    <a href="?page=<?= $i ?>&<?= http_build_query(array_filter(['search'=>$search,'status'=>$status,'filter_lang'=>$filter_lang,'lang'=>$current_lang])) ?>" class="pagination-btn <?= $i===$page?'active':'' ?>"><?= $i ?></a>
                <?php endfor; ?>
                <a href="?page=<?= min($total_pages,$page+1) ?>&<?= http_build_query(array_filter(['search'=>$search,'status'=>$status,'filter_lang'=>$filter_lang,'lang'=>$current_lang])) ?>" class="pagination-btn" <?= $page>=$total_pages?'disabled':'' ?>>&raquo;</a>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

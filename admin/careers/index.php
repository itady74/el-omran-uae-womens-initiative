<?php
require_once __DIR__ . '/../includes/functions.php';
require_auth();

$current_lang = $_GET['lang'] ?? DEFAULT_LANGUAGE;
if (!in_array($current_lang, SUPPORTED_LANGUAGES)) $current_lang = DEFAULT_LANGUAGE;
$lang = require __DIR__ . '/../includes/lang/' . $current_lang . '.php';
$_t = fn(string $k) => $lang[$k] ?? $k;

$page_title = $_t('careers');

$search = trim($_GET['search'] ?? '');
$status = $_GET['status'] ?? '';
$filter_lang = $_GET['filter_lang'] ?? '';

$where = '1=1';
$params = [];
if ($search !== '') { $where .= ' AND (ct.title LIKE ? OR ct.location LIKE ?)'; $params[] = "%{$search}%"; $params[] = "%{$search}%"; }
if (in_array($status, ['draft','published','closed'])) { $where .= ' AND c.status = ?'; $params[] = $status; }
if (in_array($filter_lang, ['en','ar'])) { $where .= ' AND ct.language = ?'; $params[] = $filter_lang; }

$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 15;
$count_stmt = db()->prepare("SELECT COUNT(DISTINCT c.id) FROM careers c LEFT JOIN career_translations ct ON c.id = ct.career_id WHERE {$where}");
$count_stmt->execute($params);
$total = (int) $count_stmt->fetchColumn();
$total_pages = max(1, ceil($total / $per_page));
$offset = ($page - 1) * $per_page;

$stmt = db()->prepare("SELECT c.*, ct.title, ct.slug, ct.location, ct.language FROM careers c LEFT JOIN career_translations ct ON c.id = ct.career_id WHERE {$where} ORDER BY c.updated_at DESC LIMIT {$per_page} OFFSET {$offset}");
$stmt->execute($params);
$careers = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="filter-bar">
    <form method="GET" style="display:flex;gap:var(--space-sm);flex-wrap:wrap;width:100%;">
        <div class="search-input-wrap" style="flex:1;min-width:200px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" name="search" class="form-input" placeholder="<?= $_t('search') ?>..." value="<?= e($search) ?>">
        </div>
        <select name="status" class="form-select">
            <option value=""><?= $_t('all') ?></option>
            <option value="draft" <?= $status==='draft'?'selected':'' ?>><?= $_t('draft') ?></option>
            <option value="published" <?= $status==='published'?'selected':'' ?>><?= $_t('published') ?></option>
            <option value="closed" <?= $status==='closed'?'selected':'' ?>><?= $_t('closed') ?></option>
        </select>
        <button type="submit" class="btn btn-secondary"><?= $_t('filter') ?></button>
    </form>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= $_t('careers') ?> <small style="color:var(--text-muted);font-weight:400">(<?= $total ?>)</small></h2>
        <a href="<?= APP_URL ?>/careers/create.php" class="btn btn-primary btn-sm">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            <?= $_t('new_career') ?>
        </a>
    </div>

    <?php if (empty($careers)): ?>
        <div class="empty-state">
            <p><?= $_t('no_results') ?></p>
            <a href="<?= APP_URL ?>/careers/create.php" class="btn btn-primary"><?= $_t('new_career') ?></a>
        </div>
    <?php else: ?>
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th><?= $_t('job_title') ?></th>
                        <th><?= $_t('location') ?></th>
                        <th><?= $_t('language') ?></th>
                        <th><?= $_t('article_status') ?></th>
                        <th><?= $_t('article_updated') ?></th>
                        <th><?= $_t('actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($careers as $c): ?>
                        <tr>
                            <td><strong><?= e($c['title'] ?: 'Untitled') ?></strong><br><small style="color:var(--text-muted)"><?= e($c['slug'] ?? '') ?></small></td>
                            <td><?= e($c['location'] ?? '') ?></td>
                            <td><span class="badge badge-<?= $c['language']==='ar'?'published':'draft' ?>"><?= strtoupper($c['language'] ?? 'en') ?></span></td>
                            <td><span class="badge badge-<?= $c['status'] ?>"><?= $_t($c['status']) ?></span></td>
                            <td style="white-space:nowrap;color:var(--text-muted);font-size:0.8125rem"><?= date('M d, Y', strtotime($c['updated_at'])) ?></td>
                            <td>
                                <div class="btn-group">
                                    <a href="<?= APP_URL ?>/careers/edit.php?id=<?= $c['id'] ?>" class="btn btn-ghost btn-sm"><?= $_t('edit') ?></a>
                                    <button onclick="confirmDelete('<?= APP_URL ?>/careers/delete.php?id=<?= $c['id'] ?>', '<?= $_t('confirm_delete') ?>', '<?= csrf_token() ?>')" class="btn btn-ghost btn-sm" style="color:var(--danger)"><?= $_t('delete') ?></button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

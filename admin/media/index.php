<?php
require_once __DIR__ . '/../includes/functions.php';
require_auth();

$current_lang = $_GET['lang'] ?? DEFAULT_LANGUAGE;
$lang = require __DIR__ . '/../includes/lang/' . $current_lang . '.php';
$_t = fn(string $k) => $lang[$k] ?? $k;
$page_title = $_t('media');

// Handle upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['files'])) {
    header('Content-Type: application/json');
    $results = [];
    foreach ($_FILES['files']['name'] as $i => $name) {
        $_FILES['upload'] = [
            'name'      => $name,
            'type'      => $_FILES['files']['type'][$i],
            'tmp_name'  => $_FILES['files']['tmp_name'][$i],
            'error'     => $_FILES['files']['error'][$i],
            'size'      => $_FILES['files']['size'][$i],
        ];
        $upload = upload_image('upload', 'articles');
        if ($upload) {
            $results[] = $upload;
        }
    }
    echo json_encode(['success' => true, 'files' => $results]);
    exit;
}

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $del_id = (int) $_POST['delete_id'];
    $media = db()->prepare('SELECT path FROM media WHERE id = ?');
    $media->execute([$del_id]);
    $media = $media->fetch();
    if ($media) {
        delete_file($media['path']);
        db()->prepare('DELETE FROM media WHERE id = ?')->execute([$del_id]);
    }
    redirect(APP_URL . '/media/');
}

// Fetch media
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 24;
$total = (int) db()->query('SELECT COUNT(*) FROM media')->fetchColumn();
$total_pages = max(1, ceil($total / $per_page));
$offset = ($page - 1) * $per_page;
$media = db()->prepare("SELECT * FROM media ORDER BY created_at DESC LIMIT {$per_page} OFFSET {$offset}");
$media->execute();
$media = $media->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= $_t('media_library') ?></h2>
    </div>

    <div class="media-upload-zone" id="uploadZone" style="margin-bottom:var(--space-xl)">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom:var(--space-sm);opacity:0.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
        <p style="color:var(--text-secondary)"><?= $_t('upload_files') ?></p>
        <p style="font-size:0.75rem;color:var(--text-muted)">JPG, PNG, WEBP - Max 5MB</p>
        <input type="file" id="fileInput" multiple accept="image/jpeg,image/png,image/webp" style="display:none">
    </div>

    <?php if (empty($media)): ?>
        <div class="empty-state"><p><?= $_t('no_results') ?></p></div>
    <?php else: ?>
        <div class="media-grid">
            <?php foreach ($media as $m): ?>
                <div class="media-item" data-id="<?= $m['id'] ?>">
                    <img src="<?= UPLOAD_URL ?>/<?= e($m['path']) ?>" alt="<?= e($m['alt_text_en'] ?? $m['original_filename']) ?>" class="media-item-img" loading="lazy">
                    <div class="media-item-info"><?= e($m['original_filename']) ?></div>
                    <div style="padding:0 var(--space-sm) var(--space-sm);display:flex;gap:4px;">
                        <button onclick="copyUrl('<?= UPLOAD_URL ?>/<?= e($m['path']) ?>')" class="btn btn-ghost btn-sm" style="font-size:0.6875rem;padding:2px 6px;">Copy URL</button>
                        <form method="POST" style="display:inline" onsubmit="return confirm('Delete this file?')">
                            <input type="hidden" name="delete_id" value="<?= $m['id'] ?>">
                            <button type="submit" class="btn btn-ghost btn-sm" style="font-size:0.6875rem;padding:2px 6px;color:var(--danger)">Delete</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?= $i ?>" class="pagination-btn <?= $i===$page?'active':'' ?>"><?= $i ?></a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script>
const uploadZone = document.getElementById('uploadZone');
const fileInput = document.getElementById('fileInput');

uploadZone.addEventListener('click', () => fileInput.click());
uploadZone.addEventListener('dragover', (e) => { e.preventDefault(); uploadZone.classList.add('dragover'); });
uploadZone.addEventListener('dragleave', () => uploadZone.classList.remove('dragover'));
uploadZone.addEventListener('drop', (e) => {
    e.preventDefault();
    uploadZone.classList.remove('dragover');
    handleFiles(e.dataTransfer.files);
});
fileInput.addEventListener('change', () => handleFiles(fileInput.files));

function handleFiles(files) {
    const formData = new FormData();
    for (const file of files) {
        formData.append('files[]', file);
    }
    uploadZone.innerHTML = '<p style="color:var(--accent)">Uploading...</p>';

    fetch('<?= APP_URL ?>/media/', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                uploadZone.innerHTML = '<p style="color:var(--danger)">Upload failed.</p>';
            }
        })
        .catch(() => { uploadZone.innerHTML = '<p style="color:var(--danger)">Upload failed.</p>'; });
}

function copyUrl(url) {
    navigator.clipboard.writeText(url).then(() => {
        if (typeof showToast === 'function') showToast('URL copied!', 'success');
    });
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>

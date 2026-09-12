<?php
require_once __DIR__ . '/../includes/functions.php';
require_auth();

$current_lang = $_GET['lang'] ?? DEFAULT_LANGUAGE;
$lang = require __DIR__ . '/../includes/lang/' . $current_lang . '.php';
$_t = fn(string $k) => $lang[$k] ?? $k;
$page_title = $_t('settings');

$user = current_user();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $errors[] = 'Invalid request.';
    } else {
        // Site settings
        update_setting('site_name', trim($_POST['site_name'] ?? ''));
        update_setting('site_url', trim($_POST['site_url'] ?? ''));
        update_setting('default_language', $_POST['default_language'] ?? 'en');
        update_setting('supported_languages', $_POST['supported_languages'] ?? 'en,ar');
        update_setting('default_index_status', $_POST['default_index_status'] ?? 'index');
        update_setting('default_follow_status', $_POST['default_follow_status'] ?? 'follow');

        // Handle OG image upload
        $upload = upload_image('default_og_image', 'og');
        if ($upload) {
            update_setting('default_og_image', $upload['path']);
        }

        // Password change
        if (!empty($_POST['new_password'])) {
            if (empty($_POST['current_password'])) {
                $errors[] = 'Current password is required to change password.';
            } else {
                $stmt = db()->prepare('SELECT password_hash FROM users WHERE id = ?');
                $stmt->execute([$user['id']]);
                $hash = $stmt->fetchColumn();
                if (!password_verify($_POST['current_password'], $hash)) {
                    $errors[] = 'Current password is incorrect.';
                } else {
                    $new_hash = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
                    db()->prepare('UPDATE users SET password_hash = ? WHERE id = ?')->execute([$new_hash, $user['id']]);
                }
            }
        }

        if (empty($errors)) {
            redirect(APP_URL . '/settings/?saved=1');
        }
    }
}

$saved = isset($_GET['saved']);

include __DIR__ . '/../includes/header.php';
?>

<?php if ($saved): ?>
<script>document.addEventListener('DOMContentLoaded', () => showToast('<?= $_t('settings_saved') ?>', 'success'));</script>
<?php endif; ?>

<?php if (!empty($errors)): ?>
    <div style="background:var(--danger-bg);border:1px solid var(--danger-border);padding:12px 16px;border-radius:var(--radius-md);margin-bottom:var(--space-lg);color:var(--danger);">
        <?php foreach ($errors as $err): ?><p><?= e($err) ?></p><?php endforeach; ?>
    </div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <div class="card">
        <div class="card-header"><h2 class="card-title">Site Settings</h2></div>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label"><?= $_t('site_name') ?></label>
                <input type="text" name="site_name" class="form-input" value="<?= e(get_setting('site_name')) ?>">
            </div>
            <div class="form-group">
                <label class="form-label"><?= $_t('site_url') ?></label>
                <input type="url" name="site_url" class="form-input" value="<?= e(get_setting('site_url')) ?>">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label"><?= $_t('default_language') ?></label>
                <select name="default_language" class="form-select">
                    <option value="en" <?= get_setting('default_language')==='en'?'selected':'' ?>>English</option>
                    <option value="ar" <?= get_setting('default_language')==='ar'?'selected':'' ?>>Arabic</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label"><?= $_t('supported_languages') ?></label>
                <input type="text" name="supported_languages" class="form-input" value="<?= e(get_setting('supported_languages')) ?>">
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h2 class="card-title">Default SEO</h2></div>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label"><?= $_t('index_status') ?></label>
                <select name="default_index_status" class="form-select">
                    <option value="index" <?= get_setting('default_index_status')==='index'?'selected':'' ?>>Index</option>
                    <option value="noindex" <?= get_setting('default_index_status')==='noindex'?'selected':'' ?>>Noindex</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label"><?= $_t('follow_status') ?></label>
                <select name="default_follow_status" class="form-select">
                    <option value="follow" <?= get_setting('default_follow_status')==='follow'?'selected':'' ?>>Follow</option>
                    <option value="nofollow" <?= get_setting('default_follow_status')==='nofollow'?'selected':'' ?>>Nofollow</option>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label class="form-label"><?= $_t('default_social_image') ?></label>
            <input type="file" name="default_og_image" class="form-input" accept="image/*">
            <?php $og = get_setting('default_og_image'); if ($og): ?>
                <div class="image-preview" style="margin-top:var(--space-sm)"><img src="<?= UPLOAD_URL ?>/<?= e($og) ?>" alt=""></div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h2 class="card-title"><?= $_t('admin_profile') ?></h2></div>
        <div class="form-group">
            <label class="form-label">Name</label>
            <input type="text" class="form-input" value="<?= e($user['name']) ?>" disabled>
        </div>
        <div class="form-group">
            <label class="form-label">Email</label>
            <input type="email" class="form-input" value="<?= e($user['email']) ?>" disabled>
        </div>
        <div class="form-group">
            <label class="form-label">New Password (leave blank to keep current)</label>
            <input type="password" name="new_password" class="form-input" minlength="8" autocomplete="new-password">
        </div>
        <div class="form-group">
            <label class="form-label">Current Password (required to change)</label>
            <input type="password" name="current_password" class="form-input" autocomplete="current-password">
        </div>
    </div>

    <button type="submit" class="btn btn-primary btn-lg"><?= $_t('save_settings') ?></button>
</form>

<?php include __DIR__ . '/../includes/footer.php'; ?>

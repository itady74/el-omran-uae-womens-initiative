<?php
/**
 * Admin header layout
 * Expects $page_title and $current_lang to be set
 */
require_once __DIR__ . '/functions.php';

if (!isset($page_title)) $page_title = 'Dashboard';
if (!isset($current_lang)) $current_lang = DEFAULT_LANGUAGE;

$lang_file = __DIR__ . '/lang/' . $current_lang . '.php';
$lang = file_exists($lang_file) ? require $lang_file : require __DIR__ . '/lang/en.php';
$_t = fn(string $key) => $lang[$key] ?? $key;

$dir = $current_lang === 'ar' ? 'rtl' : 'ltr';
$user = current_user();
?>
<!DOCTYPE html>
<html lang="<?= e($current_lang) ?>" dir="<?= $dir ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title) ?> - <?= e(APP_NAME) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Noto+Kufi+Arabic:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/admin.css">
    <script>const CSRF_TOKEN='<?= csrf_token() ?>';const CURRENT_LANG='<?= $current_lang ?>';const APP_URL='<?= APP_URL ?>';</script>
</head>
<body>
    <div class="admin-layout">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <a href="<?= APP_URL ?>" class="sidebar-logo">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                    <span><?= e(APP_NAME) ?></span>
                </a>
            </div>
            <nav class="sidebar-nav">
                <a href="<?= APP_URL ?>" class="sidebar-link <?= basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : '' ?>">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                    <span><?= $_t('dashboard') ?></span>
                </a>
                <a href="<?= APP_URL ?>/articles/" class="sidebar-link <?= strpos($_SERVER['PHP_SELF'], '/articles/') !== false ? 'active' : '' ?>">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                    <span><?= $_t('articles') ?></span>
                </a>
                <a href="<?= APP_URL ?>/careers/" class="sidebar-link <?= strpos($_SERVER['PHP_SELF'], '/careers/') !== false ? 'active' : '' ?>">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                    <span><?= $_t('careers') ?></span>
                </a>
                <a href="<?= APP_URL ?>/seo/" class="sidebar-link <?= strpos($_SERVER['PHP_SELF'], '/seo/') !== false ? 'active' : '' ?>">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <span><?= $_t('seo_tools') ?></span>
                </a>
                <a href="<?= APP_URL ?>/media/" class="sidebar-link <?= strpos($_SERVER['PHP_SELF'], '/media/') !== false ? 'active' : '' ?>">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                    <span><?= $_t('media') ?></span>
                </a>
                <a href="<?= APP_URL ?>/analytics/" class="sidebar-link <?= strpos($_SERVER['PHP_SELF'], '/analytics/') !== false ? 'active' : '' ?>">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                    <span><?= $_t('analytics') ?></span>
                </a>
                <a href="<?= APP_URL ?>/settings/" class="sidebar-link <?= strpos($_SERVER['PHP_SELF'], '/settings/') !== false ? 'active' : '' ?>">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                    <span><?= $_t('settings') ?></span>
                </a>
            </nav>
            <div class="sidebar-footer">
                <div class="sidebar-user">
                    <div class="sidebar-user-avatar"><?= strtoupper(substr($user['name'] ?? 'A', 0, 1)) ?></div>
                    <div class="sidebar-user-info">
                        <div class="sidebar-user-name"><?= e($user['name'] ?? '') ?></div>
                        <div class="sidebar-user-role"><?= e($user['role'] ?? '') ?></div>
                    </div>
                </div>
            </div>
        </aside>
        <main class="main-content">
            <header class="topbar">
                <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                </button>
                <h1 class="topbar-title"><?= e($page_title) ?></h1>
                <div class="topbar-actions">
                    <div class="lang-switcher">
                        <a href="?lang=en" class="lang-btn <?= $current_lang === 'en' ? 'active' : '' ?>">EN</a>
                        <a href="?lang=ar" class="lang-btn <?= $current_lang === 'ar' ? 'active' : '' ?>">ع</a>
                    </div>
                    <div class="topbar-user">
                        <span class="topbar-user-name"><?= e($user['name'] ?? '') ?></span>
                        <a href="<?= APP_URL ?>/logout.php" class="topbar-logout" title="<?= $_t('logout') ?>">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                        </a>
                    </div>
                </div>
            </header>
            <div class="content-wrapper">

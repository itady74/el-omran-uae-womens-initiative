/**
 * Al Omran Blog CMS - Admin JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    initSidebar();
    initCollapsibles();
    initTabs();
    initToasts();
    initSlugs();
    initCharCounts();
    initSERPPreview();
    initAutoSave();
    initMediaUpload();
    initRichEditor();
    initLangSwitch();
});

/* ========================================
   SIDEBAR
   ======================================== */
function initSidebar() {
    const toggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    if (toggle && sidebar) {
        toggle.addEventListener('click', () => sidebar.classList.toggle('open'));
        document.addEventListener('click', (e) => {
            if (sidebar.classList.contains('open') && !sidebar.contains(e.target) && e.target !== toggle) {
                sidebar.classList.remove('open');
            }
        });
    }
}

/* ========================================
   COLLAPSIBLES
   ======================================== */
function initCollapsibles() {
    document.querySelectorAll('.collapsible-header').forEach(btn => {
        btn.addEventListener('click', () => {
            btn.closest('.collapsible').classList.toggle('open');
        });
    });
}

/* ========================================
   TABS
   ======================================== */
function initTabs() {
    document.querySelectorAll('.tabs').forEach(tabBar => {
        tabBar.querySelectorAll('.tab').forEach(tab => {
            tab.addEventListener('click', () => {
                const group = tab.dataset.group || 'default';
                tabBar.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
                document.querySelectorAll(`.tab-content[data-group="${group}"]`).forEach(c => c.classList.remove('active'));
                const target = document.getElementById(tab.dataset.tab);
                if (target) target.classList.add('active');
            });
        });
    });
}

/* ========================================
   TOASTS
   ======================================== */
let toastContainer;
function initToasts() {
    toastContainer = document.createElement('div');
    toastContainer.className = 'toast-container';
    document.body.appendChild(toastContainer);
}

function showToast(message, type = 'success', duration = 3000) {
    if (!toastContainer) initToasts();
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    const icon = type === 'success' ? '&#10003;' : type === 'error' ? '&#10007;' : '&#9888;';
    toast.innerHTML = `<span>${icon}</span><span>${message}</span>`;
    toastContainer.appendChild(toast);
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        toast.style.transition = '0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, duration);
}

/* ========================================
   SLUG GENERATION
   ======================================== */
function initSlugs() {
    document.querySelectorAll('[data-slug-source]').forEach(slugInput => {
        const sourceId = slugInput.dataset.slugSource;
        const source = document.getElementById(sourceId);
        if (!source) return;

        let debounce;
        source.addEventListener('input', () => {
            clearTimeout(debounce);
            debounce = setTimeout(() => {
                if (!slugInput.dataset.manualEdit) {
                    const lang = slugInput.dataset.lang || 'en';
                    slugInput.value = generateSlug(source.value, lang);
                }
            }, 300);
        });

        slugInput.addEventListener('input', () => {
            slugInput.dataset.manualEdit = 'true';
        });
    });
}

function generateSlug(text, lang = 'en') {
    text = text.trim();
    if (lang === 'ar') {
        return text.replace(/\s+/g, '-').replace(/[^\p{Arabic}\p{M}-]/gu, '').replace(/-+/g, '-').replace(/-$/, '');
    }
    return text.toLowerCase().replace(/[^a-z0-9\s-]/g, '').replace(/[\s-]+/g, '-').replace(/^-/, '').replace(/-$/, '');
}

/* ========================================
   CHARACTER COUNTS
   ======================================== */
function initCharCounts() {
    document.querySelectorAll('[data-maxlength]').forEach(input => {
        const max = parseInt(input.dataset.maxlength);
        const counter = input.parentElement.querySelector('.char-count');
        if (!counter) return;

        const update = () => {
            const len = input.value.length;
            counter.textContent = `${len} / ${max}`;
            counter.classList.toggle('over', len > max);
            counter.classList.toggle('good', len >= max * 0.6 && len <= max);
        };

        input.addEventListener('input', update);
        update();
    });
}

/* ========================================
   SERP PREVIEW
   ======================================== */
function initSERPPreview() {
    const titleInput = document.getElementById('seo_title');
    const descInput = document.getElementById('meta_description');
    const urlDisplay = document.getElementById('serp_url');

    if (!titleInput || !descInput) return;

    const previewTitle = document.getElementById('serp_preview_title');
    const previewDesc = document.getElementById('serp_preview_desc');

    function updatePreview() {
        if (previewTitle) {
            const t = titleInput.value || 'Page Title';
            previewTitle.textContent = t;
            previewTitle.style.color = t.length > 60 ? '#c00' : '';
        }
        if (previewDesc) {
            const d = descInput.value || 'Meta description will appear here...';
            previewDesc.textContent = d;
            previewDesc.style.color = d.length > 160 ? '#c00' : '';
        }
    }

    titleInput.addEventListener('input', updatePreview);
    descInput.addEventListener('input', updatePreview);
    updatePreview();
}

/* ========================================
   AUTO-SAVE
   ======================================== */
function initAutoSave() {
    const form = document.getElementById('articleForm');
    if (!form) return;

    const saveStatus = document.getElementById('saveStatus');
    let debounce;
    let lastContent = '';

    form.querySelectorAll('input, textarea, select').forEach(el => {
        el.addEventListener('input', () => {
            clearTimeout(debounce);
            if (saveStatus) saveStatus.textContent = 'Saving...';
            debounce = setTimeout(() => {
                const content = document.getElementById('editor_content')?.innerHTML || '';
                if (content === lastContent) {
                    if (saveStatus) saveStatus.textContent = 'Saved';
                    return;
                }
                lastContent = content;

                const formData = new FormData(form);
                formData.append('auto_save', '1');
                formData.append('action', 'auto_save');

                fetch(form.action || window.location.href, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-Token': CSRF_TOKEN
                    }
                })
                .then(r => r.json())
                .then(data => {
                    if (saveStatus) {
                        saveStatus.textContent = data.success ? 'Saved' : 'Unable to save';
                        if (data.success) {
                            saveStatus.style.color = '';
                            setTimeout(() => { saveStatus.textContent = ''; }, 2000);
                        } else {
                            saveStatus.style.color = 'var(--danger)';
                        }
                    }
                    if (data.article_id && !document.querySelector('input[name="article_id"]')) {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'article_id';
                        input.value = data.article_id;
                        form.appendChild(input);
                    }
                })
                .catch(() => {
                    if (saveStatus) {
                        saveStatus.textContent = 'Unable to save';
                        saveStatus.style.color = 'var(--danger)';
                    }
                });
            }, 1500);
        });
    });
}

/* ========================================
   MEDIA UPLOAD
   ======================================== */
function initMediaUpload() {
    document.querySelectorAll('.media-upload-zone').forEach(zone => {
        const input = zone.querySelector('input[type="file"]');
        if (!input) return;

        zone.addEventListener('click', () => input.click());
        zone.addEventListener('dragover', (e) => { e.preventDefault(); zone.classList.add('dragover'); });
        zone.addEventListener('dragleave', () => zone.classList.remove('dragover'));
        zone.addEventListener('drop', (e) => {
            e.preventDefault();
            zone.classList.remove('dragover');
            if (e.dataTransfer.files.length) {
                input.files = e.dataTransfer.files;
                input.dispatchEvent(new Event('change'));
            }
        });
    });
}

/* ========================================
   RICH TEXT EDITOR
   ======================================== */
function initRichEditor() {
    document.querySelectorAll('.editor-toolbar').forEach(toolbar => {
        toolbar.querySelectorAll('.editor-toolbar-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const cmd = btn.dataset.cmd;
                const val = btn.dataset.val || null;
                const targetId = btn.dataset.target;
                const editor = targetId ? document.getElementById(targetId) : btn.closest('.editor-toolbar')?.parentElement?.querySelector('.rich-editor');
                if (!editor) return;

                if (cmd === 'createLink') {
                    const url = val || prompt('Enter URL:');
                    if (url) document.execCommand(cmd, false, url);
                } else if (cmd === 'insertImage') {
                    return;
                } else if (cmd === 'viewSource') {
                    const pre = editor.parentElement.querySelector('.editor-source');
                    if (pre) {
                        pre.style.display = pre.style.display === 'none' ? 'block' : 'none';
                        editor.style.display = pre.style.display === 'none' ? 'block' : 'none';
                        if (pre.style.display === 'block') pre.textContent = editor.innerHTML;
                    }
                    return;
                } else {
                    document.execCommand(cmd, false, val);
                }
                editor.focus();
            });
        });
    });
}

/* ========================================
   LANGUAGE SWITCH (CMS UI)
   ======================================== */
function initLangSwitch() {
    document.querySelectorAll('.lang-btn').forEach(btn => {
        // Handled by server-side via ?lang= parameter
    });
}

/* ========================================
   MEDIA MODAL
   ======================================== */
function openMediaModal(callback) {
    const modal = document.getElementById('mediaModal');
    if (!modal) return;

    modal.classList.add('active');
    modal._callback = callback;

    loadMediaGrid();

    modal.querySelector('.modal-close')?.addEventListener('click', () => modal.classList.remove('active'));
    modal.addEventListener('click', (e) => {
        if (e.target === modal) modal.classList.remove('active');
    });
}

function loadMediaGrid(page = 1) {
    const grid = document.getElementById('mediaGrid');
    if (!grid) return;

    fetch(`${APP_URL}/api/media.php?action=list&page=${page}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        grid.innerHTML = '';
        if (data.data && data.data.length) {
            data.data.forEach(item => {
                const div = document.createElement('div');
                div.className = 'media-item';
                div.innerHTML = `
                    <img src="${UPLOAD_URL}/${item.path}" alt="${item.alt_text_en || item.original_filename}" class="media-item-img" loading="lazy">
                    <div class="media-item-info">${item.original_filename}</div>
                `;
                div.addEventListener('click', () => {
                    grid.querySelectorAll('.media-item').forEach(i => i.classList.remove('selected'));
                    div.classList.add('selected');
                    const modal = document.getElementById('mediaModal');
                    if (modal && modal._callback) {
                        modal._callback(item);
                        modal.classList.remove('active');
                    }
                });
                grid.appendChild(div);
            });
        } else {
            grid.innerHTML = '<div class="empty-state"><p>No media files found.</p></div>';
        }
    });
}

/* ========================================
   CONFIRM DELETE
   ======================================== */
function confirmDelete(url, message, csrfToken) {
    if (confirm(message || 'Are you sure you want to delete this?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = url;
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = 'csrf_token';
        csrfInput.value = csrfToken || (typeof CSRF_TOKEN !== 'undefined' ? CSRF_TOKEN : '');
        form.appendChild(csrfInput);
        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'id';
        const urlParams = new URL(url, window.location.origin).searchParams;
        idInput.value = urlParams.get('id') || '';
        form.appendChild(idInput);
        document.body.appendChild(form);
        form.submit();
    }
}

/* ========================================
   KEYBOARD SHORTCUTS
   ======================================== */
document.addEventListener('keydown', (e) => {
    if ((e.ctrlKey || e.metaKey) && e.key === 's') {
        e.preventDefault();
        const form = document.getElementById('articleForm');
        if (form) {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) submitBtn.click();
        }
    }
});

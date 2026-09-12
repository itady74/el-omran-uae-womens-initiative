<?php
require_once __DIR__ . '/../includes/functions.php';
require_auth();

$current_lang = $_GET['lang'] ?? DEFAULT_LANGUAGE;
$lang = require __DIR__ . '/../includes/lang/' . $current_lang . '.php';
$_t = fn(string $k) => $lang[$k] ?? $k;

$page_title = $_t('social_images');

include __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">OG Image Generator</h2>
    </div>
    <p style="color:var(--text-secondary);margin-bottom:var(--space-xl)">Create Open Graph images for social media sharing. This editor generates 1200x630 images optimized for Facebook, LinkedIn, and Twitter/X.</p>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-xl)">
        <div>
            <div class="form-group">
                <label class="form-label">Background Image</label>
                <input type="file" id="ogBgImage" class="form-input" accept="image/*">
            </div>
            <div class="form-group">
                <label class="form-label">Title Text</label>
                <input type="text" id="ogTitle" class="form-input" placeholder="Enter title for OG image" value="">
            </div>
            <div class="form-group">
                <label class="form-label">Background Color</label>
                <input type="color" id="ogBgColor" class="form-input" value="#1a1d23" style="height:44px;padding:4px;">
            </div>
            <div class="form-group">
                <label class="form-label">Text Color</label>
                <input type="color" id="ogTextColor" class="form-input" value="#ffffff" style="height:44px;padding:4px;">
            </div>
            <div class="form-group">
                <label class="form-label">Font Size</label>
                <select id="ogFontSize" class="form-select">
                    <option value="32">Small (32px)</option>
                    <option value="42" selected>Medium (42px)</option>
                    <option value="56">Large (56px)</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Preset</label>
                <div class="btn-group">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="applyPreset('facebook')">Facebook (1200x630)</button>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="applyPreset('twitter')">Twitter/X (1200x630)</button>
                </div>
            </div>
        </div>
        <div>
            <h3 style="font-size:0.875rem;font-weight:600;margin-bottom:var(--space-md)">Preview</h3>
            <div id="ogPreview" style="width:100%;aspect-ratio:1200/630;background:#1a1d23;border-radius:var(--radius-lg);overflow:hidden;display:flex;align-items:center;justify-content:center;position:relative;">
                <canvas id="ogCanvas" width="1200" height="630" style="width:100%;height:100%;"></canvas>
            </div>
            <div style="margin-top:var(--space-md);display:flex;gap:var(--space-sm)">
                <button type="button" class="btn btn-primary" onclick="downloadOG()">Download PNG</button>
                <button type="button" class="btn btn-secondary" onclick="renderOG()">Refresh Preview</button>
            </div>
        </div>
    </div>
</div>

<script>
const canvas = document.getElementById('ogCanvas');
const ctx = canvas.getContext('2d');
let bgImage = null;

document.getElementById('ogBgImage').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function(ev) {
        bgImage = new Image();
        bgImage.onload = renderOG;
        bgImage.src = ev.target.result;
    };
    reader.readAsDataURL(file);
});

['ogTitle', 'ogBgColor', 'ogTextColor', 'ogFontSize'].forEach(id => {
    document.getElementById(id)?.addEventListener('input', renderOG);
});

function renderOG() {
    const w = 1200, h = 630;
    const title = document.getElementById('ogTitle').value || 'Al Omran Training Center';
    const bgColor = document.getElementById('ogBgColor').value;
    const textColor = document.getElementById('ogTextColor').value;
    const fontSize = parseInt(document.getElementById('ogFontSize').value);

    ctx.clearRect(0, 0, w, h);

    // Background
    if (bgImage) {
        ctx.drawImage(bgImage, 0, 0, w, h);
        ctx.fillStyle = 'rgba(0,0,0,0.5)';
        ctx.fillRect(0, 0, w, h);
    } else {
        ctx.fillStyle = bgColor;
        ctx.fillRect(0, 0, w, h);
    }

    // Title
    ctx.fillStyle = textColor;
    ctx.font = `bold ${fontSize}px Inter, sans-serif`;
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';

    // Word wrap
    const maxWidth = w * 0.8;
    const words = title.split(' ');
    let lines = [];
    let currentLine = '';
    for (const word of words) {
        const testLine = currentLine ? currentLine + ' ' + word : word;
        if (ctx.measureText(testLine).width > maxWidth) {
            lines.push(currentLine);
            currentLine = word;
        } else {
            currentLine = testLine;
        }
    }
    lines.push(currentLine);

    const lineHeight = fontSize * 1.3;
    const startY = h / 2 - ((lines.length - 1) * lineHeight) / 2;
    lines.forEach((line, i) => {
        ctx.fillText(line, w / 2, startY + i * lineHeight);
    });

    // Brand
    ctx.font = '16px Inter, sans-serif';
    ctx.fillStyle = textColor;
    ctx.globalAlpha = 0.6;
    ctx.fillText('Al Omran Training Center', w / 2, h - 40);
    ctx.globalAlpha = 1;
}

function downloadOG() {
    renderOG();
    const link = document.createElement('a');
    link.download = 'og-image.png';
    link.href = canvas.toDataURL('image/png');
    link.click();
}

function applyPreset(platform) {
    renderOG();
}

// Initial render
renderOG();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>

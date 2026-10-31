<?php
/**
 * Превью на базе live HTML (скачивается fetch-скриптом) + патч блока партнёров.
 */
$repo = dirname(__DIR__, 2);
$docroot = getenv('LIREATE_PARTNERS_PREVIEW_DOCROOT');
if ($docroot === false || $docroot === '') {
    $docroot = dirname(__DIR__, 3) . '/.dev-docroot-partners';
}
$_SERVER['DOCUMENT_ROOT'] = $docroot;

require_once __DIR__ . '/live-patch.php';

$snapshot = __DIR__ . '/fixtures/partners-live.snapshot.html';
if (!is_file($snapshot)) {
    header('Content-Type: text/plain; charset=utf-8', true, 503);
    echo "Snapshot missing. Run: python3 scripts/lireate_partners_fetch_live_snapshot.py\n";
    exit(1);
}

$cms_origin = getenv('LIREATE_PARTNERS_CMS_CSS_ORIGIN');
if ($cms_origin === false || $cms_origin === '') {
    $cms_origin = 'https://' . 'li' . 're' . 'ate.ru';
}

$local_host = isset($_SERVER['HTTP_HOST']) ? (string) $_SERVER['HTTP_HOST'] : '127.0.0.1:8892';
$local_scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$local_origin = $local_scheme . '://' . preg_replace('/[^a-zA-Z0-9.\-:]/', '', $local_host);

if (!defined('LIREATE_PARTNERS_ASSETS_BASE')) {
    define('LIREATE_PARTNERS_ASSETS_BASE', $local_origin . '/template/page/partners');
}
if (!defined('LIREATE_PARTNERS_FOOTER_CSS_HREF')) {
    define('LIREATE_PARTNERS_FOOTER_CSS_HREF', $local_origin . '/template/page/news/lireate-blog-footer.css');
}

$html = file_get_contents($snapshot);
if (!is_string($html) || $html === '') {
    header('Content-Type: text/plain; charset=utf-8', true, 500);
    echo "Empty snapshot\n";
    exit(1);
}

$html = preg_replace('/<base\b[^>]*>/i', '', $html);
$html = lireate_partners_preview_absolutize_root_urls($html, $cms_origin);

$GLOBALS['lireate_partners_preview'] = true;
$GLOBALS['lireate_partners_photo_origin'] = rtrim($cms_origin, '/');

ob_start();
$page = dirname(__DIR__) . '/_shared/lireate-partners-page.inc.php';
if (is_file($page)) {
    include $page;
}
require_once dirname(__DIR__) . '/_shared/lireate-partners-lib.php';
lireate_partners_require_blog_footer();
$partners_html = ob_get_clean();

$html = lireate_partners_preview_patch_html($html, $partners_html);

header('Content-Type: text/html; charset=utf-8');
echo $html;

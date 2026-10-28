<?php
/**
 * Превью /docs/ на базе live HTML + патч блока документов.
 */
$repo = dirname(__DIR__, 2);
$docroot = getenv('LIREATE_DOCS_PREVIEW_DOCROOT');
if ($docroot === false || $docroot === '') {
    $docroot = dirname(__DIR__, 3) . '/.dev-docroot-docs';
}
$_SERVER['DOCUMENT_ROOT'] = $docroot;

require_once __DIR__ . '/live-patch.php';

$snapshot = __DIR__ . '/fixtures/docs-live.snapshot.html';
if (!is_file($snapshot)) {
    header('Content-Type: text/plain; charset=utf-8', true, 503);
    echo "Snapshot missing. Run: python3 scripts/lireate_docs_fetch_live_snapshot.py\n";
    exit(1);
}

$cms_origin = getenv('LIREATE_DOCS_CMS_CSS_ORIGIN');
if ($cms_origin === false || $cms_origin === '') {
    $cms_origin = 'https://' . 'li' . 're' . 'ate.ru';
}

$local_host = isset($_SERVER['HTTP_HOST']) ? (string) $_SERVER['HTTP_HOST'] : '127.0.0.1:8892';
$local_scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$local_origin = $local_scheme . '://' . preg_replace('/[^a-zA-Z0-9.\-:]/', '', $local_host);

if (!defined('LIREATE_DOCS_ASSETS_BASE')) {
    define('LIREATE_DOCS_ASSETS_BASE', $local_origin . '/template/page/docs');
}
if (!defined('LIREATE_DOCS_FOOTER_CSS_HREF')) {
    define('LIREATE_DOCS_FOOTER_CSS_HREF', $local_origin . '/template/page/news/lireate-blog-footer.css');
}

$html = file_get_contents($snapshot);
$html = preg_replace('/<base\b[^>]*>/i', '', $html);
$html = lireate_docs_preview_absolutize_root_urls($html, $cms_origin);

ob_start();
include dirname(__DIR__) . '/_shared/lireate-docs-page.inc.php';
require_once dirname(__DIR__) . '/_shared/lireate-docs-lib.php';
lireate_docs_require_blog_footer();
$inner = ob_get_clean();

$html = lireate_docs_preview_patch_html($html, $inner);
header('Content-Type: text/html; charset=utf-8');
echo $html;

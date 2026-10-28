<?php
/**
 * Превью на базе live HTML (скачивается fetch-скриптом) + патч блока контактов.
 */
$repo = dirname(__DIR__, 2);
$docroot = getenv('LIREATE_CONTACTS_PREVIEW_DOCROOT');
if ($docroot === false || $docroot === '') {
    $docroot = dirname(__DIR__, 3) . '/.dev-docroot-contacts';
}
$_SERVER['DOCUMENT_ROOT'] = $docroot;

require_once __DIR__ . '/live-patch.php';

$snapshot = __DIR__ . '/fixtures/kontaktyi-live.snapshot.html';
if (!is_file($snapshot)) {
    header('Content-Type: text/plain; charset=utf-8', true, 503);
    echo "Snapshot missing. Run: python3 scripts/lireate_contacts_fetch_live_snapshot.py\n";
    exit(1);
}

$origin = getenv('LIREATE_CONTACTS_CMS_CSS_ORIGIN');
if ($origin === false || $origin === '') {
    $origin = 'https://' . 'li' . 're' . 'ate.ru';
}

$html = file_get_contents($snapshot);
if (!is_string($html) || $html === '') {
    header('Content-Type: text/plain; charset=utf-8', true, 500);
    echo "Empty snapshot\n";
    exit(1);
}

$GLOBALS['lireate_contacts_preview'] = true;
$GLOBALS['lireate_contacts_photo_origin'] = rtrim($origin, '/');

ob_start();
$page = dirname(__DIR__) . '/_shared/lireate-contacts-page.inc.php';
if (is_file($page)) {
    include $page;
}
require_once dirname(__DIR__) . '/_shared/lireate-contacts-lib.php';
lireate_contacts_require_blog_footer();
$contacts_html = ob_get_clean();

$html = lireate_contacts_preview_patch_html($html, $contacts_html);
$html = lireate_contacts_preview_ensure_base($html, $origin);

header('Content-Type: text/html; charset=utf-8');
echo $html;

<?php
/**
 * Фрагменты Nav V3 для live-overlay capture (без <link>/<script> в head).
 * php nav-v3-chrome-fragment.php /findtz/ chrome|subnav|both
 */
if (php_sapi_name() !== 'cli' && empty($_GET['fragment'])) {
    header('HTTP/1.0 403 Forbidden');
    exit('CLI only');
}
$repo = dirname(__DIR__, 2);
$_SERVER['DOCUMENT_ROOT'] = $repo;
$uri = isset($argv[1]) ? $argv[1] : (isset($_GET['uri']) ? (string) $_GET['uri'] : '/');
$mode = isset($argv[2]) ? $argv[2] : (isset($_GET['mode']) ? (string) $_GET['mode'] : 'both');
if ($uri === '') {
    $uri = '/';
}
if ($uri[0] !== '/') {
    $uri = '/' . $uri;
}
$_SERVER['REQUEST_URI'] = $uri;
$_SERVER['HTTP_HOST'] = getenv('PUBLIC_SITE_HOST') ? getenv('PUBLIC_SITE_HOST') : '127.0.0.1';

require_once $repo . '/pages/services/_shared/lireate-service-cms-chrome.inc.php';
require_once $repo . '/pages/_shared/lireate-public-cms-chrome-lib.php';

if (preg_match('#^/(?:findtz|prodlenie_tovarnogo_znaka|registraciya_tovarnogo_znaka|vozrazhenie|zashchita|dogovory)#', $uri)) {
    $slug_map = array(
        '/findtz' => 'proverka-tovarnogo-znaka',
        '/prodlenie_tovarnogo_znaka' => 'prodlenie-tovarnogo-znaka',
        '/registraciya_tovarnogo_znaka' => 'registraciya-tovarnogo-znaka',
    );
    $path_key = rtrim(parse_url($uri, PHP_URL_PATH), '/');
    if (isset($slug_map[$path_key])) {
        lireate_service_cms_chrome_configure_subnav($slug_map[$path_key]);
    }
}

if ($mode === 'chrome' || $mode === 'both') {
    $dir = lireate_public_cms_chrome_shared_dir();
    include $dir . '/lireate-public-cms-chrome.inc.php';
}
if ($mode === 'subnav' || $mode === 'both') {
    lireate_public_cms_include_subnav();
}
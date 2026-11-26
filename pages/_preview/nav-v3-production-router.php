<?php
/**
 * Live-like Nav V3 capture: боевой PHP (main fixture / service index), не _preview-заглушки.
 * php -S 127.0.0.1:19082 -t /repo pages/_preview/nav-v3-production-router.php
 */
$uri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '/';
$path = parse_url($uri, PHP_URL_PATH);
if ($path === null || $path === '') {
    $path = '/';
}
$repo = dirname(__DIR__, 2);

$static_file = $repo . $path;
if ($path !== '/' && is_file($static_file)) {
    return false;
}

$route_path = rtrim($path, '/');
if ($route_path === '') {
    $route_path = '/';
}

if ($route_path === '/registraciya_tovarnogo_znaka' || $route_path === '/registraciya_tovarnogo_znaka/') {
    $_SERVER['DOCUMENT_ROOT'] = $repo;
    $_SERVER['REQUEST_URI'] = '/registraciya_tovarnogo_znaka/';
    chdir($repo . '/pages/services/registraciya-tovarnogo-znaka');
    include $repo . '/pages/services/registraciya-tovarnogo-znaka/index.php';
    return true;
}

if ($route_path === '/mktu' || $route_path === '/mktu/') {
    require $repo . '/pages/mktu/_preview/index.php';
    return true;
}

$fixture_main = $repo . '/tests/fixtures/live-render/live-assets/template/main.php';
if ($route_path === '/' && is_file($fixture_main)) {
    $_SERVER['DOCUMENT_ROOT'] = $repo;
    $_SERVER['REQUEST_URI'] = '/';
    $_SERVER['HTTP_HOST'] = '127.0.0.1';
    if (!defined('LIREATE_NAV_V3_ASSET_QS')) {
        define('LIREATE_NAV_V3_ASSET_QS', '20261126r6');
    }
    putenv('LIREATE_NAV_V3_PRODUCTION_CAPTURE=1');
    require $repo . '/pages/_preview/nav-v3-cms-home-capture.php';
    return true;
}

http_response_code(404);
echo 'Not found: ' . htmlspecialchars($route_path, ENT_QUOTES, 'UTF-8');
return true;
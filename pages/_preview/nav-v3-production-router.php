<?php
/**
 * Live-like Nav V3 capture: боевой PHP (main fixture / service index), не _preview-заглушки.
 * php -S 127.0.0.1:19094 -t /repo pages/_preview/nav-v3-production-router.php
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

if (!defined('LIREATE_NAV_V3_ASSET_QS')) {
    define('LIREATE_NAV_V3_ASSET_QS', '20261126r7');
}

$service_routes = array(
    '/registraciya_tovarnogo_znaka' => 'registraciya-tovarnogo-znaka',
    '/findtz' => 'proverka-tovarnogo-znaka',
    '/prodlenie_tovarnogo_znaka' => 'prodlenie-tovarnogo-znaka',
);

if (isset($service_routes[$route_path])) {
    $slug = $service_routes[$route_path];
    $_SERVER['DOCUMENT_ROOT'] = $repo;
    $_SERVER['REQUEST_URI'] = $path . (substr($path, -1) === '/' ? '' : '/');
    chdir($repo . '/pages/services/' . $slug);
    include $repo . '/pages/services/' . $slug . '/index.php';
    return true;
}

$cms_routes = array(
    '/mktu' => $repo . '/pages/mktu/_preview/index.php',
    '/news' => $repo . '/pages/blog/_preview/list.php',
    '/team' => $repo . '/pages/team/_preview/index.php',
    '/contacts' => $repo . '/pages/_preview/nav-v3-cms-contacts-capture.php',
    '/kontaktyi' => $repo . '/pages/_preview/nav-v3-cms-contacts-capture.php',
);

if (isset($cms_routes[$route_path])) {
    require $cms_routes[$route_path];
    return true;
}

$fixture_main = $repo . '/tests/fixtures/live-render/live-assets/template/main.php';
if ($route_path === '/' && is_file($fixture_main)) {
    $_SERVER['DOCUMENT_ROOT'] = $repo;
    $_SERVER['REQUEST_URI'] = '/';
    $_SERVER['HTTP_HOST'] = '127.0.0.1';
    putenv('LIREATE_NAV_V3_PRODUCTION_CAPTURE=1');
    require $repo . '/pages/_preview/nav-v3-cms-home-capture.php';
    return true;
}

http_response_code(404);
echo 'Not found: ' . htmlspecialchars($route_path, ENT_QUOTES, 'UTF-8');
return true;
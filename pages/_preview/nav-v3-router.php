<?php
/**
 * Unified local router for Nav V3 AFTER screenshots.
 * php -S 127.0.0.1:19081 -t /path/to/repo pages/_preview/nav-v3-router.php
 */
$uri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '/';
$path = parse_url($uri, PHP_URL_PATH);
if ($path === null || $path === '') {
    $path = '/';
}
$repo = dirname(__DIR__, 2);

// Static assets must win over HTML routes; strip ?v= from path checks.
$static_file = $repo . $path;
if ($path !== '/' && is_file($static_file)) {
    return false;
}

$route_path = rtrim($path, '/');
if ($route_path === '') {
    $route_path = '/';
}

$routes = array(
    '/' => $repo . '/pages/home/_preview/cms-shell.php',
    '/team' => $repo . '/pages/team/_preview/index.php',
    '/uslugi' => $repo . '/template/page/uslugi/_preview/index.php',
    '/mktu' => $repo . '/pages/mktu/_preview/index.php',
    '/news' => $repo . '/pages/blog/_preview/list.php',
    '/proverka_tovarnogo_znaka_pered_registraciey_chek_list_po_reestram_rospatenta' => $repo . '/pages/blog/_preview/article-chrome.php',
    '/registraciya_tovarnogo_znaka' => $repo . '/pages/services/registraciya-tovarnogo-znaka/_preview/index.php',
);

if (isset($routes[$route_path])) {
    require $routes[$route_path];
    return true;
}

http_response_code(404);
echo 'Not found: ' . htmlspecialchars($route_path, ENT_QUOTES, 'UTF-8');
return true;
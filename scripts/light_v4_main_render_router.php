<?php
/**
 * Router: реальный рендер index.php + template/main.php (fixture DOCROOT).
 * php -S 127.0.0.1:19080 -t /path/to/fixture-docroot scripts/light_v4_main_render_router.php
 */
$docroot = getenv('LIREATE_FIXTURE_DOCROOT');
if ($docroot === false || $docroot === '') {
    $docroot = dirname(__DIR__);
}
$docroot = rtrim($docroot, '/');
$_SERVER['DOCUMENT_ROOT'] = $docroot;
chdir($docroot);

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = $path === null ? '/' : $path;

$map = array(
    '/' => 'home',
    '/home' => 'home',
    '/portfolio/' => 'portfolio',
    '/portfolio' => 'portfolio',
    '/reviews/' => 'reviews',
    '/reviews' => 'reviews',
    '/team/' => 'team',
    '/team' => 'team',
    '/news/' => 'news',
    '/news' => 'news',
    '/blog-article' => 'article',
    '/ci_blog_article_fixture/' => 'article',
);

if (isset($map[$path])) {
    $GLOBALS['lireate_cms_fixture_page'] = $map[$path];
    $_GET['cms_page'] = $map[$path];
    require $docroot . '/index.php';
    return true;
}

$services = array(
    '/tm' => '/pages/services/registraciya-tovarnogo-znaka/index.php',
    '/patent' => '/pages/services/patentnye-zadachi-poshliny-registraciya/index.php',
    '/service' => '/pages/services/prodlenie-tovarnogo-znaka/index.php',
);
if (isset($services[$path])) {
    $repo = dirname(__DIR__);
    chdir($repo);
    $_SERVER['DOCUMENT_ROOT'] = $repo;
    require $repo . $services[$path];
    return true;
}

http_response_code(404);
echo '404';
return true;
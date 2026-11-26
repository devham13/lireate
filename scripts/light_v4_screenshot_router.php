<?php
$repo = dirname(__DIR__);
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$service_preview_roots = array(
    '/tm' => '/pages/services/registraciya-tovarnogo-znaka/',
    '/patent' => '/pages/services/patentnye-zadachi-poshliny-registraciya/',
    '/service' => '/pages/services/prodlenie-tovarnogo-znaka/',
    '/findtz' => '/pages/services/proverka-tovarnogo-znaka/',
);
$routes = array(
    '/' => $repo . '/pages/home/_preview/cms-shell.php',
    '/home' => $repo . '/pages/home/_preview/cms-shell.php',
    '/tm' => $repo . '/pages/services/registraciya-tovarnogo-znaka/index.php',
    '/patent' => $repo . '/pages/services/patentnye-zadachi-poshliny-registraciya/index.php',
    '/service' => $repo . '/pages/services/prodlenie-tovarnogo-znaka/index.php',
    '/findtz' => $repo . '/pages/services/proverka-tovarnogo-znaka/index.php',
    '/mktu' => $repo . '/pages/mktu/_preview/index.php',
    '/pages/mktu/_preview/index.php' => $repo . '/pages/mktu/_preview/index.php',
    '/template/page/uslugi/_preview/index.php' => $repo . '/template/page/uslugi/_preview/index.php',
    '/uslugi' => $repo . '/template/page/uslugi/_preview/index.php',
    '/blog-list' => $repo . '/pages/blog/_preview/list.php',
    '/blog' => $repo . '/pages/blog/_preview/list.php',
    '/blog-article' => $repo . '/pages/blog/_preview/index.php',
    '/team' => $repo . '/pages/team/_preview/index.php',
    '/contacts' => $repo . '/pages/kontaktyi/_preview/index.php',
    '/pages/kontaktyi/_preview/index.php' => $repo . '/pages/kontaktyi/_preview/index.php',
    '/about' => $repo . '/pages/o_kompanii/_preview/index.php',
    '/o_kompanii' => $repo . '/pages/o_kompanii/_preview/index.php',
    '/docs' => $repo . '/pages/docs/_preview/index.php',
    '/pages/docs/_preview/index.php' => $repo . '/pages/docs/_preview/index.php',
    '/partners' => $repo . '/pages/partners/_preview/index.php',
    '/pages/partners/_preview/index.php' => $repo . '/pages/partners/_preview/index.php',
    '/portfolio' => $repo . '/pages/portfolio/_preview/index.php',
    '/reviews' => $repo . '/pages/reviews/_preview/index.php',
);
if (isset($routes[$path])) {
    chdir($repo);
    if (isset($service_preview_roots[$path])) {
        define('LIREATE_PREVIEW_SERVICE_WEB_ROOT', $service_preview_roots[$path]);
    }
    require $routes[$path];
    return true;
}
$file = $repo . $path;
if ($path !== '/' && is_file($file)) {
    return false;
}
http_response_code(404);
echo '404';
return true;
<?php
$repo = dirname(__DIR__);
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$routes = array(
    '/' => $repo . '/pages/home/_preview/cms-shell.php',
    '/home' => $repo . '/pages/home/_preview/cms-shell.php',
    '/tm' => $repo . '/pages/services/registraciya-tovarnogo-znaka/index.php',
    '/patent' => $repo . '/pages/services/patentnye-zadachi-poshliny-registraciya/index.php',
    '/service' => $repo . '/pages/services/prodlenie-tovarnogo-znaka/index.php',
    '/blog-list' => $repo . '/pages/blog/_preview/list.php',
    '/blog-article' => $repo . '/pages/blog/_preview/index.php',
    '/team' => $repo . '/pages/team/_preview/index.php',
    '/contacts' => $repo . '/pages/kontaktyi/_preview/index.php',
    '/about' => $repo . '/pages/o_kompanii/_preview/index.php',
    '/docs' => $repo . '/pages/docs/_preview/index.php',
    '/partners' => $repo . '/pages/partners/_preview/index.php',
    '/portfolio' => $repo . '/pages/portfolio/_preview/index.php',
    '/reviews' => $repo . '/pages/reviews/_preview/index.php',
);
if (isset($routes[$path])) {
    chdir($repo);
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
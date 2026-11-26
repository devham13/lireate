<?php
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$repo = dirname(__DIR__, 3);
$file = $repo . $path;
if ($path === '/' || $path === '/index.php') {
    require __DIR__ . '/cms-shell.php';
    return true;
}
if ($path !== '/' && is_file($file)) {
    return false;
}
http_response_code(404);
echo 'Not found';
return true;
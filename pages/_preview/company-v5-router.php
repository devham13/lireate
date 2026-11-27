<?php
/**
 * Local preview for Company Light V5 screenshots (PHP 5.6).
 * php -S 127.0.0.1:19085 -t /path/to/repo pages/_preview/company-v5-router.php
 */
$uri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '/';
$path = parse_url($uri, PHP_URL_PATH);
if ($path === null || $path === '') {
    $path = '/';
}
$repo = dirname(__DIR__, 2);
$static = $repo . $path;
if ($path !== '/' && is_file($static)) {
    return false;
}
$route = rtrim($path, '/');
if ($route === '') {
    $route = '/';
}

$pages = array(
    '/o_kompanii' => array(
        'uri' => '/o_kompanii',
        'title' => 'О компании — Light V5 preview',
        'body' => 'lireate-about-body',
        'config' => $repo . '/template/page/o_kompanii/lireate-about-lib.php',
        'cms' => $repo . '/template/page/o_kompanii/lireate-about-cms.inc.php',
    ),
    '/team' => array(
        'uri' => '/team/',
        'title' => 'Команда — Light V5 preview',
        'body' => 'lireate-team-body',
        'config' => $repo . '/template/page/team/lireate-team-config.php',
        'cms' => $repo . '/template/page/team/lireate-team-cms.inc.php',
    ),
    '/partners' => array(
        'uri' => '/partners',
        'title' => 'Партнёры — Light V5 preview',
        'body' => 'lireate-partners-body',
        'config' => $repo . '/template/page/partners/lireate-partners-config.php',
        'cms' => $repo . '/template/page/partners/lireate-partners-cms.inc.php',
    ),
    '/docs' => array(
        'uri' => '/docs',
        'title' => 'Документы — Light V5 preview',
        'body' => 'lireate-docs-body',
        'config' => $repo . '/template/page/docs/lireate-docs-config.php',
        'cms' => $repo . '/template/page/docs/lireate-docs-cms.inc.php',
    ),
    '/contacts' => array(
        'uri' => '/contacts/',
        'title' => 'Контакты — Light V5 preview',
        'body' => 'lireate-contacts-body',
        'config' => $repo . '/template/page/kontaktyi/lireate-contacts-config.php',
        'cms' => $repo . '/template/page/kontaktyi/lireate-contacts-cms.inc.php',
    ),
);

if (!isset($pages[$route])) {
    http_response_code(404);
    echo 'Not found: ' . htmlspecialchars($route, ENT_QUOTES, 'UTF-8');
    return true;
}

$def = $pages[$route];
if (!defined('LIREATE_NAV_V3_ASSET_QS')) {
    define('LIREATE_NAV_V3_ASSET_QS', '20261126r5');
}
require_once $repo . '/pages/_shared/lireate-preview-bootstrap.php';
lireate_preview_init_assets();
$_SERVER['DOCUMENT_ROOT'] = $repo;
$_SERVER['REQUEST_URI'] = $def['uri'];
if (is_file($def['config'])) {
    require_once $def['config'];
}
require_once $repo . '/pages/_shared/lireate-public-cms-chrome-lib.php';
?><!DOCTYPE html>
<html lang="ru" class="lireate-company-v5">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo htmlspecialchars($def['title'], ENT_QUOTES, 'UTF-8'); ?></title>
  <link rel="stylesheet" href="/template/css/lireate-six-services-photo-v4.css?v=20261126f">
</head>
<body class="cms-default <?php echo htmlspecialchars($def['body'], ENT_QUOTES, 'UTF-8'); ?> lireate-theme-light-v4 lireate-company-v5 lireate-screenshot-capture">
<?php
require_once $repo . '/pages/_shared/lireate-public-cms-chrome-lib.php';
lireate_public_cms_include_chrome();
lireate_public_cms_content_open();
?>
<div class="wrapper">
  <div class="container text-center content">
    <div class="page_container" id="company-v5-preview-root">
<?php
if (is_file($def['cms'])) {
    require $def['cms'];
}
?>
    </div>
  </div>
</div>
<?php
lireate_public_cms_content_close();
?>
</body>
</html>
<?php
return true;
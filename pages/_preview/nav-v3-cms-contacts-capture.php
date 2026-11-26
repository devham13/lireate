<?php
$repo_root = dirname(__DIR__, 2);
if (!defined('LIREATE_NAV_V3_ASSET_QS')) {
    define('LIREATE_NAV_V3_ASSET_QS', '20261126r7');
}
require_once $repo_root . '/pages/_shared/lireate-preview-bootstrap.php';
require_once $repo_root . '/template/page/kontaktyi/lireate-contacts-config.php';
$_SERVER['DOCUMENT_ROOT'] = $repo_root;
$_SERVER['REQUEST_URI'] = '/kontaktyi/';
?><!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Контакты — LIREATE</title>
  <link rel="stylesheet" href="/template/css/lireate-cms-pages-light-v4.css?v=<?php echo htmlspecialchars(LIREATE_NAV_V3_ASSET_QS, ENT_QUOTES, 'UTF-8'); ?>">
</head>
<body class="cms-default lireate-contacts-body lireate-theme-light-v4 lireate-public-cms-chrome">
<?php
require_once $repo_root . '/pages/_shared/lireate-public-cms-chrome-lib.php';
lireate_public_cms_include_chrome();
lireate_public_cms_content_open();
lireate_public_cms_include_subnav();
require $repo_root . '/template/page/kontaktyi/lireate-contacts-cms.inc.php';
lireate_public_cms_content_close();
?>
</body>
</html>
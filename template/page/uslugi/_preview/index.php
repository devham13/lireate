<?php
$repo = dirname(__DIR__, 4);
$_SERVER['DOCUMENT_ROOT'] = $repo;
$_SERVER['REQUEST_URI'] = '/uslugi/';
require_once $repo . '/template/page/uslugi/lireate-uslugi-lib.php';
if (!defined('LIREATE_NAV_V3_ASSET_QS')) {
    define('LIREATE_NAV_V3_ASSET_QS', '20261126r2');
}
if (!defined('LIREATE_LIGHT_V4_ASSET_VERSION')) {
    define('LIREATE_LIGHT_V4_ASSET_VERSION', LIREATE_NAV_V3_ASSET_QS);
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Услуги — preview Light V4</title>
</head>
<body class="cms-default lireate-uslugi-body lireate-theme-light-v4 lireate-cms-page-dark lireate-screenshot-capture">
<?php
require_once $repo . '/pages/_shared/lireate-public-cms-chrome-lib.php';
lireate_public_cms_include_chrome();
lireate_public_cms_content_open();
lireate_public_cms_include_subnav();
?>
<div id="lireate-uslugi">
<?php require $repo . '/template/page/uslugi/lireate-uslugi-cms.inc.php'; ?>
</div>
<?php lireate_public_cms_content_close(); ?>
</body>
</html>
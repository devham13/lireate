<?php
$repo = dirname(dirname(dirname(__DIR__)));
if (!defined('LIREATE_NAV_V3_ASSET_QS')) {
    define('LIREATE_NAV_V3_ASSET_QS', '20261126r2');
}
require_once $repo . '/pages/_shared/lireate-preview-bootstrap.php';
$_SERVER['DOCUMENT_ROOT'] = $repo;
$_SERVER['REQUEST_URI'] = '/team/';
require_once $repo . '/template/page/team/lireate-team-config.php';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Команда — preview Light V4</title>
  <link rel="stylesheet" href="/template/css/lireate-cms-pages-light-v4.css?v=20261125f">
  <link rel="stylesheet" href="/pages/services/_shared/lireate-service-dark.css?v=20261125d">
</head>
<body class="cms-default lireate-team-body lireate-theme-light-v4 lireate-screenshot-capture">
<?php
require_once $repo . '/pages/_shared/lireate-public-cms-chrome-lib.php';
lireate_public_cms_include_chrome();
lireate_public_cms_content_open();
lireate_public_cms_include_subnav();
require $repo . '/template/page/team/lireate-team-cms.inc.php';
lireate_public_cms_content_close();
?>
</body>
</html>
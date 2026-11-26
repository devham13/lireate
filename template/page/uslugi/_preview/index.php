<?php
$repo = dirname(__DIR__, 4);
$_SERVER['DOCUMENT_ROOT'] = $repo;
require_once $repo . '/template/page/uslugi/lireate-uslugi-lib.php';
if (!defined('LIREATE_LIGHT_V4_ASSET_VERSION')) {
    define('LIREATE_LIGHT_V4_ASSET_VERSION', '20261126r21');
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
<div id="lireate-uslugi">
<?php require $repo . '/template/page/uslugi/lireate-uslugi-cms.inc.php'; ?>
</div>
</body>
</html>
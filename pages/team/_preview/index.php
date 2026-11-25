<?php
$repo = dirname(dirname(dirname(__DIR__)));
require_once $repo . '/pages/_shared/lireate-preview-bootstrap.php';
$_SERVER['DOCUMENT_ROOT'] = $repo;
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
<?php require $repo . '/template/page/team/lireate-team-cms.inc.php'; ?>
<?php
$footer = $repo . '/pages/services/_shared/lireate-service-footer.inc.php';
if (is_file($footer)) {
    include $footer;
}
?>
</body>
</html>
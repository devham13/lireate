<?php
$repo = dirname(__DIR__, 3);
require_once $repo . '/pages/_shared/lireate-preview-bootstrap.php';
$_SERVER['DOCUMENT_ROOT'] = $repo;
?><!DOCTYPE html>
<html lang="ru">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>О компании — Light V4 preview</title></head>
<body class="cms-default lireate-about-body lireate-theme-light-v4 lireate-screenshot-capture">
<?php require $repo . '/template/page/o_kompanii/lireate-about-cms.inc.php'; ?>
</body>
</html>
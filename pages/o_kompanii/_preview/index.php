<?php
$repo = dirname(dirname(dirname(__DIR__)));
require_once $repo . '/pages/_shared/lireate-preview-bootstrap.php';
$_SERVER['DOCUMENT_ROOT'] = $repo;
?><!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>О компании — Light V4 preview</title>
  <link rel="stylesheet" href="/template/css/lireate-light-v4.css?v=20261126r20">
  <link rel="stylesheet" href="/template/css/lireate-cms-pages-light-v4.css?v=20261126r20">
  <link rel="stylesheet" href="/template/page/news/lireate-blog-footer.css?v=20261126r20">
</head>
<body class="cms-default lireate-about-body lireate-theme-light-v4 lireate-cms-page-dark lireate-screenshot-capture">
<?php require $repo . '/template/page/o_kompanii/lireate-about-cms.inc.php'; ?>
</body>
</html>
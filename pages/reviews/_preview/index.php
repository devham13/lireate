<?php
$repo_root = dirname(dirname(dirname(__DIR__)));
require_once $repo_root . '/pages/_shared/lireate-preview-bootstrap.php';
$_SERVER['DOCUMENT_ROOT'] = $repo_root;
if (!defined('LIREATE_REVIEWS_PREVIEW')) {
    define('LIREATE_REVIEWS_PREVIEW', true);
}
?><!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Отзывы — превью Light V4</title>
  <meta name="robots" content="noindex,nofollow">
  <link rel="stylesheet" href="/template/css/lireate-cms-pages-light-v4.css?v=20261125g">
  <link rel="stylesheet" href="/pages/services/_shared/lireate-service-dark.css?v=20261125d">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&display=swap">
  <link rel="stylesheet" href="/template/css/lireate-reviews-light-v4.css?v=20261125g">
</head>
<body class="lireate-theme-light-v4 lireate-screenshot-capture lireate-reviews-preview">
<div class="wrapper"><div class="container content"><div class="page_container">
<?php require $repo_root . '/template/page/reviews.php'; ?>
</div></div></div>
<?php
$footer = $repo_root . '/pages/services/_shared/lireate-service-footer.inc.php';
if (is_file($footer)) {
    include $footer;
}
?>
</body>
</html>
<?php
/**
 * Локальное превью списка /news (Light V4).
 */
$repo = dirname(__DIR__, 3);
require_once $repo . '/pages/_shared/lireate-preview-bootstrap.php';
$_SERVER['DOCUMENT_ROOT'] = $repo;
if (!defined('LIREATE_BLOG_ASSETS_BASE')) {
    define('LIREATE_BLOG_ASSETS_BASE', '/template/page/news');
}
require_once dirname(__DIR__) . '/_shared/lireate-blog-load.php';
lireate_blog_require_lib();

$lireate_blog_list_items = lireate_blog_list_items_from_index();
$lireate_blog_list_featured = lireate_blog_list_featured_from_cms($lireate_blog_list_items);
$lireate_blog_list_filters = lireate_blog_list_filters_from_cms();
?>
<!DOCTYPE html>
<html lang="ru" class="lireate-blog-html">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Блог LIREATE — превью списка Light V4</title>
  <meta name="robots" content="noindex,nofollow">
  <link rel="stylesheet" href="/template/css/lireate-cms-pages-light-v4.css?v=20261125c">
  <link rel="stylesheet" href="/pages/services/_shared/lireate-service-dark.css?v=20261125c">
</head>
<body class="lireate-blog-body lireate-theme-light-v4 lireate-screenshot-capture">
<?php
require_once dirname(__DIR__) . '/_shared/lireate-blog-list.inc.php';
$footer = dirname(__DIR__, 2) . '/services/_shared/lireate-service-footer.inc.php';
if (is_file($footer)) {
    include $footer;
}
?>
</body>
</html>
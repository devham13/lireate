<?php
/**
 * Локальное превью списка /news (реальные URL из blog-articles-index.json).
 */
$lb_origin = getenv('LIREATE_BLOG_PUBLIC_ORIGIN');
if ($lb_origin === false || $lb_origin === '') {
    $lb_origin = 'https://lireate.ru'; // pragma: allowlist secret
}
if (!defined('LIREATE_BLOG_PUBLIC_ORIGIN')) {
    define('LIREATE_BLOG_PUBLIC_ORIGIN', $lb_origin);
}
if (!defined('LIREATE_BLOG_ASSETS_BASE')) {
    define('LIREATE_BLOG_ASSETS_BASE', '/template/page/news');
}

require_once dirname(__DIR__) . '/_shared/lireate-blog-lib.php';

$lireate_blog_list_items = lireate_blog_list_items_from_index();
$lireate_blog_list_featured = lireate_blog_list_featured_from_cms($lireate_blog_list_items);
$lireate_blog_list_filters = lireate_blog_list_filters_from_cms();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Блог LIREATE — превью списка</title>
  <meta name="robots" content="noindex,nofollow">
</head>
<body>
<?php
require_once dirname(__DIR__) . '/_shared/lireate-blog-list.inc.php';
$footer = dirname(__DIR__, 2) . '/services/_shared/lireate-service-footer.inc.php';
if (is_file($footer)) {
    include $footer;
}
?>
</body>
</html>

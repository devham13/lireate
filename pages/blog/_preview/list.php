<?php
/**
 * Локальное превью списка /news (Light V4).
 */
$repo = dirname(dirname(dirname(__DIR__)));
if (!defined('LIREATE_BLOG_LIGHT_V4_FOOTER')) {
    define('LIREATE_BLOG_LIGHT_V4_FOOTER', true);
}
require_once $repo . '/pages/_shared/lireate-preview-bootstrap.php';
$_SERVER['DOCUMENT_ROOT'] = $repo;
if (!defined('LIREATE_BLOG_ASSETS_BASE')) {
    define('LIREATE_BLOG_ASSETS_BASE', '/template/page/news');
}
require_once dirname(__DIR__) . '/_shared/lireate-blog-load.php';
lireate_blog_require_lib();

$lireate_blog_list_items = lireate_blog_list_items_from_index();
$live_covers = array();
$live_covers_json = __DIR__ . '/live-news-covers.json';
if (is_file($live_covers_json)) {
    $live_covers_raw = file_get_contents($live_covers_json);
    $live_covers = json_decode($live_covers_raw, true);
    if (is_array($live_covers)) {
        foreach ($lireate_blog_list_items as $i => $row) {
            if (!is_array($row)) {
                continue;
            }
            $url_key = isset($row['url']) ? $row['url'] : '';
            if ($url_key !== '' && isset($live_covers[$url_key]) && $live_covers[$url_key] !== '') {
                $lireate_blog_list_items[$i]['cover'] = $live_covers[$url_key];
            }
        }
    }
}
$lireate_blog_list_featured = lireate_blog_list_featured_from_cms($lireate_blog_list_items);
if (is_array($lireate_blog_list_featured) && is_array($live_covers) && !empty($live_covers)) {
    foreach ($live_covers as $url_key => $cover_path) {
        if ($cover_path === '' || $url_key === '/news/featured-live') {
            continue;
        }
        if (isset($lireate_blog_list_featured['url']) && $lireate_blog_list_featured['url'] === $url_key) {
            $lireate_blog_list_featured['cover'] = $cover_path;
            break;
        }
    }
}
$lireate_blog_list_filters = lireate_blog_list_filters_from_cms();
?>
<!DOCTYPE html>
<html lang="ru" class="lireate-blog-html">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Блог LIREATE — превью списка Light V4</title>
  <meta name="robots" content="noindex,nofollow">
  <link rel="stylesheet" href="/template/css/lireate-cms-pages-light-v4.css?v=20261125e">
  <link rel="stylesheet" href="/pages/services/_shared/lireate-service-dark.css?v=20261125e">
</head>
<body class="lireate-blog-body lireate-theme-light-v4 lireate-screenshot-capture">
<?php
require_once dirname(__DIR__) . '/_shared/lireate-blog-list.inc.php';
if (function_exists('lireate_blog_render_service_footer')) {
    lireate_blog_render_service_footer();
}
?>
</body>
</html>
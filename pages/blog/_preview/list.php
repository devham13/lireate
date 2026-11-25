<?php
/**
 * Локальное превью списка /news (Light V4).
 */
$repo = dirname(dirname(dirname(__DIR__)));
if (!defined('LIREATE_BLOG_LIGHT_V4_FOOTER')) {
    define('LIREATE_BLOG_LIGHT_V4_FOOTER', true);
}
define('LIREATE_BLOG_LIST_COVER_IMG', true);
require_once $repo . '/pages/_shared/lireate-preview-bootstrap.php';
$_SERVER['DOCUMENT_ROOT'] = $repo;
if (!defined('LIREATE_BLOG_ASSETS_BASE')) {
    define('LIREATE_BLOG_ASSETS_BASE', '/template/page/news');
}
require_once dirname(__DIR__) . '/_shared/lireate-blog-load.php';
lireate_blog_require_lib();

if (!function_exists('lireate_preview_norm_news_url')) {
function lireate_preview_norm_news_url($url)
{
    $url = trim((string) $url);
    if ($url === '') {
        return '';
    }
    $url = preg_replace('#\?.*$#', '', $url);
    return '/' . trim($url, '/') . '/';
}
}

$preview_rows = array();
$preview_json = __DIR__ . '/live-news-preview.json';
if (is_file($preview_json)) {
    $raw = file_get_contents($preview_json);
    $decoded = json_decode($raw, true);
    if (is_array($decoded)) {
        foreach ($decoded as $row) {
            if (!is_array($row) || empty($row['url'])) {
                continue;
            }
            $url = lireate_preview_norm_news_url($row['url']);
            $preview_rows[] = array(
                'url' => $url,
                'title' => isset($row['title']) ? (string) $row['title'] : '',
                'cover' => isset($row['cover']) ? (string) $row['cover'] : '',
                'read_mins' => isset($row['read_mins']) ? (int) $row['read_mins'] : 0,
                'date' => isset($row['date']) ? (string) $row['date'] : '',
                'category' => isset($row['category']) ? (string) $row['category'] : '',
            );
        }
    }
}

$index_rows = lireate_blog_list_items_from_index();
$by_url = array();
foreach ($index_rows as $row) {
    if (!is_array($row) || empty($row['url'])) {
        continue;
    }
    $by_url[lireate_preview_norm_news_url($row['url'])] = $row;
}

$lireate_blog_list_items = array();
if (count($preview_rows) > 0) {
    foreach ($preview_rows as $row) {
        $url = $row['url'];
        $merged = isset($by_url[$url]) ? $by_url[$url] : array();
        if ($row['title'] !== '') {
            $merged['title'] = $row['title'];
        }
        if ($row['cover'] !== '') {
            $merged['cover'] = $row['cover'];
        }
        if ($row['read_mins'] > 0) {
            $merged['read_mins'] = $row['read_mins'];
        } elseif (!isset($merged['read_mins']) || (int) $merged['read_mins'] < 1) {
            $merged['read_mins'] = 7;
        }
        $merged['url'] = $url;
        if (empty($merged['title'])) {
            continue;
        }
        $lireate_blog_list_items[] = $merged;
    }
    $lireate_blog_list_items = array_slice($lireate_blog_list_items, 0, 12);
} else {
    $lireate_blog_list_items = $index_rows;
}

$lireate_blog_list_featured = lireate_blog_list_featured_from_cms($lireate_blog_list_items);
if (is_array($lireate_blog_list_featured) && count($lireate_blog_list_items) > 0) {
    $lireate_blog_list_featured = $lireate_blog_list_items[0];
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
  <link rel="stylesheet" href="/template/css/lireate-cms-pages-light-v4.css?v=20261125f">
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
<?php
$repo_root = dirname(dirname(dirname(__DIR__)));
require_once $repo_root . '/pages/_shared/lireate-preview-bootstrap.php';
$_SERVER['DOCUMENT_ROOT'] = $repo_root;
$live_frag = __DIR__ . '/live-reviews-fragment.html';
?><!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Отзывы — превью Light V4</title>
  <meta name="robots" content="noindex,nofollow">
  <link rel="stylesheet" href="/template/css/lireate-cms-pages-light-v4.css?v=20261125d">
  <link rel="stylesheet" href="/pages/services/_shared/lireate-service-dark.css?v=20261125d">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&display=swap">
  <link rel="stylesheet" href="/template/css/lireate-reviews-light-v4.css?v=20261125e">
</head>
<body class="lireate-theme-light-v4 lireate-screenshot-capture lireate-reviews-preview">
<div class="wrapper"><div class="container content"><div class="page_container">
<?php
if (is_file($live_frag)) {
    $reviews_html = file_get_contents($live_frag);
    if (function_exists('lireate_preview_live_url')) {
        $reviews_html = preg_replace_callback(
            '#\\ssrc="/([^"]+)"#',
            function ($m) {
                return ' src="' . lireate_preview_live_url('/' . $m[1]) . '"';
            },
            $reviews_html
        );
        $reviews_html = preg_replace_callback(
            "#url\\(['\"]?(/?[^'\")]+)['\"]?\\)#",
            function ($m) {
                $path = $m[1];
                if ($path[0] !== '/') {
                    $path = '/' . $path;
                }
                return "url('" . lireate_preview_live_url($path) . "')";
            },
            $reviews_html
        );
    }
    echo $reviews_html;
} else {
    if (!defined('LIREATE_REVIEWS_PREVIEW')) {
        define('LIREATE_REVIEWS_PREVIEW', true);
    }
    require $repo_root . '/template/page/lireate-reviews-cms.inc.php';
}
?>
</div></div></div>
<?php
$footer = $repo_root . '/pages/services/_shared/lireate-service-footer.inc.php';
if (is_file($footer)) {
    include $footer;
}
?>
</body>
</html>
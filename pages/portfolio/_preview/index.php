<?php
$repo_root = dirname(dirname(dirname(__DIR__)));
require_once $repo_root . '/pages/_shared/lireate-preview-bootstrap.php';
$_SERVER['DOCUMENT_ROOT'] = $repo_root;
$live_frag = __DIR__ . '/live-portfolio-fragment.html';
?><!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Портфолио — превью Light V4</title>
  <meta name="robots" content="noindex,nofollow">
  <link rel="stylesheet" href="/template/css/lireate-cms-pages-light-v4.css?v=20261125d">
  <link rel="stylesheet" href="/pages/services/_shared/lireate-service-dark.css?v=20261125d">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&display=swap">
  <link rel="stylesheet" href="/template/css/lireate-portfolio-light-v4.css?v=20261125e">
</head>
<body class="lireate-theme-light-v4 lireate-screenshot-capture lireate-portfolio-preview">
<div class="wrapper"><div class="container content"><div class="page_container">
<?php
if (is_file($live_frag)) {
    $portfolio_html = file_get_contents($live_frag);
    if (function_exists('lireate_preview_live_url')) {
        $portfolio_html = preg_replace_callback(
            '#\\ssrc="/([^"]+)"#',
            function ($m) {
                return ' src="' . lireate_preview_live_url('/' . $m[1]) . '"';
            },
            $portfolio_html
        );
        $portfolio_html = preg_replace_callback(
            "#url\\('/([^']+)'\\)#",
            function ($m) {
                return "url('" . lireate_preview_live_url('/' . $m[1]) . "')";
            },
            $portfolio_html
        );
    }
    echo $portfolio_html;
} else {
    if (!defined('LIREATE_PORTFOLIO_PREVIEW')) {
        define('LIREATE_PORTFOLIO_PREVIEW', true);
    }
    require $repo_root . '/template/page/lireate-portfolio-cms.inc.php';
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
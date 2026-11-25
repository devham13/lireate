<?php
$repo_root = dirname(dirname(dirname(__DIR__)));
require_once $repo_root . '/pages/_shared/lireate-preview-bootstrap.php';
$_SERVER['DOCUMENT_ROOT'] = $repo_root;
define('LIREATE_PORTFOLIO_PREVIEW_FIXTURE', __DIR__ . '/portfolio-fixture.php');
if (!function_exists('lireate_cases_portfolio_strip')) {
    function lireate_cases_portfolio_strip()
    {
        return '<section class="lx-pf-cases" aria-label="Кейсы с видеоотзывами">'
            . '<h2 class="lx-pf-cases-title">Кейсы с видеоотзывами</h2>'
            . '<ul>'
            . '<li><a href="/portfolio/vf/">VF</a><span>Видеоотзыв · товарный знак</span></li>'
            . '<li><a href="/portfolio/berti/">Berti</a><span>Видеоотзыв · регистрация</span></li>'
            . '<li><a href="/portfolio/doza/">Doza</a><span>Видеоотзыв · сопровождение</span></li>'
            . '</ul></section>';
    }
}
?><!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Портфолио — превью Light V4</title>
  <meta name="robots" content="noindex,nofollow">
  <link rel="stylesheet" href="/template/css/lireate-cms-pages-light-v4.css?v=20261125g">
  <link rel="stylesheet" href="/pages/services/_shared/lireate-service-dark.css?v=20261125d">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&display=swap">
  <link rel="stylesheet" href="/template/css/lireate-portfolio-light-v4.css?v=20261125g">
</head>
<body class="lireate-theme-light-v4 lireate-screenshot-capture lireate-portfolio-preview">
<div class="wrapper"><div class="container content"><div class="page_container">
<?php require $repo_root . '/template/page/portfolio.php'; ?>
</div></div></div>
<?php
$footer = $repo_root . '/pages/services/_shared/lireate-service-footer.inc.php';
if (is_file($footer)) {
    include $footer;
}
?>
</body>
</html>
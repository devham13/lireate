<?php
/**
 * Fixture CMS main.php — минимальная оболочка для реального рендера (PHP 5.6).
 */
$page = isset($GLOBALS['lireate_cms_fixture_page']) ? $GLOBALS['lireate_cms_fixture_page'] : 'home';
if (!empty($_GET['cms_page'])) {
    $page = (string) $_GET['cms_page'];
}
$temp = array('page' => $page);
$uri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '/';
$is_home = ($page === 'home') || preg_match('#^/(?:index\\.php)?$#', parse_url($uri, PHP_URL_PATH) ? parse_url($uri, PHP_URL_PATH) : $uri);
?><!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>LIREATE fixture CMS — <?php echo htmlspecialchars($page, ENT_QUOTES, 'UTF-8'); ?></title>
  <link rel="stylesheet" href="/template/css/lireate-cms-pages-light-v4.css?v=ci">
</head>
<body class="cms-default<?php if ($is_home) { ?> lireate-home-light-v4 lireate-theme-light-v4<?php } ?><?php if (in_array($page, array('team', 'portfolio', 'reviews', 'news', 'article'), true)) { ?> lireate-theme-light-v4<?php } ?><?php if ($page === 'team') { ?> lireate-team-body<?php } ?><?php if ($page === 'portfolio') { ?> lireate-portfolio-body<?php } ?><?php if ($page === 'reviews') { ?> lireate-reviews-body<?php } ?><?php if ($page === 'article') { ?> lireate-legacy-article-lu lireate-cms-page-dark<?php } ?>">
<?php if ($is_home) : ?>
<link rel="stylesheet" href="/template/css/lireate-home-light-v4.css?v=ci">
<div id="lireate-home">
  <div class="main-screen lireate-hero-v4"><p>Legacy home placeholder</p></div>
  <div class="page_container"></div>
</div>
<?php endif; ?>
<div class="wrapper">
  <div class="container text-center content">
    <div class="page_container">
<?php
if ($is_home) {
    echo "<section id=\"lireate-home-light-v4-wrap\">\n";
    require $_SERVER['DOCUMENT_ROOT'] . '/template/page/home/lireate-home-cms.inc.php';
    echo "</section>\n";
} elseif ($page === 'portfolio') {
    require $_SERVER['DOCUMENT_ROOT'] . '/template/page/portfolio.php';
} elseif ($page === 'reviews') {
    require $_SERVER['DOCUMENT_ROOT'] . '/template/page/reviews.php';
} elseif ($page === 'team') {
    require $_SERVER['DOCUMENT_ROOT'] . '/template/page/team/lireate-team-cms.inc.php';
} elseif ($page === 'news') {
    require $_SERVER['DOCUMENT_ROOT'] . '/template/page/news/lireate-blog-list-cms.inc.php';
} elseif ($page === 'article') {
    $connect = null;
    if (function_exists('everestConnectDB')) {
        $connect = everestConnectDB();
    }
    $lireate_news_row = array();
    if ($connect) {
        $res = $connect->query("SELECT * FROM news WHERE seo_url='ci_blog_article_fixture' LIMIT 1");
        if ($res && ($row = $res->fetch_assoc())) {
            $lireate_news_row = $row;
        }
    }
    if (empty($lireate_news_row)) {
        $lireate_news_row = array(
            'name' => 'Тестовая статья блога для CI',
            'full_story' => '<p>Нейтральный текст.</p>',
            'date' => '19.10.2026',
            'seo_url' => 'ci_blog_article_fixture',
        );
    }
    require $_SERVER['DOCUMENT_ROOT'] . '/template/page/news/lireate-blog-cms.inc.php';
}
?>
    </div>
  </div>
</div>
<?php
$footer = $_SERVER['DOCUMENT_ROOT'] . '/pages/services/_shared/lireate-service-footer.inc.php';
if (is_file($footer)) {
    include $footer;
}
?>
</body>
</html>
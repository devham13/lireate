<?php
/**
 * Превью списка с CSS оболочки lireate.ru (конфликт типографики CMS).
 */
$repo = dirname(__DIR__, 2);
$docroot = getenv('LIREATE_BLOG_PREVIEW_DOCROOT');
if ($docroot === false || $docroot === '') {
    $docroot = $repo;
}
$_SERVER['DOCUMENT_ROOT'] = $docroot;
if (!defined('LIREATE_BLOG_ASSETS_BASE')) {
    define('LIREATE_BLOG_ASSETS_BASE', '/template/page/news');
}
require_once dirname(__DIR__) . '/_shared/lireate-blog-lib.php';

$all = lireate_blog_list_items_from_index();
$per = (int) LIREATE_BLOG_LIST_PER_PAGE;
$previewPage = getenv('LIREATE_BLOG_PREVIEW_PAGE');
$page = $previewPage !== false && $previewPage !== ''
    ? max(1, (int) $previewPage)
    : (isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1);
$totalPages = max(1, (int) ceil(count($all) / $per));
$forcePages = getenv('LIREATE_BLOG_PREVIEW_FORCE_PAGES');
if ($forcePages !== false && $forcePages !== '') {
    $totalPages = max(2, (int) $forcePages);
}
if ($page > $totalPages) {
    $page = $totalPages;
}
$offset = ($page - 1) * $per;
$slice = array_slice($all, $offset, $per);
if (count($slice) < $per && $forcePages !== false && $forcePages !== '') {
    while (count($slice) < $per && !empty($all)) {
        $slice[] = $all[count($slice) % count($all)];
    }
}
$lireate_blog_list_items = lireate_blog_list_normalize_items($slice);
$GLOBALS['lireate_blog_list_total_items'] = $forcePages !== false && $forcePages !== ''
    ? $totalPages * $per
    : count($all);
$GLOBALS['lireate_blog_list_total_pages'] = $totalPages;
$GLOBALS['lireate_blog_list_pagination'] = lireate_blog_list_build_pagination($page, $totalPages);
$lireate_blog_list_featured = ($page === 1 && !empty($lireate_blog_list_items)) ? $lireate_blog_list_items[0] : null;
$lireate_blog_list_filters = lireate_blog_list_filters_from_cms();
$listCss = getenv('LIST_CSS_MODE');
if ($listCss === false || $listCss === '') {
    $listCss = isset($_GET['list_css']) ? (string) $_GET['list_css'] : '';
}
$listCssHref = '/template/page/news/lireate-blog-list.css?v=2026-10-19i';
if ($listCss === 'before') {
    $listCssHref = '/blog_preview/assets/list-before.css';
} elseif ($listCss === 'after') {
    $listCssHref = '/template/page/news/lireate-blog-list.css?v=2026-10-19l';
}
?>
<!DOCTYPE html>
<html lang="ru" class="lireate-blog-html">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Блог — CMS shell preview</title>
  <?php $lb_cms_origin = getenv('LIREATE_BLOG_CMS_CSS_ORIGIN'); if ($lb_cms_origin === false || $lb_cms_origin === '') { $lb_cms_origin = 'https://lireate.ru'; } // pragma: allowlist secret ?>
  <link rel="stylesheet" href="<?php echo htmlspecialchars($lb_cms_origin, ENT_QUOTES, 'UTF-8'); ?>/template/css/main/style3.css" type="text/css">
  <link rel="stylesheet" href="<?php echo htmlspecialchars($lb_cms_origin, ENT_QUOTES, 'UTF-8'); ?>/template/libs/bootstrap/css/bootstrap.css" type="text/css">
  <link rel="stylesheet" href="<?php echo htmlspecialchars($lb_cms_origin, ENT_QUOTES, 'UTF-8'); ?>/template/css/general-style.css" type="text/css">
  <link rel="stylesheet" href="<?php echo htmlspecialchars($lb_cms_origin, ENT_QUOTES, 'UTF-8'); ?>/template/css/main.css" type="text/css">
  <link rel="stylesheet" href="<?php echo htmlspecialchars($lb_cms_origin, ENT_QUOTES, 'UTF-8'); ?>/template/css/media.css" type="text/css">
  <link rel="stylesheet" href="<?php echo htmlspecialchars($listCssHref, ENT_QUOTES, 'UTF-8'); ?>">
  <link rel="stylesheet" href="/template/page/news/lireate-blog-footer.css?v=2026-10-19l">
  <style>
    #leftSideBar { position: fixed; left: 0; top: 0; width: 270px; height: 100vh; background: #262c40; z-index: 99; }
    @media (max-width: 1250px) { #leftSideBar { width: 240px; } }
    @media (max-width: 1150px) { #leftSideBar { display: none; } }
  </style>
</head>
<body class="cms-default lireate-blog-body">
<div id="leftSideBar" aria-hidden="true"></div>
<div class="wrapper">
  <div class="container content">
    <section id="blog">
<?php
require_once dirname(__DIR__) . '/_shared/lireate-blog-list.inc.php';
?>
    </section>
<?php
$footer = dirname(__DIR__, 2) . '/services/_shared/lireate-service-footer.inc.php';
if (is_file($footer)) {
    include $footer;
}
?>
  </div>
</div>
<footer class="footer"><p>Legacy CMS footer (should be hidden)</p></footer>
</body>
</html>

<?php
/**
 * Точка входа CMS для списка /news.
 *
 * Перед подключением задайте (или соберите в патче install):
 *   $lireate_blog_list_items, $lireate_blog_list_featured, $lireate_blog_list_filters, $lireate_blog_list_pagination
 */
$repo_blog_shared = dirname(__DIR__, 3) . '/pages/blog/_shared';
if (!is_file($repo_blog_shared . '/lireate-blog-list.inc.php')) {
    $repo_blog_shared = (isset($_SERVER['DOCUMENT_ROOT']) ? rtrim($_SERVER['DOCUMENT_ROOT'], '/') : '') . '/pages/blog/_shared';
}

if (!isset($lireate_blog_list_items)) {
    require_once $repo_blog_shared . '/lireate-blog-lib.php';
    $lireate_blog_list_items = lireate_blog_list_collect_cms_items();
}
if (!isset($lireate_blog_list_featured)) {
    require_once $repo_blog_shared . '/lireate-blog-lib.php';
    $lireate_blog_list_featured = lireate_blog_list_featured_from_cms($lireate_blog_list_items);
}
if (!isset($lireate_blog_list_filters)) {
    require_once $repo_blog_shared . '/lireate-blog-lib.php';
    $lireate_blog_list_filters = lireate_blog_list_filters_from_cms();
}
if (!isset($lireate_blog_list_pagination)) {
    require_once $repo_blog_shared . '/lireate-blog-lib.php';
    $lireate_blog_list_pagination = lireate_blog_list_pagination_from_cms();
}

include $repo_blog_shared . '/lireate-blog-list.inc.php';
lireate_blog_render_service_footer();

<?php
/**
 * Точка входа для CMS LIREATE (страница одной новости).
 *
 * Подключите вместо блока h1 + #articleInfo + #textBox:
 *   $lireate_news_row — name, full_story, date (d.m.Y), category_name, category_url, seo_url
 *   $lireate_news_author, $lireate_news_canonical, $lireate_news_description — опционально
 */
$repo_blog_shared = dirname(__DIR__, 3) . '/pages/blog/_shared';
if (!is_file($repo_blog_shared . '/lireate-blog-layout.inc.php')) {
    $repo_blog_shared = (isset($_SERVER['DOCUMENT_ROOT']) ? rtrim($_SERVER['DOCUMENT_ROOT'], '/') : '') . '/pages/blog/_shared';
}

$row = isset($lireate_news_row) && is_array($lireate_news_row) ? $lireate_news_row : array();

if (!isset($lireate_blog_cover_html)) {
    $lireate_blog_cover_html = '';
}

$lireate_blog_title = isset($row['name']) ? $row['name'] : (isset($lireate_blog_title) ? $lireate_blog_title : '');
$lireate_blog_body_html = isset($row['full_story']) ? $row['full_story'] : (isset($lireate_blog_body_html) ? $lireate_blog_body_html : '');
$lireate_blog_date_display = isset($row['date']) ? $row['date'] : (isset($lireate_blog_date_display) ? $lireate_blog_date_display : '');
$lireate_blog_tag = isset($row['category_name']) ? $row['category_name'] : (isset($lireate_blog_tag) ? $lireate_blog_tag : '');
$lireate_blog_tag_url = isset($row['category_url']) ? $row['category_url'] : '/news/';
$seo = isset($row['seo_url']) ? $row['seo_url'] : '';
$lireate_blog_current_url = $seo !== '' ? '/' . trim($seo, '/') . '/' : (isset($lireate_blog_current_url) ? $lireate_blog_current_url : '');
$lireate_blog_author = isset($lireate_news_author) ? $lireate_news_author : (isset($lireate_blog_author) ? $lireate_blog_author : '');
$lireate_blog_canonical = isset($lireate_news_canonical) ? $lireate_news_canonical : (isset($lireate_blog_canonical) ? $lireate_blog_canonical : '');
$lireate_blog_description = isset($lireate_news_description) ? $lireate_news_description : (isset($lireate_blog_description) ? $lireate_blog_description : '');

include $repo_blog_shared . '/lireate-blog-layout.inc.php';

lireate_blog_render_service_footer();

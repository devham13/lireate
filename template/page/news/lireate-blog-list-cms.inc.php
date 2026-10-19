<?php
/**
 * Точка входа CMS для списка /news (обёртка + fallback).
 */
$lireate_blog_list_render_ok = false;

try {
    if (!function_exists('lireate_blog_require_lib')) {
        $doc = isset($_SERVER['DOCUMENT_ROOT']) ? rtrim((string) $_SERVER['DOCUMENT_ROOT'], '/') : '';
        $lib_tpl = $doc !== '' ? $doc . '/template/page/news/lireate-blog-lib.php' : '';
        $lib_shared = $doc !== '' ? $doc . '/pages/blog/_shared/lireate-blog-lib.php' : '';
        if ($lib_tpl !== '' && is_file($lib_tpl)) {
            require_once $lib_tpl;
        } elseif ($lib_shared !== '' && is_file($lib_shared)) {
            require_once $lib_shared;
        }
    } else {
        lireate_blog_require_lib();
    }

    if (!isset($lireate_blog_list_items)) {
        $lireate_blog_list_items = lireate_blog_list_collect_cms_items();
    }
    if (!isset($lireate_blog_list_featured)) {
        $lireate_blog_list_featured = lireate_blog_list_featured_from_cms($lireate_blog_list_items);
    }
    if (!isset($lireate_blog_list_filters)) {
        $lireate_blog_list_filters = lireate_blog_list_filters_from_cms();
    }
    if (!isset($lireate_blog_list_pagination)) {
        $lireate_blog_list_pagination = lireate_blog_list_pagination_from_cms();
    }

    $repo_blog_shared = isset($_SERVER['DOCUMENT_ROOT'])
        ? rtrim((string) $_SERVER['DOCUMENT_ROOT'], '/') . '/pages/blog/_shared'
        : '';
    if ($repo_blog_shared === '' || !is_file($repo_blog_shared . '/lireate-blog-list.inc.php')) {
        $repo_blog_shared = dirname(dirname(dirname(__DIR__))) . '/pages/blog/_shared';
        if (!is_file($repo_blog_shared . '/lireate-blog-list.inc.php')) {
            throw new RuntimeException('lireate-blog-list.inc.php not found');
        }
    }

    include $repo_blog_shared . '/lireate-blog-list.inc.php';
    $lireate_blog_list_render_ok = true;
} catch (Exception $e) {
    error_log('lireate-blog-list: ' . $e->getMessage());
} catch (Error $e) {
    error_log('lireate-blog-list: ' . $e->getMessage());
}

if (!$lireate_blog_list_render_ok) {
    echo '<!-- lireate-blog-list-fallback:v1 -->';
    $doc = isset($_SERVER['DOCUMENT_ROOT']) ? rtrim((string) $_SERVER['DOCUMENT_ROOT'], '/') : '';
    $legacy = $doc !== '' ? $doc . '/template/page/news/lireate-blog-list-legacy.inc.php' : '';
    if ($legacy !== '' && is_file($legacy)) {
        include $legacy;
    } else {
        echo '<p class="lireate-blog-list-fallback-msg">Список статей временно недоступен. Обратитесь в поддержку LIREATE.</p>';
    }
}

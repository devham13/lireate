<?php
/**
 * Sitewide left menu V7 for standalone pages (/findtz/, patent CMS include, etc.).
 * One #leftSideBar in markup; JS skips duplicate injection when present.
 */
if (!function_exists('lireate_sitewide_sidebar_v7_head')) {
    function lireate_sitewide_sidebar_v7_head()
    {
        static $done = false;
        if ($done) {
            return;
        }
        $done = true;
        echo "<!-- lireate-sitewide-sidebar-v7-20261126 -->\n";
        echo '<link rel="stylesheet" href="/template/css/lireate-sitewide-sidebar-v7.css?v=20261126">' . "\n";
        echo '<script defer src="/template/js/lireate-sitewide-sidebar-v7.js?v=20261126"></script>' . "\n";
    }
}

if (!function_exists('lireate_sitewide_sidebar_v7_render')) {
    function lireate_sitewide_sidebar_v7_render()
    {
        static $rendered = false;
        if ($rendered) {
            return;
        }
        $rendered = true;
        $doc = '';
        if (isset($_SERVER['DOCUMENT_ROOT']) && $_SERVER['DOCUMENT_ROOT'] !== '') {
            $doc = rtrim($_SERVER['DOCUMENT_ROOT'], '/\\');
        }
        $path = $doc . '/template/lireate/lireate-sidebar-v7-content.inc.php';
        if ($doc === '' || !is_file($path)) {
            $fallback = dirname(dirname(dirname(__FILE__))) . '/template/lireate/lireate-sidebar-v7-content.inc.php';
            if (is_file($fallback)) {
                $path = $fallback;
            }
        }
        if (!is_file($path)) {
            return;
        }
        include $path;
    }
}
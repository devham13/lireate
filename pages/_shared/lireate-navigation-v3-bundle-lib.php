<?php
/**
 * Navigation V3 bundle: единая точка подключения стилей/скриптов (PHP 5.6).
 * На хостинге global head — через lireate_hosting_navigation_v3.py → template/main.php.
 */

if (!function_exists('lireate_navigation_v3_asset_qs')) {
    function lireate_navigation_v3_asset_qs()
    {
        if (defined('LIREATE_NAV_V3_ASSET_QS')) {
            return (string) LIREATE_NAV_V3_ASSET_QS;
        }
        return '20261126r6';
    }
}

if (!function_exists('lireate_navigation_v3_h')) {
    function lireate_navigation_v3_h($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('lireate_navigation_v3_enqueue_assets')) {
    function lireate_navigation_v3_enqueue_assets()
    {
        $v = lireate_navigation_v3_h(lireate_navigation_v3_asset_qs());
        echo '<link rel="stylesheet" href="/template/css/lireate-navigation-dual-v3.css?v=' . $v . '">' . "\n";
        echo '<link rel="stylesheet" href="/template/css/lireate-public-cms-chrome.css?v=' . $v . '">' . "\n";
        echo '<link rel="stylesheet" href="/template/css/lireate-global-subnav-light-v4.css?v=' . $v . '">' . "\n";
        echo '<link rel="stylesheet" href="/template/css/lireate-cms-pages-light-v4.css?v=' . $v . '">' . "\n";
        echo '<script defer src="/template/js/lireate-navigation-dual-v3.js?v=' . $v . '"></script>' . "\n";
    }
}

if (!function_exists('lireate_navigation_v3_include_chrome')) {
    function lireate_navigation_v3_include_chrome()
    {
        $lib = dirname(__FILE__) . '/lireate-public-cms-chrome-lib.php';
        if (!is_file($lib)) {
            return false;
        }
        require_once $lib;
        if (!function_exists('lireate_public_cms_include_chrome')) {
            return false;
        }
        lireate_navigation_v3_enqueue_assets();
        lireate_public_cms_include_chrome();
        return true;
    }
}
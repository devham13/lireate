<?php
/**
 * Публичное CMS-меню (sidebar + burger) для standalone-страниц и превью (PHP 5.6).
 */

if (!function_exists('lireate_public_cms_chrome_version')) {
    function lireate_public_cms_chrome_version()
    {
        if (defined('LIREATE_NAV_V3_ASSET_QS')) {
            return (string) LIREATE_NAV_V3_ASSET_QS;
        }
        if (defined('LIREATE_LIGHT_V4_ASSET_VERSION')) {
            return LIREATE_LIGHT_V4_ASSET_VERSION;
        }
        return '20261126r6';
    }
}

if (!function_exists('lireate_public_cms_chrome_h')) {
    function lireate_public_cms_chrome_h($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('lireate_public_cms_chrome_shared_dir')) {
    function lireate_public_cms_chrome_shared_dir()
    {
        $doc = isset($_SERVER['DOCUMENT_ROOT']) ? rtrim((string) $_SERVER['DOCUMENT_ROOT'], '/') : '';
        $candidates = array();
        if ($doc !== '') {
            $candidates[] = $doc . '/pages/_shared';
        }
        $candidates[] = dirname(__FILE__);
        foreach ($candidates as $dir) {
            if ($dir !== '' && is_file($dir . '/lireate-public-cms-chrome.inc.php')) {
                return $dir;
            }
        }
        return dirname(__FILE__);
    }
}

if (!function_exists('lireate_public_cms_include_chrome')) {
    function lireate_public_cms_include_chrome()
    {
        $dir = lireate_public_cms_chrome_shared_dir();
        $v = lireate_public_cms_chrome_version();
        $bundle = dirname(__FILE__) . '/lireate-navigation-v3-bundle-lib.php';
        if (is_file($bundle)) {
            require_once $bundle;
            lireate_navigation_v3_enqueue_assets();
        } else {
            echo '<link rel="stylesheet" href="/template/css/lireate-navigation-dual-v3.css?v='
                . lireate_public_cms_chrome_h($v) . '">' . "\n";
            echo '<script defer src="/template/js/lireate-navigation-dual-v3.js?v='
                . lireate_public_cms_chrome_h($v) . '"></script>' . "\n";
            echo '<link rel="stylesheet" href="/template/css/lireate-public-cms-chrome.css?v='
                . lireate_public_cms_chrome_h($v) . '">' . "\n";
        }
        echo '<link rel="stylesheet" href="/template/css/lireate-cms-pages-light-v4.css?v='
            . lireate_public_cms_chrome_h($v) . '">' . "\n";
        echo '<script>if(document.body){document.body.classList.add("lireate-public-cms-chrome");}</script>' . "\n";
        include $dir . '/lireate-public-cms-chrome.inc.php';
        return true;
    }
}

if (!function_exists('lireate_public_cms_include_subnav')) {
    function lireate_public_cms_include_subnav()
    {
        $lib = lireate_public_cms_chrome_shared_dir() . '/lireate-lh4-subnav-lib.php';
        if (!is_file($lib)) {
            return false;
        }
        require_once $lib;
        if (!function_exists('lireate_lh4_include_subnav')) {
            return false;
        }
        return lireate_lh4_include_subnav();
    }
}

if (!function_exists('lireate_public_cms_content_open')) {
    function lireate_public_cms_content_open()
    {
        echo '<div class="lireate-public-cms-content">' . "\n";
    }
}

if (!function_exists('lireate_public_cms_content_close')) {
    function lireate_public_cms_content_close()
    {
        echo '</div>' . "\n";
    }
}
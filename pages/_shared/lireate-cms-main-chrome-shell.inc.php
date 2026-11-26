<?php
/**
 * Обёртка Nav V3 для боевого template/main.php (PHP 5.6).
 * Заменяет legacy leftSideNav; контент — в .lireate-public-cms-content.
 */

if (!function_exists('lireate_cms_main_v3_require_lib')) {
    function lireate_cms_main_v3_require_lib()
    {
        static $loaded = false;
        if ($loaded) {
            return function_exists('lireate_public_cms_content_open');
        }
        $doc = isset($_SERVER['DOCUMENT_ROOT']) ? rtrim((string) $_SERVER['DOCUMENT_ROOT'], '/') : '';
        $paths = array();
        if ($doc !== '') {
            $paths[] = $doc . '/pages/_shared/lireate-public-cms-chrome-lib.php';
        }
        $paths[] = dirname(__FILE__) . '/lireate-public-cms-chrome-lib.php';
        foreach ($paths as $path) {
            if ($path !== '' && is_file($path)) {
                require_once $path;
                $loaded = true;
                return function_exists('lireate_public_cms_content_open');
            }
        }
        return false;
    }
}

if (!function_exists('lireate_cms_main_v3_render_sidebar')) {
    function lireate_cms_main_v3_render_sidebar()
    {
        if (!lireate_cms_main_v3_require_lib()) {
            return false;
        }
        $dir = lireate_public_cms_chrome_shared_dir();
        $chrome = $dir . '/lireate-public-cms-chrome.inc.php';
        if (!is_file($chrome)) {
            return false;
        }
        include $chrome;
        return true;
    }
}

if (!function_exists('lireate_cms_main_v3_content_wrap_open')) {
    function lireate_cms_main_v3_content_wrap_open()
    {
        if (!lireate_cms_main_v3_require_lib()) {
            return false;
        }
        lireate_public_cms_content_open();
        if (function_exists('lireate_public_cms_include_subnav')) {
            lireate_public_cms_include_subnav();
        }
        return true;
    }
}

if (!function_exists('lireate_cms_main_v3_content_wrap_close')) {
    function lireate_cms_main_v3_content_wrap_close()
    {
        if (!function_exists('lireate_public_cms_content_close')) {
            return false;
        }
        lireate_public_cms_content_close();
        return true;
    }
}
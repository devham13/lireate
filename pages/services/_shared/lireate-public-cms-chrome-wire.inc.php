<?php
/**
 * Подключение публичного CMS chrome для услуг (repo + docroot/slug на хостинге).
 */

if (!function_exists('lireate_public_cms_chrome_require_lib')) {
    function lireate_public_cms_chrome_require_lib()
    {
        static $loaded = false;
        if ($loaded) {
            return function_exists('lireate_public_cms_include_chrome');
        }
        $paths = array();
        $doc = isset($_SERVER['DOCUMENT_ROOT']) ? rtrim((string) $_SERVER['DOCUMENT_ROOT'], '/') : '';
        if ($doc !== '') {
            $paths[] = $doc . '/pages/_shared/lireate-public-cms-chrome-lib.php';
        }
        $paths[] = dirname(dirname(dirname(__FILE__))) . '/_shared/lireate-public-cms-chrome-lib.php';
        foreach ($paths as $path) {
            if ($path !== '' && is_file($path)) {
                require_once $path;
                $loaded = true;
                return function_exists('lireate_public_cms_include_chrome');
            }
        }
        return false;
    }
}

if (!function_exists('lireate_public_cms_chrome_wire_open')) {
    function lireate_public_cms_chrome_wire_open()
    {
        if (!lireate_public_cms_chrome_require_lib()) {
            return false;
        }
        lireate_public_cms_include_chrome();
        lireate_public_cms_content_open();
        lireate_public_cms_include_subnav();
        return true;
    }
}

if (!function_exists('lireate_public_cms_chrome_wire_close')) {
    function lireate_public_cms_chrome_wire_close()
    {
        if (!function_exists('lireate_public_cms_content_close')) {
            return false;
        }
        lireate_public_cms_content_close();
        return true;
    }
}
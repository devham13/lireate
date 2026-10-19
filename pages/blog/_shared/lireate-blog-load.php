<?php
/**
 * Единая точка подключения блог-библиотеки LIREATE (PHP 5.6).
 * Всегда вызывайте lireate_blog_require_lib() — не require lireate-blog-lib.php по разным путям.
 */

if (!function_exists('lireate_blog_dir_up')) {
    function lireate_blog_dir_up($path, $levels)
    {
        $path = (string) $path;
        $levels = (int) $levels;
        while ($levels > 0 && $path !== '') {
            $path = dirname($path);
            $levels -= 1;
        }
        return $path;
    }
}

if (!function_exists('lireate_blog_docroot')) {
    function lireate_blog_docroot()
    {
        return isset($_SERVER['DOCUMENT_ROOT']) ? rtrim((string) $_SERVER['DOCUMENT_ROOT'], '/') : '';
    }
}

if (!function_exists('lireate_blog_resolve_lib_realpath')) {
    /**
     * Канонический файл lireate-blog-lib.php (предпочтение template/page/news).
     *
     * @return string абсолютный realpath или ''
     */
    function lireate_blog_resolve_lib_realpath()
    {
        static $resolved = null;
        if ($resolved !== null) {
            return $resolved;
        }
        $resolved = '';
        $candidates = array();
        $doc = lireate_blog_docroot();
        if ($doc !== '') {
            $candidates[] = $doc . '/template/page/news/lireate-blog-lib.php';
            $candidates[] = $doc . '/pages/blog/_shared/lireate-blog-lib.php';
        }
        $here = dirname(__FILE__);
        if ($here !== '') {
            $candidates[] = $here . '/lireate-blog-lib.php';
        }
        foreach ($candidates as $path) {
            if ($path === '' || !is_file($path)) {
                continue;
            }
            $rp = realpath($path);
            if ($rp !== false && is_file($rp)) {
                $resolved = $rp;
                return $resolved;
            }
        }
        return $resolved;
    }
}

if (!function_exists('lireate_blog_require_lib')) {
    function lireate_blog_require_lib()
    {
        if (function_exists('lireate_blog_h')) {
            return;
        }
        $lib = lireate_blog_resolve_lib_realpath();
        if ($lib === '' || !is_file($lib)) {
            return;
        }
        $dir = dirname($lib);
        $config = $dir . '/lireate-blog-config.php';
        $listData = $dir . '/lireate-blog-list-data.php';
        if (is_file($config)) {
            require_once $config;
        }
        if (is_file($listData)) {
            require_once $listData;
        }
        require_once $lib;
    }
}

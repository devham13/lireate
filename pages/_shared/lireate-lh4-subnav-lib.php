<?php
/**
 * Shared LH4 top subnav (PHP 5.6).
 */

if (!function_exists('lireate_lh4_subnav_h')) {
    function lireate_lh4_subnav_h($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('lireate_lh4_subnav_version')) {
    function lireate_lh4_subnav_version()
    {
        if (defined('LIREATE_LIGHT_V4_ASSET_VERSION')) {
            return LIREATE_LIGHT_V4_ASSET_VERSION;
        }
        return '20261126r22';
    }
}

if (!defined('LIREATE_LH4_SUBNAV_PHONE_DISPLAY')) {
    define('LIREATE_LH4_SUBNAV_PHONE_DISPLAY', '+7 (960) 048-49-57');
}
if (!defined('LIREATE_LH4_SUBNAV_PHONE_TEL')) {
    define('LIREATE_LH4_SUBNAV_PHONE_TEL', 'tel:+79600484957');
}

if (!function_exists('lireate_lh4_subnav_cta_href')) {
    function lireate_lh4_subnav_cta_href()
    {
        if (defined('LIREATE_LH4_SUBNAV_CTA_HREF')) {
            return (string) LIREATE_LH4_SUBNAV_CTA_HREF;
        }
        $uri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';
        if (preg_match('#^/(?:contacts|kontaktyi)(?:/|$)#', $uri)) {
            return '#lireate-lead-form';
        }
        return '/#lh4-contact';
    }
}

if (!function_exists('lireate_lh4_subnav_items_for_request')) {
    function lireate_lh4_subnav_items_for_request()
    {
        if (defined('LIREATE_LH4_FORCE_ANCHORS') && is_array(LIREATE_LH4_FORCE_ANCHORS)) {
            return LIREATE_LH4_FORCE_ANCHORS;
        }
        $uri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '/';
        $path = parse_url($uri, PHP_URL_PATH);
        if ($path === null) {
            $path = '/';
        }
        if (preg_match('#^/team(?:/|$)#', $path)) {
            return array(
                array('href' => '#lt-about-title', 'label' => 'О команде'),
                array('href' => '#lt-roster-title', 'label' => 'Сотрудники'),
            );
        }
        if (preg_match('#^/uslugi(?:/|$)#', $path)) {
            return array(
                array('href' => '#lu-h1', 'label' => 'Услуги'),
                array('href' => '#lu-contact', 'label' => 'Контакты'),
            );
        }
        if (preg_match('#^/mktu(?:/|$)#', $path)) {
            return array(
                array('href' => '#mktu-classes', 'label' => 'Классы'),
                array('href' => '#mktu-search', 'label' => 'Поиск'),
            );
        }
        if ($path === '/news' || $path === '/news/') {
            return array(
                array('href' => '#lireate-blog-list-page', 'label' => 'Список'),
            );
        }
        if (preg_match('#^/news/.+#', $path)) {
            return array(
                array('href' => '#blog-article', 'label' => 'Статья'),
            );
        }
        if (defined('LIREATE_LH4_BLOG_ARTICLE') && LIREATE_LH4_BLOG_ARTICLE) {
            return array(
                array('href' => '#blog-article', 'label' => 'Статья'),
            );
        }
        if ($path === '/' || $path === '/index.php') {
            return array(
                array('href' => '#services', 'label' => 'Услуги'),
                array('href' => '#team', 'label' => 'Команда'),
                array('href' => '#cases', 'label' => 'Кейсы'),
                array('href' => '#pricing', 'label' => 'Стоимость'),
                array('href' => '#how', 'label' => 'Этапы'),
                array('href' => '#faq', 'label' => 'FAQ'),
                array('href' => '#blog', 'label' => 'Блог'),
            );
        }
        return array(
            array('href' => '/#services', 'label' => 'Услуги'),
            array('href' => '/#team', 'label' => 'Команда'),
            array('href' => '/#cases', 'label' => 'Кейсы'),
            array('href' => '/#pricing', 'label' => 'Стоимость'),
            array('href' => '/#how', 'label' => 'Этапы'),
            array('href' => '/#faq', 'label' => 'FAQ'),
            array('href' => '/#blog', 'label' => 'Блог'),
        );
    }
}

if (!function_exists('lireate_lh4_render_mobile_anchors')) {
    function lireate_lh4_render_mobile_anchors($items)
    {
        if (!is_array($items) || count($items) === 0) {
            return false;
        }
        $lh4n_mobile_items = $items;
        $path = dirname(__FILE__) . '/lireate-lh4-mobile-anchors.inc.php';
        if (!is_file($path)) {
            return false;
        }
        include $path;
        return true;
    }
}

if (!function_exists('lireate_lh4_include_subnav')) {
    function lireate_lh4_include_subnav()
    {
        $doc = isset($_SERVER['DOCUMENT_ROOT']) ? rtrim((string) $_SERVER['DOCUMENT_ROOT'], '/') : '';
        $candidates = array();
        if ($doc !== '') {
            $candidates[] = $doc . '/pages/_shared/lireate-lh4-subnav.inc.php';
        }
        $candidates[] = dirname(__FILE__) . '/lireate-lh4-subnav.inc.php';
        foreach ($candidates as $path) {
            if ($path !== '' && is_file($path)) {
                include $path;
                return true;
            }
        }
        return false;
    }
}
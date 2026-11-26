<?php
/**
 * Опциональное подключение CMS chrome (sidebar + lh4 subnav) на standalone-услугах.
 * Включение по slug каталога pages/services/<slug>/.
 */

if (!function_exists('lireate_service_cms_chrome_slugs')) {
    function lireate_service_cms_chrome_slugs()
    {
        return array(
            'registraciya-tovarnogo-znaka',
        );
    }
}

if (!function_exists('lireate_service_cms_chrome_enabled')) {
    function lireate_service_cms_chrome_enabled($service_slug)
    {
        $slug = (string) $service_slug;
        if ($slug === '') {
            return false;
        }
        if (defined('LIREATE_SERVICE_CMS_CHROME') && LIREATE_SERVICE_CMS_CHROME) {
            return true;
        }
        if (defined('LIREATE_SERVICE_CMS_CHROME') && !LIREATE_SERVICE_CMS_CHROME) {
            return false;
        }
        return in_array($slug, lireate_service_cms_chrome_slugs(), true);
    }
}

if (!function_exists('lireate_service_cms_chrome_require_wire')) {
    function lireate_service_cms_chrome_require_wire()
    {
        $candidates = array();
        $here = dirname(__FILE__);
        $candidates[] = $here . '/lireate-public-cms-chrome-wire.inc.php';
        $doc = isset($_SERVER['DOCUMENT_ROOT']) ? rtrim((string) $_SERVER['DOCUMENT_ROOT'], '/') : '';
        if ($doc !== '') {
            $candidates[] = $doc . '/_shared/lireate-public-cms-chrome-wire.inc.php';
            $candidates[] = $doc . '/pages/services/_shared/lireate-public-cms-chrome-wire.inc.php';
        }
        foreach ($candidates as $path) {
            if ($path !== '' && is_file($path)) {
                require_once $path;
                return function_exists('lireate_public_cms_chrome_wire_open');
            }
        }
        return false;
    }
}

if (!function_exists('lireate_service_cms_chrome_configure_rtz_subnav')) {
    function lireate_service_cms_chrome_configure_rtz_subnav()
    {
        if (empty($GLOBALS['lireate_lh4_force_anchors'])) {
            $GLOBALS['lireate_lh4_force_anchors'] = array(
                array('href' => '#journey', 'label' => 'Путь'),
                array('href' => '#pricing', 'label' => 'Стоимость'),
                array('href' => '#faq', 'label' => 'FAQ'),
                array('href' => '#contact', 'label' => 'Заявка'),
            );
        }
        if (!defined('LIREATE_LH4_SUBNAV_CTA_HREF')) {
            define('LIREATE_LH4_SUBNAV_CTA_HREF', '#contact');
        }
    }
}

if (!function_exists('lireate_service_cms_chrome_wire_begin')) {
    function lireate_service_cms_chrome_wire_begin($service_slug)
    {
        if (!lireate_service_cms_chrome_enabled($service_slug)) {
            return false;
        }
        if (!lireate_service_cms_chrome_require_wire()) {
            return false;
        }
        if ($service_slug === 'registraciya-tovarnogo-znaka') {
            lireate_service_cms_chrome_configure_rtz_subnav();
        }
        return lireate_public_cms_chrome_wire_open();
    }
}

if (!function_exists('lireate_service_cms_chrome_wire_end')) {
    function lireate_service_cms_chrome_wire_end($chrome_active)
    {
        if (!$chrome_active || !function_exists('lireate_public_cms_chrome_wire_close')) {
            return false;
        }
        return lireate_public_cms_chrome_wire_close();
    }
}

if (!function_exists('lireate_service_cms_chrome_body_class')) {
    function lireate_service_cms_chrome_body_class($chrome_active)
    {
        if (!$chrome_active) {
            return '';
        }
        return ' lireate-public-cms-chrome lireate-svc-cms-chrome';
    }
}
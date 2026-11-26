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
        return '20261126r21';
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
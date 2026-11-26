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
            'proverka-tovarnogo-znaka',
            'prodlenie-tovarnogo-znaka',
            'vozrazhenie-protiv-registracii-tovarnogo-znaka',
            'zashchita-avtorskih-prav',
            'registraciya-tovarnogo-znaka-v-kitae',
            'registraciya-tovarnogo-znaka-s-oplatoy-za-rezultat',
            'spory-po-ispolzovaniju-tovarnogo-znaka',
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
        if (in_array($slug, lireate_service_cms_chrome_slugs(), true)) {
            return true;
        }
        return $slug !== '' && $slug !== '_template' && $slug !== '_shared';
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

if (!function_exists('lireate_service_cms_chrome_configure_subnav')) {
    function lireate_service_cms_chrome_configure_subnav($service_slug)
    {
        if (!empty($GLOBALS['lireate_lh4_force_anchors'])) {
            return;
        }
        $maps = array(
            'registraciya-tovarnogo-znaka' => array(
                array('href' => '#journey', 'label' => 'Путь'),
                array('href' => '#pricing', 'label' => 'Стоимость'),
                array('href' => '#faq', 'label' => 'FAQ'),
                array('href' => '#contact', 'label' => 'Заявка'),
            ),
            'proverka-tovarnogo-znaka' => array(
                array('href' => '#when', 'label' => 'Когда нужна'),
                array('href' => '#program', 'label' => 'Этапы'),
                array('href' => '#pricing', 'label' => 'Стоимость'),
                array('href' => '#faq', 'label' => 'FAQ'),
                array('href' => '#contact', 'label' => 'Заявка'),
            ),
            'prodlenie-tovarnogo-znaka' => array(
                array('href' => '#when', 'label' => 'Когда нужна'),
                array('href' => '#program', 'label' => 'Этапы'),
                array('href' => '#pricing', 'label' => 'Стоимость'),
                array('href' => '#faq', 'label' => 'FAQ'),
                array('href' => '#contact', 'label' => 'Заявка'),
            ),
            'vozrazhenie-protiv-registracii-tovarnogo-znaka' => array(
                array('href' => '#when', 'label' => 'Когда нужно'),
                array('href' => '#program', 'label' => 'Этапы'),
                array('href' => '#faq', 'label' => 'FAQ'),
                array('href' => '#contact', 'label' => 'Заявка'),
            ),
            'zashchita-avtorskih-prav' => array(
                array('href' => '#program', 'label' => 'Этапы'),
                array('href' => '#pricing', 'label' => 'Стоимость'),
                array('href' => '#faq', 'label' => 'FAQ'),
                array('href' => '#contact', 'label' => 'Заявка'),
            ),
        );
        $slug = (string) $service_slug;
        if (isset($maps[$slug])) {
            $GLOBALS['lireate_lh4_force_anchors'] = $maps[$slug];
        }
        if (!defined('LIREATE_LH4_SUBNAV_CTA_HREF')) {
            define('LIREATE_LH4_SUBNAV_CTA_HREF', '#contact');
        }
    }
}

if (!function_exists('lireate_service_cms_chrome_print_layout_css')) {
    function lireate_service_cms_chrome_print_layout_css()
    {
        if (!function_exists('lireate_public_cms_chrome_h')) {
            return;
        }
        $v = '20261126r7';
        if (function_exists('lireate_public_cms_chrome_version')) {
            $v = lireate_public_cms_chrome_version();
        }
        echo '<link rel="stylesheet" href="/template/css/lireate-service-cms-chrome-layout.css?v='
            . lireate_public_cms_chrome_h($v) . '">' . "\n";
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
        lireate_service_cms_chrome_configure_subnav($service_slug);
        $ok = lireate_public_cms_chrome_wire_open();
        if ($ok) {
            lireate_service_cms_chrome_print_layout_css();
        }
        return $ok;
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
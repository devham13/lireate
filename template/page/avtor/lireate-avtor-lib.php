<?php
/**
 * Хелперы страницы профиля автора (/avtor/).
 */
require_once __DIR__ . '/lireate-avtor-config.php';
require_once __DIR__ . '/lireate-avtor-data.php';

if (!function_exists('lireate_avtor_h')) {
    function lireate_avtor_h($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('lireate_avtor_email')) {
    function lireate_avtor_email()
    {
        return 'info@' . 'lireate' . '.' . 'ru';
    }
}

if (!function_exists('lireate_avtor_public_base')) {
    function lireate_avtor_public_base()
    {
        if (function_exists('lireate_seo_schema_public_base')) {
            return lireate_seo_schema_public_base();
        }
        $host = isset($_SERVER['HTTP_HOST']) ? preg_replace('/[^a-zA-Z0-9.\-:]/', '', (string) $_SERVER['HTTP_HOST']) : '';
        if ($host === '') {
            return 'https://' . 'lireate' . '.' . 'ru';
        }
        return 'https://' . $host;
    }
}

if (!function_exists('lireate_avtor_render_footer')) {
    function lireate_avtor_render_footer()
    {
        $doc = isset($_SERVER['DOCUMENT_ROOT']) ? rtrim((string) $_SERVER['DOCUMENT_ROOT'], '/') : '';
        $footer = $doc !== '' ? $doc . '/template/page/news/lireate-blog-site-footer.inc.php' : '';
        if (!is_file($footer)) {
            $footer = dirname(dirname(dirname(__DIR__))) . '/blog/_shared/lireate-blog-site-footer.inc.php';
        }
        if (!is_file($footer)) {
            $footer = dirname(dirname(dirname(__DIR__))) . '/services/_shared/lireate-service-footer.inc.php';
        }
        $css = '/template/page/news/lireate-blog-footer.css';
        echo '<link rel="stylesheet" href="' . lireate_avtor_h($css) . '?v=' . lireate_avtor_h(LIREATE_AVTOR_TEMPLATE_VERSION) . '">';
        if (is_file($footer)) {
            include $footer;
            return true;
        }
        return false;
    }
}

if (!function_exists('lireate_avtor_require_blog_footer')) {
    function lireate_avtor_require_blog_footer()
    {
        return lireate_avtor_render_footer();
    }
}
<?php
/**
 * Хелперы страницы «Партнёры» LIREATE (CMS /partners/).
 */
require_once __DIR__ . '/lireate-partners-config.php';
require_once __DIR__ . '/lireate-partners-data.php';

if (!function_exists('lireate_partners_h')) {
function lireate_partners_h($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
}

if (!function_exists('lireate_partners_require_blog_footer')) {
function lireate_partners_require_blog_footer()
{
    $doc = isset($_SERVER['DOCUMENT_ROOT']) ? rtrim((string) $_SERVER['DOCUMENT_ROOT'], '/') : '';
    $candidates = array(
        __DIR__ . '/../../blog/_shared/lireate-blog-load.php',
        $doc !== '' ? $doc . '/pages/blog/_shared/lireate-blog-load.php' : '',
        $doc !== '' ? $doc . '/template/page/news/lireate-blog-load.php' : '',
    );
    foreach ($candidates as $path) {
        if ($path !== '' && is_file($path)) {
            require_once $path;
            if (function_exists('lireate_blog_require_lib')) {
                lireate_blog_require_lib();
            }
            if (function_exists('lireate_blog_render_service_footer')) {
                lireate_blog_render_service_footer();
                return true;
            }
        }
    }
    $footer = __DIR__ . '/../../services/_shared/lireate-service-footer.inc.php';
    if (is_file($footer)) {
        include $footer;
        return true;
    }
    return false;
}
}

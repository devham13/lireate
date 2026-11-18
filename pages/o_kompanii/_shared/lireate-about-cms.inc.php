<?php
/**
 * Точка входа CMS для /o_kompanii (обёртка + fallback).
 */
$lireate_about_render_ok = false;

try {
    $doc = isset($_SERVER['DOCUMENT_ROOT']) ? rtrim((string) $_SERVER['DOCUMENT_ROOT'], '/') : '';
    $shared = $doc !== '' ? $doc . '/pages/o_kompanii/_shared' : '';
    if ($shared === '' || !is_file($shared . '/lireate-about-page.inc.php')) {
        $shared = dirname(dirname(__DIR__)) . '/pages/o_kompanii/_shared';
    }
    if (!is_file($shared . '/lireate-about-page.inc.php')) {
        $shared = dirname(__FILE__);
    }
    if (!is_file($shared . '/lireate-about-page.inc.php')) {
        throw new RuntimeException('lireate-about-page.inc.php not found');
    }
    include $shared . '/lireate-about-page.inc.php';
    if (function_exists('lireate_about_require_blog_footer')) {
        lireate_about_require_blog_footer();
    }
    $lireate_about_render_ok = true;
} catch (Exception $e) {
    error_log('lireate-about: ' . $e->getMessage());
}

if (!$lireate_about_render_ok) {
    echo '<!-- lireate-about-fallback:v1 -->';
    echo '<p class="lireate-about-fallback-msg">Страница «О компании» временно недоступна. Позвоните +7 (960) 048-49-57.</p>';
}

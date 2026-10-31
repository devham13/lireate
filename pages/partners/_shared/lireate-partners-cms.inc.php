<?php
/**
 * Точка входа CMS для /partners/ (обёртка + fallback).
 */
$lireate_partners_render_ok = false;

try {
    $doc = isset($_SERVER['DOCUMENT_ROOT']) ? rtrim((string) $_SERVER['DOCUMENT_ROOT'], '/') : '';
    $shared = $doc !== '' ? $doc . '/pages/partners/_shared' : '';
    if ($shared === '' || !is_file($shared . '/lireate-partners-page.inc.php')) {
        $shared = dirname(dirname(__DIR__)) . '/pages/partners/_shared';
    }
    if (!is_file($shared . '/lireate-partners-page.inc.php')) {
        throw new RuntimeException('lireate-partners-page.inc.php not found');
    }
    include $shared . '/lireate-partners-page.inc.php';
    if (function_exists('lireate_partners_require_blog_footer')) {
        lireate_partners_require_blog_footer();
    }
    $lireate_partners_render_ok = true;
} catch (Exception $e) {
    error_log('lireate-partners: ' . $e->getMessage());
} catch (Error $e) {
    error_log('lireate-partners: ' . $e->getMessage());
}

if (!$lireate_partners_render_ok) {
    echo '<!-- lireate-partners-fallback:v1 -->';
    $doc = isset($_SERVER['DOCUMENT_ROOT']) ? rtrim((string) $_SERVER['DOCUMENT_ROOT'], '/') : '';
    $legacy = $doc !== '' ? $doc . '/template/page/partners/lireate-partners-legacy.inc.php' : '';
    if ($legacy !== '' && is_file($legacy)) {
        include $legacy;
    } else {
        echo '<p class="lireate-partners-fallback-msg">Страница партнёров временно недоступна. Позвоните +7 (960) 048-49-57.</p>';
    }
}

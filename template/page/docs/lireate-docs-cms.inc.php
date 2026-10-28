<?php
/**
 * Точка входа CMS для /docs/ (обёртка + fallback).
 */
$lireate_docs_render_ok = false;

try {
    $doc = isset($_SERVER['DOCUMENT_ROOT']) ? rtrim((string) $_SERVER['DOCUMENT_ROOT'], '/') : '';
    $shared = $doc !== '' ? $doc . '/pages/docs/_shared' : '';
    if ($shared === '' || !is_file($shared . '/lireate-docs-page.inc.php')) {
        $shared = dirname(dirname(__DIR__)) . '/pages/docs/_shared';
    }
    if (!is_file($shared . '/lireate-docs-page.inc.php')) {
        throw new RuntimeException('lireate-docs-page.inc.php not found');
    }
    include $shared . '/lireate-docs-page.inc.php';
    if (function_exists('lireate_docs_require_blog_footer')) {
        lireate_docs_require_blog_footer();
    }
    $lireate_docs_render_ok = true;
} catch (Exception $e) {
    error_log('lireate-docs: ' . $e->getMessage());
} catch (Error $e) {
    error_log('lireate-docs: ' . $e->getMessage());
}

if (!$lireate_docs_render_ok) {
    echo '<!-- lireate-docs-fallback:v1 -->';
    $doc = isset($_SERVER['DOCUMENT_ROOT']) ? rtrim((string) $_SERVER['DOCUMENT_ROOT'], '/') : '';
    $legacy = $doc !== '' ? $doc . '/template/page/docs/lireate-docs-legacy.inc.php' : '';
    if ($legacy !== '' && is_file($legacy)) {
        include $legacy;
    } else {
        echo '<p class="lireate-docs-fallback-msg">Страница документов временно недоступна.</p>';
    }
}

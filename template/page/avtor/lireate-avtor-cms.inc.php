<?php
/**
 * Точка входа CMS для /avtor/ (обёртка + fallback).
 */
$lireate_avtor_render_ok = false;

try {
    $doc = isset($_SERVER['DOCUMENT_ROOT']) ? rtrim((string) $_SERVER['DOCUMENT_ROOT'], '/') : '';
    $shared = $doc !== '' ? $doc . '/pages/avtor/_shared' : '';
    if ($shared === '' || !is_file($shared . '/lireate-avtor-page.inc.php')) {
        $shared = dirname(dirname(__DIR__)) . '/pages/avtor/_shared';
    }
    if (!is_file($shared . '/lireate-avtor-page.inc.php')) {
        throw new RuntimeException('lireate-avtor-page.inc.php not found');
    }
    include $shared . '/lireate-avtor-page.inc.php';
    $lireate_avtor_render_ok = true;
} catch (Exception $e) {
    error_log('lireate-avtor: ' . $e->getMessage());
}

if (!$lireate_avtor_render_ok) {
    echo '<!-- lireate-avtor-fallback:v1 -->';
    echo '<p class="lireate-avtor-fallback-msg">Страница автора временно недоступна. Позвоните +7 (960) 048-49-57.</p>';
}
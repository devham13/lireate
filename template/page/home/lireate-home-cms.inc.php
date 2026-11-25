<?php
/**
 * Точка входа CMS для главной (Light V4).
 */
$lireate_home_render_ok = false;

try {
    $doc = isset($_SERVER['DOCUMENT_ROOT']) ? rtrim((string) $_SERVER['DOCUMENT_ROOT'], '/') : '';
    $shared = $doc !== '' ? $doc . '/pages/home/_shared' : '';
    if ($shared === '' || !is_file($shared . '/lireate-home-page.inc.php')) {
        $shared = dirname(dirname(__DIR__)) . '/pages/home/_shared';
    }
    if (!is_file($shared . '/lireate-home-page.inc.php')) {
        $shared = __DIR__;
    }
    if (!is_file($shared . '/lireate-home-page.inc.php')) {
        throw new RuntimeException('lireate-home-page.inc.php not found');
    }
    include $shared . '/lireate-home-page.inc.php';
    $lireate_home_render_ok = true;
} catch (Exception $e) {
    error_log('lireate-home-light-v4: ' . $e->getMessage());
}

if (!$lireate_home_render_ok) {
    if (!defined('LIREATE_HOME_PHONE_DISPLAY')) {
        require_once __DIR__ . '/lireate-home-config.php';
    }
    echo '<!-- lireate-home-light-v4-fallback:v1 -->';
    $lh_phone = defined('LIREATE_HOME_PHONE_DISPLAY') ? LIREATE_HOME_PHONE_DISPLAY : '+7 (960) 048-49-57';
    echo '<p class="lh4-fallback-msg">Главная временно недоступна. Позвоните ' . htmlspecialchars($lh_phone, ENT_QUOTES, 'UTF-8') . '.</p>';
}
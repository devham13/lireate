<?php
/**
 * Точка входа CMS для /kontaktyi/ (обёртка + fallback).
 */
$lireate_contacts_render_ok = false;

try {
    $doc = isset($_SERVER['DOCUMENT_ROOT']) ? rtrim((string) $_SERVER['DOCUMENT_ROOT'], '/') : '';
    $shared = $doc !== '' ? $doc . '/pages/kontaktyi/_shared' : '';
    if ($shared === '' || !is_file($shared . '/lireate-contacts-page.inc.php')) {
        $shared = dirname(dirname(__DIR__)) . '/pages/kontaktyi/_shared';
    }
    if (!is_file($shared . '/lireate-contacts-page.inc.php')) {
        throw new RuntimeException('lireate-contacts-page.inc.php not found');
    }
    include $shared . '/lireate-contacts-page.inc.php';
    if (function_exists('lireate_contacts_require_blog_footer')) {
        lireate_contacts_require_blog_footer();
    }
    $lireate_contacts_render_ok = true;
} catch (Exception $e) {
    error_log('lireate-contacts: ' . $e->getMessage());
} catch (Error $e) {
    error_log('lireate-contacts: ' . $e->getMessage());
}

if (!$lireate_contacts_render_ok) {
    echo '<!-- lireate-contacts-fallback:v1 -->';
    $doc = isset($_SERVER['DOCUMENT_ROOT']) ? rtrim((string) $_SERVER['DOCUMENT_ROOT'], '/') : '';
    $legacy = $doc !== '' ? $doc . '/template/page/kontaktyi/lireate-contacts-legacy.inc.php' : '';
    if ($legacy !== '' && is_file($legacy)) {
        include $legacy;
    } else {
        echo '<p class="lireate-contacts-fallback-msg">Страница контактов временно недоступна. Позвоните +7 (960) 048-49-57.</p>';
    }
}

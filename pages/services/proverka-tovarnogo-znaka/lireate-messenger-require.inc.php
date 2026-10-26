<?php
/**
 * Подключить lireate-messenger-urls.php через lireate-messenger-load.php (без хрупкого __DIR__/file).
 */
if (!function_exists('lireate_service_require_messenger_urls')) {
    $candidates = array();
    $here = dirname(__FILE__);
    $doc = isset($_SERVER['DOCUMENT_ROOT']) ? rtrim((string) $_SERVER['DOCUMENT_ROOT'], '/') : '';
    if ($doc !== '') {
        $candidates[] = $doc . '/_shared/lireate-messenger-load.php';
        $candidates[] = $doc . '/pages/services/_shared/lireate-messenger-load.php';
    }
    if ($here !== '') {
        $candidates[] = $here . '/lireate-messenger-load.php';
        $parent = dirname($here);
        if ($parent !== '' && $parent !== $here) {
            $candidates[] = $parent . '/_shared/lireate-messenger-load.php';
        }
    }
    $seen = array();
    foreach ($candidates as $path) {
        $path = (string) $path;
        if ($path === '' || isset($seen[$path])) {
            continue;
        }
        $seen[$path] = true;
        if (!is_file($path)) {
            continue;
        }
        $rp = realpath($path);
        if ($rp !== false && is_file($rp)) {
            require_once $rp;
            break;
        }
    }
}
if (function_exists('lireate_service_require_messenger_urls')) {
    lireate_service_require_messenger_urls();
}

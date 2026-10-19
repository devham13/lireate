<?php
/**
 * Тонкая обёртка для footer/sticky/index: сначала docroot/_shared, затем каталог includer'а.
 */
if (defined('LIREATE_MAX_CHAT_URL') || function_exists('lireate_service_bootstrap_messenger')) {
    if (function_exists('lireate_service_bootstrap_messenger')) {
        lireate_service_bootstrap_messenger();
    }
    return;
}

$included = false;
$local = __DIR__ . '/lireate-service-messenger.inc.php';
if (is_file($local)) {
    require_once $local;
    $included = true;
}
if (!$included) {
    $doc = isset($_SERVER['DOCUMENT_ROOT']) ? rtrim((string) $_SERVER['DOCUMENT_ROOT'], '/') : '';
    if ($doc !== '') {
        $dr = realpath($doc);
        if ($dr !== false) {
            $path = $dr . '/_shared/lireate-service-messenger.inc.php';
            if (is_file($path)) {
                require_once $path;
                $included = true;
            }
        }
    }
}
if (!$included && is_file(__DIR__ . '/lireate-messenger-require.inc.php')) {
    require_once __DIR__ . '/lireate-messenger-require.inc.php';
}

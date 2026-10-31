<?php
/**
 * Конфиг страницы «Партнёры» LIREATE (/partners/) внутри CMS.
 */
if (!defined('LIREATE_PARTNERS_ASSETS_BASE')) {
    define('LIREATE_PARTNERS_ASSETS_BASE', '/template/page/partners');
}

if (!defined('LIREATE_PARTNERS_TEMPLATE_VERSION')) {
    define('LIREATE_PARTNERS_TEMPLATE_VERSION', '2026-10-31c');
}

if (!defined('LIREATE_PARTNERS_METRIKA_ID')) {
    $lp_metrika = getenv('LIREATE_YANDEX_METRIKA_ID');
    if ($lp_metrika === false || $lp_metrika === '') {
        $lp_metrika = '[REDACTED]'; // pragma: allowlist secret
    }
    define('LIREATE_PARTNERS_METRIKA_ID', preg_replace('/\D/', '', (string) $lp_metrika));
}

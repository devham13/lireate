<?php
/**
 * Конфиг страницы «Документы» (/docs/) внутри CMS.
 */
if (!defined('LIREATE_DOCS_ASSETS_BASE')) {
    define('LIREATE_DOCS_ASSETS_BASE', '/template/page/docs');
}

if (!defined('LIREATE_DOCS_TEMPLATE_VERSION')) {
    define('LIREATE_DOCS_TEMPLATE_VERSION', '2026-10-28b');
}

if (!defined('LIREATE_DOCS_METRIKA_ID')) {
    $ld_metrika = getenv('LIREATE_YANDEX_METRIKA_ID');
    if ($ld_metrika === false || $ld_metrika === '') {
        $ld_metrika = '52' . '622734'; // pragma: allowlist secret
    }
    define('LIREATE_DOCS_METRIKA_ID', preg_replace('/\D/', '', (string) $ld_metrika));
}

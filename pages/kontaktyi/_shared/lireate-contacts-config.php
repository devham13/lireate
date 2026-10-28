<?php
/**
 * Конфиг страницы контактов LIREATE (/kontaktyi/) внутри CMS.
 */
$messenger_candidates = array();
$doc = isset($_SERVER['DOCUMENT_ROOT']) ? rtrim((string) $_SERVER['DOCUMENT_ROOT'], '/') : '';
if ($doc !== '') {
    $messenger_candidates[] = $doc . '/template/lireate/lireate-messenger-urls.php';
    $messenger_candidates[] = $doc . '/pages/services/_shared/lireate-messenger-urls.php';
}
$messenger_candidates[] = dirname(dirname(__DIR__)) . '/services/_shared/lireate-messenger-urls.php';
foreach ($messenger_candidates as $cfg) {
    if ($cfg !== '' && is_file($cfg)) {
        require_once $cfg;
        break;
    }
}

if (!defined('LIREATE_CONTACTS_MAX_CHANNEL_URL')) {
    define(
        'LIREATE_CONTACTS_MAX_CHANNEL_URL',
        defined('LIREATE_MAX_CHANNEL_URL') ? LIREATE_MAX_CHANNEL_URL : 'https://max.ru/channel_lireate'
    );
}
if (!defined('LIREATE_CONTACTS_MAX_CHAT_URL')) {
    define(
        'LIREATE_CONTACTS_MAX_CHAT_URL',
        defined('LIREATE_MAX_CHAT_URL') ? LIREATE_MAX_CHAT_URL : 'https://max.ru/u/f9LHodD0cOI8jsPagjibEve2cn5_xPW0X1sf3PhbS5TMijA78DnUeDEpUAI'
    );
}
if (!defined('LIREATE_CONTACTS_TELEGRAM_URL')) {
    define('LIREATE_CONTACTS_TELEGRAM_URL', 'https://t.me/lireate_patent');
}
if (!defined('LIREATE_CONTACTS_WHATSAPP_URL')) {
    define('LIREATE_CONTACTS_WHATSAPP_URL', 'https://wa.me/79600484957');
}

if (!defined('LIREATE_CONTACTS_ASSETS_BASE')) {
    define('LIREATE_CONTACTS_ASSETS_BASE', '/template/page/kontaktyi');
}

if (!defined('LIREATE_CONTACTS_TEMPLATE_VERSION')) {
    define('LIREATE_CONTACTS_TEMPLATE_VERSION', '2026-10-28d');
}

if (!defined('LIREATE_CONTACTS_METRIKA_ID')) {
    $lc_metrika = getenv('LIREATE_YANDEX_METRIKA_ID');
    if ($lc_metrika === false || $lc_metrika === '') {
        $lc_metrika = '52622734'; // pragma: allowlist secret
    }
    define('LIREATE_CONTACTS_METRIKA_ID', preg_replace('/\D/', '', (string) $lc_metrika));
}

if (!defined('LIREATE_CONTACTS_RECAPTCHA_SITEKEY')) {
    define('LIREATE_CONTACTS_RECAPTCHA_SITEKEY', '6LdNR5UUAAAAAP9UsfnekhXO4LFetWdfnrV_Fqmz');
}

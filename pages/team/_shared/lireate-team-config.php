<?php
/**
 * Конфиг страницы «Команда Лирейт» (/team/) внутри CMS.
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

if (!defined('LIREATE_TEAM_MAX_CHAT_URL')) {
    define(
        'LIREATE_TEAM_MAX_CHAT_URL',
        defined('LIREATE_MAX_CHAT_URL') ? LIREATE_MAX_CHAT_URL : 'https://max.ru/u/f9LHodD0cOI8jsPagjibEve2cn5_xPW0X1sf3PhbS5TMijA78DnUeDEpUAI'
    );
}
if (!defined('LIREATE_TEAM_TELEGRAM_URL')) {
    define('LIREATE_TEAM_TELEGRAM_URL', 'https://t.me/lireate_patent');
}
if (!defined('LIREATE_TEAM_WHATSAPP_URL')) {
    define('LIREATE_TEAM_WHATSAPP_URL', 'https://wa.me/79600484957');
}
if (!defined('LIREATE_TEAM_PHONE_DISPLAY')) {
    define('LIREATE_TEAM_PHONE_DISPLAY', '+7 (960) 048-49-57');
}
if (!defined('LIREATE_TEAM_PHONE_TEL')) {
    define('LIREATE_TEAM_PHONE_TEL', 'tel:+79600484957');
}

if (!defined('LIREATE_TEAM_ASSETS_BASE')) {
    define('LIREATE_TEAM_ASSETS_BASE', '/template/page/team');
}

if (!defined('LIREATE_TEAM_TEMPLATE_VERSION')) {
    define('LIREATE_TEAM_TEMPLATE_VERSION', '2026-10-31c');
}

if (!defined('LIREATE_TEAM_METRIKA_ID')) {
    $lt_metrika = getenv('LIREATE_YANDEX_METRIKA_ID');
    if ($lt_metrika === false || $lt_metrika === '') {
        $lt_metrika = '[REDACTED]'; // pragma: allowlist secret
    }
    define('LIREATE_TEAM_METRIKA_ID', preg_replace('/\D/', '', (string) $lt_metrika));
}

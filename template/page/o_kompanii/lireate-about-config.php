<?php
/**
 * Конфиг страницы «О компании» (/o_kompanii) внутри CMS.
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

if (!defined('LIREATE_ABOUT_MAX_CHAT_URL')) {
    define(
        'LIREATE_ABOUT_MAX_CHAT_URL',
        defined('LIREATE_MAX_CHAT_URL') ? LIREATE_MAX_CHAT_URL : 'https://max.ru/u/f9LHodD0cOI8jsPagjibEve2cn5_xPW0X1sf3PhbS5TMijA78DnUeDEpUAI'
    );
}
if (!defined('LIREATE_ABOUT_TELEGRAM_URL')) {
    define('LIREATE_ABOUT_TELEGRAM_URL', 'https://t.me/lireate_patent');
}
if (!defined('LIREATE_ABOUT_WHATSAPP_URL')) {
    define('LIREATE_ABOUT_WHATSAPP_URL', 'https://wa.me/79600484957');
}
if (!defined('LIREATE_ABOUT_PHONE_DISPLAY')) {
    define('LIREATE_ABOUT_PHONE_DISPLAY', '+7 (960) 048-49-57');
}
if (!defined('LIREATE_ABOUT_PHONE_TEL')) {
    define('LIREATE_ABOUT_PHONE_TEL', 'tel:+79600484957');
}
if (!defined('LIREATE_ABOUT_PHONE2_DISPLAY')) {
    define('LIREATE_ABOUT_PHONE2_DISPLAY', '+7 (499) 938-45-95');
}
if (!defined('LIREATE_ABOUT_PHONE2_TEL')) {
    define('LIREATE_ABOUT_PHONE2_TEL', 'tel:+74999384595');
}
if (!defined('LIREATE_ABOUT_ASSETS_BASE')) {
    define('LIREATE_ABOUT_ASSETS_BASE', '/template/page/o_kompanii');
}
if (!defined('LIREATE_ABOUT_TEMPLATE_VERSION')) {
    define('LIREATE_ABOUT_TEMPLATE_VERSION', '20261126r20');
}
if (!defined('LIREATE_ABOUT_METRIKA_ID')) {
    $la_metrika = getenv('LIREATE_YANDEX_METRIKA_ID');
    if ($la_metrika === false || $la_metrika === '') {
        $la_metrika = '52622734';
    }
    define('LIREATE_ABOUT_METRIKA_ID', preg_replace('/\D/', '', (string) $la_metrika));
}

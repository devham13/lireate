<?php
/**
 * Конфиг главной LIREATE Light V4 (CMS home /).
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

if (!defined('LIREATE_HOME_TELEGRAM_URL')) {
    define('LIREATE_HOME_TELEGRAM_URL', 'https://t.me/lireate_patent');
}
if (!defined('LIREATE_HOME_MAX_CHAT_URL')) {
    define(
        'LIREATE_HOME_MAX_CHAT_URL',
        defined('LIREATE_MAX_CHAT_URL') ? LIREATE_MAX_CHAT_URL : 'https://max.ru/u/f9LHodD0cOI8jsPagjibEve2cn5_xPW0X1sf3PhbS5TMijA78DnUeDEpUAI'
    );
}

if (!defined('LIREATE_HOME_PHONE_DISPLAY')) {
    define('LIREATE_HOME_PHONE_DISPLAY', '+7 (960) 048-49-57');
}
if (!defined('LIREATE_HOME_PHONE_TEL')) {
    define('LIREATE_HOME_PHONE_TEL', 'tel:+79600484957');
}

if (!defined('LIREATE_HOME_CSS_HREF')) {
    define('LIREATE_HOME_CSS_HREF', '/template/css/lireate-home-light-v4.css');
}
if (!defined('LIREATE_HOME_JS_HREF')) {
    define('LIREATE_HOME_JS_HREF', '/template/js/lireate-home-light-v4.js');
}
if (!defined('LIREATE_HOME_LIGHT_V4_CSS_HREF')) {
    define('LIREATE_HOME_LIGHT_V4_CSS_HREF', '/template/css/lireate-light-v4.css');
}
if (!defined('LIREATE_HOME_LIGHT_V4_JS_HREF')) {
    define('LIREATE_HOME_LIGHT_V4_JS_HREF', '/template/js/lireate-light-v4.js');
}

if (!defined('LIREATE_HOME_TEMPLATE_VERSION')) {
    define('LIREATE_HOME_TEMPLATE_VERSION', '20261126r20');
}
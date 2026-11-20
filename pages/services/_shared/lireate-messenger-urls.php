<?php
/**
 * Канонические URL мессенджеров LIREATE (одна точка правки).
 */
if (!defined('LIREATE_MAX_CHANNEL_URL')) {
    define('LIREATE_MAX_CHANNEL_URL', 'https://max.ru/channel_lireate');
}
if (!defined('LIREATE_MAX_CHAT_URL')) {
    define('LIREATE_MAX_CHAT_URL', 'https://max.ru/u/f9LHodD0cOI8jsPagjibEve2cn5_xPW0X1sf3PhbS5TMijA78DnUeDEpUAI');
}

if (!function_exists('lireate_service_max_channel_url')) {
    function lireate_service_max_channel_url()
    {
        if (defined('LIREATE_MAX_CHANNEL_URL')) {
            return LIREATE_MAX_CHANNEL_URL;
        }
        return 'https://max.ru/channel_lireate';
    }
}

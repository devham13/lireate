<?php
/**
 * Конфиг шаблона статей блога LIREATE (новости /news).
 * Меняйте URL канала MAX в одном месте.
 */
if (!defined('LIREATE_BLOG_MAX_CHANNEL_URL')) {
    define(
        'LIREATE_BLOG_MAX_CHANNEL_URL',
        'https://max.ru/channel_lireate'
    );
}

if (!defined('LIREATE_BLOG_DEFAULT_AUTHOR')) {
    define('LIREATE_BLOG_DEFAULT_AUTHOR', 'Юрий Горбачев');
}

if (!defined('LIREATE_BLOG_TEMPLATE_VERSION')) {
    define('LIREATE_BLOG_TEMPLATE_VERSION', '2026-10-19');
}

if (!defined('LIREATE_BLOG_ASSETS_BASE')) {
    define('LIREATE_BLOG_ASSETS_BASE', '/template/page/news');
}

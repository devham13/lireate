<?php
/**
 * Конфиг шаблона статей блога LIREATE (новости /news).
 */
$messenger_cfg = dirname(__DIR__, 2) . '/services/_shared/lireate-messenger-urls.php';
if (is_file($messenger_cfg)) {
    require_once $messenger_cfg;
}
if (!defined('LIREATE_BLOG_MAX_CHANNEL_URL')) {
    define(
        'LIREATE_BLOG_MAX_CHANNEL_URL',
        defined('LIREATE_MAX_CHANNEL_URL') ? LIREATE_MAX_CHANNEL_URL : 'https://max.ru/channel_lireate'
    );
}

/** Для локального превью: абсолютный origin обложек (напр. прод-сайт). На бою пусто — относительные /template/... */
if (!defined('LIREATE_BLOG_PUBLIC_ORIGIN')) {
    define('LIREATE_BLOG_PUBLIC_ORIGIN', '');
}

if (!defined('LIREATE_BLOG_DEFAULT_AUTHOR')) {
    define('LIREATE_BLOG_DEFAULT_AUTHOR', 'Юрий Горбачев');
}

if (!defined('LIREATE_BLOG_AUTHOR_NAME')) {
    define('LIREATE_BLOG_AUTHOR_NAME', 'Юрий Горбачев');
}

if (!defined('LIREATE_BLOG_AUTHOR_ROLE')) {
    define('LIREATE_BLOG_AUTHOR_ROLE', 'Генеральный директор ООО «ЛИРЕЙТ»');
}

/** Без расширения: /template/images/blog/author/yuriy-gorbachev.{webp,jpg} */
if (!defined('LIREATE_BLOG_AUTHOR_AVATAR_PATH')) {
    define('LIREATE_BLOG_AUTHOR_AVATAR_PATH', '/template/images/blog/author/yuriy-gorbachev');
}

if (!defined('LIREATE_BLOG_READING_WPM')) {
    define('LIREATE_BLOG_READING_WPM', 180);
}

if (!defined('LIREATE_BLOG_TEMPLATE_VERSION')) {
    define('LIREATE_BLOG_TEMPLATE_VERSION', '2026-10-19d');
}

if (!defined('LIREATE_BLOG_ASSETS_BASE')) {
    define('LIREATE_BLOG_ASSETS_BASE', '/template/page/news');
}

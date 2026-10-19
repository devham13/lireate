<?php
/**
 * Конфиг шаблона статей блога LIREATE (новости /news).
 */
$lireate_blog_messenger_candidates = array();
$doc = isset($_SERVER['DOCUMENT_ROOT']) ? rtrim((string) $_SERVER['DOCUMENT_ROOT'], '/') : '';
if ($doc !== '') {
    $lireate_blog_messenger_candidates[] = $doc . '/template/lireate/lireate-messenger-urls.php';
    $lireate_blog_messenger_candidates[] = $doc . '/_shared/lireate-messenger-urls.php';
    $lireate_blog_messenger_candidates[] = $doc . '/pages/services/_shared/lireate-messenger-urls.php';
}
$lireate_blog_messenger_candidates[] = dirname(dirname(__DIR__)) . '/services/_shared/lireate-messenger-urls.php';
$lireate_blog_messenger_candidates[] = dirname(dirname(dirname(__DIR__))) . '/template/lireate/lireate-messenger-urls.php';
foreach ($lireate_blog_messenger_candidates as $messenger_cfg) {
    if ($messenger_cfg !== '' && is_file($messenger_cfg)) {
        require_once $messenger_cfg;
        break;
    }
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
    define('LIREATE_BLOG_TEMPLATE_VERSION', '2026-10-19n');
}

if (!defined('LIREATE_BLOG_LIST_TITLE')) {
    define('LIREATE_BLOG_LIST_TITLE', 'Блог LIREATE');
}

if (!defined('LIREATE_BLOG_LIST_LEAD')) {
    define(
        'LIREATE_BLOG_LIST_LEAD',
        'Практические материалы о товарных знаках, патентах, бренде и защите интеллектуальной собственности для предпринимателей и юристов.'
    );
}

if (!defined('LIREATE_BLOG_ASSETS_BASE')) {
    define('LIREATE_BLOG_ASSETS_BASE', '/template/page/news');
}

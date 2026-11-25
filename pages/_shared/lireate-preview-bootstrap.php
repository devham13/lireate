<?php
/**
 * Локальное превью Light V4: ассеты с live, классы для съёмки скриншотов.
 */
if (!defined('LIREATE_PREVIEW_BOOTSTRAP')) {
    define('LIREATE_PREVIEW_BOOTSTRAP', true);
}

if (!defined('LIREATE_PREVIEW_LIVE_ORIGIN')) {
    $origin = getenv('LIREATE_PUBLIC_SITE_URL');
    if ($origin === false || $origin === '') {
        $origin = getenv('LIREATE_PUBLIC_SITE_HOST');
        if ($origin !== false && $origin !== '') {
            $origin = 'https://' . preg_replace('#^https?://#', '', $origin);
        }
    }
    if (is_string($origin) && $origin !== '') {
        define('LIREATE_PREVIEW_LIVE_ORIGIN', rtrim($origin, '/'));
    }
}

if (defined('LIREATE_PREVIEW_LIVE_ORIGIN') && !defined('LIREATE_BLOG_PUBLIC_ORIGIN')) {
    define('LIREATE_BLOG_PUBLIC_ORIGIN', LIREATE_PREVIEW_LIVE_ORIGIN);
}

if (!function_exists('lireate_preview_live_url')) {
function lireate_preview_live_url($path)
{
    $path = (string) $path;
    if ($path === '' || strpos($path, 'http://') === 0 || strpos($path, 'https://') === 0) {
        return $path;
    }
    if (!defined('LIREATE_PREVIEW_LIVE_ORIGIN')) {
        return $path;
    }
  if ($path[0] !== '/') {
      $path = '/' . $path;
  }
    return LIREATE_PREVIEW_LIVE_ORIGIN . $path;
}
}

if (!function_exists('lireate_preview_init_assets')) {
function lireate_preview_init_assets()
{
    if (!defined('LIREATE_PREVIEW_LIVE_ORIGIN')) {
        return;
    }
    $GLOBALS['lireate_team_photo_origin'] = LIREATE_PREVIEW_LIVE_ORIGIN;
    $GLOBALS['lireate_blog_live_asset_origin'] = LIREATE_PREVIEW_LIVE_ORIGIN;
}
}

if (!function_exists('lireate_blog_media_url')) {
function lireate_blog_media_url($url)
{
    $url = trim((string) $url);
    if ($url === '') {
        return '';
    }
    if (function_exists('lireate_preview_live_url')) {
        $url = lireate_preview_live_url($url);
    }
    return $url;
}
}

lireate_preview_init_assets();
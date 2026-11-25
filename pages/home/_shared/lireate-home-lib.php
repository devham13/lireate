<?php
/**
 * Хелперы главной LIREATE Light V4.
 */
require_once __DIR__ . '/lireate-home-config.php';
require_once __DIR__ . '/lireate-home-data.php';

if (!function_exists('lireate_home_h')) {
function lireate_home_h($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
}
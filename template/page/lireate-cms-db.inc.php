<?php
/**
 * Подключение MySQL для CMS-вставок через data/db.php на хостинге (без секретов в репозитории).
 * PHP 5.6+.
 */
if (!function_exists('lireate_cms_docroot')) {
function lireate_cms_docroot()
{
    if (!empty($_SERVER['DOCUMENT_ROOT'])) {
        return rtrim((string) $_SERVER['DOCUMENT_ROOT'], '/\\');
    }
    return '';
}
}

if (!function_exists('lireate_cms_parse_db_constants')) {
function lireate_cms_parse_db_constants($dbFile)
{
    $text = @file_get_contents($dbFile);
    if ($text === false) {
        return null;
    }
    $keys = array('DB_SERVER', 'DB_USER', 'DB_PASS', 'DB_NAME');
    $out = array();
    foreach ($keys as $key) {
        $pattern = '/define\s*\(\s*["\']' . $key . '["\']\s*,\s*["\']([^"\']*)["\']\s*\)/i';
        if (!preg_match($pattern, $text, $m)) {
            return null;
        }
        $out[$key] = $m[1];
    }
    return $out;
}
}

if (!function_exists('lireate_cms_mysqli')) {
function lireate_cms_mysqli()
{
    if (function_exists('everestConnectDB')) {
        $db = everestConnectDB();
        if ($db instanceof mysqli) {
            return $db;
        }
        if ($db) {
            return $db;
        }
    }
    $doc = lireate_cms_docroot();
    if ($doc === '') {
        return null;
    }
    $dbFile = $doc . '/data/db.php';
    if (is_file($dbFile)) {
        require_once $dbFile;
        if (function_exists('everestConnectDB')) {
            $db = everestConnectDB();
            if ($db) {
                return $db;
            }
        }
    }
    $cfg = lireate_cms_parse_db_constants($dbFile);
    if ($cfg === null) {
        return null;
    }
    $mysqli = @new mysqli($cfg['DB_SERVER'], $cfg['DB_USER'], $cfg['DB_PASS'], $cfg['DB_NAME']);
    if ($mysqli->connect_errno) {
        return null;
    }
    if (!$mysqli->set_charset('utf8mb4')) {
        $mysqli->set_charset('utf8');
    }
    return $mysqli;
}
}

if (!function_exists('lireate_cms_connect_db')) {
function lireate_cms_connect_db()
{
    $db = lireate_cms_mysqli();
    if (!$db) {
        if (defined('LIREATE_PORTFOLIO_PREVIEW') && LIREATE_PORTFOLIO_PREVIEW) {
            return null;
        }
        if (defined('LIREATE_REVIEWS_PREVIEW') && LIREATE_REVIEWS_PREVIEW) {
            return null;
        }
        die('Не удалось подключиться к базе данных.');
    }
    return $db;
}
}
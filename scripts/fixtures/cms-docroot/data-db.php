<?php
define('DB_SERVER', getenv('LIREATE_MYSQL_HOST') ? getenv('LIREATE_MYSQL_HOST') : '127.0.0.1');
define('DB_USER', getenv('LIREATE_MYSQL_USER') ? getenv('LIREATE_MYSQL_USER') : 'root');
define('DB_PASS', getenv('LIREATE_MYSQL_PASSWORD') ? getenv('LIREATE_MYSQL_PASSWORD') : 'root');
define('DB_NAME', getenv('LIREATE_MYSQL_DATABASE') ? getenv('LIREATE_MYSQL_DATABASE') : 'lireate_ci');

function everestConnectDB()
{
    static $link = null;
    if ($link instanceof mysqli) {
        return $link;
    }
    if (function_exists('mysqli_report')) {
        mysqli_report(MYSQLI_REPORT_OFF);
    }
    try {
        $link = @new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
    } catch (Throwable $e) {
        return null;
    }
    if (!$link || $link->connect_errno) {
        $link = null;
        return null;
    }
    $link->set_charset('utf8');
    return $link;
}
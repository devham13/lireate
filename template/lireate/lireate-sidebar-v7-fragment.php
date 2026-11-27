<?php
/**
 * Same-origin HTML fragment for public LIREATE pages missing the CMS sidebar.
 * PHP 5.6 compatible. No user data, no forms, no redirects.
 */
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: public, max-age=300');
include __DIR__ . '/lireate-sidebar-v7-content.inc.php';

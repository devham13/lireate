<?php
/**
 * CLI regression: dual-tree include must not redeclare lireate_blog_dir_up (PHP 5.6).
 */
ini_set('display_errors', '1');
error_reporting(E_ALL);
$doc = getenv('LIREATE_TEST_DOCROOT');
if ($doc === false || $doc === '') {
    fwrite(STDERR, "LIREATE_TEST_DOCROOT required\n");
    exit(2);
}
$_SERVER['DOCUMENT_ROOT'] = $doc;
$tpl = $doc . '/template/page/news/lireate-blog-lib.php';
$shared = $doc . '/pages/blog/_shared/lireate-blog-lib.php';
require_once $tpl;
require_once $shared;
if (!function_exists('lireate_blog_h')) {
    fwrite(STDERR, "lireate_blog_h missing\n");
    exit(1);
}
echo "OK dual-lib\n";

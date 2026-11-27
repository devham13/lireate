<?php
/**
 * Include from index.php immediately before $template = template($db, $temp);
 */
$_lireate_data_dir = is_file(__DIR__ . '/lireate-seo-runtime.php') ? __DIR__ : (__DIR__ . '/data');
$_lireate_seo_rt = $_lireate_data_dir . '/lireate-seo-runtime.php';
$_lireate_seo_schema = $_lireate_data_dir . '/lireate-seo-schema.inc.php';
if (is_file($_lireate_seo_rt)) {
    require_once $_lireate_seo_rt;
}
if (is_file($_lireate_seo_schema)) {
    require_once $_lireate_seo_schema;
}
if (function_exists('lireate_seo_init_globals')) {
    lireate_seo_init_globals();
}

if (!empty($temp['news_sw']) && $temp['news_sw'] === 'news' && !empty($temp['db_page'][0]) && function_exists('lireate_seo_bind_news_article')) {
    lireate_seo_bind_news_article(
        $temp['db_page'][0],
        array(
            'seo_title' => isset($seo_title) ? $seo_title : '',
            'seo_description' => isset($seo_description) ? $seo_description : '',
        )
    );
}

$_lireate_seo_page_meta = $_lireate_data_dir . '/lireate-seo-page-meta.inc.php';
if (is_file($_lireate_seo_page_meta)) {
    require_once $_lireate_seo_page_meta;
}

$__lb_path = function_exists('lireate_seo_request_path') ? lireate_seo_request_path() : '/';
if ($__lb_path === '/avtor' || $__lb_path === '/avtor/') {
    $GLOBALS['lireate_seo_page'] = 'avtor';
    if (isset($temp) && is_array($temp)) {
        $temp['apsw'] = false;
    }
    if (function_exists('lireate_seo_apply_static_page_meta_to_cfg')) {
        lireate_seo_apply_static_page_meta_to_cfg('avtor', $cfg, $seo_title, $seo_description);
    }
}

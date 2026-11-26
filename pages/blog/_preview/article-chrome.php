<?php
/**
 * Превью статьи блога с Nav V3 chrome (скриншоты AFTER).
 */
$repo = dirname(dirname(dirname(__DIR__)));
if (!defined('LIREATE_NAV_V3_ASSET_QS')) {
    define('LIREATE_NAV_V3_ASSET_QS', '20261126r3');
}
define('LIREATE_LH4_BLOG_ARTICLE', true);
require_once $repo . '/pages/_shared/lireate-preview-bootstrap.php';
$_SERVER['DOCUMENT_ROOT'] = $repo;
$_SERVER['REQUEST_URI'] = '/proverka_tovarnogo_znaka_pered_registraciey_chek_list_po_reestram_rospatenta/';
$lireate_blog_title = 'Проверка товарного знака перед регистрацией: чек-лист по реестрам Роспатента';
$lireate_blog_date_display = '19.10.2026';
$lireate_blog_date_iso = '2026-10-19';
$lireate_blog_author = 'Юрий Горбачев';
$lireate_blog_tag = 'Товарный знак';
$lireate_blog_tag_url = '/news?num=2&category=9';
$lireate_blog_current_url = '/proverka_tovarnogo_znaka_pered_registraciey_chek_list_po_reestram_rospatenta/';
$lireate_blog_description = 'Как проверить товарный знак по реестрам Роспатента и ФИПС перед подачей заявки.';
$lireate_blog_canonical = $lireate_blog_current_url;
$lireate_blog_body_html = file_get_contents(__DIR__ . '/sample-body.html');
$assets_base = '/template/page/news';
define('LIREATE_BLOG_ASSETS_BASE', $assets_base);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo htmlspecialchars($lireate_blog_title, ENT_QUOTES, 'UTF-8'); ?></title>
</head>
<body class="lireate-blog-body lireate-theme-light-v4 lireate-screenshot-capture">
<?php
require_once $repo . '/pages/_shared/lireate-public-cms-chrome-lib.php';
lireate_public_cms_include_chrome();
lireate_public_cms_content_open();
lireate_public_cms_include_subnav();
require $repo . '/pages/blog/_shared/lireate-blog-layout.inc.php';
lireate_public_cms_content_close();
$footer = $repo . '/pages/services/_shared/lireate-service-footer.inc.php';
if (is_file($footer)) {
    include $footer;
}
?>
</body>
</html>
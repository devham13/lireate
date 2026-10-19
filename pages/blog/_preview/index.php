<?php
/**
 * Локальное превью шаблона статьи (php -S + symlink в .dev-docroot).
 */
$lireate_blog_title = 'Проверка товарного знака перед регистрацией: чек-лист по реестрам Роспатента';
$lireate_blog_date_display = '19.10.2026';
$lireate_blog_date_iso = '2026-10-19';
$lireate_blog_author = 'Юрий Горбачев';
$lireate_blog_tag = 'Товарный знак';
$lireate_blog_tag_url = '/news?num=2&category=9';
$lireate_blog_current_url = '/proverka_tovarnogo_znaka_pered_registraciey_chek_list_po_reestram_rospatenta/';
$lireate_blog_description = 'Как проверить товарный знак по реестрам Роспатента и ФИПС перед подачей заявки: чек-лист без выдуманных гарантий.';
$lireate_blog_canonical = '/proverka_tovarnogo_znaka_pered_registraciey_chek_list_po_reestram_rospatenta/';
$lireate_blog_body_html = file_get_contents(__DIR__ . '/sample-body.html');

$assets_base = '/template/page/news';
define('LIREATE_BLOG_PUBLIC_ORIGIN', 'https://lireate.ru'); // pragma: allowlist secret
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo htmlspecialchars($lireate_blog_title, ENT_QUOTES, 'UTF-8'); ?></title>
  <meta name="description" content="<?php echo htmlspecialchars($lireate_blog_description, ENT_QUOTES, 'UTF-8'); ?>">
  <link rel="canonical" href="<?php echo htmlspecialchars($lireate_blog_canonical, ENT_QUOTES, 'UTF-8'); ?>">
</head>
<body class="lireate-blog-body">
<?php
define('LIREATE_BLOG_ASSETS_BASE', $assets_base);
require_once dirname(__DIR__) . '/_shared/lireate-blog-layout.inc.php';
$footer = dirname(__DIR__, 2) . '/services/_shared/lireate-service-footer.inc.php';
if (is_file($footer)) {
    include $footer;
}
?>
</body>
</html>

<?php
/**
 * Локальное превью главной Light V4 (CMS page_container + полный шаблон).
 * php -S 127.0.0.1:19080 pages/home/_preview/router.php
 */
$repo_root = dirname(dirname(dirname(__DIR__)));
unset($_ENV['LIREATE_PUBLIC_SITE_URL'], $_ENV['LIREATE_PUBLIC_SITE_HOST']);
putenv('LIREATE_PUBLIC_SITE_URL');
putenv('LIREATE_PUBLIC_SITE_HOST');
require_once $repo_root . '/pages/_shared/lireate-preview-bootstrap.php';
require_once $repo_root . '/pages/home/_shared/lireate-home-config.php';
$lh_preview_v = defined('LIREATE_HOME_CSS_VERSION') ? LIREATE_HOME_CSS_VERSION : '20261126r27';
if (!defined('LIREATE_NAV_V3_ASSET_QS')) {
    define('LIREATE_NAV_V3_ASSET_QS', '20261126r4');
}
if (!defined('LIREATE_HOME_PREVIEW')) {
    define('LIREATE_HOME_PREVIEW', true);
}
$_SERVER['DOCUMENT_ROOT'] = $repo_root;
$temp = array('page' => 'home');
$seo_title = 'Юридическая защита интеллектуальной собственности в России';
$seo_description = 'Регистрация товарных знаков, патентов, программ ЭВМ и договоры. Помогаем выбрать способ защиты, говорим о реальных возможностях и сроках и сопровождаем до результата.';
$metrika_id = '52622734'; // pragma: allowlist secret
?><!DOCTYPE html>
<html lang="ru" data-lireate-metrika-id="<?php echo htmlspecialchars($metrika_id, ENT_QUOTES, 'UTF-8'); ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo htmlspecialchars($seo_title, ENT_QUOTES, 'UTF-8'); ?></title>
  <meta name="description" content="<?php echo htmlspecialchars($seo_description, ENT_QUOTES, 'UTF-8'); ?>">
  <link rel="canonical" href="/">
  <link rel="stylesheet" href="/template/css/lireate-cms-pages-light-v4.css?v=<?php echo htmlspecialchars($lh_preview_v, ENT_QUOTES, 'UTF-8'); ?>">
  <link rel="stylesheet" href="/template/css/lireate-home-light-v4.css?v=<?php echo htmlspecialchars($lh_preview_v, ENT_QUOTES, 'UTF-8'); ?>">
  <link rel="stylesheet" href="/pages/services/_shared/lireate-service-dark.css?v=20261125c">
  <?php require $repo_root . '/template/page/home/lireate-home-schema-live.inc.php'; ?>
  <script type="text/javascript">
   (function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
   m[i].l=1*new Date();k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)})
   (window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym");
   ym(<?php echo (int) $metrika_id; ?>, "init", {
        clickmap:true,
        trackLinks:true,
        accurateTrackBounce:true
   });
  </script>
  <script src="/template/js/lireate-utm.js?v=20261117b1"></script>
</head>
<body class="cms-default lireate-home-light-v4 lireate-theme-light-v4 lireate-home-preview">
<?php
require_once $repo_root . '/pages/_shared/lireate-public-cms-chrome-lib.php';
lireate_public_cms_include_chrome();
lireate_public_cms_content_open();
?>
<div class="wrapper">
  <div class="container text-center content">
    <div class="page_container">
<?php
require $repo_root . '/template/page/home/lireate-home-cms.inc.php';
?>
    </div>
  </div>
</div>
<?php
lireate_public_cms_content_close();
$footer = $repo_root . '/pages/services/_shared/lireate-service-footer.inc.php';
if (is_file($footer)) {
    include $footer;
}
?>
</body>
</html>
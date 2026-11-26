<?php
/**
 * Live-like главная: боевой home CMS inc + Nav V3 chrome (не nav-v3-router preview).
 */
$repo_root = dirname(__DIR__, 2);
require_once $repo_root . '/pages/_shared/lireate-preview-bootstrap.php';
require_once $repo_root . '/pages/home/_shared/lireate-home-config.php';
$lh_preview_v = defined('LIREATE_HOME_CSS_VERSION') ? LIREATE_HOME_CSS_VERSION : '20261126r27';
if (!defined('LIREATE_NAV_V3_ASSET_QS')) {
    define('LIREATE_NAV_V3_ASSET_QS', '20261126r6');
}
$_SERVER['DOCUMENT_ROOT'] = $repo_root;
$temp = array('page' => 'home');
$seo_title = 'Юридическая защита интеллектуальной собственности в России';
$seo_description = 'Регистрация товарных знаков, патентов, программ ЭВМ и договоры.';
$metrika_id = '';
?><!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo htmlspecialchars($seo_title, ENT_QUOTES, 'UTF-8'); ?></title>
  <meta name="description" content="<?php echo htmlspecialchars($seo_description, ENT_QUOTES, 'UTF-8'); ?>">
  <link rel="stylesheet" href="/template/css/lireate-cms-pages-light-v4.css?v=<?php echo htmlspecialchars($lh_preview_v, ENT_QUOTES, 'UTF-8'); ?>">
  <link rel="stylesheet" href="/template/css/lireate-home-light-v4.css?v=<?php echo htmlspecialchars($lh_preview_v, ENT_QUOTES, 'UTF-8'); ?>">
</head>
<body class="cms-default lireate-home-light-v4 lireate-theme-light-v4 lireate-public-cms-chrome">
<?php
require_once $repo_root . '/pages/_shared/lireate-public-cms-chrome-lib.php';
lireate_public_cms_include_chrome();
lireate_public_cms_content_open();
?>
<div class="wrapper">
  <div class="container text-center content">
    <div class="page_container">
<?php require $repo_root . '/template/page/home/lireate-home-cms.inc.php'; ?>
    </div>
  </div>
</div>
<?php
lireate_public_cms_content_close();
?>
</body>
</html>
<?php
/** Local /mktu sidebar logo QA (CMS mask + V7 wordmark). */
$repo = dirname(__DIR__, 3);
require_once $repo . '/pages/services/_shared/lireate-sitewide-sidebar-v7.inc.php';
$v7css = '/template/css/lireate-sitewide-sidebar-v7.css?v=20261127c';
?><!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>МКТУ — sidebar logo preview</title>
  <link rel="stylesheet" href="/template/css/lireate-cms-pages-light-v4.css?v=qa">
  <link rel="stylesheet" href="<?php echo htmlspecialchars($v7css, ENT_QUOTES, 'UTF-8'); ?>">
  <?php lireate_sitewide_sidebar_v7_head(); ?>
</head>
<body class="cms-default lireate-cms-page-dark lireate-mktu-lu-body lireate-theme-light-v4 lsv7-managed lsv7-sidebar-present">
<?php lireate_sitewide_sidebar_v7_render(); ?>
<div class="page_container"><div class="container content"><section class="mktu_head"><h1 class="sub-title">МКТУ — preview</h1></section></div></div>
</body>
</html>
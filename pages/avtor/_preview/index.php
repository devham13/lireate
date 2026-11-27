<?php
/**
 * Превью /avtor/ Light V4 в CMS-оболочке как на проде:
 * prod CSS bundles + локальные avtor / sidebar V7 (без фото в меню).
 */
$repo = dirname(dirname(dirname(__DIR__)));
if (!defined('LIREATE_NAV_V3_ASSET_QS')) {
    define('LIREATE_NAV_V3_ASSET_QS', '20261127-avtor-shell');
}
require_once $repo . '/pages/_shared/lireate-preview-bootstrap.php';
$_SERVER['DOCUMENT_ROOT'] = $repo;
$_SERVER['REQUEST_URI'] = '/avtor/';
require_once $repo . '/pages/avtor/_shared/lireate-avtor-config.php';
require_once $repo . '/pages/services/_shared/lireate-sitewide-sidebar-v7.inc.php';

$pv = '20261127-avtor-shell';
$live = defined('LIREATE_PREVIEW_LIVE_ORIGIN') ? LIREATE_PREVIEW_LIVE_ORIGIN : '';
$bundle_a = $live . '/template/css/lireate-bundle-502ea8ef39bdb181.css';
$bundle_b = $live . '/template/css/lireate-bundle-96e274db0fbaa094.css';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo htmlspecialchars('Юрий Горбачев — директор патентного бюро Лирейт (preview)', ENT_QUOTES, 'UTF-8'); ?></title>
<?php if ($live !== '') : ?>
  <link rel="stylesheet" href="<?php echo htmlspecialchars($bundle_a, ENT_QUOTES, 'UTF-8'); ?>">
  <link rel="stylesheet" href="<?php echo htmlspecialchars($bundle_b, ENT_QUOTES, 'UTF-8'); ?>">
<?php endif; ?>
  <link rel="stylesheet" href="/template/css/lireate-light-v4.css?v=<?php echo htmlspecialchars($pv, ENT_QUOTES, 'UTF-8'); ?>">
  <link rel="stylesheet" href="/pages/avtor/_shared/lireate-avtor.css?v=<?php echo htmlspecialchars($pv, ENT_QUOTES, 'UTF-8'); ?>">
  <link rel="stylesheet" href="/pages/avtor/_preview/preview-chrome.css?v=<?php echo htmlspecialchars($pv, ENT_QUOTES, 'UTF-8'); ?>">
  <?php lireate_sitewide_sidebar_v7_head(); ?>
</head>
<body class="cms-default lireate-cms-page-dark lireate-avtor-body lireate-theme-light-v4 lireate-screenshot-capture lsv7-managed lsv7-sidebar-present">
<?php lireate_sitewide_sidebar_v7_render(); ?>
<div class="wrapper">
  <div class="content">
    <div class="container content">
      <div class="row">
        <div class="col-xs-12">
          <div class="page_container">
<?php require $repo . '/pages/avtor/_shared/lireate-avtor-cms.inc.php'; ?>
          </div><!-- /page_container -->
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
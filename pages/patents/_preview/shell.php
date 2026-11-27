<?php
/**
 * Local CMS-like shell for /patent and /patent_prom_obr (sidebar V7 + patent light v4 hero).
 * Preview: scripts/lireate_patent_cms_preview_server.sh
 */
$key = isset($_GET['page']) ? (string) $_GET['page'] : 'patent';
if (!in_array($key, ['patent', 'patent_prom_obr'], true)) {
    header('HTTP/1.0 400 Bad Request');
    echo 'bad page';
    exit;
}
$lireate_patent_key = $key;
$repo = dirname(__DIR__, 3);
$shared = $repo . '/pages/services/_shared';
require_once $shared . '/lireate-sitewide-sidebar-v7.inc.php';
?><!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Patent preview — <?php echo htmlspecialchars($key, ENT_QUOTES, 'UTF-8'); ?></title>
  <?php lireate_sitewide_sidebar_v7_head(); ?>
</head>
<body class="cms-default lireate-cms-page-dark lireate-patent-light-v4 lireate-theme-light-v4 lsv7-managed lsv7-sidebar-present">
<?php lireate_sitewide_sidebar_v7_render(); ?>
<div class="page_container">
<?php
$_SERVER['DOCUMENT_ROOT'] = $repo;
require dirname(__DIR__) . '/_shared/lireate-patent-page.inc.php';
?>
</div>
</body>
</html>
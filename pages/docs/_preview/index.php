<?php
$repo = dirname(__DIR__, 3);
require_once $repo . '/pages/_shared/lireate-preview-bootstrap.php';
$_SERVER['DOCUMENT_ROOT'] = $repo;
?><!DOCTYPE html>
<html lang="ru">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Документы — Light V4 preview</title></head>
<body class="cms-default lireate-docs-body lireate-theme-light-v4 lireate-screenshot-capture">
<?php
require_once $repo . '/pages/_shared/lireate-public-cms-chrome-lib.php';
lireate_public_cms_include_chrome();
lireate_public_cms_content_open();
lireate_public_cms_include_subnav();
$inc = $repo . '/template/page/docs/lireate-docs-cms.inc.php';
if (is_file($inc)) {
    require $inc;
} else {
    require $repo . '/template/page/docs/lireate-docs-page.inc.php';
}
lireate_public_cms_content_close();
?>
</body>
</html>
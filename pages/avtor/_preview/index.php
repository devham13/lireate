<?php
$repo = dirname(dirname(dirname(__DIR__)));
if (!defined('LIREATE_NAV_V3_ASSET_QS')) {
    define('LIREATE_NAV_V3_ASSET_QS', '20261127');
}
require_once $repo . '/pages/_shared/lireate-preview-bootstrap.php';
$_SERVER['DOCUMENT_ROOT'] = $repo;
$_SERVER['REQUEST_URI'] = '/avtor/';
require_once $repo . '/pages/avtor/_shared/lireate-avtor-config.php';
$seo_title = 'Юрий Горбачев — директор патентного бюро Лирейт';
$seo_description = 'Юрий Горбачев — директор и управляющий партнёр ООО «ЛИРЕЙТ»: товарные знаки, патенты, авторское право и споры. Статьи на vc.ru, в Т—Ж, на Клерке и Хабре.';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo htmlspecialchars($seo_title, ENT_QUOTES, 'UTF-8'); ?></title>
  <meta name="description" content="<?php echo htmlspecialchars($seo_description, ENT_QUOTES, 'UTF-8'); ?>">
  <link rel="canonical" href="/avtor/">
  <link rel="stylesheet" href="/template/css/lireate-cms-pages-light-v4.css?v=20261127">
</head>
<body class="cms-default lireate-avtor-body lireate-theme-light-v4 lireate-screenshot-capture">
<?php
require_once $repo . '/pages/_shared/lireate-public-cms-chrome-lib.php';
lireate_public_cms_include_chrome();
lireate_public_cms_content_open();
require $repo . '/pages/avtor/_shared/lireate-avtor-cms.inc.php';
lireate_public_cms_content_close();
?>
</body>
</html>
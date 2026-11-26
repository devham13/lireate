<?php
$repo = dirname(dirname(dirname(__DIR__)));
require_once $repo . '/pages/_shared/lireate-preview-bootstrap.php';
$_SERVER['DOCUMENT_ROOT'] = $repo;
$_SERVER['REQUEST_URI'] = '/mktu';
if (!defined('LIREATE_NAV_V3_ASSET_QS')) {
    define('LIREATE_NAV_V3_ASSET_QS', '20261126r3');
}
$v = LIREATE_NAV_V3_ASSET_QS;
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>МКТУ — preview Light V4 + Nav V3</title>
  <link rel="stylesheet" href="/template/css/lireate-cms-pages-light-v4.css?v=<?php echo htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); ?>">
</head>
<body class="cms-default lireate-mktu-lu-body lireate-theme-light-v4 lireate-public-cms-chrome lireate-screenshot-capture">
<?php
require_once $repo . '/pages/_shared/lireate-public-cms-chrome-lib.php';
lireate_public_cms_include_chrome();
lireate_public_cms_content_open();
lireate_public_cms_include_subnav();
?>
<section class="mktu_head">
  <div class="container content">
    <article><h1>МКТУ — Международная классификация товаров и услуг</h1><p class="default_text">Справочник классов для регистрации товарного знака.</p></article>
  </div>
</section>
<section class="mktu_body" id="mktu-classes">
  <div class="container content">
    <h2>Класс 35</h2>
    <p>Реклама; управление коммерческой деятельностью.</p>
    <h2 id="mktu-search">Класс 42</h2>
    <p>Научные и технологические услуги; разработка ПО.</p>
  </div>
</section>
<?php
lireate_public_cms_content_close();
?>
</body>
</html>
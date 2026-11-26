<?php
$repo = dirname(__DIR__, 3);
$_SERVER['DOCUMENT_ROOT'] = $repo;
$v = '20261126r21';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>МКТУ — preview Light V4</title>
  <link rel="stylesheet" href="/template/css/lireate-light-v4.css?v=<?php echo htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); ?>">
  <link rel="stylesheet" href="/template/css/lireate-mktu-light-v4.css?v=<?php echo htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); ?>">
  <link rel="stylesheet" href="/template/css/lireate-cms-pages-light-v4.css?v=<?php echo htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); ?>">
  <link rel="stylesheet" href="/template/css/lireate-global-subnav-light-v4.css?v=<?php echo htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); ?>">
</head>
<body class="cms-default lireate-mktu-lu-body lireate-theme-light-v4 lireate-cms-page-dark lireate-screenshot-capture">
<?php
require_once $repo . '/pages/_shared/lireate-public-cms-chrome-lib.php';
lireate_public_cms_include_chrome();
?>
<?php
require_once $repo . '/pages/_shared/lireate-lh4-subnav-lib.php';
lireate_lh4_include_subnav();
?>
<section class="mktu_head">
  <div class="container content">
    <article><h1>МКТУ — Международная классификация товаров и услуг</h1><p class="default_text">Справочник классов для регистрации товарного знака.</p></article>
  </div>
</section>
<section class="mktu_body">
  <div class="container content">
    <h2>Класс 35</h2>
    <p>Реклама; управление коммерческой деятельностью.</p>
    <h2>Класс 42</h2>
    <p>Научные и технологические услуги; разработка ПО.</p>
  </div>
</section>
</body>
</html>
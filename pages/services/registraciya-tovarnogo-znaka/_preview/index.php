<?php
/**
 * Nav V3 AFTER preview: CMS chrome + фрагмент услуги (без полного longread deploy).
 */
$repo = dirname(__DIR__, 4);
$_SERVER['DOCUMENT_ROOT'] = $repo;
$_SERVER['REQUEST_URI'] = '/registraciya_tovarnogo_znaka/';
if (!defined('LIREATE_NAV_V3_ASSET_QS')) {
    define('LIREATE_NAV_V3_ASSET_QS', '20261126r3');
}
require_once $repo . '/pages/_shared/lireate-preview-bootstrap.php';
require_once $repo . '/pages/services/_shared/lireate-public-cms-chrome-wire.inc.php';
?><!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Регистрация товарного знака — Nav V3 preview</title>
  <meta name="robots" content="noindex,nofollow">
</head>
<body class="cms-default lireate-theme-light-v4 lireate-screenshot-capture">
<?php
lireate_public_cms_chrome_wire_open();
?>
<div class="lireate-public-cms-service-preview" style="padding:24px;max-width:960px">
  <p class="default_text" style="color:#52637b;font-size:14px">Превью chrome: услуга «Регистрация товарного знака» (якоря — общие с главной).</p>
  <h1 id="services" style="font-family:Manrope,sans-serif;margin-top:12px">Регистрация товарного знака под ключ</h1>
  <p>Фрагмент для скриншота навигации; полный longread — в каталоге pages/services/registraciya-tovarnogo-znaka/.</p>
  <h2 id="pricing" style="margin-top:32px">Стоимость</h2>
  <p>Госпошлина за 1 класс — 35 000 ₽ по таблице Роспатента (ориентир).</p>
  <h2 id="faq" style="margin-top:32px">FAQ</h2>
  <p>Гарантий регистрации нет — решение принимает Роспатент.</p>
</div>
<?php lireate_public_cms_chrome_wire_close(); ?>
</body>
</html>
<?php
/**
 * Превью /kontaktyi/ в оболочке CMS (левое меню + тёмный контент).
 */
$repo = dirname(__DIR__, 2);
$docroot = getenv('LIREATE_CONTACTS_PREVIEW_DOCROOT');
if ($docroot === false || $docroot === '') {
    $docroot = $repo;
}
$_SERVER['DOCUMENT_ROOT'] = $docroot;
if (!defined('LIREATE_CONTACTS_ASSETS_BASE')) {
    define('LIREATE_CONTACTS_ASSETS_BASE', '/template/page/kontaktyi');
}
$lc_cms_origin = getenv('LIREATE_CONTACTS_CMS_CSS_ORIGIN');
if ($lc_cms_origin === false || $lc_cms_origin === '') {
    $lc_cms_origin = 'https://' . 'lireate' . '.' . 'ru'; // pragma: allowlist secret
}
$lc_assets = LIREATE_CONTACTS_ASSETS_BASE;
$lc_v = '2026-10-28a';
$GLOBALS['lireate_contacts_photo_origin'] = $lc_cms_origin;
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Контакты — CMS preview</title>
  <style>
    #leftSideBar { position: fixed; left: 0; top: 0; width: 270px; height: 100vh; background: #262c40; z-index: 99; color: #fff; padding: 24px 16px; box-sizing: border-box; overflow-y: auto; }
    .leftSideLogo { display: block; width: 140px; height: 48px; margin-bottom: 20px; background: linear-gradient(135deg,#6d28d9,#2563eb); border-radius: 8px; }
    .leftSideTitle { font-size: 13px; text-transform: uppercase; opacity: .65; margin: 16px 0 8px; }
    .leftSideNaviButton { display: block; color: #e2e8f0; text-decoration: none; padding: 8px 10px; border-radius: 8px; margin-bottom: 4px; font-size: 15px; }
    .leftSideNaviButton:hover, a.leftSideNaviButton:nth-of-type(3) { background: rgba(255,255,255,.08); }
    .leftSideTel { font-size: 14px; margin-top: 8px; }
    .leftSideTel a { color: #fff; text-decoration: none; }
    .wrapper { min-height: 100vh; }
    .content { margin-left: 270px; padding: 0; min-height: 100vh; }
    @media (max-width: 1150px) { #leftSideBar { width: 240px; } .content { margin-left: 240px; } }
    @media (max-width: 900px) { #leftSideBar { width: 220px; } .content { margin-left: 220px; } }
  </style>
  <script src="https://vk.com/js/api/openapi.js?169"></script>
  <script>document.documentElement.setAttribute('data-lireate-metrika-id','52622734');</script><!-- pragma: allowlist secret -->
</head>
<body class="cms-default lireate-contacts-body">
<section id="leftSideBar">
  <a class="leftSideLogo" href="/"></a>
  <nav id="leftSideNav">
    <div class="leftSideTitle">Информация о компании</div>
    <a href="/about" class="leftSideNaviButton">О компании</a>
    <a href="/team" class="leftSideNaviButton">Команда Лирейт</a>
    <a href="/kontaktyi/" class="leftSideNaviButton">Контакты</a>
    <div class="leftSideTel leftSideTelFirst"><a href="tel:+74999384595">+7 (499) 938-45-95</a></div>
    <div class="leftSideTel"><a href="tel:+79600484957">+7 (960) 048-49-57</a></div>
  </nav>
</section>
<div class="wrapper">
  <div class="container content">
    <section id="lireate-contacts">
<?php
$page = dirname(__DIR__) . '/_shared/lireate-contacts-page.inc.php';
if (is_file($page)) {
    include $page;
}
require_once dirname(__DIR__) . '/_shared/lireate-contacts-lib.php';
lireate_contacts_require_blog_footer();
?>
    </section>
  </div>
</div>
<footer class="footer"><p>Legacy CMS footer (hidden by contacts CSS)</p></footer>
</body>
</html>

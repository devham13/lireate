<?php
/** Упрощённое CMS-меню для превью (структура как на live: hamburger + leftSideBar). */
?>
<link rel="stylesheet" href="/template/css/lireate-home-preview-chrome.css?v=20261125d">
<div class="lireate-cms-chrome">
  <button type="button" class="hamburger is-closed" data-toggle="offcanvas" aria-label="Меню сайта">
    <span class="hamb-top"></span>
    <span class="hamb-middle"></span>
    <span class="hamb-bottom"></span>
  </button>
  <div id="leftSideBar">
    <a class="leftSideLogo" href="/" aria-label="LIREATE"></a>
    <nav id="leftSideNav" aria-label="Меню CMS">
      <div class="leftSideTitle">Услуги</div>
      <a href="/findtz" class="leftSideNaviInSection">Проверка товарного знака</a>
      <a href="/registraciya_tovarnogo_znaka/" class="leftSideNaviInSection">Регистрация товарного знака</a>
      <a href="/prodlenie_tovarnogo_znaka/" class="leftSideNaviInSection">Продление товарного знака</a>
      <div class="leftSideTitle">Патенты</div>
      <a href="/patent" class="leftSideNaviInSection">Патентование изобретений</a>
      <div class="leftSideTitle">Компания</div>
      <a href="/team/" class="leftSideNaviInSection">Команда</a>
      <a href="/o_kompanii/" class="leftSideNaviInSection">О компании</a>
      <a href="/portfolio/" class="leftSideNaviInSection">Портфолио</a>
      <a href="/reviews/" class="leftSideNaviInSection">Отзывы</a>
      <a href="/news/" class="leftSideNaviInSection">Блог</a>
      <a href="/contacts/" class="leftSideNaviInSection">Контакты</a>
    </nav>
  </div>
</div>
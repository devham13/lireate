<?php
/** Shared LIREATE sidebar used only by standalone Patent Tasks pages.
 * Original links/official social vectors copied from pages/_shared/lireate-public-cms-sidebar.inc.php.
 * No duplicated modal handler: standalone pages retain their own consultation CTA.
 */
?>
<section id="leftSideBar" class="lireate-public-cms-sidebar lxq-tools-sidebar" aria-label="Меню сайта">
  <div class="lpc-sidebar-head">
    <a class="leftSideLogo" href="/" aria-label="LIREATE — на главную"></a>
    
  </div>
  <nav id="leftSideNav" aria-label="Разделы">
    <div class="leftSideTitle">Услуги</div>
    <div class="leftSideNaviButton js-question">Товарный знак <div class="leftSideArrow"></div></div>
    <div class="js-answer">
      <a href="/findtz" class="leftSideNaviInSection">Проверка товарного знака</a>
      <a href="/registraciya_tovarnogo_znaka/" class="leftSideNaviInSection">Регистрация товарного знака</a>
      <a href="/prodlenie_tovarnogo_znaka/" class="leftSideNaviInSection">Продление товарного знака</a>
    </div>
    <div class="leftSideNaviButton js-question">Патенты <div class="leftSideArrow"></div></div>
    <div class="js-answer">
      <a href="/patent" class="leftSideNaviInSection">Патентование изобретений</a>
      <a href="/patent_prom_obr" class="leftSideNaviInSection">Патентование промышленных образцов</a>
    </div>
    <a href="/uslugi/" class="leftSideNaviButton">Все услуги</a>
    <div class="bottomLine"></div>
    <div class="leftSideTitle">Портфолио</div>
    <a href="/portfolio" class="leftSideNaviButton">Портфолио</a>
    <a href="/reviews" class="leftSideNaviButton">Видео отзывы</a>
    <div class="bottomLine"></div>
    <div class="leftSideTitle">Полезная информация</div>
    <a href="/news" class="leftSideNaviButton">Блог</a>
    <a href="/mktu" class="leftSideNaviButton">МКТУ</a>
    <a href="/patentnye-zadachi/" class="leftSideNaviButton">Патентные задачи</a>
    <div class="bottomLine"></div>
    <div class="leftSideTitle">Информация о компании</div>
    <a href="/o_kompanii" class="leftSideNaviButton">О компании</a>
    <a href="/team" class="leftSideNaviButton">Команда Лирейт</a>
    <a href="/partners" class="leftSideNaviButton">Партнёры</a>
    <a href="/docs" class="leftSideNaviButton">Документы</a>
    <a href="/contacts/" class="leftSideNaviButton">Контакты</a>
    <div class="bottomLine" style="margin-top:17px;"></div>
    <div class="leftSideSoc">
      <a class="lsn-soc--tg" href="https://t.me/lireate_patent" target="_blank" rel="noopener noreferrer" aria-label="Telegram" data-lx-ym-goal="perehod_v_telegramm"><svg width="24" height="24" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M9.78 18.65l.28-4.23 7.68-6.92c.34-.31-.07-.46-.52-.19L7.74 13.3 3.64 12c-.88-.25-.89-.86.2-1.3l15.97-6.16c.73-.33 1.43.18 1.15 1.3l-2.72 12.81c-.19.91-.74 1.13-1.5.71L12.6 16.3l-1.99 1.93c-.23.23-.42.42-.83.42z"/></svg></a>
      <a class="lsn-soc--wa" href="https://wa.me/79600484957" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp" data-lx-ym-goal="perehod_v_messendzher"><svg width="24" height="24" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M12 2C6.5 2 2 6.2 2 11.4c0 1.9.5 3.7 1.5 5.3L2 22l5.6-1.5c1.5.8 3.2 1.3 4.9 1.3 5.5 0 10-4.2 10-9.4S17.5 2 12 2zm5.8 13.5c-.2.6-1.2 1.1-1.7 1.1-.4 0-1.1.2-3.6-.9-3-1.3-5-4.4-5.1-4.6-.2-.3-1.2-1.6-1.2-3.1 0-1.5.8-2.2 1.1-2.5.3-.3.6-.4.8-.4.2 0 .4 0 .6.1.2 0 .4.2.5.5.2.5.6 1.7.7 1.8.1.1.1.3 0 .4 0 .1-.1.2-.2.3l-.3.3c-.1.1-.2.2-.1.4.1.2.5 1 1.1 1.6.8.7 1.5 1 1.7 1.1.2.1.3 0 .4-.1l.6-.7c.1-.2.3-.2.5-.1.8.3 2.8 1.3 3.2 1.5.3.2.5.3.6.4.1.2.1.7-.1 1.3z"/></svg></a>
      <a class="lsn-soc--max" href="https://max.ru/channel_lireate" target="_blank" rel="noopener noreferrer" aria-label="Max" data-lx-ym-goal="perehod_v_messendzher"><svg width="24" height="24" viewBox="0 0 100 100" aria-hidden="true" focusable="false"><path fill="currentColor" d="M50.76 0.26c27.54 0 49.13 22.34 49.13 49.89S77.61 99.49 51.02 99.49c-9.44 0-14.01-1.33-21.37-6.54-.51-.36-1.2-.27-1.63.19-5.66 6.04-20.17 10.28-20.83 2.03 0-14.39-7.19-23.73-7.19-45.3C0 21.55 23.22 0.26 50.76 0.26zm.77 24.55c-13.06-.69-23.26 8.39-25.51 22.57-1.86 11.75 1.44 26.07 4.27 26.79 1.2.31 4.08-1.88 6.18-3.86.39-.37 1-.44 1.46-.15 3.27 2 6.97 3.5 11.05 3.71 13.41.7 25.3-9.8 26.01-23.21.7-13.41-10.02-25.14-23.43-25.84z"/></svg></a>
      <a class="lsn-soc--vk" href="https://vk.ru/lireate" target="_blank" rel="noopener noreferrer" aria-label="ВКонтакте"><svg width="24" height="24" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M12.785 16.241s.288-.032.436-.194c.136-.148.132-.427.132-.427s-.02-1.304.58-1.496c.591-.19 1.35.956 2.155 1.378.605.318 1.064.248 1.064.248l2.141-.03s1.117-.07.587-.95c-.043-.073-.308-.65-1.588-1.838-1.344-1.245-1.163-.522.454-1.598.983-.656 1.377-1.055 1.254-1.224-.116-.155-.833-.114-.833-.114l-2.375.015s-.176-.024-.306.053c-.127.075-.208.249-.208.249s-.374 1.001-.87 1.852c-1.05 1.508-1.47 1.587-1.641 1.493-.399-.214-.299-.86-.299-1.319 0-1.434.216-2.03-.422-2.186-.212-.051-.368-.085-.911-.09-.697-.007-1.286.002-1.619.166-.222.115-.393.372-.289.387.129.018.421.078.576.285.2.264.193.855.193.855s.116 1.72-.27 1.936c-.266.175-.631-.182-1.414-1.283-.4-.54-.702-1.137-.702-1.137s-.058-.142-.162-.218c-.126-.093-.302-.122-.302-.122l-2.256.014s-.338.01-.462.156c-.111.13-.008.4-.008.4s1.77 4.146 3.773 6.24c1.836 1.918 3.927 1.794 3.927 1.794h.947z"/></svg></a>
      <a class="lsn-soc--yt" href="https://www.youtube.com/channel/UCpg1LWotCQWwzviPDb_9Rew" target="_blank" rel="noopener noreferrer" aria-label="YouTube"><svg width="24" height="24" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M21.6 7.2a2.5 2.5 0 0 0-1.8-1.8C18.2 5 12 5 12 5s-6.2 0-7.8.4A2.5 2.5 0 0 0 2.4 7.2 26 26 0 0 0 2 12a26 26 0 0 0 .4 4.8 2.5 2.5 0 0 0 1.8 1.8C5.8 19 12 19 12 19s6.2 0 7.8-.4a2.5 2.5 0 0 0 1.8-1.8A26 26 0 0 0 22 12a26 26 0 0 0-.4-4.8zM10 15V9l5.2 3L10 15z"/></svg></a>
    </div>
    <div class="bottomLine"></div>
    <a href="tel:+74999384595" class="leftSideNaviButton leftSideTel leftSideTelFirst" data-lx-ym-goal="klik_po_nomeru_telefona">+7 (499) 938-45-95</a>
    <a href="tel:+79600484957" class="leftSideNaviButton leftSideTel" data-lx-ym-goal="klik_po_nomeru_telefona">+7 (960) 048-49-57</a>
  </nav>
  
</section>

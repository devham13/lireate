<?php
/**
 * Верхнее меню LH4 на CMS-страницах (≥1151px). PHP 5.6.
 */
require_once __DIR__ . '/lireate-lh4-subnav-lib.php';
$lh4n_v = lireate_lh4_subnav_version();
$lh4n_cta = lireate_lh4_subnav_cta_href();
?>
<link rel="stylesheet" href="/template/css/lireate-global-subnav-light-v4.css?v=<?php echo lireate_lh4_subnav_h($lh4n_v); ?>">
<nav class="lh4-subnav" id="lh4Subnav" aria-label="Навигация по разделам сайта">
  <div class="lh4-subnav-in">
    <div class="lh4-subnav-links">
      <a href="/#services">Услуги</a>
      <a href="/#team">Команда</a>
      <a href="/#cases">Кейсы</a>
      <a href="/#pricing">Стоимость</a>
      <a href="/#how">Этапы</a>
      <a href="/#faq">FAQ</a>
      <a href="/#blog">Блог</a>
    </div>
    <div class="lh4-subnav-right">
      <a class="lh4-subnav-phone" href="<?php echo lireate_lh4_subnav_h(LIREATE_LH4_SUBNAV_PHONE_TEL); ?>" data-lx-ym-goal="klik_po_nomeru_telefona"><?php echo lireate_lh4_subnav_h(LIREATE_LH4_SUBNAV_PHONE_DISPLAY); ?></a>
      <a class="lh4-subnav-cta btn primary" href="<?php echo lireate_lh4_subnav_h($lh4n_cta); ?>">Оставить заявку</a>
    </div>
  </div>
</nav>
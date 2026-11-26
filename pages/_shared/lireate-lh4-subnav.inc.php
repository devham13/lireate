<?php
/**
 * Верхнее меню LH4 на CMS-страницах (≥1151px). PHP 5.6.
 */
require_once __DIR__ . '/lireate-lh4-subnav-lib.php';
$lh4n_v = lireate_lh4_subnav_version();
$lh4n_cta = lireate_lh4_subnav_cta_href();
?>
<link rel="stylesheet" href="/template/css/lireate-global-subnav-light-v4.css?v=<?php echo lireate_lh4_subnav_h($lh4n_v); ?>">
<?php
$lh4n_items = function_exists('lireate_lh4_subnav_items_for_request')
    ? lireate_lh4_subnav_items_for_request()
    : array();
?>
<nav class="lh4-subnav ln3-page-subnav" id="lh4Subnav" aria-label="Навигация по разделам страницы">
  <div class="lh4-subnav-in">
    <div class="lh4-subnav-links" role="navigation">
<?php foreach ($lh4n_items as $lh4n_item) : ?>
      <a href="<?php echo lireate_lh4_subnav_h($lh4n_item['href']); ?>"><?php echo lireate_lh4_subnav_h($lh4n_item['label']); ?></a>
<?php endforeach; ?>
    </div>
    <div class="lh4-subnav-right">
      <a class="lh4-subnav-phone" href="<?php echo lireate_lh4_subnav_h(LIREATE_LH4_SUBNAV_PHONE_TEL); ?>" data-lx-ym-goal="klik_po_nomeru_telefona"><?php echo lireate_lh4_subnav_h(LIREATE_LH4_SUBNAV_PHONE_DISPLAY); ?></a>
      <a class="lh4-subnav-cta btn primary" href="<?php echo lireate_lh4_subnav_h($lh4n_cta); ?>">Оставить заявку</a>
    </div>
  </div>
</nav>
<?php
if (!empty($lh4n_items)) {
    lireate_lh4_render_mobile_anchors($lh4n_items);
}
?>
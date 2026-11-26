<?php
/**
 * Мобильная полоса «Разделы» + сетка якорей (≤1150px). PHP 5.6.
 *
 * @param array $lh4n_mobile_items список array('href','label')
 */
if (!isset($lh4n_mobile_items) || !is_array($lh4n_mobile_items) || count($lh4n_mobile_items) === 0) {
    return;
}
?>
<div class="ln3-anchor-mobile-bar" id="ln3AnchorBar">
  <button type="button" class="ln3-anchor-toggle" id="anchorToggle" aria-expanded="false" aria-controls="anchorMobile">
    Разделы
  </button>
  <a class="ln3-anchor-mobile-phone" href="<?php echo lireate_lh4_subnav_h(LIREATE_LH4_SUBNAV_PHONE_TEL); ?>" data-lx-ym-goal="klik_po_nomeru_telefona"><?php echo lireate_lh4_subnav_h(LIREATE_LH4_SUBNAV_PHONE_DISPLAY); ?></a>
</div>
<nav class="anchor-mobile ln3-anchor-mobile" id="anchorMobile" aria-label="Якоря текущей страницы" hidden>
  <div class="ln3-anchor-mobile-grid">
<?php foreach ($lh4n_mobile_items as $lh4n_mobile_item) : ?>
    <a href="<?php echo lireate_lh4_subnav_h($lh4n_mobile_item['href']); ?>"><?php echo lireate_lh4_subnav_h($lh4n_mobile_item['label']); ?></a>
<?php endforeach; ?>
  </div>
</nav>
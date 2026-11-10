<?php
/**
 * Блок доверия: выводится только при непустом $lx_trust_verified (массив пунктов с ключами title, text).
 * Опционально text_html — доверенная разметка из index.php (ссылки), без произвольного ввода пользователя.
 * Не заполнять вымышленными отзывами, логотипами и счётчиками.
 */
if (empty($lx_trust_verified) || !is_array($lx_trust_verified)) {
    return;
}
?>
<section class="lx-section lx-trust lx-reveal" id="trust" aria-label="Проверенные факты">
  <span class="lx-section-label">LIREATE</span>
  <h2>Опыт и факты</h2>
  <ul class="lx-trust-list">
<?php foreach ($lx_trust_verified as $item) :
    if (empty($item['title'])) {
        continue;
    }
    if (empty($item['text']) && empty($item['text_html'])) {
        continue;
    }
?>
    <li><strong><?php echo htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8'); ?></strong> — <span><?php
    if (!empty($item['text_html'])) {
        echo $item['text_html'];
    } else {
        echo htmlspecialchars($item['text'], ENT_QUOTES, 'UTF-8');
    }
?></span></li>
<?php endforeach; ?>
  </ul>
</section>

<?php
/**
 * Один опубликованный отзыв (видео) с сайта /reviews/ — без выдуманных цитат.
 */
$lx_reviews_path = __DIR__ . '/../assets/lireate-shared/reviews.json';
if (!is_file($lx_reviews_path)) {
    return;
}
$lx_reviews_raw = file_get_contents($lx_reviews_path);
if ($lx_reviews_raw === false) {
    return;
}
$lx_reviews_data = json_decode($lx_reviews_raw, true);
if (!is_array($lx_reviews_data) || empty($lx_reviews_data['items'])) {
    return;
}
$lx_highlight = null;
foreach ($lx_reviews_data['items'] as $lx_item) {
    if (!is_array($lx_item) || (isset($lx_item['type']) ? $lx_item['type'] : '') !== 'video') {
        continue;
    }
    $lx_tag = isset($lx_item['service_tag']) ? $lx_item['service_tag'] : '';
    if ($lx_tag === 'Товарный знак') {
        $lx_highlight = $lx_item;
        break;
    }
}
if ($lx_highlight === null) {
    return;
}
$lx_h_title = isset($lx_highlight['case_line']) ? $lx_highlight['case_line'] : (isset($lx_highlight['author']) ? $lx_highlight['author'] : 'Отзыв клиента');
$lx_h_poster = isset($lx_highlight['poster_url']) ? $lx_highlight['poster_url'] : '';
$lx_h_watch = isset($lx_highlight['watch_url']) ? $lx_highlight['watch_url'] : '';
$lx_h_yt = isset($lx_highlight['youtube_id']) ? $lx_highlight['youtube_id'] : '';
?>
<section class="lx-section lx-reveal" id="review-highlight" aria-labelledby="review-highlight-title">
  <span class="lx-section-label">Опыт</span>
  <h2 id="review-highlight-title">Отзыв о работе с товарным знаком</h2>
  <article class="lx-review-highlight-card">
    <?php if ($lx_h_poster !== '') : ?>
    <a class="lx-review-highlight-media" href="<?php echo htmlspecialchars($lx_h_watch !== '' ? $lx_h_watch : '#', ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer">
      <img src="<?php echo htmlspecialchars($lx_h_poster, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($lx_h_title, ENT_QUOTES, 'UTF-8'); ?>" loading="lazy" decoding="async" width="320" height="180">
      <span class="lx-review-play" aria-hidden="true">▶</span>
    </a>
    <?php endif; ?>
    <div>
      <h3><?php echo htmlspecialchars($lx_h_title, ENT_QUOTES, 'UTF-8'); ?></h3>
      <?php if ($lx_h_watch !== '') : ?>
      <p><a href="<?php echo htmlspecialchars($lx_h_watch, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer">Смотреть видео на YouTube</a><?php if ($lx_h_yt !== '') : ?> (id <?php echo htmlspecialchars($lx_h_yt, ENT_QUOTES, 'UTF-8'); ?>)<?php endif; ?></p>
      <?php endif; ?>
      <p class="lx-muted">Видео размещено на <a href="/reviews/">странице отзывов LIREATE</a>.</p>
    </div>
  </article>
</section>

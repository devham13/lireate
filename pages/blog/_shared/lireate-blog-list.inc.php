<?php
/**
 * Разметка списка статей блога (/news).
 *
 * $lireate_blog_list_items — массив нормализованных карточек
 * $lireate_blog_list_featured — опционально, первая/главная статья
 * $lireate_blog_list_filters — рубрики (label, url, num?, category?)
 * $lireate_blog_list_pagination — опционально (current, total, pages[])
 */
if (!function_exists('lireate_blog_h')) {
    require_once __DIR__ . '/lireate-blog-load.php';
    lireate_blog_require_lib();
}

$lb_items = isset($lireate_blog_list_items) && is_array($lireate_blog_list_items)
    ? $lireate_blog_list_items
    : lireate_blog_list_items_from_index();
$lb_featured = isset($lireate_blog_list_featured) && is_array($lireate_blog_list_featured)
    ? $lireate_blog_list_featured
    : lireate_blog_list_featured_from_cms($lb_items);
$lb_filters = isset($lireate_blog_list_filters) && is_array($lireate_blog_list_filters)
    ? $lireate_blog_list_filters
    : lireate_blog_list_filters_from_cms();
$lb_pagination = isset($lireate_blog_list_pagination)
    ? $lireate_blog_list_pagination
    : lireate_blog_list_pagination_from_cms();

$lb_title = defined('LIREATE_BLOG_LIST_TITLE') ? LIREATE_BLOG_LIST_TITLE : 'Блог LIREATE';
$lb_lead = defined('LIREATE_BLOG_LIST_LEAD') ? LIREATE_BLOG_LIST_LEAD : '';
$lb_assets = LIREATE_BLOG_ASSETS_BASE;
$lb_v = LIREATE_BLOG_TEMPLATE_VERSION;
$lb_max = LIREATE_BLOG_MAX_CHANNEL_URL;
$lb_avatar = defined('LIREATE_BLOG_AUTHOR_AVATAR_PATH') ? LIREATE_BLOG_AUTHOR_AVATAR_PATH : '';
$lb_author_default = defined('LIREATE_BLOG_AUTHOR_NAME') ? LIREATE_BLOG_AUTHOR_NAME : LIREATE_BLOG_DEFAULT_AUTHOR;
?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Marmelad&family=Rubik:wght@400;600;700&family=Unbounded:wght@700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?php echo lireate_blog_h($lb_assets); ?>/lireate-blog-list.css?v=<?php echo lireate_blog_h($lb_v); ?>">
<link rel="stylesheet" href="<?php echo lireate_blog_h($lb_assets); ?>/lireate-blog-footer.css?v=<?php echo lireate_blog_h($lb_v); ?>">
<script>
document.documentElement.classList.add('lireate-blog-html');
if (document.body) { document.body.classList.add('lireate-blog-body'); }
</script>
<div class="lireate-blog-list-page" data-lireate-blog-list-version="<?php echo lireate_blog_h($lb_v); ?>">
  <?php
  $lb_total_items = isset($GLOBALS['lireate_blog_list_total_items']) ? (int) $GLOBALS['lireate_blog_list_total_items'] : count($lb_items);
  $lb_total_pages = isset($GLOBALS['lireate_blog_list_total_pages']) ? (int) $GLOBALS['lireate_blog_list_total_pages'] : 1;
  if (is_array($lb_pagination) && !empty($lb_pagination['total'])) {
      $lb_total_pages = (int) $lb_pagination['total'];
  }
  echo '<!-- lireate-blog-list-diag-total-items:' . (int) $lb_total_items . ' -->';
  echo '<!-- lireate-blog-list-diag-total-pages:' . (int) $lb_total_pages . ' -->';
  $lb_cover_cards = array();
  if (!empty($lb_featured) && is_array($lb_featured)) {
      $lb_cover_cards[] = $lb_featured;
  }
  foreach ($lb_items as $card) {
      if (!is_array($card)) {
          continue;
      }
      if (!empty($lb_featured) && is_array($lb_featured) && isset($card['url'], $lb_featured['url']) && $card['url'] === $lb_featured['url']) {
          continue;
      }
      $lb_cover_cards[] = $card;
  }
  $lb_covers_ok = 0;
  $lb_covers_missing = 0;
  foreach ($lb_cover_cards as $card) {
      $cover = isset($card['cover']) ? trim((string) $card['cover']) : '';
      if ($cover !== '') {
          $lb_covers_ok += 1;
      } else {
          $lb_covers_missing += 1;
      }
  }
  echo '<!-- lireate-blog-list-diag-covers-ok:' . (int) $lb_covers_ok . ' -->';
  echo '<!-- lireate-blog-list-diag-covers-missing:' . (int) $lb_covers_missing . ' -->';
  ?>
  <header class="lireate-blog-list-hero">
    <div class="lireate-blog-list-wrap">
      <p class="lireate-blog-list-kicker">Блог LIREATE</p>
      <h1 class="lireate-blog-list-h1">
        <span class="lireate-blog-list-h1-main"><?php echo lireate_blog_h($lb_title); ?></span>
        <?php if ($lb_lead !== '') : ?>
        <span class="lireate-blog-list-h1-sub"><?php echo lireate_blog_h($lb_lead); ?></span>
        <?php endif; ?>
      </h1>
      <div class="lireate-blog-list-hero-actions">
        <a class="lireate-blog-list-pill lireate-blog-list-pill--solid" href="<?php echo lireate_blog_h($lb_max); ?>" target="_blank" rel="noopener noreferrer">Канал в Max</a>
        <a class="lireate-blog-list-pill lireate-blog-list-pill--outline" href="/news/">Все материалы</a>
      </div>
    </div>
  </header>

  <?php if (!empty($lb_filters)) : ?>
  <nav class="lireate-blog-list-filters" aria-label="Рубрики блога">
    <div class="lireate-blog-list-wrap lireate-blog-list-filters-scroll">
      <?php foreach ($lb_filters as $rubric) :
          if (!is_array($rubric) || empty($rubric['label']) || empty($rubric['url'])) {
              continue;
          }
          $active = lireate_blog_list_rubric_is_active($rubric);
          ?>
      <a class="lireate-blog-list-filter<?php echo $active ? ' is-active' : ''; ?>" href="<?php echo lireate_blog_h($rubric['url']); ?>"><?php echo lireate_blog_h($rubric['label']); ?></a>
      <?php endforeach; ?>
    </div>
  </nav>
  <?php endif; ?>

  <?php if (!empty($lb_featured)) :
      $fcover = isset($lb_featured['cover']) ? $lb_featured['cover'] : '';
      $fcover_style = $fcover !== '' ? 'background-image:url(\'' . lireate_blog_h($fcover) . '\')' : '';
      ?>
  <section class="lireate-blog-list-featured" aria-label="Свежая публикация">
    <div class="lireate-blog-list-wrap lireate-blog-list-featured-grid">
      <a class="lireate-blog-list-featured-cover<?php echo $fcover === '' ? ' lireate-blog-list-featured-cover--fallback' : ''; ?>" href="<?php echo lireate_blog_h($lb_featured['url']); ?>"<?php echo $fcover_style !== '' ? ' style="' . $fcover_style . '"' : ''; ?>>
        <span class="lireate-blog-list-featured-label">Свежий материал</span>
      </a>
      <div class="lireate-blog-list-featured-body">
        <?php if (!empty($lb_featured['tag'])) : ?>
        <p class="lireate-blog-list-card-rubric"><a href="<?php echo lireate_blog_h($lb_featured['tag_url']); ?>"><?php echo lireate_blog_h($lb_featured['tag']); ?></a></p>
        <?php endif; ?>
        <?php if (!empty($lb_featured['date_display'])) : ?>
        <p class="lireate-blog-list-card-date"><time datetime="<?php echo lireate_blog_h($lb_featured['date_iso']); ?>"><?php echo lireate_blog_h($lb_featured['date_display']); ?></time></p>
        <?php endif; ?>
        <h2 class="lireate-blog-list-featured-title"><a href="<?php echo lireate_blog_h($lb_featured['url']); ?>"><?php echo lireate_blog_h($lb_featured['title']); ?></a></h2>
        <?php if (!empty($lb_featured['excerpt'])) : ?>
        <p class="lireate-blog-list-featured-excerpt"><?php echo lireate_blog_h($lb_featured['excerpt']); ?></p>
        <?php endif; ?>
        <div class="lireate-blog-list-card-meta-row">
          <span class="lireate-blog-list-read-pill"><?php echo (int) $lb_featured['read_mins']; ?> мин читать</span>
          <span class="lireate-blog-list-author-inline">
            <?php if ($lb_avatar !== '') : ?>
            <img class="lireate-blog-list-author-avatar" src="<?php echo lireate_blog_h($lb_avatar); ?>.webp" width="28" height="28" alt="" loading="lazy" decoding="async">
            <?php endif; ?>
            <?php echo lireate_blog_h(isset($lb_featured['author']) ? $lb_featured['author'] : $lb_author_default); ?>
          </span>
        </div>
        <a class="lireate-blog-list-card-more" href="<?php echo lireate_blog_h($lb_featured['url']); ?>">Читать</a>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <section class="lireate-blog-list-canvas" aria-label="Статьи блога">
    <div class="lireate-blog-list-wrap">
      <div class="lireate-blog-list-grid">
        <?php foreach ($lb_items as $card) :
            if (!is_array($card) || empty($card['url']) || empty($card['title'])) {
                continue;
            }
            if (!empty($lb_featured) && $card['url'] === $lb_featured['url']) {
                continue;
            }
            $cover = isset($card['cover']) ? $card['cover'] : '';
            $cover_style = $cover !== '' ? 'background-image:url(\'' . lireate_blog_h($cover) . '\')' : '';
            ?>
        <article class="lireate-blog-list-card">
          <a class="lireate-blog-list-card-cover<?php echo $cover === '' ? ' lireate-blog-list-card-cover--fallback' : ''; ?>" href="<?php echo lireate_blog_h($card['url']); ?>"<?php echo $cover_style !== '' ? ' style="' . $cover_style . '"' : ''; ?>>
            <?php if (!empty($card['tag'])) : ?>
            <span class="lireate-blog-list-card-tag"><?php echo lireate_blog_h($card['tag']); ?></span>
            <?php endif; ?>
          </a>
          <div class="lireate-blog-list-card-body">
            <div class="lireate-blog-list-card-top-meta">
              <?php if (!empty($card['date_display'])) : ?>
              <p class="lireate-blog-list-card-date"><time datetime="<?php echo lireate_blog_h($card['date_iso']); ?>"><?php echo lireate_blog_h($card['date_display']); ?></time></p>
              <?php endif; ?>
              <span class="lireate-blog-list-read-pill"><?php echo (int) $card['read_mins']; ?> мин</span>
            </div>
            <h2 class="lireate-blog-list-card-title"><a href="<?php echo lireate_blog_h($card['url']); ?>"><?php echo lireate_blog_h($card['title']); ?></a></h2>
            <?php if (!empty($card['excerpt'])) : ?>
            <p class="lireate-blog-list-card-excerpt"><?php echo lireate_blog_h($card['excerpt']); ?></p>
            <?php endif; ?>
            <div class="lireate-blog-list-card-footer">
              <span class="lireate-blog-list-author-inline">
                <?php if ($lb_avatar !== '') : ?>
                <img class="lireate-blog-list-author-avatar" src="<?php echo lireate_blog_h($lb_avatar); ?>.webp" width="24" height="24" alt="" loading="lazy" decoding="async">
                <?php endif; ?>
                <?php echo lireate_blog_h(isset($card['author']) ? $card['author'] : $lb_author_default); ?>
              </span>
              <a class="lireate-blog-list-card-more" href="<?php echo lireate_blog_h($card['url']); ?>">Читать</a>
            </div>
          </div>
        </article>
        <?php endforeach; ?>
      </div>

      <?php if (is_array($lb_pagination) && !empty($lb_pagination['items'])) :
          $lb_prev = isset($lb_pagination['prev_url']) ? $lb_pagination['prev_url'] : null;
          $lb_next = isset($lb_pagination['next_url']) ? $lb_pagination['next_url'] : null;
          ?>
      <nav class="lireate-blog-list-pagination" aria-label="Страницы блога">
        <div class="lireate-blog-list-pagination-inner">
          <?php if ($lb_prev !== null && $lb_prev !== '') : ?>
          <a class="lireate-blog-list-page-nav lireate-blog-list-page-nav--prev" href="<?php echo lireate_blog_h($lb_prev); ?>">Назад</a>
          <?php else : ?>
          <span class="lireate-blog-list-page-nav lireate-blog-list-page-nav--prev is-disabled" aria-hidden="true">Назад</span>
          <?php endif; ?>
          <?php foreach ($lb_pagination['items'] as $page_item) :
              if (!is_array($page_item)) {
                  continue;
              }
              $item_type = isset($page_item['type']) ? $page_item['type'] : 'page';
              if ($item_type === 'ellipsis') : ?>
          <span class="lireate-blog-list-page-ellipsis" aria-hidden="true">…</span>
              <?php continue;
              endif;
              if (empty($page_item['url'])) {
                  continue;
              }
              $is_current = !empty($page_item['current']);
              ?>
          <a class="lireate-blog-list-page-link<?php echo $is_current ? ' is-current' : ''; ?>" href="<?php echo lireate_blog_h($page_item['url']); ?>"<?php echo $is_current ? ' aria-current="page"' : ''; ?>><?php echo lireate_blog_h(isset($page_item['num']) ? $page_item['num'] : ''); ?></a>
          <?php endforeach; ?>
          <?php if ($lb_next !== null && $lb_next !== '') : ?>
          <a class="lireate-blog-list-page-nav lireate-blog-list-page-nav--next" href="<?php echo lireate_blog_h($lb_next); ?>">Далее</a>
          <?php else : ?>
          <span class="lireate-blog-list-page-nav lireate-blog-list-page-nav--next is-disabled" aria-hidden="true">Далее</span>
          <?php endif; ?>
        </div>
      </nav>
      <?php endif; ?>
    </div>
  </section>

  <section class="lireate-blog-list-subscribe" aria-label="Подписка в Max">
    <div class="lireate-blog-list-wrap">
      <div class="lireate-blog-subscribe-card lireate-blog-list-subscribe-card">
        <p class="lireate-blog-subscribe-label">MAX</p>
        <p class="lireate-blog-subscribe-text">Разборы кейсов по товарным знакам и ИС в канале LIREATE в Max</p>
        <a class="lireate-blog-subscribe-btn" href="<?php echo lireate_blog_h($lb_max); ?>" target="_blank" rel="noopener noreferrer">
          <span class="lireate-blog-subscribe-btn-icon" aria-hidden="true"></span>
          Подписаться
        </a>
      </div>
    </div>
  </section>
</div>

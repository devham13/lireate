<?php
/**
 * Разметка статьи блога (двухколоночный layout).
 *
 * Ожидаемые переменные:
 * $lireate_blog_title (h1)
 * $lireate_blog_body_html (full_story / article fragment)
 * $lireate_blog_date_display — «19.10.2026»
 * $lireate_blog_date_iso — опционально
 * $lireate_blog_author — опционально
 * $lireate_blog_tag, $lireate_blog_tag_url — опционально
 * $lireate_blog_current_url — путь /slug/ для related
 * $lireate_blog_description — для JSON-LD
 * $lireate_blog_canonical — полный URL
 * $lireate_blog_cover_html — опционально, обложка (первое img из /template/images/blog/)
 */
require_once __DIR__ . '/lireate-blog-load.php';
lireate_blog_require_lib();

$lb_title = isset($lireate_blog_title) ? $lireate_blog_title : '';
$lb_body = isset($lireate_blog_body_html) ? $lireate_blog_body_html : '';
$lb_date_display = isset($lireate_blog_date_display) ? $lireate_blog_date_display : '';
$lb_date_iso = isset($lireate_blog_date_iso) ? $lireate_blog_date_iso : lireate_blog_date_display_to_iso($lb_date_display);
$lb_author = isset($lireate_blog_author) && $lireate_blog_author !== ''
    ? $lireate_blog_author
    : (defined('LIREATE_BLOG_AUTHOR_NAME') ? LIREATE_BLOG_AUTHOR_NAME : LIREATE_BLOG_DEFAULT_AUTHOR);
$lb_author_role = defined('LIREATE_BLOG_AUTHOR_ROLE') ? LIREATE_BLOG_AUTHOR_ROLE : '';
$lb_avatar_path = defined('LIREATE_BLOG_AUTHOR_AVATAR_PATH') ? LIREATE_BLOG_AUTHOR_AVATAR_PATH : '';
$lb_tag = isset($lireate_blog_tag) ? $lireate_blog_tag : '';
$lb_tag_url = isset($lireate_blog_tag_url) ? $lireate_blog_tag_url : '/news/';
$lb_current = isset($lireate_blog_current_url) ? $lireate_blog_current_url : '';
$lb_desc = isset($lireate_blog_description) ? $lireate_blog_description : '';
$lb_canonical = isset($lireate_blog_canonical) ? $lireate_blog_canonical : '';
$lb_cover_html = isset($lireate_blog_cover_html) ? $lireate_blog_cover_html : '';
$lb_collage_mode = !empty($lireate_blog_collage_mode);
$lb_collage_hero = isset($lireate_blog_collage_hero_html) ? $lireate_blog_collage_hero_html : '';

$lb_body = lireate_blog_prepare_body_html($lb_body);
$lb_read_mins = lireate_blog_reading_time_minutes($lb_body);
$lb_toc = lireate_blog_extract_toc($lb_body);
$lb_related = lireate_blog_related_articles($lb_current, 3);
$lb_assets = LIREATE_BLOG_ASSETS_BASE;
$lb_v = LIREATE_BLOG_TEMPLATE_VERSION;
$lb_max = LIREATE_BLOG_MAX_CHANNEL_URL;

$lb_jsonld = lireate_blog_article_jsonld(
    array(
        'title' => $lb_title,
        'description' => $lb_desc,
        'author' => $lb_author,
        'author_role' => $lb_author_role,
        'date_display' => $lb_date_display,
        'date_iso' => $lb_date_iso,
        'canonical' => $lb_canonical,
    )
);
$lb_jsonld = array_filter(
    $lb_jsonld,
    function ($v) {
        return $v !== null && $v !== '';
    }
);
?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Unbounded:wght@700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?php echo lireate_blog_h($lb_assets); ?>/lireate-blog-article.css?v=<?php echo lireate_blog_h($lb_v); ?>">
<link rel="stylesheet" href="<?php echo lireate_blog_h($lb_assets); ?>/lireate-blog-footer.css?v=<?php echo lireate_blog_h($lb_v); ?>">
<div class="lireate-blog-page" data-lireate-blog-version="<?php echo lireate_blog_h($lb_v); ?>">
  <div class="lireate-blog-grid">
    <?php if (!empty($lb_toc)) : ?>
    <details class="lireate-blog-toc-mobile">
      <summary class="lireate-blog-toc-mobile-summary">
        <span class="lireate-blog-toc-icon" aria-hidden="true"></span>
        Содержание
      </summary>
      <ol class="lireate-blog-toc-list">
        <?php foreach ($lb_toc as $item) : ?>
        <li><a href="#<?php echo lireate_blog_h($item['id']); ?>"><?php echo lireate_blog_h($item['text']); ?></a></li>
        <?php endforeach; ?>
      </ol>
    </details>
    <?php endif; ?>

    <article class="lireate-blog-main">
      <header class="lireate-blog-header<?php echo $lb_collage_mode ? ' lireate-blog-header--collage-v1' : ''; ?>">
        <div class="lireate-blog-header-top<?php echo $lb_collage_mode ? ' lireate-blog-header-top--collage' : ''; ?>">
          <a class="lireate-blog-pill lireate-blog-pill--back" href="/news/">‹ Все статьи блога</a>
          <?php if (!$lb_collage_mode) : ?>
          <div class="lireate-blog-header-top-right">
            <?php if ($lb_tag !== '') : ?>
            <a class="lireate-blog-pill lireate-blog-pill--topic" href="<?php echo lireate_blog_h($lb_tag_url); ?>"><?php echo lireate_blog_h($lb_tag); ?></a>
            <?php endif; ?>
            <span class="lireate-blog-pill lireate-blog-pill--read">
              <span class="lireate-blog-pill-clock" aria-hidden="true"></span>
              <?php echo (int) $lb_read_mins; ?> мин читать
            </span>
          </div>
          <?php endif; ?>
        </div>
        <?php if ($lb_collage_mode && $lb_collage_hero !== '') : ?>
        <div class="lireate-blog-collage-hero-wrap" data-lireate-collage-hero="1">
          <?php echo $lb_collage_hero; ?>
        </div>
        <?php endif; ?>
        <h1 class="lireate-blog-h1"><?php echo lireate_blog_h($lb_title); ?></h1>
        <?php if ($lb_collage_mode) : ?>
        <div class="lireate-blog-collage-meta">
          <div class="lireate-blog-collage-meta-pills">
            <?php if ($lb_tag !== '') : ?>
            <a class="lireate-blog-pill lireate-blog-pill--topic" href="<?php echo lireate_blog_h($lb_tag_url); ?>"><?php echo lireate_blog_h($lb_tag); ?></a>
            <?php endif; ?>
            <?php if ($lb_date_display !== '') : ?>
            <span class="lireate-blog-pill lireate-blog-pill--date">
              <time datetime="<?php echo lireate_blog_h($lb_date_iso); ?>"><?php echo lireate_blog_h($lb_date_display); ?></time>
            </span>
            <?php endif; ?>
            <span class="lireate-blog-pill lireate-blog-pill--read">
              <span class="lireate-blog-pill-clock" aria-hidden="true"></span>
              <?php echo (int) $lb_read_mins; ?> мин читать
            </span>
          </div>
        </div>
        <?php endif; ?>
        <hr class="lireate-blog-header-rule" />
        <div class="lireate-blog-author-row">
          <div class="lireate-blog-author-block">
            <?php if ($lb_avatar_path !== '') :
                $lb_avatar_base = '/' . trim($lb_avatar_path, '/');
                $lb_avatar_webp = lireate_blog_h($lb_avatar_base . '.webp');
                $lb_avatar_jpg = lireate_blog_h($lb_avatar_base . '.jpg');
                ?>
            <picture class="lireate-blog-author-avatar">
              <source srcset="<?php echo $lb_avatar_webp; ?>" type="image/webp">
              <img src="<?php echo $lb_avatar_jpg; ?>" width="40" height="40" alt="<?php echo lireate_blog_h($lb_author); ?>" loading="lazy" decoding="async" class="lireate-blog-author-avatar-img">
            </picture>
            <?php endif; ?>
            <div class="lireate-blog-author-text">
              <p class="lireate-blog-author-name"><?php echo lireate_blog_h($lb_author); ?></p>
              <?php if ($lb_author_role !== '') : ?>
              <p class="lireate-blog-author-role"><?php echo lireate_blog_h($lb_author_role); ?></p>
              <?php endif; ?>
            </div>
          </div>
          <?php if (!$lb_collage_mode && $lb_date_display !== '') : ?>
          <p class="lireate-blog-published">
            Опубликовано:
            <time datetime="<?php echo lireate_blog_h($lb_date_iso); ?>"><?php echo lireate_blog_h($lb_date_display); ?></time>
          </p>
          <?php endif; ?>
        </div>
      </header>
      <?php if (!$lb_collage_mode && $lb_cover_html !== '') : ?>
      <div class="lireate-blog-cover" aria-hidden="false"><?php echo $lb_cover_html; ?></div>
      <?php endif; ?>
      <div class="lireate-blog-prose" id="lireate-blog-prose">
        <?php echo $lb_body; ?>
      </div>
    </article>

    <aside class="lireate-blog-aside" aria-label="Боковая колонка">
      <?php if (!empty($lb_toc)) : ?>
      <nav class="lireate-blog-toc-card" aria-label="Содержание">
        <div class="lireate-blog-toc-card-head">
          <span class="lireate-blog-toc-icon" aria-hidden="true"></span>
          <span class="lireate-blog-toc-title">Содержание</span>
        </div>
        <ol class="lireate-blog-toc-list" data-lx-blog-toc>
          <?php foreach ($lb_toc as $item) : ?>
          <li><a href="#<?php echo lireate_blog_h($item['id']); ?>" data-lx-toc-href="#<?php echo lireate_blog_h($item['id']); ?>"><?php echo lireate_blog_h($item['text']); ?></a></li>
          <?php endforeach; ?>
        </ol>
      </nav>
      <?php endif; ?>
      <div class="lireate-blog-subscribe-card lireate-blog-subscribe-card--aside">
        <p class="lireate-blog-subscribe-label">MAX</p>
        <p class="lireate-blog-subscribe-text">Разборы кейсов по товарным знакам и ИС в канале LIREATE в Max</p>
        <a class="lireate-blog-subscribe-btn" href="<?php echo lireate_blog_h($lb_max); ?>" target="_blank" rel="noopener noreferrer">
          <span class="lireate-blog-subscribe-btn-icon" aria-hidden="true"></span>
          Подписаться
        </a>
      </div>
    </aside>

    <div class="lireate-blog-subscribe-card lireate-blog-subscribe-card--mobile">
      <p class="lireate-blog-subscribe-label">MAX</p>
      <p class="lireate-blog-subscribe-text">Разборы кейсов по товарным знакам и ИС в канале LIREATE в Max</p>
      <a class="lireate-blog-subscribe-btn" href="<?php echo lireate_blog_h($lb_max); ?>" target="_blank" rel="noopener noreferrer">
        <span class="lireate-blog-subscribe-btn-icon" aria-hidden="true"></span>
        Подписаться
      </a>
    </div>
  </div>

  <?php if (!empty($lb_related)) : ?>
  <section class="lireate-blog-related" aria-labelledby="lireate-blog-related-heading">
    <div class="lireate-blog-related-head">
      <h2 id="lireate-blog-related-heading" class="lireate-blog-related-title">Читайте также</h2>
      <a class="lireate-blog-related-all" href="/news/">Все статьи блога →</a>
    </div>
    <div class="lireate-blog-related-grid">
      <?php foreach ($lb_related as $card) :
          $curl = isset($card['url']) ? $card['url'] : '#';
          $ctitle = isset($card['title']) ? $card['title'] : '';
          $cdate = isset($card['date_display']) ? $card['date_display'] : '';
          $ctag = isset($card['tag']) ? $card['tag'] : '';
          $ccover_raw = isset($card['cover']) ? $card['cover'] : '';
          $ccover = lireate_blog_resolve_cover_url($ccover_raw);
          $cexcerpt = isset($card['excerpt']) ? $card['excerpt'] : '';
          $cover_fallback = ($ccover === '');
          $cover_style = $cover_fallback ? '' : 'background-image:url(\'' . lireate_blog_h($ccover) . '\')';
          ?>
      <article class="lireate-blog-card">
        <a class="lireate-blog-card-cover<?php echo $cover_fallback ? ' lireate-blog-card-cover--fallback' : ''; ?>" href="<?php echo lireate_blog_h($curl); ?>"<?php echo $cover_style !== '' ? ' style="' . $cover_style . '"' : ''; ?>>
          <?php if ($cover_fallback) : ?><span class="lireate-blog-card-cover-brand" aria-hidden="true">LIREATE</span><?php endif; ?>
          <?php if ($ctag !== '') : ?><span class="lireate-blog-card-tag"><?php echo lireate_blog_h($ctag); ?></span><?php endif; ?>
        </a>
        <div class="lireate-blog-card-body">
          <?php if ($cdate !== '') : ?><p class="lireate-blog-card-date"><?php echo lireate_blog_h($cdate); ?></p><?php endif; ?>
          <h3 class="lireate-blog-card-title"><a href="<?php echo lireate_blog_h($curl); ?>"><?php echo lireate_blog_h($ctitle); ?></a></h3>
          <?php if ($cexcerpt !== '') : ?><p class="lireate-blog-card-excerpt"><?php echo lireate_blog_h($cexcerpt); ?></p><?php endif; ?>
          <a class="lireate-blog-card-btn" href="<?php echo lireate_blog_h($curl); ?>">Читать</a>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>
</div>
<script src="<?php echo lireate_blog_h($lb_assets); ?>/lireate-blog-article.js?v=<?php echo lireate_blog_h($lb_v); ?>" defer></script>
<script type="application/ld+json"><?php echo json_encode($lb_jsonld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?></script>

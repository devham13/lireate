<?php
/**
 * Лента отзывов (горизонтальный скролл + lightbox).
 *
 * $lx_review_filter_services — slug услуг для фильтра items (опционально).
 * $lx_reviews_intro — короткая строка над лентой (опционально).
 * $lx_reviews_demo_only — true только в _template: одна демо-карточка без JSON.
 */
$lx_site_base = '';
if (isset($site_root) && is_string($site_root) && $site_root !== '') {
    $lx_site_base = rtrim($site_root, '/');
} elseif (!empty($_SERVER['HTTP_HOST'])) {
    $host = preg_replace('/[^a-zA-Z0-9.\-:]/', '', (string) $_SERVER['HTTP_HOST']);
    if ($host !== '') {
        $lx_site_base = 'https://' . $host;
    }
}
if (!function_exists('lx_review_resolve_url')) {
    function lx_review_resolve_url($url, $base)
    {
        if ($url === '' || preg_match('#^https?://#i', $url)) {
            return $url;
        }
        if ($base === '') {
            return $url;
        }
        return $base . (strpos($url, '/') === 0 ? $url : '/' . $url);
    }
}
$lx_reviews_intro = isset($lx_reviews_intro) && is_string($lx_reviews_intro) && $lx_reviews_intro !== ''
    ? $lx_reviews_intro
    : 'Клиенты делятся опытом работы с LIREATE — текст и видео с их разрешения.';
$filter = isset($lx_review_filter_services) && is_array($lx_review_filter_services) ? $lx_review_filter_services : [];
$demo_only = !empty($lx_reviews_demo_only);
$items = [];

if ($demo_only) {
    $items[] = [
        'id' => 'demo-layout',
        'type' => 'demo',
        'author' => '',
        'service_tag' => 'Услуга',
        'text' => 'Здесь будет краткий фрагмент реального отзыва клиента. Текст остаётся в HTML для индексации, полная версия открывается по клику.',
        'text_full' => 'Здесь будет полный текст отзыва клиента после согласования с автором. На боевых страницах подставляются только проверенные цитаты с lireate.ru или сообщества VK — без выдуманных имён и формулировок.',
        'source_url' => '/reviews/',
    ];
} else {
    $reviews_path = dirname(__DIR__) . '/assets/lireate-shared/reviews.json';
    if (!is_file($reviews_path)) {
        return;
    }
    $data = json_decode(file_get_contents($reviews_path), true);
    if (!is_array($data)) {
        return;
    }
    $pool = isset($data['items']) && is_array($data['items']) ? $data['items'] : [];
    foreach ($pool as $item) {
        if (!is_array($item)) {
            continue;
        }
        if ($filter) {
            $svc = isset($item['services']) && is_array($item['services']) ? $item['services'] : [];
            if (!array_intersect($filter, $svc)) {
                continue;
            }
        }
        $items[] = $item;
    }
    if (!empty($data['chat_screenshots']) && is_array($data['chat_screenshots'])) {
        foreach ($data['chat_screenshots'] as $chat) {
            if (!is_array($chat) || empty($chat['image'])) {
                continue;
            }
            if ($filter && !empty($chat['services']) && !array_intersect($filter, $chat['services'])) {
                continue;
            }
            $chat['type'] = 'chat';
            $items[] = $chat;
        }
    }
}

if (!$items) {
    return;
}

$lx_review_accents = ['mint', 'violet', 'sky', 'lime', 'fuchsia', 'cyan'];
$video_ld = [];

if (!function_exists('lx_review_excerpt')) {
    function lx_review_excerpt($text, $max = 160)
    {
        $text = trim(preg_replace('/\s+/u', ' ', (string) $text));
        if ($text === '') {
            return '';
        }
        if (function_exists('mb_strlen') && function_exists('mb_substr')) {
            if (mb_strlen($text, 'UTF-8') <= $max) {
                return $text;
            }
            return rtrim(mb_substr($text, 0, $max - 1, 'UTF-8')) . '…';
        }
        if (strlen($text) <= $max) {
            return $text;
        }
        return rtrim(substr($text, 0, $max - 1)) . '…';
    }
}
?>
<section class="lx-section lx-reviews-section lx-reveal" id="reviews" aria-label="Отзывы клиентов">
  <span class="lx-section-label">Отзывы</span>
  <h2>Отзывы клиентов LIREATE</h2>
  <p class="lx-reviews-intro"><?php echo htmlspecialchars($lx_reviews_intro, ENT_QUOTES, 'UTF-8'); ?></p>
  <div class="lx-reviews-rail" data-lx-reviews-rail tabindex="0" role="region" aria-label="Лента отзывов">
    <button type="button" class="lx-reviews-rail-prev" aria-label="Предыдущие отзывы">‹</button>
    <div class="lx-reviews-rail-viewport">
      <ol class="lx-reviews-rail-track">
<?php
foreach ($items as $idx => $item) :
    $type = isset($item['type']) ? $item['type'] : 'text';
    $author = isset($item['author']) ? $item['author'] : '';
    $tag = isset($item['service_tag']) ? $item['service_tag'] : 'Товарный знак';
    $source = isset($item['source_url']) ? $item['source_url'] : '/reviews/';
    $accent = $lx_review_accents[$idx % count($lx_review_accents)];
    $is_demo = ($type === 'demo');
    $card_id = isset($item['id']) ? $item['id'] : ('review-' . $idx);

    if ($is_demo) :
        $preview = lx_review_excerpt($item['text'], 140);
        $full = $item['text_full'];
?>
        <li class="lx-review-frame lx-review-frame--<?php echo htmlspecialchars($accent, ENT_QUOTES, 'UTF-8'); ?> lx-review-frame--demo">
          <button type="button" class="lx-review-card" data-lx-review-open aria-haspopup="dialog"
            data-review-id="<?php echo htmlspecialchars($card_id, ENT_QUOTES, 'UTF-8'); ?>">
            <span class="lx-review-demo-badge">Пример оформления</span>
            <div class="lx-review-chat-preview" aria-hidden="true">
              <div class="lx-review-bubble lx-review-bubble--them"><?php echo htmlspecialchars($preview, ENT_QUOTES, 'UTF-8'); ?></div>
              <div class="lx-review-bubble lx-review-bubble--us">Спасибо за доверие!</div>
            </div>
            <span class="lx-review-tag-pill"><?php echo htmlspecialchars($tag, ENT_QUOTES, 'UTF-8'); ?></span>
            <span class="lx-review-author">Клиент</span>
          </button>
          <div class="lx-review-full" id="lx-review-full-<?php echo htmlspecialchars($card_id, ENT_QUOTES, 'UTF-8'); ?>" hidden>
            <p class="lx-review-lightbox-text"><?php echo htmlspecialchars($full, ENT_QUOTES, 'UTF-8'); ?></p>
            <p class="lx-review-lightbox-meta"><em>Демо-карточка для вёрстки. На опубликованных страницах не показывается.</em></p>
          </div>
        </li>
<?php
    elseif ($type === 'video' && (!empty($item['youtube_id']) || !empty($item['embed_url']))) :
        $vid = isset($item['youtube_id']) ? $item['youtube_id'] : '';
        $embed = isset($item['embed_url']) ? $item['embed_url'] : ('https://www.youtube.com/embed/' . $vid);
        $watch = isset($item['watch_url']) ? $item['watch_url'] : ($vid ? 'https://www.youtube.com/watch?v=' . $vid : $source);
        $poster = isset($item['poster_url']) ? $item['poster_url'] : '';
        $case = isset($item['case_line']) ? $item['case_line'] : 'Видеоотзыв клиента LIREATE';
        $preview = lx_review_excerpt($case, 120);
        if ($embed && ($vid || strpos($embed, 'vk.') !== false || strpos($embed, 'video_ext') !== false)) {
            $video_ld[] = [
                '@type' => 'VideoObject',
                'name' => 'Видеоотзыв — ' . $author,
                'description' => $case,
                'thumbnailUrl' => $poster ? [lx_review_resolve_url($poster, $lx_site_base)] : [],
                'embedUrl' => $embed,
                'contentUrl' => $watch,
                'publisher' => ['@type' => 'Organization', 'name' => 'LIREATE', 'url' => $lx_site_base !== '' ? $lx_site_base . '/' : '/'],
            ];
        }
?>
        <li class="lx-review-frame lx-review-frame--<?php echo htmlspecialchars($accent, ENT_QUOTES, 'UTF-8'); ?> lx-review-frame--video">
          <button type="button" class="lx-review-card" data-lx-review-open data-lx-review-video="1"
            data-embed="<?php echo htmlspecialchars($embed, ENT_QUOTES, 'UTF-8'); ?>"
            aria-haspopup="dialog" aria-label="Открыть видеоотзыв <?php echo htmlspecialchars($author, ENT_QUOTES, 'UTF-8'); ?>">
            <div class="lx-review-chat-preview">
              <?php if ($poster) : ?>
              <div class="lx-review-video-poster"><img src="<?php echo htmlspecialchars($poster, ENT_QUOTES, 'UTF-8'); ?>" alt="" loading="lazy" width="280" height="360"></div>
              <?php endif; ?>
              <div class="lx-review-bubble lx-review-bubble--them"><?php echo htmlspecialchars($preview, ENT_QUOTES, 'UTF-8'); ?></div>
              <span class="lx-review-video-play" aria-hidden="true">▶</span>
            </div>
            <span class="lx-review-tag-pill"><?php echo htmlspecialchars($tag, ENT_QUOTES, 'UTF-8'); ?></span>
            <span class="lx-review-author"><?php echo htmlspecialchars($author, ENT_QUOTES, 'UTF-8'); ?></span>
          </button>
          <div class="lx-review-full" id="lx-review-full-<?php echo htmlspecialchars($card_id, ENT_QUOTES, 'UTF-8'); ?>" hidden>
            <p class="lx-review-lightbox-text"><?php echo htmlspecialchars($case, ENT_QUOTES, 'UTF-8'); ?></p>
            <p class="lx-review-lightbox-meta"><a href="<?php echo htmlspecialchars($source, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer">Страница отзывов на lireate.ru</a></p>
            <div class="lx-review-lightbox-video" data-lx-lightbox-video data-embed="<?php echo htmlspecialchars($embed, ENT_QUOTES, 'UTF-8'); ?>"></div>
          </div>
        </li>
<?php
    elseif ($type === 'chat' && !empty($item['image'])) :
        $caption = isset($item['text']) ? $item['text'] : '';
        $preview = $caption !== '' ? lx_review_excerpt($caption, 100) : 'Благодарность в переписке';
?>
        <li class="lx-review-frame lx-review-frame--<?php echo htmlspecialchars($accent, ENT_QUOTES, 'UTF-8'); ?> lx-review-frame--chat">
          <button type="button" class="lx-review-card" data-lx-review-open aria-haspopup="dialog">
            <div class="lx-review-chat-preview">
              <div class="lx-review-chat-shot"><img src="<?php echo htmlspecialchars($item['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="" loading="lazy"></div>
              <?php if ($preview) : ?><div class="lx-review-bubble lx-review-bubble--them"><?php echo htmlspecialchars($preview, ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
            </div>
            <span class="lx-review-tag-pill"><?php echo htmlspecialchars($tag, ENT_QUOTES, 'UTF-8'); ?></span>
            <span class="lx-review-author"><?php echo htmlspecialchars($author, ENT_QUOTES, 'UTF-8'); ?></span>
          </button>
          <div class="lx-review-full" id="lx-review-full-<?php echo htmlspecialchars($card_id, ENT_QUOTES, 'UTF-8'); ?>" hidden>
            <figure class="lx-review-lightbox-figure">
              <img src="<?php echo htmlspecialchars($item['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="Скриншот переписки с благодарностью клиента">
            </figure>
            <?php if ($caption !== '') : ?><blockquote class="lx-review-lightbox-text"><?php echo htmlspecialchars($caption, ENT_QUOTES, 'UTF-8'); ?></blockquote><?php endif; ?>
            <p class="lx-review-lightbox-meta"><strong><?php echo htmlspecialchars($author, ENT_QUOTES, 'UTF-8'); ?></strong> · <a href="<?php echo htmlspecialchars($source, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer">источник</a></p>
          </div>
        </li>
<?php
    elseif ($type === 'text' && !empty($item['text'])) :
        $full = $item['text'];
        $preview = lx_review_excerpt($full, 140);
        $case = isset($item['case_line']) ? $item['case_line'] : '';
?>
        <li class="lx-review-frame lx-review-frame--<?php echo htmlspecialchars($accent, ENT_QUOTES, 'UTF-8'); ?> lx-review-frame--text">
          <button type="button" class="lx-review-card" data-lx-review-open aria-haspopup="dialog">
            <div class="lx-review-chat-preview">
              <div class="lx-review-bubble lx-review-bubble--them"><?php echo htmlspecialchars($preview, ENT_QUOTES, 'UTF-8'); ?></div>
            </div>
            <span class="lx-review-tag-pill"><?php echo htmlspecialchars($tag, ENT_QUOTES, 'UTF-8'); ?></span>
            <span class="lx-review-author"><?php echo htmlspecialchars($author, ENT_QUOTES, 'UTF-8'); ?></span>
          </button>
          <div class="lx-review-full" id="lx-review-full-<?php echo htmlspecialchars($card_id, ENT_QUOTES, 'UTF-8'); ?>" hidden>
            <blockquote class="lx-review-lightbox-text"><?php echo htmlspecialchars($full, ENT_QUOTES, 'UTF-8'); ?></blockquote>
            <?php if ($case !== '') : ?><p class="lx-review-lightbox-meta"><?php echo htmlspecialchars($case, ENT_QUOTES, 'UTF-8'); ?></p><?php endif; ?>
            <p class="lx-review-lightbox-meta"><strong><?php echo htmlspecialchars($author, ENT_QUOTES, 'UTF-8'); ?></strong> · <a href="<?php echo htmlspecialchars($source, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer">источник</a></p>
          </div>
        </li>
<?php
    endif;
endforeach;
?>
      </ol>
    </div>
    <button type="button" class="lx-reviews-rail-next" aria-label="Следующие отзывы">›</button>
  </div>
</section>
<div class="lx-review-lightbox" hidden data-lx-review-lightbox>
  <div class="lx-review-lightbox-backdrop" data-lx-review-close tabindex="-1"></div>
  <div class="lx-review-lightbox-panel" role="dialog" aria-modal="true" aria-labelledby="lx-review-lightbox-title">
    <button type="button" class="lx-review-lightbox-close" data-lx-review-close aria-label="Закрыть">×</button>
    <h3 class="lx-review-lightbox-title" id="lx-review-lightbox-title"></h3>
    <div class="lx-review-lightbox-body" data-lx-review-lightbox-body></div>
  </div>
</div>
<?php if ($video_ld) : ?>
<script type="application/ld+json"><?php echo json_encode(['@context' => 'https://schema.org', '@graph' => $video_ld], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?></script>
<?php endif; ?>

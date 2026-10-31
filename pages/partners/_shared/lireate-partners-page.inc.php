<?php
/**
 * Разметка страницы «Партнёры» (/partners/) — тёмный стиль как /news.
 */
require_once __DIR__ . '/lireate-partners-lib.php';

$lp_assets = LIREATE_PARTNERS_ASSETS_BASE;
$lp_v = LIREATE_PARTNERS_TEMPLATE_VERSION;
$lp_intro = lireate_partners_page_intro();
$lp_partners = lireate_partners_list();
$lp_team = lireate_partners_team_members();
$lp_photo_origin = '';
if (isset($GLOBALS['lireate_partners_photo_origin']) && is_string($GLOBALS['lireate_partners_photo_origin'])) {
    $lp_photo_origin = rtrim($GLOBALS['lireate_partners_photo_origin'], '/');
}
if ($lp_photo_origin !== '') {
    foreach ($lp_partners as $i => $partner) {
        if (isset($partner['photo']) && strpos($partner['photo'], 'http') !== 0) {
            $lp_partners[$i]['photo'] = $lp_photo_origin . $partner['photo'];
        }
    }
    foreach ($lp_team as $i => $member) {
        if (isset($member['photo']) && strpos($member['photo'], 'http') !== 0) {
            $lp_team[$i]['photo'] = $lp_photo_origin . $member['photo'];
        }
    }
}
$lp_footer_css = defined('LIREATE_PARTNERS_FOOTER_CSS_HREF')
    ? LIREATE_PARTNERS_FOOTER_CSS_HREF
    : '/template/page/news/lireate-blog-footer.css';
?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Marmelad&family=Rubik:wght@400;600;700&family=Unbounded:wght@700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?php echo lireate_partners_h($lp_assets); ?>/lireate-partners.css?v=<?php echo lireate_partners_h($lp_v); ?>">
<link rel="stylesheet" href="<?php echo lireate_partners_h($lp_footer_css); ?>?v=<?php echo lireate_partners_h($lp_v); ?>">
<script>
document.documentElement.classList.add('lireate-partners-html');
document.documentElement.setAttribute('data-lireate-metrika-id', '<?php echo lireate_partners_h(LIREATE_PARTNERS_METRIKA_ID); ?>');
if (document.body) { document.body.classList.add('lireate-partners-body'); }
</script>
<div class="lireate-partners-page" data-lireate-partners-version="<?php echo lireate_partners_h($lp_v); ?>">
  <header class="lireate-partners-hero">
    <div class="lireate-partners-wrap">
      <p class="lireate-partners-kicker">LIREATE</p>
      <h1 class="lireate-partners-h1">Наши Партнёры</h1>
      <?php if ($lp_intro !== '') : ?>
      <p class="lireate-partners-lead"><?php echo lireate_partners_h($lp_intro); ?></p>
      <?php endif; ?>
    </div>
  </header>

  <section class="lireate-partners-wrap lireate-partners-list" aria-label="Партнёры LIREATE">
    <div class="lireate-partners-grid">
      <?php foreach ($lp_partners as $partner) :
          $photo = isset($partner['photo']) ? $partner['photo'] : '';
      ?>
      <article class="lireate-partners-card">
        <div class="lireate-partners-card-body">
          <div class="lireate-partners-head">
            <?php if ($photo !== '') : ?>
            <div class="lireate-partners-photo-wrap">
              <img class="lireate-partners-photo" src="<?php echo lireate_partners_h($photo); ?>" alt="" loading="lazy" decoding="async">
            </div>
            <?php endif; ?>
            <div class="lireate-partners-head-text">
              <?php if (!empty($partner['name'])) : ?>
              <h2 class="lireate-partners-name"><?php echo lireate_partners_h($partner['name']); ?></h2>
              <?php endif; ?>
              <?php if (!empty($partner['city'])) : ?>
              <p class="lireate-partners-city"><?php echo lireate_partners_h($partner['city']); ?></p>
              <?php endif; ?>
            </div>
          </div>
          <?php if (!empty($partner['fields']) && is_array($partner['fields'])) : ?>
          <dl class="lireate-partners-fields">
            <?php foreach ($partner['fields'] as $field) :
                if (empty($field['items'])) {
                    continue;
                }
            ?>
            <div class="lireate-partners-field">
              <?php if (!empty($field['title'])) : ?>
              <dt><?php echo lireate_partners_h($field['title']); ?></dt>
              <?php endif; ?>
              <dd>
                <?php foreach ($field['items'] as $item) : ?>
                <p><?php echo lireate_partners_h($item); ?></p>
                <?php endforeach; ?>
              </dd>
            </div>
            <?php endforeach; ?>
          </dl>
          <?php endif; ?>
          <?php if (!empty($partner['contacts']) && is_array($partner['contacts'])) : ?>
          <dl class="lireate-partners-contacts">
            <?php foreach ($partner['contacts'] as $contact) : ?>
            <div>
              <?php if (!empty($contact['title'])) : ?>
              <dt><?php echo lireate_partners_h($contact['title']); ?></dt>
              <?php endif; ?>
              <dd>
                <?php
                $text = isset($contact['text']) ? $contact['text'] : '';
                if (!empty($contact['tel'])) {
                    echo '<a href="tel:' . lireate_partners_h(preg_replace('/\s+/', '', $contact['tel'])) . '">' . lireate_partners_h($text !== '' ? $text : $contact['tel']) . '</a>';
                } elseif (!empty($contact['url'])) {
                    $href = $contact['url'];
                    if (strpos($href, 'mailto:') !== 0 && strpos($href, 'http') !== 0 && strpos($href, 'tel:') !== 0) {
                        $href = 'http://' . ltrim($href, '/');
                    }
                    echo '<a href="' . lireate_partners_h($href) . '" target="_blank" rel="noopener noreferrer">' . lireate_partners_h($text !== '' ? $text : $href) . '</a>';
                } else {
                    echo lireate_partners_h($text);
                }
                ?>
              </dd>
            </div>
            <?php endforeach; ?>
          </dl>
          <?php endif; ?>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="lireate-partners-wrap lireate-partners-team" aria-label="Команда LIREATE">
    <div class="lireate-partners-team-grid">
      <?php foreach ($lp_team as $idx => $member) :
          $photo = isset($member['photo']) ? $member['photo'] : '';
      ?>
      <div class="lireate-partners-team-card" role="button" tabindex="0" data-lp-team-index="<?php echo (int) $idx; ?>">
        <span class="lireate-partners-team-photo" style="background-image:url('<?php echo lireate_partners_h($photo); ?>')"></span>
        <span class="lireate-partners-team-name"><?php echo lireate_partners_h($member['name']); ?></span>
        <span class="lireate-partners-team-role"><?php echo lireate_partners_h($member['role']); ?></span>
      </div>
      <?php endforeach; ?>
    </div>
  </section>

  <div class="lireate-partners-modal" id="lp-team-modal" hidden>
    <div class="lireate-partners-modal-backdrop" data-lp-modal-close></div>
    <div class="lireate-partners-modal-panel" role="dialog" aria-modal="true" aria-labelledby="lp-modal-name">
      <button type="button" class="lireate-partners-modal-close" data-lp-modal-close aria-label="Закрыть">&times;</button>
      <div class="lireate-partners-modal-photo-wrap">
        <img class="lireate-partners-modal-photo" id="lp-modal-photo" alt="">
      </div>
      <h3 class="lireate-partners-modal-name" id="lp-modal-name"></h3>
      <p class="lireate-partners-modal-role" id="lp-modal-role"></p>
    </div>
  </div>

  <script type="application/json" id="lp-team-json"><?php
  echo json_encode($lp_team, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);
  ?></script>
  <script src="<?php echo lireate_partners_h($lp_assets); ?>/lireate-partners.js?v=<?php echo lireate_partners_h($lp_v); ?>"></script>
  <script src="<?php echo lireate_partners_h($lp_assets); ?>/lireate-metrika-goals.js?v=<?php echo lireate_partners_h($lp_v); ?>"></script>
</div>

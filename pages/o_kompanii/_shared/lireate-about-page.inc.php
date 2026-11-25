<?php
/**
 * Разметка страницы «О компании» (/o_kompanii) — тёмный стиль как /team.
 */
require_once __DIR__ . '/lireate-about-lib.php';

$la_assets = LIREATE_ABOUT_ASSETS_BASE;
$la_v = LIREATE_ABOUT_TEMPLATE_VERSION;
$la_partners = lireate_about_partners();
$la_history = lireate_about_history_paragraphs();
$la_accent = lireate_about_accent_paragraphs();
$la_footer_css = defined('LIREATE_ABOUT_FOOTER_CSS_HREF')
    ? LIREATE_ABOUT_FOOTER_CSS_HREF
    : '/template/page/news/lireate-blog-footer.css';
?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Marmelad&family=Rubik:wght@400;600;700&family=Unbounded:wght@700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?php echo lireate_about_h($la_assets); ?>/lireate-about.css?v=<?php echo lireate_about_h($la_v); ?>">
<link rel="stylesheet" href="<?php echo lireate_about_h($la_footer_css); ?>?v=<?php echo lireate_about_h($la_v); ?>">
<script>
document.documentElement.classList.add('lireate-about-html');
document.documentElement.setAttribute('data-lireate-metrika-id', '<?php echo lireate_about_h(LIREATE_ABOUT_METRIKA_ID); ?>');
if (document.body) { document.body.classList.add('lireate-about-body'); }
</script>
<div class="lireate-about-page" data-lireate-about-version="<?php echo lireate_about_h($la_v); ?>">
  <header class="lireate-about-hero">
    <div class="lireate-about-wrap">
      <p class="lireate-about-kicker">LIREATE</p>
      <h1 class="lireate-about-h1">О компании</h1>
      <p class="lireate-about-lead">Современная, динамично развивающаяся компания, работающая на рынке услуг по охране интеллектуальной собственности с 2005 года.</p>
      <div class="lireate-about-hero-actions" aria-label="Связаться с LIREATE">
        <a class="lireate-about-pill lireate-about-pill--solid" href="<?php echo lireate_about_h(LIREATE_ABOUT_MAX_CHAT_URL); ?>" target="_blank" rel="noopener noreferrer" data-lx-ym-goal="perehod_v_messendzher">Max</a>
        <a class="lireate-about-pill lireate-about-pill--outline" href="<?php echo lireate_about_h(LIREATE_ABOUT_TELEGRAM_URL); ?>" target="_blank" rel="noopener noreferrer" data-lx-ym-goal="perehod_v_telegramm">Telegram</a>
        <a class="lireate-about-pill lireate-about-pill--outline" href="<?php echo lireate_about_h(LIREATE_ABOUT_WHATSAPP_URL); ?>" target="_blank" rel="noopener noreferrer" data-lx-ym-goal="perehod_v_messendzher">WhatsApp</a>
        <a class="lireate-about-pill lireate-about-pill--outline" href="<?php echo lireate_about_h(LIREATE_ABOUT_PHONE_TEL); ?>" data-lx-ym-goal="klik_po_nomeru_telefona"><?php echo lireate_about_h(LIREATE_ABOUT_PHONE_DISPLAY); ?></a>
        <a class="lireate-about-pill lireate-about-pill--outline" href="<?php echo lireate_about_h(LIREATE_ABOUT_PHONE2_TEL); ?>" data-lx-ym-goal="klik_po_nomeru_telefona"><?php echo lireate_about_h(LIREATE_ABOUT_PHONE2_DISPLAY); ?></a>
      </div>
    </div>
  </header>

  <section class="lireate-about-wrap lireate-about-about" aria-labelledby="la-history-title">
    <article class="lireate-about-card">
      <span class="lireate-about-badge">LIREATE</span>
      <h2 class="lireate-about-card-title" id="la-history-title">История компании</h2>
      <p class="lireate-about-text"><?php echo lireate_about_h($la_history[0]); ?></p>
      <div class="lireate-about-accent">
        <?php foreach ($la_accent as $p) : ?>
        <p><?php echo lireate_about_h($p); ?></p>
        <?php endforeach; ?>
      </div>
      <p class="lireate-about-text"><?php echo lireate_about_h($la_history[1]); ?></p>
      <p class="lireate-about-text lireate-about-text--muted"><?php echo lireate_about_h($la_history[2]); ?></p>
    </article>
  </section>

  <section class="lireate-about-wrap lireate-about-roster" aria-labelledby="la-partners-title">
    <div class="lireate-about-roster-head">
      <span class="lireate-about-badge lireate-about-badge--green">Партнёры</span>
      <h2 class="lireate-about-card-title" id="la-partners-title">Стратегическое управление</h2>
    </div>
    <?php foreach ($la_partners as $partner) : ?>
    <article class="lireate-about-card lireate-about-partner">
      <div class="lireate-about-partner-photo" style="background-image:url('<?php echo lireate_about_h(lireate_about_asset_url($partner['photo'])); ?>')" role="img" aria-label="<?php echo lireate_about_h($partner['name']); ?>"></div>
      <div class="lireate-about-partner-body">
        <h3 class="lireate-about-partner-name"><?php echo lireate_about_h($partner['name']); ?></h3>
        <p class="lireate-about-partner-role"><?php echo lireate_about_h($partner['role']); ?></p>
        <p class="lireate-about-spec-label">Специализация:</p>
        <ul class="lireate-about-spec">
          <?php foreach ($partner['specializations'] as $item) : ?>
          <li><?php echo lireate_about_h($item); ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    </article>
    <?php endforeach; ?>
  </section>

  <section class="lireate-about-wrap" aria-label="Другие разделы">
    <article class="lireate-about-card">
      <span class="lireate-about-badge">Навигация</span>
      <h2 class="lireate-about-card-title">Команда и контакты</h2>
      <p class="lireate-about-text">Состав бюро — на странице <a href="/team">Команда Лирейт</a>. Телефоны, мессенджеры, адрес офиса и заявка — на странице <a href="/contacts/">Контакты</a>.</p>
    </article>
  </section>

  <script src="<?php echo lireate_about_h($la_assets); ?>/lireate-metrika-goals.js?v=<?php echo lireate_about_h($la_v); ?>"></script>
</div>

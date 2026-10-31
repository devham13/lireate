<?php
/**
 * Разметка страницы «Команда Лирейт» (/team/) — тёмный стиль как /news.
 */
require_once __DIR__ . '/lireate-team-lib.php';

$lt_assets = LIREATE_TEAM_ASSETS_BASE;
$lt_v = LIREATE_TEAM_TEMPLATE_VERSION;
$lt_intro = lireate_team_page_intro();
$lt_tagline = lireate_team_page_tagline();
$lt_team = lireate_team_members();
$lt_photo_origin = '';
if (isset($GLOBALS['lireate_team_photo_origin']) && is_string($GLOBALS['lireate_team_photo_origin'])) {
    $lt_photo_origin = rtrim($GLOBALS['lireate_team_photo_origin'], '/');
}
if ($lt_photo_origin !== '') {
    foreach ($lt_team as $i => $member) {
        if (isset($member['photo']) && strpos($member['photo'], 'http') !== 0) {
            $lt_team[$i]['photo'] = $lt_photo_origin . $member['photo'];
        }
    }
}
$lt_footer_css = defined('LIREATE_TEAM_FOOTER_CSS_HREF')
    ? LIREATE_TEAM_FOOTER_CSS_HREF
    : '/template/page/news/lireate-blog-footer.css';
?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Marmelad&family=Rubik:wght@400;600;700&family=Unbounded:wght@700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?php echo lireate_team_h($lt_assets); ?>/lireate-team.css?v=<?php echo lireate_team_h($lt_v); ?>">
<link rel="stylesheet" href="<?php echo lireate_team_h($lt_footer_css); ?>?v=<?php echo lireate_team_h($lt_v); ?>">
<script>
document.documentElement.classList.add('lireate-team-html');
document.documentElement.setAttribute('data-lireate-metrika-id', '<?php echo lireate_team_h(LIREATE_TEAM_METRIKA_ID); ?>');
if (document.body) { document.body.classList.add('lireate-team-body'); }
</script>
<div class="lireate-team-page" data-lireate-team-version="<?php echo lireate_team_h($lt_v); ?>">
  <header class="lireate-team-hero">
    <div class="lireate-team-wrap">
      <p class="lireate-team-kicker">LIREATE</p>
      <h1 class="lireate-team-h1">Команда Лирейт</h1>
      <p class="lireate-team-sub">Наша команда</p>
      <?php if ($lt_intro !== '') : ?>
      <p class="lireate-team-lead"><?php echo lireate_team_h($lt_intro); ?></p>
      <?php endif; ?>
      <?php if ($lt_tagline !== '') : ?>
      <p class="lireate-team-tagline"><?php echo lireate_team_h($lt_tagline); ?></p>
      <?php endif; ?>
      <div class="lireate-team-hero-actions" aria-label="Связаться с LIREATE">
        <a class="lireate-team-pill lireate-team-pill--solid" href="<?php echo lireate_team_h(LIREATE_TEAM_MAX_CHAT_URL); ?>" target="_blank" rel="noopener noreferrer" data-lx-ym-goal="perehod_v_messendzher">Max</a>
        <a class="lireate-team-pill lireate-team-pill--outline" href="<?php echo lireate_team_h(LIREATE_TEAM_TELEGRAM_URL); ?>" target="_blank" rel="noopener noreferrer" data-lx-ym-goal="perehod_v_telegramm">Telegram</a>
        <a class="lireate-team-pill lireate-team-pill--outline" href="<?php echo lireate_team_h(LIREATE_TEAM_WHATSAPP_URL); ?>" target="_blank" rel="noopener noreferrer" data-lx-ym-goal="perehod_v_messendzher">WhatsApp</a>
        <a class="lireate-team-pill lireate-team-pill--outline" href="<?php echo lireate_team_h(LIREATE_TEAM_PHONE_TEL); ?>" data-lx-ym-goal="perehod_v_telefon"><?php echo lireate_team_h(LIREATE_TEAM_PHONE_DISPLAY); ?></a>
      </div>
    </div>
  </header>

  <section class="lireate-team-wrap lireate-team-about" aria-labelledby="lt-about-title">
    <div class="lireate-team-about-grid">
      <article class="lireate-team-card">
        <span class="lireate-team-badge">LIREATE</span>
        <h2 class="lireate-team-card-title" id="lt-about-title">Чем занимается команда</h2>
        <p class="lireate-team-text">Юридическая защита вашей интеллектуальной собственности. Регистрация и защита товарных знаков, патентов и авторских прав.</p>
        <p class="lireate-team-text lireate-team-text--muted">Юридическая поддержка в Роспатенте, ФАС, ФТС, Суде по интеллектуальным правам (СИП) и во всех судебных инстанциях.</p>
      </article>
      <article class="lireate-team-card">
        <span class="lireate-team-badge lireate-team-badge--green">Отделы</span>
        <h2 class="lireate-team-card-title">Направления в составе команды</h2>
        <ul class="lireate-team-list-bullets">
          <li>Управляющие партнёры</li>
          <li>Отдел регистрации интеллектуальной собственности</li>
          <li>Патентный отдел</li>
        </ul>
      </article>
      <article class="lireate-team-card lireate-team-card--wide">
        <span class="lireate-team-badge">Как работаем</span>
        <h2 class="lireate-team-card-title">С клиентом — коротко</h2>
        <ol class="lireate-team-steps">
          <li>Свяжитесь через <a href="/kontaktyi/">контакты</a>, мессенджеры или телефон — мы перезвоним в ближайшее время.</li>
          <li>Обсуждаем задачу: товарные знаки, патенты, авторские права и сопутствующие процедуры.</li>
          <li>Готовим документы и сопровождаем в Роспатенте, ФАС, ФТС, СИП и судах — по вашей ситуации.</li>
        </ol>
        <p class="lireate-team-text lireate-team-text--muted">Подробнее об офисе и форме заявки — на странице <a href="/kontaktyi/">Контакты</a>.</p>
      </article>
    </div>
  </section>

  <section class="lireate-team-wrap lireate-team-roster" aria-labelledby="lt-roster-title">
    <div class="lireate-team-roster-head">
      <span class="lireate-team-badge">15 специалистов</span>
      <h2 class="lireate-team-card-title" id="lt-roster-title">Сотрудники LIREATE</h2>
    </div>
    <div class="lireate-team-grid">
      <?php foreach ($lt_team as $idx => $member) :
          $photo = isset($member['photo']) ? $member['photo'] : '';
      ?>
      <div class="lireate-team-member-card" role="button" tabindex="0" data-lt-team-index="<?php echo (int) $idx; ?>">
        <?php if ($photo !== '') : ?>
        <span class="lireate-team-member-photo-wrap">
          <img class="lireate-team-member-photo" src="<?php echo lireate_team_h($photo); ?>" alt="" loading="lazy" decoding="async">
        </span>
        <?php endif; ?>
        <span class="lireate-team-member-name"><?php echo lireate_team_h($member['name']); ?></span>
        <span class="lireate-team-member-role"><?php echo lireate_team_h($member['role']); ?></span>
      </div>
      <?php endforeach; ?>
    </div>
  </section>

  <div class="lireate-team-modal" id="lt-team-modal" hidden>
    <div class="lireate-team-modal-backdrop" data-lt-modal-close></div>
    <div class="lireate-team-modal-panel" role="dialog" aria-modal="true" aria-labelledby="lt-modal-name">
      <button type="button" class="lireate-team-modal-close" data-lt-modal-close aria-label="Закрыть">&times;</button>
      <div class="lireate-team-modal-photo-wrap">
        <img class="lireate-team-modal-photo" id="lt-modal-photo" src="" alt="">
      </div>
      <h3 class="lireate-team-modal-name" id="lt-modal-name"></h3>
      <p class="lireate-team-modal-role" id="lt-modal-role"></p>
    </div>
  </div>

  <script type="application/json" id="lt-team-json"><?php
  echo json_encode($lt_team, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);
  ?></script>
  <script src="<?php echo lireate_team_h($lt_assets); ?>/lireate-team.js?v=<?php echo lireate_team_h($lt_v); ?>"></script>
  <script src="<?php echo lireate_team_h($lt_assets); ?>/lireate-metrika-goals.js?v=<?php echo lireate_team_h($lt_v); ?>"></script>
</div>

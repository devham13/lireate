<?php
/**
 * Разметка профиля автора (/avtor/) — LIREATE Light V4.
 */
require_once __DIR__ . '/lireate-avtor-lib.php';

$la_v = LIREATE_AVTOR_TEMPLATE_VERSION;
$la_assets = LIREATE_AVTOR_ASSETS_BASE;
$la_pubs = lireate_avtor_publications();
$la_specs = lireate_avtor_specializations();
$la_facts = lireate_avtor_company_facts();
$la_email = lireate_avtor_email();
?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" media="print" onload="this.media='all'">
<noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"></noscript>
<link rel="stylesheet" href="/template/css/lireate-cms-pages-light-v4.css?v=<?php echo lireate_avtor_h($la_v); ?>">
<link rel="stylesheet" href="/template/css/lireate-light-v4.css?v=<?php echo lireate_avtor_h($la_v); ?>">
<link rel="stylesheet" href="<?php echo lireate_avtor_h($la_assets); ?>/lireate-avtor.css?v=<?php echo lireate_avtor_h($la_v); ?>">
<script>
document.documentElement.classList.add('lireate-avtor-html', 'lireate-theme-light-v4');
document.documentElement.setAttribute('data-lireate-metrika-id', '<?php echo lireate_avtor_h(LIREATE_AVTOR_METRIKA_ID); ?>');
if (document.body) { document.body.classList.add('lireate-avtor-body'); }
</script>
<div id="lireate-avtor" class="lu-page">
  <div class="lu-glow" aria-hidden="true"></div>
  <div class="lu-wrap">
    <nav class="lu-crumbs" aria-label="Хлебные крошки">
      <ol>
        <li><a href="/">Главная</a></li>
        <li><span aria-current="page">Автор</span></li>
      </ol>
    </nav>

    <header class="la-hero" aria-labelledby="la-h1">
      <div class="la-hero-text">
        <p class="lu-eyebrow">LIREATE · патентное бюро · автор статей</p>
        <h1 class="lu-h1" id="la-h1">Горбачев Юрий Александрович</h1>
        <p class="la-role"><strong>Директор и управляющий партнёр ООО «ЛИРЕЙТ», патентное бюро «Лирейт»</strong></p>
        <div class="la-hero-cta">
          <a class="lu-btn lu-btn-primary" href="<?php echo lireate_avtor_h(LIREATE_AVTOR_TELEGRAM_URL); ?>" target="_blank" rel="noopener noreferrer" data-la-goal="perehod_v_telegramm">Telegram</a>
          <a class="lu-btn lu-btn-ghost" href="<?php echo lireate_avtor_h(LIREATE_AVTOR_WHATSAPP_URL); ?>" target="_blank" rel="noopener noreferrer" data-la-goal="perehod_v_messendzher">WhatsApp</a>
          <a class="lu-btn lu-btn-ghost" href="<?php echo lireate_avtor_h(LIREATE_AVTOR_MAX_CHAT_URL); ?>" target="_blank" rel="noopener noreferrer" data-la-goal="perehod_v_messendzher">MAX</a>
          <a class="lu-btn lu-btn-ghost" href="<?php echo lireate_avtor_h(LIREATE_AVTOR_PHONE_TEL); ?>" data-la-goal="klik_po_nomeru_telefona"><?php echo lireate_avtor_h(LIREATE_AVTOR_PHONE_DISPLAY); ?></a>
        </div>
      </div>
      <figure class="la-photo">
        <picture>
          <source srcset="/template/images/team/person/11.webp" type="image/webp">
          <img src="/template/images/team/person/11.jpg" alt="Горбачев Юрий Александрович" width="350" height="525" loading="eager" decoding="async">
        </picture>
      </figure>
    </header>

    <section class="lu-section" aria-labelledby="la-about">
      <h2 class="lu-h2" id="la-about">Чем занимается</h2>
      <div class="la-prose">
        <p>Юрий Горбачев руководит патентным бюро «Лирейт» из Зеленодольска (Республика Татарстан). Бюро регистрирует и защищает товарные знаки, патенты и авторские права. Он директор ООО «ЛИРЕЙТ» с января 2021 года и один из двух совладельцев компании.</p>
        <p>Основные направления его работы:</p>
        <ul class="la-list">
<?php foreach ($la_specs as $item) : ?>
          <li><?php echo lireate_avtor_h($item); ?></li>
<?php endforeach; ?>
        </ul>
      </div>
    </section>

    <section class="lu-section" aria-labelledby="la-company">
      <h2 class="lu-h2" id="la-company">Компания и цифры</h2>
      <ul class="la-list la-list--cards">
<?php foreach ($la_facts as $item) : ?>
        <li class="la-fact-card"><?php echo lireate_avtor_h($item); ?></li>
<?php endforeach; ?>
      </ul>
    </section>

    <section class="lu-section" aria-labelledby="la-pubs">
      <h2 class="lu-h2" id="la-pubs">Публикации</h2>
      <p class="lu-section-lead">Юрий Горбачев пишет для предпринимателей о товарных знаках, патентах и авторском праве. Его материалы выходят здесь:</p>
      <div class="la-pub-grid">
<?php foreach ($la_pubs as $pub) : ?>
        <a class="la-pub-card lu-card" href="<?php echo lireate_avtor_h($pub['href']); ?>" target="_blank" rel="noopener">
          <strong><?php echo lireate_avtor_h($pub['title']); ?></strong>
          <span><?php echo lireate_avtor_h($pub['text']); ?></span>
        </a>
<?php endforeach; ?>
      </div>
    </section>

    <section class="lu-section" aria-labelledby="la-contacts">
      <h2 class="lu-h2" id="la-contacts">Контакты компании</h2>
      <div class="la-contacts la-prose">
        <p>ООО «ЛИРЕЙТ», ИНН 1648040894, ОГРН 1151673001578</p>
        <p>Телефон: <a href="tel:+79600484957">+7 (960) 048-49-57</a><br>
        E-mail: <a href="mailto:<?php echo lireate_avtor_h($la_email); ?>"><?php echo lireate_avtor_h($la_email); ?></a></p>
        <p class="la-nav-links">
          <a href="/team/">Команда</a><span aria-hidden="true">·</span>
          <a href="/docs">Документы и реквизиты</a><span aria-hidden="true">·</span>
          <a href="/kontaktyi">Контакты</a>
        </p>
      </div>
    </section>
  </div>
</div>
<?php lireate_avtor_render_footer(); ?>
<script>
(function () {
  document.addEventListener('click', function (e) {
    var a = e.target && e.target.closest ? e.target.closest('[data-la-goal]') : null;
    if (!a) { return; }
    try {
      var mid = document.documentElement.getAttribute('data-lireate-metrika-id');
      if (mid && typeof window.ym === 'function') {
        window.ym(parseInt(mid, 10), 'reachGoal', a.getAttribute('data-la-goal'));
      }
    } catch (err) {}
  });
})();
</script>
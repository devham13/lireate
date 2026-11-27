<?php
require_once __DIR__ . '/lireate-uslugi-lib.php';
$lu_groups = lireate_uslugi_groups();
$lu_v = LIREATE_USLUGI_VERSION;
$lu_m = lireate_uslugi_messengers();
$lu_count = 0;
foreach ($lu_groups as $g) { $lu_count += count($g['cards']); }
?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" media="print" onload="this.media='all'">
<noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"></noscript>
<link rel="stylesheet" href="/template/css/lireate-cms-pages-light-v4.css?v=20261127">
<link rel="stylesheet" href="/template/page/uslugi/lireate-uslugi.css?v=<?php echo lireate_uslugi_h($lu_v); ?>">
<script>
document.documentElement.classList.add('lireate-uslugi-html', 'lireate-theme-light-v4');
if (document.body) { document.body.classList.add('lireate-uslugi-body'); }
</script>
<div class="lu-page">
  <div class="lu-glow" aria-hidden="true"></div>
  <div class="lu-wrap">
    <nav class="lu-crumbs" aria-label="Хлебные крошки">
      <ol>
        <li><a href="/">Главная</a></li>
        <li><span aria-current="page">Услуги</span></li>
      </ol>
    </nav>

    <section class="lu-hero" aria-labelledby="lu-h1">
      <div class="lu-hero-main">
        <p class="lu-eyebrow">LIREATE · патентное бюро · услуги</p>
        <h1 class="lu-h1" id="lu-h1">Услуги по защите интеллектуальной собственности</h1>
        <p class="lu-lead">Регистрация товарных знаков, патенты и защита авторских прав — сопровождаем до результата. Выберите направление: на каждой странице описаны сроки, этапы и формат работы.</p>
        <div class="lu-cta">
          <a class="lu-btn lu-btn-primary" href="#lu-contact">Получить консультацию</a>
          <a class="lu-btn lu-btn-ghost" href="<?php echo lireate_uslugi_h($lu_m['tg']); ?>" target="_blank" rel="noopener noreferrer" data-lu-goal="perehod_v_telegramm">Написать в мессенджер</a>
        </div>
        <div class="lu-stats">
          <div class="lu-stat"><strong><?php echo (int) $lu_count; ?></strong><span>направлений работы</span></div>
          <a class="lu-stat" href="https://vk.com/lireate" target="_blank" rel="noopener noreferrer"><strong>133</strong><span>кейса за 2 года во ВКонтакте</span></a>
          <div class="lu-stat"><strong>РФ и КНР</strong><span>регистрация знаков</span></div>
        </div>
      </div>
      <aside class="lu-panel" aria-label="Как мы работаем">
        <div class="lu-panel-head"><span>Как мы работаем</span><span class="lu-live">Роспатент</span></div>
        <ol class="lu-steps">
          <li><span class="lu-dot lu-dot--blue"></span><div><strong>Проверка</strong><span>Ищем сходные обозначения и оцениваем риски</span></div></li>
          <li><span class="lu-dot lu-dot--green"></span><div><strong>Заявка</strong><span>Готовим документы и подаём в Роспатент</span></div></li>
          <li><span class="lu-dot lu-dot--amber"></span><div><strong>Экспертиза</strong><span>Отвечаем на запросы и уведомления</span></div></li>
          <li><span class="lu-dot lu-dot--violet"></span><div><strong>Результат</strong><span>Свидетельство или патент на ваше имя</span></div></li>
        </ol>
      </aside>
    </section>

<?php foreach ($lu_groups as $g) : ?>
    <section class="lu-section" aria-labelledby="<?php echo lireate_uslugi_h($g['id']); ?>">
      <div class="lu-section-head">
        <h2 class="lu-h2" id="<?php echo lireate_uslugi_h($g['id']); ?>"><?php echo lireate_uslugi_h($g['title']); ?></h2>
        <p class="lu-section-lead"><?php echo lireate_uslugi_h($g['lead']); ?></p>
      </div>
      <div class="lu-grid">
<?php $i = 0; foreach ($g['cards'] as $card) : $i++; ?>
        <a class="lu-card" href="<?php echo lireate_uslugi_h($card['href']); ?>">
          <span class="lu-card-top"><span class="lu-card-num"><?php echo sprintf('%02d', $i); ?></span><span class="lu-card-tag"><?php echo lireate_uslugi_h($card['tag']); ?></span></span>
          <h3 class="lu-card-title"><?php echo lireate_uslugi_h($card['title']); ?></h3>
          <p class="lu-card-text"><?php echo lireate_uslugi_h($card['text']); ?></p>
          <span class="lu-card-more">Подробнее <?php echo lireate_uslugi_icon('arrow'); ?></span>
        </a>
<?php endforeach; ?>
      </div>
    </section>
<?php endforeach; ?>

    
    <section class="lu-section" aria-labelledby="lu-is-contracts"><!-- lireate_design_uslugi_dogovory_evm_v1 -->
      <div class="lu-section-head">
        <h2 class="lu-h2" id="lu-is-contracts">Договоры и программы</h2>
        <p class="lu-section-lead">Права на код, контент и сделки с объектами ИС.</p>
      </div>
      <div class="lu-grid">
        <a class="lu-card" href="/dogovory/">
          <span class="lu-card-top"><span class="lu-card-num">01</span><span class="lu-card-tag">ГК РФ</span></span>
          <h3 class="lu-card-title">Договоры в сфере ИС</h3>
          <p class="lu-card-text">Лицензия, франчайзинг, отчуждение, авторский заказ и регистрация распоряжений в Роспатенте.</p>
          <span class="lu-card-more">Подробнее <svg class="lu-ico lu-ico--arrow" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg></span>
        </a>
        <a class="lu-card" href="/registraciya_programm_evm/">
          <span class="lu-card-top"><span class="lu-card-num">02</span><span class="lu-card-tag">ПО и БД</span></span>
          <h3 class="lu-card-title">Регистрация программ для ЭВМ</h3>
          <p class="lu-card-text">Регистрация программы и базы данных, защита сайта и приложений, сопровождение в Роспатенте.</p>
          <span class="lu-card-more">Подробнее <svg class="lu-ico lu-ico--arrow" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg></span>
        </a>
      </div>
    </section>

<section class="lu-contact" id="lu-contact" aria-labelledby="lu-contact-h">
      <div class="lu-contact-text">
        <h2 class="lu-h2" id="lu-contact-h">Не знаете, с чего начать?</h2>
        <p>Напишите название бренда и чем занимаетесь — подскажем, какая услуга нужна, и посчитаем стоимость. Госпошлины покажем отдельно.</p>
        <p class="lu-contact-phones"><a href="tel:+79600484957" data-lx-ym-goal="klik_po_nomeru_telefona">+7 (960) 048-49-57</a><span aria-hidden="true">•</span><a href="tel:+74999384595" data-lx-ym-goal="klik_po_nomeru_telefona">+7 (499) 938-45-95</a></p>
      </div>
      <div class="lu-contact-btns">
        <a class="lu-msg lu-msg--tg" href="<?php echo lireate_uslugi_h($lu_m['tg']); ?>" target="_blank" rel="noopener noreferrer" data-lu-goal="perehod_v_telegramm"><?php echo lireate_uslugi_icon('tg'); ?>Telegram</a>
        <a class="lu-msg lu-msg--wa" href="<?php echo lireate_uslugi_h($lu_m['wa']); ?>" target="_blank" rel="noopener noreferrer" data-lu-goal="perehod_v_messendzher"><?php echo lireate_uslugi_icon('wa'); ?>WhatsApp</a>
        <a class="lu-msg lu-msg--max" href="<?php echo lireate_uslugi_h($lu_m['max']); ?>" target="_blank" rel="noopener noreferrer" data-lu-goal="perehod_v_messendzher"><?php echo lireate_uslugi_icon('max'); ?>MAX</a>
      </div>
    </section>
  </div>
<?php lireate_uslugi_render_footer(); ?>
</div>
<aside class="lu-dock" aria-label="Быстрый контакт">
  <a class="lu-dock-lead" href="#lu-contact" title="Оставить заявку" aria-label="Оставить заявку"><?php echo lireate_uslugi_icon('lead'); ?></a>
  <a class="lu-dock-tg" href="<?php echo lireate_uslugi_h($lu_m['tg']); ?>" title="Telegram" aria-label="Telegram" target="_blank" rel="noopener noreferrer" data-lu-goal="perehod_v_telegramm"><?php echo lireate_uslugi_icon('tg'); ?></a>
  <a class="lu-dock-wa" href="<?php echo lireate_uslugi_h($lu_m['wa']); ?>" title="WhatsApp" aria-label="WhatsApp" target="_blank" rel="noopener noreferrer" data-lu-goal="perehod_v_messendzher"><?php echo lireate_uslugi_icon('wa'); ?></a>
  <a class="lu-dock-max" href="<?php echo lireate_uslugi_h($lu_m['max']); ?>" title="MAX" aria-label="MAX" target="_blank" rel="noopener noreferrer" data-lu-goal="perehod_v_messendzher"><?php echo lireate_uslugi_icon('max'); ?></a>
</aside>
<script>
(function () {
  document.addEventListener('click', function (e) {
    var a = e.target && e.target.closest ? e.target.closest('[data-lu-goal]') : null;
    if (!a) { return; }
    try { if (typeof window.ym === 'function') { window.ym(52622734, 'reachGoal', a.getAttribute('data-lu-goal')); } } catch (err) {} // pragma: allowlist secret
  });
})();
</script>

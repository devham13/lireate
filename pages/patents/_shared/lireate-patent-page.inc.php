<?php
/** CMS content include for /patent and /patent_prom_obr. SEO <head> stays owned by the CMS. */
require_once __DIR__ . '/lireate-patent-data.php';
if (!function_exists('lp4_h')) {
    function lp4_h($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
}
$lp4_key = isset($lireate_patent_key) ? $lireate_patent_key : '';
$lp4 = lp4_page_data($lp4_key);
$lp4_phone = '+7 (960) 048-49-57';
$lp4_phone_href = 'tel:+79600484957';
$lp4_version = '20261126-v1';
?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Inter+Tight:wght@500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/template/css/lireate-patent-light-v4.css?v=<?php echo lp4_h($lp4_version); ?>">
<script>document.body.classList.add('lireate-theme-light-v4','lireate-patent-light-v4');</script>
<div id="lireate-patent-v4" class="lp4-root lp4-<?php echo lp4_h($lp4_key); ?>">
  <nav class="lp4-topnav" aria-label="Разделы текущей страницы">
    <div class="lp4-topnav-items">
      <a href="#lp4-about" class="lp4-nav-link is-active">Об услуге</a>
      <a href="#lp4-criteria" class="lp4-nav-link">Что защищаем</a>
      <a href="#lp4-geography" class="lp4-nav-link">Страны</a>
      <a href="#lp4-steps" class="lp4-nav-link">Этапы</a>
      <?php if (!empty($lp4['cases'])) : ?><a href="#lp4-cases" class="lp4-nav-link">Кейсы</a><?php endif; ?>
      <a href="#lp4-faq" class="lp4-nav-link">FAQ</a>
    </div>
    <div class="lp4-topnav-actions">
      <a class="lp4-topnav-phone" href="<?php echo $lp4_phone_href; ?>" data-lx-ym-goal="klik_po_nomeru_telefona"><?php echo lp4_h($lp4_phone); ?></a>
      <a class="lp4-nav-cta" href="#lp4-contact">Оставить заявку</a>
    </div>
  </nav>
  <main class="lp4-main" id="lp4-main">
    <section class="lp4-hero lp4-wrap" id="lp4-about">
      <div class="lp4-hero-copy">
        <div class="lp4-kicker"><span aria-hidden="true" class="lp4-kicker-dot"></span><?php echo lp4_h($lp4['eyebrow']); ?></div>
        <h1><?php echo lp4_h($lp4['title']); ?></h1>
        <p class="lp4-hero-lead"><?php echo lp4_h($lp4['lead']); ?></p>
        <div class="lp4-hero-points"><span>✓&nbsp; Бесплатная первичная консультация</span><span>✓&nbsp; Оценка возможности охраны</span><span>✓&nbsp; Сопровождение до результата экспертизы</span></div>
        <div class="lp4-buttons">
          <a class="lp4-btn lp4-btn-main" href="#lp4-contact">Обсудить патентование <span aria-hidden="true">↗</span></a>
          <a class="lp4-btn lp4-btn-light" href="https://t.me/lireate_patent" target="_blank" rel="noopener noreferrer" data-lx-ym-goal="perehod_v_telegramm">Написать в Telegram <span aria-hidden="true">↗</span></a>
        </div>
        <div class="lp4-small-note"><span class="lp4-status-dot" aria-hidden="true"></span>Специалисты Лирейт помогут выбрать следующий шаг</div>
      </div>
      <figure class="lp4-photo-card">
        <div class="lp4-photo-frame"><img src="<?php echo lp4_h($lp4['image']); ?>" alt="<?php echo lp4_h($lp4['photo_alt']); ?>" width="640" height="848" fetchpriority="high"></div>
        <figcaption class="lp4-photo-caption"><span>LIREATE / Экспертное сопровождение</span><strong><?php echo lp4_h($lp4['photo_caption']); ?></strong></figcaption>
      </figure>
    </section>
    <div class="lp4-wrap lp4-stats" aria-label="Порядок работы">
      <?php foreach ($lp4['metrics'] as $metric) : ?>
      <div><span><?php echo lp4_h($metric[0]); ?></span><strong><?php echo lp4_h($metric[1]); ?></strong></div>
      <?php endforeach; ?>
    </div>
    <section class="lp4-section lp4-wrap" id="lp4-criteria">
      <div class="lp4-section-head"><p class="lp4-section-eyebrow">Подготовка заявки</p><h2><?php echo $lp4_key === 'patent' ? 'Что важно для патентования' : 'Какие элементы дизайна можно защитить'; ?></h2><p><?php echo lp4_h($lp4['intro']); ?></p></div>
      <div class="lp4-card-grid">
        <?php foreach ($lp4['overview'] as $idx => $point) : ?>
        <article class="lp4-info-card"><span class="lp4-card-number">0<?php echo $idx + 1; ?> / <?php echo $lp4_key === 'patent' ? 'Критерий' : 'Объект'; ?></span><h3><?php echo lp4_h($point[0]); ?></h3><p><?php echo lp4_h($point[1]); ?></p><div class="lp4-card-decoration" aria-hidden="true"><?php echo $idx === 0 ? '®' : ($idx === 1 ? '§' : '↗'); ?></div></article>
        <?php endforeach; ?>
      </div>
    </section>
    <section class="lp4-section lp4-section-tinted" id="lp4-geography">
      <div class="lp4-wrap"><div class="lp4-section-head"><p class="lp4-section-eyebrow">География защиты</p><h2>Патентование в России и за рубежом</h2><p>Выбор страны и процедуры зависит от объекта, бюджета и задачи. Предварительные сроки обсуждаются индивидуально.</p></div>
        <div class="lp4-countries">
          <?php foreach ($lp4['countries'] as $country) : ?>
          <article class="lp4-country"><span><?php echo lp4_h($country[0]); ?></span><h3><?php echo lp4_h($country[1]); ?></h3><p><?php echo lp4_h($country[2]); ?></p></article>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
    <section class="lp4-section lp4-wrap" id="lp4-steps">
      <div class="lp4-section-head"><p class="lp4-section-eyebrow">Порядок сотрудничества</p><h2>Как мы работаем</h2><p>Процесс начинается с оценки и завершается сопровождением заявки на протяжении экспертизы.</p></div>
      <div class="lp4-steps">
        <?php foreach ($lp4['steps'] as $step) : ?>
        <details class="lp4-step" <?php echo $step[0] === '01' ? 'open' : ''; ?>><summary><span class="lp4-step-num"><?php echo lp4_h($step[0]); ?></span><strong><?php echo lp4_h($step[1]); ?></strong><span class="lp4-step-toggle" aria-hidden="true">+</span></summary><div class="lp4-step-detail"><p><?php echo lp4_h($step[2]); ?></p></div></details>
        <?php endforeach; ?>
      </div>
    </section>
    <section class="lp4-section lp4-section-tinted">
      <div class="lp4-wrap lp4-checklist"><div><p class="lp4-section-eyebrow">Первый шаг</p><h2>Что обсудим на консультации</h2><p>Эти вопросы помогут оценить задачу без лишней бюрократии.</p><a href="#lp4-contact" class="lp4-btn lp4-btn-main">Обсудить мой проект ↗</a></div><ul><?php foreach ($lp4['questions'] as $question) : ?><li><span aria-hidden="true">✓</span><?php echo lp4_h($question); ?></li><?php endforeach; ?></ul></div>
    </section>
    <?php if (!empty($lp4['cases'])) : ?>
    <section class="lp4-section lp4-wrap" id="lp4-cases">
      <div class="lp4-section-head"><p class="lp4-section-eyebrow">Практика Лирейт</p><h2>Реальные истории патентования</h2><p>Примеры опубликованы в группе Лирейт ВКонтакте. По ссылкам можно ознакомиться с исходными публикациями.</p></div>
      <div class="lp4-card-grid lp4-case-grid">
        <?php foreach ($lp4['cases'] as $case) : ?><article class="lp4-case-card"><span><?php echo lp4_h($case[0]); ?></span><h3><?php echo lp4_h($case[1]); ?></h3><p><?php echo lp4_h($case[2]); ?></p><a href="<?php echo lp4_h($case[3]); ?>" target="_blank" rel="noopener noreferrer">Читать кейс во ВКонтакте ↗</a></article><?php endforeach; ?>
      </div>
    </section>
    <?php endif; ?>
    <section class="lp4-section lp4-wrap" id="lp4-faq">
      <div class="lp4-section-head"><p class="lp4-section-eyebrow">FAQ</p><h2>Ответы на частые вопросы</h2></div>
      <div class="lp4-faq-list">
        <?php foreach ($lp4['faqs'] as $item) : ?><details class="lp4-faq"><summary><?php echo lp4_h($item[0]); ?><span aria-hidden="true">+</span></summary><p><?php echo lp4_h($item[1]); ?></p></details><?php endforeach; ?>
      </div>
    </section>
    <section class="lp4-section lp4-wrap" id="lp4-contact">
      <div class="lp4-contact"><div><p class="lp4-section-eyebrow">Обратная связь</p><h2>Расскажите о своей задаче</h2><p>Обсудим техническое решение или дизайн изделия, возможности защиты и дальнейшие шаги.</p><div class="lp4-contact-trust">Без ложных обещаний · Работаем по договору</div></div>
        <div class="lp4-contact-actions"><button type="button" class="lp4-btn lp4-btn-main js-modal-form" data-toggle="modal" data-btn="Отправить заявку" data-frm="name,tel" data-mtitle="Оставьте заявку и мы свяжемся с вами в ближайшее время" data-success="Мы перезвоним Вам в ближайшее время." data-tomail="Заказать обратный звонок">Оставить заявку ↗</button><a class="lp4-btn lp4-btn-light" href="<?php echo $lp4_phone_href; ?>" data-lx-ym-goal="klik_po_nomeru_telefona"><?php echo lp4_h($lp4_phone); ?></a><a class="lp4-contact-tg" href="https://t.me/lireate_patent" target="_blank" rel="noopener noreferrer" data-lx-ym-goal="perehod_v_telegramm">Или напишите в Telegram ↗</a></div>
      </div>
    </section>
  </main>
</div>
<script defer src="/template/js/lireate-patent-light-v4.js?v=<?php echo lp4_h($lp4_version); ?>"></script>

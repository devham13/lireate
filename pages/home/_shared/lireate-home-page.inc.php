<?php
/**
 * Разметка главной LIREATE Light V4 (CMS home /).
 */
require_once __DIR__ . '/lireate-home-lib.php';

$lh_v = LIREATE_HOME_TEMPLATE_VERSION;
$lh_team = lireate_home_team_members();
$lh_stages = lireate_home_process_stages();
$lh_expert = null;
foreach ($lh_team as $m) {
    if (strpos($m['name'], 'Габдрахманова') !== false) {
        $lh_expert = $m;
        break;
    }
}
if ($lh_expert === null && count($lh_team) > 0) {
    $lh_expert = $lh_team[0];
}
$lh_spam_guard = md5(date('m.Y'));
$lh_mail_action = '/mail.php';
$lh_preview = defined('LIREATE_HOME_PREVIEW') && LIREATE_HOME_PREVIEW;
?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Inter+Tight:wght@500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?php echo lireate_home_h(LIREATE_HOME_CSS_HREF); ?>?v=<?php echo lireate_home_h($lh_v); ?>">
<link rel="stylesheet" href="<?php echo lireate_home_h(LIREATE_HOME_LIGHT_V4_CSS_HREF); ?>?v=<?php echo lireate_home_h($lh_v); ?>">

<div id="lireate-home-light-v4" class="lh4-root">
<div class="scroll-progress" aria-hidden="true"></div>

<main id="lh4-top">
  <section class="hero">
    <div class="wrap hero-grid">
      <div class="reveal">
        <div class="eyebrow"><span class="eyebrow-mark" aria-hidden="true"></span> Интеллектуальная собственность · товарные знаки · патенты · авторское право</div>
        <h1 class="lh4-seo-h1">Патентное бюро «<span class="lh4-brand-red">Лирейт</span>»</h1>
        <p class="lh4-hero-subtitle">Регистрация и защита интеллектуальной собственности</p>
        <p class="lead">Регистрация товарных знаков, патентов, программ ЭВМ и договоры. Помогаем выбрать способ защиты, говорим о реальных возможностях и сроках и сопровождаем до результата.</p>
        <ul class="hero-bullets">
          <li><i>✓</i><span>бесплатная консультация</span></li>
          <li><i>✓</i><span>говорим только о реальных возможностях и сроках — никаких ложных обещаний</span></li>
          <li><i>✓</i><span>работаем с финансовой гарантией</span></li>
        </ul>
        <div class="hero-actions">
          <a class="btn primary" href="/findtz">Проверить товарный знак</a>
          <a class="btn soft" href="<?php echo lireate_home_h(LIREATE_HOME_TELEGRAM_URL); ?>" target="_blank" rel="noopener noreferrer" data-lx-ym-goal="perehod_v_telegramm">Бесплатная консультация ↗</a>
        </div>
        <div class="hero-note"><span class="live-pulse" aria-hidden="true"></span> Через 15 минут вы получите ответ на свой вопрос.</div>
      </div>
      <div class="hero-right reveal">
        <?php if ($lh_expert !== null) : ?>
        <article class="expert-card">
          <div class="expert-photo"><div class="photo-shine" aria-hidden="true"></div><img src="<?php echo lireate_home_h($lh_expert['photo']); ?>" alt="<?php echo lireate_home_h($lh_expert['name']); ?>"></div>
          <div class="expert-info">
            <div class="badge"><?php echo lireate_home_h($lh_expert['role']); ?></div>
            <h2><?php echo lireate_home_h(preg_replace('/\\s+\\S+$/u', '', $lh_expert['name'])); ?></h2>
            <div class="role">Специалист Лирейт · консультации, сопровождение заявок и защита интеллектуальной собственности</div>
            <div class="links">
              <a class="btn linkish" href="/team">Команда Лирейт ↗</a>
              <a class="btn linkish" href="/o_kompanii">О компании ↗</a>
            </div>
          </div>
        </article>
        <?php endif; ?>
        <div class="expert-mini">
          <strong>Понятный первый шаг</strong>
          <p>Оставьте заявку — специалисты свяжутся с вами, уточнят задачу и подберут следующий шаг: проверка знака, консультация по патентованию или защите авторских прав.</p>
        </div>
      </div>
    </div>
    <div class="wrap stats">
      <div class="stats-grid reveal">
        <div class="stat"><strong class="count" data-from="2000" data-to="2015">2015</strong><span>работаем с этого года</span></div>
        <div class="stat"><strong class="count" data-from="0" data-to="15">15</strong><span>специалистов</span></div>
        <div class="stat"><strong class="count" data-from="0" data-to="15">15</strong><span>минут до ответа</span></div>
        <div class="stat"><strong class="checkmark">✓</strong><span>с финансовой гарантией</span></div>
      </div>
    </div>
  </section>

  <div class="lh4-subnav" id="lh4Subnav" aria-label="Навигация по разделам главной">
    <div class="wrap lh4-subnav-in">
      <nav class="lh4-subnav-links">
        <a href="#services">Услуги</a><a href="#team">Команда</a><a href="#cases">Кейсы</a><a href="#pricing">Стоимость</a><a href="#how">Этапы</a><a href="#faq">FAQ</a><a href="#blog">Блог</a>
      </nav>
      <div class="lh4-subnav-right">
        <a class="lh4-subnav-phone" href="<?php echo lireate_home_h(LIREATE_HOME_PHONE_TEL); ?>" data-lx-ym-goal="klik_po_nomeru_telefona"><?php echo lireate_home_h(LIREATE_HOME_PHONE_DISPLAY); ?></a>
        <a class="lh4-subnav-cta btn primary" href="#lh4-contact">Оставить заявку</a>
      </div>
    </div>
  </div>

  <div class="motion-strip" aria-label="Направления работы Лирейт"><div class="motion-strip-inner" aria-hidden="true"><span>ТОВАРНЫЕ ЗНАКИ</span><i>•</i><span>ПАТЕНТЫ</span><i>•</i><span>ДОГОВОРЫ</span><i>•</i><span>ПРОГРАММЫ ЭВМ</span><i>•</i><span>ЗАЩИТА ПРАВ</span><i>•</i><span>ТОВАРНЫЕ ЗНАКИ</span><i>•</i><span>ПАТЕНТЫ</span><i>•</i><span>ДОГОВОРЫ</span><i>•</i><span>ПРОГРАММЫ ЭВМ</span><i>•</i><span>ЗАЩИТА ПРАВ</span><i>•</i></div></div>

  <section class="section" id="services">
    <div class="wrap">
      <div class="section-head reveal">
        <h2>Выберите интересующую <span class="brand-highlight">услугу</span></h2>
        <p>«Удовлетворить предъявляемые Роспатентом требования — непросто. Подача заявки без помощи специалиста по интеллектуальной собственности может привести к отказу в регистрации»</p>
      </div>
      <div class="service-grid">
        <article class="service-card reveal"><div class="service-num">01 · Товарный знак</div><h3>Регистрация товарного знака</h3><p>Зарегистрируем Ваш товарный знак не только на территории России, но и во всём мире.</p><a class="link-arrow" href="/registraciya_tovarnogo_znaka/">Подробнее ↗</a></article>
        <article class="service-card reveal"><div class="service-num">02 · Патенты</div><h3>Патентование решений</h3><p>Поможем Вам получить патент на изобретение, промышленный образец или полезную модель.</p><a class="link-arrow" href="/patent">Подробнее ↗</a></article>
        <article class="service-card reveal"><div class="service-num">03 · Договоры</div><h3>Договоры и передача прав</h3><p>Подготовим индивидуальный договор для охраны Ваших прав на результат интеллектуальной деятельности.</p><a class="link-arrow" href="/dogovory/">Подробнее ↗</a></article>
        <article class="service-card reveal"><div class="service-num">04 · Программы ЭВМ</div><h3>Защита программ и интерфейсов</h3><p>Защитим Ваше приложение: программный код, дизайн, интерфейс.</p><a class="link-arrow" href="/registraciya_programm_evm/">Подробнее ↗</a></article>
      </div>
    </div>
  </section>

  <section class="section" style="padding-top:0">
    <div class="wrap">
      <div class="section-head reveal">
        <h2>Почему нас <span class="brand-highlight">выбирают</span></h2>
      </div>
      <div class="why-grid">
        <article class="why-card reveal"><div class="icon">⚖</div><h3>Опыт</h3><p>У нас есть специалисты, которые занимаются защитой интеллектуальной собственности более 20 лет.</p></article>
        <article class="why-card reveal"><div class="icon">✓</div><h3>Честно</h3><p>Мы не обманываем. Обсуждая проект, мы говорим только о реальных возможностях и сроках. Никаких ложных обещаний.</p></article>
        <article class="why-card reveal"><div class="icon">✓</div><h3>Финансовая гарантия</h3><p>Работаем с финансовой гарантией.</p></article>
        <article class="why-card reveal"><div class="icon">₽</div><h3>Бесплатная консультация</h3><p>Нет времени искать ответ на страницах сайта? Оставьте заявку, и наши специалисты свяжутся с вами.</p></article>
      </div>
    </div>
  </section>

  <section class="section" id="team" style="padding-top:0">
    <div class="wrap team-shell reveal">
      <div class="team-top">
        <div>
          <div class="eyebrow">Команда LIREATE</div>
          <h2 class="wide-title">Люди, которые защищают ваши идеи</h2>
        </div>
      </div>
      <div class="team-tools">
        <div class="filters">
          <button type="button" class="filter active" data-filter="all" aria-pressed="true">Все специалисты</button>
          <button type="button" class="filter" data-filter="management" aria-pressed="false">Руководство</button>
          <button type="button" class="filter" data-filter="registration" aria-pressed="false">Регистрация</button>
          <button type="button" class="filter" data-filter="patent" aria-pressed="false">Патентный отдел</button>
        </div>
        <div class="arrow-group">
          <button type="button" class="arrow" id="lh4-prev-team" aria-label="Предыдущие сотрудники">←</button>
          <button type="button" class="arrow" id="lh4-next-team" aria-label="Следующие сотрудники">→</button>
        </div>
      </div>
      <div class="team-carousel" aria-roledescription="карусель сотрудников">
        <div class="team-track" id="lh4-team-track" tabindex="0" aria-label="Сотрудники Лирейт — прокрутка стрелками">
          <?php foreach ($lh_team as $member) : ?>
          <article class="team-card" data-group="<?php echo lireate_home_h($member['group']); ?>">
            <div class="avatar"><img src="<?php echo lireate_home_h($member['photo']); ?>" alt="<?php echo lireate_home_h($member['name']); ?>"></div>
            <h3><?php echo lireate_home_h($member['name']); ?></h3>
            <p><?php echo lireate_home_h($member['role']); ?></p>
            <span class="team-tag"><?php echo lireate_home_h($member['tag']); ?></span>
          </article>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </section>

  <section class="section" id="cases">
    <div class="wrap">
      <div class="section-head reveal">
        <h2>Кейсы и <span class="brand-highlight">экспертный опыт</span></h2>
        <p>Мы ежедневно регистрируем товарные знаки, защищаем авторские права, получаем патенты и консультируем.</p>
      </div>
      <div class="case-grid">
        <article class="case-card reveal"><span class="pill">Товарные знаки</span><h3>Работы бюро</h3><p>Примеры работ Лирейт по регистрации и защите интеллектуальной собственности.</p><a class="link-arrow" href="/portfolio">Портфолио ↗</a></article>
        <article class="case-card reveal"><span class="pill">Патенты</span><h3>Отзывы клиентов</h3><p>Что говорят клиенты о работе с бюро.</p><a class="link-arrow" href="/reviews">Отзывы ↗</a></article>
        <article class="case-card reveal"><span class="pill">Экспертный блог</span><h3>Разборы и инструкции</h3><p>Бесплатные решения: как зарегистрировать товарный знак, получить патент, провести проверку торговой марки.</p><a class="link-arrow" href="/news/">Перейти в блог ↗</a></article>
      </div>
    </div>
  </section>

  <section class="section" id="pricing" style="padding-top:0">
    <div class="wrap">
      <div class="section-head reveal">
        <h2>Понятный первый шаг и <span class="brand-highlight">стоимость</span></h2>
        <p>Нет времени искать ответ на страницах сайта? Оставьте заявку, и наши специалисты свяжутся с вами.</p>
      </div>
      <div class="pricing-grid">
        <article class="price-card reveal"><div class="service-num">Первый шаг</div><h3>Бесплатная консультация</h3><div class="price">0 ₽</div><p>Оставьте заявку — специалисты свяжутся с вами и подскажут, что делать дальше.</p><a class="link-arrow" href="<?php echo lireate_home_h(LIREATE_HOME_TELEGRAM_URL); ?>" target="_blank" rel="noopener noreferrer">Получить консультацию ↗</a></article>
        <article class="price-card reveal"><div class="service-num">Товарный знак</div><h3>Регистрация товарного знака с финансовой гарантией</h3><div class="price">от 30000 руб.</div><p>Вы решили самостоятельно зарегистрировать свой товарный знак? Это похвально. В среднем вам понадобится около 100 часов на обучение и изучение требований Роспатента. Если у вас есть время на ошибки, мы желаем вам успехов. Регистрация нужна уже сейчас? Мы готовы помочь.</p><a class="link-arrow" href="/registraciya_tovarnogo_znaka_s_oplatoy_za_rezultat/">Подробнее об услуге ↗</a></article>
      </div>
    </div>
  </section>

  <section class="section process-section" id="how" style="padding-top:0" aria-labelledby="process-heading">
    <div class="wrap reveal">
      <div class="section-head">
        <h2 id="process-heading">Как мы <span class="brand-highlight">работаем</span></h2>
        <p>Вы оставляете заявку, а специалисты Лирейт уточняют ситуацию и предлагают маршрут: товарный знак, патент, договор или регистрация программы ЭВМ.</p>
      </div>
      <div class="process-flow" aria-hidden="true"><span id="processProgress"></span></div>
      <div class="process process-new" role="tablist" aria-label="Этапы работы">
        <button id="step-tab-1" type="button" class="process-card active" role="tab" aria-controls="step-panel" aria-selected="true" tabindex="0" data-step="0">
          <strong>Шаг 01 <span class="process-card-toggle" aria-hidden="true">↗</span></strong>
          <h3>Постановка задачи</h3>
          <p>Разбираемся в ситуации, уточняем объект защиты и предлагаем дальнейший план.</p>
          <span class="process-card-hint">Подробнее об этапе <span aria-hidden="true">→</span></span>
        </button>
        <button id="step-tab-2" type="button" class="process-card" role="tab" aria-controls="step-panel" aria-selected="false" tabindex="-1" data-step="1">
          <strong>Шаг 02 <span class="process-card-toggle" aria-hidden="true">↗</span></strong>
          <h3>Подготовка документов</h3>
          <p>Согласуем условия, готовим материалы и проверяем их перед подачей.</p>
          <span class="process-card-hint">Подробнее об этапе <span aria-hidden="true">→</span></span>
        </button>
        <button id="step-tab-3" type="button" class="process-card" role="tab" aria-controls="step-panel" aria-selected="false" tabindex="-1" data-step="2">
          <strong>Шаг 03 <span class="process-card-toggle" aria-hidden="true">↗</span></strong>
          <h3>Сопровождение до результата</h3>
          <p>Ведём дело, отслеживаем статус и помогаем пройти этапы рассмотрения.</p>
          <span class="process-card-hint">Подробнее об этапе <span aria-hidden="true">→</span></span>
        </button>
      </div>
      <div class="process-detail" id="step-panel" role="tabpanel" aria-labelledby="step-tab-1" tabindex="0" data-current="0">
        <div class="process-detail-top">
          <div class="process-detail-meta"><span class="process-indicator" id="detailCounter">01 / 03</span><span class="process-detail-kicker">Подробно об этапе</span></div>
          <span class="process-detail-mark" aria-hidden="true"><span id="detailMark">01</span></span>
        </div>
        <div class="process-detail-grid" id="processDetailBody">
          <div class="process-detail-main">
            <h3 id="detailTitle">Постановка задачи</h3>
            <p id="detailIntro">Начинаем с понимания вашей ситуации — чтобы выбрать подходящий способ защиты интеллектуальной собственности.</p>
            <div class="process-detail-list-title">Что делает Лирейт</div>
            <ul class="process-task-list" id="detailTasks">
              <li>Выясняем, что именно нужно защитить: бренд, разработку, программу или права по договору.</li>
              <li>Уточняем цели, сроки, текущие документы и вопросы клиента.</li>
              <li>Объясняем возможные пути работы и следующий шаг.</li>
            </ul>
          </div>
          <aside class="process-detail-aside">
            <div class="process-result-label"><span aria-hidden="true">✓</span> Что вы получаете</div>
            <h4 id="detailResult">Понятный план действий</h4>
            <p id="detailResultText">Вы знаете, какие действия предстоят, какие данные понадобятся и как начать работу.</p>
            <a class="process-action" id="detailAction" href="<?php echo lireate_home_h(LIREATE_HOME_TELEGRAM_URL); ?>" target="_blank" rel="noopener noreferrer"><span class="action-label">Обсудить задачу</span> <span aria-hidden="true">↗</span></a>
          </aside>
        </div>
        <div class="process-detail-bottom">
          <span class="process-step-caption">Выберите следующий этап или вернитесь к предыдущему</span>
          <div class="process-step-nav">
            <button type="button" class="process-prev" id="prevProcess" aria-label="Предыдущий этап">←</button>
            <button type="button" class="process-next" id="nextProcess">Следующий шаг <span aria-hidden="true">→</span></button>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="section faq-section" id="faq" style="padding-top:0">
    <div class="wrap">
      <div class="section-head reveal"><h2>Частые <span class="brand-highlight">вопросы</span></h2></div>
      <div class="faq-list">
        <details class="faq-item reveal"><summary>С чего начать регистрацию товарного знака? <span aria-hidden="true">+</span></summary><div class="faq-answer">Начните с <a href="/findtz">проверки товарного знака</a> или консультации специалиста.</div></details>
        <details class="faq-item reveal"><summary>Можно ли запатентовать изобретение или полезную модель? <span aria-hidden="true">+</span></summary><div class="faq-answer">Да, Лирейт занимается патентованием технических решений. <a href="/patent">Подробнее о патентах ↗</a></div></details>
        <details class="faq-item reveal"><summary>Помогаете ли вы с программами ЭВМ? <span aria-hidden="true">+</span></summary><div class="faq-answer">Лирейт оказывает услуги по правовой защите программного кода и интерфейсов. <a href="/registraciya_programm_evm/">Посмотреть услугу ↗</a></div></details>
        <details class="faq-item reveal"><summary>Как получить консультацию? <span aria-hidden="true">+</span></summary><div class="faq-answer">Свяжитесь с командой по телефону <?php echo lireate_home_h(LIREATE_HOME_PHONE_DISPLAY); ?> или <a href="<?php echo lireate_home_h(LIREATE_HOME_TELEGRAM_URL); ?>" target="_blank" rel="noopener noreferrer">в Telegram</a>.</div></details>
      </div>
    </div>
  </section>

  <section class="section" id="blog" style="padding-top:0">
    <div class="wrap">
      <div class="section-head reveal">
        <h2>Блог и <span class="brand-highlight">полезные материалы</span></h2>
        <p>В блоге мы публикуем полезные материалы, делимся решениями — это ценно. Нам не импонируют агрессивные продажи, безумные триггеры и надоедливые звонки.</p>
      </div>
      <div class="blog-grid">
        <article class="blog-card reveal"><div class="num">Материал 01</div><h3>Иск о нарушении товарного знака: требования и доказательства</h3><p>Практические вопросы по защите товарного знака.</p><a class="link-arrow" href="/isk_o_narushenii_tovarnogo_znaka_trebovaniya_i_dokazatelstva">Открыть ↗</a></article>
        <article class="blog-card reveal"><div class="num">Материал 02</div><h3>Компенсация за товарный знак: расчет и доказательства</h3><p>Разбор вопросов, связанных с компенсацией.</p><a class="link-arrow" href="/kompensatsiya_za_narushenie_tovarnogo_znaka_raschet_i_dokazatelstva/">Открыть ↗</a></article>
        <article class="blog-card reveal"><div class="num">Материал 03</div><h3>Как обжаловать решение Роспатента в СИП</h3><p>Материал для тех, кто столкнулся с отказом.</p><a class="link-arrow" href="/kak_obzhalovat_reshenie_rospatenta_v_sude_po_intellektualnym_pravam">Открыть ↗</a></article>
      </div>
    </div>
  </section>

  <section class="cta" id="lh4-contact">
    <div class="wrap">
      <div class="cta-box reveal">
        <div>
          <div class="eyebrow">Консультация</div>
          <h2>Нет времени искать ответ на страницах сайта?</h2>
          <p>Оставьте заявку — специалисты Лирейт свяжутся с вами и помогут понять, какой способ защиты подойдёт в вашей ситуации.</p>
          <div class="cta-list">
            <div><span>✓</span><span>товарные знаки, патенты, программы ЭВМ, договоры</span></div>
            <div><span>✓</span><span>честная оценка перспектив и понятный следующий шаг</span></div>
            <div><span>✓</span><span>быстрый контакт через Telegram, Max и телефон</span></div>
          </div>
        </div>
        <div>
          <form id="lh4-lead-form" class="ajax_form form-unit pb30" method="POST" action="<?php echo lireate_home_h($lh_mail_action); ?>" data-lead-topic="Заявка с главной LIREATE Light V4" novalidate>
            <input type="hidden" name="mail_token" value="503066424d5f7b5ad2097703ef3404d2">
            <input type="hidden" name="fuck_spamers" value="<?php echo lireate_home_h($lh_spam_guard); ?>">
            <input type="hidden" name="mail" value="mailorder">
            <input type="hidden" name="form-type" value="order">
            <input type="hidden" name="tomail" value="Заявка с главной LIREATE Light V4">
            <input type="hidden" name="consent_form" value="home-light-v4">
            <label for="lh4-name">Имя</label>
            <input id="lh4-name" name="name" type="text" required autocomplete="name" placeholder="Как к вам обращаться">
            <label for="lh4-phone">Телефон</label>
            <input id="lh4-phone" name="tel" type="tel" required autocomplete="tel" placeholder="+7 …" inputmode="tel">
            <?php
            $lx_consent_pd_id = 'lh4-consent';
            $lx_consent_mkt_id = 'lh4-consent-mkt';
            $lx_consent_path = lireate_home_consent_fields_inc_path();
            if ($lx_consent_path !== '') {
                include $lx_consent_path;
            } else {
                lireate_home_render_consent_fields($lx_consent_pd_id, $lx_consent_mkt_id);
            }
            ?>
            <button type="submit" class="btn primary lx-form-submit" style="width:100%;margin-top:12px">Отправить заявку</button>
            <p class="lx-form-success" id="lh4-success" role="status" aria-live="polite"></p>
            <p class="lx-form-error" id="lh4-error" role="alert" hidden></p>
          </form>
          <a class="btn soft" style="width:100%;margin:12px 0" href="<?php echo lireate_home_h(LIREATE_HOME_TELEGRAM_URL); ?>" target="_blank" rel="noopener noreferrer" data-lx-ym-goal="perehod_v_telegramm">Написать в Telegram ↗</a>
          <a class="btn soft" style="width:100%;margin-bottom:12px" href="<?php echo lireate_home_h(LIREATE_HOME_MAX_CHAT_URL); ?>" target="_blank" rel="noopener noreferrer" data-lx-ym-goal="perehod_v_messendzher">Написать в Max ↗</a>
          <a class="btn" style="width:100%" href="<?php echo lireate_home_h(LIREATE_HOME_PHONE_TEL); ?>" data-lx-ym-goal="klik_po_nomeru_telefona">Позвонить: <?php echo lireate_home_h(LIREATE_HOME_PHONE_DISPLAY); ?></a>
        </div>
      </div>
    </div>
  </section>
</main>
</div>

<script>window.lireateHomeProcessStages=<?php echo json_encode($lh_stages, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;</script>
<script>
(function(){function s(f){var c=f.querySelector(".lireate-consent-marketing-combined"),m=f.querySelector('input[name="consent_marketing_data"]'),a=f.querySelector('input[name="consent_ads"]');if(!c||!m||!a)return;if(c.checked){m.disabled=false;a.disabled=false;m.value="1";a.value="1";}else{m.disabled=true;a.disabled=true;m.value="";a.value="";}}function b(f){var c=f&&f.querySelector(".lireate-consent-marketing-combined");if(!c)return;c.addEventListener("change",function(){s(f);});s(f);}document.addEventListener("DOMContentLoaded",function(){var f=document.getElementById("lh4-lead-form");if(f)b(f);var ext="/template/js/lireate-home-consent-marketing.js?v=20261125e";var e=document.createElement("script");e.src=ext;e.onerror=function(){};document.body.appendChild(e);});})();
</script>
<?php if (!$lh_preview) : ?>
<script>window.lireateMailSpamGuard=<?php echo json_encode($lh_spam_guard); ?>;</script>
<script src="/template/js/lireate-utm.js?v=20261117b1"></script>
<script src="/registraciya_tovarnogo_znaka/assets/lireate-shared/lireate-lead-mail.js"></script>
<?php endif; ?>
<script src="<?php echo lireate_home_h(LIREATE_HOME_JS_HREF); ?>?v=<?php echo lireate_home_h($lh_v); ?>" defer></script>
<script src="<?php echo lireate_home_h(LIREATE_HOME_LIGHT_V4_JS_HREF); ?>?v=<?php echo lireate_home_h($lh_v); ?>" defer></script>

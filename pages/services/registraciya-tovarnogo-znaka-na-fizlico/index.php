<?php
header('Content-Type: text/html; charset=utf-8');
$path = '/registraciya_tovarnogo_znaka_na_fizicheskoe_lico/';
$host = isset($_SERVER['HTTP_HOST']) ? preg_replace('/[^a-zA-Z0-9.\-:]/', '', $_SERVER['HTTP_HOST']) : '';
$site_root = ($host !== '' ? 'https://' . $host : '');
$canonical = $site_root . $path;
$seo_title = 'Регистрация ТЗ на физлицо — LIREATE';
$seo_description = 'Регистрация товарного знака на физлицо в Роспатенте: кто может подать заявку, сроки 8–12 месяцев, ориентир от 33 000 ₽. Проверка МКТУ и сопровождение LIREATE.';
$og_type = 'website';
$og_image = $site_root . '/local/templates/lireate/images/og-default.jpg';
$lireate_phone_display = '+7 (960) 048-49-57';
$lireate_phone_tel = 'tel:+79600484957';
$lireate_telegram = 'https://t.me/lireate_patent';
$lireate_whatsapp = 'https://wa.me/79600484957';
$lireate_max = 'https://max.ru/u/f9LHodD0cOI8jsPagjibEve2cn5_xPW0X1sf3PhbS5TMijA78DnUeDEpUAI';
$faq_entities = [
  ['Может ли физическое лицо зарегистрировать товарный знак?', 'Да. По ст. 1478 ГК РФ правообладателем может быть гражданин. Заявка подаётся на ФИО и адрес заявителя, без обязательного статуса ИП или ООО.'],
  ['Чем отличается регистрация на физлицо и на ИП?', 'Правовой режим знака одинаковый, меняются реквизиты заявителя и договор сопровождения. При смене статуса на ИП или ООО позже оформляют переход прав по ст. 1490 ГК РФ.'],
  ['Сколько длится регистрация?', 'По материалам LIREATE ориентир 8–12 месяцев: формальная и экспертиза обозначения в Роспатенте, ответы на запросы. Точные сроки зависят от очереди и сложности знака.'],
  ['Сколько стоит регистрация на физлицо?', 'На странице LIREATE указан ориентир от 33 000 ₽ — сумма зависит от числа классов МКТУ и объёма сопровождения. Госпошлины Роспатента оплачиваются отдельно по Положению о пошлинах (сверяйте с rospatent.gov.ru).'],
  ['Нужен ли ИП самозанятому?', 'Для подачи заявки в Роспатент статус предпринимателя не обязателен. Для легальной продажи товаров и услуг действуют отдельные правила налогового и гражданского законодательства — их учитывают до выхода на рынок.'],
  ['Что будет, если не использовать знак?', 'По ст. 1486 ГК РФ заинтересованное лицо вправе оспорить правовую охрану, если знак не использовался непрерывно в течение трёх лет. Оценка — в судебном порядке, исход не гарантируем.'],
  ['Гарантируете ли регистрацию?', 'Нет. Решение принимает Роспатент после экспертизы. Мы готовим заявку, отвечаем на запросы и сопровождаем процедуру без обещания положительного исхода.'],
];
$lx_breadcrumb_current = 'Регистрация ТЗ на физлицо';
$lx_review_filter_services = ['registraciya-tovarnogo-znaka-na-fizlico', 'registraciya-brenda-dlya-marketpleysov', 'prodlenie-tovarnogo-znaka'];
$schema_graph = [
  '@context' => 'https://schema.org',
  '@graph' => [
    ['@type' => 'Service', '@id' => $canonical . '#service', 'name' => 'Регистрация товарного знака на физическое лицо', 'url' => $canonical, 'description' => $seo_description, 'areaServed' => 'RU', 'provider' => ['@id' => $canonical . '#organization']],
    ['@type' => ['LegalService', 'Organization'], '@id' => $canonical . '#organization', 'name' => 'LIREATE', 'url' => $site_root . '/', 'telephone' => '+7-960-048-49-57', 'sameAs' => [$lireate_telegram, $lireate_whatsapp, $lireate_max]],
    ['@type' => 'BreadcrumbList', 'itemListElement' => [
      ['@type' => 'ListItem', 'position' => 1, 'name' => 'Главная', 'item' => $site_root . '/'],
      ['@type' => 'ListItem', 'position' => 2, 'name' => 'Услуги', 'item' => $site_root . '/uslugi/'],
      ['@type' => 'ListItem', 'position' => 3, 'name' => 'Регистрация ТЗ на физлицо', 'item' => $canonical],
    ]],
    ['@type' => 'FAQPage', 'mainEntity' => array_map(static function ($pair) {
      return ['@type' => 'Question', 'name' => $pair[0], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => strip_tags($pair[1])]];
    }, $faq_entities)],
  ],
];
?><!DOCTYPE html>
<html lang="ru" data-lireate-telegram="<?php echo htmlspecialchars($lireate_telegram, ENT_QUOTES, 'UTF-8'); ?>" data-lireate-whatsapp="<?php echo htmlspecialchars($lireate_whatsapp, ENT_QUOTES, 'UTF-8'); ?>" data-lireate-max="<?php echo htmlspecialchars($lireate_max, ENT_QUOTES, 'UTF-8'); ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo htmlspecialchars($seo_title, ENT_QUOTES, 'UTF-8'); ?></title>
  <meta name="description" content="<?php echo htmlspecialchars($seo_description, ENT_QUOTES, 'UTF-8'); ?>">
  <link rel="canonical" href="<?php echo htmlspecialchars($canonical, ENT_QUOTES, 'UTF-8'); ?>">
  <meta property="og:title" content="<?php echo htmlspecialchars($seo_title, ENT_QUOTES, 'UTF-8'); ?>">
  <meta property="og:description" content="<?php echo htmlspecialchars($seo_description, ENT_QUOTES, 'UTF-8'); ?>">
  <meta property="og:url" content="<?php echo htmlspecialchars($canonical, ENT_QUOTES, 'UTF-8'); ?>">
  <meta property="og:type" content="<?php echo htmlspecialchars($og_type, ENT_QUOTES, 'UTF-8'); ?>">
  <meta property="og:image" content="<?php echo htmlspecialchars($og_image, ENT_QUOTES, 'UTF-8'); ?>">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?php echo htmlspecialchars($seo_title, ENT_QUOTES, 'UTF-8'); ?>">
  <meta name="twitter:description" content="<?php echo htmlspecialchars($seo_description, ENT_QUOTES, 'UTF-8'); ?>">
  <link rel="stylesheet" href="assets/lireate-shared/lireate-service-dark.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
  <script type="application/ld+json"><?php echo json_encode($schema_graph, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?></script>
</head>
<body class="lireate-svc-body">
<div class="lx-bg-glow" aria-hidden="true"></div>
<div class="lx-nav-shell">
  <header class="lx-nav-pill" role="banner">
    <a class="lx-logo" href="/">LIREATE</a>
    <nav class="lx-nav-links" aria-label="Разделы страницы">
      <a href="#answer">Ответ</a>
      <a href="#program">Этапы</a>
      <a href="#pricing">Стоимость</a>
      <a href="#faq">FAQ</a>
      <a href="#contact">Заявка</a>
    </nav>
    <a class="lx-btn lx-btn-primary" href="#contact">Консультация</a>
  </header>
</div>

<main id="primary" class="lireate-svc-page registraciya-tovarnogo-znaka-na-fizlico-page" role="main" tabindex="-1">

<?php include __DIR__ . '/blocks/lireate-breadcrumbs.inc.php'; ?>

<section class="lx-hero" id="top">
  <div class="lx-hero-mascot-slot" data-lx-mascot="default"></div>
  <div>
    <p class="lx-eyebrow">LIREATE · товарные знаки · физлицо</p>
    <h1>Регистрация товарного знака на физическое лицо</h1>
    <p class="lx-hero-lead"><strong>Короткий ответ:</strong> да — гражданин может подать заявку в Роспатент и стать правообладателем (ст. 1478 ГК РФ). Ориентир по срокам — 8–12 месяцев, по бюджету на материалах LIREATE — от 33&nbsp;000&nbsp;₽. Решение по заявке принимает ведомство, мы сопровождаем без гарантий исхода.</p>
    <div class="lx-tags"><span>ст. 1478 ГК РФ</span><span>МКТУ</span><span>ФИПС</span><span>8–12 мес.</span></div>

    <div class="lx-hero-cta">
      <a class="lx-btn lx-btn-primary" href="#contact">Оставить заявку</a>
      <a class="lx-btn lx-btn-ghost lx-btn--with-icon" id="hero-telegram" href="<?php echo htmlspecialchars($lireate_telegram, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" data-lx-icon="telegram"><span>Telegram</span></a>
      <a class="lx-btn lx-btn-ghost lx-btn--with-icon" id="hero-whatsapp" href="<?php echo htmlspecialchars($lireate_whatsapp, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" data-lx-icon="whatsapp"><span>WhatsApp</span></a>
      <a class="lx-btn lx-btn-ghost lx-btn--with-icon" id="hero-max" href="<?php echo htmlspecialchars($lireate_max, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" data-lx-icon="max"><span>MAX</span></a>
    </div>

    <div class="lx-hero-mini">
      <div class="lx-mini-stat"><strong class="lx-stat-value">от 33 000 ₽</strong><span>ориентир на странице LIREATE</span></div>
      <div class="lx-mini-stat"><strong class="lx-stat-value">8–12 мес.</strong><span>типичный цикл регистрации</span></div>
      <div class="lx-mini-stat"><strong class="lx-stat-value">без ИП</strong><span>заявка на гражданина</span></div>
    </div>
  </div>
  <div class="lx-glass lx-glass-anim" aria-label="Маршрут регистрации на физлицо">
    <div class="lx-panel-head"><span>Маршрут · ориентиры</span><span class="lx-live">Роспатент</span></div>
    <div class="lx-kpi-row">
      <div class="lx-kpi"><b class="lx-stat-value">проверка</b><small>ФИПС и сходство</small></div>
      <div class="lx-kpi"><b class="lx-stat-value">заявка</b><small>ФИО и адрес заявителя</small></div>
      <div class="lx-kpi"><b class="lx-stat-value">экспертиза</b><small>формальная и по знаку</small></div>
    </div>
    <ul class="lx-feed">
      <li>Подбор классов МКТУ под ваши товары и услуги</li>
      <li>Подача через портал Роспатента / Госуслуги</li>
      <li>Ответы на запросы экспертизы в срок</li>
      <li>Свидетельство и запись в Госреестре при положительном решении</li>
    </ul>
    <div class="lx-route" role="img" aria-label="Этапы регистрации">
      <ol class="lx-route-steps">
        <li><span class="lx-route-node">Поиск</span><span class="lx-route-label">ФИПС</span></li>
        <li><span class="lx-route-node">Заявка</span><span class="lx-route-label">подача</span></li>
        <li><span class="lx-route-node">Эксп.</span><span class="lx-route-label">Роспатент</span></li>
        <li><span class="lx-route-node lx-route-node--wide">Свидетельство</span><span class="lx-route-label">реестр</span></li>
      </ol>
    </div>
  </div>
</section>

<section class="lx-section lx-reveal" id="answer">
  <span class="lx-section-label">Ответ на запрос</span>
  <h2>Можно ли зарегистрировать товарный знак на себя как на физлицо?</h2>
  <p>Да. Гражданин вправе обладать исключительным правом на товарный знак наравне с юридическим лицом и индивидуальным предпринимателем. Это удобно, если вы ещё не открыли ИП или ООО, но хотите заранее занять обозначение, оформить наследование знака или разделить бренд и операционную компанию между учредителями.</p>
  <p>Регистрация не заменяет требования к ведению предпринимательской деятельности: продажа товаров и оказание услуг регулируются налоговым законодательством отдельно. Статус самозанятого или ИП выбирают по факту выручки и режима, а заявку в Роспатент можно подать уже на физическое лицо.</p>
</section>

<section class="lx-section lx-reveal" id="when">
  <span class="lx-section-label">Когда уместно</span>
  <h2>Зачем физлицу товарный знак</h2>
  <div class="lx-carousel" data-lx-carousel tabindex="0" aria-label="Сценарии регистрации на физлицо">
    <button type="button" class="lx-carousel-prev" aria-label="Предыдущий слайд">‹</button>
    <div class="lx-carousel-viewport">
      <ol class="lx-carousel-track">
        <li class="lx-carousel-slide"><article class="lx-card"><h3>Запуск бренда</h3><p>Занять название до открытия ИП и выхода на <a href="/nuzhno_li_registrirovat_brend_dlya_vayldberriz/">маркетплейсы</a>.</p></article></li>
        <li class="lx-carousel-slide"><article class="lx-card"><h3>Автор и блогер</h3><p>Закрепить псевдоним, логотип канала или линейку мерча.</p></article></li>
        <li class="lx-carousel-slide"><article class="lx-card"><h3>Наследование</h3><p>Переоформление прав по ст. 1490 ГК РФ без «обёртки» в ИП.</p></article></li>
        <li class="lx-carousel-slide"><article class="lx-card"><h3>Холдинг</h3><p>Бренд на физлице, операции — в ООО по договору.</p></article></li>
        <li class="lx-carousel-slide"><article class="lx-card"><h3>Проверка ниши</h3><p>Сначала <a href="/findtz/">поиск коллизий</a>, затем заявка.</p></article></li>
      </ol>
    </div>
    <button type="button" class="lx-carousel-next" aria-label="Следующий слайд">›</button>
    <div class="lx-carousel-dots"></div>
  </div>
</section>

<section class="lx-section lx-reveal" id="program">
  <span class="lx-section-label">Как работаем</span>
  <h2>Этапы регистрации товарного знака на физлицо</h2>
  <div class="lx-steps-walk" data-lx-steps-walk>
    <div class="lx-steps-walk-mascot-slot" aria-hidden="true"></div>
    <ol class="lx-steps-walk-list">
      <li class="lx-steps-walk-step"><span class="lx-card-num">01</span><h3>Проверка</h3><p><strong>3–7 дней.</strong> Поиск тождественных и сходных знаков в ФИПС, оценка охраноспособности.</p></li>
      <li class="lx-steps-walk-step"><span class="lx-card-num">02</span><h3>МКТУ</h3><p><strong>2–5 дней.</strong> Классы под реальные товары и услуги, формулировки перечня.</p></li>
      <li class="lx-steps-walk-step"><span class="lx-card-num">03</span><h3>Заявка</h3><p><strong>1–3 дня.</strong> Паспортные данные заявителя, макет, оплата пошлин.</p></li>
      <li class="lx-steps-walk-step"><span class="lx-card-num">04</span><h3>Экспертиза</h3><p><strong>месяцы.</strong> Формальная и по обозначению; ответы на уведомления.</p></li>
      <li class="lx-steps-walk-step"><span class="lx-card-num">05</span><h3>Регистрация</h3><p><strong>по решению.</strong> Свидетельство и публикация в реестре при одобрении.</p></li>
    </ol>
  </div>
</section>

<section class="lx-section lx-reveal" id="details">
  <span class="lx-section-label">Практика</span>
  <h2>Особенности заявки гражданина</h2>
  <p>В заявке указывают ФИО, адрес для переписки и контакты. Макет знака должен соответствовать требованиям Роспатента к разрешению и цветовому сочетанию. Если обозначение описательное или совпадает с чужим знаком в отношении однородных товаров, возможен отказ — поэтому предварительный поиск в открытых реестрах обязателен.</p>
  <p>После регистрации право действует 10 лет с даты подачи заявки (ст. 1491 ГК РФ) и продлевается при уплате пошлин. Использование знака в гражданском обороте должно быть реальным: по общим правилам ст. 1486 ГК РФ неиспользование в течение трёх лет может стать основанием для оспаривания в суде.</p>
  <p>При переходе на ИП или ООО оформляют договор отчуждения или лицензию — иначе расхождение между правообладателем в реестре и продавцом на маркетплейсе создаёт риски в спорах. Сопровождение LIREATE включает консультацию по схеме владения до подачи.</p>
  <p>Госпошлины рассчитываются по Положению о пошлинах за действия Роспатента (ПП РФ № 941). Актуальные суммы и коды платежей публикуются на <a href="https://rospatent.gov.ru/" target="_blank" rel="noopener noreferrer">rospatent.gov.ru</a> — мы не фиксируем тариф вместо ведомства. Услуги патентного сопровождения оплачиваются по договору отдельно от госпошлин.</p>
</section>

<section class="lx-section lx-reveal" id="pricing">
  <span class="lx-section-label">Стоимость</span>
  <h2>Ориентиры по бюджету и срокам</h2>
  <div class="lx-price-grid">
    <div class="lx-price-card"><strong data-lx-count-end="33000" data-lx-count-sep="space" data-lx-count-suffix=" ₽">от 33 000 ₽</strong><span>ориентир стоимости на материалах LIREATE (зависит от классов МКТУ)</span></div>
    <div class="lx-price-card"><strong>8–12 мес.</strong><span>типичный срок процедуры в Роспатенте по опыту LIREATE</span></div>
    <div class="lx-price-card"><strong>по тарифам</strong><span>госпошлины отдельно — сверка с rospatent.gov.ru</span></div>
  </div>
  <p class="lx-calc-note">Цифры не являются публичной офертой. Точная смета — после проверки обозначения и перечня товаров.</p>
</section>

<section class="lx-section lx-reveal" id="risks">
  <span class="lx-section-label">Риски</span>
  <h2>Если отложить регистрацию</h2>
  <div class="lx-risk-grid">
    <div class="lx-risk-item"><h3>Занятие знака третьим лицом</h3><p>Кто первым подал заявку на сходное обозначение, получает преимущество в процедуре.</p></div>
    <div class="lx-risk-item"><h3>Претензия правообладателя</h3><p>Использование чужого знака влечёт требования по ст. 1515 ГК РФ.</p></div>
    <div class="lx-risk-item"><h3>Отказ Роспатента</h3><p>Ошибки в МКТУ и макете затягивают процесс и увеличивают расходы на пошлины.</p></div>
  </div>
</section>

<?php include __DIR__ . '/blocks/lireate-reviews.inc.php'; ?>

<section class="lx-section lx-reveal" id="faq">
  <span class="lx-section-label">FAQ</span>
  <h2>Вопросы о регистрации на физическое лицо</h2>
  <details class="lx-faq"><summary>Может ли физическое лицо зарегистрировать товарный знак?</summary><p>Да. По ст. 1478 ГК РФ правообладателем может быть гражданин. Заявка подаётся на ФИО и адрес заявителя, без обязательного статуса ИП или ООО.</p></details>
  <details class="lx-faq"><summary>Чем отличается регистрация на физлицо и на ИП?</summary><p>Правовой режим знака одинаковый, меняются реквизиты заявителя и договор сопровождения. При смене статуса на ИП или ООО позже оформляют переход прав по ст. 1490 ГК РФ.</p></details>
  <details class="lx-faq"><summary>Сколько длится регистрация?</summary><p>По материалам LIREATE ориентир 8–12 месяцев: формальная и экспертиза обозначения в Роспатенте, ответы на запросы. Точные сроки зависят от очереди и сложности знака.</p></details>
  <details class="lx-faq"><summary>Сколько стоит регистрация на физлицо?</summary><p>На странице LIREATE указан ориентир от 33 000 ₽ — сумма зависит от числа классов МКТУ и объёма сопровождения. Госпошлины Роспатента оплачиваются отдельно по Положению о пошлинах (сверяйте с rospatent.gov.ru).</p></details>
  <details class="lx-faq"><summary>Нужен ли ИП самозанятому?</summary><p>Для подачи заявки в Роспатент статус предпринимателя не обязателен. Для легальной продажи товаров и услуг действуют отдельные правила налогового и гражданского законодательства — их учитывают до выхода на рынок.</p></details>
  <details class="lx-faq"><summary>Что будет, если не использовать знак?</summary><p>По ст. 1486 ГК РФ заинтересованное лицо вправе оспорить правовую охрану, если знак не использовался непрерывно в течение трёх лет. Оценка — в судебном порядке, исход не гарантируем.</p></details>
  <details class="lx-faq"><summary>Гарантируете ли регистрацию?</summary><p>Нет. Решение принимает Роспатент после экспертизы. Мы готовим заявку, отвечаем на запросы и сопровождаем процедуру без обещания положительного исхода.</p></details>
</section>


<section class="lx-section lx-reveal" id="contact">
  <div class="lx-contact">
    <div>
      <span class="lx-section-label">Контакт</span>
      <h2>Заявка и мессенджеры</h2>
      <p>Телефон: <a href="<?php echo htmlspecialchars($lireate_phone_tel, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($lireate_phone_display, ENT_QUOTES, 'UTF-8'); ?></a></p>
      <div class="lx-msg-grid">
        <a class="lx-msg-btn" id="link-telegram" href="<?php echo htmlspecialchars($lireate_telegram, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" data-lx-icon="telegram"><span>Telegram</span></a>
        <a class="lx-msg-btn" id="link-whatsapp" href="<?php echo htmlspecialchars($lireate_whatsapp, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" data-lx-icon="whatsapp"><span>WhatsApp</span></a>
        <a class="lx-msg-btn lx-msg-btn--max" id="link-max" href="<?php echo htmlspecialchars($lireate_max, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" data-lx-icon="max"><span>MAX</span></a>
      </div>
    </div>
    <div class="lx-form-panel">
      <form id="lireate-lead-form" data-lead-topic="Заявка: ТЗ на физлицо" novalidate>
        <label for="lf-name">Имя</label>
        <input id="lf-name" name="name" type="text" required autocomplete="name" placeholder="Как к вам обращаться">
        <label for="lf-phone">Телефон</label>
        <input id="lf-phone" name="phone" type="tel" required autocomplete="tel" placeholder="+7 …">
        <label class="lx-consent"><input type="checkbox" id="lf-consent" required> Согласен на <a href="/private_data" target="_blank" rel="noopener noreferrer">обработку персональных данных</a> и с <a href="/politika_konfidencialnosti" target="_blank" rel="noopener noreferrer">политикой конфиденциальности</a>.</label>
        <button type="submit" class="lx-btn lx-btn-primary" style="width:100%;margin-top:12px;">Получить консультацию</button>
        <div class="lx-form-alt">
          <span class="lx-form-alt-label">или напишите нам</span>
          <div class="lx-form-alt-icons">
            <a id="form-alt-telegram" class="lx-form-alt-link" href="<?php echo htmlspecialchars($lireate_telegram, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" data-lx-icon="telegram" aria-label="Telegram"></a>
            <a id="form-alt-max" class="lx-form-alt-link lx-form-alt-link--max" href="<?php echo htmlspecialchars($lireate_max, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" data-lx-icon="max-white" aria-label="MAX"></a>
          </div>
        </div>
        <p class="lx-form-success" id="lf-success" role="status">Спасибо! Откроем мессенджер с готовым текстом — отправьте сообщение, чтобы заявка дошла до юриста.</p>
      </form>
    </div>
  </div>
</section>

<section class="lx-section lx-reveal" id="related">
  <span class="lx-section-label">LIREATE</span>
  <h2>Смежные услуги</h2>
  <div class="lx-related-grid">
    <a class="lx-related-card" href="/findtz/"><span class="lx-related-card-type">Услуга</span><strong>Проверка товарного знака</strong></a>
    <a class="lx-related-card" href="/registraciya_tovarnogo_znaka/"><span class="lx-related-card-type">Услуга</span><strong>Регистрация товарного знака</strong></a>
    <a class="lx-related-card" href="/nuzhno_li_registrirovat_brend_dlya_vayldberriz/"><span class="lx-related-card-type">Маркетплейс</span><strong>Бренд для Wildberries</strong></a>
    <a class="lx-related-card" href="/prodlenie_tovarnogo_znaka/"><span class="lx-related-card-type">Услуга</span><strong>Продление товарного знака</strong></a>
    <a class="lx-related-card" href="/contacts/"><span class="lx-related-card-type">Контакты</span><strong>Телефон и офис</strong></a>
  </div>
</section>

</main>

<?php include __DIR__ . '/lireate-service-footer.inc.php'; ?>
<?php include __DIR__ . '/blocks/lireate-sticky-mobile.inc.php'; ?>

<aside class="lx-dock" aria-label="Быстрый контакт">
  <a href="#contact" class="lx-dock-lead" title="Оставить заявку" aria-label="Оставить заявку"></a>
  <a id="dock-telegram" href="<?php echo htmlspecialchars($lireate_telegram, ENT_QUOTES, 'UTF-8'); ?>" title="Telegram" aria-label="Telegram" target="_blank" rel="noopener noreferrer"></a>
  <a id="dock-max" class="lx-dock-max" href="<?php echo htmlspecialchars($lireate_max, ENT_QUOTES, 'UTF-8'); ?>" title="MAX" aria-label="MAX" target="_blank" rel="noopener noreferrer"></a>
</aside>

<script src="assets/lireate-shared/lireate-motion-init.js"></script>
<script src="assets/lireate-shared/lireate-reveal.js"></script>
<script src="assets/lireate-shared/lireate-hero-panel.js"></script>
<script src="assets/lireate-shared/lireate-carousel.js"></script>
<script src="assets/lireate-shared/lireate-steps-walk.js"></script>
<script src="assets/lireate-shared/lireate-mascot.js"></script>
<script src="assets/lireate-shared/lireate-service-extras.js"></script>
<script src="assets/lireate-shared/lireate-reviews.js"></script>
<script src="assets/lireate-shared/lireate-mobile-bar.js"></script>
<script src="assets/lireate-shared/lireate-contact.js"></script>
</body>
</html>

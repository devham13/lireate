<?php
header('Content-Type: text/html; charset=utf-8');
$path = '/nuzhno_li_registrirovat_brend_dlya_vayldberriz/';
$host = isset($_SERVER['HTTP_HOST']) ? preg_replace('/[^a-zA-Z0-9.\-:]/', '', $_SERVER['HTTP_HOST']) : '';
$site_root = ($host !== '' ? 'https://' . $host : '');
$canonical = $site_root . $path;
$seo_title = 'Бренд для Wildberries: регистрация — LIREATE';
$seo_description = 'Нужно ли регистрировать бренд для Wildberries: когда нужно свидетельство, риски оферты WB и претензий по ГК РФ. Проверка знака и сопровождение LIREATE.';
$og_type = 'website';
$og_image = $site_root . '/local/templates/lireate/images/og-default.jpg';
$lireate_phone_display = '+7 (960) 048-49-57';
$lireate_phone_tel = 'tel:+79600484957';
$lireate_telegram = 'https://t.me/lireate_patent';
$lireate_whatsapp = 'https://wa.me/79600484957';
$lireate_max = 'https://max.ru/u/f9LHodD0cOI8jsPagjibEve2cn5_xPW0X1sf3PhbS5TMijA78DnUeDEpUAI';
$faq_entities = [
  ['Обязательно ли регистрировать бренд для Wildberries?', 'Закон не требует регистрации, чтобы указать слово в поле «бренд» в карточке. Но без свидетельства в Роспатенте вы не являетесь правообладателем обозначения и не можете полноценно защищать его от копирования.'],
  ['Можно ли продавать на WB без своего товарного знака?', 'Да, если вы не нарушаете чужие знаки и соблюдаете оферту маркетплейса. Для собственного бренда отсутствие регистрации повышает риск претензий и блокировок при споре с правообладателем.'],
  ['Что грозит за чужой бренд без разрешения?', 'По ст. 1515 ГК РФ возможна компенсация от 10 000 до 5 000 000 руб. по усмотрению суда либо в двукратном размере стоимости контрафакта. Площадка может ограничить карточки по жалобе правообладателя.'],
  ['Нужно ли согласие при перепродаже известных марок?', 'Да. Использование чужого знака без лицензии или договора — нарушение исключительного права (ст. 1484 ГК РФ). Документы могут запросить и маркетплейс, и правообладатель.'],
  ['Как проверить бренд до подачи?', 'Сверьте обозначение с реестром ФИПС и открытыми заявками, оцените сходство по классам МКТУ. На LIREATE доступна <a href="/findtz/">проверка товарного знака</a>.'],
  ['Сколько стоит регистрация для селлера?', 'На главной LIREATE указан ориентир услуг от 30 000 ₽; итог зависит от классов МКТУ и сопровождения. Госпошлины — по тарифам Роспатента, оплачиваются отдельно.'],
  ['Гарантируете ли одобрение Роспатента?', 'Нет. Мы помогаем с проверкой, заявкой и ответами на запросы; решение принимает ведомство.'],
];
$lx_breadcrumb_current = 'Бренд для Wildberries';
$lx_review_filter_services = ['registraciya-tovarnogo-znaka-na-fizlico', 'registraciya-brenda-dlya-marketpleysov', 'prodlenie-tovarnogo-znaka'];
$schema_graph = [
  '@context' => 'https://schema.org',
  '@graph' => [
    ['@type' => 'Service', '@id' => $canonical . '#service', 'name' => 'Регистрация бренда для Wildberries', 'url' => $canonical, 'description' => $seo_description, 'areaServed' => 'RU', 'provider' => ['@id' => $canonical . '#organization']],
    ['@type' => ['LegalService', 'Organization'], '@id' => $canonical . '#organization', 'name' => 'LIREATE', 'url' => $site_root . '/', 'telephone' => '+7-960-048-49-57', 'sameAs' => [$lireate_telegram, $lireate_whatsapp, $lireate_max]],
    ['@type' => 'BreadcrumbList', 'itemListElement' => [
      ['@type' => 'ListItem', 'position' => 1, 'name' => 'Главная', 'item' => $site_root . '/'],
      ['@type' => 'ListItem', 'position' => 2, 'name' => 'Услуги', 'item' => $site_root . '/uslugi/'],
      ['@type' => 'ListItem', 'position' => 3, 'name' => 'Бренд для Wildberries', 'item' => $canonical],
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

<main id="primary" class="lireate-svc-page registraciya-brenda-dlya-marketpleysov-page" role="main" tabindex="-1">

<?php include __DIR__ . '/blocks/lireate-breadcrumbs.inc.php'; ?>

<section class="lx-hero" id="top">
  <div class="lx-hero-mascot-slot" data-lx-mascot="default"></div>
  <div>
    <p class="lx-eyebrow">LIREATE · маркетплейсы · Wildberries</p>
    <h1>Нужно ли регистрировать бренд для Wildberries</h1>
    <p class="lx-hero-lead"><strong>Короткий ответ:</strong> закон не обязывает регистрировать бренд, чтобы заполнить поле в карточке WB. Но для <em>собственного</em> названия без свидетельства в Роспатенте вы не правообладатель — рискуете блокировкой, претензией и компенсацией по ст. 1515 ГК РФ. Регистрация — практическая защита, не гарантия одобрения ведомством.</p>
    <div class="lx-tags"><span>ст. 1484 ГК РФ</span><span>ст. 1515</span><span>оферта WB</span><span>ФИПС</span></div>

    <div class="lx-hero-cta">
      <a class="lx-btn lx-btn-primary" href="#contact">Оставить заявку</a>
      <a class="lx-btn lx-btn-ghost lx-btn--with-icon" id="hero-telegram" href="<?php echo htmlspecialchars($lireate_telegram, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" data-lx-icon="telegram"><span>Telegram</span></a>
      <a class="lx-btn lx-btn-ghost lx-btn--with-icon" id="hero-whatsapp" href="<?php echo htmlspecialchars($lireate_whatsapp, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" data-lx-icon="whatsapp"><span>WhatsApp</span></a>
      <a class="lx-btn lx-btn-ghost lx-btn--with-icon" id="hero-max" href="<?php echo htmlspecialchars($lireate_max, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" data-lx-icon="max"><span>MAX</span></a>
    </div>

    <div class="lx-hero-mini">
      <div class="lx-mini-stat"><strong class="lx-stat-value">не обязательно</strong><span>для поля «бренд» в ЛК</span></div>
      <div class="lx-mini-stat"><strong class="lx-stat-value">желательно</strong><span>для своего производства</span></div>
      <div class="lx-mini-stat"><strong class="lx-stat-value">от 30 000 ₽</strong><span>ориентир услуг на lireate.ru</span></div>
    </div>
  </div>
  <div class="lx-glass lx-glass-anim" aria-label="Риски без регистрации на WB">
    <div class="lx-panel-head"><span>Селлер · риски</span><span class="lx-live">ГК РФ</span></div>
    <div class="lx-kpi-row">
      <div class="lx-kpi"><b class="lx-stat-value">жалоба</b><small>правообладателя</small></div>
      <div class="lx-kpi"><b class="lx-stat-value">оферта</b><small>обязанности селлера</small></div>
      <div class="lx-kpi"><b class="lx-stat-value">реестр</b><small>доказательство прав</small></div>
    </div>
    <ul class="lx-feed">
      <li>Проверка бренда в ФИПС до масштабирования продаж</li>
      <li>Регистрация на физлицо, ИП или ООО — по вашей схеме бизнеса</li>
      <li>Лицензия при перепродаже чужих марок</li>
      <li>Ответы на запросы маркетплейса при споре</li>
    </ul>
    <div class="lx-route" role="img" aria-label="Маршрут защиты бренда">
      <ol class="lx-route-steps">
        <li><span class="lx-route-node">Поиск</span><span class="lx-route-label">ФИПС</span></li>
        <li><span class="lx-route-node">Заявка</span><span class="lx-route-label">Роспатент</span></li>
        <li><span class="lx-route-node">WB</span><span class="lx-route-label">карточки</span></li>
        <li><span class="lx-route-node lx-route-node--wide">Защита</span><span class="lx-route-label">претензия</span></li>
      </ol>
    </div>
  </div>
</section>

<section class="lx-section lx-reveal" id="answer">
  <span class="lx-section-label">Ответ на запрос</span>
  <h2>Нужна ли регистрация бренда для торговли на Wildberries?</h2>
  <p>Маркетплейс позволяет указать произвольное слово в поле «бренд» при создании карточки. Это не равно регистрации товарного знака: исключительное право возникает после внесения обозначения в Госреестр (ст. 1481 ГК РФ). Пока свидетельства нет, конкурент может подать заявку на сходное название, а правообладатель чужого знака — потребовать убрать карточки.</p>
  <p>В оферте Wildberries селлер обязан соблюдать права на интеллектуальную собственность и участвовать в урегулировании споров. При нарушении возможны санкции площадки вплоть до блокировки личного кабинета. Регистрация в Роспатенте не отменяет эти правила, но даёт документ для защиты своего обозначения и для ответа на жалобы.</p>
</section>

<section class="lx-section lx-reveal" id="when">
  <span class="lx-section-label">Кому нужно</span>
  <h2>Три типовых сценария селлера</h2>
  <div class="lx-card-grid">
    <article class="lx-card"><h3>Свой бренд и производство</h3><p>Регистрация в Роспатенте настоятельно рекомендуется до масштабирования: вы защищаете название, которое раскручиваете на WB.</p></article>
    <article class="lx-card"><h3>Перепродажа чужих марок</h3><p>Нужны документы на право использования знака (дистрибуция, лицензия). Без них — риск претензии по ст. 1484 ГК РФ.</p></article>
    <article class="lx-card"><h3>Товар без бренда</h3><p>Регистрация не обязательна, но узнаваемость ниже; при росте оборота имеет смысл выделить линейку и <a href="/registraciya_tovarnogo_znaka_na_fizicheskoe_lico/">зарегистрировать знак</a>.</p></article>
  </div>
</section>

<section class="lx-section lx-reveal" id="program">
  <span class="lx-section-label">Как работаем</span>
  <h2>Маршрут: от идеи бренда до карточек на WB</h2>
  <div class="lx-steps-walk" data-lx-steps-walk>
    <div class="lx-steps-walk-mascot-slot" aria-hidden="true"></div>
    <ol class="lx-steps-walk-list">
      <li class="lx-steps-walk-step"><span class="lx-card-num">01</span><h3>Проверка</h3><p><strong>3–7 дней.</strong> ФИПС, сходство, классы под категорию WB.</p></li>
      <li class="lx-steps-walk-step"><span class="lx-card-num">02</span><h3>Заявка</h3><p><strong>1–3 дня.</strong> Физлицо, ИП или ООО — по вашей модели.</p></li>
      <li class="lx-steps-walk-step"><span class="lx-card-num">03</span><h3>Экспертиза</h3><p><strong>месяцы.</strong> Сопровождение запросов Роспатента.</p></li>
      <li class="lx-steps-walk-step"><span class="lx-card-num">04</span><h3>Карточки WB</h3><p><strong>после свидетельства.</strong> Единое имя в реестре и на площадке.</p></li>
    </ol>
  </div>
</section>

<section class="lx-section lx-reveal" id="details">
  <span class="lx-section-label">Правовая рамка</span>
  <h2>Оферта WB и ответственность</h2>
  <p>Wildberries не заменяет Роспатент: площадка не выдаёт права на обозначение. При жалобе правообладателя маркетплейс может запросить документы и ограничить показы. Судебная компенсация за незаконное использование знака определяется по правилам ст. 1515 ГК РФ — диапазон широкий, конкретную сумму в каждом случае назначает суд.</p>
  <p>Перед выходом в нишу проверьте, не занято ли обозначение в ваших классах МКТУ. Совпадение названия в поиске WB не означает, что знак свободен: важен статус в реестре ФИПС. Обратная ситуация тоже встречается: в реестре есть знак, но в другом классе — нужен анализ однородности товаров.</p>
  <p>Интерфейс личного кабинета WB обновляется; шаги добавления бренда в карточку смотрите в актуальной справке площадки. Свидетельство Роспатента в стандартную форму карточки не загружается, но его могут запросить в поддержке при споре — храните PDF и данные регистрации.</p>
  <p>Для экспорта и закупок в КНР дополнительно рассматривают <a href="/registraciya_tovarnogo_znaka_v_kitae/">регистрацию знака в Китае</a> — российское свидетельство не подменяет национальную охрану во всех сценариях.</p>
</section>

<section class="lx-section lx-reveal" id="pricing">
  <span class="lx-section-label">Стоимость</span>
  <h2>Сколько стоит регистрация для селлера</h2>
  <div class="lx-price-grid">
    <div class="lx-price-card"><strong data-lx-count-end="30000" data-lx-count-sep="space" data-lx-count-suffix=" ₽">от 30 000 ₽</strong><span>ориентир услуг LIREATE на главной сайта (без гарантии итоговой сметы)</span></div>
    <div class="lx-price-card"><strong>МКТУ</strong><span>чем шире перечень классов, тем выше пошлины Роспатента</span></div>
    <div class="lx-price-card"><strong>сопровождение</strong><span>проверка, заявка, ответы на запросы — по договору</span></div>
  </div>
  <p class="lx-calc-note">Госпошлины — по тарифам на rospatent.gov.ru. Не является публичной офертой.</p>
</section>

<section class="lx-section lx-reveal" id="risks">
  <span class="lx-section-label">Риски</span>
  <h2>Если не регистрировать свой бренд</h2>
  <div class="lx-risk-grid">
    <div class="lx-risk-item"><h3>Потеря названия</h3><p>Конкурент регистрирует сходное обозначение и требует убрать карточки.</p></div>
    <div class="lx-risk-item"><h3>Блокировка ЛК</h3><p>Жалоба правообладателя и санкции по правилам маркетплейса.</p></div>
    <div class="lx-risk-item"><h3>Компенсация</h3><p>Иски по ст. 1515 ГК РФ — сумма определяется судом.</p></div>
  </div>
</section>

<?php include __DIR__ . '/blocks/lireate-reviews.inc.php'; ?>

<section class="lx-section lx-reveal" id="faq">
  <span class="lx-section-label">FAQ</span>
  <h2>Частые вопросы селлеров Wildberries</h2>
  <details class="lx-faq"><summary>Обязательно ли регистрировать бренд для Wildberries?</summary><p>Закон не требует регистрации, чтобы указать слово в поле «бренд» в карточке. Но без свидетельства в Роспатенте вы не являетесь правообладателем обозначения и не можете полноценно защищать его от копирования.</p></details>
  <details class="lx-faq"><summary>Можно ли продавать на WB без своего товарного знака?</summary><p>Да, если вы не нарушаете чужие знаки и соблюдаете оферту маркетплейса. Для собственного бренда отсутствие регистрации повышает риск претензий и блокировок при споре с правообладателем.</p></details>
  <details class="lx-faq"><summary>Что грозит за чужой бренд без разрешения?</summary><p>По ст. 1515 ГК РФ возможна компенсация от 10 000 до 5 000 000 руб. по усмотрению суда либо в двукратном размере стоимости контрафакта. Площадка может ограничить карточки по жалобе правообладателя.</p></details>
  <details class="lx-faq"><summary>Нужно ли согласие при перепродаже известных марок?</summary><p>Да. Использование чужого знака без лицензии или договора — нарушение исключительного права (ст. 1484 ГК РФ). Документы могут запросить и маркетплейс, и правообладатель.</p></details>
  <details class="lx-faq"><summary>Как проверить бренд до подачи?</summary><p>Сверьте обозначение с реестром ФИПС и открытыми заявками, оцените сходство по классам МКТУ. На LIREATE доступна <a href="/findtz/">проверка товарного знака</a>.</p></details>
  <details class="lx-faq"><summary>Сколько стоит регистрация для селлера?</summary><p>На главной LIREATE указан ориентир услуг от 30 000 ₽; итог зависит от классов МКТУ и сопровождения. Госпошлины — по тарифам Роспатента, оплачиваются отдельно.</p></details>
  <details class="lx-faq"><summary>Гарантируете ли одобрение Роспатента?</summary><p>Нет. Мы помогаем с проверкой, заявкой и ответами на запросы; решение принимает ведомство.</p></details>
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
      <form id="lireate-lead-form" data-lead-topic="Заявка: бренд для Wildberries" novalidate>
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
    <a class="lx-related-card" href="/registraciya_tovarnogo_znaka_na_fizicheskoe_lico/"><span class="lx-related-card-type">Услуга</span><strong>ТЗ на физлицо</strong></a>
    <a class="lx-related-card" href="/registraciya_tovarnogo_znaka/"><span class="lx-related-card-type">Услуга</span><strong>Регистрация товарного знака</strong></a>
    <a class="lx-related-card" href="/registraciya_tovarnogo_znaka_v_kitae/"><span class="lx-related-card-type">Услуга</span><strong>Регистрация в Китае</strong></a>
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

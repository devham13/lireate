<?php
header('Content-Type: text/html; charset=utf-8');
$path = '/prodlenie_tovarnogo_znaka/';
$host = isset($_SERVER['HTTP_HOST']) ? preg_replace('/[^a-zA-Z0-9.\-:]/', '', $_SERVER['HTTP_HOST']) : '';
$site_root = ($host !== '' ? 'https://' . $host : '');
$canonical = $site_root . $path;
$seo_title = 'Продление товарного знака — LIREATE';
$seo_description = 'Продление товарного знака в Роспатенте: сроки по ст. 1491 ГК РФ, пошлины п. 2.22, онлайн-подача, риски пропуска дедлайна. Сопровождение LIREATE.';
$og_type = 'website';
$og_image = $site_root . '/local/templates/lireate/images/og-default.jpg';
$lireate_phone_display = '+7 (960) 048-49-57';
$lireate_phone_tel = 'tel:+79600484957';
$lireate_telegram = 'https://t.me/lireate_patent';
$lireate_whatsapp = 'https://wa.me/79600484957';
$lireate_max = 'https://max.ru/u/f9LHodD0cOI8jsPagjibEve2cn5_xPW0X1sf3PhbS5TMijA78DnUeDEpUAI';
$faq_entities = [
  ['Какой срок действия товарного знака в России?', 'По общему правилу ст. 1491 ГК РФ исключительное право на товарный знак действует десять лет с даты подачи заявки в Роспатент. Срок можно продлевать неограниченное число раз — каждый раз на десять лет при соблюдении процедуры и уплате пошлин.'],
  ['Когда нужно подавать заявление на продление товарного знака?', 'Заявление подают в течение последнего года действия исключительного права. Если этот год пропущен, возможна подача в дополнительный шестимесячный срок после истечения десятилетия — при уплате пошлины за рассмотрение ходатайства (п. 2.25 приложения № 1 к Положению о пошлинах) и соблюдении иных требований регламента Роспатента.'],
  ['Сколько стоит продление товарного знака в Роспатенте?', 'Базовая пошлина по п. 2.22 приложения № 1 к Постановлению Правительства РФ от 10.12.2008 № 941: 22 000 руб. за рассмотрение заявления о продлении плюс 2 000 руб. за каждый класс МКТУ свыше пяти и 500 руб. за каждое наименование товара или услуги в классе свыше десяти. Отдельно — 3 000 руб. за выдачу свидетельства на бумажном носителе по ходатайству. Сверяйте суммы с таблицей на rospatent.gov.ru.'],
  ['Можно ли продлить товарный знак онлайн?', 'Да. Роспатент принимает заявления в электронной форме через сервисы на портале ведомства и через Единый портал госуслуг. Перед подачей нужно корректно рассчитать и уплатить пошлины с указанием номера свидетельства и кода юридически значимого действия.'],
  ['Что будет, если не продлить товарный знак вовремя?', 'После истечения срока действия и без успешного продления исключительное право прекращается. Обозначение может стать доступным для регистрации третьими лицами. Пропуск последнего года частично компенсируется шестимесячным льготным периодом, но он требует отдельного ходатайства и дополнительной пошлины.'],
  ['Нужно ли менять перечень товаров и услуг при продлении?', 'Продление сохраняет охрану в отношении тех классов и формулировок, которые указаны в свидетельстве и реестре. Существенное сужение или расширение перечня — отдельные процедуры. На старте мы сверяем действующий перечень с вашим ассортиментом.'],
  ['Как узнать дату окончания срока действия знака?', 'Ориентир — дата подачи заявки плюс десять лет с учётом всех ранее продлений. Точную дату смотрите в свидетельстве, выписке из Госреестра на сайте ФИПС и в сервисах Роспатента. Рекомендуем напоминание за 12–18 месяцев до дедлайна.'],
  ['Гарантируете ли вы продление товарного знака?', 'Нет. Решение принимает Роспатент после проверки документов и оплаты. Мы сопровождаем расчёт пошлин, подготовку заявления и контроль сроков, но не можем гарантировать положительный исход при нарушении формальных требований.'],
];
$schema_graph = [
  '@context' => 'https://schema.org',
  '@graph' => [
    [
      '@type' => 'Service',
      '@id' => $canonical . '#service',
      'name' => 'Продление товарного знака',
      'url' => $canonical,
      'description' => $seo_description,
      'areaServed' => 'RU',
      'provider' => ['@id' => $canonical . '#organization'],
    ],
    [
      '@type' => ['LegalService', 'Organization'],
      '@id' => $canonical . '#organization',
      'name' => 'LIREATE',
      'url' => $site_root . '/',
      'telephone' => '+7-960-048-49-57',
      'sameAs' => [$lireate_telegram, $lireate_whatsapp, $lireate_max],
    ],
    [
      '@type' => 'BreadcrumbList',
      'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Главная', 'item' => $site_root . '/'],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Услуги', 'item' => $site_root . '/uslugi/'],
        ['@type' => 'ListItem', 'position' => 3, 'name' => 'Продление товарного знака', 'item' => $canonical],
      ],
    ],
    [
      '@type' => 'FAQPage',
      'mainEntity' => array_map(static function ($pair) {
        return [
          '@type' => 'Question',
          'name' => $pair[0],
          'acceptedAnswer' => ['@type' => 'Answer', 'text' => $pair[1]],
        ];
      }, $faq_entities),
    ],
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
      <a href="#legal">Сроки</a>
      <a href="#program">Этапы</a>
      <a href="#duties">Пошлины</a>
      <a href="#faq">FAQ</a>
      <a href="#contact">Заявка</a>
    </nav>
    <a class="lx-btn lx-btn-primary" href="#contact">Консультация</a>
  </header>
</div>

<main id="primary" class="lireate-svc-page prodlenie-tovarnogo-znaka-page" role="main" tabindex="-1">

<section class="lx-hero" id="top">
  <div>
    <p class="lx-eyebrow">LIREATE · товарные знаки · Роспатент · продление</p>
    <h1>Продление товарного знака</h1>
    <p class="lx-hero-lead">Сохраняем охрану бренда без сюрпризов в дедлайн: сверяем дату окончания срока по свидетельству и реестру, рассчитываем пошлины по актуальной таблице Роспатента, готовим заявление и сопровождаем электронную подачу. Без гарантий исхода ведомства — с прозрачным маршрутом и напоминаниями о рисках пропуска срока.</p>
    <div class="lx-tags" aria-label="Темы услуги">
      <span>ст. 1491 ГК РФ</span><span>10 лет</span><span>п. 2.22 пошлины</span><span>ФИПС</span><span>Госуслуги</span>
    </div>
    <div class="lx-hero-cta">
      <a class="lx-btn lx-btn-primary" href="#contact">Оставить заявку</a>
      <a class="lx-btn lx-btn-ghost lx-btn--with-icon" id="hero-telegram" href="<?php echo htmlspecialchars($lireate_telegram, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" data-lx-icon="telegram"><span>Написать в Telegram</span></a>
      <a class="lx-btn lx-btn-ghost lx-btn--with-icon" id="hero-max" href="<?php echo htmlspecialchars($lireate_max, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" data-lx-icon="max"><span>Написать в Max</span></a>
    </div>
    <div class="lx-hero-mini">
      <div class="lx-mini-stat"><strong class="lx-stat-value">10 лет</strong><span>каждое продление</span></div>
      <div class="lx-mini-stat"><strong class="lx-stat-value">12 мес.</strong><span>окно подачи в последний год</span></div>
      <div class="lx-mini-stat"><strong class="lx-stat-value">+6 мес.</strong><span>льготный период с ходатайством</span></div>
    </div>
  </div>
  <div class="lx-glass" aria-label="Ориентиры по продлению товарного знака">
    <div class="lx-panel-head">
      <span>Календарь · ориентиры</span>
      <span class="lx-live">Роспатент</span>
    </div>
    <div class="lx-kpi-row">
      <div class="lx-kpi"><b class="lx-stat-value">п. 2.22</b><small>базовая пошлина 22 000 ₽</small></div>
      <div class="lx-kpi"><b class="lx-stat-value">п. 2.25</b><small>ходатайство 3 000 ₽</small></div>
      <div class="lx-kpi"><b class="lx-stat-value">онлайн</b><small>подача через портал ведомства</small></div>
    </div>
    <ul class="lx-feed">
      <li>Проверка даты окончания срока в свидетельстве и реестре</li>
      <li>Расчёт пошлин по числу классов МКТУ и позиций перечня</li>
      <li>Подача заявления о продлении в установленные сроки</li>
      <li>Запись о продлении в Госреестр и обновление портфеля</li>
    </ul>
    <div class="lx-route" role="img" aria-label="Маршрут продления: от календаря до записи в реестре">
      <ol class="lx-route-steps">
        <li><span class="lx-route-node">Срок</span><span class="lx-route-label">календарь</span></li>
        <li><span class="lx-route-node">₽</span><span class="lx-route-label">пошлины</span></li>
        <li><span class="lx-route-node">ФИПС</span><span class="lx-route-label">подача</span></li>
        <li><span class="lx-route-node lx-route-node--wide">Роспатент</span><span class="lx-route-label">решение</span></li>
        <li><span class="lx-route-node">+10</span><span class="lx-route-label">лет охраны</span></li>
      </ol>
    </div>
  </div>
</section>

<section class="lx-section lx-reveal" id="legal">
    <span class="lx-section-label">Правовая база</span>
    <h2>Срок действия товарного знака по ГК РФ</h2>
    <p>Согласно <a href="https://www.consultant.ru/document/cons_doc_LAW_64629/b750f79795c779e92bb91e22a676aa4017c4f459/" rel="noopener noreferrer" target="_blank">статье 1491 части четвёртой ГК РФ</a>, срок действия исключительного права на товарный знак составляет десять лет с даты подачи заявки на государственную регистрацию. Продление возможно по заявлению правообладателя — каждый раз на десять лет, без ограничения числа продлений, если соблюдены сроки подачи и уплачены пошлины.</p>
    <p>Заявление о продлении подают в течение последнего года действия права. Если этот период упущен, закон допускает подачу в течение шести месяцев после истечения срока, но при условии уплаты повышенной пошлины за рассмотрение ходатайства о предоставлении дополнительного срока (п. 2.25 приложения № 1 к <a href="https://base.garant.ru/12163962/" rel="noopener noreferrer" target="_blank">Положению о пошлинах</a>, утв. Постановлением Правительства РФ № 941).</p>
    <p>Продление не заменяет регистрацию нового обозначения: если вы сменили бренд, сузили бизнес или правообладатель изменился без договора, отдельно проверьте цепочку прав. Для первичной охраны см. <a href="/registraciya_tovarnogo_znaka/">регистрацию товарного знака</a>, для оценки коллизий до подачи — <a href="/findtz/">проверку товарного знака</a>.</p>
</section>

<section class="lx-section lx-reveal" id="timing">
    <span class="lx-section-label">Сроки</span>
    <h2>Когда подавать заявление на продление</h2>
    <p>На практике удобно делить календарь на три зоны. За 18–12 месяцев до конца десятилетия — аудит: сверяем свидетельство, выписку ФИПС, договоры лицензии и фактическое использование знака. За 12–1 месяц до дедлайна — расчёт пошлин и подготовка платёжных документов. В последний год действия права — подача заявления о продлении; именно этот интервал прямо назван в <a href="https://rospatent.gov.ru/ru/stateservices/prodlenie-sroka-deystviya-isklyuchitelnogo-prava-na-tovarnyy-znak" rel="noopener noreferrer" target="_blank">описании госуслуги Роспатента</a>.</p>
    <p>Если последний год пропущен, включается «льготные» шесть месяцев: сначала подаётся ходатайство о дополнительном сроке (пошлина по п. 2.25 — 3 000 руб. по таблице на <a href="https://rospatent.gov.ru/ru/activities/dues/table" rel="noopener noreferrer" target="_blank">rospatent.gov.ru</a>), затем — заявление о продлении с пошлиной по п. 2.22. Пропуск и этого окна означает прекращение права: знак перестаёт охраняться, а восстановление возможно только в исключительных процедурах, если они применимы к вашей ситуации.</p>
    <p>Для группы компаний важно, чтобы заявление подал действующий правообладатель по реестру. Если за время жизни знака были сделки по отчуждению или лицензии, мы проверяем, не осталось ли расхождений между договором и Госреестром — иначе продление могут приостановить до устранения формальных несоответствий.</p>
</section>

<section class="lx-section lx-reveal" id="duties">
    <span class="lx-section-label">Пошлины</span>
    <h2>Размер пошлин Роспатента за продление</h2>
    <p>Размеры патентных пошлин установлены приложением № 1 к Положению о пошлинах (Постановление Правительства РФ от 10.12.2008 № 941, с изменениями; актуальная редакция — на <a href="https://base.garant.ru/12163962/" rel="noopener noreferrer" target="_blank">ГАРАНТ</a> и в <a href="https://rospatent.gov.ru/ru/activities/dues/table" rel="noopener noreferrer" target="_blank">таблице Роспатента</a>). Для продления обычного товарного знака ключевой пункт — <strong>2.22</strong>:</p>
    <ul class="lx-checklist">
      <li><strong>22 000 руб.</strong> — рассмотрение заявления о продлении срока действия исключительного права на товарный знак и принятие решения;</li>
      <li><strong>+ 2 000 руб.</strong> — за каждый класс МКТУ, для которых предоставляется правовая охрана, <em>свыше пяти</em>;</li>
      <li><strong>+ 500 руб.</strong> — за каждое наименование товара или услуги в перечне в одном классе, <em>свыше десяти</em>;</li>
      <li><strong>+ 3 000 руб.</strong> — выдача свидетельства на бумажном носителе по ходатайству правообладателя (если нужен бумажный экземпляр).</li>
    </ul>
    <p>Если заявление о продлении подаётся после истечения последнего года действия права, дополнительно уплачивается пошлина по <strong>п. 2.25</strong> — <strong>3 000 руб.</strong> за рассмотрение ходатайства о предоставлении шестимесячного срока для подачи заявления о продлении.</p>
    <p>Для коллективного знака применяется отдельный пункт <strong>2.23</strong> (30 000 руб. + 2 000 руб. за класс свыше пяти). Мы рассчитываем сумму по вашему свидетельству и не округляем «в меньшую сторону»: недоплата — частая причина приостановки.</p>
    <p>Услуги LIREATE по сопровождению оплачиваются отдельно от госпошлин; итоговая смета зависит от числа знаков в портфеле и необходимости срочной подачи в льготном полугодии.</p>
</section>

<section class="lx-section lx-reveal" id="online">
    <span class="lx-section-label">Подача</span>
    <h2>Как подать заявление на продление онлайн</h2>
    <p>Роспатент принимает заявления о продлении в электронной форме. Официальный маршрут описан на странице госуслуги <a href="https://rospatent.gov.ru/ru/stateservices/prodlenie-sroka-deystviya-isklyuchitelnogo-prava-na-tovarnyy-znak" rel="noopener noreferrer" target="_blank">«Продление срока действия исключительного права на товарный знак»</a>: подготовка платёжного документа с указанием номера свидетельства, портального номера (при подаче через Госуслуги) и кода юридически значимого действия, затем заполнение формы заявления и приложение подтверждения оплаты.</p>
    <p>До подачи имеет смысл получить актуальную выписку из Госреестра на <a href="https://www.fips.ru/" rel="noopener noreferrer" target="_blank">сайте ФИПС</a> и сверить правообладателя, перечень классов и формулировки товаров и услуг. Если бизнес ушёл в новые категории, одного продления может быть недостаточно — обсудим, нужна ли подача новой заявки или иные изменения.</p>
    <p>Мы не подменяем личный кабинет правообладателя, но готовим чек-лист платежа, текст сопроводительных данных и контролируем, чтобы заявление ушло в пределах последнего года или льготных шести месяцев.</p>
</section>

<section class="lx-section lx-reveal" id="program">
    <span class="lx-section-label">Маршрут</span>
    <h2>Этапы сопровождения продления</h2>
    <p>Ниже — типовой маршрут для одного свидетельства. Сроки этапов зависят от загрузки Роспатента и полноты оплаты; фиксированную дату записи в реестре ведомство не обещает.</p>
    <div class="lx-card-grid">
      <article class="lx-card lx-reveal">
        <span class="lx-card-num">01</span>
        <h3>Календарь и выписка</h3>
        <p><strong>1–3 рабочих дня.</strong> Сверяем дату окончания срока, номер свидетельства, правообладателя и перечень МКТУ по ФИПС и вашим документам.</p>
      </article>
      <article class="lx-card lx-reveal">
        <span class="lx-card-num">02</span>
        <h3>Аудит использования</h3>
        <p><strong>3–5 дней.</strong> Проверяем, совпадает ли реальный ассортимент с перечнем; при рисках коллизий рекомендуем <a href="/findtz/">проверку обозначения</a> параллельно с продлением.</p>
      </article>
      <article class="lx-card lx-reveal">
        <span class="lx-card-num">03</span>
        <h3>Расчёт пошлин</h3>
        <p><strong>1–2 дня.</strong> Считаем платежи по п. 2.22 (и 2.25 при просрочке последнего года), готовим реквизиты и пояснения для бухгалтерии.</p>
      </article>
      <article class="lx-card lx-reveal">
        <span class="lx-card-num">04</span>
        <h3>Подача заявления</h3>
        <p><strong>1 день.</strong> Заполняем форму на портале Роспатента / Госуслуг, прикладываем подтверждение оплаты, фиксируем исходящий номер.</p>
      </article>
      <article class="lx-card lx-reveal">
        <span class="lx-card-num">05</span>
        <h3>Рассмотрение</h3>
        <p><strong>по регламенту ведомства.</strong> Отслеживаем статус, отвечаем на запросы при неполной оплате или несоответствии данных реестру.</p>
      </article>
      <article class="lx-card lx-reveal">
        <span class="lx-card-num">06</span>
        <h3>Реестр и напоминания</h3>
        <p><strong>после решения.</strong> Контролируем публикацию о продлении, обновляем внутренний реестр ИС и ставим напоминание на следующее десятилетие.</p>
      </article>
    </div>
</section>

<section class="lx-section lx-reveal" id="client">
    <span class="lx-section-label">От вас</span>
    <h2>Что нужно от правообладателя</h2>
    <ul class="lx-checklist">
      <li>Номер свидетельства и дата подачи исходной заявки (или скан свидетельства).</li>
      <li>Реквизиты правообладателя, совпадающие с Госреестром; доверенность, если заявление подаёт представитель.</li>
      <li>Актуальный перечень классов МКТУ — для проверки расчёта пошлин.</li>
      <li>Подтверждение оплаты пошлин (платёжный документ с кодом юридически значимого действия).</li>
      <li>Контакт ответственного лица для оперативных уточнений по срокам.</li>
    </ul>
    <p>Если право получено по договору отчуждения, приложите сведения о регистрации перехода права. Для брендов, выходящих на рынок КНР, обсудим синхронизацию с <a href="/registraciya_tovarnogo_znaka_v_kitae/">регистрацией товарного знака в Китае</a> — продление в РФ не продлевает охрану за рубежом.</p>
</section>

<section class="lx-section lx-reveal" id="risks">
    <span class="lx-section-label">Риски</span>
    <h2>Что будет, если пропустить срок продления</h2>
    <p>Исключительное право прекращается. Третьи лица могут подать заявку на сходное обозначение; доказать «старые» права после истечения срока, как правило, нельзя. Для бизнеса это риск смены юридической «оболочки» бренда: договоры, домены, маркетплейсы и рекламные кабинеты завязаны на регистрацию.</p>
    <p>Даже в льготных шести месяцах остаются два платежа (ходатайство и продление) и жёсткий дедлайн. Ошибка в реквизитах платежа или неверный код действия в платёжке приводит к приостановке — а время в льготном периоде не бесконечно.</p>
    <p>Мы не обещаем «спасение» знака после полного истечения срока без оснований, но помогаем заранее: напоминания, расчёт бюджета пошлин и параллельная стратегия, если часть брендов в портфеле созрела на продление в одном квартале.</p>
</section>

<section class="lx-section lx-reveal" id="portfolio">
    <span class="lx-section-label">Портфель</span>
    <h2>Несколько знаков и линейки брендов</h2>
    <p>У компаний с сеткой торговых марок, логотипами коллекций и отдельными знаками для франшизы даты продления редко совпадают. Имеет смысл вести единый реестр: номер свидетельства, дата окончания, ответственный, статус оплаты. LIREATE может сопровождать пакетное продление — с отдельным расчётом пошлины по каждому пункту 2.22.</p>
    <p>Если знак давно не используется, продление не всегда экономически оправдано: иногда дешевле подать новую заявку на актуальное обозначение после <a href="/findtz/">проверки</a>, чем платить пошлины за широкий устаревший перечень. Решение принимается на основе бизнес-планов, а не «по привычке».</p>
    <p>Для экспорта не забывайте о зарубежных юрисдикциях: продление российского свидетельства не заменяет продление или поддержание международной регистрации. При выходе в Китай см. отдельный маршрут на странице про <a href="/registraciya_tovarnogo_znaka_v_kitae/">регистрацию в КНР</a>.</p>
</section>

<section class="lx-section lx-reveal" id="faq">
    <span class="lx-section-label">FAQ</span>
    <h2>Частые вопросы о продлении товарного знака</h2>
    <details class="lx-faq"><summary>Какой срок действия товарного знака в России?</summary><p>По общему правилу ст. 1491 ГК РФ исключительное право на товарный знак действует десять лет с даты подачи заявки в Роспатент. Срок можно продлевать неограниченное число раз — каждый раз на десять лет при соблюдении процедуры и уплате пошлин.</p></details>
    <details class="lx-faq"><summary>Когда нужно подавать заявление на продление товарного знака?</summary><p>Заявление подают в течение последнего года действия исключительного права. Если этот год пропущен, возможна подача в дополнительный шестимесячный срок после истечения десятилетия — при уплате пошлины за рассмотрение ходатайства (п. 2.25 приложения № 1 к Положению о пошлинах) и соблюдении иных требований регламента Роспатента.</p></details>
    <details class="lx-faq"><summary>Сколько стоит продление товарного знака в Роспатенте?</summary><p>Базовая пошлина по п. 2.22 приложения № 1 к Постановлению Правительства РФ от 10.12.2008 № 941: 22 000 руб. за рассмотрение заявления о продлении плюс 2 000 руб. за каждый класс МКТУ свыше пяти и 500 руб. за каждое наименование товара или услуги в классе свыше десяти. Отдельно — 3 000 руб. за выдачу свидетельства на бумажном носителе по ходатайству. Сверяйте суммы с таблицей на rospatent.gov.ru.</p></details>
    <details class="lx-faq"><summary>Можно ли продлить товарный знак онлайн?</summary><p>Да. Роспатент принимает заявления в электронной форме через сервисы на портале ведомства и через Единый портал госуслуг. Перед подачей нужно корректно рассчитать и уплатить пошлины с указанием номера свидетельства и кода юридически значимого действия.</p></details>
    <details class="lx-faq"><summary>Что будет, если не продлить товарный знак вовремя?</summary><p>После истечения срока действия и без успешного продления исключительное право прекращается. Обозначение может стать доступным для регистрации третьими лицами. Пропуск последнего года частично компенсируется шестимесячным льготным периодом, но он требует отдельного ходатайства и дополнительной пошлины.</p></details>
    <details class="lx-faq"><summary>Нужно ли менять перечень товаров и услуг при продлении?</summary><p>Продление сохраняет охрану в отношении тех классов и формулировок, которые указаны в свидетельстве и реестре. Существенное сужение или расширение перечня — отдельные процедуры. На старте мы сверяем действующий перечень с вашим ассортиментом.</p></details>
    <details class="lx-faq"><summary>Как узнать дату окончания срока действия знака?</summary><p>Ориентир — дата подачи заявки плюс десять лет с учётом всех ранее продлений. Точную дату смотрите в свидетельстве, выписке из Госреестра на сайте ФИПС и в сервисах Роспатента. Рекомендуем напоминание за 12–18 месяцев до дедлайна.</p></details>
    <details class="lx-faq"><summary>Гарантируете ли вы продление товарного знака?</summary><p>Нет. Решение принимает Роспатент после проверки документов и оплаты. Мы сопровождаем расчёт пошлин, подготовку заявления и контроль сроков, но не можем гарантировать положительный исход при нарушении формальных требований.</p></details>
</section>

<section class="lx-section lx-reveal" id="contact">
  <div class="lx-contact">
    <div>
      <span class="lx-section-label">Контакт</span>
      <h2>Заявка и мессенджеры</h2>
      <p>Оставьте бриф в форме или напишите в мессенджер — укажите номер свидетельства и ориентировочную дату окончания срока. Юрист вернётся с расчётом пошлин и планом подачи.</p>
      <p>Телефон: <a href="<?php echo htmlspecialchars($lireate_phone_tel, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($lireate_phone_display, ENT_QUOTES, 'UTF-8'); ?></a></p>
      <div class="lx-msg-grid" id="messenger-grid">
        <a class="lx-msg-btn" id="link-telegram" href="<?php echo htmlspecialchars($lireate_telegram, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" data-lx-icon="telegram"><span>Telegram</span></a>
        <a class="lx-msg-btn" id="link-whatsapp" href="<?php echo htmlspecialchars($lireate_whatsapp, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" data-lx-icon="whatsapp"><span>WhatsApp</span></a>
        <a class="lx-msg-btn lx-msg-btn--max" id="link-max" href="<?php echo htmlspecialchars($lireate_max, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" data-lx-icon="max"><span>MAX</span></a>
        <a class="lx-msg-btn" href="/contacts/" data-lx-icon="lead"><span>Контакты</span></a>
      </div>
    </div>
    <div class="lx-form-panel">
      <form id="lireate-lead-form" data-lead-topic="Заявка: продление ТЗ" novalidate>
        <label for="lf-name">Имя</label>
        <input id="lf-name" name="name" type="text" required autocomplete="name" placeholder="Как к вам обращаться">
        <label for="lf-phone">Телефон</label>
        <input id="lf-phone" name="phone" type="tel" required autocomplete="tel" placeholder="+7 …">
        <label class="lx-consent"><input type="checkbox" id="lf-consent" required> Согласен на обработку персональных данных для ответа на обращение (<a href="/privacy/">политика</a>).</label>
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
  <ul class="lx-related-links">
    <li><a href="/findtz/">Проверка товарного знака</a></li>
    <li><a href="/registraciya_tovarnogo_znaka/">Регистрация товарного знака в России</a></li>
    <li><a href="/registraciya_tovarnogo_znaka_v_kitae/">Регистрация товарного знака в Китае</a></li>
    <li><a href="/contacts/">Контакты</a></li>
  </ul>
</section>

<section class="lx-section lx-reveal">
    <p class="lx-consent" style="max-width:720px;">Информация на странице не является публичной офертой и не заменяет консультацию юриста. Размеры пошлин приведены по приложению № 1 к Положению о пошлинах (ПП РФ № 941) и таблице Роспатента; перед оплатой сверяйте актуальные суммы на rospatent.gov.ru. Решения Роспатента зависят от обстоятельств дела.</p>
</section>

</main>

<aside class="lx-dock" aria-label="Быстрый контакт">
  <a href="#contact" class="lx-dock-lead" title="Оставить заявку" aria-label="Оставить заявку"></a>
  <a id="dock-telegram" href="<?php echo htmlspecialchars($lireate_telegram, ENT_QUOTES, 'UTF-8'); ?>" title="Telegram" aria-label="Telegram" target="_blank" rel="noopener noreferrer"></a>
  <a id="dock-max" class="lx-dock-max" href="<?php echo htmlspecialchars($lireate_max, ENT_QUOTES, 'UTF-8'); ?>" title="MAX" aria-label="MAX" target="_blank" rel="noopener noreferrer"></a>
</aside>

<script src="assets/lireate-shared/lireate-reveal.js"></script>
<script src="assets/lireate-shared/lireate-contact.js"></script>
</body>
</html>

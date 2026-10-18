<?php
header('Content-Type: text/html; charset=utf-8');
$path = '/registraciya_tovarnogo_znaka_v_kitae/';
$host = isset($_SERVER['HTTP_HOST']) ? preg_replace('/[^a-zA-Z0-9.\-:]/', '', $_SERVER['HTTP_HOST']) : '';
$site_root = ($host !== '' ? 'https://' . $host : '');
$canonical = $site_root . $path;
$seo_title = 'Регистрация товарного знака в Китае — LIREATE';
$seo_description = 'Подача и сопровождение регистрации товарного знака в КНР: классы МКТУ, перевод, CNIPA, риски для экспорта и маркетплейсов. Консультация LIREATE.';
$og_type = 'website';
$og_image = $site_root . '/local/templates/lireate/images/og-default.jpg';
$lireate_phone_display = '+7 (960) 048-49-57';
$lireate_phone_tel = 'tel:+79600484957';
$lireate_telegram = 'https://t.me/lireate_patent';
$lireate_whatsapp = 'https://wa.me/79600484957';
$lireate_max = 'https://max.ru/u/f9LHodD0cOI8jsPagjibEve2cn5_xPW0X1sf3PhbS5TMijA78DnUeDEpUAI';
$faq_entities = [
  ['Достаточно ли международной регистрации по Мадриду?', 'Зависит от стратегии и набора стран. Мадридская система охватывает Китай при соответствующем указании, но национальная процедура и сроки ответа CNIPA всё равно применяются.'],
  ['Можно ли зарегистрировать знак на русском языке?', 'Заявляют словесные, изобразительные и комбинированные обозначения; для китайского рынка часто готовят транслитерацию или иероглифический вариант.'],
  ['Кто может быть заявителем?', 'Иностранное юридическое лицо или предприниматель при соблюдении требований к представителю и документам.'],
  ['Гарантируете ли регистрацию?', 'Нет. Итог экспертизы определяет CNIPA; мы снижаем формальные риски и готовим позицию при возражениях.'],
  ['Сколько стоит сопровождение?', 'Зависит от количества классов, вида знака и необходимости ответов на отказы. После брифа даём смету этапов.'],
  ['Как начать?', 'Оставьте заявку в форме ниже или напишите в мессенджер с изображением знака и описанием товаров.'],
];
$schema_graph = [
  '@context' => 'https://schema.org',
  '@graph' => [
    [
      '@type' => 'Service',
      '@id' => $canonical . '#service',
      'name' => 'Регистрация товарного знака в Китае',
      'url' => $canonical,
      'description' => $seo_description,
      'areaServed' => 'CN',
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
        ['@type' => 'ListItem', 'position' => 3, 'name' => 'Регистрация ТЗ в Китае', 'item' => $canonical],
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
      <a href="#program">Маршрут</a>
      <a href="#classes">МКТУ</a>
      <a href="#faq">FAQ</a>
      <a href="#contact">Заявка</a>
    </nav>
    <a class="lx-btn lx-btn-primary" href="#contact">Консультация</a>
  </header>
</div>

<main id="primary" class="lireate-svc-page registraciya-tovarnogo-znaka-v-kitae-page" role="main" tabindex="-1">

<section class="lx-hero" id="top">
  <div>
    <p class="lx-eyebrow">LIREATE · товарные знаки · Китай · CNIPA</p>
    <h1>Регистрация товарного знака в&nbsp;Китае</h1>
    <p class="lx-hero-lead">Выводите бренд на рынок КНР с понятным маршрутом: аудит знака, классы МКТУ, перевод, подача и ответы на уведомления ведомства. Без гарантий исхода экспертизы — с прозрачными этапами и связкой с вашим российским портфелем ИС.</p>
    <div class="lx-tags" aria-label="Темы услуги">
      <span>CNIPA</span><span>МКТУ</span><span>Мадрид / КНР</span><span>Маркетплейсы</span><span>Перевод</span>
    </div>
    <div class="lx-hero-cta">
      <a class="lx-btn lx-btn-primary" href="#contact">Оставить заявку</a>
      <a class="lx-btn lx-btn-ghost lx-btn--with-icon" id="hero-telegram" href="<?php echo htmlspecialchars($lireate_telegram, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" data-lx-icon="telegram"><span>Написать в Telegram</span></a>
      <a class="lx-btn lx-btn-ghost lx-btn--with-icon" id="hero-max" href="<?php echo htmlspecialchars($lireate_max, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" data-lx-icon="max"><span>Написать в Max</span></a>
      <a class="lx-btn lx-btn-ghost" href="#program">Смотреть программу</a>
    </div>
    <div class="lx-hero-mini">
      <div class="lx-mini-stat"><strong class="lx-stat-value">12+ мес.</strong><span>типичный цикл экспертизы</span></div>
      <div class="lx-mini-stat"><strong class="lx-stat-value">6 этапов</strong><span>маршрут работ</span></div>
      <div class="lx-mini-stat"><strong class="lx-stat-value">2 языка</strong><span>русский и китайский</span></div>
    </div>
  </div>
  <div class="lx-glass" aria-label="Статус сопровождения заявки">
    <div class="lx-panel-head">
      <span>Маршрут заявки · ориентиры</span>
      <span class="lx-live">Сопровождение CNIPA</span>
    </div>
    <div class="lx-kpi-row">
      <div class="lx-kpi"><b class="lx-stat-value">6 этапов</b><small>маршрут сопровождения</small></div>
      <div class="lx-kpi"><b class="lx-stat-value">45 классов</b><small>МКТУ (макс. в классификации)</small></div>
      <div class="lx-kpi"><b class="lx-stat-value">2 языка</b><small>документы и переписка</small></div>
    </div>
    <ul class="lx-feed">
      <li>Аудит обозначения и коллизии в открытых реестрах</li>
      <li>Пакет документов и перевод подачи в CNIPA</li>
      <li>Ответ на уведомление экспертизы в срок</li>
      <li>Регистрация в реестре после положительного решения</li>
    </ul>
    <div class="lx-route" role="img" aria-label="Путь заявки: от бренда до регистрации в Китае">
      <ol class="lx-route-steps">
        <li><span class="lx-route-node">RU</span><span class="lx-route-label">Бренд</span></li>
        <li><span class="lx-route-node">CNIPA</span><span class="lx-route-label">Подача</span></li>
        <li><span class="lx-route-node">МКТУ</span><span class="lx-route-label">Классы</span></li>
        <li><span class="lx-route-node lx-route-node--wide">Экспертиза</span><span class="lx-route-label">рассмотрение CNIPA</span></li>
        <li><span class="lx-route-node">Реестр</span><span class="lx-route-label">Регистрация</span></li>
      </ol>
    </div>
  </div>
</section>

<section class="lx-section lx-reveal" id="why-china">
    <span class="lx-section-label">Контекст рынка</span>
    <h2>Зачем отдельно регистрировать знак в Китае</h2>
    <p>Российское или международное свидетельство (в том числе по системе Мадридского соглашения) не заменяет национальную процедуру в КНР автоматически для всех сценариев. Если вы выводите бренд на китайский рынок, продаёте на локальных маркетплейсах, открываете представительство или лицензируете производство, права на обозначение в Китае нужно выстраивать заранее — пока конкурент не подал заявку на сходное имя первым.</p>
    <p>В КНР действует принцип «первым подал — первым получил право» в рамках национальной регистрации. Это означает, что промедление с подачей в CNIPA (Национальное управление интеллектуальной собственности Китая) повышает риск блокировки вашего же бренда третьими лицами. Для бизнеса это не абстрактный юридический тезис: без регистрации сложнее защищать логотип в рекламе, на упаковке, в доменных спорах и при работе с дистрибьюторами.</p>
    <p>Мы сопровождаем подготовку материалов, перевод на китайский, выбор классов МКТУ под реальный ассортимент и коммуникацию по формальным требованиям ведомства. Ниже — структурированный разбор без обещаний «гарантированной регистрации»: итог экспертизы зависит от перечня товаров и услуг, сходства с чужими знаками и полноты доказательств использования, если они запрашиваются.</p>
</section>

<section class="lx-section lx-reveal" id="legal-frame">
    <span class="lx-section-label">Правовая рамка</span>
    <h2>Правовая рамка: КНР, Мадрид и российский контур</h2>
    <p>Национальная регистрация ведется по правилам Китая через CNIPA. Международная подача возможна в том числе через Мадридскую систему (ВОИС) с указанием Китая как охраняемой территории — но стратегия «только Мадрид» или «только национальная заявка» зависит от портфеля знаков, сроков и бюджета. Подробности процедур публикуются на сайтах <a href="https://www.cnipa.gov.cn/" rel="noopener noreferrer" target="_blank">CNIPA</a> и <a href="https://www.wipo.int/portal/en/index.html" rel="noopener noreferrer" target="_blank">ВОИС</a>.</p>
    <p>Для российского правообладателя важно синхронизировать китайскую заявку с действующими договорами, лицензиями и структурой группы компаний. Вопросы трансграничного использования знака и ответственности дистрибьюторов разбираются в связке с <a href="https://www.consultant.ru/document/cons_doc_LAW_64629/" rel="noopener noreferrer" target="_blank">частью четвертой ГК РФ</a> и договорной документацией — без подмены китайского права российскими нормами.</p>
</section>

<section class="lx-section lx-reveal" id="program">
    <span class="lx-section-label">Маршрут</span>
    <h2>Маршрут регистрации: 6 блоков работ</h2>
    <p>Как на насыщенных лендингах с пошаговой программой, мы разбили путь на блоки — чтобы вы видели, где нужны ваши данные, а где подключается юрист и переводчик.</p>
    <div class="lx-card-grid">
      <article class="lx-card lx-reveal">
        <span class="lx-card-num">01</span>
        <h3>Аудит обозначения</h3>
        <p>Поиск коллизий, оценка охраноспособности, рекомендации по классам МКТУ и формату знака (словесный, изобразительный, комбинированный).</p>
      </article>
      <article class="lx-card lx-reveal">
        <span class="lx-card-num">02</span>
        <h3>Пакет документов</h3>
        <p>Учредительные документы заявителя, описание товаров и услуг, изображение знака, перевод на китайский язык, оформление представителя в КНР.</p>
      </article>
      <article class="lx-card lx-reveal">
        <span class="lx-card-num">03</span>
        <h3>Подача и сопровождение</h3>
        <p>Взаимодействие с китайским поверенным, отслеживание стадий экспертизы, ответы на запросы ведомства в установленные сроки.</p>
      </article>
      <article class="lx-card lx-reveal">
        <span class="lx-card-num">04</span>
        <h3>Экспертиза и возражения</h3>
        <p>Если поступит предварительный отказ, готовим аргументированный ответ: сравнение знаков, доводы о различии, уточнение формулировок товаров.</p>
      </article>
      <article class="lx-card lx-reveal">
        <span class="lx-card-num">05</span>
        <h3>Регистрация и свидетельство</h3>
        <p>После положительного решения — оплата пошлин за регистрацию и выдачу свидетельства, контроль публикации в реестре.</p>
      </article>
      <article class="lx-card lx-reveal">
        <span class="lx-card-num">06</span>
        <h3>Пост-регистрация</h3>
        <p>Наблюдение за сходными подачами, напоминания о продлении, типовые лицензионные оговорки для контрактов в Китае.</p>
      </article>
    </div>
</section>

<section class="lx-section lx-reveal" id="classes">
    <span class="lx-section-label">МКТУ</span>
    <h2>Классы МКТУ и «широкий» перечень</h2>
    <p>Ошибка на старте — заявить слишком узкий перечень товаров и услуг, а через год расширять линейку под другим юридическим покрытием. Мы помогаем сформулировать перечень под текущий экспорт и планируемый ассортимент на маркетплейсах (одежда, косметика, электроника, B2B-оборудование и т.д.), не уходя в заведомо неохраняемые формулировки.</p>
    <p>Для комбинированных знаков отдельно прорабатываем версию на китайском: транслитерация, смысловой перевод, проверка культурных и языковых коннотаций. Негативные значения иероглифов в рекламе — отдельный риск, который дешевле закрыть до подачи, а не после отказа контрагента размещать бренд.</p>
</section>

<section class="lx-section lx-reveal" id="marketplaces">
    <span class="lx-section-label">Риски рынка</span>
    <h2>Маркетплейсы, OEM и контрафакт</h2>
    <p>Площадки в Китае и для китайского покупателя предъявляют требования к подтверждению прав на бренд при жалобах и блокировках карточек. Наличие национальной регистрации упрощает переписку с площадкой и дистрибьютором, но не отменяет доказательную работу по каждому кейсу контрафакта.</p>
    <p>Если производство ведется по OEM/ODM, в договоре с заводом закрепляют запрет на регистрацию вашего обозначения третьими лицами и ответственность за нарушение. Это договорная дисциплина, а не замена регистрации в CNIPA.</p>
</section>

<section class="lx-section lx-reveal" id="timing">
    <span class="lx-section-label">Сроки</span>
    <h2>Сроки и факторы, которые на них влияют</h2>
    <p>Формальные сроки экспертизы меняются и зависят от очереди CNIPA, полноты ответов на уведомления и необходимости возражений. Мы даем ориентиры по этапам после аудита, без обещания фиксированной даты регистрации. Ускорение возможно только в рамках правил ведомства и оплачиваемых процедур, если они применимы к вашей заявке.</p>
    <p>Параллельная подача в нескольких юрисдикциях (РФ, ЕС, США, КНР) требует календаря приоритетов: иногда выгодно сначала зафиксировать дату подачи в Китае, иногда — выстроить международную заявку. Решение принимается по портфелю, а не по шаблону.</p>
</section>

<section class="lx-section lx-reveal" id="documents">
    <span class="lx-section-label">Документы</span>
    <h2>Что подготовить до старта</h2>
    <ul class="lx-checklist">
      <li>Изображение знака в требуемом качестве (вектор или растровый макет без потери читаемости).</li>
      <li>Перечень товаров и услуг в бизнес-формулировках — мы приведем к языку МКТУ.</li>
      <li>Данные заявителя: юридическое лицо или ИП, страна регистрации, цепочка владения брендом.</li>
      <li>Сведения о предыдущих заявках и регистрациях (РФ, Мадрид, другие страны).</li>
      <li>Доказательства использования — если знак уже присутствует на рынке (не всегда обязательно на стадии подачи, но полезно при спорах).</li>
    </ul>
</section>

<section class="lx-section lx-reveal" id="risks">
    <span class="lx-section-label">Риски</span>
    <h2>Типовые риски без китайской регистрации</h2>
    <p>Регистрация сходного знака на китайского агента или партнера «для удобства» без прозрачного договора отчуждения. Потеря домена и соцсетей из-за споров о товарном знаке. Блокировка рекламных кабинетов и карточек товара. Сложности при таможенном оформлении брендированной продукции. Вынужденный ребрендинг после инвестиций в локализацию.</p>
    <p>Ни один из пунктов не является автоматическим следствием — но в практике трансграничной торговли такие сценарии встречаются регулярно, когда знак в КНР не занят правообладателем.</p>
</section>

<section class="lx-section lx-reveal" id="strategy">
    <span class="lx-section-label">Стратегия</span>
    <h2>Стратегия: когда подавать и что делать параллельно в РФ</h2>
    <p>Компании, которые уже торгуют в Китае через дистрибьютора, часто узнают о проблеме поздно: партнёр регистрирует сходный знак на себя или требует эксклюзив, ссылаясь на «локальную охрану». Параллельная регистрация в РФ и КНР не дублирует друг друга, но позволяет выстраивать единую линию лицензирования: кто владеет знаком, кто платит роялти, кто отвечает за качество на маркетплейсе.</p>
    <p>Если бренд строится на латинице, имеет смысл заранее определить «китайское имя бренда» (иногда его выбирает рынок, а не кабинет маркетинга). Фиксация в заявке снижает риск, что через год придётся менять упаковку под уже занятое обозначение. Мы не навязываем иероглифы ради красоты макета — только если они нужны для охвата целевой аудитории и не создают правовых коллизий.</p>
    <p>Для группы компаний обсуждаем, кто выступает заявителем: материнская структура, дочернее общество в СЭЗ или контрактный производитель. Ошибка в заявителе на старте иногда исправляется передачей прав, но это дополнительные пошлины и время. На аудите проговариваем сценарий «что если партнёрство прекращается» — без драматизации, но с понятным планом B.</p>
</section>

<section class="lx-section lx-reveal" id="faq">
    <span class="lx-section-label">FAQ</span>
    <h2>FAQ</h2>
    <details class="lx-faq"><summary>Достаточно ли международной регистрации по Мадриду?</summary><p>Зависит от стратегии и набора стран. Мадридская система охватывает Китай при соответствующем указании, но национальная процедура и сроки ответа CNIPA всё равно применяются. Мы сопоставим ваши текущие регистрации с целевым перечнем классов в КНР.</p></details>
    <details class="lx-faq"><summary>Можно ли зарегистрировать знак на русском языке?</summary><p>Заявляют словесные, изобразительные и комбинированные обозначения; для китайского рынка часто готовят транслитерацию или иероглифический вариант. Охраноспособность оценивается по правилам КНР, а не только по восприятию в России.</p></details>
    <details class="lx-faq"><summary>Кто может быть заявителем?</summary><p>Иностранное юридическое лицо или предприниматель при соблюдении требований к представителю и документам. Конкретная схема владения обсуждается на аудите.</p></details>
    <details class="lx-faq"><summary>Гарантируете ли регистрацию?</summary><p>Нет. Юристы не могут гарантировать положительное решение ведомства. Мы снижаем формальные риски и готовим позицию при возражениях, но итог экспертизы определяет CNIPA.</p></details>
    <details class="lx-faq"><summary>Сколько стоит сопровождение?</summary><p>Зависит от количества классов, вида знака, необходимости ответов на отказы и перевода. После краткого брифа даем смету этапов без скрытых доплат за согласованный объем.</p></details>
    <details class="lx-faq"><summary>Как начать?</summary><p>Оставьте заявку в форме ниже или напишите в мессенджер — пришлите изображение знака и описание товаров. Вернемся с чек-листом и предложением по маршруту.</p></details>
</section>

<section class="lx-section lx-reveal" id="contact">
  <div class="lx-contact">
    <div>
      <span class="lx-section-label">Контакт</span>
      <h2>Заявка и мессенджеры</h2>
      <p>Как на современных лендингах с быстрым контактом: форма для брифа и прямые кнопки мессенджеров. Мы не обрабатываем персональные данные без согласия — отметьте чекбокс перед отправкой.</p>
      <p>Если удобнее — напишите в Telegram, WhatsApp или MAX: приложите логотип и кратко опишите товары. Юрист вернётся с чек-листом документов.</p>
      <p>Телефон: <a href="<?php echo htmlspecialchars($lireate_phone_tel, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($lireate_phone_display, ENT_QUOTES, 'UTF-8'); ?></a></p>
      <div class="lx-msg-grid" id="messenger-grid">
        <a class="lx-msg-btn" id="link-telegram" href="<?php echo htmlspecialchars($lireate_telegram, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" data-lx-icon="telegram"><span>Telegram</span></a>
        <a class="lx-msg-btn" id="link-whatsapp" href="<?php echo htmlspecialchars($lireate_whatsapp, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" data-lx-icon="whatsapp"><span>WhatsApp</span></a>
        <a class="lx-msg-btn lx-msg-btn--max" id="link-max" href="<?php echo htmlspecialchars($lireate_max, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" data-lx-icon="max"><span>MAX</span></a>
        <a class="lx-msg-btn" id="link-email" href="/contacts/" data-lx-icon="lead"><span>Контакты</span></a>
      </div>
    </div>
    <div class="lx-form-panel">
      <form id="lireate-lead-form" data-lead-topic="Заявка: регистрация ТЗ в Китае" novalidate>
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
  <h2>Смежные услуги и материалы</h2>
  <ul class="lx-related-links">
    <li><a href="/uslugi/registraciya-tovarnogo-znaka/">Регистрация товарного знака в России</a></li>
    <li><a href="/news/">Новости и разборы</a></li>
    <li><a href="/contacts/">Контакты и телефон</a></li>
  </ul>
</section>

<section class="lx-section lx-reveal">
    <p class="lx-consent" style="max-width:720px;">Информация на странице не является публичной офертой и не заменяет консультацию юриста. Решения CNIPA и судов КНР зависят от обстоятельств дела. Ссылки на ГК РФ и ведомства приведены для ориентира; применимое право спора определяется отдельно.</p>
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

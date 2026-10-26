<?php
header('Content-Type: text/html; charset=utf-8');
$path = '/findtz/';
$host = isset($_SERVER['HTTP_HOST']) ? preg_replace('/[^a-zA-Z0-9.\-:]/', '', $_SERVER['HTTP_HOST']) : '';
$site_root = ($host !== '' ? 'https://' . $host : '');
$canonical = $site_root . $path;
$seo_title = 'Проверка товарного знака перед регистрацией — LIREATE';
$seo_description = 'Проверка товарного знака перед регистрацией: поиск по реестру Роспатента, оценка рисков сходства. Сопровождение LIREATE по смете, без гарантий экспертизы.';
$og_type = 'website';
$og_image = $site_root . '/local/templates/lireate/images/og-default.jpg';
$lireate_phone_display = '+7 (960) 048-49-57';
$lireate_phone_tel = 'tel:+79600484957';
$lireate_telegram = 'https://t.me/lireate_patent';
$lireate_whatsapp = 'https://wa.me/79600484957';
require_once dirname(__DIR__) . '/_shared/lireate-service-messenger-include.inc.php';
$lireate_max = LIREATE_MAX_CHAT_URL;
$lireate_metrika_id = getenv('LIREATE_YANDEX_METRIKA_ID') ?: '';
$faq_entities = [
  ['Зачем проверять товарный знак до подачи заявки?', 'Госпошлины за формальную экспертизу и экспертизу по существу платятся до решения Роспатента. Если обозначение сходно с уже охраняемым знаком или попадает под основания отказа (ст. 1483 ГК РФ), деньги в бюджет, как правило, не возвращаются. Проверка снижает риск подать заведомо проблемную заявку.'],
  ['Чем профессиональная проверка отличается от поиска на сайте ФИПС?', 'Открытый поиск по реестру показывает совпадения по слову, но не заменяет анализ сходства до степени смешения, фонетики, графики и однородности товаров. Мы сопоставляем ваше обозначение с найденными знаками и формулируем вывод о рисках для экспертизы — без обещания «зелёного света».'],
  ['Гарантируете ли вы, что после проверки Роспатент зарегистрирует знак?', 'Нет. Проверка — оценка вероятности, а не замена экспертизы. Решение принимает Роспатент; новые заявки и публикации в реестре могут изменить картину после отчёта.'],
  ['Сколько стоит проверка товарного знака в LIREATE?', 'Вознаграждение определяется сметой после брифа: зависит от вида обозначения (слово, логотип, комбинация), числа классов МКТУ и глубины поиска. Мы не публикуем фиксированный прайс «за всё»; платные поиски ФИПС, если нужны, оплачиваются отдельно по тарифам ведомства.'],
  ['Как долго готовится отчёт?', 'Срок согласуем после брифа: простое словесное обозначение и один класс обычно быстрее, чем комбинированный знак с несколькими классами. Срочность не отменяет необходимость сверить реестр и описать риски письменно.'],
  ['Проверяете ли логотип и комбинированные знаки?', 'Да. Для изобразительных и комбинированных обозначений поиск строится и по словесным элементам, и по графике в пределах доступных баз. Иногда имеет смысл проверять название и знак отдельно — обсудим на старте.'],
  ['Входит ли международный поиск?', 'Базовый фокус — реестр Роспатента и сведения, доступные для оценки рисков в РФ. Расширенный поиск по зарубежным базам и доменам — по отдельной смете, если это нужно вашей стратегии.'],
  ['Что делать, если проверка показала сходный знак?', 'Варианты: доработать обозначение, сузить перечень товаров и услуг, оценить переговоры с правообладателем или отложить подачу. Регистрация при высоком риске — отдельное решение; мы объясняем аргументы, но не настаиваем подавать «вслепую».'],
  ['Можно ли после проверки заказать регистрацию в LIREATE?', 'Да. Отчёт становится основой для заявки: классы МКТУ, перечень и стратегия ответов экспертизе. Регистрацию можно заказать отдельно — услуга «Регистрация товарного знака» на сайте LIREATE.'],
];
$lx_breadcrumb_current = 'Проверка товарного знака';
$lx_review_filter_services = ['prodlenie-tovarnogo-znaka', 'registraciya-tovarnogo-znaka-s-oplatoy-za-rezultat'];
$schema_graph = [
  '@context' => 'https://schema.org',
  '@graph' => [
    ['@type' => 'Service', '@id' => $canonical . '#service', 'name' => 'Проверка товарного знака', 'serviceType' => 'Проверка обозначения перед регистрацией в Роспатенте', 'url' => $canonical, 'description' => $seo_description, 'areaServed' => 'RU', 'provider' => ['@id' => $canonical . '#organization']],
    ['@type' => ['LegalService', 'Organization'], '@id' => $canonical . '#organization', 'name' => 'LIREATE', 'url' => $site_root . '/', 'telephone' => '+7-960-048-49-57', 'sameAs' => [$lireate_telegram, $lireate_whatsapp, $lireate_max]],
    ['@type' => 'BreadcrumbList', 'itemListElement' => [
      ['@type' => 'ListItem', 'position' => 1, 'name' => 'Главная', 'item' => $site_root . '/'],
      ['@type' => 'ListItem', 'position' => 2, 'name' => 'Услуги', 'item' => $site_root . '/uslugi/'],
      ['@type' => 'ListItem', 'position' => 3, 'name' => 'Проверка товарного знака', 'item' => $canonical],
    ]],
    ['@type' => 'FAQPage', 'mainEntity' => array_map(static function ($pair) {
      return ['@type' => 'Question', 'name' => $pair[0], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $pair[1]]];
    }, $faq_entities)],
  ],
];
?><!DOCTYPE html>
<html lang="ru" data-lireate-telegram="<?php echo htmlspecialchars($lireate_telegram, ENT_QUOTES, 'UTF-8'); ?>" data-lireate-whatsapp="<?php echo htmlspecialchars($lireate_whatsapp, ENT_QUOTES, 'UTF-8'); ?>" data-lireate-max="<?php echo htmlspecialchars($lireate_max, ENT_QUOTES, 'UTF-8'); ?>" data-lireate-metrika-id="<?php echo htmlspecialchars(preg_replace('/\D/', '', (string) $lireate_metrika_id), ENT_QUOTES, 'UTF-8'); ?>">
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
      <a href="#when">Когда нужна</a>
      <a href="#program">Этапы</a>
      <a href="#pricing">Стоимость</a>
      <a href="#risks">Риски</a>
      <a href="#queries">Запросы</a>
      <a href="#faq">FAQ</a>
      <a href="#contact">Заявка</a>
    </nav>
    <a class="lx-btn lx-btn-primary" href="#contact">Консультация</a>
  </header>
</div>

<main id="primary" class="lireate-svc-page proverka-tovarnogo-znaka-page" role="main" tabindex="-1">

<?php include __DIR__ . '/blocks/lireate-breadcrumbs.inc.php'; ?>

<section class="lx-hero" id="top">
  <div class="lx-hero-mascot-slot" data-lx-mascot="search"></div>
  <div>
    <p class="lx-eyebrow">LIREATE · товарные знаки · поиск по реестру</p>
    <h1>Проверка товарного знака</h1>
    <p class="lx-hero-lead">Поиск по реестру Роспатента и оценка рисков до подачи заявки: сходство, однородность, основания отказа по ст. 1483 ГК РФ. Отчёт и рекомендации — по смете; регистрацию и исход экспертизы гарантировать нельзя.</p>
    <div class="lx-tags"><span>ФИПС</span><span>реестр ТЗ</span><span>МКТУ</span><span>ст. 1483 ГК РФ</span></div>
    <div class="lx-hero-cta">
      <a class="lx-btn lx-btn-primary" href="#contact">Заказать проверку</a>
      <a class="lx-btn lx-btn-ghost lx-btn--with-icon" id="hero-telegram" href="<?php echo htmlspecialchars($lireate_telegram, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" data-lx-icon="telegram"><span>Написать в Telegram</span></a>
      <a class="lx-btn lx-btn-ghost lx-btn--with-icon" id="hero-max" href="<?php echo htmlspecialchars($lireate_max, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" data-lx-icon="max"><span>Написать в Max</span></a>
    </div>
    <div class="lx-hero-mini">
      <div class="lx-mini-stat"><strong class="lx-stat-value">реестр</strong><span>Роспатент / ФИПС</span></div>
      <div class="lx-mini-stat"><strong class="lx-stat-value">3 шага</strong><span>бриф → поиск → отчёт</span></div>
      <div class="lx-mini-stat"><strong class="lx-stat-value">по смете</strong><span>услуги LIREATE</span></div>
    </div>
  </div>
  <div class="lx-glass lx-glass-anim" aria-label="Этапы проверки">
    <div class="lx-panel-head"><span>Проверка · ориентиры</span><span class="lx-live">до пошлин</span></div>
    <ul class="lx-feed">
      <li>Сбор данных об обозначении и товарах</li>
      <li>Поиск тождественных и сходных знаков</li>
      <li>Вывод о рисках и следующих шагах</li>
      <li>При необходимости — переход к регистрации</li>
    </ul>
    <div class="lx-route" role="img" aria-label="Маршрут проверки">
      <ol class="lx-route-steps">
        <li><span class="lx-route-node">Бриф</span><span class="lx-route-label">данные</span></li>
        <li><span class="lx-route-node">Поиск</span><span class="lx-route-label">реестр</span></li>
        <li><span class="lx-route-node lx-route-node--wide">Отчёт</span><span class="lx-route-label">риски</span></li>
        <li><span class="lx-route-node">ТЗ</span><span class="lx-route-label">заявка</span></li>
      </ol>
    </div>
  </div>
</section>

<section class="lx-section lx-reveal" id="when">
  <span class="lx-section-label">Когда нужна</span>
  <h2>Проверка товарного знака: когда заказывать поиск</h2>
  <p>Проверка нужна до уплаты госпошлин за регистрацию. Минимальный цикл пошлин за один класс МКТУ — 35 000 ₽ (п. 2.1, 2.4 и 2.11 таблицы Роспатента); эти суммы уходят в бюджет независимо от того, зарегистрируют знак или откажут. Поэтому разумно сначала оценить, нет ли в реестре сходных обозначений и не описывает ли ваше название товар напрямую.</p>
  <div class="lx-carousel" data-lx-carousel tabindex="0" aria-label="Когда нужна проверка">
    <button type="button" class="lx-carousel-prev" aria-label="Предыдущий слайд">‹</button>
    <div class="lx-carousel-viewport">
      <ol class="lx-carousel-track">
        <li class="lx-carousel-slide"><article class="lx-card"><h3>Новый бренд</h3><p>Название и логотип перед запуском и рекламой.</p></article></li>
        <li class="lx-carousel-slide"><article class="lx-card"><h3>Маркетплейс</h3><p>Подготовка к WB, Ozon и договорам с партнёрами.</p></article></li>
        <li class="lx-carousel-slide"><article class="lx-card"><h3>Ребрендинг</h3><p>Смена имени — новые риски сходства.</p></article></li>
        <li class="lx-carousel-slide"><article class="lx-card"><h3>Спорный знак</h3><p>Уже есть претензия — оценка до подачи.</p></article></li>
        <li class="lx-carousel-slide"><article class="lx-card"><h3>После самопроверки</h3><p>Нашли совпадение на fips.ru — нужен разбор.</p></article></li>
      </ol>
    </div>
    <button type="button" class="lx-carousel-next" aria-label="Следующий слайд">›</button>
    <div class="lx-carousel-dots"></div>
  </div>
  <h3>Как проверить, зарегистрирован ли товарный знак самостоятельно</h3>
  <p>На портале ФИПС доступен открытый поиск по реестру: можно ввести слово и увидеть действующие знаки и заявки. Это полезный первый шаг, но он не отвечает на вопрос, сочтёт ли экспертиза обозначения сходными до степени смешения в ваших классах МКТУ. Мы дополняем поиск профессиональным сопоставлением и фиксируем выводы в отчёте, который можно приложить к внутреннему решению о подаче.</p>
  <p>Если проверка показывает высокий риск, иногда выгоднее доработать знак до подачи, чем платить пошлины за заведомо слабую заявку. Доработка и подготовка новой заявки — отдельные этапы; после отчёта вы можете заказать <a href="/registraciya_tovarnogo_znaka/">регистрацию товарного знака</a> у LIREATE или подать самостоятельно.</p>
  <h3>ИП, ООО и маркетплейсы</h3>
  <p>Заявитель может быть и компанией, и индивидуальным предпринимателем (ст. 1492 ГК РФ) — на результат поиска это не влияет, но влияет выбор классов под реальные товары. Для Wildberries, Ozon и других площадок проверка не заменяет внутренние правила площадки, однако помогает понять, есть ли в реестре конфликтующие знаки в классах вашего ассортимента. Если бренд уже используется годами без регистрации, поиск покажет, не занял ли кто-то сходное обозначение раньше.</p>
  <h3>Когда проверка не нужна «в ноль»</h3>
  <p>Если знак — уникальное выдуманное слово без смысла в русском языке и вы готовы подтвердить различительную способность материалами, риски ниже, но не исчезают: фонетическое сходство с чужими знаками всё равно возможно. Мы честно говорим, когда достаточно короткого поиска, а когда нужен развёрнутый отчёт — это часть брифа, а не допродажа «по умолчанию».</p>
</section>

<section class="lx-section lx-reveal" id="program">
  <span class="lx-section-label">Как работаем</span>
  <h2>Программа проверки: три шага к отчёту</h2>
  <p>Классическая схема, которую мы использовали на странице /findtz/ и сохраняем в обновлённом формате: короткий бриф, поиск по базам, письменный результат с рекомендациями. Сроки и глубина поиска зависят от обозначения — их фиксируем в смете.</p>
  <div class="lx-steps-walk" data-lx-steps-walk>
    <div class="lx-steps-walk-mascot-slot" aria-hidden="true"></div>
    <ol class="lx-steps-walk-list">
      <li class="lx-steps-walk-step"><span class="lx-card-num">01</span><h3>Бриф и классы</h3><p><strong>1–2 дня.</strong> Название, логотип, описание товаров и услуг, планы по МКТУ.</p></li>
      <li class="lx-steps-walk-step"><span class="lx-card-num">02</span><h3>Поиск по реестру</h3><p><strong>по смете.</strong> Тождественные и сходные знаки, релевантные классы, при необходимости — платные поиски ФИПС.</p></li>
      <li class="lx-steps-walk-step"><span class="lx-card-num">03</span><h3>Отчёт и консультация</h3><p><strong>письменно.</strong> Риски по ст. 1483, рекомендации: подавать, доработать или отложить.</p></li>
    </ol>
  </div>
  <h3>Экспертиза товарного знака на этапе предварительной проверки</h3>
  <p>Государственная экспертиза по существу начинается только после подачи заявки. Предварительная проверка — это экспертный анализ до пошлин: мы смотрим на обозначение глазами практики Роспатента, но не подменяем решение ведомства. В отчёте отделяем факты (найденные знаки, классы, статус) от оценочных выводов (вероятность запроса или отказа).</p>
  <p>Для комбинированных знаков учитываем доминирующий элемент; для слоганов — различительную способность и описательность. Если вы планируете серию брендов, можем заложить в бриф единый стиль поиска — это обсуждается отдельно.</p>
</section>

<section class="lx-section lx-reveal" id="pricing">
  <span class="lx-section-label">Стоимость</span>
  <h2>Стоимость проверки: смета LIREATE и платные поиски</h2>
  <p>Размер вознаграждения за проверку не установлен законом — он определяется договором после брифа. Мы не указываем на странице вымышленные «цены от …»: в смете будут строки за аналитику LIREATE и, при необходимости, за официальные платные поиски ФИПС (тарифы публикует ведомство, не LIREATE).</p>
  <div class="lx-price-grid">
    <div class="lx-price-card"><strong>по смете</strong><span>проверка и отчёт LIREATE — после брифа, не оферта</span></div>
    <div class="lx-price-card"><strong>ФИПС</strong><span>платные поиски по тарифам библиотеки — опционально, сверяйте на fips.ru</span></div>
    <div class="lx-price-card"><strong>35 000 ₽</strong><span>ориентир госпошлин за регистрацию 1 класса — не входят в проверку</span></div>
  </div>
  <p>Честная смета отделяет работу исполнителя от пошлин будущей заявки. Если после отчёта вы заказываете регистрацию, проверка не дублируется бесплатно «внутри» регистрации — объём работ в договоре на регистрацию согласуем отдельно.</p>
  <h3>Что обычно входит в отчёт</h3>
  <ul class="lx-checklist">
    <li>описание обозначения и перечня товаров/услуг, по которым искали;</li>
    <li>список релевантных знаков и заявок с номерами, статусом и классами МКТУ;</li>
    <li>комментарий о сходстве и однородности (оценочный, не решение Роспатента);</li>
    <li>рекомендации: подавать, скорректировать перечень, изменить графику или отложить подачу.</li>
  </ul>
  <p>Отчёт не является юридическим заключением с «гарантией регистрации» и не заменяет договор с правообладателем при урегулировании спора. Его задача — дать вам фактуру для решения о пошлинах и сроках запуска бренда.</p>
</section>

<section class="lx-section lx-reveal" id="risks">
  <span class="lx-section-label">Риски</span>
  <h2>Риски подачи без проверки</h2>
  <div class="lx-risk-grid">
    <div class="lx-risk-item"><h3>Потеря пошлин</h3><p>При отказе по ст. 1483 ГК РФ пошлины за пройденные этапы, как правило, не возвращаются.</p></div>
    <div class="lx-risk-item"><h3>Ложная уверенность</h3><p>«Не нашёл в поиске» ≠ отсутствие сходных знаков в смежных классах.</p></div>
    <div class="lx-risk-item"><h3>Задержка запуска</h3><p>Месяцы экспертизы с запросами — время, которое можно сэкономить на этапе брифа.</p></div>
    <div class="lx-risk-item"><h3>Споры с конкурентами</h3><p>Регистрация сходного знака может привести к обращению третьих лиц по ст. 1493 ГК РФ.</p></div>
  </div>
  <p>Проверка не устраняет риски полностью: в реестр могут появиться новые заявки после даты поиска. Мы указываем дату среза базы в отчёте. При высоких ставках на бренд имеет смысл повторить поиск ближе к подаче или заказать платный поиск ФИПС для ускорения корреспонденции экспертизы — без обещания конкретного срока.</p>
  <p>Если конкурент уже подал сходную заявку, возможна процедура <a href="/vozrazhenie-protiv-registracii-tovarnogo-znaka/">возражения против регистрации</a> по ст. 1493 ГК РФ — это отдельная услуга от предварительной проверки вашего знака.</p>
</section>

<section class="lx-section lx-reveal" id="queries">
  <span class="lx-section-label">Запросы</span>
  <h2>Как формулируют запрос в поиске</h2>

  <h3>Поиск товарных знаков</h3>
  <p>Запрос «поиск товарных знаков» обычно означает проверку по реестру Роспатента на тождество и сходство. Мы ищем в открытых и профессиональных источниках, фиксируем найденные знаки с номерами и классами МКТУ.</p>

  <h3>Проверка на уникальность</h3>
  <p>«Проверка товарного знака на уникальность» — бытовое название юридической оценки: абсолютной уникальности не существует, важно отсутствие конфликта с охраняемыми знаками в однородных классах.</p>

  <h3>Как проверить, зарегистрирован ли товарный знак</h3>
  <p>Для самостоятельного ориентира — поиск на fips.ru; для решения о подаче — отчёт специалиста с выводом о рисках и вариантах доработки обозначения.</p>

  <h3>Экспертиза товарного знака</h3>
  <p>Государственная экспертиза начинается после заявки. Предварительная экспертиза в LIREATE — аналитика до пошлин, чтобы вы понимали вероятный сценарий в Роспатенте.</p>
  <p>После проверки логичный следующий шаг — регистрация или продление уже действующего знака. Для действующих свидетельств смотрите <a href="/prodlenie_tovarnogo_znaka/">продление товарного знака</a>; если спор касается чужой заявки, а не вашей — <a href="/vozrazhenie-protiv-registracii-tovarnogo-znaka/">возражение против регистрации</a>.</p>
</section>

<?php include __DIR__ . '/blocks/lireate-reviews.inc.php'; ?>

<section class="lx-section lx-reveal" id="faq">
  <span class="lx-section-label">FAQ</span>
  <h2>Вопросы о проверке товарного знака</h2>
<?php foreach ($faq_entities as $faq_pair) : ?>
  <details class="lx-faq"><summary><?php echo htmlspecialchars($faq_pair[0], ENT_QUOTES, 'UTF-8'); ?></summary><p><?php echo htmlspecialchars($faq_pair[1], ENT_QUOTES, 'UTF-8'); ?></p></details>
<?php endforeach; ?>
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
      <form id="lireate-lead-form" data-lead-topic="Заявка: проверка товарного знака" novalidate>
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
    <a class="lx-related-card" href="/registraciya_tovarnogo_znaka/"><span class="lx-related-card-type">Услуга</span><strong>Регистрация товарного знака</strong></a>
    <a class="lx-related-card" href="/prodlenie_tovarnogo_znaka/"><span class="lx-related-card-type">Услуга</span><strong>Продление товарного знака</strong></a>
    <a class="lx-related-card" href="/vozrazhenie-protiv-registracii-tovarnogo-znaka/"><span class="lx-related-card-type">Услуга</span><strong>Возражение против регистрации</strong></a>
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
<?php include dirname(__DIR__) . '/_shared/lireate-metrika.inc.php'; ?>
<script src="assets/lireate-shared/lireate-metrika-goals.js"></script>
</body>
</html>

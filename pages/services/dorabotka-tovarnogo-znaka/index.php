<?php
header('Content-Type: text/html; charset=utf-8');
$path = '/dorabotka_logotipa_i_nazvaniya_dlya_registracii_tovarnogo_znaka/';
$host = isset($_SERVER['HTTP_HOST']) ? preg_replace('/[^a-zA-Z0-9.\-:]/', '', $_SERVER['HTTP_HOST']) : '';
$site_root = ($host !== '' ? 'https://' . $host : '');
$canonical = $site_root . $path;
$seo_title = 'Доработка логотипа для регистрации ТЗ — LIREATE';
$seo_description = 'Доработка логотипа и названия бренда под требования Роспатента: ст. 1483 ГК РФ, поиск коллизий, новая версия для заявки. Консультация LIREATE.';
$og_type = 'website';
$og_image = $site_root . '/local/templates/lireate/images/og-default.jpg';
$lireate_phone_display = '+7 (960) 048-49-57';
$lireate_phone_tel = 'tel:+79600484957';
$lireate_telegram = 'https://t.me/lireate_patent';
$lireate_whatsapp = 'https://wa.me/79600484957';
require_once dirname(__DIR__) . '/_shared/lireate-messenger-urls.php';
$lireate_max = LIREATE_MAX_CHAT_URL;
$faq_entities = [
  ['Чем доработка логотипа отличается от регистрации «как есть»?', 'Мы готовим обозначение к экспертизе: убираем или ослабляем элементы, из‑за которых возможен отказ по ст. 1483 ГК РФ, и сверяем версию с результатами поиска по реестрам. Решение о регистрации принимает Роспатент.'],
  ['Можно ли зарегистрировать название, которое описывает товар?', 'Описательные и ложные элементы, а также обозначения, вошедшие во всеобщее употребление, не охраняются (п. 1, 3 ст. 1483 ГК РФ). Иногда допустима регистрация с дискламером неохраняемых элементов — это оценивает экспертиза.'],
  ['Обязательно ли менять фирменное название компании?', 'Нет. Для заявки можно выбрать иное словесное обозначение или комбинированный знак; юридическое наименование и бренд на рынке могут различаться, если это согласовано с маркетингом и договорами.'],
  ['Сколько итераций доработки обычно нужно?', 'Зависит от исходного макета и перечня МКТУ. После каждой правки имеет смысл повторять <a href="/findtz/">проверку по реестрам</a> — без гарантии, что экспертиза согласится с выбранным вариантом.'],
  ['Вы гарантируете регистрацию после доработки?', 'Нет. Мы снижаем юридические риски отказа за счёт анализа оснований ст. 1483 ГК РФ и коллизий, но итог определяет Роспатент.'],
  ['Нужно ли заново подавать заявку, если меняем логотип?', 'Если заявка ещё не подана — подаётся выбранная финальная версия. Если экспертиза уже идёт, возможны ответы на уведомление или новая заявка — зависит от стадии и содержания правок (ст. 1499, 1500 ГК РФ).'],
];
$lx_breadcrumb_current = 'Доработка логотипа и названия';
$lx_review_filter_services = ['dorabotka-tovarnogo-znaka', 'registraciya-tovarnogo-znaka-v-kitae'];
$schema_graph = [
  '@context' => 'https://schema.org',
  '@graph' => [
    ['@type' => 'Service', '@id' => $canonical . '#service', 'name' => 'Доработка логотипа и названия для регистрации товарного знака', 'url' => $canonical, 'description' => $seo_description, 'areaServed' => 'RU', 'provider' => ['@id' => $canonical . '#organization']],
    ['@type' => ['LegalService', 'Organization'], '@id' => $canonical . '#organization', 'name' => 'LIREATE', 'url' => $site_root . '/', 'telephone' => '+7-960-048-49-57', 'sameAs' => [$lireate_telegram, $lireate_whatsapp, $lireate_max]],
    ['@type' => 'BreadcrumbList', 'itemListElement' => [
      ['@type' => 'ListItem', 'position' => 1, 'name' => 'Главная', 'item' => $site_root . '/'],
      ['@type' => 'ListItem', 'position' => 2, 'name' => 'Услуги', 'item' => $site_root . '/uslugi/'],
      ['@type' => 'ListItem', 'position' => 3, 'name' => 'Доработка логотипа и названия', 'item' => $canonical],
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
      <a href="#when">Когда нужна</a>
      <a href="#program">Этапы</a>
      <a href="#article">Правовые основания</a>
      <a href="#faq">FAQ</a>
      <a href="#contact">Заявка</a>
    </nav>
    <a class="lx-btn lx-btn-primary" href="#contact">Консультация</a>
  </header>
</div>

<main id="primary" class="lireate-svc-page dorabotka-tovarnogo-znaka-page" role="main" tabindex="-1">

<?php include __DIR__ . '/blocks/lireate-breadcrumbs.inc.php'; ?>

<section class="lx-hero" id="top">
  <div class="lx-hero-mascot-slot" data-lx-mascot="default"></div>
  <div>
    <p class="lx-eyebrow">LIREATE · товарные знаки · доработка обозначения</p>
    <h1>Доработка логотипа и названия под регистрацию товарного знака</h1>
    <p class="lx-hero-lead">Готовим словесное и комбинированное обозначение к экспертизе Роспатента: поиск коллизий, правки по ст. 1483 ГК РФ, финальный макет для заявки — без гарантии регистрации.</p>
    <div class="lx-tags"><span>ст. 1483 ГК РФ</span><span>ст. 1477–1481</span><span>ФИПС</span></div>
    <div class="lx-hero-cta">
      <a class="lx-btn lx-btn-primary" href="#contact">Оставить заявку</a>
      <a class="lx-btn lx-btn-ghost lx-btn--with-icon" id="hero-telegram" href="<?php echo htmlspecialchars($lireate_telegram, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" data-lx-icon="telegram"><span>Написать в Telegram</span></a>
      <a class="lx-btn lx-btn-ghost lx-btn--with-icon" id="hero-max" href="<?php echo htmlspecialchars($lireate_max, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" data-lx-icon="max"><span>Написать в Max</span></a>
    </div>
    <div class="lx-hero-mini">
      <div class="lx-mini-stat"><strong class="lx-stat-value">поиск</strong><span>реестры ФИПС и открытые базы</span></div>
      <div class="lx-mini-stat"><strong class="lx-stat-value">макет</strong><span>версия для подачи заявки</span></div>
      <div class="lx-mini-stat"><strong class="lx-stat-value">ст. 1483</strong><span>основания для отказа</span></div>
    </div>
  </div>
  <div class="lx-glass lx-glass-anim" aria-label="Маршрут доработки обозначения">
    <div class="lx-panel-head"><span>Обозначение · маршрут</span><span class="lx-live">Роспатент</span></div>
    <div class="lx-kpi-row">
      <div class="lx-kpi"><b class="lx-stat-value">слово</b><small>название бренда и транслитерации</small></div>
      <div class="lx-kpi"><b class="lx-stat-value">знак</b><small>логотип, комбинированный вариант</small></div>
      <div class="lx-kpi"><b class="lx-stat-value">МКТУ</b><small>связь с перечнем товаров и услуг</small></div>
    </div>
    <ul class="lx-feed">
      <li>Предварительный поиск сходных знаков и наименований</li>
      <li>Карта рисков по абсолютным основаниям ст. 1483 ГК РФ</li>
      <li>Доработка названия бренда и визуала логотипа</li>
      <li>Согласование финальной версии перед <a href="/registraciya_tovarnogo_znaka/">подачей заявки</a></li>
    </ul>
    <div class="lx-route" role="img" aria-label="Маршрут доработки">
      <ol class="lx-route-steps">
        <li><span class="lx-route-node">Поиск</span><span class="lx-route-label">реестры</span></li>
        <li><span class="lx-route-node">Риски</span><span class="lx-route-label">1483</span></li>
        <li><span class="lx-route-node">Макет</span><span class="lx-route-label">правки</span></li>
        <li><span class="lx-route-node lx-route-node--wide">Заявка</span><span class="lx-route-label">ФИПС</span></li>
        <li><span class="lx-route-node">Экспертиза</span><span class="lx-route-label">ответы</span></li>
      </ol>
    </div>
  </div>
</section>

<section class="lx-section lx-reveal" id="when">
  <span class="lx-section-label">Когда нужна</span>
  <h2>Доработка логотипа перед регистрацией: типичные ситуации</h2>
  <div class="lx-carousel" data-lx-carousel tabindex="0" aria-label="Когда дорабатывать обозначение">
    <button type="button" class="lx-carousel-prev" aria-label="Предыдущий слайд">‹</button>
    <div class="lx-carousel-viewport">
      <ol class="lx-carousel-track">
        <li class="lx-carousel-slide"><article class="lx-card"><h3>Поиск показал коллизии</h3><p>Сходное слово или графика в реестре — нужен иной вариант или стратегия по <a href="/findtz/">проверке знака</a>.</p></article></li>
        <li class="lx-carousel-slide"><article class="lx-card"><h3>Описательное название</h3><p>Бренд прямо описывает товар или услугу (п. 1 ст. 1483 ГК РФ).</p></article></li>
        <li class="lx-carousel-slide"><article class="lx-card"><h3>Государственная символика</h3><p>Элементы флага, герба, официальных знаков без разрешения (п. 2 ст. 1483 ГК РФ).</p></article></li>
        <li class="lx-carousel-slide"><article class="lx-card"><h3>Общеупотребимое слово</h3><p>Обозначение стало указанием вида товара (п. 1 ст. 1483 ГК РФ).</p></article></li>
        <li class="lx-carousel-slide"><article class="lx-card"><h3>Дизайн «как у всех»</h3><p>Шаблонная иконка без различительной способности — усиление оригинальных элементов.</p></article></li>
        <li class="lx-carousel-slide"><article class="lx-card"><h3>Отказ или уведомление</h3><p>Нужно ответить экспертизе или подготовить новую версию (ст. 1499 ГК РФ).</p></article></li>
      </ol>
    </div>
    <button type="button" class="lx-carousel-next" aria-label="Следующий слайд">›</button>
    <div class="lx-carousel-dots"></div>
  </div>
</section>

<section class="lx-section lx-reveal" id="program">
  <span class="lx-section-label">Как работаем</span>
  <h2>Этапы доработки логотипа и названия для Роспатента</h2>
  <div class="lx-steps-walk" data-lx-steps-walk>
    <div class="lx-steps-walk-mascot-slot" aria-hidden="true"></div>
    <ol class="lx-steps-walk-list">
      <li class="lx-steps-walk-step"><span class="lx-card-num">01</span><h3>Бриф и МКТУ</h3><p><strong>1–2 дня.</strong> Товары и услуги, планы по <a href="/mktu/">классам МКТУ</a>, исходные файлы логотипа.</p></li>
      <li class="lx-steps-walk-step"><span class="lx-card-num">02</span><h3>Поиск</h3><p><strong>2–5 дней.</strong> Словесные и графические варианты, транслитерации, смежные классы.</p></li>
      <li class="lx-steps-walk-step"><span class="lx-card-num">03</span><h3>Юридическая карта</h3><p><strong>1–3 дня.</strong> Абсолютные и относительные основания; перечень неохраняемых элементов.</p></li>
      <li class="lx-steps-walk-step"><span class="lx-card-num">04</span><h3>Доработка названия</h3><p><strong>3–7 дней.</strong> Варианты словесного обозначения, дискламеры, согласование с маркетингом.</p></li>
      <li class="lx-steps-walk-step"><span class="lx-card-num">05</span><h3>Доработка логотипа</h3><p><strong>3–10 дней.</strong> Комбинированный знак, цветовая модель, правила использования.</p></li>
      <li class="lx-steps-walk-step"><span class="lx-card-num">06</span><h3>Подача</h3><p><strong>по согласованию.</strong> Финальный макет передаётся в сопровождение <a href="/registraciya_tovarnogo_znaka/">регистрации ТЗ</a>.</p></li>
    </ol>
  </div>
</section>

<section class="lx-section lx-reveal" id="article">
  <span class="lx-section-label">Разбор</span>
  <h2>Требования Роспатента к логотипу и названию бренда</h2>
  <div class="lx-prose">
    <p>Товарный знак — обозначение для индивидуализации товаров юридических лиц и индивидуальных предпринимателей (ст. 1477 ГК РФ). Регистрация даёт исключительное право (ст. 1479, 1481 ГК РФ), но экспертиза проверяет, можно ли такое обозначение включить в реестр. Запросы вроде «доработка логотипа перед регистрацией товарного знака» и «доработка логотипа для регистрации в Роспатенте» как раз про этот этап: когда макет уже есть, а юридическая «проходимость» под вопросом.</p>
    <h3>Абсолютные основания для отказа (ст. 1483 ГК РФ)</h3>
    <p>Пункт 1 ст. 1483 ГК РФ — обозначения, не обладающие различительной способностью или состоящие только из элементов, вошедших во всеобщее употребление как обозначения товаров определённого вида; характеризующих товары, в том числе указывающих на вид, качество, количество, свойство, назначение, ценность, а также на время, место и способ их производства или сбыта. Практически это значит: если название бренда прямо говорит, чем вы занимаетесь («Быстрая доставка еды», «Студия маникюра»), экспертиза может признать его описательным для заявленных услуг. Доработка названия бренда часто сводится к добавлению фантазийного элемента, нестандартного написания или выделению охраняемой части при дискламере описательной.</p>
    <p>Пункт 2 — обозначения, тождественные или сходные до степени смешения с официальными государственными символами, без согласия уполномоченного органа. В логотипе нельзя свободно использовать элементы, которые визуально воспринимаются как герб, флаг или их имитация.</p>
    <p>Пункт 3 — противоречащие общественным интересам, принципам гуманности и морали. Пункт 4 — способные ввести потребителя в заблуждение относительно товара или его изготовителя (ложное указание). Если на упаковке или сайте обозначение создаёт неверное представление о происхождении или свойствах, риск отказа повышается.</p>
    <p>Пункты 5–7 — запрет на воспроизведение официальных контрольных знаков, тождественность или сходство с чужими охраняемыми обозначениями в отношении однородных товаров, а также с фирменным наименованием или коммерческим обозначением при известности на территории РФ. Здесь работает связка с поиском: даже удачная доработка логотипа не поможет, если словесная часть остаётся сходной с чужим знаком в тех же классах МКТУ.</p>
    <h3>Что меняют в логотипе на практике</h3>
    <p>Доработка логотипа под требования Роспатента — не «косметика», а усиление различительной способности и снятие запретных элементов. Типичные шаги: упростить или заменить описательную иконку; вынести неохраняемое слово в отдельную строку с дискламером; изменить композицию, чтобы знак не повторял чужую графику из результатов поиска; подготовить чёрно-белую и цветовую версии для заявки. Для словесного знака — проверить транслитерации (латиница, кириллица), варианты с дефисом и слитным написанием.</p>
    <h3>Доработка названия бренда и юридическое имя</h3>
    <p>Коммерческое обозначение на витрине и заявленное в Роспатенте слово могут различаться. Важно заранее согласовать: как бренд будет звучать в рекламе, домене, договорах с дизайнером (переход исключительного права на логотип по ст. 1288, 1296 ГК РФ). Если право на макет принадлежит подрядчику, без договора отчуждения или лицензии заявитель рискует спорами уже после регистрации.</p>
    <h3>Связь с регистрацией и ответами экспертизе</h3>
    <p>После подачи заявки Роспатент проводит формальную экспертизу и экспертизу по существу (ст. 1499 ГК РФ). При уведомлении о результатах проверки заявленного обозначения заявитель может представить аргументы или изменить обозначение в пределах, допускаемых законом (ст. 1500 ГК РФ). Поэтому доработку логотипа для товарного знака разумно закладывать и до подачи, и как запасной сценарий при первом ответе ведомства — без обещания, что любая правка будет принята.</p>
    <h3>Чем услуга LIREATE отличается от «просто дизайна»</h3>
    <p>Дизайнер готовит визуал; патентный поверенный и юрист смотрят на соответствие ст. 1483 ГК РФ и результаты поиска. Мы не подменяем государственную экспертизу и не гарантируем регистрацию: готовим обоснованную версию обозначения, пояснения для заявки и маршрут — от <a href="/findtz/">проверки товарного знака</a> до передачи материалов в сопровождение регистрации. Пошлины Роспатента и сроки рассмотрения заявки определяются законом и тарифами ведомства (rospatent.gov.ru), а не условиями этой страницы.</p>
    <p>Если вы видите сайт в поиске по запросу «доработка логотипа для товарного знака», но сниппет не отвечает на вопрос, отдельная посадочная с ясным title и description помогает отфильтровать целевой трафик: владельцам бренда нужна не общая статья о регистрации, а шаги по адаптации имени и знака под критерии охраноспособности.</p>
  </div>
</section>

<section class="lx-section lx-reveal" id="pricing">
  <span class="lx-section-label">Стоимость</span>
  <h2>Стоимость доработки и госпошлины</h2>
  <div class="lx-price-grid">
    <div class="lx-price-card"><strong>по смете</strong><span>анализ, поиск, правки макета и названия — после брифа</span></div>
    <div class="lx-price-card"><strong>отдельно</strong><span>госпошлины за подачу заявки по тарифам Роспатента (не входят в услугу доработки)</span></div>
  </div>
  <p class="lx-calc-note">Итоговая смета зависит от числа итераций, классов МКТУ и необходимости участия дизайнера. Не является публичной офертой.</p>
</section>

<section class="lx-section lx-reveal" id="risks">
  <span class="lx-section-label">Риски</span>
  <h2>Риски, если не доработать обозначение</h2>
  <div class="lx-risk-grid">
    <div class="lx-risk-item"><h3>Отказ в регистрации</h3><p>По абсолютным основаниям ст. 1483 ГК РФ — потеря времени и пошлин.</p></div>
    <div class="lx-risk-item"><h3>Претензии правообладателей</h3><p>Сходство с чужим знаком на однородных товарах (п. 6 ст. 1483 ГК РФ).</p></div>
    <div class="lx-risk-item"><h3>Слабая охрана</h3><p>Регистрация с широким дискламером неохраняемых слов сужает объём прав.</p></div>
  </div>
</section>

<?php include __DIR__ . '/blocks/lireate-reviews.inc.php'; ?>

<section class="lx-section lx-reveal" id="faq">
  <span class="lx-section-label">FAQ</span>
  <h2>Вопросы о доработке логотипа и названия</h2>
<?php foreach ($faq_entities as $faq_pair) : ?>
  <details class="lx-faq"><summary><?php echo htmlspecialchars($faq_pair[0], ENT_QUOTES, 'UTF-8'); ?></summary><p><?php echo $faq_pair[1]; ?></p></details>
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
      <form id="lireate-lead-form" data-lead-topic="Заявка: доработка логотипа и названия ТЗ" novalidate>
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
    <a class="lx-related-card" href="/mktu/"><span class="lx-related-card-type">Справочник</span><strong>Классы МКТУ</strong></a>
    <a class="lx-related-card" href="/process_registracii_tovarnogo_znaka/"><span class="lx-related-card-type">Гид</span><strong>Процесс регистрации ТЗ</strong></a>
    <a class="lx-related-card" href="/news/"><span class="lx-related-card-type">Блог</span><strong>Новости LIREATE</strong></a>
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

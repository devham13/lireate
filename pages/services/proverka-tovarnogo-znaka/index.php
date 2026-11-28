<?php
header('Content-Type: text/html; charset=utf-8');
$path = '/findtz/';
$host = isset($_SERVER['HTTP_HOST']) ? preg_replace('/[^a-zA-Z0-9.\-:]/', '', $_SERVER['HTTP_HOST']) : '';
$site_root = ($host !== '' ? 'https://' . $host : '');
$canonical = $site_root . $path;
$seo_title = 'Проверка товарного знака перед регистрацией — Лирейт';
$seo_description = 'Проверим название и логотип по реестрам Роспатента, оценим сходство и риски отказа. Закажите экспертную проверку перед подачей заявки в Роспатент.';
$og_type = 'website';
$og_image = $site_root . '/template/images/OG_image.jpg';
$lireate_phone_display = '+7 (960) 048-49-57';
$lireate_phone_tel = 'tel:+79600484957';
$lireate_telegram = 'https://t.me/lireate_patent';
$lireate_whatsapp = 'https://wa.me/79600484957';
require_once dirname(__DIR__) . '/_shared/lireate-service-messenger-include.inc.php';
require_once dirname(__DIR__) . '/_shared/lireate-sitewide-sidebar-v7.inc.php';
require_once dirname(__DIR__) . '/_shared/lireate-service-nav-links.php';
require_once dirname(__DIR__) . '/_shared/lireate-service-assets.php';
$lx_service_asset_dir = __DIR__;
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
    ['@type' => 'BreadcrumbList', 'itemListElement' => lireate_breadcrumb_schema_items($site_root, 'Проверка товарного знака', $canonical)],
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
  <link rel="stylesheet" href="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-service-dark.css', $lx_service_asset_dir); ?>">
  <link rel="stylesheet" href="/template/css/lireate-service-mobile-bar.css?v=20261111svc1" /><!-- lireate_design_service_mobile_bar_css_v1 -->
  <link rel="preload" href="<?php echo lireate_service_asset_href('assets/fonts/inter/inter-cyrillic.woff2', $lx_service_asset_dir); ?>" as="font" type="font/woff2" crossorigin>
  <link rel="stylesheet" href="<?php echo lireate_service_asset_href('assets/findtz-inter-font.css', $lx_service_asset_dir); ?>?v=20261128-fint1">
  <script type="application/ld+json"><?php echo json_encode($schema_graph, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?></script>
  <link rel="stylesheet" href="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-light-v4.css', $lx_service_asset_dir); ?>"><!-- lireate_light_v4 -->
  <link rel="stylesheet" href="<?php echo lireate_service_asset_href('assets/findtz-topnav-center.css', $lx_service_asset_dir); ?>">
  <link rel="stylesheet" href="/template/css/lireate-six-services-photo-v4.css?v=20261128-findtz6"><!-- lireate_findtz_photo_v4_hero -->
  <link rel="stylesheet" href="<?php echo lireate_service_asset_href('assets/findtz-inter-family-v1.css', $lx_service_asset_dir); ?>?v=20261128-fint1">
  <?php lireate_sitewide_sidebar_v7_head(); ?>
</head>
<body class="lsv7-managed lsv7-sidebar-present lireate-svc-body lireate-theme-light-v4 lireate-findtz-topnav-center lireate-findtz-inter-v1 lx-photo-v4">
<?php lireate_sitewide_sidebar_v7_render(); ?>
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

<?php /* UI batch 2026-11-27: visually remove in-page breadcrumb navigation on /findtz/.
 * Keep the structured BreadcrumbList in the existing JSON-LD above for SEO.
 * Shared breadcrumb templates for other pages are unchanged.
 */ ?>

<section class="lx-hero" id="top">
  <div class="lx-hero-mascot-slot" data-lx-mascot="search"></div>
  <div class="lx-photo-v4-copy">
    <p class="lx-eyebrow">LIREATE · товарные знаки · поиск по реестру</p>
    <h1>Проверка товарного знака перед регистрацией</h1>
    <p class="lx-hero-lead">Поиск по реестру Роспатента и оценка рисков до подачи заявки: сходство, однородность, основания отказа по ст. 1483 ГК РФ. Отчёт и рекомендации — по смете; регистрацию и исход экспертизы гарантировать нельзя.</p>
    <div class="lx-tags"><span>ФИПС</span><span>реестр ТЗ</span><span>МКТУ</span><span>ст. 1483 ГК РФ</span></div>
    <div class="lx-hero-cta lx-findtz-messenger-cta" aria-label="Связаться с Лирейт">
      <a class="lx-btn lx-findtz-messenger-btn lx-findtz-messenger-btn--wa" id="hero-whatsapp" href="<?php echo htmlspecialchars($lireate_whatsapp, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" data-lx-ym-goal="perehod_v_messendzher" aria-label="Написать в WhatsApp">
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M12 2C6.5 2 2 6.2 2 11.4c0 1.9.5 3.7 1.5 5.3L2 22l5.6-1.5c1.5.8 3.2 1.3 4.9 1.3 5.5 0 10-4.2 10-9.4S17.5 2 12 2zm5.8 13.5c-.2.6-1.2 1.1-1.7 1.1-.4 0-1.1.2-3.6-.9-3-1.3-5-4.4-5.1-4.6-.2-.3-1.2-1.6-1.2-3.1 0-1.5.8-2.2 1.1-2.5.3-.3.6-.4.8-.4.2 0 .4 0 .6.1.2 0 .4.2.5.5.2.5.6 1.7.7 1.8.1.1.1.3 0 .4 0 .1-.1.2-.2.3l-.3.3c-.1.1-.2.2-.1.4.1.2.5 1 1.1 1.6.8.7 1.5 1 1.7 1.1.2.1.3 0 .4-.1l.6-.7c.1-.2.3-.2.5-.1.8.3 2.8 1.3 3.2 1.5.3.2.5.3.6.4.1.2.1.7-.1 1.3z"/></svg>
        <span>WhatsApp</span>
      </a>
      <a class="lx-btn lx-findtz-messenger-btn lx-findtz-messenger-btn--max" id="hero-max" href="<?php echo htmlspecialchars($lireate_max, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" data-lx-ym-goal="perehod_v_messendzher" aria-label="Написать в MAX">
        <svg viewBox="0 0 100 100" aria-hidden="true" focusable="false"><path fill="currentColor" d="M50.76 0.26c27.54 0 49.13 22.34 49.13 49.89S77.61 99.49 51.02 99.49c-9.44 0-14.01-1.33-21.37-6.54-.51-.36-1.2-.27-1.63.19-5.66 6.04-20.17 10.28-20.83 2.03 0-14.39-7.19-23.73-7.19-45.3C0 21.55 23.22 0.26 50.76 0.26zm.77 24.55c-13.06-.69-23.26 8.39-25.51 22.57-1.86 11.75 1.44 26.07 4.27 26.79 1.2.31 4.08-1.88 6.18-3.86.39-.37 1-.44 1.46-.15 3.27 2 6.97 3.5 11.05 3.71 13.41.7 25.3-9.8 26.01-23.21.7-13.41-10.02-25.14-23.43-25.84z"/></svg>
        <span>MAX</span>
      </a>
      <a class="lx-btn lx-findtz-messenger-btn lx-findtz-messenger-btn--tg" id="hero-telegram" href="<?php echo htmlspecialchars($lireate_telegram, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" data-lx-ym-goal="perehod_v_telegramm" aria-label="Написать в Telegram">
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M9.78 18.65l.28-4.23 7.68-6.92c.34-.31-.07-.46-.52-.19L7.74 13.3 3.64 12c-.88-.25-.89-.86.2-1.3l15.97-6.16c.73-.33 1.43.18 1.15 1.3l-2.72 12.81c-.19.91-.74 1.13-1.5.71L12.6 16.3l-1.99 1.93c-.23.23-.42.42-.83.42z"/></svg>
        <span>Telegram</span>
      </a>
    </div>
  </div>
  <aside class="lx-photo-v4-expert-stack" aria-label="Команда Лирейт">
    <article class="lx-photo-v4-expert-card lx-photo-v4-expert-card--photo-only">
      <div class="lx-photo-v4-expert-media lx-photo-v4-expert-media--findtz" data-photo="findtz-hero">
        <picture>
          <source type="image/webp" srcset="/template/images/findtz/findtz-hero.webp?v=20261128-findtz6">
          <img src="/template/images/findtz/findtz-hero.jpg?v=20261128-findtz6" alt="Сотрудник LIREATE — экспертное сопровождение" width="1200" height="1800" loading="eager" decoding="async" fetchpriority="high">
        </picture>
        <div class="lx-photo-v4-expert-caption">
          <span class="lx-photo-v4-expert-caption-kicker">LIREATE / ЭКСПЕРТНОЕ СОПРОВОЖДЕНИЕ</span>
          <p class="lx-photo-v4-expert-caption-lead">Проверим обозначение по реестру до уплаты пошлин</p>
        </div>
      </div>
    </article>
  </aside>
  <div class="lx-hero-mini lx-hero-mini--after-photo">
    <div class="lx-mini-stat"><strong class="lx-stat-value">реестр</strong><span>Роспатент / ФИПС</span></div>
    <div class="lx-mini-stat"><strong class="lx-stat-value">3 шага</strong><span>бриф → поиск → отчёт</span></div>
    <div class="lx-mini-stat"><strong class="lx-stat-value">по смете</strong><span>услуги LIREATE</span></div>
  </div>
</section>

<div class="lx-glass lx-glass-anim lx-photo-v4-summary" aria-label="Этапы проверки">
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

<section class="lx-section lx-reveal" id="when">
  <span class="lx-section-label">Когда нужна</span>
  <h2>Проверка товарного знака: когда заказывать поиск</h2>
  <p>Проверка нужна до уплаты госпошлин за регистрацию. В базовом случае (один класс МКТУ, до 10 наименований) подача и экспертиза стоят 17 000 ₽. Ещё 18 000 ₽ за регистрацию платят после положительного решения Роспатента. Поэтому разумно сначала оценить, нет ли в реестре сходных обозначений и не описывает ли ваше название товар напрямую.</p>
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
  <p>На портале ФИПС доступен открытый поиск по реестру: можно ввести слово и увидеть действующие знаки и заявки. Это полезный первый шаг, но он не отвечает на вопрос, сочтёт ли экспертиза обозначения сходными до степени смешения в ваших классах МКТУ. Подсказки по самостоятельному поиску — в материале о том, <a href="/rospatent-fips-proverit-tovarnyy-znak-onlayn-besplatno/">как самостоятельно проверить знак в открытом реестре Роспатента</a>; мы дополняем поиск профессиональным сопоставлением и фиксируем выводы в отчёте.</p>
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
    <div class="lx-price-card"><strong>35 000 ₽</strong><span>ориентир госпошлин за регистрацию 1 класса — не входят в проверку</span></div>
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
  <p>Если конкурент уже подал сходную заявку, возможна процедура <a href="/vozrazhenie_protiv_registracii_tovarnogo_znaka/">возражения против регистрации</a> по ст. 1493 ГК РФ — это отдельная услуга от предварительной проверки вашего знака.</p>
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
  <p>После проверки логичный следующий шаг — регистрация или продление уже действующего знака. Для действующих свидетельств смотрите <a href="/prodlenie_tovarnogo_znaka/">продление товарного знака</a>; если спор касается чужой заявки, а не вашей — <a href="/vozrazhenie_protiv_registracii_tovarnogo_znaka/">возражение против регистрации</a>.</p>
</section>

<?php include __DIR__ . '/blocks/lireate-reviews.inc.php'; ?>

<section class="lx-section lx-reveal" id="faq">
  <span class="lx-section-label">FAQ</span>
  <h2>Вопросы о проверке товарного знака</h2>
<?php foreach ($faq_entities as $faq_pair) : ?>
  <details class="lx-faq"><summary><?php echo htmlspecialchars($faq_pair[0], ENT_QUOTES, 'UTF-8'); ?></summary><p><?php echo htmlspecialchars($faq_pair[1], ENT_QUOTES, 'UTF-8'); ?></p></details>
<?php endforeach; ?>
</section>

<?php include __DIR__ . '/blocks/lireate-vk-cases.inc.php'; ?>
<?php
$lx_lead_topic = 'Заявка: проверка товарного знака';
$lx_lead_tomail = 'Проверка товарного знака — заявка с сайта';
$lx_lead_consent_form = 'lx-proverka-tz';
include __DIR__ . '/blocks/lireate-contact-section.inc.php';
?>

<section class="lx-section lx-reveal" id="related">
  <span class="lx-section-label">LIREATE</span>
  <h2>Смежные услуги</h2>
  <div class="lx-related-grid">
    <a class="lx-related-card" href="/registraciya_tovarnogo_znaka/"><span class="lx-related-card-type">Услуга</span><strong>Регистрация товарного знака</strong></a>
    <a class="lx-related-card" href="/kak-zaregistrirovat-tovarnyy-znak-samostoyatelno-2026/"><span class="lx-related-card-type">Инструкция</span><strong>Как зарегистрировать товарный знак самостоятельно</strong></a>
    <a class="lx-related-card" href="/prodlenie_tovarnogo_znaka/"><span class="lx-related-card-type">Услуга</span><strong>Продление товарного знака</strong></a>
    <a class="lx-related-card" href="/vozrazhenie_protiv_registracii_tovarnogo_znaka/"><span class="lx-related-card-type">Услуга</span><strong>Возражение против регистрации</strong></a>
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
  <a id="dock-whatsapp" class="lx-dock-whatsapp" href="<?php echo htmlspecialchars($lireate_whatsapp, ENT_QUOTES, 'UTF-8'); ?>" title="WhatsApp" aria-label="WhatsApp" target="_blank" rel="noopener noreferrer" data-lx-ym-goal="perehod_v_messendzher"></a>
  <a id="dock-max" class="lx-dock-max" href="<?php echo htmlspecialchars($lireate_max, ENT_QUOTES, 'UTF-8'); ?>" title="MAX" aria-label="MAX" target="_blank" rel="noopener noreferrer"></a>
</aside>

<script src="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-motion-init.js', $lx_service_asset_dir); ?>"></script>
<script src="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-reveal.js', $lx_service_asset_dir); ?>"></script>
<script src="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-hero-panel.js', $lx_service_asset_dir); ?>"></script>
<script src="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-carousel.js', $lx_service_asset_dir); ?>"></script>
<script src="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-steps-walk.js', $lx_service_asset_dir); ?>"></script>
<script src="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-mascot.js', $lx_service_asset_dir); ?>"></script>
<script src="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-service-extras.js', $lx_service_asset_dir); ?>"></script>
<script src="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-reviews.js', $lx_service_asset_dir); ?>"></script>
<?php include __DIR__ . '/lireate-lead-scripts.inc.php'; ?>
<?php include dirname(__DIR__) . '/_shared/lireate-metrika.inc.php'; ?>
<script src="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-metrika-goals.js', $lx_service_asset_dir); ?>"></script>
  <script defer src="/template/js/lireate-service-metrika-goals.js?v=20261111svc3"></script><!-- lireate_design_service_mobile_bar_js_v1 -->
  <script defer src="/template/js/lireate-service-mobile-bar.js?v=20261111svc3"></script>
<script src="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-light-v4.js', $lx_service_asset_dir); ?>"></script><!-- lireate_light_v4 -->
  <script defer src="/template/js/lireate-six-services-photo-v4.js?v=20261128-findtz6"></script><!-- lireate_findtz_photo_v4_hero -->
</body>
</html>

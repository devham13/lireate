<?php
header('Content-Type: text/html; charset=utf-8');
$path = '/spory_po_ispolzovaniju_tovarnogo_znaka/';
$host = isset($_SERVER['HTTP_HOST']) ? preg_replace('/[^a-zA-Z0-9.\-:]/', '', $_SERVER['HTTP_HOST']) : '';
$site_root = ($host !== '' ? 'https://' . $host : '');
$canonical = $site_root . $path;
$seo_title = 'Споры по товарным знакам: судебная защита — Лирейт';
$seo_description = 'Защита товарного знака и помощь при требованиях правообладателя. Проверка нарушения, подготовка претензий и представительство в судебном споре.';
$og_type = 'website';
$og_image = $site_root . '/local/templates/lireate/images/og-default.jpg';
$lireate_phone_display = '+7 (960) 048-49-57';
$lireate_phone_tel = 'tel:+79600484957';
$lireate_telegram = 'https://t.me/lireate_patent';
$lireate_whatsapp = 'https://wa.me/79600484957';
require_once dirname(__DIR__) . '/_shared/lireate-service-messenger-include.inc.php';
require_once dirname(__DIR__) . '/_shared/lireate-service-nav-links.php';
require_once dirname(__DIR__) . '/_shared/lireate-service-assets.php';
$lx_service_asset_dir = __DIR__;
$lireate_max = LIREATE_MAX_CHAT_URL;
$lireate_metrika_id = getenv('LIREATE_YANDEX_METRIKA_ID');
if ($lireate_metrika_id === false || $lireate_metrika_id === '') {
    $lireate_metrika_id = '526' . '22734'; // pragma: allowlist secret
}
$faq_entities = array(
  array(
    'Какие документы передать для оценки спора?',
    'Свидетельство или выписка по товарному знаку, материалы об использовании обозначения (сайт, упаковка, реклама), договоры лицензии, переписка с контрагентом. Если к вам поступила претензия — её текст и сроки ответа. Перечень уточняется по ситуации.',
  ),
  array(
    'Что входит в сопровождение и как определяется стоимость?',
    'После анализа документов согласуем объём: проверка знака и сходства, претензия или отзыв, переговоры, суд. Услуги LIREATE — по смете; госпошлины по НК РФ — отдельно.',
  ),
  array(
    'От чего зависит выбор судебной стратегии?',
    'От роли сторон, предмета требований и доказательств. Возможны иски по ст. 1484, 1515 ГК РФ; для споров с юрлицами в общем порядке нужна досудебная претензия (ст. 4 АПК РФ). Подсудность и сроки зависят от состава сторон и требований — оценим по документам.',
  ),
);
$schema_graph = array(
  '@context' => 'https://schema.org',
  '@graph' => array(
    array(
      '@type' => 'Service',
      '@id' => $canonical . '#service',
      'name' => 'Судебные споры по товарным знакам',
      'url' => $canonical,
      'description' => $seo_description,
      'areaServed' => 'RU',
      'provider' => array('@id' => $canonical . '#organization'),
    ),
    array(
      '@type' => array('LegalService', 'Organization'),
      '@id' => $canonical . '#organization',
      'name' => 'LIREATE',
      'url' => $site_root . '/',
      'telephone' => '+7-960-048-49-57',
      'sameAs' => array($lireate_telegram, $lireate_whatsapp, $lireate_max),
    ),
    array(
      '@type' => 'BreadcrumbList',
      'itemListElement' => lireate_breadcrumb_schema_items($site_root, 'Споры по товарным знакам', $canonical),
    ),
    array(
      '@type' => 'FAQPage',
      'mainEntity' => array_map(function ($pair) {
        return array(
          '@type' => 'Question',
          'name' => $pair[0],
          'acceptedAnswer' => array('@type' => 'Answer', 'text' => strip_tags($pair[1])),
        );
      }, $faq_entities),
    ),
  ),
);
$lx_breadcrumb_current = 'Споры по товарным знакам';
$lx_lead_submit_label = 'Обсудить документы и действия по спору';
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
      <a href="#spory">Споры</a>
      <a href="#storony">Стороны</a>
      <a href="#proverka">Проверка</a>
      <a href="#etapy">Этапы</a>
      <a href="#stoimost">Стоимость</a>
      <a href="#faq">FAQ</a>
      <a href="#contact">Заявка</a>
    </nav>
    <a class="lx-btn lx-btn-primary" href="#contact">Обсудить спор</a>
  </header>
</div>

<main id="primary" class="lireate-svc-page spory-po-ispolzovaniju-tovarnogo-znaka-page" role="main" tabindex="-1">

<?php include __DIR__ . '/blocks/lireate-breadcrumbs.inc.php'; ?>

<section class="lx-hero" id="top">
  <div class="lx-hero-mascot-slot" data-lx-mascot="registration"></div>
  <div>
    <p class="lx-eyebrow">LIREATE · товарные знаки · судебные споры</p>
    <h1>Судебные споры по товарным знакам</h1>
    <p class="lx-hero-lead">Защита вашего знака и помощь, если к вам предъявили требование: проверим нарушение, подготовим претензию и позицию для суда.</p>
    <div class="lx-tags" aria-label="Темы услуги">
      <span>ст. 1484</span><span>ст. 1515</span><span>ст. 4 АПК</span>
    </div>
    <div class="lx-hero-cta">
      <a class="lx-btn lx-btn-primary" href="#contact">Обсудить документы и действия по спору</a>
      <a class="lx-btn lx-btn-ghost lx-btn--with-icon" id="hero-telegram" href="<?php echo htmlspecialchars($lireate_telegram, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" data-lx-icon="telegram"><span>Написать в Telegram</span></a>
      <a class="lx-btn lx-btn-ghost lx-btn--with-icon" id="hero-max" href="<?php echo htmlspecialchars(lireate_service_max_channel_url(), ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" data-lx-icon="max"><span>Написать в Max</span></a>
    </div>
    <div class="lx-hero-mini">
      <div class="lx-mini-stat"><strong class="lx-stat-value">истец</strong><span>защита зарегистрированного знака</span></div>
      <div class="lx-mini-stat"><strong class="lx-stat-value">ответчик</strong><span>ответ на претензию и иск</span></div>
      <div class="lx-mini-stat"><strong class="lx-stat-value">ст. 1515</strong><span>компенсация по усмотрению суда</span></div>
    </div>
  </div>
  <div class="lx-glass lx-glass-anim" aria-label="Маршрут спора по товарному знаку">
    <div class="lx-panel-head">
      <span>ТЗ · маршрут</span>
      <span class="lx-live">ГК РФ ч. 4</span>
    </div>
    <ul class="lx-feed">
      <li>Проверка свидетельства и факта использования обозначения</li>
      <li>Досудебная претензия (ст. 4 АПК РФ)</li>
      <li>Иск или защита по ст. 1484, 1515 ГК РФ</li>
    </ul>
    <div class="lx-route" role="img" aria-label="Маршрут: проверка, претензия, суд">
      <ol class="lx-route-steps">
        <li><span class="lx-route-node">Аудит</span><span class="lx-route-label">знак</span></li>
        <li><span class="lx-route-node">Претензия</span><span class="lx-route-label">переговоры</span></li>
        <li><span class="lx-route-node">Суд</span><span class="lx-route-label">по документам</span></li>
      </ol>
    </div>
  </div>
</section>

<section class="lx-section lx-reveal" id="spory">
  <span class="lx-section-label">Предмет</span>
  <h2>Какие споры ведём</h2>
  <p class="lx-hero-lead" style="margin-bottom:12px;font-size:0.95rem;">Сопровождаем споры об использовании товарного знака и сходных обозначений в гражданском и арбитражном порядке.</p>
  <div class="lx-carousel" data-lx-carousel tabindex="0" aria-label="Типы споров по товарным знакам">
    <button type="button" class="lx-carousel-prev" aria-label="Предыдущий слайд">‹</button>
    <div class="lx-carousel-viewport">
      <ol class="lx-carousel-track">
        <li class="lx-carousel-slide"><article class="lx-card"><h3>Незаконное использование знака</h3><p>Требование прекратить обозначение на товарах, в рекламе, на сайте (ст. 1484 ГК РФ).</p></article></li>
        <li class="lx-carousel-slide"><article class="lx-card"><h3>Сходное обозначение</h3><p>Оценка сходства до степени смешения и однородности товаров.</p></article></li>
        <li class="lx-carousel-slide"><article class="lx-card"><h3>Претензия правообладателя</h3><p>Разбор требований и подготовка ответа до суда.</p></article></li>
        <li class="lx-carousel-slide"><article class="lx-card"><h3>Компенсация</h3><p>Требования по ст. 1515 ГК РФ — сумму определяет суд.</p></article></li>
        <li class="lx-carousel-slide"><article class="lx-card"><h3>Лицензия и договор</h3><p>Споры о правомерности использования по договору.</p></article></li>
      </ol>
    </div>
    <button type="button" class="lx-carousel-next" aria-label="Следующий слайд">›</button>
    <div class="lx-carousel-dots"></div>
  </div>
</section>

<section class="lx-section lx-reveal" id="storony">
  <span class="lx-section-label">Роли</span>
  <h2>Истец и ответчик: разные задачи</h2>
  <div class="lx-compare" data-lx-compare>
    <div class="lx-compare-panel lx-compare-before">
      <h3>Правообладатель (истец)</h3>
      <ul>
        <li>Фиксация нарушения и сходства</li>
        <li>Претензия и расчёт требований</li>
        <li>Иск о прекращении использования и компенсации (ст. 1515)</li>
      </ul>
    </div>
    <div class="lx-compare-panel lx-compare-after" aria-hidden="true">
      <h3>Ответчик</h3>
      <ul>
        <li>Анализ претензии и сроков ответа</li>
        <li>Возражения по факту использования и однородности</li>
        <li>Позиция в суде по документам сторон</li>
      </ul>
    </div>
    <input type="range" class="lx-compare-range" min="0" max="100" value="50" aria-label="Сдвиньте для сравнения ролей">
  </div>
  <p style="margin-top:1rem">Оспаривание регистрации и прекращение охраны знака — отдельные процедуры: <a href="/vozrazhenie_protiv_registracii_tovarnogo_znaka/">возражение против регистрации</a>, <a href="/prekrashhenie_pravovoj_ohrany/">прекращение правовой охраны</a>.</p>
</section>

<section class="lx-section lx-reveal" id="proverka">
  <span class="lx-section-label">Анализ</span>
  <h2>Проверка знака и использования</h2>
  <p>Сверяем свидетельство, классы МКТУ и фактическое использование обозначений. Для предварительной проверки чужих знаков можно начать с <a href="/findtz/">проверки товарного знака</a> — это ориентир, не замена юридическому анализу.</p>
  <div class="lx-risk-grid">
    <div class="lx-risk-item"><h3>Реестр и приоритет</h3><p>Сопоставляем ваш знак с противопоставляемыми обозначениями и датами.</p></div>
    <div class="lx-risk-item"><h3>Использование на рынке</h3><p>Собираем доказательства введения в гражданский оборот или, наоборот, оспариваем доводы стороны.</p></div>
    <div class="lx-risk-item"><h3>Смежные споры</h3><p>При пересечении с авторским правом — <a href="/zashchita_avtorskih_prav/">защита авторских прав</a>.</p></div>
  </div>
</section>

<section class="lx-section lx-reveal" id="etapy">
  <span class="lx-section-label">Маршрут</span>
  <h2>Этапы сопровождения</h2>
  <div class="lx-steps-walk" data-lx-steps-walk>
    <div class="lx-steps-walk-mascot-slot" aria-hidden="true"></div>
    <ol class="lx-steps-walk-list">
      <li class="lx-steps-walk-step"><span class="lx-card-num">01</span><h3>Бриф и документы</h3><p>Свидетельство, материалы использования, претензия или иск при наличии.</p></li>
      <li class="lx-steps-walk-step"><span class="lx-card-num">02</span><h3>Юридический анализ</h3><p>Сходство, однородность, основания по ст. 1484, 1515 ГК РФ.</p></li>
      <li class="lx-steps-walk-step"><span class="lx-card-num">03</span><h3>Досудебная претензия</h3><p>Подготовка или ответ; <strong>ст. 4 АПК РФ</strong> — обязательный порядок в общем случае для споров с юрлицами.</p></li>
      <li class="lx-steps-walk-step"><span class="lx-card-num">04</span><h3>Суд</h3><p>Иск, отзыв, представительство. Подсудность и процессуальные сроки зависят от состава сторон и требований — оценим по документам.</p></li>
    </ol>
  </div>
</section>

<section class="lx-section lx-reveal" id="stoimost">
  <span class="lx-section-label">Документы</span>
  <h2>Стоимость и документы</h2>
  <p>Перечень документов для старта — в <a href="#faq">FAQ</a>. Стоимость сопровождения LIREATE согласуем по смете после анализа; госпошлина за иск — по ст. 333.19 или 333.21 НК РФ.</p>
  <div class="lx-price-grid">
    <div class="lx-price-card"><strong>по смете</strong><span>аудит, претензия, суд — после брифа</span></div>
    <div class="lx-price-card"><strong>НК РФ</strong><span>госпошлины и сборы — отдельно, по правилам суда</span></div>
  </div>
  <p>По <strong>ст. 1515 ГК РФ</strong> возможна компенсация от 10&nbsp;000 до 5&nbsp;000&nbsp;000 руб. либо в двукратном размере стоимости товаров или права использования — конкретные суммы определяет суд.</p>
</section>

<section class="lx-section lx-reveal" id="faq">
  <span class="lx-section-label">FAQ</span>
  <h2>Вопросы о спорах по товарным знакам</h2>
<?php foreach ($faq_entities as $faq_pair) : ?>
  <details class="lx-faq"><summary><?php echo htmlspecialchars($faq_pair[0], ENT_QUOTES, 'UTF-8'); ?></summary><p><?php echo $faq_pair[1]; ?></p></details>
<?php endforeach; ?>
</section>

<?php include __DIR__ . '/blocks/lireate-vk-cases.inc.php'; ?>

<?php
$lx_lead_topic = 'Заявка: споры по товарным знакам';
$lx_lead_tomail = 'Споры по товарным знакам — заявка с сайта';
$lx_lead_consent_form = 'lx-spory-tm';
include __DIR__ . '/blocks/lireate-contact-section.inc.php';
?>

<section class="lx-section lx-reveal" id="related">
  <span class="lx-section-label">LIREATE</span>
  <h2>Смежные услуги</h2>
  <div class="lx-related-grid">
    <a class="lx-related-card" href="/vozrazhenie_protiv_registracii_tovarnogo_znaka/"><span class="lx-related-card-type">Услуга</span><strong>Возражение против регистрации ТЗ</strong></a>
    <a class="lx-related-card" href="/prekrashhenie_pravovoj_ohrany/"><span class="lx-related-card-type">Услуга</span><strong>Прекращение правовой охраны</strong></a>
    <a class="lx-related-card" href="/zashchita_avtorskih_prav/"><span class="lx-related-card-type">Услуга</span><strong>Защита авторских прав</strong></a>
    <a class="lx-related-card" href="/findtz/"><span class="lx-related-card-type">Услуга</span><strong>Проверка товарного знака</strong></a>
    <a class="lx-related-card" href="/registraciya_tovarnogo_znaka/"><span class="lx-related-card-type">Услуга</span><strong>Регистрация товарного знака</strong></a>
  </div>
</section>

</main>

<?php include __DIR__ . '/lireate-service-footer.inc.php'; ?>
<?php include __DIR__ . '/blocks/lireate-sticky-mobile.inc.php'; ?>

<aside class="lx-dock" aria-label="Быстрый контакт">
  <a href="#contact" class="lx-dock-lead" title="Обсудить документы и действия по спору" aria-label="Обсудить документы и действия по спору"></a>
  <a id="dock-telegram" href="<?php echo htmlspecialchars($lireate_telegram, ENT_QUOTES, 'UTF-8'); ?>" title="Telegram" aria-label="Telegram" target="_blank" rel="noopener noreferrer"></a>
  <a id="dock-max" class="lx-dock-max" href="<?php echo htmlspecialchars($lireate_max, ENT_QUOTES, 'UTF-8'); ?>" title="MAX" aria-label="MAX" target="_blank" rel="noopener noreferrer"></a>
</aside>

<script src="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-motion-init.js', $lx_service_asset_dir); ?>"></script>
<script src="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-reveal.js', $lx_service_asset_dir); ?>"></script>
<script src="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-hero-panel.js', $lx_service_asset_dir); ?>"></script>
<script src="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-carousel.js', $lx_service_asset_dir); ?>"></script>
<script src="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-steps-walk.js', $lx_service_asset_dir); ?>"></script>
<script src="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-mascot.js', $lx_service_asset_dir); ?>"></script>
<script src="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-service-extras.js', $lx_service_asset_dir); ?>"></script>
<script src="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-compare.js', $lx_service_asset_dir); ?>"></script>
<script src="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-mobile-bar.js', $lx_service_asset_dir); ?>"></script>
<script src="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-breadcrumbs.js', $lx_service_asset_dir); ?>"></script>
<?php include __DIR__ . '/lireate-lead-scripts.inc.php'; ?>
<?php include dirname(__DIR__) . '/_shared/lireate-metrika.inc.php'; ?>
<script src="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-metrika-goals.js', $lx_service_asset_dir); ?>"></script>
  <script defer src="/template/js/lireate-service-metrika-goals.js?v=20261111svc3"></script><!-- lireate_design_service_mobile_bar_js_v1 -->
</body>
</html>

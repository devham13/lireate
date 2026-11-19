<?php
header('Content-Type: text/html; charset=utf-8');
$path = '/prekrashhenie_pravovoj_ohrany/';
$host = isset($_SERVER['HTTP_HOST']) ? preg_replace('/[^a-zA-Z0-9.\-:]/', '', $_SERVER['HTTP_HOST']) : '';
$site_root = ($host !== '' ? 'https://' . $host : '');
$canonical = $site_root . $path;
$seo_title = 'Оспаривание товарного знака и защита регистрации — Лирейт';
$seo_description = 'Анализ оснований оспаривания товарного знака, подготовка возражений и защита регистрации. Определим подходящий административный или судебный порядок.';
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
    'Свидетельство или выписка по знаку, решения и переписка Роспатента, материалы об использовании обозначения, договоры и доказательства заинтересованности. Для спора о неиспользовании — перечень товаров и доказательства введения в оборот или их отсутствие. Список уточняется по ситуации.',
  ),
  array(
    'Что входит в сопровождение и как определяется стоимость?',
    'После анализа согласуем объём: выбор административного или судебного порядка, подготовка возражения или иска, представительство. Услуги LIREATE — по смете; госпошлины по НК РФ — отдельно.',
  ),
  array(
    'От чего зависит выбор судебной стратегии?',
    'От основания (ст. 1512, 1513, 1514, 1486 ГК РФ), стадии регистрации, доказательств заинтересованности и процессуальных требований. Подсудность и сроки зависят от состава сторон и требований — оценим по документам.',
  ),
  array(
    'Как устроено прекращение охраны за неиспользование (ст. 1486 ГК РФ)?',
    'Правовая охрана знака может быть прекращена досрочно, если обозначение не использовалось непрерывно в течение любых трёх лет после регистрации. Заявитель направляет правообладателю письменное предложение о досрочном прекращении (не ранее чем по истечении трёх лет с регистрации); срок ответа — два месяца со дня направления предложения. Иск в суд по интеллектуальным правам подаётся в течение тридцати дней после истечения срока ответа. Детали процедуры смотрим по документам сторон.',
  ),
);
$schema_graph = array(
  '@context' => 'https://schema.org',
  '@graph' => array(
    array(
      '@type' => 'Service',
      '@id' => $canonical . '#service',
      'name' => 'Оспаривание товарного знака и защита регистрации',
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
      'itemListElement' => lireate_breadcrumb_schema_items($site_root, 'Оспаривание и прекращение охраны ТЗ', $canonical),
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
$lx_breadcrumb_current = 'Оспаривание и прекращение охраны ТЗ';
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
      <a href="#osnovaniya">Основания</a>
      <a href="#rospatent-sud">Порядок</a>
      <a href="#zainteresovannost">Заинтересованность</a>
      <a href="#zashchita-registracii">Защита</a>
      <a href="#etapy-stoimost">Этапы</a>
      <a href="#prekrashchenie-tovarnogo-znaka-neispolzovanie">Неиспользование</a>
      <a href="#faq">FAQ</a>
      <a href="#contact">Заявка</a>
    </nav>
    <a class="lx-btn lx-btn-primary" href="#contact">Обсудить спор</a>
  </header>
</div>

<main id="primary" class="lireate-svc-page prekrashhenie-pravovoj-ohrany-page" role="main" tabindex="-1">

<?php include __DIR__ . '/blocks/lireate-breadcrumbs.inc.php'; ?>

<section class="lx-hero" id="top">
  <div class="lx-hero-mascot-slot" data-lx-mascot="registration"></div>
  <div>
    <p class="lx-eyebrow">LIREATE · товарные знаки · оспаривание и охрана</p>
    <h1>Оспаривание товарного знака и защита регистрации</h1>
    <p class="lx-hero-lead">Разберём основания по ГК РФ, подготовим возражение в Роспатент или позицию в суде и сопроводим защиту вашей регистрации.</p>
    <div class="lx-tags" aria-label="Темы услуги">
      <span>ст. 1512 · 1513</span><span>ст. 1514</span><span>ст. 1486</span>
    </div>
    <div class="lx-hero-cta">
      <a class="lx-btn lx-btn-primary" href="#contact">Обсудить документы и действия по спору</a>
      <a class="lx-btn lx-btn-ghost lx-btn--with-icon" id="hero-telegram" href="<?php echo htmlspecialchars($lireate_telegram, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" data-lx-icon="telegram"><span>Написать в Telegram</span></a>
      <a class="lx-btn lx-btn-ghost lx-btn--with-icon" id="hero-max" href="<?php echo htmlspecialchars($lireate_max, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" data-lx-icon="max"><span>Написать в Max</span></a>
    </div>
  </div>
  <div class="lx-glass lx-glass-anim" aria-label="Административный и судебный маршрут">
    <ul class="lx-feed">
      <li>Анализ основания: ст. 1512, 1513, 1514 или 1486 ГК РФ</li>
      <li>Возражение в Роспатент или иск в суд</li>
      <li>Доказательства заинтересованности и использования</li>
    </ul>
  </div>
</section>

<section class="lx-section lx-reveal" id="osnovaniya">
  <span class="lx-section-label">Основания</span>
  <h2>Основания и предмет оспаривания</h2>
  <p>Оспаривание может касаться как решения о регистрации, так и охраны уже зарегистрированного знака. Состав требований зависит от выбранной процедуры.</p>
  <div class="lx-card-grid">
    <article class="lx-card"><h3>Ст. 1512, 1513 ГК РФ</h3><p>Возражение против предоставления правовой охраны зарегистрированному товарному знаку в Роспатент (административный порядок).</p></article>
    <article class="lx-card"><h3>Ст. 1514 ГК РФ</h3><p>Иные основания прекращения правовой охраны, в том числе общепринятое обозначение и введение в заблуждение — по обстоятельствам дела.</p></article>
    <article class="lx-card"><h3>Ст. 1486 ГК РФ</h3><p>Досрочное прекращение за неиспользование — отдельный маршрут; см. <a href="#prekrashchenie-tovarnogo-znaka-neispolzovanie">раздел ниже</a>.</p></article>
  </div>
  <p>Обращение по ст. 1493 ГК РФ (на этапе заявки) и подробная процедура возражения — на странице <a href="/vozrazhenie_protiv_registracii_tovarnogo_znaka/">возражения против регистрации</a>. Судебные споры об использовании чужого знака — <a href="/spory_po_ispolzovaniju_tovarnogo_znaka/">споры по использованию</a>. Материал <a href="/annulirovanie_chuzhogo_tovarnogo_znaka/">об аннулировании чужого знака</a> в блоге носит справочный характер.</p>
</section>

<section class="lx-section lx-reveal" id="rospatent-sud">
  <span class="lx-section-label">Процедура</span>
  <h2>Роспатент и судебный порядок</h2>
  <p>По <strong>ст. 1512, 1513 ГК РФ</strong> заинтересованное лицо вправе подать в Роспатент возражение против предоставления правовой охраны товарному знаку. Рассмотрение ведётся коллегией; исход зависит от представленных доводов и доказательств.</p>
  <p>Судебный порядок применяется, когда этого требуют основания или стадия спора (в том числе по <strong>ст. 1486</strong> и иным нормам). Подсудность и сроки зависят от состава сторон и требований — оценим по документам.</p>
</section>

<section class="lx-section lx-reveal" id="zainteresovannost">
  <span class="lx-section-label">Доказательства</span>
  <h2>Доказательства заинтересованности</h2>
  <p>Для возражения по ст. 1512–1513 нужно показать, что решение Роспатента затрагивает ваши права: сходство обозначений, однородность товаров, факт хозяйственной деятельности. Собираем договоры, свидетельства, материалы из открытых реестров и использования на рынке.</p>
</section>

<section class="lx-section lx-reveal" id="zashchita-registracii">
  <span class="lx-section-label">Защита прав</span>
  <h2>Защита действующей регистрации</h2>
  <p>Если к вашему знаку готовят возражение или иск о прекращении охраны, готовим отзыв: подтверждаем использование, оспариваем заинтересованность и доводы по ст. 1512–1514 или 1486. Стратегия согласуется после анализа документов противоположной стороны.</p>
</section>

<section class="lx-section lx-reveal" id="etapy-stoimost">
  <span class="lx-section-label">Сопровождение</span>
  <h2>Этапы и стоимость</h2>
  <ol class="lx-steps-walk-list" style="list-style:none;padding:0">
    <li class="lx-steps-walk-step"><span class="lx-card-num">01</span><h3>Бриф и документы</h3><p>Свидетельство, переписка с Роспатентом, сведения об использовании.</p></li>
    <li class="lx-steps-walk-step"><span class="lx-card-num">02</span><h3>Выбор порядка</h3><p>Возражение (ст. 1512–1513), суд (ст. 1486, 1514) или комбинация шагов.</p></li>
    <li class="lx-steps-walk-step"><span class="lx-card-num">03</span><h3>Подача и представительство</h3><p>Подготовка текста, приложений, участие в коллегии или суде.</p></li>
  </ol>
  <div class="lx-price-grid" style="margin-top:1rem">
    <div class="lx-price-card"><strong>по смете</strong><span>услуги LIREATE после анализа документов</span></div>
    <div class="lx-price-card"><strong>НК РФ</strong><span>госпошлины Роспатента и суда — по таблицам, отдельно</span></div>
  </div>
</section>

<section class="lx-section lx-reveal" id="prekrashchenie-tovarnogo-znaka-neispolzovanie">
  <span class="lx-section-label">Раздел S06</span>
  <h2>Прекращение охраны товарного знака за неиспользование</h2>
  <p>По <strong>ст. 1486 ГК РФ</strong> правовая охрана товарного знака может быть прекращена досрочно, если обозначение не использовалось непрерывно в течение любых трёх лет, следующих за датой его регистрации.</p>

  <h3>Цели и основания обращения</h3>
  <p>Заявитель добивается снятия блокирующего знака или защищается от требования о прекращении охраны собственного знака. Основание — факт неиспользования в течение указанного в законе срока.</p>

  <h3>Заинтересованность и перечень товаров</h3>
  <p>В позиции фиксируют товары и услуги, по которым знак не использовался, и доказывают заинтересованность заявителя в досрочном прекращении или, наоборот, показывают целевое использование правообладателем.</p>

  <h3>Досудебное предложение</h3>
  <p>Заинтересованное лицо направляет правообладателю письменное предложение о досрочном прекращении правовой охраны. Предложение не может быть направлено ранее чем по истечении трёх лет после регистрации знака. Правообладатель вправе представить возражения в течение <strong>двух месяцев</strong> со дня направления предложения.</p>

  <h3>Доказательства использования</h3>
  <p>Для защиты регистрации собирают контракты, маркировку, рекламу, отчётность и иные материалы о введении знака в гражданский оборот по заявленным классам. Для истца — наоборот, фиксируют отсутствие использования в спорный период.</p>

  <h3>Представительство</h3>
  <p>Иск по делам о нарушении исключительных прав на результаты интеллектуальной деятельности подаётся в суд по правилам судопроизводства по интеллектуальным правам. Иск о досрочном прекращении может быть подан в течение <strong>тридцати дней</strong> после истечения срока для ответа на досудебное предложение. Сопровождение LIREATE — по смете после анализа документов.</p>
</section>

<section class="lx-section lx-reveal" id="faq">
  <span class="lx-section-label">FAQ</span>
  <h2>Вопросы об оспаривании и неиспользовании</h2>
<?php foreach ($faq_entities as $faq_pair) : ?>
  <details class="lx-faq"><summary><?php echo htmlspecialchars($faq_pair[0], ENT_QUOTES, 'UTF-8'); ?></summary><p><?php echo $faq_pair[1]; ?></p></details>
<?php endforeach; ?>
</section>

<?php include __DIR__ . '/blocks/lireate-vk-cases.inc.php'; ?>

<?php
$lx_lead_topic = 'Заявка: оспаривание и прекращение охраны ТЗ';
$lx_lead_tomail = 'Оспаривание ТЗ — заявка с сайта';
$lx_lead_consent_form = 'lx-prekrash-ohr';
include __DIR__ . '/blocks/lireate-contact-section.inc.php';
?>

<section class="lx-section lx-reveal" id="related">
  <span class="lx-section-label">LIREATE</span>
  <h2>Смежные страницы</h2>
  <div class="lx-related-grid">
    <a class="lx-related-card" href="/vozrazhenie_protiv_registracii_tovarnogo_znaka/"><span class="lx-related-card-type">Услуга</span><strong>Возражение против регистрации</strong></a>
    <a class="lx-related-card" href="/spory_po_ispolzovaniju_tovarnogo_znaka/"><span class="lx-related-card-type">Услуга</span><strong>Споры по использованию ТЗ</strong></a>
    <a class="lx-related-card" href="/annulirovanie_chuzhogo_tovarnogo_znaka/"><span class="lx-related-card-type">Блог</span><strong>Аннулирование чужого знака</strong></a>
    <a class="lx-related-card" href="/findtz/"><span class="lx-related-card-type">Услуга</span><strong>Проверка товарного знака</strong></a>
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
<script src="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-mascot.js', $lx_service_asset_dir); ?>"></script>
<script src="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-compare.js', $lx_service_asset_dir); ?>"></script>
<script src="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-mobile-bar.js', $lx_service_asset_dir); ?>"></script>
<script src="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-breadcrumbs.js', $lx_service_asset_dir); ?>"></script>
<?php include __DIR__ . '/lireate-lead-scripts.inc.php'; ?>
<?php include dirname(__DIR__) . '/_shared/lireate-metrika.inc.php'; ?>
<script src="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-metrika-goals.js', $lx_service_asset_dir); ?>"></script>
  <script defer src="/template/js/lireate-service-metrika-goals.js?v=20261111svc3"></script><!-- lireate_design_service_mobile_bar_js_v1 -->
</body>
</html>
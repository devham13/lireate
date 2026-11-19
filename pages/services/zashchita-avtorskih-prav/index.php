<?php
header('Content-Type: text/html; charset=utf-8');
$path = '/zashchita_avtorskih_prav/';
$host = isset($_SERVER['HTTP_HOST']) ? preg_replace('/[^a-zA-Z0-9.\-:]/', '', $_SERVER['HTTP_HOST']) : '';
$site_root = ($host !== '' ? 'https://' . $host : '');
$canonical = $site_root . $path;
$seo_title = 'Защита авторских прав: претензии и судебные споры — Лирейт';
$seo_description = 'Помощь при копировании фотографий, текстов, дизайна и программ. Проверим права и доказательства, подготовим претензию и позицию по судебному спору.';
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
    'Исходные файлы и материалы, договоры с авторами и подрядчиками, переписка, скриншоты или иная фиксация использования. При наличии — сведения о регистрации программы (ст. 1262 ГК РФ). Список уточняется по конкретной ситуации.',
  ),
  array(
    'Что входит в сопровождение и как определяется стоимость?',
    'После анализа документов согласуем объём: аудит прав, подготовка претензии и ответа, переговоры и при необходимости судебное представительство. Услуги LIREATE — по смете; госпошлины по ст. 333.19, 333.21, 333.30 НК РФ оплачиваются отдельно.',
  ),
  array(
    'От чего зависит выбор судебной стратегии?',
    'От предмета спора, доказательств, позиции сторон и процессуального порядка. Возможны требования по ст. 1250, 1252, 1301 ГК РФ; для споров с юридическими лицами в общем порядке нужна досудебная претензия (ст. 4 АПК РФ). Исход суда не предсказываем.',
  ),
);
$faq_extra = array(
  array('Какая ответственность за нарушение?', 'По ст. 1301 ГК РФ суд может определить компенсацию от 10&nbsp;000 до 5&nbsp;000&nbsp;000 руб. либо в двукратном размере стоимости использования. Конкретную сумму определяет суд.'),
  array('Гарантируете ли выигрыш в суде?', 'Нет. Решения принимают суды, платформы и ведомства. Мы готовим материалы и сопровождаем процедуру без обещания исхода.'),
);
$schema_graph = array(
  '@context' => 'https://schema.org',
  '@graph' => array(
    array(
      '@type' => 'Service',
      '@id' => $canonical . '#service',
      'name' => 'Защита авторских прав и сопровождение судебных споров',
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
      'itemListElement' => lireate_breadcrumb_schema_items($site_root, 'Защита авторских прав', $canonical),
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
$lx_breadcrumb_current = 'Защита авторских прав';
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
      <a href="#obekty">Объекты</a>
      <a href="#prava-dokaz">Доказательства</a>
      <a href="#pretenziya">Претензия</a>
      <a href="#sud">Суд</a>
      <a href="#stoimost">Стоимость</a>
      <a href="#prava-na-po">ПО</a>
      <a href="#faq">FAQ</a>
      <a href="#contact">Заявка</a>
    </nav>
    <a class="lx-btn lx-btn-primary" href="#contact">Обсудить спор</a>
  </header>
</div>

<main id="primary" class="lireate-svc-page zashchita-avtorskih-prav-page" role="main" tabindex="-1">

<?php include __DIR__ . '/blocks/lireate-breadcrumbs.inc.php'; ?>

<section class="lx-hero" id="top">
  <div class="lx-hero-mascot-slot" data-lx-mascot="copyright"></div>
  <div>
    <p class="lx-eyebrow">LIREATE · авторское право · претензии и суды</p>
    <h1>Защита авторских прав и сопровождение судебных споров</h1>
    <p class="lx-hero-lead">Фото, тексты, дизайн и программы: проверим права и доказательства, подготовим претензию и позицию в суде — без обещания исхода.</p>
    <div class="lx-tags" aria-label="Темы услуги">
      <span>ст. 1250 · 1252</span><span>ст. 1301</span><span>ст. 4 АПК</span>
    </div>
    <div class="lx-hero-cta">
      <a class="lx-btn lx-btn-primary" href="#contact">Обсудить документы и действия по спору</a>
      <a class="lx-btn lx-btn-ghost lx-btn--with-icon" id="hero-telegram" href="<?php echo htmlspecialchars($lireate_telegram, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" data-lx-icon="telegram"><span>Написать в Telegram</span></a>
      <a class="lx-btn lx-btn-ghost lx-btn--with-icon" id="hero-max" href="<?php echo htmlspecialchars($lireate_max, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" data-lx-icon="max"><span>Написать в Max</span></a>
    </div>
    <div class="lx-hero-mini">
      <div class="lx-mini-stat"><strong class="lx-stat-value" data-lx-count-end="70" data-lx-count-suffix=" лет">70 лет</strong><span>срок после смерти автора (ст. 1281)</span></div>
      <div class="lx-mini-stat"><strong class="lx-stat-value" data-lx-count-end="5000" data-lx-count-sep="space" data-lx-count-suffix=" ₽">5&nbsp;000&nbsp;₽</strong><span>ориентир пошлины за регистрацию ПО</span></div>
      <div class="lx-mini-stat"><strong class="lx-stat-value">от 2 нед.</strong><span>типичный срок аудита и претензии</span></div>
    </div>
  </div>
  <div class="lx-glass lx-glass-anim" aria-label="Маршрут спора по авторским правам">
    <div class="lx-panel-head">
      <span>Спор · маршрут</span>
      <span class="lx-live">ГК РФ ч. 4</span>
    </div>
    <div class="lx-kpi-row">
      <div class="lx-kpi"><b class="lx-stat-value">объекты</b><small>фото, текст, дизайн, ПО</small></div>
      <div class="lx-kpi"><b class="lx-stat-value">доказательства</b><small>авторство и факт использования</small></div>
      <div class="lx-kpi"><b class="lx-stat-value">ст. 1301</b><small>компенсация по усмотрению суда</small></div>
    </div>
    <ul class="lx-feed">
      <li>Аудит: объекты, авторы, договоры, факт копирования</li>
      <li>Фиксация нарушения и пакет доказательств</li>
      <li>Досудебная претензия и переговоры (ст. 4 АПК РФ)</li>
      <li>Иск или защита в суде — ст. 1250, 1252, 1301 ГК РФ</li>
    </ul>
    <div class="lx-route" role="img" aria-label="Маршрут: аудит, факты, претензия, суд">
      <ol class="lx-route-steps">
        <li><span class="lx-route-node">Аудит</span><span class="lx-route-label">права</span></li>
        <li><span class="lx-route-node">Факты</span><span class="lx-route-label">доказательства</span></li>
        <li><span class="lx-route-node lx-route-node--wide">Претензия</span><span class="lx-route-label">до суда</span></li>
        <li><span class="lx-route-node">Суд</span><span class="lx-route-label">если нужно</span></li>
      </ol>
    </div>
  </div>
</section>

<section class="lx-section lx-reveal" id="obekty">
  <span class="lx-section-label">Объекты</span>
  <h2>Какие объекты защищаем</h2>
  <p class="lx-hero-lead" style="margin-bottom:12px;font-size:0.95rem;">Работаем с результатами творческого труда по ст. 1259 ГК РФ: оцениваем конкретные файлы, а не идеи (п. 5 ст. 1259).</p>
  <div class="lx-carousel" data-lx-carousel tabindex="0" aria-label="Объекты авторского права">
    <button type="button" class="lx-carousel-prev" aria-label="Предыдущий слайд">‹</button>
    <div class="lx-carousel-viewport">
      <ol class="lx-carousel-track">
        <li class="lx-carousel-slide"><article class="lx-card"><h3>Фотографии и визуал</h3><p>Копирование фото и иллюстраций без разрешения (ст. 1270 ГК РФ).</p></article></li>
        <li class="lx-carousel-slide"><article class="lx-card"><h3>Тексты и контент</h3><p>Статьи, описания, скрипты — в том числе на сайтах и в соцсетях.</p></article></li>
        <li class="lx-carousel-slide"><article class="lx-card"><h3>Дизайн и вёрстка</h3><p>Макеты, интерфейсы, элементы оформления как произведения.</p></article></li>
        <li class="lx-carousel-slide"><article class="lx-card"><h3>Программы для ЭВМ</h3><p>Код и документация; при необходимости — регистрация по ст. 1262 (см. <a href="#prava-na-po">блок о ПО</a>).</p></article></li>
        <li class="lx-carousel-slide"><article class="lx-card"><h3>Претензия к вам</h3><p>Оценка оснований требования и ответа до суда.</p></article></li>
      </ol>
    </div>
    <button type="button" class="lx-carousel-next" aria-label="Следующий слайд">›</button>
    <div class="lx-carousel-dots"></div>
  </div>
</section>

<section class="lx-section lx-reveal" id="prava-dokaz">
  <span class="lx-section-label">Права и факты</span>
  <h2>Подтверждение прав и нарушения</h2>
  <p class="lx-hero-lead" style="margin-bottom:8px;font-size:0.95rem;">Сначала сверяем, кому принадлежит право и есть ли объективные признаки использования без согласия.</p>
  <div class="lx-steps-walk" data-lx-steps-walk>
    <div class="lx-steps-walk-mascot-slot" aria-hidden="true"></div>
    <ol class="lx-steps-walk-list">
      <li class="lx-steps-walk-step"><span class="lx-card-num">01</span><h3>Аудит прав</h3><p><strong>1–3 дня.</strong> Объекты, авторы, договоры, сценарии использования (ст. 1257, 1259 ГК РФ).</p></li>
      <li class="lx-steps-walk-step"><span class="lx-card-num">02</span><h3>Фиксация нарушения</h3><p><strong>1–5 дней.</strong> Скриншоты, исходники, при необходимости нотариальная фиксация.</p></li>
    </ol>
  </div>
  <div class="lx-risk-grid" style="margin-top:1.25rem">
    <div class="lx-risk-item"><h3>Нет доказательств авторства</h3><p>Без исходников и договоров сложно подтвердить право.</p></div>
    <div class="lx-risk-item"><h3>Охраняется форма, не идея</h3><p>П. 5 ст. 1259 ГК РФ — сходный формат сам по себе не всегда нарушение.</p></div>
  </div>
</section>

<section class="lx-section lx-reveal" id="pretenziya">
  <span class="lx-section-label">Досудебный этап</span>
  <h2>Претензия и переговоры</h2>
  <p>Готовим претензию или ответ с расчётом требований, ведём переговоры. Жалоба на платформу — по её правилам, параллельно с претензией нарушителю.</p>
  <p><strong>Ст. 4 АПК РФ</strong> — в общем порядке для споров с юридическими лицами досудебная претензия обязательна, если иное не установлено законом или договором.</p>
  <div class="lx-timeline" data-lx-timeline>
    <ol class="lx-timeline-steps">
      <li class="lx-timeline-step"><strong>Претензия — 2–5 дней</strong><p>Платформа и контрагент, варианты урегулирования.</p></li>
      <li class="lx-timeline-step"><strong>Переговоры</strong><p>Соглашение или подготовка к суду.</p></li>
    </ol>
  </div>
</section>

<section class="lx-section lx-reveal" id="sud">
  <span class="lx-section-label">Суд</span>
  <h2>Судебные требования и защита</h2>
  <p>Готовим иск или отзыв: прекращение нарушения, сбор доказательств, требования по <strong>ст. 1250, 1252, 1301 ГК РФ</strong>. По ст. 1301 возможна компенсация <strong>от 10&nbsp;000 до 5&nbsp;000&nbsp;000 руб.</strong> либо в двукратном размере стоимости использования — конкретную сумму определяет суд.</p>
  <p>Госпошлина за иск — по ст. 333.19 или 333.21 НК РФ. Срок рассмотрения зависит от суда; исход не гарантируем.</p>
  <div class="lx-compare" data-lx-compare style="margin-top:1rem">
    <div class="lx-compare-panel lx-compare-before">
      <h3>Без сопровождения</h3>
      <ul><li>Нет структурированного пакета доказательств</li><li>Риск ошибок в претензии и иске</li><li>Сложнее выбрать основание по ГК РФ</li></ul>
    </div>
    <div class="lx-compare-panel lx-compare-after" aria-hidden="true">
      <h3>С сопровождением LIREATE</h3>
      <ul><li>Аудит прав и маршрут спора</li><li>Претензия и позиция в суде</li><li>Оценка процессуальных шагов</li></ul>
    </div>
    <input type="range" class="lx-compare-range" min="0" max="100" value="50" aria-label="Сдвиньте для сравнения вариантов">
  </div>
</section>

<section class="lx-section lx-reveal" id="stoimost">
  <span class="lx-section-label">Документы и оплата</span>
  <h2>Стоимость и документы</h2>
  <p>Для оценки спора полезны исходники, договоры, переписка и материалы о нарушении (см. <a href="#faq">FAQ</a>). Состав работ согласуем после аудита.</p>
  <div class="lx-price-grid">
    <div class="lx-price-card"><strong>по смете</strong><span>сопровождение LIREATE: аудит, претензия, суд — после брифа</span></div>
    <div class="lx-price-card"><strong data-lx-count-end="5000" data-lx-count-sep="space" data-lx-count-suffix=" ₽">5&nbsp;000&nbsp;₽</strong><span>ориентир пошлины Роспатента за регистрацию ПО (ст. 333.30 НК РФ, rospatent.gov.ru)</span></div>
  </div>
  <div class="lx-calc" data-lx-calc="po-reg" id="calc">
    <p class="lx-calc-out">Госпошлина за регистрацию ПО: <strong data-lx-calc-out-gov>5&nbsp;000&nbsp;₽</strong></p>
    <p class="lx-calc-note">Калькулятор показывает только ориентир пошлины Роспатента (ст. 333.30 НК РФ). Услуги LIREATE — по смете. Не является офертой.</p>
  </div>
</section>

<section class="lx-section lx-reveal" id="prava-na-po">
  <span class="lx-section-label">Программы</span>
  <h2>Права на ПО и споры с разработчиками</h2>
  <p>Отдельный контур: кому принадлежит код при заказной разработке (ст. 1288, 1295, 1296 ГК РФ), допустимое использование и передача прав. Добровольная <strong>регистрация программы</strong> в Роспатенте по ст. 1262 ГК РФ — по желанию, для фиксации в реестре; она не заменяет защиту в споре, но может упростить доказывание.</p>
  <p>Если спор с подрядчиком или нужна регистрация ПО, оценим договоры и исходники. Подробнее об оформлении — услуга <a href="/registraciya_programm_evm/">регистрация программ для ЭВМ</a> (не путать с судебным сопровождением на этой странице).</p>
  <p>Логотип может охраняться как произведение, но для обозначения товаров нужна <a href="/registraciya_tovarnogo_znaka/">регистрация товарного знака</a> (ст. 1477, 1481 ГК РФ).</p>
</section>

<section class="lx-section lx-reveal" id="quiz">
  <span class="lx-section-label">Мини-тест</span>
  <h2>Нужна ли вам защита авторских прав?</h2>
  <div class="lx-quiz" data-lx-quiz data-lx-quiz-threshold="2" data-lx-quiz-yes="Похоже, нужен аудит и маршрут — обсудите документы по спору." data-lx-quiz-no="Пока достаточно самопроверки; при сомнениях — консультация.">
    <div class="lx-quiz-mascot" aria-hidden="true"><svg viewBox="0 0 72&nbsp;108" class="lx-mascot"><circle cx="36" cy="28" r="18" fill="#f8fafc"/><rect x="34" y="26" width="3" height="8" rx="1.5" fill="#0f172a"/><rect x="41" y="26" width="3" height="8" rx="1.5" fill="#0f172a"/></svg></div>
    <div class="lx-quiz-step"><h3>Чужой контент используют без разрешения?</h3><div class="lx-quiz-options"><button type="button" data-quiz-answer data-quiz-weight="2">Да</button><button type="button" data-quiz-answer data-quiz-weight="0">Нет</button></div></div>
    <div class="lx-quiz-step" hidden><h3>Есть исходники или договоры с авторами?</h3><div class="lx-quiz-options"><button type="button" data-quiz-answer data-quiz-weight="0">Да, всё оформлено</button><button type="button" data-quiz-answer data-quiz-weight="2">Не уверен</button></div></div>
    <div class="lx-quiz-step" hidden><h3>Вам направили претензию или иск?</h3><div class="lx-quiz-options"><button type="button" data-quiz-answer data-quiz-weight="2">Да</button><button type="button" data-quiz-answer data-quiz-weight="0">Нет</button></div></div>
    <div class="lx-quiz-verdict" hidden><p data-quiz-result></p><a class="lx-btn lx-btn-primary" href="#contact">Обсудить документы и действия по спору</a></div>
  </div>
</section>

<section class="lx-section lx-reveal" id="faq">
  <span class="lx-section-label">FAQ</span>
  <h2>Вопросы о защите авторских прав</h2>
<?php foreach ($faq_entities as $faq_pair) : ?>
  <details class="lx-faq"><summary><?php echo htmlspecialchars($faq_pair[0], ENT_QUOTES, 'UTF-8'); ?></summary><p><?php echo $faq_pair[1]; ?></p></details>
<?php endforeach; ?>
<?php foreach ($faq_extra as $faq_pair) : ?>
  <details class="lx-faq"><summary><?php echo htmlspecialchars($faq_pair[0], ENT_QUOTES, 'UTF-8'); ?></summary><p><?php echo $faq_pair[1]; ?></p></details>
<?php endforeach; ?>
</section>

<?php include __DIR__ . '/blocks/lireate-vk-cases.inc.php'; ?>

<?php
$lx_lead_topic = 'Заявка: защита авторских прав';
$lx_lead_tomail = 'Защита авторских прав — заявка с сайта';
$lx_lead_consent_form = 'lx-zashchita-av';
include __DIR__ . '/blocks/lireate-contact-section.inc.php';
?>

<section class="lx-section lx-reveal" id="related">
  <span class="lx-section-label">LIREATE</span>
  <h2>Смежные услуги и материалы</h2>
  <div class="lx-related-grid">
    <a class="lx-related-card" href="/registraciya_programm_evm/"><span class="lx-related-card-type">Услуга</span><strong>Регистрация программ для ЭВМ</strong></a>
    <a class="lx-related-card" href="/findtz/"><span class="lx-related-card-type">Услуга</span><strong>Проверка товарного знака</strong></a>
    <a class="lx-related-card" href="/registraciya_tovarnogo_znaka/"><span class="lx-related-card-type">Услуга</span><strong>Регистрация товарного знака</strong></a>
    <a class="lx-related-card" href="/news/"><span class="lx-related-card-type">Блог</span><strong>Новости и разборы LIREATE</strong></a>
    <a class="lx-related-card" href="/contacts/"><span class="lx-related-card-type">Контакты</span><strong>Телефон и офис</strong></a>
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
<script src="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-calculator.js', $lx_service_asset_dir); ?>"></script>
<script src="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-quiz.js', $lx_service_asset_dir); ?>"></script>
<script src="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-timeline.js', $lx_service_asset_dir); ?>"></script>
<script src="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-compare.js', $lx_service_asset_dir); ?>"></script>
<script src="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-mobile-bar.js', $lx_service_asset_dir); ?>"></script>
<script src="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-breadcrumbs.js', $lx_service_asset_dir); ?>"></script>
<?php include __DIR__ . '/lireate-lead-scripts.inc.php'; ?>
<?php include dirname(__DIR__) . '/_shared/lireate-metrika.inc.php'; ?>
<script src="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-metrika-goals.js', $lx_service_asset_dir); ?>"></script>
  <script defer src="/template/js/lireate-service-metrika-goals.js?v=20261111svc3"></script><!-- lireate_design_service_mobile_bar_js_v1 -->
</body>
</html>
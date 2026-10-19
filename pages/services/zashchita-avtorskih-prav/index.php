<?php
header('Content-Type: text/html; charset=utf-8');
$path = '/zashchita_avtorskih_prav/';
$host = isset($_SERVER['HTTP_HOST']) ? preg_replace('/[^a-zA-Z0-9.\-:]/', '', $_SERVER['HTTP_HOST']) : '';
$site_root = ($host !== '' ? 'https://' . $host : '');
$canonical = $site_root . $path;
$seo_title = 'Защита авторских прав: контент, софт — LIREATE';
$seo_description = 'Защита авторских прав на контент, софт и дизайн: аудит по ГК РФ, фиксация нарушений, претензия, регистрация ПО в Роспатенте. Консультация LIREATE.';
$og_type = 'website';
$og_image = $site_root . '/local/templates/lireate/images/og-default.jpg';
$lireate_phone_display = '+7 (960) 048-49-57';
$lireate_phone_tel = 'tel:+79600484957';
$lireate_telegram = 'https://t.me/lireate_patent';
$lireate_whatsapp = 'https://wa.me/79600484957';
require_once dirname(__DIR__) . '/_shared/lireate-messenger-urls.php';
$lireate_max = LIREATE_MAX_CHAT_URL;
$faq_entities = [
  ['Нужна ли регистрация, чтобы охранялись авторские права?', 'Нет. По п. 4 ст. 1259 ГК РФ право возникает при создании произведения в объективной форме. Программу или БД можно дополнительно зарегистрировать по желанию (ст. 1262 ГК РФ).'],
  ['Что охраняется, а что нет?', 'Охраняются результаты творческого труда из ст. 1259 ГК РФ. Не охраняются идеи, методы и факты (п. 5 ст. 1259). Оценка делается по конкретным файлам.'],
  ['Сколько стоит госрегистрация программы для ЭВМ?', 'Пошлины — ст. 333.30 НК РФ. На странице госуслуги Роспатента ориентир 5 000 руб. за регистрацию; сверяйте с rospatent.gov.ru. Услуги LIREATE оплачиваются отдельно.'],
  ['Можно ли защитить логотип без товарного знака?', 'Логотип может охраняться как произведение, но для обозначения товаров и услуг нужна регистрация ТЗ (ст. 1477, 1481 ГК РФ). См. <a href="/findtz/">проверку знака</a>.'],
  ['Что делать, если контент скопировали?', 'Зафиксируйте нарушение, соберите доказательства авторства, подайте жалобу на платформу и направьте претензию. При необходимости — суд (ст. 1250, 1252, 1301 ГК РФ).'],
  ['Какая ответственность за нарушение?', 'По ст. 1301 ГК РФ возможна компенсация от 10 000 до 5 000 000 руб. по усмотрению суда либо в двукратном размере стоимости использования. Конкретную сумму не обещаем.'],
  ['Гарантируете ли регистрацию или выигрыш в суде?', 'Нет. Решения принимают Роспатент, платформы и суд. Мы готовим материалы и сопровождаем процедуру без гарантии исхода.'],
];
$schema_graph = [
  '@context' => 'https://schema.org',
  '@graph' => [
    [
      '@type' => 'Service',
      '@id' => $canonical . '#service',
      'name' => 'Защита авторских прав',
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
        ['@type' => 'ListItem', 'position' => 3, 'name' => 'Защита авторских прав', 'item' => $canonical],
      ],
    ],
    [
      '@type' => 'FAQPage',
      'mainEntity' => array_map(static function ($pair) {
        return [
          '@type' => 'Question',
          'name' => $pair[0],
          'acceptedAnswer' => ['@type' => 'Answer', 'text' => strip_tags($pair[1])],
        ];
      }, $faq_entities),
    ],
  ],
];
$lx_breadcrumb_current = 'Защита авторских прав';
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
      <a href="#pricing">Стоимость</a>
      <a href="#faq">FAQ</a>
      <a href="#contact">Заявка</a>
    </nav>
    <a class="lx-btn lx-btn-primary" href="#contact">Консультация</a>
  </header>
</div>

<main id="primary" class="lireate-svc-page zashchita-avtorskih-prav-page" role="main" tabindex="-1">

<?php include __DIR__ . '/blocks/lireate-breadcrumbs.inc.php'; ?>

<section class="lx-hero" id="top">
  <div class="lx-hero-mascot-slot" data-lx-mascot="copyright"></div>
  <div>
    <p class="lx-eyebrow">LIREATE · авторское право · ГК РФ ч. 4</p>
    <h1>Защита авторских прав</h1>
    <p class="lx-hero-lead">Аудит прав, фиксация нарушений и претензия — без гарантий исхода суда или платформы.</p>
    <div class="lx-tags" aria-label="Темы услуги">
      <span>ст. 1259 ГК РФ</span><span>ст. 1262 · ПО</span><span>ст. 1301</span>
    </div>
    <div class="lx-hero-cta">
      <a class="lx-btn lx-btn-primary" href="#contact">Оставить заявку</a>
      <a class="lx-btn lx-btn-ghost lx-btn--with-icon" id="hero-telegram" href="<?php echo htmlspecialchars($lireate_telegram, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" data-lx-icon="telegram"><span>Написать в Telegram</span></a>
      <a class="lx-btn lx-btn-ghost lx-btn--with-icon" id="hero-max" href="<?php echo htmlspecialchars($lireate_max, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" data-lx-icon="max"><span>Написать в Max</span></a>
    </div>
    <div class="lx-hero-mini">
      <div class="lx-mini-stat"><strong class="lx-stat-value" data-lx-count-end="70" data-lx-count-suffix=" лет">70 лет</strong><span>срок после смерти автора (ст. 1281)</span></div>
      <div class="lx-mini-stat"><strong class="lx-stat-value" data-lx-count-end="5000" data-lx-count-sep="space" data-lx-count-suffix=" ₽">5 000 ₽</strong><span>ориентир пошлины за регистрацию ПО</span></div>
      <div class="lx-mini-stat"><strong class="lx-stat-value">от 2 нед.</strong><span>типичный срок аудита и претензии</span></div>
    </div>
  </div>
  <div class="lx-glass lx-glass-anim" aria-label="Маршрут защиты авторских прав">
    <div class="lx-panel-head">
      <span>Права · маршрут защиты</span>
      <span class="lx-live">ГК РФ ч. 4</span>
    </div>
    <div class="lx-kpi-row">
      <div class="lx-kpi"><b class="lx-stat-value">без реестра</b><small>право возникает при создании</small></div>
      <div class="lx-kpi"><b class="lx-stat-value">ст. 1262</b><small>ПО и БД: регистрация по желанию</small></div>
      <div class="lx-kpi"><b class="lx-stat-value">ст. 333.30</b><small>пошлина НК РФ за регистрацию</small></div>
    </div>
    <ul class="lx-feed">
      <li>Аудит: что создано, кто автор, кому принадлежит право</li>
      <li>Фиксация нарушения и доказательств авторства</li>
      <li>Жалоба на платформу и претензия нарушителю</li>
      <li>Регистрация программы или базы данных, если это целесообразно</li>
    </ul>
    <div class="lx-route" role="img" aria-label="Маршрут: аудит, факты, жалоба, претензия, суд">
      <ol class="lx-route-steps">
        <li><span class="lx-route-node">Аудит</span><span class="lx-route-label">права</span></li>
        <li><span class="lx-route-node">Факты</span><span class="lx-route-label">доказательства</span></li>
        <li><span class="lx-route-node">Жалоба</span><span class="lx-route-label">платформа</span></li>
        <li><span class="lx-route-node lx-route-node--wide">Претензия</span><span class="lx-route-label">до суда</span></li>
        <li><span class="lx-route-node">Суд</span><span class="lx-route-label">если нужно</span></li>
      </ol>
    </div>
  </div>
</section>

<section class="lx-section lx-reveal" id="when">
  <span class="lx-section-label">Когда нужна</span>
  <h2>Защита авторских прав: когда обращаться</h2>
  <div class="lx-carousel" data-lx-carousel tabindex="0" aria-label="Ситуации для обращения">
    <button type="button" class="lx-carousel-prev" aria-label="Предыдущий слайд">‹</button>
    <div class="lx-carousel-viewport">
      <ol class="lx-carousel-track">
        <li class="lx-carousel-slide"><article class="lx-card"><h3>Скопировали контент</h3><p>Текст, фото или видео без разрешения (ст. 1270 ГК РФ).</p></article></li>
        <li class="lx-carousel-slide"><article class="lx-card"><h3>Спор с подрядчиком</h3><p>Кому принадлежит право по договору (ст. 1288, 1295, 1296 ГК РФ).</p></article></li>
        <li class="lx-carousel-slide"><article class="lx-card"><h3>Программа или БД</h3><p>Добровольная регистрация в Роспатенте (ст. 1262 ГК РФ).</p></article></li>
        <li class="lx-carousel-slide"><article class="lx-card"><h3>Логотип и бренд</h3><p>Авторское право не заменяет <a href="/registraciya_tovarnogo_znaka/">регистрацию ТЗ</a>.</p></article></li>
        <li class="lx-carousel-slide"><article class="lx-card"><h3>Претензия к вам</h3><p>Оценка оснований и ответа до суда.</p></article></li>
      </ol>
    </div>
    <button type="button" class="lx-carousel-next" aria-label="Следующий слайд">›</button>
    <div class="lx-carousel-dots"></div>
  </div>
</section>

<section class="lx-section lx-reveal" id="program">
  <span class="lx-section-label">Как работаем</span>
  <h2>Этапы сопровождения защиты авторских прав</h2>
  <p class="lx-hero-lead" style="margin-bottom:8px;font-size:0.95rem;">Типовой маршрут; сроки — ориентиры и зависят от объёма материалов и ответа сторон.</p>
  <div class="lx-steps-walk" data-lx-steps-walk>
    <div class="lx-steps-walk-mascot-slot" aria-hidden="true"></div>
    <ol class="lx-steps-walk-list">
      <li class="lx-steps-walk-step"><span class="lx-card-num">01</span><h3>Аудит прав</h3><p><strong>1–3 дня.</strong> Объекты, авторы, договоры, сценарии использования.</p></li>
      <li class="lx-steps-walk-step"><span class="lx-card-num">02</span><h3>Фиксация доказательств</h3><p><strong>1–5 дней.</strong> Скриншоты, исходники, при необходимости нотариус.</p></li>
      <li class="lx-steps-walk-step"><span class="lx-card-num">03</span><h3>Жалоба и претензия</h3><p><strong>2–5 дней.</strong> Платформа и нарушитель, варианты урегулирования.</p></li>
      <li class="lx-steps-walk-step"><span class="lx-card-num">04</span><h3>Регистрация ПО и БД</h3><p><strong>по задаче.</strong> Ст. 1262 ГК РФ, пошлина ст. 333.30 НК РФ.</p></li>
      <li class="lx-steps-walk-step"><span class="lx-card-num">05</span><h3>Договоры и логотип</h3><p><strong>3–7 дней.</strong> Переход прав; <a href="/findtz/">проверка знака</a> при необходимости.</p></li>
      <li class="lx-steps-walk-step"><span class="lx-card-num">06</span><h3>Суд или соглашение</h3><p><strong>по обстоятельствам.</strong> Ст. 1301 ГК РФ — без гарантии суммы.</p></li>
    </ol>
  </div>
</section>

<section class="lx-section lx-reveal" id="pricing">
  <span class="lx-section-label">Стоимость</span>
  <h2>Стоимость сопровождения и госпошлины</h2>
  <div class="lx-price-grid">
    <div class="lx-price-card"><strong>по смете</strong><span>сопровождение LIREATE: аудит, претензия, суд — после брифа</span></div>
    <div class="lx-price-card"><strong data-lx-count-end="5000" data-lx-count-sep="space" data-lx-count-suffix=" ₽">5 000 ₽</strong><span>ориентир пошлины Роспатента за регистрацию ПО (ст. 333.30 НК РФ, rospatent.gov.ru)</span></div>
  </div>
  <div class="lx-calc" data-lx-calc="po-reg" id="calc">
    <p class="lx-calc-out">Госпошлина за регистрацию ПО: <strong data-lx-calc-out-gov>5 000 ₽</strong></p>
    <p class="lx-calc-note">Калькулятор показывает только ориентир пошлины Роспатента (ст. 333.30 НК РФ). Услуги LIREATE — по смете. Не является офертой.</p>
  </div>
  <p>Госпошлина за иск — по ст. 333.19 или 333.21 НК РФ. Итог определяют ведомство и суд.</p>
</section>

<section class="lx-section lx-reveal" id="terms">
  <span class="lx-section-label">Сроки</span>
  <h2>Сроки маршрута защиты</h2>
  <div class="lx-timeline" data-lx-timeline>
    <div class="lx-timeline-mascot" aria-hidden="true"><svg class="lx-mascot" viewBox="0 0 72 108"><circle cx="36" cy="28" r="18" fill="#f8fafc"/><rect x="34" y="26" width="3" height="8" rx="1.5" fill="#0f172a"/><rect x="41" y="26" width="3" height="8" rx="1.5" fill="#0f172a"/></svg></div>
    <ol class="lx-timeline-steps">
      <li class="lx-timeline-step"><strong>Аудит — 1–3 дня</strong><p>Объекты и договоры.</p></li>
      <li class="lx-timeline-step"><strong>Доказательства — 1–5 дней</strong><p>Фиксация нарушения.</p></li>
      <li class="lx-timeline-step"><strong>Претензия — 2–5 дней</strong><p>Платформа и нарушитель.</p></li>
      <li class="lx-timeline-step"><strong>Суд — по регламенту</strong><p>Без гарантии срока и исхода.</p></li>
    </ol>
  </div>
</section>

<section class="lx-section lx-reveal" id="compare">
  <span class="lx-section-label">Сравнение</span>
  <h2>Без сопровождения и с юристом LIREATE</h2>
  <div class="lx-compare" data-lx-compare>
    <div class="lx-compare-panel lx-compare-before">
      <h3>Без системной защиты</h3>
      <ul><li>Нет пакета доказательств</li><li>Риск ошибок в претензии</li><li>Сложнее выбрать основание по ГК РФ</li></ul>
    </div>
    <div class="lx-compare-panel lx-compare-after" aria-hidden="true">
      <h3>С сопровождением</h3>
      <ul><li>Аудит прав и маршрут</li><li>Претензия и жалоба по процедуре</li><li>Оценка регистрации ПО при необходимости</li></ul>
    </div>
    <input type="range" class="lx-compare-range" min="0" max="100" value="50" aria-label="Сдвиньте для сравнения вариантов">
  </div>
</section>

<section class="lx-section lx-reveal" id="quiz">
  <span class="lx-section-label">Мини-тест</span>
  <h2>Нужна ли вам защита авторских прав?</h2>
  <div class="lx-quiz" data-lx-quiz data-lx-quiz-threshold="2" data-lx-quiz-yes="Похоже, нужен аудит и маршрут — оставьте заявку." data-lx-quiz-no="Пока достаточно самопроверки; при сомнениях — консультация.">
    <div class="lx-quiz-mascot" aria-hidden="true"><svg viewBox="0 0 72 108" class="lx-mascot"><circle cx="36" cy="28" r="18" fill="#f8fafc"/><rect x="34" y="26" width="3" height="8" rx="1.5" fill="#0f172a"/><rect x="41" y="26" width="3" height="8" rx="1.5" fill="#0f172a"/></svg></div>
    <div class="lx-quiz-step"><h3>Чужой контент используют без разрешения?</h3><div class="lx-quiz-options"><button type="button" data-quiz-answer data-quiz-weight="2">Да</button><button type="button" data-quiz-answer data-quiz-weight="0">Нет</button></div></div>
    <div class="lx-quiz-step" hidden><h3>Есть исходники или договоры с авторами?</h3><div class="lx-quiz-options"><button type="button" data-quiz-answer data-quiz-weight="0">Да, всё оформлено</button><button type="button" data-quiz-answer data-quiz-weight="2">Не уверен</button></div></div>
    <div class="lx-quiz-step" hidden><h3>Нужна регистрация ПО в Роспатенте?</h3><div class="lx-quiz-options"><button type="button" data-quiz-answer data-quiz-weight="1">Рассматриваем</button><button type="button" data-quiz-answer data-quiz-weight="0">Нет</button></div></div>
    <div class="lx-quiz-verdict" hidden><p data-quiz-result></p><a class="lx-btn lx-btn-primary" href="#contact">Получить консультацию</a></div>
  </div>
</section>

<section class="lx-section lx-reveal" id="risks">
  <span class="lx-section-label">Риски</span>
  <h2>Риски при защите авторских прав</h2>
  <div class="lx-risk-grid">
    <div class="lx-risk-item"><h3>Нет доказательств авторства</h3><p>Без исходников и договоров сложно подтвердить право (ст. 1257, 1259 ГК РФ).</p></div>
    <div class="lx-risk-item"><h3>Охраняется форма, не идея</h3><p>П. 5 ст. 1259 ГК РФ — «похожий формат» сам по себе не всегда нарушение.</p></div>
    <div class="lx-risk-item"><h3>Исход не гарантирован</h3><p>Компенсацию по ст. 1301 ГК РФ определяет суд; платформа действует по своим правилам.</p></div>
  </div>
</section>

<section class="lx-section lx-reveal" id="faq">
  <span class="lx-section-label">FAQ</span>
  <h2>Вопросы о защите авторских прав</h2>
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
      <form id="lireate-lead-form" data-lead-topic="Заявка: защита авторских прав" novalidate>
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
  <h2>Смежные услуги и материалы</h2>
  <div class="lx-related-grid">
    <a class="lx-related-card" href="/findtz/"><span class="lx-related-card-type">Услуга</span><strong>Проверка товарного знака</strong></a>
    <a class="lx-related-card" href="/registraciya_tovarnogo_znaka/"><span class="lx-related-card-type">Услуга</span><strong>Регистрация товарного знака</strong></a>
    <a class="lx-related-card" href="/prodlenie_tovarnogo_znaka/"><span class="lx-related-card-type">Услуга</span><strong>Продление товарного знака</strong></a>
    <a class="lx-related-card" href="/news/"><span class="lx-related-card-type">Блог</span><strong>Новости и разборы LIREATE</strong></a>
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
<script src="assets/lireate-shared/lireate-calculator.js"></script>
<script src="assets/lireate-shared/lireate-quiz.js"></script>
<script src="assets/lireate-shared/lireate-timeline.js"></script>
<script src="assets/lireate-shared/lireate-compare.js"></script>
<script src="assets/lireate-shared/lireate-mobile-bar.js"></script>
<script src="assets/lireate-shared/lireate-contact.js"></script>
</body>
</html>

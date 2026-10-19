<?php
header('Content-Type: text/html; charset=utf-8');
$path = '/registraciya_tovarnogo_znaka_v_kitae/';
$host = isset($_SERVER['HTTP_HOST']) ? preg_replace('/[^a-zA-Z0-9.\-:]/', '', $_SERVER['HTTP_HOST']) : '';
$site_root = ($host !== '' ? 'https://' . $host : '');
$canonical = $site_root . $path;
$seo_title = 'Регистрация товарного знака в Китае — LIREATE';
$seo_description = 'Регистрация товарного знака в Китае: МКТУ, перевод, подача в CNIPA, ответы ведомству, риски экспорта и маркетплейсов. Сопровождение LIREATE.';
$og_type = 'website';
$og_image = $site_root . '/local/templates/lireate/images/og-default.jpg';
$lireate_phone_display = '+7 (960) 048-49-57';
$lireate_phone_tel = 'tel:+79600484957';
$lireate_telegram = 'https://t.me/lireate_patent';
$lireate_whatsapp = 'https://wa.me/79600484957';
require_once dirname(__DIR__) . '/_shared/lireate-messenger-require.inc.php';
$lireate_max = LIREATE_MAX_CHAT_URL;
$lx_breadcrumb_current = 'Регистрация ТЗ в Китае';
$lx_review_filter_services = ['registraciya-tovarnogo-znaka-v-kitae', 'prodlenie-tovarnogo-znaka'];
$faq_entities = [
  ['Достаточно ли международной регистрации по Мадриду?', 'Мадрид может охватывать КНР, но применяются сроки и требования CNIPA. Сверяем перечень классов с вашим портфелем.'],
  ['Можно ли зарегистрировать знак на русском языке?', 'Заявляют словесные и комбинированные обозначения; для рынка часто готовят транслитерацию или иероглифы.'],
  ['Кто может быть заявителем?', 'Иностранное юрлицо или ИП при соблюдении правил представителя в КНР.'],
  ['Гарантируете ли регистрацию?', 'Нет. Решение принимает CNIPA; мы снижаем формальные риски и готовим ответы на уведомления.'],
  ['Сколько стоит сопровождение?', 'Зависит от классов МКТУ, перевода и ответов на отказы. Смета после брифа.'],
  ['Как начать?', 'Оставьте заявку или напишите в мессенджер с макетом знака и описанием товаров.'],
];
$schema_graph = [
  '@context' => 'https://schema.org',
  '@graph' => [
    ['@type' => 'Service', '@id' => $canonical . '#service', 'name' => 'Регистрация товарного знака в Китае', 'url' => $canonical, 'description' => $seo_description, 'areaServed' => 'CN', 'provider' => ['@id' => $canonical . '#organization']],
    ['@type' => ['LegalService', 'Organization'], '@id' => $canonical . '#organization', 'name' => 'LIREATE', 'url' => $site_root . '/', 'telephone' => '+7-960-048-49-57', 'sameAs' => [$lireate_telegram, $lireate_whatsapp, $lireate_max]],
    ['@type' => 'BreadcrumbList', 'itemListElement' => [
      ['@type' => 'ListItem', 'position' => 1, 'name' => 'Главная', 'item' => $site_root . '/'],
      ['@type' => 'ListItem', 'position' => 2, 'name' => 'Услуги', 'item' => $site_root . '/uslugi/'],
      ['@type' => 'ListItem', 'position' => 3, 'name' => 'Регистрация ТЗ в Китае', 'item' => $canonical],
    ]],
    ['@type' => 'FAQPage', 'mainEntity' => array_map(static function ($pair) {
      return ['@type' => 'Question', 'name' => $pair[0], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $pair[1]]];
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
      <a href="#reviews">Отзывы</a>
      <a href="#faq">FAQ</a>
      <a href="#contact">Заявка</a>
    </nav>
    <a class="lx-btn lx-btn-primary" href="#contact">Консультация</a>
  </header>
</div>

<main id="primary" class="lireate-svc-page registraciya-tovarnogo-znaka-v-kitae-page" role="main" tabindex="-1">

<?php include __DIR__ . '/blocks/lireate-breadcrumbs.inc.php'; ?>

<section class="lx-hero" id="top">
  <div class="lx-hero-mascot-slot" data-lx-mascot="china"></div>
  <div>
    <p class="lx-eyebrow">LIREATE · товарные знаки · Китай · CNIPA</p>
    <h1>Регистрация товарного знака в&nbsp;Китае</h1>
    <p class="lx-hero-lead">Маршрут подачи в CNIPA: аудит знака, классы МКТУ, перевод и ответы на уведомления — без гарантий исхода экспертизы.</p>
    <div class="lx-tags" aria-label="Темы услуги">
      <span>CNIPA</span><span>МКТУ</span><span>Мадрид / КНР</span><span>Маркетплейсы</span>
    </div>
    <div class="lx-hero-cta">
      <a class="lx-btn lx-btn-primary" href="#contact">Оставить заявку</a>
      <a class="lx-btn lx-btn-ghost lx-btn--with-icon" id="hero-telegram" href="<?php echo htmlspecialchars($lireate_telegram, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" data-lx-icon="telegram"><span>Написать в Telegram</span></a>
      <a class="lx-btn lx-btn-ghost lx-btn--with-icon" id="hero-max" href="<?php echo htmlspecialchars($lireate_max, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" data-lx-icon="max"><span>Написать в Max</span></a>
    </div>
    <div class="lx-hero-mini">
      <div class="lx-mini-stat"><strong class="lx-stat-value">12+ мес.</strong><span>типичный цикл экспертизы</span></div>
      <div class="lx-mini-stat"><strong class="lx-stat-value">6 этапов</strong><span>маршрут работ</span></div>
      <div class="lx-mini-stat"><strong class="lx-stat-value">2 языка</strong><span>русский и китайский</span></div>
    </div>
  </div>
  <div class="lx-glass lx-glass-anim" aria-label="Маршрут заявки в CNIPA">
    <div class="lx-panel-head"><span>Маршрут · ориентиры</span><span class="lx-live">CNIPA</span></div>
    <div class="lx-kpi-row">
      <div class="lx-kpi"><b class="lx-stat-value">6 этапов</b><small>сопровождение</small></div>
      <div class="lx-kpi"><b class="lx-stat-value">45 классов</b><small>МКТУ</small></div>
      <div class="lx-kpi"><b class="lx-stat-value">2 языка</b><small>документы</small></div>
    </div>
    <ul class="lx-feed">
      <li>Аудит обозначения и коллизий в реестрах</li>
      <li>Пакет документов и перевод подачи</li>
      <li>Ответ на уведомление экспертизы в срок</li>
      <li>Запись в реестре после решения CNIPA</li>
    </ul>
    <div class="lx-route" role="img" aria-label="Путь заявки в Китае">
      <ol class="lx-route-steps">
        <li><span class="lx-route-node">RU</span><span class="lx-route-label">бренд</span></li>
        <li><span class="lx-route-node">CNIPA</span><span class="lx-route-label">подача</span></li>
        <li><span class="lx-route-node">МКТУ</span><span class="lx-route-label">классы</span></li>
        <li><span class="lx-route-node lx-route-node--wide">Экспертиза</span><span class="lx-route-label">CNIPA</span></li>
        <li><span class="lx-route-node">Реестр</span><span class="lx-route-label">регистрация</span></li>
      </ol>
    </div>
  </div>
</section>

<section class="lx-section lx-reveal" id="when">
  <span class="lx-section-label">Когда нужна</span>
  <h2>Регистрация знака в Китае: сигналы к действию</h2>
  <p>Российское или мадридское свидетельство не заменяет национальную охрану во всех сценариях экспорта. Если вы выходите на маркетплейсы КНР, открываете дистрибуцию или OEM-производство, знак лучше занять заранее — в Китае действует принцип «первым подал».</p>
  <div class="lx-carousel" data-lx-carousel tabindex="0" aria-label="Когда нужна регистрация в Китае">
    <button type="button" class="lx-carousel-prev" aria-label="Предыдущий слайд">‹</button>
    <div class="lx-carousel-viewport">
      <ol class="lx-carousel-track">
        <li class="lx-carousel-slide"><article class="lx-card"><h3>Маркетплейсы</h3><p>Жалобы на карточки и бренд без локальной регистрации сложнее.</p></article></li>
        <li class="lx-carousel-slide"><article class="lx-card"><h3>OEM / дистрибьютор</h3><p>Риск регистрации сходного знака партнёром.</p></article></li>
        <li class="lx-carousel-slide"><article class="lx-card"><h3>Иероглифы бренда</h3><p>Транслитерация и смысл до подачи в CNIPA.</p></article></li>
        <li class="lx-carousel-slide"><article class="lx-card"><h3>Портфель РФ + КНР</h3><p>Синхронизация с <a href="/registraciya_tovarnogo_znaka/">регистрацией в России</a>.</p></article></li>
        <li class="lx-carousel-slide"><article class="lx-card"><h3>После отказа</h3><p>Аргументированный ответ на уведомление CNIPA.</p></article></li>
      </ol>
    </div>
    <button type="button" class="lx-carousel-next" aria-label="Следующий слайд">›</button>
    <div class="lx-carousel-dots"></div>
  </div>
</section>

<section class="lx-section lx-reveal" id="program">
  <span class="lx-section-label">Как работаем</span>
  <h2>Шесть этапов сопровождения в CNIPA</h2>
  <div class="lx-steps-walk" data-lx-steps-walk>
    <div class="lx-steps-walk-mascot-slot" aria-hidden="true"></div>
    <ol class="lx-steps-walk-list">
      <li class="lx-steps-walk-step"><span class="lx-card-num">01</span><h3>Аудит</h3><p><strong>3–7 дней.</strong> Коллизии, классы МКТУ, формат знака.</p></li>
      <li class="lx-steps-walk-step"><span class="lx-card-num">02</span><h3>Документы</h3><p><strong>5–10 дней.</strong> Перевод, представитель, макет.</p></li>
      <li class="lx-steps-walk-step"><span class="lx-card-num">03</span><h3>Подача</h3><p><strong>1–3 дня.</strong> Взаимодействие с поверенным в КНР.</p></li>
      <li class="lx-steps-walk-step"><span class="lx-card-num">04</span><h3>Экспертиза</h3><p><strong>месяцы.</strong> Сроки CNIPA, без фиксированной даты.</p></li>
      <li class="lx-steps-walk-step"><span class="lx-card-num">05</span><h3>Ответ на отказ</h3><p><strong>по уведомлению.</strong> Доводы и уточнение перечня.</p></li>
      <li class="lx-steps-walk-step"><span class="lx-card-num">06</span><h3>Реестр</h3><p><strong>по решению.</strong> Пошлины за регистрацию и свидетельство.</p></li>
    </ol>
  </div>
</section>

<section class="lx-section lx-reveal" id="context">
  <span class="lx-section-label">Контекст</span>
  <h2>МКТУ, маркетплейсы и правовая рамка</h2>
  <p>Перечень товаров и услуг формулируем под реальный ассортимент и планы на маркетплейсах — без заведомо неохраняемых формулировок. Национальная подача идёт по правилам CNIPA; международный маршрут через ВОИС возможен, стратегию выбираем после аудита портфеля.</p>
  <p>Для комбинированных знаков отдельно прорабатываем китайскую версию: транслитерация, смысл иероглифов, культурные риски. Ссылки на первоисточники: <a href="https://www.cnipa.gov.cn/" target="_blank" rel="noopener noreferrer">CNIPA</a>, <a href="https://www.wipo.int/" target="_blank" rel="noopener noreferrer">ВОИС</a>.</p>
  <ul class="lx-checklist">
    <li>Макет знака и перечень товаров в бизнес-формулировках</li>
    <li>Данные заявителя и цепочка владения брендом</li>
    <li>Сведения о заявках в РФ и по Мадриду</li>
  </ul>
</section>

<section class="lx-section lx-reveal" id="terms">
  <span class="lx-section-label">Сроки</span>
  <h2>Ориентиры по срокам CNIPA</h2>
  <div class="lx-timeline" data-lx-timeline>
    <div class="lx-timeline-mascot" aria-hidden="true"></div>
    <ol class="lx-timeline-steps">
      <li class="lx-timeline-step"><strong>Аудит — 1 неделя</strong><p>Коллизии и классы.</p></li>
      <li class="lx-timeline-step"><strong>Подача — 1–2 недели</strong><p>Документы и перевод.</p></li>
      <li class="lx-timeline-step"><strong>Экспертиза — 12+ мес.</strong><p>Очередь CNIPA, ответы на запросы.</p></li>
      <li class="lx-timeline-step"><strong>Реестр — по решению</strong><p>Без гарантии даты регистрации.</p></li>
    </ol>
  </div>
</section>

<section class="lx-section lx-reveal" id="compare">
  <span class="lx-section-label">Сравнение</span>
  <h2>Без китайской регистрации и с сопровождением</h2>
  <div class="lx-compare" data-lx-compare>
    <div class="lx-compare-panel lx-compare-before">
      <h3>Без регистрации в КНР</h3>
      <ul><li>Риск сходной заявки третьих лиц</li><li>Сложнее жалобы на маркетплейсах</li><li>Споры с дистрибьютором о бренде</li></ul>
    </div>
    <div class="lx-compare-panel lx-compare-after" aria-hidden="true">
      <h3>С сопровождением LIREATE</h3>
      <ul><li>Аудит и маршрут CNIPA</li><li>Перевод и ответы на уведомления</li><li>Связка с портфелем в РФ</li></ul>
    </div>
    <input type="range" class="lx-compare-range" min="0" max="100" value="50" aria-label="Сдвиньте для сравнения вариантов">
  </div>
</section>

<section class="lx-section lx-reveal" id="quiz">
  <span class="lx-section-label">Мини-тест</span>
  <h2>Нужна ли вам регистрация в Китае?</h2>
  <div class="lx-quiz" data-lx-quiz data-lx-quiz-threshold="2" data-lx-quiz-yes="Похоже, стоит начать с аудита знака для КНР." data-lx-quiz-no="Пока достаточно самопроверки; при выходе на рынок — консультация.">
    <div class="lx-quiz-mascot" aria-hidden="true"></div>
    <div class="lx-quiz-step"><h3>Продаёте или планируете продажи в Китае?</h3><div class="lx-quiz-options"><button type="button" data-quiz-answer data-quiz-weight="2">Да</button><button type="button" data-quiz-answer data-quiz-weight="0">Нет</button></div></div>
    <div class="lx-quiz-step" hidden><h3>Есть дистрибьютор или OEM в КНР?</h3><div class="lx-quiz-options"><button type="button" data-quiz-answer data-quiz-weight="2">Да</button><button type="button" data-quiz-answer data-quiz-weight="1">Обсуждаем</button></div></div>
    <div class="lx-quiz-step" hidden><h3>Знак уже подан в CNIPA?</h3><div class="lx-quiz-options"><button type="button" data-quiz-answer data-quiz-weight="0">Да</button><button type="button" data-quiz-answer data-quiz-weight="2">Нет</button></div></div>
    <div class="lx-quiz-verdict" hidden><p data-quiz-result></p><a class="lx-btn lx-btn-primary" href="#contact">Получить консультацию</a></div>
  </div>
</section>

<section class="lx-section lx-reveal" id="risks">
  <span class="lx-section-label">Риски</span>
  <h2>Типовые риски без регистрации в КНР</h2>
  <div class="lx-risk-grid">
    <div class="lx-risk-item"><h3>Чужая заявка</h3><p>Сходное обозначение может занять агент или конкурент.</p></div>
    <div class="lx-risk-item"><h3>Ребрендинг</h3><p>Инвестиции в локализацию при занятом имени.</p></div>
    <div class="lx-risk-item"><h3>Карточки на площадках</h3><p>Жалобы и блокировки без подтверждения прав.</p></div>
  </div>
</section>

<?php include __DIR__ . '/blocks/lireate-reviews.inc.php'; ?>

<section class="lx-section lx-reveal" id="faq">
  <span class="lx-section-label">FAQ</span>
  <h2>Вопросы о регистрации в Китае</h2>
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
      <form id="lireate-lead-form" data-lead-topic="Заявка: регистрация ТЗ в Китае" novalidate>
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
    <a class="lx-related-card" href="/registraciya_tovarnogo_znaka/"><span class="lx-related-card-type">Услуга</span><strong>Регистрация ТЗ в России</strong></a>
    <a class="lx-related-card" href="/prodlenie_tovarnogo_znaka/"><span class="lx-related-card-type">Услуга</span><strong>Продление товарного знака</strong></a>
    <a class="lx-related-card" href="/findtz/"><span class="lx-related-card-type">Услуга</span><strong>Проверка товарного знака</strong></a>
    <a class="lx-related-card" href="/news/"><span class="lx-related-card-type">Блог</span><strong>Новости LIREATE</strong></a>
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
<script src="assets/lireate-shared/lireate-quiz.js"></script>
<script src="assets/lireate-shared/lireate-timeline.js"></script>
<script src="assets/lireate-shared/lireate-compare.js"></script>
<script src="assets/lireate-shared/lireate-reviews.js"></script>
<script src="assets/lireate-shared/lireate-mobile-bar.js"></script>
<script src="assets/lireate-shared/lireate-contact.js"></script>
</body>
</html>

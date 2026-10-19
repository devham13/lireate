<?php
header('Content-Type: text/html; charset=utf-8');
$path = '/prodlenie_tovarnogo_znaka/';
$host = isset($_SERVER['HTTP_HOST']) ? preg_replace('/[^a-zA-Z0-9.\-:]/', '', $_SERVER['HTTP_HOST']) : '';
$site_root = ($host !== '' ? 'https://' . $host : '');
$canonical = $site_root . $path;
$seo_title = 'Продление товарного знака — LIREATE';
$seo_description = 'Продление товарного знака в Роспатенте: сроки по ст. 1491 ГК РФ, пошлины п. 2.22, онлайн-подача и риски пропуска дедлайна. Сопровождение LIREATE.';
$og_type = 'website';
$og_image = $site_root . '/local/templates/lireate/images/og-default.jpg';
$lireate_phone_display = '+7 (960) 048-49-57';
$lireate_phone_tel = 'tel:+79600484957';
$lireate_telegram = 'https://t.me/lireate_patent';
$lireate_whatsapp = 'https://wa.me/79600484957';
require_once dirname(__DIR__) . '/_shared/lireate-messenger-require.inc.php';
$lireate_max = LIREATE_MAX_CHAT_URL;
$faq_entities = [
  ['Какой срок действия товарного знака?', 'По ст. 1491 ГК РФ — 10 лет с даты подачи заявки; продление возможно неограниченно при уплате пошлин.'],
  ['Когда подавать заявление?', 'В последний год действия права; при пропуске — дополнительные 6 месяцев с пошлиной по п. 2.25 Положения о пошлинах.'],
  ['Сколько стоит продление в Роспатенте?', 'П. 2.22: 22 000 руб. + 2 000 руб. за класс свыше 5 и 500 руб. за позицию свыше 10 в классе. Сверяйте с rospatent.gov.ru.'],
  ['Можно ли продлить онлайн?', 'Да, через портал Роспатента и Госуслуги с корректной оплатой и кодом юридически значимого действия.'],
  ['Что если не продлить вовремя?', 'Исключительное право прекращается; обозначение может занять третье лицо.'],
  ['Гарантируете ли продление?', 'Нет. Решение принимает Роспатент после проверки документов и оплаты.'],
];
$lx_breadcrumb_current = 'Продление товарного знака';
$lx_review_filter_services = ['prodlenie-tovarnogo-znaka', 'registraciya-tovarnogo-znaka-v-kitae'];
$schema_graph = [
  '@context' => 'https://schema.org',
  '@graph' => [
    ['@type' => 'Service', '@id' => $canonical . '#service', 'name' => 'Продление товарного знака', 'url' => $canonical, 'description' => $seo_description, 'areaServed' => 'RU', 'provider' => ['@id' => $canonical . '#organization']],
    ['@type' => ['LegalService', 'Organization'], '@id' => $canonical . '#organization', 'name' => 'LIREATE', 'url' => $site_root . '/', 'telephone' => '+7-960-048-49-57', 'sameAs' => [$lireate_telegram, $lireate_whatsapp, $lireate_max]],
    ['@type' => 'BreadcrumbList', 'itemListElement' => [
      ['@type' => 'ListItem', 'position' => 1, 'name' => 'Главная', 'item' => $site_root . '/'],
      ['@type' => 'ListItem', 'position' => 2, 'name' => 'Услуги', 'item' => $site_root . '/uslugi/'],
      ['@type' => 'ListItem', 'position' => 3, 'name' => 'Продление товарного знака', 'item' => $canonical],
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
      <a href="#pricing">Стоимость</a>
      <a href="#reviews">Отзывы</a>
      <a href="#faq">FAQ</a>
      <a href="#contact">Заявка</a>
    </nav>
    <a class="lx-btn lx-btn-primary" href="#contact">Консультация</a>
  </header>
</div>

<main id="primary" class="lireate-svc-page prodlenie-tovarnogo-znaka-page" role="main" tabindex="-1">

<?php include __DIR__ . '/blocks/lireate-breadcrumbs.inc.php'; ?>

<section class="lx-hero" id="top">
  <div class="lx-hero-mascot-slot" data-lx-mascot="renewal"></div>
  <div>
    <p class="lx-eyebrow">LIREATE · товарные знаки · продление</p>
    <h1>Продление товарного знака</h1>
    <p class="lx-hero-lead">Сверяем дедлайн по реестру, считаем пошлины и сопровождаем подачу — без гарантий решения Роспатента.</p>
    <div class="lx-tags"><span>ст. 1491 ГК РФ</span><span>п. 2.22</span><span>ФИПС</span></div>
    <div class="lx-hero-cta">
      <a class="lx-btn lx-btn-primary" href="#contact">Оставить заявку</a>
      <a class="lx-btn lx-btn-ghost lx-btn--with-icon" id="hero-telegram" href="<?php echo htmlspecialchars($lireate_telegram, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" data-lx-icon="telegram"><span>Написать в Telegram</span></a>
      <a class="lx-btn lx-btn-ghost lx-btn--with-icon" id="hero-max" href="<?php echo htmlspecialchars($lireate_max, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" data-lx-icon="max"><span>Написать в Max</span></a>
    </div>
    <div class="lx-hero-mini">
      <div class="lx-mini-stat"><strong class="lx-stat-value" data-lx-count-end="10" data-lx-count-suffix=" лет">10 лет</strong><span>каждое продление</span></div>
      <div class="lx-mini-stat"><strong class="lx-stat-value">12 мес.</strong><span>окно подачи в последний год</span></div>
      <div class="lx-mini-stat"><strong class="lx-stat-value">+6 мес.</strong><span>льготный период с ходатайством</span></div>
    </div>
  </div>
  <div class="lx-glass lx-glass-anim" aria-label="Календарь продления">
    <div class="lx-panel-head"><span>Календарь · ориентиры</span><span class="lx-live">Роспатент</span></div>
    <div class="lx-kpi-row">
      <div class="lx-kpi"><b class="lx-stat-value">п. 2.22</b><small>базовая пошлина 22 000 ₽</small></div>
      <div class="lx-kpi"><b class="lx-stat-value">п. 2.25</b><small>ходатайство 3 000 ₽</small></div>
      <div class="lx-kpi"><b class="lx-stat-value">онлайн</b><small>подача через портал ведомства</small></div>
    </div>
    <ul class="lx-feed">
      <li>Проверка даты окончания срока в свидетельстве и реестре</li>
      <li>Расчёт пошлин по числу классов МКТУ и позиций перечня</li>
      <li>Подача заявления о продлении в установленные сроки</li>
      <li>Запись о продлении в Госреестр</li>
    </ul>
    <div class="lx-route" role="img" aria-label="Маршрут продления">
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

<section class="lx-section lx-reveal" id="when">
  <span class="lx-section-label">Когда нужна</span>
  <h2>Продление товарного знака: сигналы к действию</h2>
  <div class="lx-carousel" data-lx-carousel tabindex="0" aria-label="Когда продлевать знак">
    <button type="button" class="lx-carousel-prev" aria-label="Предыдущий слайд">‹</button>
    <div class="lx-carousel-viewport">
      <ol class="lx-carousel-track">
        <li class="lx-carousel-slide"><article class="lx-card"><h3>Истекает 10 лет</h3><p>Ст. 1491 ГК РФ; сверка по ФИПС.</p></article></li>
        <li class="lx-carousel-slide"><article class="lx-card"><h3>Портфель брендов</h3><p>Единый календарь дедлайнов.</p></article></li>
        <li class="lx-carousel-slide"><article class="lx-card"><h3>Пропущен последний год</h3><p>6 месяцев и пошлина п. 2.25.</p></article></li>
        <li class="lx-carousel-slide"><article class="lx-card"><h3>Смена правообладателя</h3><p>Сверка реестра и договоров.</p></article></li>
        <li class="lx-carousel-slide"><article class="lx-card"><h3>Экспорт в КНР</h3><p>Отдельно — <a href="/registraciya_tovarnogo_znaka_v_kitae/">регистрация в Китае</a>.</p></article></li>
      </ol>
    </div>
    <button type="button" class="lx-carousel-next" aria-label="Следующий слайд">›</button>
    <div class="lx-carousel-dots"></div>
  </div>
</section>

<section class="lx-section lx-reveal" id="program">
  <span class="lx-section-label">Как работаем</span>
  <h2>Этапы сопровождения продления</h2>
  <div class="lx-steps-walk" data-lx-steps-walk>
    <div class="lx-steps-walk-mascot-slot" aria-hidden="true"></div>
    <ol class="lx-steps-walk-list">
      <li class="lx-steps-walk-step"><span class="lx-card-num">01</span><h3>Выписка</h3><p><strong>1–3 дня.</strong> Свидетельство, ФИПС, МКТУ.</p></li>
      <li class="lx-steps-walk-step"><span class="lx-card-num">02</span><h3>Пошлины</h3><p><strong>1–2 дня.</strong> П. 2.22 и при необходимости 2.25.</p></li>
      <li class="lx-steps-walk-step"><span class="lx-card-num">03</span><h3>Подача</h3><p><strong>1 день.</strong> Портал Роспатента / Госуслуги.</p></li>
      <li class="lx-steps-walk-step"><span class="lx-card-num">04</span><h3>Реестр</h3><p><strong>по регламенту.</strong> Запись о продлении.</p></li>
    </ol>
  </div>
</section>

<section class="lx-section lx-reveal" id="pricing">
  <span class="lx-section-label">Стоимость</span>
  <h2>Пошлины Роспатента и услуги LIREATE</h2>
  <div class="lx-price-grid">
    <div class="lx-price-card"><strong data-lx-count-end="22000" data-lx-count-sep="space" data-lx-count-suffix=" ₽">22 000 ₽</strong><span>п. 2.22 — базовая пошлина за продление (ПП РФ № 941, rospatent.gov.ru)</span></div>
    <div class="lx-price-card"><strong>по смете</strong><span>сопровождение LIREATE отдельно от госпошлин</span></div>
  </div>
  <div class="lx-calc" data-lx-calc="prodlenie-tz" id="calc">
    <label>Классов МКТУ <input type="number" data-lx-calc-classes min="1" max="45" value="5"></label>
    <label><input type="checkbox" data-lx-calc-late> Льготные 6 месяцев (п. 2.25, +3 000 ₽)</label>
    <p class="lx-calc-out">Ориентир госпошлины: <strong data-lx-calc-out-gov>22 000 ₽</strong></p>
    <p class="lx-calc-note">Только официальные пошлины; не оферта. Сверяйте с rospatent.gov.ru.</p>
  </div>
</section>

<section class="lx-section lx-reveal" id="risks">
  <span class="lx-section-label">Риски</span>
  <h2>Риски пропуска срока продления</h2>
  <div class="lx-risk-grid">
    <div class="lx-risk-item"><h3>Прекращение права</h3><p>После истечения срока без продления охрана по ст. 1491 ГК РФ прекращается.</p></div>
    <div class="lx-risk-item"><h3>Регистрация третьим лицом</h3><p>Сходное обозначение может занять другой заявитель.</p></div>
    <div class="lx-risk-item"><h3>Ошибка в платёжке</h3><p>Неверный код действия — приостановка; в льготном периоде время ограничено.</p></div>
  </div>
</section>

<?php include __DIR__ . '/blocks/lireate-reviews.inc.php'; ?>

<section class="lx-section lx-reveal" id="faq">
  <span class="lx-section-label">FAQ</span>
  <h2>Вопросы о продлении товарного знака</h2>
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
      <form id="lireate-lead-form" data-lead-topic="Заявка: продление ТЗ" novalidate>
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
    <a class="lx-related-card" href="/registraciya_tovarnogo_znaka_v_kitae/"><span class="lx-related-card-type">Услуга</span><strong>Регистрация в Китае</strong></a>
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
<script src="assets/lireate-shared/lireate-calculator.js"></script>
<script src="assets/lireate-shared/lireate-reviews.js"></script>
<script src="assets/lireate-shared/lireate-mobile-bar.js"></script>
<script src="assets/lireate-shared/lireate-contact.js"></script>
</body>
</html>

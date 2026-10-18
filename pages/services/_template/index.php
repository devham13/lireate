<?php
/**
 * Шаблон страницы услуги LIREATE.
 * Скопируйте каталог _template → pages/services/<slug>/ и замените TODO.
 * Эталон: pages/services/registraciya-tovarnogo-znaka-v-kitae/
 */
header('Content-Type: text/html; charset=utf-8');
$path = '/uslugi/SLUG-ЗАМЕНИТЬ/'; // TODO: совпадает с publish.manifest.json url_path
$host = isset($_SERVER['HTTP_HOST']) ? preg_replace('/[^a-zA-Z0-9.\-:]/', '', $_SERVER['HTTP_HOST']) : '';
$site_root = ($host !== '' ? 'https://' . $host : '');
$canonical = $site_root . $path;
$seo_title = 'TODO: заголовок до 60 символов — LIREATE';
$seo_description = 'TODO: описание ровно 140–160 символов: суть услуги, география, польза, бренд LIREATE. Без гарантий исхода процедуры или суда.';
$og_type = 'website';
$og_image = $site_root . '/local/templates/lireate/images/og-default.jpg';
$faq_entities = [
  ['TODO вопрос FAQ 1?', 'TODO краткий ответ.'],
  ['TODO вопрос FAQ 2?', 'TODO краткий ответ.'],
];
$schema_graph = [
  '@context' => 'https://schema.org',
  '@graph' => [
    [
      '@type' => 'Service',
      '@id' => $canonical . '#service',
      'name' => 'TODO название услуги',
      'url' => $canonical,
      'description' => $seo_description,
      'provider' => ['@id' => $canonical . '#organization'],
    ],
    [
      '@type' => ['LegalService', 'Organization'],
      '@id' => $canonical . '#organization',
      'name' => 'LIREATE',
      'url' => $site_root . '/',
    ],
    [
      '@type' => 'BreadcrumbList',
      'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Главная', 'item' => $site_root . '/'],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Услуги', 'item' => $site_root . '/uslugi/'],
        ['@type' => 'ListItem', 'position' => 3, 'name' => 'TODO', 'item' => $canonical],
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
<html lang="ru">
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
      <a href="#faq">FAQ</a>
      <a href="#contact">Заявка</a>
    </nav>
    <a class="lx-btn lx-btn-primary" href="#contact">Консультация</a>
  </header>
</div>

<main id="primary" class="lireate-svc-page SLUG-page" role="main" tabindex="-1">

<section class="lx-hero" id="top">
  <div>
    <p class="lx-eyebrow">LIREATE · TODO · направление</p>
    <h1>TODO: один H1 — название услуги</h1>
    <p class="lx-hero-lead">TODO: лид 2–3 предложения, без гарантий исхода.</p>
    <div class="lx-tags" aria-label="Темы услуги">
      <span>Тег 1</span><span>Тег 2</span><span>Тег 3</span>
    </div>
    <div class="lx-hero-cta">
      <a class="lx-btn lx-btn-primary" href="#contact">Оставить заявку</a>
      <a class="lx-btn lx-btn-ghost" href="#program">Смотреть программу</a>
    </div>
    <div class="lx-hero-mini">
      <div class="lx-mini-stat"><strong class="lx-stat-value">TODO</strong><span>подпись</span></div>
    </div>
  </div>
  <div class="lx-glass" aria-label="Ориентиры по услуге">
    <div class="lx-panel-head">
      <span>Маршрут · ориентиры</span>
      <span class="lx-live">Сопровождение</span>
    </div>
    <div class="lx-kpi-row">
      <div class="lx-kpi"><b class="lx-stat-value">TODO</b><small>метрика</small></div>
    </div>
    <ul class="lx-feed">
      <li>Этап 1 — кратко</li>
      <li>Этап 2 — кратко</li>
    </ul>
    <div class="lx-route" role="img" aria-label="Схема этапов">
      <ol class="lx-route-steps">
        <li><span class="lx-route-node">01</span><span class="lx-route-label">Этап</span></li>
        <li><span class="lx-route-node">02</span><span class="lx-route-label">Этап</span></li>
      </ol>
    </div>
  </div>
</section>

<section class="lx-section lx-reveal" id="program">
  <span class="lx-section-label">Маршрут</span>
  <h2>TODO: программа работ</h2>
  <div class="lx-card-grid">
    <article class="lx-card lx-reveal">
      <span class="lx-card-num">01</span>
      <h3>Этап</h3>
      <p>Описание этапа.</p>
    </article>
  </div>
</section>

<section class="lx-section lx-reveal" id="faq">
  <span class="lx-section-label">FAQ</span>
  <h2>FAQ</h2>
  <details class="lx-faq"><summary>Вопрос</summary><p>Ответ.</p></details>
</section>

<section class="lx-section lx-reveal" id="related">
  <span class="lx-section-label">LIREATE</span>
  <h2>Смежные услуги и материалы</h2>
  <ul class="lx-related-links">
    <li><a href="/uslugi/">Каталог услуг</a></li>
    <li><a href="/news/">Новости</a></li>
    <li><a href="/contacts/">Контакты и телефон</a></li>
  </ul>
</section>

<section class="lx-section lx-reveal" id="contact">
  <div class="lx-contact">
    <div>
      <span class="lx-section-label">Контакт</span>
      <h2>Заявка и мессенджеры</h2>
      <div class="lx-msg-grid">
        <a class="lx-msg-btn" id="link-telegram" href="/contacts/">Telegram</a>
        <a class="lx-msg-btn" id="link-whatsapp" href="/contacts/">WhatsApp</a>
      </div>
    </div>
    <div class="lx-form-panel">
      <form id="lireate-lead-form" novalidate>
        <label for="lf-name">Имя</label>
        <input id="lf-name" name="name" type="text" required>
        <label for="lf-phone">Телефон</label>
        <input id="lf-phone" name="phone" type="tel" required>
        <label for="lf-msg">Сообщение</label>
        <textarea id="lf-msg" name="message" required></textarea>
        <label class="lx-consent"><input type="checkbox" required> Согласие на обработку ПДн</label>
        <button type="submit" class="lx-btn lx-btn-primary">Отправить</button>
      </form>
    </div>
  </div>
</section>

</main>

<aside class="lx-dock" aria-label="Быстрый контакт">
  <a href="#contact" aria-label="Форма">✉</a>
  <a id="dock-telegram" href="/contacts/" aria-label="Telegram">TG</a>
</aside>

<script src="assets/lireate-shared/lireate-reveal.js"></script>
</body>
</html>

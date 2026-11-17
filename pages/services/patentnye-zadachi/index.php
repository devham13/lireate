<?php
header('Content-Type: text/html; charset=utf-8');
$path = '/patentnye-zadachi/';
$host = isset($_SERVER['HTTP_HOST']) ? preg_replace('/[^a-zA-Z0-9.\-:]/', '', $_SERVER['HTTP_HOST']) : '';
$site_root = ($host !== '' ? 'https://' . $host : '');
$canonical = $site_root . $path;
$seo_title = 'Патентные задачи — инструменты LIREATE';
$seo_description = 'Каталог инструментов для товарных знаков: калькуляторы пошлин регистрации и продления, редактор МКТУ. Расчёт по официальной таблице Роспатента.';
$lireate_telegram = 'https://t.me/lireate_patent';
$lireate_whatsapp = 'https://wa.me/79600484957';
require_once dirname(__DIR__) . '/_shared/lireate-service-messenger-include.inc.php';
require_once dirname(__DIR__) . '/_shared/lireate-service-assets.php';
require_once dirname(__DIR__) . '/_shared/lireate-patent-breadcrumbs.php';
$lx_service_asset_dir = __DIR__;
$lireate_metrika_id = getenv('LIREATE_YANDEX_METRIKA_ID');
if ($lireate_metrika_id === false || $lireate_metrika_id === '') {
    $lireate_metrika_id = '526' . '22734';
}
$schema_graph = [
  '@context' => 'https://schema.org',
  '@graph' => [
    ['@type' => 'WebPage', 'name' => 'Патентные задачи', 'url' => $canonical],
    ['@type' => 'BreadcrumbList', 'itemListElement' => lireate_patent_breadcrumb_schema_items($site_root, 'Каталог', $canonical)],
  ],
];
?><!DOCTYPE html>
<html lang="ru" data-lireate-metrika-id="<?php echo htmlspecialchars(preg_replace('/\D/', '', (string) $lireate_metrika_id), ENT_QUOTES, 'UTF-8'); ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo htmlspecialchars($seo_title, ENT_QUOTES, 'UTF-8'); ?></title>
  <meta name="description" content="<?php echo htmlspecialchars($seo_description, ENT_QUOTES, 'UTF-8'); ?>">
  <link rel="canonical" href="<?php echo htmlspecialchars($canonical, ENT_QUOTES, 'UTF-8'); ?>">
  <link rel="stylesheet" href="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-service-dark.css', $lx_service_asset_dir); ?>">
  <link rel="stylesheet" href="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-patent-tools.css', $lx_service_asset_dir); ?>">
  <script type="application/ld+json"><?php echo json_encode($schema_graph, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?></script>
</head>
<body class="lireate-svc-body lx-patent-catalog">
<div class="lx-bg-glow" aria-hidden="true"></div>
<div class="lx-nav-shell">
  <header class="lx-nav-pill" role="banner">
    <a class="lx-logo" href="/">LIREATE</a>
    <nav class="lx-nav-links" aria-label="Раздел">
      <a href="#tools">Инструменты</a>
      <a href="#help">Справка</a>
    </nav>
    <a class="lx-btn lx-btn-primary" href="<?php echo htmlspecialchars($lireate_telegram, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer">Консультация</a>
  </header>
</div>
<main id="primary" class="lireate-svc-page lx-reveal" tabindex="-1">
  <section class="lx-hero" id="top">
    <p class="lx-eyebrow">LIREATE · инструменты</p>
    <h1>Патентные задачи</h1>
    <p class="lx-hero-lead">Подготовка заявки, свидетельство и платежи — в одном каталоге. На первом этапе доступны калькуляторы пошлин и редактор МКТУ. Расчёт выполняется в браузере, данные полей не уходят на сервер.</p>
  </section>

  <section class="lx-section lx-cat-group lx-reveal" id="tools">
    <h2>Подготовка заявки</h2>
    <div class="lx-cat-cards">
      <a class="lx-cat-card" href="/patentnye-zadachi/poshliny-registraciya/"><strong>Пошлины регистрации</strong><span>Два режима: по числу классов и по перечню МКТУ</span></a>
      <a class="lx-cat-card" href="/patentnye-zadachi/redaktor-mktu/"><strong>Редактор МКТУ</strong><span>Нормализация перечня и передача в калькулятор</span></a>
    </div>
  </section>

  <section class="lx-section lx-cat-group lx-reveal">
    <h2>Свидетельство и реестр</h2>
    <div class="lx-cat-cards">
      <a class="lx-cat-card" href="/patentnye-zadachi/poshliny-prodlenie/"><strong>Пошлины продления</strong><span>Классы, позиции, дополнительный срок п. 2.25</span></a>
    </div>
  </section>

  <section class="lx-section lx-reveal" id="help">
    <h2>Связанные страницы</h2>
    <ul>
      <li><a href="/mktu">Каталог МКТУ</a></li>
      <li><a href="/registraciya_tovarnogo_znaka/">Регистрация товарного знака</a></li>
      <li><a href="/prodlenie_tovarnogo_znaka/">Продление товарного знака</a></li>
      <li><a href="/findtz/">Проверка товарного знака</a></li>
      <li><a href="/kak-zaregistrirovat-tovarnyy-znak-samostoyatelno-2026/">Самостоятельная подача</a></li>
    </ul>
  </section>
</main>
<?php $lireate_max = LIREATE_MAX_CHAT_URL; include dirname(__DIR__) . '/_shared/blocks/lireate-patent-tool-footer.inc.php'; ?>
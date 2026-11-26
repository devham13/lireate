<?php
header('Content-Type: text/html; charset=utf-8');
$path = '/patentnye-zadachi/';
$host = isset($_SERVER['HTTP_HOST']) ? preg_replace('/[^a-zA-Z0-9.\-:]/', '', $_SERVER['HTTP_HOST']) : '';
$site_root = ($host !== '' ? 'https://' . $host : '');
$canonical = $site_root . $path;
$seo_title = 'Патентные задачи — инструменты LIREATE';
$seo_description = 'Инструменты для товарных знаков: пошлины, МКТУ, адрес, TIFF, контакты, оплата. Расчёт в браузере по таблице Роспатента. Данные не уходят с устройства.';
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
$lx_breadcrumb_current = 'Патентные задачи';
$lx_patent_breadcrumb_is_catalog = true;
$lx_patent_nav_links = array(
    array('href' => '#tools', 'label' => 'Инструменты'),
    array('href' => '#help', 'label' => 'Справка'),
);
$lx_patent_cta_href = $lireate_telegram;
$lx_patent_cta_external = true;
$schema_graph = array(
  '@context' => 'https://schema.org',
  '@graph' => array(
    array('@type' => 'WebPage', 'name' => 'Патентные задачи', 'url' => $canonical),
    array('@type' => 'BreadcrumbList', 'itemListElement' => lireate_patent_breadcrumb_schema_items($site_root, 'Каталог', $canonical)),
  ),
);
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
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
  <script type="application/ld+json"><?php echo json_encode($schema_graph, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?></script>
  <link rel="stylesheet" href="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-light-v4.css', $lx_service_asset_dir); ?>"><!-- lireate_light_v4 -->
  <link rel="stylesheet" href="/template/css/lireate-qa-v6.css?v=20261126">
  <script defer src="/template/js/lireate-qa-v6.js?v=20261126"></script>
</head>
<body class="lireate-svc-body lx-patent-catalog lireate-theme-light-v4 lxq-patent-tasks">
<?php $lxq_sidebar = isset($_SERVER['DOCUMENT_ROOT']) ? rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/template/lireate/lireate-patent-tasks-sidebar-v6.inc.php' : ''; if ($lxq_sidebar !== '' && is_file($lxq_sidebar)) { include $lxq_sidebar; } ?>
<?php include __DIR__ . '/blocks/lireate-patent-header.inc.php'; ?>
<main id="primary" class="lireate-svc-page" role="main" tabindex="-1">
<?php include __DIR__ . '/blocks/lireate-patent-breadcrumbs.inc.php'; ?>
  <section class="lx-hero lx-hero--stack lx-reveal" id="top">
    <div>
      <p class="lx-eyebrow">LIREATE · инструменты</p>
      <h1>Патентные задачи</h1>
      <p class="lx-hero-lead">Подготовка заявки, работа со свидетельством и расчёт пошлин — в одном каталоге. Расчёт выполняется в браузере, содержимое полей не отправляется на сервер.</p>
    </div>
  </section>

  <section class="lx-section lx-cat-group lx-reveal" id="tools">
    <h2>Подготовка заявки</h2>
    <div class="lx-cat-cards">
      <a class="lx-cat-card" href="/patentnye-zadachi/poshliny-registraciya/"><strong>Пошлины регистрации</strong><span>Два режима: по числу классов и по перечню МКТУ</span><span class="lx-cat-card-cta">Открыть →</span></a>
      <a class="lx-cat-card" href="/patentnye-zadachi/redaktor-mktu/"><strong>Редактор МКТУ</strong><span>Нормализация перечня и передача в калькулятор</span><span class="lx-cat-card-cta">Открыть →</span></a>
      <a class="lx-cat-card" href="/patentnye-zadachi/adres-zayavitelya/"><strong>Адрес заявителя</strong><span>Форматирование адреса заявителя и для переписки</span><span class="lx-cat-card-cta">Открыть →</span></a>
      <a class="lx-cat-card" href="/patentnye-zadachi/fail-slovesnogo-znaka/"><strong>Файл словесного знака</strong><span>Предпросмотр и TIFF 944×944, 300 dpi</span><span class="lx-cat-card-cta">Открыть →</span></a>
    </div>
  </section>

  <section class="lx-section lx-cat-group lx-reveal">
    <h2>Свидетельство и реестр</h2>
    <div class="lx-cat-cards">
      <a class="lx-cat-card" href="/patentnye-zadachi/poshliny-prodlenie/"><strong>Пошлины продления</strong><span>Классы, позиции, дополнительный срок п. 2.25</span><span class="lx-cat-card-cta">Открыть →</span></a>
      <a class="lx-cat-card" href="/patentnye-zadachi/poshliny-izmeneniya/"><strong>Пошлины за изменения</strong><span>п. 2.16 и 2.17 по числу знаков и изменений</span><span class="lx-cat-card-cta">Открыть →</span></a>
      <a class="lx-cat-card" href="/patentnye-zadachi/sokrashchenie-mktu/"><strong>Сокращение МКТУ</strong><span>Сравнение продления до и после сокращения</span><span class="lx-cat-card-cta">Открыть →</span></a>
    </div>
  </section>

  <section class="lx-section lx-cat-group lx-reveal">
    <h2>Платежи</h2>
    <div class="lx-cat-cards">
      <a class="lx-cat-card" href="/patentnye-zadachi/oplata-patentnyh-poshlin/"><strong>Оплата патентных пошлин</strong><span>Черновик реквизитов и QR (ST00012) в браузере</span><span class="lx-cat-card-cta">Открыть →</span></a>
      <a class="lx-cat-card" href="/patentnye-zadachi/oplata-uslug-fips/"><strong>Оплата услуг ФИПС</strong><span>Отдельный профиль; при неподтверждённых данных — официальные ссылки</span><span class="lx-cat-card-cta">Открыть →</span></a>
      <a class="lx-cat-card" href="/patentnye-zadachi/poisk-platezha-evm/"><strong>Госпошлина ПрЭВМ</strong><span>QR по прил. №2 и инструкция проверки в ЛК ФИПС</span><span class="lx-cat-card-cta">Открыть →</span></a>
    </div>
  </section>

  <section class="lx-section lx-cat-group lx-reveal">
    <h2>Помощь с ФИПС</h2>
    <div class="lx-cat-cards">
      <a class="lx-cat-card" href="/patentnye-zadachi/kontakty-fips/"><strong>Контакты ФИПС</strong><span>Телефоны и e-mail Роспатента и ФИПС</span><span class="lx-cat-card-cta">Открыть →</span></a>
      <a class="lx-cat-card" href="/patentnye-zadachi/starye-zayavki/"><strong>Старые заявки</strong><span>Переход в АРМ «Регистратор» под вашей учётной записью</span><span class="lx-cat-card-cta">Открыть →</span></a>
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
<script src="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-light-v4.js', $lx_service_asset_dir); ?>"></script><!-- lireate_light_v4 -->
<?php $lireate_max = LIREATE_MAX_CHAT_URL; include __DIR__ . '/blocks/lireate-patent-tool-footer.inc.php'; ?>
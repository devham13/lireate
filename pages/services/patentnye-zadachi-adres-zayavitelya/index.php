<?php
header('Content-Type: text/html; charset=utf-8');
$path = '/patentnye-zadachi/adres-zayavitelya/';
$host = isset($_SERVER['HTTP_HOST']) ? preg_replace('/[^a-zA-Z0-9.\-:]/', '', $_SERVER['HTTP_HOST']) : '';
$site_root = ($host !== '' ? 'https://' . $host : '');
$canonical = $site_root . $path;
$seo_title = 'Адрес заявителя для заявки — LIREATE';
$seo_description = 'Форматирование адреса заявителя и адреса для переписки по заявке на товарный знак: сокращения по правилам, копирование, только в браузере, без ГАР.';
$lireate_telegram = 'https://t.me/lireate_patent';
require_once dirname(__DIR__) . '/_shared/lireate-service-messenger-include.inc.php';
require_once dirname(__DIR__) . '/_shared/lireate-service-assets.php';
require_once dirname(__DIR__) . '/_shared/lireate-patent-breadcrumbs.php';
$lx_service_asset_dir = __DIR__;
$lireate_metrika_id = getenv('LIREATE_YANDEX_METRIKA_ID');
if ($lireate_metrika_id === false || $lireate_metrika_id === '') {
    $lireate_metrika_id = '526' . '22734';
}
$lx_breadcrumb_current = 'Адрес заявителя';
$lx_patent_cta_href = $lireate_telegram;
$lx_patent_cta_external = true;
$schema_graph = array(
  '@context' => 'https://schema.org',
  '@graph' => array(
    array('@type' => 'WebApplication', 'name' => 'Адрес заявителя', 'url' => $canonical),
    array('@type' => 'BreadcrumbList', 'itemListElement' => lireate_patent_breadcrumb_schema_items($site_root, 'Адрес заявителя', $canonical)),
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
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet" media="print" onload="this.media='all'"><noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap"></noscript>
  <script type="application/ld+json"><?php echo json_encode($schema_graph, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?></script>
  <link rel="stylesheet" href="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-light-v4.css', $lx_service_asset_dir); ?>"><!-- lireate_light_v4 -->
  <link rel="stylesheet" href="/template/css/lireate-qa-v6.css?v=20261126d">
  <link rel="stylesheet" href="/template/css/lireate-patent-center-v1.css?v=20261127b"><!-- patent-center-v1 -->
<script defer src="/template/js/lireate-qa-v6.js?v=20261126d"></script>
</head>
<body class="lireate-svc-body lx-patent-tool lireate-theme-light-v4 lxq-patent-tasks">
<?php
$lxq_sidebar = isset($_SERVER['DOCUMENT_ROOT']) ? rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/template/lireate/lireate-patent-tasks-sidebar-v6.inc.php' : '';
if ($lxq_sidebar !== '' && is_file($lxq_sidebar)) { include $lxq_sidebar; }
?>
<?php include __DIR__ . '/blocks/lireate-patent-header.inc.php'; ?>
<main id="primary" class="lireate-svc-page" role="main" tabindex="-1" data-lx-patent-adres>
<?php include __DIR__ . '/blocks/lireate-patent-breadcrumbs.inc.php'; ?>
  <section class="lx-hero lx-hero--stack">
    <div>
      <p class="lx-eyebrow">Патентные задачи · заявка</p>
      <h1>Адрес заявителя</h1>
      <p class="lx-hero-lead">Отдельно оформите адрес заявителя и адрес для переписки. Сверка по ГАР на этой странице не выполняется.</p>
    </div>
  </section>
  <section class="lx-section lx-section--tool" id="tool">
    <div class="lx-tool-grid">
      <div>
        <div class="lx-field">
          <label for="lx-adres-a">Адрес заявителя (исходник)</label>
          <textarea id="lx-adres-a" data-lx-adres-applicant-in placeholder="125993, Москва, ул. Примерная, д. 1, оф. 2"></textarea>
        </div>
        <div class="lx-field">
          <label for="lx-adres-c">Адрес для переписки (исходник)</label>
          <textarea id="lx-adres-c" data-lx-adres-corr-in placeholder="Если отличается от адреса заявителя"></textarea>
        </div>
        <div class="lx-actions">
          <button type="button" class="lx-btn lx-btn-primary" data-lx-adres-run>Форматировать</button>
          <button type="button" class="lx-btn lx-btn-ghost" data-lx-adres-clear>Очистить</button>
        </div>
        <p class="lx-hint" data-lx-adres-note></p>
      </div>
      <div>
        <div class="lx-field">
          <label for="lx-adres-out-a">Для заявки — заявитель</label>
          <textarea id="lx-adres-out-a" data-lx-adres-applicant-out readonly></textarea>
          <button type="button" class="lx-btn lx-btn-ghost" data-lx-adres-copy="applicant">Копировать</button>
        </div>
        <div class="lx-field">
          <label for="lx-adres-out-c">Для заявки — переписка</label>
          <textarea id="lx-adres-out-c" data-lx-adres-corr-out readonly></textarea>
          <button type="button" class="lx-btn lx-btn-ghost" data-lx-adres-copy="corr">Копировать</button>
        </div>
        <p class="lx-hint"><a href="/kak-zaregistrirovat-tovarnyy-znak-samostoyatelno-2026/">Самостоятельная подача с LIREATE</a></p>
      </div>
    </div>
  </section>
</main>
<script src="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-patent-address-core.js', $lx_service_asset_dir); ?>"></script>
<script src="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-patent-tools.js', $lx_service_asset_dir); ?>"></script>
<?php
$lx_patent_footer_mode = 'reference';
$lx_patent_footer_ref_url = 'https://rospatent.gov.ru/ru/contacts';
$lireate_max = LIREATE_MAX_CHAT_URL;
?>
<script src="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-light-v4.js', $lx_service_asset_dir); ?>"></script><!-- lireate_light_v4 -->
<?php include __DIR__ . '/blocks/lireate-patent-tool-footer.inc.php'; ?>
<?php
header('Content-Type: text/html; charset=utf-8');
$path = '/patentnye-zadachi/fail-slovesnogo-znaka/';
$host = isset($_SERVER['HTTP_HOST']) ? preg_replace('/[^a-zA-Z0-9.\-:]/', '', $_SERVER['HTTP_HOST']) : '';
$site_root = ($host !== '' ? 'https://' . $host : '');
$canonical = $site_root . $path;
$seo_title = 'Файл словесного знака — TIFF для заявки — LIREATE';
$seo_description = 'Предпросмотр и скачивание файла словесного товарного знака для заявки: TIFF 944×944 px, 300 dpi, RGB без сжатия, белый фон по требованиям Роспатента.';
$lireate_telegram = 'https://t.me/lireate_patent';
require_once dirname(__DIR__) . '/_shared/lireate-service-messenger-include.inc.php';
require_once dirname(__DIR__) . '/_shared/lireate-service-assets.php';
require_once dirname(__DIR__) . '/_shared/lireate-patent-breadcrumbs.php';
$lx_service_asset_dir = __DIR__;
$lireate_metrika_id = getenv('LIREATE_YANDEX_METRIKA_ID');
if ($lireate_metrika_id === false || $lireate_metrika_id === '') {
    $lireate_metrika_id = '526' . '22734';
}
$lx_breadcrumb_current = 'Файл словесного знака';
$lx_patent_cta_href = $lireate_telegram;
$lx_patent_cta_external = true;
$schema_graph = array(
  '@context' => 'https://schema.org',
  '@graph' => array(
    array('@type' => 'WebApplication', 'name' => 'Файл словесного знака', 'url' => $canonical),
    array('@type' => 'BreadcrumbList', 'itemListElement' => lireate_patent_breadcrumb_schema_items($site_root, 'Файл словесного знака', $canonical)),
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
</head>
<body class="lireate-svc-body lx-patent-tool lireate-theme-light-v4">
<?php /* lireate-public-cms-chrome-wire:v1 */ require_once dirname(__DIR__) . '/_shared/lireate-public-cms-chrome-wire.inc.php'; lireate_public_cms_chrome_wire_open(); ?>
<?php include __DIR__ . '/blocks/lireate-patent-header.inc.php'; ?>
<main id="primary" class="lireate-svc-page" role="main" tabindex="-1" data-lx-patent-wordmark>
<?php include __DIR__ . '/blocks/lireate-patent-breadcrumbs.inc.php'; ?>
  <section class="lx-hero lx-hero--stack">
    <div>
      <p class="lx-eyebrow">Патентные задачи · изображение</p>
      <h1>Файл словесного знака</h1>
      <p class="lx-hero-lead">Требования Роспатента: <a href="https://rospatent.gov.ru/index.php/ru/faq/27-kakie-trebovaniya-predyavlyayutsya-k-fajlu-izobrazheniya-zayavlyaemogo-oboznacheniya" target="_blank" rel="noopener noreferrer">FAQ по файлу</a>. Файл формируется в браузере.</p>
    </div>
  </section>
  <section class="lx-section lx-section--tool" id="tool">
    <div class="lx-tool-grid">
      <div>
        <div class="lx-field">
          <label for="lx-wm-in">Текст обозначения</label>
          <textarea id="lx-wm-in" data-lx-wordmark-in placeholder="ЛИРЕЙТ"></textarea>
        </div>
        <div class="lx-actions">
          <button type="button" class="lx-btn lx-btn-primary" data-lx-wordmark-preview>Предпросмотр</button>
          <button type="button" class="lx-btn lx-btn-primary" data-lx-wordmark-download>Скачать TIFF</button>
          <button type="button" class="lx-btn lx-btn-ghost" data-lx-wordmark-clear>Очистить</button>
        </div>
        <p class="lx-warn" data-lx-wordmark-error hidden></p>
        <p class="lx-hint">944×944 px, 300 dpi, RGB 24-bit, без сжатия, белый фон.</p>
      </div>
      <aside>
        <p class="lx-field-label">Предпросмотр</p>
        <div class="lx-wordmark-preview" data-lx-wordmark-canvas></div>
        <p class="lx-hint"><a href="/registraciya_tovarnogo_znaka/">Регистрация с LIREATE</a></p>
      </aside>
    </div>
  </section>
</main>
<script src="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-patent-wordmark-core.js', $lx_service_asset_dir); ?>"></script>
<script src="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-patent-tools.js', $lx_service_asset_dir); ?>"></script>
<?php
$lx_patent_footer_mode = 'reference';
$lx_patent_footer_ref_url = 'https://rospatent.gov.ru/ru/faq/27-kakie-trebovaniya-predyavlyayutsya-k-fajlu-izobrazheniya-zayavlyaemogo-oboznacheniya';
$lireate_max = LIREATE_MAX_CHAT_URL;
?>
<script src="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-light-v4.js', $lx_service_asset_dir); ?>"></script><!-- lireate_light_v4 -->
<?php /* lireate-public-cms-chrome-wire:v1 */ lireate_public_cms_chrome_wire_close(); ?>
<?php include __DIR__ . '/blocks/lireate-patent-tool-footer.inc.php'; ?>
<?php
header('Content-Type: text/html; charset=utf-8');
$path = '/patentnye-zadachi/oplata-patentnyh-poshlin/';
$host = isset($_SERVER['HTTP_HOST']) ? preg_replace('/[^a-zA-Z0-9.\-:]/', '', $_SERVER['HTTP_HOST']) : '';
$site_root = ($host !== '' ? 'https://' . $host : '');
$canonical = $site_root . $path;
$seo_title = 'Оплата патентных пошлин Роспатента — LIREATE';
$seo_description = 'Черновик платёжных реквизитов и QR ST00012 по профилю Роспатента из приложения к Положению о пошлинах. Сумма и назначение — только в браузере, без сервера.';
$lireate_telegram = 'https://t.me/lireate_patent';
require_once dirname(__DIR__) . '/_shared/lireate-service-messenger-include.inc.php';
require_once dirname(__DIR__) . '/_shared/lireate-service-assets.php';
require_once dirname(__DIR__) . '/_shared/lireate-patent-breadcrumbs.php';
$lx_service_asset_dir = __DIR__;
$lireate_metrika_id = getenv('LIREATE_YANDEX_METRIKA_ID');
if ($lireate_metrika_id === false || $lireate_metrika_id === '') {
    $lireate_metrika_id = '526' . '22734';
}
$lx_breadcrumb_current = 'Оплата пошлин';
$lx_patent_cta_href = $lireate_telegram;
$lx_patent_cta_external = true;
$lx_pay_profiles_json = lireate_service_asset_href('assets/lireate-shared/lireate-patent-payment-profiles.json', $lx_service_asset_dir);
$schema_graph = array(
  '@context' => 'https://schema.org',
  '@graph' => array(
    array('@type' => 'WebApplication', 'name' => 'Оплата пошлин', 'url' => $canonical),
    array('@type' => 'BreadcrumbList', 'itemListElement' => lireate_patent_breadcrumb_schema_items($site_root, 'Оплата пошлин', $canonical)),
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
<?php /* lireate-public-cms-chrome-wire:v1 */ require_once dirname(dirname(__DIR__)) . '/_shared/lireate-public-cms-chrome-lib.php'; lireate_public_cms_include_chrome(); lireate_public_cms_include_subnav(); lireate_public_cms_content_open(); ?>
<?php include __DIR__ . '/blocks/lireate-patent-header.inc.php'; ?>
<main id="primary" class="lireate-svc-page" role="main" tabindex="-1" data-lx-patent-pay="patent" data-lx-pay-profiles-json="<?php echo htmlspecialchars($lx_pay_profiles_json, ENT_QUOTES, 'UTF-8'); ?>">
<?php include __DIR__ . '/blocks/lireate-patent-breadcrumbs.inc.php'; ?>
  <section class="lx-hero lx-hero--stack">
    <div>
      <p class="lx-eyebrow">Патентные задачи · платежи</p>
      <h1>Оплата патентных пошлин</h1>
      <p class="lx-hero-lead">Реквизиты по <a href="https://rospatent.gov.ru/content/uploadfiles/rekviziti-pril1.pdf" target="_blank" rel="noopener noreferrer">приложению №1 к приказу Роспатента № 167</a>. Образец — черновик для сверки в банке.</p>
    </div>
  </section>
  <section class="lx-section lx-section--tool" id="tool" data-lx-pay-tool></section>
</main>
<script src="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-patent-payment-core.js', $lx_service_asset_dir); ?>"></script>
<script src="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-qrcode-npm.js', $lx_service_asset_dir); ?>"></script>
<script src="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-qrcode.min.js', $lx_service_asset_dir); ?>"></script>
<script src="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-patent-tools.js', $lx_service_asset_dir); ?>"></script>
<script src="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-patent-stage3.js', $lx_service_asset_dir); ?>"></script>
<?php
$lx_patent_footer_mode = 'reference';
$lx_patent_footer_ref_url = 'https://rospatent.gov.ru/ru/activities/dues/req';
$lireate_max = LIREATE_MAX_CHAT_URL;
?>
<script src="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-light-v4.js', $lx_service_asset_dir); ?>"></script><!-- lireate_light_v4 -->
<?php /* lireate-public-cms-chrome-wire:v1 */ lireate_public_cms_content_close(); ?>
<?php include __DIR__ . '/blocks/lireate-patent-tool-footer.inc.php'; ?>

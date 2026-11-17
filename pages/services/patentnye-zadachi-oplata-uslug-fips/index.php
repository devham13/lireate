<?php
header('Content-Type: text/html; charset=utf-8');
$path = '/patentnye-zadachi/oplata-uslug-fips/';
$host = isset($_SERVER['HTTP_HOST']) ? preg_replace('/[^a-zA-Z0-9.\-:]/', '', $_SERVER['HTTP_HOST']) : '';
$site_root = ($host !== '' ? 'https://' . $host : '');
$canonical = $site_root . $path;
$seo_title = 'Оплата платных услуг ФИПС — LIREATE';
$seo_description = 'Справка об оплате платных услуг ФИПС: отдельный профиль реквизитов. Пока данные не подтверждены официально — только ссылки на сайт ФИПС, без QR на LIREATE.';
$lireate_telegram = 'https://t.me/lireate_patent';
require_once dirname(__DIR__) . '/_shared/lireate-service-messenger-include.inc.php';
require_once dirname(__DIR__) . '/_shared/lireate-service-assets.php';
require_once dirname(__DIR__) . '/_shared/lireate-patent-breadcrumbs.php';
$lx_service_asset_dir = __DIR__;
$lireate_metrika_id = getenv('LIREATE_YANDEX_METRIKA_ID');
if ($lireate_metrika_id === false || $lireate_metrika_id === '') {
    $lireate_metrika_id = '526' . '22734';
}
$lx_breadcrumb_current = 'Оплата услуг ФИПС';
$lx_patent_cta_href = $lireate_telegram;
$lx_patent_cta_external = true;
$lx_pay_profiles_json = lireate_service_asset_href('assets/lireate-shared/lireate-patent-payment-profiles.json', $lx_service_asset_dir);
$schema_graph = array(
  '@context' => 'https://schema.org',
  '@graph' => array(
    array('@type' => 'WebApplication', 'name' => 'Оплата услуг ФИПС', 'url' => $canonical),
    array('@type' => 'BreadcrumbList', 'itemListElement' => lireate_patent_breadcrumb_schema_items($site_root, 'Оплата услуг ФИПС', $canonical)),
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
</head>
<body class="lireate-svc-body lx-patent-tool">
<?php include dirname(__DIR__) . '/_shared/blocks/lireate-patent-header.inc.php'; ?>
<main id="primary" class="lireate-svc-page" role="main" tabindex="-1" data-lx-patent-pay="fips" data-lx-pay-profiles-json="<?php echo htmlspecialchars($lx_pay_profiles_json, ENT_QUOTES, 'UTF-8'); ?>">
<?php include dirname(__DIR__) . '/_shared/blocks/lireate-patent-breadcrumbs.inc.php'; ?>
  <section class="lx-hero lx-hero--stack">
    <div>
      <p class="lx-eyebrow">Патентные задачи · платные услуги</p>
      <h1>Оплата платных услуг ФИПС</h1>
      <p class="lx-hero-lead">Платные услуги ФИПС отделены от патентных пошлин. Реквизиты сверяются только с официальной публикацией.</p>
    </div>
  </section>
  <section class="lx-section lx-section--tool" id="tool" data-lx-pay-tool></section>
</main>
<script src="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-patent-payment-core.js', $lx_service_asset_dir); ?>"></script>
<script src="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-qrcode.min.js', $lx_service_asset_dir); ?>"></script>
<script src="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-patent-tools.js', $lx_service_asset_dir); ?>"></script>
<script src="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-patent-stage3.js', $lx_service_asset_dir); ?>"></script>
<?php
$lx_patent_footer_mode = 'reference';
$lx_patent_footer_ref_url = 'https://rospatent.gov.ru/ru/activities/dues/req';
$lireate_max = LIREATE_MAX_CHAT_URL;
include dirname(__DIR__) . '/_shared/blocks/lireate-patent-tool-footer.inc.php';
?>

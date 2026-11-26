<?php
header('Content-Type: text/html; charset=utf-8');
$path = '/patentnye-zadachi/starye-zayavki/';
$host = isset($_SERVER['HTTP_HOST']) ? preg_replace('/[^a-zA-Z0-9.\-:]/', '', $_SERVER['HTTP_HOST']) : '';
$site_root = ($host !== '' ? 'https://' . $host : '');
$canonical = $site_root . $path;
$seo_title = 'Старые заявки в АРМ «Регистратор» — LIREATE';
$seo_description = 'Переход в официальный личный кабинет и АРМ ФИПС для работы со своими заявками. LIREATE не принимает, не хранит и не передаёт учётные данные ФИПС.';
$lireate_telegram = 'https://t.me/lireate_patent';
require_once dirname(__DIR__) . '/_shared/lireate-service-messenger-include.inc.php';
require_once dirname(__DIR__) . '/_shared/lireate-service-assets.php';
require_once dirname(__DIR__) . '/_shared/lireate-patent-breadcrumbs.php';
$lx_service_asset_dir = __DIR__;
$lireate_metrika_id = getenv('LIREATE_YANDEX_METRIKA_ID');
if ($lireate_metrika_id === false || $lireate_metrika_id === '') {
    $lireate_metrika_id = '526' . '22734';
}
$lx_breadcrumb_current = 'Старые заявки';
$lx_patent_cta_href = $lireate_telegram;
$lx_patent_cta_external = true;
$lx_pay_profiles_json = lireate_service_asset_href('assets/lireate-shared/lireate-patent-payment-profiles.json', $lx_service_asset_dir);
$schema_graph = array(
  '@context' => 'https://schema.org',
  '@graph' => array(
    array('@type' => 'WebApplication', 'name' => 'Старые заявки', 'url' => $canonical),
    array('@type' => 'BreadcrumbList', 'itemListElement' => lireate_patent_breadcrumb_schema_items($site_root, 'Старые заявки', $canonical)),
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
  <link rel="stylesheet" href="/template/css/lireate-qa-v6.css?v=20261126d">
  <script defer src="/template/js/lireate-qa-v6.js?v=20261126d"></script>
</head>
<body class="lireate-svc-body lx-patent-tool lireate-theme-light-v4 lxq-patent-tasks">
<?php $lxq_sidebar = isset($_SERVER['DOCUMENT_ROOT']) ? rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/template/lireate/lireate-patent-tasks-sidebar-v6.inc.php' : ''; if ($lxq_sidebar !== '' && is_file($lxq_sidebar)) { include $lxq_sidebar; } ?>
<?php include __DIR__ . '/blocks/lireate-patent-header.inc.php'; ?>
<main id="primary" class="lireate-svc-page" role="main" tabindex="-1" data-lx-patent-old-apps data-lx-pay-profiles-json="<?php echo htmlspecialchars($lx_pay_profiles_json, ENT_QUOTES, 'UTF-8'); ?>">
<?php include __DIR__ . '/blocks/lireate-patent-breadcrumbs.inc.php'; ?>
  <section class="lx-hero lx-hero--stack">
    <div>
      <p class="lx-eyebrow">Патентные задачи · АРМ</p>
      <h1>Старые заявки</h1>
      <p class="lx-hero-lead">Доступ к документам заявки — только под вашей учётной записью на стороне ФИПС.</p>
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
?>
<script src="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-light-v4.js', $lx_service_asset_dir); ?>"></script><!-- lireate_light_v4 -->
<?php include __DIR__ . '/blocks/lireate-patent-tool-footer.inc.php'; ?>

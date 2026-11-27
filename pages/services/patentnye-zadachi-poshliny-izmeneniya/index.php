<?php
header('Content-Type: text/html; charset=utf-8');
$path = '/patentnye-zadachi/poshliny-izmeneniya/';
$host = isset($_SERVER['HTTP_HOST']) ? preg_replace('/[^a-zA-Z0-9.\-:]/', '', $_SERVER['HTTP_HOST']) : '';
$site_root = ($host !== '' ? 'https://' . $host : '');
$canonical = $site_root . $path;
$seo_title = 'Пошлины за изменения в реестре — LIREATE';
$seo_description = 'Калькулятор пошлин п. 2.16 и 2.17 за изменения в государственном реестре и свидетельстве на товарный знак: число знаков, бумажный дубликат, таблица Роспатента.';
$lireate_telegram = 'https://t.me/lireate_patent';
require_once dirname(__DIR__) . '/_shared/lireate-service-messenger-include.inc.php';
require_once dirname(__DIR__) . '/_shared/lireate-service-assets.php';
require_once dirname(__DIR__) . '/_shared/lireate-patent-breadcrumbs.php';
$lx_service_asset_dir = __DIR__;
$lireate_metrika_id = getenv('LIREATE_YANDEX_METRIKA_ID');
if ($lireate_metrika_id === false || $lireate_metrika_id === '') {
    $lireate_metrika_id = '526' . '22734';
}
$lx_breadcrumb_current = 'Пошлины за изменения';
$lx_patent_cta_href = $lireate_telegram;
$lx_patent_cta_external = true;
$schema_graph = array(
  '@context' => 'https://schema.org',
  '@graph' => array(
    array('@type' => 'WebApplication', 'name' => 'Пошлины за изменения', 'url' => $canonical),
    array('@type' => 'BreadcrumbList', 'itemListElement' => lireate_patent_breadcrumb_schema_items($site_root, 'Пошлины за изменения', $canonical)),
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
<script defer src="/template/js/lireate-qa-v6.js?v=20261126d"></script>
</head>
<body class="lireate-svc-body lx-patent-tool lireate-theme-light-v4 lxq-patent-tasks">
<?php
$lxq_sidebar = isset($_SERVER['DOCUMENT_ROOT']) ? rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/template/lireate/lireate-patent-tasks-sidebar-v6.inc.php' : '';
if ($lxq_sidebar !== '' && is_file($lxq_sidebar)) { include $lxq_sidebar; }
?>
<?php include __DIR__ . '/blocks/lireate-patent-header.inc.php'; ?>
<main id="primary" class="lireate-svc-page" role="main" tabindex="-1" data-lx-patent-changes>
<?php include __DIR__ . '/blocks/lireate-patent-breadcrumbs.inc.php'; ?>
  <section class="lx-hero lx-hero--stack">
    <div>
      <p class="lx-eyebrow">Патентные задачи · реестр</p>
      <h1>Пошлины за изменения</h1>
      <p class="lx-hero-lead">п. 2.16 — 3 000 ₽ за каждое изменение по каждому знаку; п. 2.17 — 2 000 ₽ + бумажный дубликат при необходимости. <a href="https://rospatent.gov.ru/ru/activities/dues/table" target="_blank" rel="noopener noreferrer">Таблица</a>.</p>
    </div>
  </section>
  <section class="lx-section lx-section--tool" id="tool">
    <div class="lx-tool-grid">
      <div>
        <div class="lx-field">
          <label for="lx-ch-marks">Число регистраций (знаков)</label>
          <input type="number" id="lx-ch-marks" data-lx-changes-marks min="1" step="1" value="1">
        </div>
        <div class="lx-field">
          <label for="lx-ch-216">Изменения по п. 2.16 (на один знак)</label>
          <input type="number" id="lx-ch-216" data-lx-changes-216 min="0" step="1" value="0">
        </div>
        <div class="lx-field">
          <label for="lx-ch-217">Исправления по п. 2.17 (на один знак)</label>
          <input type="number" id="lx-ch-217" data-lx-changes-217 min="0" step="1" value="0">
        </div>
        <p><label><input type="checkbox" data-lx-changes-paper> Бумажный дубликат по п. 2.17 (+3 000 ₽ за знак)</label></p>
        <fieldset class="lx-field">
          <legend class="lx-field-label">Сведения есть в выданном свидетельстве?</legend>
          <p><label><input type="radio" name="lx-cert-info" data-lx-changes-cert-yes value="yes" checked> Да</label></p>
          <p><label><input type="radio" name="lx-cert-info" data-lx-changes-cert-no value="no"> Нет / не уверен</label></p>
        </fieldset>
        <div class="lx-actions">
          <button type="button" class="lx-btn lx-btn-primary" data-lx-patent-calc>Рассчитать</button>
          <button type="button" class="lx-btn lx-btn-ghost" data-lx-patent-copy>Копировать</button>
          <button type="button" class="lx-btn lx-btn-ghost" data-lx-patent-print>Печать</button>
        </div>
        <p class="lx-warn" data-lx-patent-error hidden></p>
      </div>
      <aside class="lx-result-panel" data-lx-patent-result hidden>
        <p class="lx-field-label">Пошлины Роспатента</p>
        <p class="lx-sum-total" data-lx-patent-total>—</p>
        <div data-lx-patent-result-body></div>
        <p class="lx-hint"><a href="/findtz/">Проверка и сопровождение знака</a></p>
      </aside>
    </div>
  </section>
</main>
<script src="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-patent-fees-core.js', $lx_service_asset_dir); ?>"></script>
<script src="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-patent-tools.js', $lx_service_asset_dir); ?>"></script>
<script src="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-light-v4.js', $lx_service_asset_dir); ?>"></script><!-- lireate_light_v4 -->
<?php $lireate_max = LIREATE_MAX_CHAT_URL; include __DIR__ . '/blocks/lireate-patent-tool-footer.inc.php'; ?>
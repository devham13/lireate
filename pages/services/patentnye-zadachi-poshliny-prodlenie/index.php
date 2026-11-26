<?php
header('Content-Type: text/html; charset=utf-8');
$path = '/patentnye-zadachi/poshliny-prodlenie/';
$host = isset($_SERVER['HTTP_HOST']) ? preg_replace('/[^a-zA-Z0-9.\-:]/', '', $_SERVER['HTTP_HOST']) : '';
$site_root = ($host !== '' ? 'https://' . $host : '');
$canonical = $site_root . $path;
$seo_title = 'Калькулятор пошлин продления ТЗ — LIREATE';
$seo_description = 'Калькулятор продления ТЗ: п. 2.22 и 2.25 по классам и позициям МКТУ. Сверка с реестром и таблицей Роспатента. Расчёт только в браузере. LIREATE.';
$lireate_telegram = 'https://t.me/lireate_patent';
require_once dirname(__DIR__) . '/_shared/lireate-service-messenger-include.inc.php';
require_once dirname(__DIR__) . '/_shared/lireate-service-assets.php';
require_once dirname(__DIR__) . '/_shared/lireate-patent-breadcrumbs.php';
$lx_service_asset_dir = __DIR__;
$lireate_metrika_id = getenv('LIREATE_YANDEX_METRIKA_ID');
if ($lireate_metrika_id === false || $lireate_metrika_id === '') {
    $lireate_metrika_id = '526' . '22734';
}
$lx_breadcrumb_current = 'Пошлины продления';
$lx_patent_cta_href = $lireate_telegram;
$lx_patent_cta_external = true;
$schema_graph = array(
  '@context' => 'https://schema.org',
  '@graph' => array(
    array('@type' => 'WebApplication', 'name' => 'Калькулятор продления', 'url' => $canonical),
    array('@type' => 'BreadcrumbList', 'itemListElement' => lireate_patent_breadcrumb_schema_items($site_root, 'Пошлины продления', $canonical)),
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
  <link rel="stylesheet" href="/template/css/lireate-qa-v6.css?v=20261126b">
<script defer src="/template/js/lireate-qa-v6.js?v=20261126b"></script>
</head>
<body class="lireate-svc-body lx-patent-tool lireate-theme-light-v4 lxq-patent-tasks">
<?php
$lxq_sidebar = isset($_SERVER['DOCUMENT_ROOT']) ? rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/template/lireate/lireate-patent-tasks-sidebar-v6.inc.php' : '';
if ($lxq_sidebar !== '' && is_file($lxq_sidebar)) { include $lxq_sidebar; }
?>
<?php include __DIR__ . '/blocks/lireate-patent-header.inc.php'; ?>
<main id="primary" class="lireate-svc-page" role="main" tabindex="-1" data-lx-patent-renew>
<?php include __DIR__ . '/blocks/lireate-patent-breadcrumbs.inc.php'; ?>
  <section class="lx-hero lx-hero--stack">
    <div>
      <p class="lx-eyebrow">Патентные задачи · продление</p>
      <h1>Пошлины продления товарного знака</h1>
      <p class="lx-hero-lead">Сверьте перечень с <a href="https://new.fips.ru/registers-web/action?acName=clickRegister&amp;regName=RUPTO" target="_blank" rel="noopener noreferrer">реестром ФИПС</a>. Источник ставок: <a href="https://rospatent.gov.ru/ru/activities/dues/table" target="_blank" rel="noopener noreferrer">таблица Роспатента</a>.</p>
    </div>
  </section>
  <section class="lx-section lx-section--tool" id="tool">
    <div class="lx-tool-grid">
      <div>
        <div class="lx-field">
          <label for="lx-mktu-renew">Перечень из свидетельства (фрагмент 511)</label>
          <textarea id="lx-mktu-renew" data-lx-patent-mktu-in placeholder="25&#10;одежда; обувь"></textarea>
        </div>
        <p><label><input type="checkbox" data-lx-patent-line-mode> Позиция с новой строки</label></p>
        <div class="lx-field">
          <span class="lx-field-label">Уточните позиции по классам</span>
          <div class="lx-class-grid" data-lx-patent-class-table></div>
        </div>
        <p><label><input type="checkbox" data-lx-patent-extra-term> Дополнительный шестимесячный срок (п. 2.25)</label></p>
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
        <p class="lx-hint"><a href="/prodlenie_tovarnogo_znaka/">Продление с сопровождением LIREATE</a></p>
      </aside>
    </div>
  </section>
</main>
<script src="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-patent-fees-core.js', $lx_service_asset_dir); ?>"></script>
<script src="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-patent-mktu-core.js', $lx_service_asset_dir); ?>"></script>
<script src="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-patent-tools.js', $lx_service_asset_dir); ?>"></script>
<script src="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-light-v4.js', $lx_service_asset_dir); ?>"></script><!-- lireate_light_v4 -->
<?php $lireate_max = LIREATE_MAX_CHAT_URL; include __DIR__ . '/blocks/lireate-patent-tool-footer.inc.php'; ?>
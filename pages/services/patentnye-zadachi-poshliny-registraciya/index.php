<?php
header('Content-Type: text/html; charset=utf-8');
$path = '/patentnye-zadachi/poshliny-registraciya/';
$host = isset($_SERVER['HTTP_HOST']) ? preg_replace('/[^a-zA-Z0-9.\-:]/', '', $_SERVER['HTTP_HOST']) : '';
$site_root = ($host !== '' ? 'https://' . $host : '');
$canonical = $site_root . $path;
$seo_title = 'Калькулятор пошлин регистрации ТЗ — LIREATE';
$seo_description = 'Калькулятор пошлин Роспатента за регистрацию ТЗ: режим по числу классов или перечню МКТУ. Пункты 2.1, 2.4, 2.11 и бумажное свидетельство. LIREATE.';
$lireate_telegram = 'https://t.me/lireate_patent';
require_once dirname(__DIR__) . '/_shared/lireate-service-messenger-include.inc.php';
require_once dirname(__DIR__) . '/_shared/lireate-service-assets.php';
require_once dirname(__DIR__) . '/_shared/lireate-patent-breadcrumbs.php';
$lx_service_asset_dir = __DIR__;
$lireate_metrika_id = getenv('LIREATE_YANDEX_METRIKA_ID');
if ($lireate_metrika_id === false || $lireate_metrika_id === '') {
    $lireate_metrika_id = '526' . '22734';
}
$lx_breadcrumb_current = 'Пошлины регистрации';
$lx_patent_cta_href = $lireate_telegram;
$lx_patent_cta_external = true;
$schema_graph = array(
  '@context' => 'https://schema.org',
  '@graph' => array(
    array('@type' => 'WebApplication', 'name' => 'Калькулятор пошлин регистрации', 'url' => $canonical),
    array('@type' => 'BreadcrumbList', 'itemListElement' => lireate_patent_breadcrumb_schema_items($site_root, 'Пошлины регистрации', $canonical)),
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
<body class="lireate-svc-body lx-patent-tool lireate-theme-light-v4 lxq-patent-tasks">
<?php
$lxq_sidebar = isset($_SERVER['DOCUMENT_ROOT']) ? rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/template/lireate/lireate-patent-tasks-sidebar-v6.inc.php' : '';
if ($lxq_sidebar !== '' && is_file($lxq_sidebar)) { include $lxq_sidebar; }
?>
<?php include __DIR__ . '/blocks/lireate-patent-header.inc.php'; ?>
<main id="primary" class="lireate-svc-page" role="main" tabindex="-1" data-lx-patent-reg>
<?php include __DIR__ . '/blocks/lireate-patent-breadcrumbs.inc.php'; ?>
  <section class="lx-hero lx-hero--stack">
    <div>
      <p class="lx-eyebrow">Патентные задачи · регистрация</p>
      <h1>Пошлины регистрации товарного знака</h1>
      <p class="lx-hero-lead">Официальная таблица (публикация 03.10.2025): <a href="https://rospatent.gov.ru/ru/activities/dues/table" target="_blank" rel="noopener noreferrer">rospatent.gov.ru</a></p>
    </div>
  </section>
  <section class="lx-section lx-section--tool" id="tool">
    <div class="lx-seg" role="tablist" aria-label="Режим расчёта">
      <button type="button" data-lx-patent-mode="classes" aria-pressed="true">По числу классов</button>
      <button type="button" data-lx-patent-mode="list" aria-pressed="false">По перечню МКТУ</button>
    </div>
    <div class="lx-tool-grid">
      <div>
        <div data-lx-panel="classes">
          <div class="lx-field">
            <label for="lx-class-num">Число классов МКТУ (1–45)</label>
            <input id="lx-class-num" type="number" min="1" max="45" step="1" data-lx-patent-class-num placeholder="Например, 1">
            <p class="lx-hint">В быстром режиме в каждом классе до 10 наименований.</p>
          </div>
        </div>
        <div data-lx-panel="list" hidden>
          <div class="lx-field">
            <label for="lx-mktu-in">Перечень МКТУ</label>
            <textarea id="lx-mktu-in" data-lx-patent-mktu-in placeholder="01&#10;кофе; чай"></textarea>
          </div>
          <p><label><input type="checkbox" data-lx-patent-line-mode> Позиция с новой строки</label></p>
          <div class="lx-field">
            <span class="lx-field-label">Подтвердите число позиций по классам</span>
            <div class="lx-class-grid" data-lx-patent-class-table></div>
          </div>
        </div>
        <p><label><input type="checkbox" data-lx-patent-paper> Бумажное свидетельство (п. 2.14)</label></p>
        <div class="lx-actions">
          <button type="button" class="lx-btn lx-btn-primary" data-lx-patent-calc>Рассчитать</button>
          <button type="button" class="lx-btn lx-btn-ghost" data-lx-patent-clear>Очистить</button>
          <button type="button" class="lx-btn lx-btn-ghost" data-lx-patent-copy>Копировать</button>
          <button type="button" class="lx-btn lx-btn-ghost" data-lx-patent-print>Печать</button>
        </div>
        <p class="lx-warn" data-lx-patent-error hidden></p>
      </div>
      <aside class="lx-result-panel" data-lx-patent-result hidden>
        <p class="lx-field-label">Пошлины Роспатента</p>
        <p class="lx-sum-total" data-lx-patent-total>—</p>
        <div data-lx-patent-result-body></div>
        <p class="lx-hint">Нужно сопровождение — <a href="/registraciya_tovarnogo_znaka/">регистрация под ключ</a> или <a href="/patentnye-zadachi/redaktor-mktu/">редактор МКТУ</a>.</p>
      </aside>
    </div>
  </section>
</main>
<script src="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-patent-fees-core.js', $lx_service_asset_dir); ?>"></script>
<script src="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-patent-mktu-core.js', $lx_service_asset_dir); ?>"></script>
<script src="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-patent-tools.js', $lx_service_asset_dir); ?>"></script>
<script src="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-light-v4.js', $lx_service_asset_dir); ?>"></script><!-- lireate_light_v4 -->
<?php $lireate_max = LIREATE_MAX_CHAT_URL; include __DIR__ . '/blocks/lireate-patent-tool-footer.inc.php'; ?>
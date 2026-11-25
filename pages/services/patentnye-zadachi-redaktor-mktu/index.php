<?php
header('Content-Type: text/html; charset=utf-8');
$path = '/patentnye-zadachi/redaktor-mktu/';
$host = isset($_SERVER['HTTP_HOST']) ? preg_replace('/[^a-zA-Z0-9.\-:]/', '', $_SERVER['HTTP_HOST']) : '';
$site_root = ($host !== '' ? 'https://' . $host : '');
$canonical = $site_root . $path;
$seo_title = 'Редактор перечня МКТУ — LIREATE';
$seo_description = 'Нормализация перечня МКТУ для заявки: классы 01–45, разделители, предупреждения о дубликатах и передача подтверждённых классов в калькулятор LIREATE.';
$lireate_telegram = 'https://t.me/lireate_patent';
require_once dirname(__DIR__) . '/_shared/lireate-service-messenger-include.inc.php';
require_once dirname(__DIR__) . '/_shared/lireate-service-assets.php';
require_once dirname(__DIR__) . '/_shared/lireate-patent-breadcrumbs.php';
$lx_service_asset_dir = __DIR__;
$lireate_metrika_id = getenv('LIREATE_YANDEX_METRIKA_ID');
if ($lireate_metrika_id === false || $lireate_metrika_id === '') {
    $lireate_metrika_id = '526' . '22734';
}
$lx_breadcrumb_current = 'Редактор МКТУ';
$lx_patent_cta_href = $lireate_telegram;
$lx_patent_cta_external = true;
$schema_graph = array(
  '@context' => 'https://schema.org',
  '@graph' => array(
    array('@type' => 'WebApplication', 'name' => 'Редактор МКТУ', 'url' => $canonical),
    array('@type' => 'BreadcrumbList', 'itemListElement' => lireate_patent_breadcrumb_schema_items($site_root, 'Редактор МКТУ', $canonical)),
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
<?php include __DIR__ . '/blocks/lireate-patent-header.inc.php'; ?>
<main id="primary" class="lireate-svc-page" role="main" tabindex="-1" data-lx-patent-mktu>
<?php include __DIR__ . '/blocks/lireate-patent-breadcrumbs.inc.php'; ?>
  <section class="lx-hero lx-hero--stack">
    <div>
      <p class="lx-eyebrow">Патентные задачи · МКТУ</p>
      <h1>Редактор перечня МКТУ</h1>
      <p class="lx-hero-lead">Классификатор на сайте: <a href="/mktu">каталог МКТУ</a>. Обработка — только в браузере.</p>
    </div>
  </section>
  <section class="lx-section lx-section--tool" id="tool">
    <div class="lx-tool-grid">
      <div class="lx-field">
        <label for="lx-mktu-src">Исходный текст</label>
        <textarea id="lx-mktu-src" data-lx-mktu-source placeholder="09&#10;программы для ЭВМ; приложения"></textarea>
        <p><label><input type="checkbox" data-lx-patent-line-mode> Позиция с новой строки</label></p>
        <div class="lx-actions">
          <button type="button" class="lx-btn lx-btn-primary" data-lx-mktu-run>Разобрать</button>
          <button type="button" class="lx-btn lx-btn-ghost" data-lx-mktu-clear>Очистить</button>
          <a class="lx-btn lx-btn-ghost" data-lx-mktu-to-calc href="/patentnye-zadachi/poshliny-registraciya/">В калькулятор</a>
        </div>
        <div data-lx-mktu-warn></div>
      </div>
      <div class="lx-field">
        <label for="lx-mktu-out">Результат</label>
        <textarea id="lx-mktu-out" data-lx-mktu-result readonly></textarea>
        <p class="lx-hint">В калькулятор передаются только номера классов и количества позиций.</p>
      </div>
    </div>
  </section>
</main>
<script src="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-patent-mktu-core.js', $lx_service_asset_dir); ?>"></script>
<script src="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-patent-tools.js', $lx_service_asset_dir); ?>"></script>
<script src="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-light-v4.js', $lx_service_asset_dir); ?>"></script><!-- lireate_light_v4 -->
<?php $lireate_max = LIREATE_MAX_CHAT_URL; include __DIR__ . '/blocks/lireate-patent-tool-footer.inc.php'; ?>
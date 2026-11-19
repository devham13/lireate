<?php
/** @var string $lx_breadcrumb_current Название текущей страницы (крошка без ссылки) */
if (!isset($lx_breadcrumb_current) || $lx_breadcrumb_current === '') {
    return;
}
if (!function_exists('lireate_service_nav_links')) {
    $lx_services_root = dirname(dirname(__DIR__));
    $lx_nav_file = $lx_services_root . '/lireate-service-nav-links.php';
    if (!is_file($lx_nav_file)) {
        $lx_nav_file = $lx_services_root . '/_shared/lireate-service-nav-links.php';
    }
    if (is_file($lx_nav_file)) {
        require_once $lx_nav_file;
    }
}
if (!function_exists('lireate_service_nav_links')) {
    return;
}
$lx_service_nav_links = lireate_service_nav_links();
$lx_bc_menu_id = 'lx-breadcrumb-services-menu';
?>
<style id="lx-breadcrumb-critical">
.lireate-svc-page .lx-breadcrumb-services-menu[hidden],
.lireate-svc-page .lx-breadcrumb-services:not(.is-open) .lx-breadcrumb-services-menu{display:none!important}
.lireate-svc-page .lx-breadcrumb-services-menu,
.lireate-svc-page .lx-breadcrumb-services-menu li{list-style:none!important;margin:0;padding-left:0!important}
.lireate-svc-page .lx-breadcrumb-services-menu li::after{content:none!important}
.lireate-svc-page button.lx-breadcrumb-services-trigger{-webkit-appearance:none;appearance:none;border:1px solid rgba(125,211,252,.45);border-radius:8px;background:rgba(15,23,42,.35)}
</style>
<nav class="lx-breadcrumbs lx-breadcrumbs--in-page" aria-label="Хлебные крошки">
  <ol>
    <li><a href="/">Главная</a></li>
    <li class="lx-breadcrumb-services">
      <button
        type="button"
        class="lx-breadcrumb-services-trigger"
        id="lx-breadcrumb-services-trigger"
        aria-expanded="false"
        aria-haspopup="true"
        aria-controls="<?php echo htmlspecialchars($lx_bc_menu_id, ENT_QUOTES, 'UTF-8'); ?>"
      >Услуги</button>
      <ul
        class="lx-breadcrumb-services-menu"
        id="<?php echo htmlspecialchars($lx_bc_menu_id, ENT_QUOTES, 'UTF-8'); ?>"
        role="menu"
        aria-labelledby="lx-breadcrumb-services-trigger"
        hidden
      >
        <?php foreach ($lx_service_nav_links as $lx_nav_item) : ?>
        <li role="none">
          <a role="menuitem" href="<?php echo htmlspecialchars($lx_nav_item['href'], ENT_QUOTES, 'UTF-8'); ?>">
            <?php echo htmlspecialchars($lx_nav_item['label'], ENT_QUOTES, 'UTF-8'); ?>
          </a>
        </li>
        <?php endforeach; ?>
      </ul>
    </li>
    <li aria-current="page"><?php echo htmlspecialchars($lx_breadcrumb_current, ENT_QUOTES, 'UTF-8'); ?></li>
  </ol>
</nav>

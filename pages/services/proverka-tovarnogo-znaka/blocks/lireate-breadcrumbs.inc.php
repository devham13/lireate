<?php
/** @var string $lx_breadcrumb_current Название текущей страницы (крошка без ссылки) */
if (!isset($lx_breadcrumb_current) || $lx_breadcrumb_current === '') {
    return;
}
require_once dirname(__DIR__) . '/lireate-service-nav-links.php';
$lx_service_nav_links = lireate_service_nav_links();
$lx_bc_menu_id = 'lx-breadcrumb-services-menu';
?>
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

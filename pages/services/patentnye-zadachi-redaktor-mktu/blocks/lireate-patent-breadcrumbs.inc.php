<?php
/** @var string $lx_breadcrumb_current Текущий пункт (без ссылки) */
/** @var bool $lx_patent_breadcrumb_is_catalog true — без средней ссылки на каталог */
if (!isset($lx_breadcrumb_current) || $lx_breadcrumb_current === '') {
    return;
}
$lx_patent_is_catalog = !empty($lx_patent_breadcrumb_is_catalog);
?>
<nav class="lx-breadcrumbs lx-breadcrumbs--in-page lx-breadcrumbs--patent" aria-label="Хлебные крошки">
  <ol>
    <li><a href="/">Главная</a></li>
    <?php if ($lx_patent_is_catalog) : ?>
    <li aria-current="page"><?php echo htmlspecialchars($lx_breadcrumb_current, ENT_QUOTES, 'UTF-8'); ?></li>
    <?php else : ?>
    <li><a href="/patentnye-zadachi/">Патентные задачи</a></li>
    <li aria-current="page"><?php echo htmlspecialchars($lx_breadcrumb_current, ENT_QUOTES, 'UTF-8'); ?></li>
    <?php endif; ?>
  </ol>
</nav>
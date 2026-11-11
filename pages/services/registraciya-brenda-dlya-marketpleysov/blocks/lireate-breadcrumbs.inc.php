<?php
/** @var string $lx_breadcrumb_current Название текущей страницы (крошка без ссылки) */
if (!isset($lx_breadcrumb_current) || $lx_breadcrumb_current === '') {
    return;
}
?>
<nav class="lx-breadcrumbs lx-breadcrumbs--in-page" aria-label="Хлебные крошки">
  <ol>
    <li><a href="/">Главная</a></li>
    <li><a href="/uslugi/">Услуги</a></li>
    <li aria-current="page"><?php echo htmlspecialchars($lx_breadcrumb_current, ENT_QUOTES, 'UTF-8'); ?></li>
  </ol>
</nav>

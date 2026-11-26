<?php
/**
 * Шапка патентных инструментов — тот же lx-nav-pill, что на страницах услуг.
 * Опционально: $lx_patent_nav_links = array(array('href' => '#id', 'label' => '…'), …)
 */
if (!isset($lx_patent_nav_links) || !is_array($lx_patent_nav_links)) {
    $lx_patent_nav_links = array(
        array('href' => '/patentnye-zadachi/', 'label' => 'Каталог'),
        array('href' => '/patentnye-zadachi/poshliny-registraciya/', 'label' => 'Регистрация'),
        array('href' => '/patentnye-zadachi/poshliny-prodlenie/', 'label' => 'Продление'),
        array('href' => '/patentnye-zadachi/redaktor-mktu/', 'label' => 'МКТУ'),
    );
}
$lx_patent_cta_href = isset($lx_patent_cta_href) ? $lx_patent_cta_href : '#contact';
if ($lx_patent_cta_href === '#contact' && isset($lireate_telegram)) {
    $lx_patent_cta_href = $lireate_telegram;
    $lx_patent_cta_external = true;
}
$lx_patent_cta_external = !empty($lx_patent_cta_external);
?>
<div class="lx-bg-glow" aria-hidden="true"></div>
<div class="lx-nav-shell">
  <header class="lx-nav-pill" role="banner">
    <a class="lx-logo" href="/">LIREATE</a>
    <nav class="lx-nav-links" aria-label="Патентные задачи">
      <?php foreach ($lx_patent_nav_links as $lx_patent_nav_item) : ?>
      <a href="<?php echo htmlspecialchars($lx_patent_nav_item['href'], ENT_QUOTES, 'UTF-8'); ?>">
        <?php echo htmlspecialchars($lx_patent_nav_item['label'], ENT_QUOTES, 'UTF-8'); ?>
      </a>
      <?php endforeach; ?>
    </nav>
    <a class="lx-btn lx-btn-primary" href="<?php echo htmlspecialchars($lx_patent_cta_href, ENT_QUOTES, 'UTF-8'); ?>"<?php
    if ($lx_patent_cta_external) {
        echo ' target="_blank" rel="noopener noreferrer"';
    }
    ?>>Консультация</a>
  </header>
</div>
<div class="lx-patent-footer-shell">
<footer class="lx-patent-footer-bar" role="contentinfo" id="contact">
  <div class="lx-patent-footer-inner">
    <p class="lx-patent-footer-note">Расчёт ориентировочный; сверяйте с <a href="https://rospatent.gov.ru/ru/activities/dues/table" target="_blank" rel="noopener noreferrer">таблицей пошлин Роспатента</a>. Услуги бюро оплачиваются отдельно.</p>
    <div class="lx-patent-footer-actions">
      <a class="lx-btn lx-btn-ghost" href="/patentnye-zadachi/">К каталогу</a>
      <a class="lx-btn lx-btn-primary" href="<?php echo htmlspecialchars($lireate_telegram, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer">Telegram</a>
    </div>
  </div>
</footer>
</div>

<script src="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-reveal.js', $lx_service_asset_dir); ?>"></script>
<?php
$lx_patent_metrika = dirname(dirname(__DIR__)) . '/_shared/lireate-metrika.inc.php';
if (!is_file($lx_patent_metrika)) {
    $lx_patent_metrika = dirname(__DIR__) . '/lireate-metrika.inc.php';
}
if (is_file($lx_patent_metrika)) {
    include $lx_patent_metrika;
}
?>
<script src="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-metrika-goals.js', $lx_service_asset_dir); ?>"></script>
</body>
</html>
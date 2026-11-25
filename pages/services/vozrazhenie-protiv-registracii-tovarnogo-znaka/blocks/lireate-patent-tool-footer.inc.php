<div class="lx-patent-footer-shell">
<footer class="lx-patent-footer-bar" role="contentinfo" id="contact">
  <div class="lx-patent-footer-inner">
    <?php
    $lx_patent_footer_mode = isset($lx_patent_footer_mode) ? $lx_patent_footer_mode : 'calc';
    if ($lx_patent_footer_mode === 'reference') {
        $lx_patent_footer_ref = isset($lx_patent_footer_ref_url) ? $lx_patent_footer_ref_url : 'https://rospatent.gov.ru/ru/contacts';
        $lx_patent_footer_ref = htmlspecialchars($lx_patent_footer_ref, ENT_QUOTES, 'UTF-8');
        $lx_patent_footer_note_html = 'Сведения справочные; сверяйте с <a href="' . $lx_patent_footer_ref . '" target="_blank" rel="noopener noreferrer">официальным источником</a>. Услуги бюро оплачиваются отдельно.';
    } else {
        $lx_patent_footer_note_html = 'Расчёт ориентировочный; сверяйте с <a href="https://rospatent.gov.ru/ru/activities/dues/table" target="_blank" rel="noopener noreferrer">таблицей пошлин Роспатента</a>. Услуги бюро оплачиваются отдельно.';
    }
    ?>
    <p class="lx-patent-footer-note"><?php echo $lx_patent_footer_note_html; ?></p>
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
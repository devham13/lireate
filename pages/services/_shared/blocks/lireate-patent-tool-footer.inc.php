<footer class="lx-footer lx-patent-footer" role="contentinfo">
  <p class="lx-hint">Расчёт ориентировочный; сверяйте с <a href="https://rospatent.gov.ru/ru/activities/dues/table" target="_blank" rel="noopener noreferrer">таблицей пошлин Роспатента</a>. Услуги бюро оплачиваются отдельно.</p>
  <div class="lx-actions">
    <a class="lx-btn lx-btn-ghost" href="/patentnye-zadachi/">К каталогу</a>
    <a class="lx-btn lx-btn-primary" href="<?php echo htmlspecialchars($lireate_telegram, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer">Telegram</a>
  </div>
</footer>

<script src="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-reveal.js', $lx_service_asset_dir); ?>"></script>
<?php include dirname(__DIR__) . '/../lireate-metrika.inc.php'; ?>
<script src="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-metrika-goals.js', $lx_service_asset_dir); ?>"></script>
</body>
</html>
<?php
/**
 * Разметка страницы «Документы» (/docs/) — тёмный стиль как /news.
 */
require_once __DIR__ . '/lireate-docs-lib.php';

$ld_assets = LIREATE_DOCS_ASSETS_BASE;
$ld_v = LIREATE_DOCS_TEMPLATE_VERSION;
$ld_docs = lireate_docs_documents();
$ld_req = lireate_docs_requisites();
$ld_footer_css = defined('LIREATE_DOCS_FOOTER_CSS_HREF')
    ? LIREATE_DOCS_FOOTER_CSS_HREF
    : '/template/page/news/lireate-blog-footer.css';
?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400;600;700&family=Unbounded:wght@700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?php echo lireate_docs_h($ld_assets); ?>/lireate-docs.css?v=<?php echo lireate_docs_h($ld_v); ?>">
<link rel="stylesheet" href="<?php echo lireate_docs_h($ld_footer_css); ?>?v=<?php echo lireate_docs_h($ld_v); ?>">
<script>
document.documentElement.classList.add('lireate-docs-html');
document.documentElement.setAttribute('data-lireate-metrika-id', '<?php echo lireate_docs_h(LIREATE_DOCS_METRIKA_ID); ?>');
</script>
<div class="lireate-docs-page" data-lireate-docs-version="<?php echo lireate_docs_h($ld_v); ?>">
  <header class="lireate-docs-hero">
    <div class="lireate-docs-wrap">
      <p class="lireate-docs-kicker">LIREATE</p>
      <h1 class="lireate-docs-h1">Документы Лирейт</h1>
      <p class="lireate-docs-lead">Официальные документы компании и актуальные реквизиты для договоров и оплаты.</p>
    </div>
  </header>

  <section class="lireate-docs-wrap lireate-docs-section" aria-labelledby="ld-docs-title">
    <div class="lireate-docs-section-head">
      <span class="lireate-docs-badge">Файлы</span>
      <h2 class="lireate-docs-h2" id="ld-docs-title">Документы компании</h2>
    </div>
    <div class="lireate-docs-grid">
      <?php foreach ($ld_docs as $doc) : ?>
      <article class="lireate-docs-card">
        <h3 class="lireate-docs-card-title">
          <a href="<?php echo lireate_docs_h($doc['href']); ?>" target="_blank" rel="noopener noreferrer" data-lx-ym-goal="docs_open"><?php echo lireate_docs_h($doc['title']); ?></a>
        </h3>
        <?php if (!empty($doc['size'])) : ?>
        <p class="lireate-docs-size"><?php echo lireate_docs_h($doc['size']); ?></p>
        <?php endif; ?>
        <div class="lireate-docs-actions">
          <a class="lireate-docs-btn lireate-docs-btn--solid" href="<?php echo lireate_docs_h($doc['href']); ?>" target="_blank" rel="noopener noreferrer" data-lx-ym-goal="docs_open">открыть</a>
          <a class="lireate-docs-btn lireate-docs-btn--outline" href="<?php echo lireate_docs_h($doc['href']); ?>" download data-lx-ym-goal="docs_download">скачать</a>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="lireate-docs-wrap lireate-docs-section" aria-labelledby="ld-req-title">
    <div class="lireate-docs-section-head">
      <span class="lireate-docs-badge lireate-docs-badge--green">Реквизиты</span>
      <h2 class="lireate-docs-h2" id="ld-req-title">Реквизиты компании</h2>
    </div>
    <div class="lireate-docs-req-card">
      <dl class="lireate-docs-dl">
        <?php foreach ($ld_req as $row) : ?>
        <div class="lireate-docs-dl-row">
          <dt><?php echo lireate_docs_h($row['label']); ?></dt>
          <dd><?php echo lireate_docs_h($row['value']); ?></dd>
        </div>
        <?php endforeach; ?>
      </dl>
    </div>
  </section>
</div>
<script src="<?php echo lireate_docs_h($ld_assets); ?>/lireate-docs.js?v=<?php echo lireate_docs_h($ld_v); ?>"></script>
<script src="<?php echo lireate_docs_h($ld_assets); ?>/lireate-metrika-goals.js?v=<?php echo lireate_docs_h($ld_v); ?>"></script>

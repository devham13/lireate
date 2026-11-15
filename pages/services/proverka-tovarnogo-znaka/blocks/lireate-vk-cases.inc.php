<?php
/**
 * Блок «Наши кейсы» (посты VK). Данные: pages/services/_shared/vk-cases.json → docroot/_shared/ при публикации.
 */
$lx_vk_cases = array();
$lx_vk_json_path = '';
$candidates = array(
    dirname(__DIR__) . '/../_shared/vk-cases.json',
    dirname(dirname(__DIR__)) . '/_shared/vk-cases.json',
);
foreach ($candidates as $path) {
    if (is_file($path)) {
        $lx_vk_json_path = $path;
        break;
    }
}
if ($lx_vk_json_path !== '') {
    $raw = file_get_contents($lx_vk_json_path);
    if ($raw !== false) {
        $decoded = json_decode($raw, true);
        if (is_array($decoded) && !empty($decoded['cases']) && is_array($decoded['cases'])) {
            $lx_vk_cases = $decoded['cases'];
        }
    }
}
?>
<link rel="stylesheet" href="/_shared/lireate-vk-cases.css?v=2026-11-10-cases-v3">
<section class="lx-section lx-reveal lireate-vk-cases" id="cases" aria-labelledby="lireate-vk-cases-title">
  <div class="lireate-vk-cases-head">
    <span class="lx-section-label">Практика</span>
    <h2 id="lireate-vk-cases-title">Наши кейсы</h2>
    <p class="lireate-vk-cases-lead">Реальные ситуации из группы LIREATE ВКонтакте — без выдуманных цифр и имён.</p>
  </div>
<?php if (!empty($lx_vk_cases)) : ?>
  <div class="lireate-vk-cases-grid">
<?php foreach ($lx_vk_cases as $lx_case) :
    if (empty($lx_case['title'])) {
        continue;
    }
    $lx_img = isset($lx_case['image']) ? $lx_case['image'] : '';
    $lx_dt = isset($lx_case['datetime']) ? $lx_case['datetime'] : '';
    $lx_date_label = isset($lx_case['date_label']) ? $lx_case['date_label'] : $lx_dt;
    $lx_title = $lx_case['title'];
    $lx_text = isset($lx_case['text']) ? $lx_case['text'] : '';
    $lx_url = isset($lx_case['url']) ? $lx_case['url'] : '';
    ?>
    <article class="lireate-vk-case-card">
      <div class="lireate-vk-case-media" data-lireate-case-media="img">
        <img class="lireate-vk-case-img" src="<?php echo htmlspecialchars($lx_img, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($lx_title, ENT_QUOTES, 'UTF-8'); ?>" width="400" height="225" loading="lazy" decoding="async" onerror="lireateVkCaseImgError(this)">
      </div>
      <div class="lireate-vk-case-body">
<?php if ($lx_dt !== '') : ?>
        <time class="lireate-vk-case-date" datetime="<?php echo htmlspecialchars($lx_dt, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($lx_date_label, ENT_QUOTES, 'UTF-8'); ?></time>
<?php endif; ?>
        <h3 class="lireate-vk-case-title"><?php echo htmlspecialchars($lx_title, ENT_QUOTES, 'UTF-8'); ?></h3>
<?php if ($lx_text !== '') : ?>
        <p class="lireate-vk-case-text"><?php echo htmlspecialchars($lx_text, ENT_QUOTES, 'UTF-8'); ?></p>
<?php endif; ?>
<?php if ($lx_url !== '') : ?>
        <a class="lireate-vk-case-link" href="<?php echo htmlspecialchars($lx_url, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer">Подробнее в ВК</a>
<?php endif; ?>
      </div>
    </article>
<?php endforeach; ?>
  </div>
<?php endif; ?>
  <div class="lireate-vk-cases-footer">
    <a class="lireate-vk-cases-all lireate-vk-cases-all--lx" href="https://vk.com/lireate" target="_blank" rel="noopener noreferrer">Все кейсы</a>
  </div>
</section>
<script>
function lireateVkCaseImgError(img) {
  if (!img || !img.parentElement) {
    return;
  }
  var card = img.closest('.lireate-vk-case-card');
  var topic = '';
  if (card) {
    var h = card.querySelector('.lireate-vk-case-title');
    if (h) {
      topic = h.textContent || '';
    }
  }
  var wrap = document.createElement('div');
  wrap.className = 'lireate-vk-case-media lireate-vk-case-media--placeholder';
  wrap.setAttribute('data-lireate-case-media', 'placeholder');
  var safe = topic.replace(/</g, '') || 'Кейс LIREATE';
  wrap.innerHTML = '<div class="lireate-vk-case-ph-inner" aria-hidden="true"><span class="lireate-vk-case-ph-icon"></span><span class="lireate-vk-case-ph-badge">Кейс</span></div><p class="lireate-vk-case-ph-topic">' + safe + '</p>';
  img.parentElement.replaceWith(wrap);
}
</script>

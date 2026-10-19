<?php
$lx_sticky_tel = isset($lireate_phone_tel) ? $lireate_phone_tel : 'tel:+79600484957';
$lx_sticky_tg = isset($lireate_telegram) ? $lireate_telegram : 'https://t.me/lireate_patent';
require_once dirname(__DIR__) . '/lireate-messenger-urls.php';
$lx_sticky_max = isset($lireate_max) ? $lireate_max : LIREATE_MAX_CHAT_URL;
?>
<aside class="lx-mobile-bar" aria-label="Быстрый звонок и мессенджеры">
  <a class="lx-mobile-bar-btn lx-mobile-bar-btn--call" href="<?php echo htmlspecialchars($lx_sticky_tel, ENT_QUOTES, 'UTF-8'); ?>">Позвонить</a>
  <a class="lx-mobile-bar-btn" href="<?php echo htmlspecialchars($lx_sticky_tg, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" data-lx-icon="telegram" aria-label="Telegram"></a>
  <a class="lx-mobile-bar-btn lx-mobile-bar-btn--max" href="<?php echo htmlspecialchars($lx_sticky_max, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" data-lx-icon="max-white" aria-label="MAX"></a>
  <a class="lx-mobile-bar-btn lx-mobile-bar-btn--primary" href="#contact">Заявка</a>
</aside>

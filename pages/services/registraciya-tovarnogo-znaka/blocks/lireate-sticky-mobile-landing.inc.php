<?php
/** Липкая панель лендинга регистрации: «Рассчитать» / «Написать» (только этот slug). */
$lx_lm_tg = isset($lireate_telegram) ? $lireate_telegram : 'https://t.me/lireate_patent';
$lx_lm_hint = 'Здравствуйте! Хочу зарегистрировать название ___. Мои товары или услуги — ___. Подскажите стоимость';
$lx_lm_body = rawurlencode($lx_lm_hint);
$lx_lm_tg_href = (strpos($lx_lm_tg, '?') === false) ? ($lx_lm_tg . '?text=' . $lx_lm_body) : ($lx_lm_tg . '&text=' . $lx_lm_body);
?>
<aside class="lx-mobile-bar lx-mobile-bar--landing-tz" aria-label="Быстрый расчёт и мессенджер">
  <a class="lx-mobile-bar-btn lx-mobile-bar-btn--primary" href="#contact" data-lx-tz-form-open="mobile_bar">Рассчитать</a>
  <a class="lx-mobile-bar-btn lx-mobile-bar-btn--write" href="<?php echo htmlspecialchars($lx_lm_tg_href, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" data-lx-tz-messenger="telegram" data-lx-tz-placement="mobile_bar" data-lx-ym-goal="perehod_v_telegramm">Написать</a>
</aside>

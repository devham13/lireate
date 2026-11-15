<?php
/**
 * Блок #contact: мессенджеры + заявка → /mail.php → amoCRM.
 *
 * Перед include задайте:
 *   $lx_lead_topic — data-lead-topic;
 *   $lx_lead_tomail — скрытое tomail;
 *   $lx_lead_consent_form — consent_form (уникальный id страницы).
 * Опционально: $lx_lead_submit_label, $lx_lead_success_text, $lx_lead_name_required (default true).
 *
 * @var string $lireate_phone_tel $lireate_phone_display $lireate_telegram $lireate_whatsapp $lireate_max
 */
if (empty($lx_lead_topic)) {
    $lx_lead_topic = 'Заявка LIREATE';
}
if (empty($lx_lead_tomail)) {
    $lx_lead_tomail = $lx_lead_topic;
}
if (empty($lx_lead_consent_form)) {
    $lx_lead_consent_form = 'lx-service-lead';
}
if (empty($lx_lead_submit_label)) {
    $lx_lead_submit_label = 'Получить консультацию';
}
if (empty($lx_lead_success_text)) {
    $lx_lead_success_text = 'Заявка принята. Мы свяжемся с вами в ближайшее время.';
}
$lx_lead_name_required = !isset($lx_lead_name_required) || $lx_lead_name_required;
$lx_spam_guard = md5(date('m.Y'));
?>
<section class="lx-section lx-reveal" id="contact">
  <div class="lx-contact">
    <div>
      <span class="lx-section-label">Контакт</span>
      <h2>Заявка и мессенджеры</h2>
      <p>Телефон: <a href="<?php echo htmlspecialchars($lireate_phone_tel, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($lireate_phone_display, ENT_QUOTES, 'UTF-8'); ?></a></p>
      <div class="lx-msg-grid">
        <a class="lx-msg-btn" id="link-telegram" href="<?php echo htmlspecialchars($lireate_telegram, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" data-lx-icon="telegram" data-lx-ym-goal="perehod_v_telegramm"><span>Telegram</span></a>
        <a class="lx-msg-btn" id="link-whatsapp" href="<?php echo htmlspecialchars($lireate_whatsapp, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" data-lx-icon="whatsapp" data-lx-ym-goal="perehod_v_messendzher"><span>WhatsApp</span></a>
        <a class="lx-msg-btn lx-msg-btn--max" id="link-max" href="<?php echo htmlspecialchars($lireate_max, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" data-lx-icon="max" data-lx-ym-goal="perehod_v_messendzher"><span>MAX</span></a>
      </div>
    </div>
    <div class="lx-form-panel">
      <form
        id="lireate-lead-form"
        class="lx-mail-lead-form"
        method="POST"
        action="/mail.php"
        data-lx-mail-action="/mail.php"
        data-lead-topic="<?php echo htmlspecialchars($lx_lead_topic, ENT_QUOTES, 'UTF-8'); ?>"
        data-lx-success-text="<?php echo htmlspecialchars($lx_lead_success_text, ENT_QUOTES, 'UTF-8'); ?>"
        novalidate
      >
        <input type="hidden" name="mail_token" value="503066424d5f7b5ad2097703ef3404d2">
        <input type="hidden" name="fuck_spamers" value="<?php echo htmlspecialchars($lx_spam_guard, ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="mail" value="mailorder">
        <input type="hidden" name="form-type" value="order">
        <input type="hidden" name="tomail" value="<?php echo htmlspecialchars($lx_lead_tomail, ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="consent_form" value="<?php echo htmlspecialchars($lx_lead_consent_form, ENT_QUOTES, 'UTF-8'); ?>">
        <label for="lf-name">Имя<?php if (!$lx_lead_name_required) : ?> <span class="lx-opt">(необязательно)</span><?php endif; ?></label>
        <input id="lf-name" name="name" type="text"<?php echo $lx_lead_name_required ? ' required' : ''; ?> autocomplete="name" placeholder="Как к вам обращаться">
        <label for="lf-phone">Телефон</label>
        <input id="lf-phone" name="tel" type="tel" required autocomplete="tel" placeholder="+7 …" inputmode="tel">
        <label class="lx-consent"><input type="checkbox" id="lf-consent" required> Согласен на <a href="/private_data" target="_blank" rel="noopener noreferrer">обработку персональных данных</a> и с <a href="/politika_konfidencialnosti" target="_blank" rel="noopener noreferrer">политикой конфиденциальности</a>.</label>
        <button type="submit" class="lx-btn lx-btn-primary lx-form-submit" style="width:100%;margin-top:12px;"><?php echo htmlspecialchars($lx_lead_submit_label, ENT_QUOTES, 'UTF-8'); ?></button>
        <div class="lx-form-alt">
          <span class="lx-form-alt-label">или напишите нам</span>
          <div class="lx-form-alt-icons">
            <a id="form-alt-telegram" class="lx-form-alt-link" href="<?php echo htmlspecialchars($lireate_telegram, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" data-lx-icon="telegram" data-lx-ym-goal="perehod_v_telegramm" aria-label="Telegram"></a>
            <a id="form-alt-whatsapp" class="lx-form-alt-link" href="<?php echo htmlspecialchars($lireate_whatsapp, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" data-lx-icon="whatsapp" data-lx-ym-goal="perehod_v_messendzher" aria-label="WhatsApp"></a>
            <a id="form-alt-max" class="lx-form-alt-link lx-form-alt-link--max" href="<?php echo htmlspecialchars($lireate_max, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" data-lx-icon="max-white" data-lx-ym-goal="perehod_v_messendzher" aria-label="MAX"></a>
          </div>
        </div>
        <p class="lx-form-success" id="lf-success" role="status" aria-live="polite"></p>
        <p class="lx-form-error" id="lf-error" role="alert" hidden></p>
      </form>
    </div>
  </div>
</section>

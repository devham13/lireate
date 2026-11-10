<?php
/** @var string $lireate_phone_tel $lireate_phone_display $lireate_telegram $lireate_whatsapp $lireate_max */
$lx_spam_guard = md5(date('m.Y'));
$lx_messenger_hint = 'Здравствуйте! Хочу зарегистрировать название ___. Мои товары или услуги — ___. Подскажите стоимость';
$lx_messenger_body = rawurlencode($lx_messenger_hint);
$lx_tg_prefill = (strpos($lireate_telegram, '?') === false) ? ($lireate_telegram . '?text=' . $lx_messenger_body) : ($lireate_telegram . '&text=' . $lx_messenger_body);
?>
<section class="lx-section lx-reveal lx-contact lx-contact--landing" id="contact" tabindex="-1">
  <div class="lx-contact lx-contact--landing-inner">
    <div class="lx-form-panel lx-form-panel--primary">
      <h2 class="lx-form-title" id="lf-title">Получить расчёт регистрации</h2>
      <p class="lx-form-lead">Оставьте телефон. Специалист уточнит название и перечень товаров или услуг для расчёта</p>
      <form
        id="lireate-lead-form"
        class="lx-lead-form--landing lx-mail-lead-form"
        data-lx-lead-handler="landing"
        data-lx-mail-action="/mail.php"
        data-lead-topic="Заявка: расчёт регистрации товарного знака"
        data-lx-success-text="Заявка принята. Специалист свяжется с вами, чтобы уточнить детали для расчёта"
        novalidate
      >
        <input type="hidden" name="mail_token" value="503066424d5f7b5ad2097703ef3404d2">
        <input type="hidden" name="fuck_spamers" value="<?php echo htmlspecialchars($lx_spam_guard, ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="mail" value="mailorder">
        <input type="hidden" name="form-type" value="order">
        <input type="hidden" name="tomail" value="Расчёт регистрации товарного знака (лендинг услуги)">
        <input type="hidden" name="consent_form" value="tz-landing-registraciya">
        <label for="lf-phone">Телефон <span class="lx-req" aria-hidden="true">*</span></label>
        <input id="lf-phone" name="tel" type="tel" required autocomplete="tel" placeholder="+7 …" inputmode="tel">
        <label for="lf-name">Имя <span class="lx-opt">(необязательно)</span></label>
        <input id="lf-name" name="name" type="text" autocomplete="name" placeholder="Как к вам обращаться">
        <label class="lx-consent"><input type="checkbox" id="lf-consent" required> Согласен на <a href="/private_data" target="_blank" rel="noopener noreferrer">обработку персональных данных</a> и с <a href="/politika_konfidencialnosti" target="_blank" rel="noopener noreferrer">политикой конфиденциальности</a>.</label>
        <button type="submit" class="lx-btn lx-btn-primary lx-form-submit" style="width:100%;margin-top:12px;">Получить расчёт</button>
        <p class="lx-form-success" id="lf-success" role="status" aria-live="polite"></p>
        <p class="lx-form-error" id="lf-error" role="alert" hidden></p>
        <div class="lx-form-alt">
          <span class="lx-form-alt-label">или напишите нам</span>
          <div class="lx-form-alt-icons">
            <a id="form-alt-telegram" class="lx-form-alt-link" href="<?php echo htmlspecialchars($lx_tg_prefill, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" data-lx-icon="telegram" data-lx-tz-messenger="telegram" data-lx-tz-placement="form_alt" data-lx-ym-goal="perehod_v_telegramm" aria-label="Telegram"></a>
            <a id="form-alt-max" class="lx-form-alt-link lx-form-alt-link--max" href="<?php echo htmlspecialchars($lireate_max, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" data-lx-icon="max-white" data-lx-tz-messenger="max" data-lx-tz-placement="form_alt" data-lx-ym-goal="perehod_v_messendzher" aria-label="MAX"></a>
          </div>
        </div>
      </form>
    </div>
    <div class="lx-contact-side">
      <p class="lx-contact-side-label">или напишите</p>
      <div class="lx-msg-grid">
        <a class="lx-msg-btn" id="link-telegram" data-lx-tz-messenger="telegram" data-lx-tz-placement="contact" data-lx-ym-goal="perehod_v_telegramm" href="<?php echo htmlspecialchars($lx_tg_prefill, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" data-lx-icon="telegram"><span>Telegram</span></a>
        <a class="lx-msg-btn" id="link-whatsapp" data-lx-tz-messenger="whatsapp" data-lx-tz-placement="contact" data-lx-ym-goal="perehod_v_messendzher" href="<?php echo htmlspecialchars($lireate_whatsapp, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" data-lx-icon="whatsapp"><span>WhatsApp</span></a>
        <a class="lx-msg-btn lx-msg-btn--max" id="link-max" data-lx-tz-messenger="max" data-lx-tz-placement="contact" data-lx-ym-goal="perehod_v_messendzher" href="<?php echo htmlspecialchars($lireate_max, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" data-lx-icon="max"><span>MAX</span></a>
      </div>
      <p class="lx-messenger-hint">Подсказка для чата: «<?php echo htmlspecialchars($lx_messenger_hint, ENT_QUOTES, 'UTF-8'); ?>»</p>
      <p>Телефон: <a href="<?php echo htmlspecialchars($lireate_phone_tel, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($lireate_phone_display, ENT_QUOTES, 'UTF-8'); ?></a></p>
    </div>
  </div>
</section>

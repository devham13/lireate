<?php
/**
 * Стандартные галочки согласия (ПДн + реклама) для форм /mail.php.
 * Перед include: $lx_consent_pd_id, $lx_consent_mkt_id (уникальные id).
 */
if (empty($lx_consent_pd_id)) {
    $lx_consent_pd_id = 'lx-consent-pd';
}
if (empty($lx_consent_mkt_id)) {
    $lx_consent_mkt_id = 'lx-consent-mkt';
}
?>
<label class="lx-consent" for="<?php echo htmlspecialchars($lx_consent_pd_id, ENT_QUOTES, 'UTF-8'); ?>"><input type="checkbox" id="<?php echo htmlspecialchars($lx_consent_pd_id, ENT_QUOTES, 'UTF-8'); ?>" name="consent_request" value="1" required><span class="lx-consent-text">Согласен на <a href="/private_data" target="_blank" rel="noopener noreferrer">обработку персональных данных</a> и с <a href="/politika_konfidencialnosti" target="_blank" rel="noopener noreferrer">политикой конфиденциальности</a>.</span></label>
<label class="lx-consent lx-consent--marketing" for="<?php echo htmlspecialchars($lx_consent_mkt_id, ENT_QUOTES, 'UTF-8'); ?>"><input type="checkbox" class="lireate-consent-marketing-combined" id="<?php echo htmlspecialchars($lx_consent_mkt_id, ENT_QUOTES, 'UTF-8'); ?>" value="1"><span class="lx-consent-text">Я согласен(-на) на <a href="/marketing-data-consent.html" target="_blank" rel="noopener noreferrer">обработку контактных данных для рекламы</a> и <a href="/advertising-consent.html" target="_blank" rel="noopener noreferrer">получение рекламы ЛИРЕЙТ</a> по телефону и, если указан, email.</span></label>
<input type="hidden" name="consent_marketing_data" value="" disabled="disabled">
<input type="hidden" name="consent_ads" value="" disabled="disabled">
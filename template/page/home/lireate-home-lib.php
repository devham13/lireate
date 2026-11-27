<?php
/**
 * Хелперы главной LIREATE Light V4.
 */
require_once __DIR__ . '/lireate-home-config.php';
require_once __DIR__ . '/lireate-home-data.php';

if (!function_exists('lireate_home_h')) {
function lireate_home_h($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
}

if (!function_exists('lireate_home_consent_fields_inc_path')) {
function lireate_home_consent_fields_inc_path()
{
    $doc = isset($_SERVER['DOCUMENT_ROOT']) ? rtrim((string) $_SERVER['DOCUMENT_ROOT'], '/') : '';
  $candidates = array();
    if ($doc !== '') {
        $candidates[] = $doc . '/pages/services/_shared/lireate-lead-consent-fields.inc.php';
    }
    $candidates[] = dirname(dirname(dirname(__DIR__))) . '/pages/services/_shared/lireate-lead-consent-fields.inc.php';
    foreach ($candidates as $path) {
        if ($path !== '' && is_file($path)) {
            return $path;
        }
    }
    return '';
}
}

if (!function_exists('lireate_home_render_consent_fields')) {
function lireate_home_render_consent_fields($pd_id, $mkt_id)
{
    if (empty($pd_id)) {
        $pd_id = 'lh4-consent';
    }
    if (empty($mkt_id)) {
        $mkt_id = 'lh4-consent-mkt';
    }
    $pd = lireate_home_h($pd_id);
    $mkt = lireate_home_h($mkt_id);
    echo '<label class="lx-consent" for="' . $pd . '"><input type="checkbox" id="' . $pd . '" name="consent_request" value="1" required><span class="lx-consent-text">Согласен на <a href="/private_data" target="_blank" rel="noopener noreferrer">обработку персональных данных</a> и с <a href="/politika_konfidencialnosti" target="_blank" rel="noopener noreferrer">политикой конфиденциальности</a>.</span></label>';
    echo '<label class="lx-consent lx-consent--marketing" for="' . $mkt . '"><input type="checkbox" class="lireate-consent-marketing-combined" id="' . $mkt . '" value="1"><span class="lx-consent-text">Я согласен(-на) на <a href="/marketing-data-consent.html" target="_blank" rel="noopener noreferrer">обработку контактных данных для рекламы</a> и <a href="/advertising-consent.html" target="_blank" rel="noopener noreferrer">получение рекламы ЛИРЕЙТ</a> по телефону и, если указан, email.</span></label>';
    echo '<input type="hidden" name="consent_marketing_data" value="" disabled="disabled">';
    echo '<input type="hidden" name="consent_ads" value="" disabled="disabled">';
}
}
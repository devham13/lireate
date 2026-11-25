<?php
/**
 * Путь к lireate-lead-consent-fields.inc.php: опубликованная услуга (assets/lireate-shared) и репозиторий.
 * PHP 5.6.
 */
if (!function_exists('lireate_lead_consent_fields_path')) {
    function lireate_lead_consent_fields_path($blocks_dir)
    {
        $blocks_dir = (string) $blocks_dir;
        $service_root = dirname($blocks_dir);
        $candidates = array(
            $service_root . '/assets/lireate-shared/lireate-lead-consent-fields.inc.php',
            dirname(dirname($blocks_dir)) . '/_shared/lireate-lead-consent-fields.inc.php',
        );
        if (isset($_SERVER['DOCUMENT_ROOT']) && $_SERVER['DOCUMENT_ROOT'] !== '') {
            $doc = rtrim((string) $_SERVER['DOCUMENT_ROOT'], '/\\');
            $candidates[] = $doc . '/pages/services/_shared/lireate-lead-consent-fields.inc.php';
        }
        foreach ($candidates as $path) {
            if (is_file($path)) {
                return $path;
            }
        }
        return '';
    }
}
<?php
/**
 * Версионирование статики страниц услуг (cache-busting по mtime), PHP 5.6.
 */
if (!function_exists('lireate_service_asset_query')) {
    /**
     * @param string $relative_path путь от каталога услуги, напр. assets/lireate-shared/foo.css
     * @param string $service_dir   абсолютный путь к pages/services/<slug>/
     */
    function lireate_service_asset_query($relative_path, $service_dir)
    {
        $relative_path = ltrim(str_replace('\\', '/', (string) $relative_path), '/');
        $base = rtrim((string) $service_dir, '/\\');
        $fs = $base . '/' . $relative_path;
        if (is_file($fs)) {
            return '?v=' . rawurlencode((string) filemtime($fs));
        }
        return '?v=0';
    }

    function lireate_service_asset_href($relative_path, $service_dir)
    {
        $relative_path = ltrim(str_replace('\\', '/', (string) $relative_path), '/');
        $q = lireate_service_asset_query($relative_path, $service_dir);
        return htmlspecialchars($relative_path . $q, ENT_QUOTES, 'UTF-8');
    }

    function lireate_service_asset_echo($relative_path, $service_dir)
    {
        echo lireate_service_asset_href($relative_path, $service_dir);
    }
}

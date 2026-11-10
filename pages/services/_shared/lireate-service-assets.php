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

    /**
     * Неразрывные пробелы в суммах (17{NNBSP}000{NBSP}₽) и в «п.{NBSP}2.1».
     *
     * @param string $text
     * @return string
     */
    function lireate_typography_nb($text)
    {
        if (!is_string($text) || $text === '') {
            return $text;
        }
        $nnbs = "\xE2\x80\xAF";
        $nbsp = "\xC2\xA0";
        do {
            $prev = $text;
            $text = preg_replace('/(\d) (\d{3})(?!\d)/u', '$1' . $nnbs . '$2', $text);
        } while ($prev !== $text);
        $text = preg_replace('/(\d) ₽/u', '$1' . $nbsp . '₽', $text);
        $text = preg_replace('/п\. (\d)/u', 'п.' . $nbsp . '$1', $text);
        return $text;
    }
}

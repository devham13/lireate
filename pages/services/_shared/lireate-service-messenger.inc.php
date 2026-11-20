<?php
/**
 * Загрузка lireate-messenger-urls.php (PHP 5.6): realpath-кандидаты, без template/_shared.
 */
if (defined('LIREATE_MAX_CHAT_URL')) {
    return;
}

if (!function_exists('lireate_service_resolve_messenger_bundle_file')) {
    function lireate_service_resolve_messenger_bundle_file($filename)
    {
        $candidates = array();
        $doc = isset($_SERVER['DOCUMENT_ROOT']) ? rtrim((string) $_SERVER['DOCUMENT_ROOT'], '/') : '';
        if ($doc !== '') {
            $dr = realpath($doc);
            if ($dr !== false) {
                $candidates[] = $dr . '/_shared/' . $filename;
                $candidates[] = $dr . '/pages/services/_shared/' . $filename;
                $candidates[] = $dr . '/pages/blog/_shared/' . $filename;
                $candidates[] = $dr . '/template/lireate/' . $filename;
            }
        }
        $anchor = dirname(__FILE__);
        if ($anchor !== '') {
            $candidates[] = $anchor . '/' . $filename;
            $parent = dirname($anchor);
            if ($parent !== '' && $parent !== $anchor) {
                $candidates[] = $parent . '/' . $filename;
                $candidates[] = $parent . '/_shared/' . $filename;
            }
            $gp = dirname($parent);
            if ($gp !== '' && $gp !== $parent) {
                $candidates[] = $gp . '/_shared/' . $filename;
                $candidates[] = $gp . '/services/_shared/' . $filename;
            }
        }
        if (function_exists('debug_backtrace')) {
            $bt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 8);
            for ($i = 1; $i < count($bt); $i++) {
                if (empty($bt[$i]['file'])) {
                    continue;
                }
                $callerDir = dirname($bt[$i]['file']);
                if ($callerDir === '') {
                    continue;
                }
                $candidates[] = $callerDir . '/' . $filename;
                $pp = dirname($callerDir);
                if ($pp !== '' && $pp !== $callerDir) {
                    $candidates[] = $pp . '/_shared/' . $filename;
                }
            }
        }
        $seen = array();
        foreach ($candidates as $path) {
            $path = (string) $path;
            if ($path === '' || isset($seen[$path])) {
                continue;
            }
            $seen[$path] = true;
            if (!is_file($path)) {
                continue;
            }
            $rp = realpath($path);
            if ($rp !== false && is_file($rp)) {
                return $rp;
            }
        }
        return '';
    }
}

if (!function_exists('lireate_service_bootstrap_messenger')) {
    function lireate_service_bootstrap_messenger()
    {
        if (defined('LIREATE_MAX_CHAT_URL')) {
            return;
        }
        if (!function_exists('lireate_service_require_messenger_urls')) {
            $load = lireate_service_resolve_messenger_bundle_file('lireate-messenger-load.php');
            if ($load !== '') {
                require_once $load;
            }
        }
        if (function_exists('lireate_service_require_messenger_urls')) {
            lireate_service_require_messenger_urls();
        }
    }
}

lireate_service_bootstrap_messenger();

<?php
/**
 * Каноническая загрузка lireate-messenger-urls.php для страниц услуг (PHP 5.6).
 */

if (!function_exists('lireate_service_docroot')) {
    function lireate_service_docroot()
    {
        return isset($_SERVER['DOCUMENT_ROOT']) ? rtrim((string) $_SERVER['DOCUMENT_ROOT'], '/') : '';
    }
}

if (!function_exists('lireate_service_require_messenger_urls')) {
    function lireate_service_require_messenger_urls()
    {
        if (defined('LIREATE_MAX_CHAT_URL')) {
            return;
        }
        $candidates = array();
        $doc = lireate_service_docroot();
        if ($doc !== '') {
            $dr = realpath($doc);
            if ($dr !== false) {
                $candidates[] = $dr . '/_shared/lireate-messenger-urls.php';
                $candidates[] = $dr . '/pages/services/_shared/lireate-messenger-urls.php';
                $candidates[] = $dr . '/pages/blog/_shared/lireate-messenger-urls.php';
                $candidates[] = $dr . '/template/lireate/lireate-messenger-urls.php';
            } else {
                $candidates[] = $doc . '/_shared/lireate-messenger-urls.php';
                $candidates[] = $doc . '/pages/services/_shared/lireate-messenger-urls.php';
                $candidates[] = $doc . '/pages/blog/_shared/lireate-messenger-urls.php';
                $candidates[] = $doc . '/template/lireate/lireate-messenger-urls.php';
            }
        }
        $here = dirname(__FILE__);
        if ($here !== '') {
            $candidates[] = $here . '/lireate-messenger-urls.php';
        }
        if (function_exists('debug_backtrace')) {
            $bt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 4);
            for ($i = 1; $i < count($bt); $i++) {
                if (empty($bt[$i]['file'])) {
                    continue;
                }
                $callerDir = dirname($bt[$i]['file']);
                if ($callerDir === '') {
                    continue;
                }
                $candidates[] = $callerDir . '/lireate-messenger-urls.php';
                $candidates[] = dirname($callerDir) . '/_shared/lireate-messenger-urls.php';
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
                require_once $rp;
                return;
            }
        }
    }
}

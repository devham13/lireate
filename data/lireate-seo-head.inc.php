<?php
/**
 * Canonical, robots, Open Graph, home Organization JSON-LD (articles: JSON-LD in layout tail).
 */

if (!function_exists('lireate_seo_bootstrap')) {
    function lireate_seo_bootstrap()
    {
        static $done = false;
        if ($done) {
            return;
        }
        $dataDir = dirname(__FILE__);
        $runtime = $dataDir . '/lireate-seo-runtime.php';
        $schema = $dataDir . '/lireate-seo-schema.inc.php';
        if (is_file($runtime)) {
            require_once $runtime;
        }
        if (is_file($schema)) {
            require_once $schema;
        }
        if (function_exists('lireate_seo_init_globals')) {
            lireate_seo_init_globals();
        }
        $done = true;
    }
}

if (!function_exists('lireate_seo_render_og_meta')) {
    function lireate_seo_render_og_meta($og)
    {
        if (!is_array($og)) {
            return;
        }
        $GLOBALS['lireate_seo_og_done'] = true;

        $type = isset($og['type']) ? (string) $og['type'] : 'website';
        $title = isset($og['title']) ? (string) $og['title'] : '';
        $desc = isset($og['description']) ? (string) $og['description'] : '';
        $url = isset($og['url']) ? (string) $og['url'] : '';
        $image = isset($og['image']) ? (string) $og['image'] : '';

        if (function_exists('lireate_seo_https_url')) {
            $url = lireate_seo_https_url($url);
            $image = lireate_seo_https_url($image);
        }

        if ($url !== '') {
            echo '<meta property="og:url" content="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . "\" />\n";
        }
        echo '<meta property="og:type" content="' . htmlspecialchars($type, ENT_QUOTES, 'UTF-8') . "\" />\n";
        if ($title !== '') {
            echo '<meta property="og:title" content="' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . "\" />\n";
        }
        if ($desc !== '') {
            echo '<meta property="og:description" content="' . htmlspecialchars($desc, ENT_QUOTES, 'UTF-8') . "\" />\n";
        }
        if ($image !== '') {
            echo '<meta property="og:image" content="' . htmlspecialchars($image, ENT_QUOTES, 'UTF-8') . "\" />\n";
        }

        if ($type === 'article' && !empty($GLOBALS['lireate_seo_article']) && is_array($GLOBALS['lireate_seo_article'])) {
            $published = isset($GLOBALS['lireate_seo_article']['date_iso'])
                ? (string) $GLOBALS['lireate_seo_article']['date_iso']
                : '';
            $modified = isset($GLOBALS['lireate_seo_article']['date_modified_iso'])
                ? (string) $GLOBALS['lireate_seo_article']['date_modified_iso']
                : $published;
            if ($published !== '') {
                echo '<meta property="article:published_time" content="' . htmlspecialchars($published, ENT_QUOTES, 'UTF-8') . "\" />\n";
            }
            if ($modified !== '') {
                echo '<meta property="article:modified_time" content="' . htmlspecialchars($modified, ENT_QUOTES, 'UTF-8') . "\" />\n";
            }
        }
    }
}

if (!function_exists('lireate_seo_render_head_tags')) {
    function lireate_seo_render_head_tags()
    {
        lireate_seo_bootstrap();

        if (function_exists('lireate_seo_prepare_og_globals')) {
            lireate_seo_prepare_og_globals();
        }

        if (!empty($GLOBALS['lireate_canonical_url']) && strpos($GLOBALS['lireate_canonical_url'], '/404') === false) {
            $c = function_exists('lireate_seo_https_url')
                ? lireate_seo_https_url($GLOBALS['lireate_canonical_url'])
                : $GLOBALS['lireate_canonical_url'];
            echo '<link rel="canonical" href="' . htmlspecialchars($c, ENT_QUOTES, 'UTF-8') . "\" />\n";
        }
        if (!empty($GLOBALS['lireate_robots_noindex'])) {
            echo '<meta name="robots" content="noindex, follow" />' . "\n";
        }

        $og = !empty($GLOBALS['lireate_seo_og']) && is_array($GLOBALS['lireate_seo_og'])
            ? $GLOBALS['lireate_seo_og']
            : null;

        if ($og !== null) {
            lireate_seo_render_og_meta($og);
        }

        $path = function_exists('lireate_seo_request_path') ? lireate_seo_request_path() : '/';
        $isHome = ($path === '/' || trim($path, '/') === '');
        if ($isHome && function_exists('lireate_seo_home_organization')) {
            $json = lireate_seo_jsonld_encode(lireate_seo_home_organization());
            if ($json !== '') {
                echo '<script type="application/ld+json">' . $json . "</script>\n";
            }
        }

        if (function_exists('lireate_seo_render_service_schema_scripts') && function_exists('lireate_seo_request_path')) {
            $slug = trim(lireate_seo_request_path(), '/');
            $slugOne = $slug !== '' ? explode('/', $slug)[0] : '';
            $canonical = !empty($GLOBALS['lireate_canonical_url']) ? (string) $GLOBALS['lireate_canonical_url'] : '';
            $ogTitle = ($og !== null && isset($og['title'])) ? (string) $og['title'] : '';
            if ($slugOne !== '') {
                lireate_seo_render_service_schema_scripts($slugOne, $ogTitle, $canonical);
            }
        }

    }
}

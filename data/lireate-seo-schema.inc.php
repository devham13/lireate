<?php
/**
 * JSON-LD + article context for LIREATE SEO (used with lireate-seo-head.inc.php).
 */

if (!function_exists('lireate_seo_schema_h')) {
    function lireate_seo_schema_h($s)
    {
        return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('lireate_seo_schema_public_base')) {
    function lireate_seo_schema_public_base()
    {
        if (function_exists('lireate_seo_public_base')) {
            return lireate_seo_public_base();
        }
        $host = isset($_SERVER['HTTP_HOST']) ? (string) $_SERVER['HTTP_HOST'] : 'example.invalid';
        $host = preg_replace('/^www\./i', '', $host);
        return 'https://' . $host;
    }
}

if (!function_exists('lireate_seo_datetime_to_iso')) {
    function lireate_seo_datetime_to_iso($unix, $displayFallback = '')
    {
        $unix = (int) $unix;
        if ($unix > 0) {
            return gmdate('Y-m-d', $unix) . 'T12:00:00+03:00';
        }
        $displayFallback = trim((string) $displayFallback);
        if ($displayFallback !== '' && preg_match('/^(\d{2})\.(\d{2})\.(\d{4})$/', $displayFallback, $m)) {
            return $m[3] . '-' . $m[2] . '-' . $m[1] . 'T12:00:00+03:00';
        }
        return '';
    }
}

if (!function_exists('lireate_seo_extract_faq_pairs')) {
    function lireate_seo_extract_faq_pairs($html)
    {
        $html = (string) $html;
        if ($html === '') {
            return array();
        }
        $section = $html;
        if (preg_match('/<h2[^>]*>\s*Частые вопросы\s*<\/h2>(.*)$/isu', $html, $m)) {
            $section = $m[1];
        } else {
            return array();
        }
        $pairs = array();
        if (!preg_match_all('/<h3[^>]*>(.*?)<\/h3>\s*<(p|div)[^>]*>(.*?)<\/\2>/isu', $section, $matches, PREG_SET_ORDER)) {
            return array();
        }
        foreach ($matches as $match) {
            $q = trim(strip_tags($match[1]));
            $a = trim(strip_tags($match[3]));
            if ($q === '' || $a === '') {
                continue;
            }
            $pairs[] = array('question' => $q, 'answer' => $a);
            if (count($pairs) >= 12) {
                break;
            }
        }
        return $pairs;
    }
}

if (!function_exists('lireate_seo_absolutize_image_url')) {
    function lireate_seo_absolutize_image_url($src)
    {
        $src = trim((string) $src);
        if ($src === '') {
            return '';
        }
        if (preg_match('#^https?://#i', $src)) {
            return function_exists('lireate_seo_https_url')
                ? lireate_seo_https_url($src)
                : preg_replace('#^http:#i', 'https:', $src);
        }
        $base = lireate_seo_schema_public_base();
        return $base . '/' . ltrim($src, '/');
    }
}

if (!function_exists('lireate_seo_first_https_image')) {
    function lireate_seo_first_https_image($html, $coverHtml = '')
    {
        $candidates = array((string) $coverHtml, (string) $html);
        foreach ($candidates as $chunk) {
            if ($chunk === '') {
                continue;
            }
            if (!preg_match_all('/<img[^>]+src=["\']([^"\']+)["\']/iu', $chunk, $all)) {
                continue;
            }
            foreach ($all[1] as $src) {
                $src = trim($src);
                if ($src !== '' && stripos($src, '/template/images/blog/') !== false) {
                    return lireate_seo_absolutize_image_url($src);
                }
            }
            if (!empty($all[1][0])) {
                return lireate_seo_absolutize_image_url($all[1][0]);
            }
        }
        return '';
    }
}

if (!function_exists('lireate_seo_db_mysqli')) {
    function lireate_seo_db_mysqli()
    {
        static $mysqli = null;
        static $failed = false;
        if ($failed) {
            return null;
        }
        if ($mysqli instanceof mysqli) {
            return $mysqli;
        }
        $docroot = isset($_SERVER['DOCUMENT_ROOT']) ? rtrim((string) $_SERVER['DOCUMENT_ROOT'], '/') : '';
        $dbFile = $docroot . '/data/db.php';
        if ($docroot === '' || !is_file($dbFile)) {
            $failed = true;
            return null;
        }
        require_once $dbFile;
        if (!defined('DB_SERVER') || !defined('DB_USER') || !defined('DB_NAME')) {
            $failed = true;
            return null;
        }
        $pass = defined('DB_PASS') ? DB_PASS : '';
        $mysqli = @new mysqli(DB_SERVER, DB_USER, $pass, DB_NAME);
        if ($mysqli->connect_errno) {
            $mysqli = null;
            $failed = true;
            return null;
        }
        if (!$mysqli->set_charset('utf8mb4')) {
            $mysqli->set_charset('utf8');
        }
        return $mysqli;
    }
}

if (!function_exists('lireate_seo_fetch_news_by_slug')) {
    function lireate_seo_fetch_news_by_slug($slug)
    {
        $slug = trim((string) $slug);
        if ($slug === '') {
            return null;
        }
        $mysqli = lireate_seo_db_mysqli();
        if (!$mysqli) {
            return null;
        }
        $st = $mysqli->prepare('SELECT name, full_story, seo_title, seo_description, datetime FROM news WHERE seo_url=? AND active=1 LIMIT 1');
        if (!$st) {
            return null;
        }
        $st->bind_param('s', $slug);
        $st->execute();
        $row = $st->get_result()->fetch_assoc();
        return is_array($row) ? $row : null;
    }
}

if (!function_exists('lireate_seo_fetch_page_by_slug')) {
    function lireate_seo_fetch_page_by_slug($slug)
    {
        $slug = trim((string) $slug);
        if ($slug === '') {
            return null;
        }
        $mysqli = lireate_seo_db_mysqli();
        if (!$mysqli) {
            return null;
        }
        $st = $mysqli->prepare('SELECT name, seo_title, seo_description FROM pages WHERE seo_url=? LIMIT 1');
        if (!$st) {
            return null;
        }
        $st->bind_param('s', $slug);
        $st->execute();
        $row = $st->get_result()->fetch_assoc();
        return is_array($row) ? $row : null;
    }
}

if (!function_exists('lireate_seo_audit_meta_config')) {
    function lireate_seo_audit_meta_config()
    {
        static $cfg = null;
        if ($cfg !== null) {
            return $cfg;
        }
        $cfg = array('news' => array(), 'pages' => array());
        $docroot = isset($_SERVER['DOCUMENT_ROOT']) ? rtrim((string) $_SERVER['DOCUMENT_ROOT'], '/') : '';
        $candidates = array();
        if ($docroot !== '') {
            $candidates[] = $docroot . '/data/lireate-seo-audit-meta.json';
        }
        $candidates[] = dirname(__DIR__) . '/../shared/lireate-seo-audit-meta.json';
        foreach ($candidates as $file) {
            if (!is_file($file)) {
                continue;
            }
            $raw = file_get_contents($file);
            if ($raw === false || $raw === '') {
                continue;
            }
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                if (!empty($decoded['news']) && is_array($decoded['news'])) {
                    $cfg['news'] = $decoded['news'];
                }
                if (!empty($decoded['pages']) && is_array($decoded['pages'])) {
                    $cfg['pages'] = $decoded['pages'];
                }
            }
            break;
        }
        return $cfg;
    }
}

if (!function_exists('lireate_seo_service_landings')) {
    function lireate_seo_service_landings()
    {
        $labels = array(
            'poisk_tovarnyh_znakov' => 'Поиск товарных знаков',
            'ekspertiza_tovarnogo_znaka' => 'Экспертиза товарного знака',
            'mezhdunarodnaja_registracija_tovarnogo_znaka' => 'Международная регистрация товарного знака',
            'registracija_torgovyh_marok_i_logotipov' => 'Регистрация торговых марок и логотипов',
            'patent' => 'Патентование изобретений',
            'patent_prom_obr' => 'Промышленные образцы',
        );
        if (function_exists('lireate_seo_service_with_slash')) {
            foreach (lireate_seo_service_with_slash() as $slug) {
                if (!isset($labels[$slug])) {
                    $labels[$slug] = $slug;
                }
            }
        }
        return $labels;
    }
}

if (!function_exists('lireate_seo_render_mktu_schema_scripts')) {
    function lireate_seo_render_mktu_schema_scripts($classNum, $title, $canonical)
    {
        if (!empty($GLOBALS['lireate_seo_mktu_schema_rendered'])) {
            return;
        }
        $classNum = (int) $classNum;
        if ($classNum < 1 || $classNum > 45) {
            return;
        }
        $meta = function_exists('lireate_seo_mktu_class_meta')
            ? lireate_seo_mktu_class_meta($classNum)
            : null;
        $base = lireate_seo_schema_public_base();
        $mktuHub = $base . '/mktu';
        $crumbClass = 'Класс ' . $classNum;
        if (is_array($meta) && !empty($meta['h1'])) {
            $crumbClass = (string) $meta['h1'];
        } elseif ($title !== '') {
            $crumbClass = $title;
        }
        if ($canonical === '') {
            $canonical = $base . '/' . $classNum . '/';
        }
        $pageName = $title !== '' ? $title : $crumbClass;
        $webPageId = $canonical;
        $graph = array(
            '@context' => 'https://schema.org',
            '@graph' => array(
                array(
                    '@type' => 'WebPage',
                    '@id' => $webPageId . '#webpage',
                    'url' => $canonical,
                    'name' => $pageName,
                    'isPartOf' => array('@id' => $base . '/#website'),
                ),
                array(
                    '@type' => 'BreadcrumbList',
                    '@id' => $canonical . '#breadcrumb',
                    'itemListElement' => array(
                        array('@type' => 'ListItem', 'position' => 1, 'name' => 'Главная', 'item' => $base . '/'),
                        array('@type' => 'ListItem', 'position' => 2, 'name' => 'МКТУ', 'item' => $mktuHub),
                        array('@type' => 'ListItem', 'position' => 3, 'name' => $crumbClass, 'item' => $canonical),
                    ),
                ),
            ),
        );
        $json = lireate_seo_jsonld_encode($graph);
        if ($json === '') {
            return;
        }
        echo '<script type="application/ld+json">' . $json . "</script>\n";
        $GLOBALS['lireate_seo_mktu_schema_rendered'] = true;
    }
}

if (!function_exists('lireate_seo_render_service_schema_scripts')) {
    function lireate_seo_render_service_schema_scripts($slug, $title, $canonical)
    {
        if (!empty($GLOBALS['lireate_seo_service_schema_rendered'])) {
            return;
        }
        $landings = lireate_seo_service_landings();
        $knownService = function_exists('lireate_seo_service_with_slash')
            && in_array($slug, lireate_seo_service_with_slash(), true);
        if (!$knownService && !isset($landings[$slug])) {
            return;
        }
        $base = lireate_seo_schema_public_base();
        $name = $title !== '' ? $title : (isset($landings[$slug]) ? $landings[$slug] : $slug);
        if ($name === $slug && function_exists('lireate_seo_static_page_meta')) {
            $meta = lireate_seo_static_page_meta($slug);
            if (is_array($meta) && !empty($meta['title'])) {
                $name = (string) $meta['title'];
            }
        }
        $canonical = $canonical !== '' ? $canonical : ($base . '/' . $slug . '/');
        $webPageId = $canonical;
        $graph = array(
            '@context' => 'https://schema.org',
            '@graph' => array(
                array(
                    '@type' => 'WebPage',
                    '@id' => $webPageId . '#webpage',
                    'url' => $canonical,
                    'name' => $name,
                    'isPartOf' => array(
                        '@type' => 'WebSite',
                        '@id' => $base . '/#website',
                        'url' => $base . '/',
                        'name' => 'ЛИРЕЙТ',
                    ),
                ),
                array(
                    '@type' => 'BreadcrumbList',
                    '@id' => $canonical . '#breadcrumb',
                    'itemListElement' => array(
                        array('@type' => 'ListItem', 'position' => 1, 'name' => 'Главная', 'item' => $base . '/'),
                        array('@type' => 'ListItem', 'position' => 2, 'name' => 'Услуги', 'item' => $base . '/uslugi/'),
                        array('@type' => 'ListItem', 'position' => 3, 'name' => $name, 'item' => $canonical),
                    ),
                ),
                array(
                    '@type' => 'Service',
                    '@id' => $canonical . '#service',
                    'name' => $name,
                    'url' => $canonical,
                    'provider' => array(
                        '@type' => 'Organization',
                        'name' => 'ООО «ЛИРЕЙТ»',
                        'url' => $base . '/',
                    ),
                    'areaServed' => 'RU',
                ),
            ),
        );
        $json = lireate_seo_jsonld_encode($graph);
        if ($json === '') {
            return;
        }
        echo '<script type="application/ld+json">' . $json . "</script>\n";
        $GLOBALS['lireate_seo_service_schema_rendered'] = true;
    }
}

if (!function_exists('lireate_seo_hub_schema_slugs')) {
    function lireate_seo_hub_schema_slugs()
    {
        $hubs = array(
            'mktu', 'docs', 'team', 'news', 'portfolio', 'reviews', 'o_kompanii', 'partners',
            'kontaktyi', 'contacts', 'uslugi', 'avtor',
            'private_data', 'politika_konfidencialnosti',
        );
        if (function_exists('lireate_seo_cms_slashless')) {
            foreach (lireate_seo_cms_slashless() as $slug) {
                if (!in_array($slug, $hubs, true)) {
                    $hubs[] = $slug;
                }
            }
        }
        return $hubs;
    }
}

if (!function_exists('lireate_seo_hub_breadcrumb_items')) {
    /**
     * @param string $pathSlug trimmed path without leading/trailing slash
     * @param string $pageTitle
     * @return array|null list of array(name, url)
     */
    function lireate_seo_hub_breadcrumb_items($pathSlug, $pageTitle)
    {
        $base = lireate_seo_schema_public_base();
        $home = array('name' => 'Главная', 'url' => $base . '/');
        $pathSlug = trim((string) $pathSlug, '/');
        if ($pathSlug === '') {
            return null;
        }
        $parts = explode('/', $pathSlug);
        $one = $parts[0];
        $labels = array(
            'mktu' => 'МКТУ',
            'docs' => 'Документы',
            'team' => 'Команда',
            'news' => 'Блог',
            'portfolio' => 'Портфолио',
            'reviews' => 'Отзывы',
            'o_kompanii' => 'О компании',
            'partners' => 'Партнёры',
            'kontaktyi' => 'Контакты',
            'contacts' => 'Контакты',
            'uslugi' => 'Услуги',
            'avtor' => 'Автор',
            'private_data' => 'Персональные данные',
            'politika_konfidencialnosti' => 'Политика конфиденциальности',
        );
        if (count($parts) === 1) {
            if (!in_array($one, lireate_seo_hub_schema_slugs(), true)) {
                return null;
            }
            $name = isset($labels[$one]) ? $labels[$one] : ($pageTitle !== '' ? $pageTitle : $one);
            if (function_exists('lireate_seo_service_with_slash') && in_array($one, lireate_seo_service_with_slash(), true)) {
                return null;
            }
            $url = $base . '/' . $one;
            if ($one === 'contacts' || $one === 'uslugi' || $one === 'avtor') {
                $url = $base . '/' . $one . '/';
            }
            return array($home, array('name' => $name, 'url' => $url));
        }
        if ($one === 'portfolio' && count($parts) >= 2) {
            $caseTitle = $pageTitle !== '' ? $pageTitle : $parts[1];
            $portfolioUrl = $base . '/portfolio';
            $caseUrl = $base . '/portfolio/' . $parts[1] . '/';
            return array(
                $home,
                array('name' => 'Портфолио', 'url' => $portfolioUrl),
                array('name' => $caseTitle, 'url' => $caseUrl),
            );
        }
        return null;
    }
}

if (!function_exists('lireate_seo_render_avtor_schema_scripts')) {
    function lireate_seo_render_avtor_schema_scripts($title, $canonical)
    {
        if (!empty($GLOBALS['lireate_seo_avtor_schema_rendered'])) {
            return;
        }
        $base = lireate_seo_schema_public_base();
        if ($canonical === '') {
            $canonical = $base . '/avtor/';
        }
        $pageTitle = $title !== '' ? $title : 'Юрий Горбачев — директор патентного бюро Лирейт';
        $personName = 'Горбачев Юрий Александрович';
        $image = $base . '/template/images/team/person/11.jpg';
        $email = 'info@' . 'lireate' . '.' . 'ru';
        $graph = array(
            '@context' => 'https://schema.org',
            '@graph' => array(
                array(
                    '@type' => 'ProfilePage',
                    '@id' => $canonical . '#profilepage',
                    'url' => $canonical,
                    'name' => $pageTitle,
                    'mainEntity' => array('@id' => $canonical . '#person'),
                    'isPartOf' => array(
                        '@type' => 'WebSite',
                        '@id' => $base . '/#website',
                        'url' => $base . '/',
                        'name' => 'ЛИРЕЙТ',
                    ),
                ),
                array(
                    '@type' => 'Person',
                    '@id' => $canonical . '#person',
                    'name' => $personName,
                    'alternateName' => 'Юрий Горбачев',
                    'jobTitle' => 'Директор, управляющий партнёр',
                    'url' => $canonical,
                    'image' => $image,
                    'worksFor' => array(
                        '@type' => 'Organization',
                        'name' => 'LIREATE',
                        'legalName' => 'ООО «ЛИРЕЙТ»',
                        'url' => $base . '/',
                        'taxID' => '1648040894',
                        'telephone' => '+7-960-048-49-57',
                        'email' => $email,
                        'sameAs' => array('https://vk.com/lireate', 'https://t.me/lireate_patent'),
                    ),
                    'knowsAbout' => array(
                        'Товарные знаки',
                        'Патентное право',
                        'Авторское право',
                        'Программы для ЭВМ',
                        'Споры об интеллектуальной собственности',
                    ),
                    'sameAs' => array(
                        'https://vc.ru/id694569',
                        'https://t-j.ru/user2640218/',
                        'https://www.klerk.ru/author/6739/',
                        'https://tenchat.ru/gorbachev_yuriy',
                        'https://www.cossa.ru/profile/?ID=259138',
                    ),
                ),
                array(
                    '@type' => 'BreadcrumbList',
                    '@id' => $canonical . '#breadcrumb',
                    'itemListElement' => array(
                        array('@type' => 'ListItem', 'position' => 1, 'name' => 'Главная', 'item' => $base . '/'),
                        array('@type' => 'ListItem', 'position' => 2, 'name' => 'Автор', 'item' => $canonical),
                    ),
                ),
            ),
        );
        $json = lireate_seo_jsonld_encode($graph);
        if ($json === '') {
            return;
        }
        echo '<script type="application/ld+json">' . $json . "</script>\n";
        $GLOBALS['lireate_seo_avtor_schema_rendered'] = true;
        $GLOBALS['lireate_seo_hub_schema_rendered'] = true;
        $GLOBALS['lireate_seo_service_schema_rendered'] = true;
    }
}

if (!function_exists('lireate_seo_render_hub_schema_scripts')) {
    function lireate_seo_render_hub_schema_scripts($pathSlug, $title, $canonical)
    {
        if (!empty($GLOBALS['lireate_seo_hub_schema_rendered'])
            || !empty($GLOBALS['lireate_seo_service_schema_rendered'])
            || !empty($GLOBALS['lireate_seo_mktu_schema_rendered'])) {
            return;
        }
        $items = lireate_seo_hub_breadcrumb_items($pathSlug, $title);
        if ($items === null || count($items) < 1) {
            return;
        }
        $base = lireate_seo_schema_public_base();
        $canonical = $canonical !== '' ? $canonical : $base . '/' . trim($pathSlug, '/') . '/';
        if (substr($canonical, -1) !== '/' && strpos($pathSlug, 'portfolio/') === 0) {
            $canonical = rtrim($canonical, '/') . '/';
        }
        $name = $title !== '' ? $title : $items[count($items) - 1]['name'];
        $crumbs = array();
        $pos = 1;
        foreach ($items as $it) {
            $crumbs[] = array(
                '@type' => 'ListItem',
                'position' => $pos,
                'name' => $it['name'],
                'item' => $it['url'],
            );
            $pos++;
        }
        $graph = array(
            '@context' => 'https://schema.org',
            '@graph' => array(
                array(
                    '@type' => 'WebPage',
                    '@id' => $canonical . '#webpage',
                    'url' => $canonical,
                    'name' => $name,
                    'isPartOf' => array(
                        '@type' => 'WebSite',
                        '@id' => $base . '/#website',
                        'url' => $base . '/',
                        'name' => 'ЛИРЕЙТ',
                    ),
                ),
                array(
                    '@type' => 'BreadcrumbList',
                    '@id' => $canonical . '#breadcrumb',
                    'itemListElement' => $crumbs,
                ),
            ),
        );
        $json = lireate_seo_jsonld_encode($graph);
        if ($json === '') {
            return;
        }
        echo '<script type="application/ld+json">' . $json . "</script>\n";
        $GLOBALS['lireate_seo_hub_schema_rendered'] = true;
    }
}

$_lireate_seo_page_meta_inc = dirname(__FILE__) . '/lireate-seo-page-meta.inc.php';
if (is_file($_lireate_seo_page_meta_inc)) {
    require_once $_lireate_seo_page_meta_inc;
}

if (!function_exists('lireate_seo_prepare_og_globals')) {
    function lireate_seo_prepare_og_globals()
    {
        if (!empty($GLOBALS['lireate_seo_og']) && is_array($GLOBALS['lireate_seo_og'])) {
            return;
        }
        $path = function_exists('lireate_seo_request_path') ? lireate_seo_request_path() : '/';
        $kind = function_exists('lireate_seo_page_kind') ? lireate_seo_page_kind($path) : 'other';
        $base = lireate_seo_schema_public_base();
        $metaCfg = lireate_seo_audit_meta_config();
        $canonical = '';
        if (!empty($GLOBALS['lireate_canonical_url'])) {
            $canonical = lireate_seo_https_url($GLOBALS['lireate_canonical_url']);
        } elseif (function_exists('lireate_seo_canonical_from_path')) {
            $canonical = lireate_seo_https_url(lireate_seo_canonical_from_path($path));
        }
        $defaultImage = function_exists('lireate_seo_default_og_image')
            ? lireate_seo_default_og_image()
            : lireate_seo_https_url($base . '/template/images/OG_image.jpg');
        $ogImage = $defaultImage;

        if ($kind === 'article') {
            $slug = trim($path, '/');
            $parts = explode('/', $slug);
            $slug = $parts[0];
            $row = lireate_seo_fetch_news_by_slug($slug);
            if (is_array($row) && function_exists('lireate_seo_bind_news_article')) {
                $seoDesc = isset($row['seo_description']) ? $row['seo_description'] : '';
                if ($seoDesc === '' && isset($metaCfg['news'][$slug])) {
                    $seoDesc = (string) $metaCfg['news'][$slug];
                }
                lireate_seo_bind_news_article(
                    $row,
                    array(
                        'seo_title' => isset($row['seo_title']) ? $row['seo_title'] : '',
                        'seo_description' => $seoDesc,
                    )
                );
                return;
            }
        }

        $title = '';
        $desc = '';
        if ($kind === 'home') {
            $homeMeta = function_exists('lireate_seo_static_page_meta')
                ? lireate_seo_static_page_meta('home')
                : null;
            if (is_array($homeMeta)) {
                $title = !empty($homeMeta['title']) ? $homeMeta['title'] : $title;
                $desc = !empty($homeMeta['description']) ? $homeMeta['description'] : $desc;
            }
            if ($title === '') {
                $title = 'Патентное бюро Лирейт — защита ИС и товарных знаков';
            }
            if ($desc === '') {
                $desc = 'Регистрация товарных знаков, патентование, защита авторских прав и договоры. Патентное бюро Лирейт работает по России с 2015 года.';
            }
        } elseif ($kind === 'mktu') {
            $slug = trim($path, '/');
            if (preg_match('/^(\d{1,2})$/', $slug, $mktuM)) {
                $mktuMeta = function_exists('lireate_seo_mktu_class_meta')
                    ? lireate_seo_mktu_class_meta($mktuM[1])
                    : null;
                if (is_array($mktuMeta)) {
                    if (!empty($mktuMeta['title'])) {
                        $title = $mktuMeta['title'];
                    }
                    if (!empty($mktuMeta['description'])) {
                        $desc = $mktuMeta['description'];
                    }
                }
            }
        } elseif ($kind === 'service' || $kind === 'other' || $kind === 'cms') {
            $slug = trim($path, '/');
            $parts = explode('/', $slug);
            $slugOne = $parts[0];
            if ($slug !== '') {
                if (function_exists('lireate_seo_static_page_meta')) {
                    $staticMeta = lireate_seo_static_page_meta($slug);
                    if (!is_array($staticMeta) && $slugOne !== '') {
                        $staticMeta = lireate_seo_static_page_meta($slugOne);
                    }
                    if (is_array($staticMeta)) {
                        if (!empty($staticMeta['title'])) {
                            $title = $staticMeta['title'];
                        }
                        if (!empty($staticMeta['description'])) {
                            $desc = $staticMeta['description'];
                        }
                        if (!empty($staticMeta['image'])) {
                            $ogImage = lireate_seo_https_url($base . '/' . ltrim($staticMeta['image'], '/'));
                        }
                    }
                }
                $pageRow = lireate_seo_fetch_page_by_slug($slugOne);
                if (is_array($pageRow)) {
                    if ($title === '') {
                        $title = isset($pageRow['seo_title']) && $pageRow['seo_title'] !== ''
                            ? $pageRow['seo_title']
                            : (isset($pageRow['name']) ? $pageRow['name'] : '');
                    }
                    if ($desc === '') {
                        $desc = isset($pageRow['seo_description']) ? $pageRow['seo_description'] : '';
                    }
                }
                if ($desc === '' && isset($metaCfg['pages'][$slugOne])) {
                    $desc = (string) $metaCfg['pages'][$slugOne];
                }
                if ($title === '' && isset(lireate_seo_service_landings()[$slugOne])) {
                    $title = lireate_seo_service_landings()[$slugOne];
                }
            }
        }

        $GLOBALS['lireate_seo_og'] = array(
            'type' => 'website',
            'title' => $title,
            'description' => $desc,
            'url' => $canonical !== '' ? $canonical : lireate_seo_https_url($base . '/'),
            'image' => $ogImage !== '' ? $ogImage : $defaultImage,
        );
    }
}

if (!function_exists('lireate_seo_jsonld_encode')) {
    function lireate_seo_jsonld_encode($data)
    {
        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            return '';
        }
        json_decode($json);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return '';
        }
        return $json;
    }
}

if (!function_exists('lireate_seo_home_organization')) {
    function lireate_seo_home_organization()
    {
        $base = lireate_seo_schema_public_base();
        return array(
            '@context' => 'https://schema.org',
            '@graph' => array(
                array(
                    '@type' => 'Organization',
                    '@id' => $base . '/#organization',
                    'name' => 'ООО «ЛИРЕЙТ»',
                    'legalName' => 'ООО «ЛИРЕЙТ»',
                    'url' => $base . '/',
                    'email' => 'info@' . 'lireate' . '.' . 'ru',
                    'telephone' => array('+7-499-938-45-95', '+7-960-048-49-57'),
                    'address' => array(
                        '@type' => 'PostalAddress',
                        'streetAddress' => 'ул. Карла Маркса, 24, оф. 1',
                        'addressLocality' => 'Зеленодольск',
                        'addressCountry' => 'RU',
                    ),
                ),
                array(
                    '@type' => 'LegalService',
                    '@id' => $base . '/#legalservice',
                    'name' => 'ЛИРЕЙТ — патентное бюро',
                    'url' => $base . '/',
                    'provider' => array('@id' => $base . '/#organization'),
                    'areaServed' => 'RU',
                    'address' => array(
                        '@type' => 'PostalAddress',
                        'streetAddress' => 'ул. Карла Маркса, 24, оф. 1',
                        'addressLocality' => 'Зеленодольск',
                        'addressCountry' => 'RU',
                    ),
                ),
            ),
        );
    }
}

if (!function_exists('lireate_seo_bind_news_article')) {
    function lireate_seo_bind_news_article($row, $seo = array())
    {
        if (!is_array($row)) {
            return;
        }
        $base = lireate_seo_schema_public_base();
        $seoUrl = isset($row['seo_url']) ? trim((string) $row['seo_url']) : '';
        $canonical = $seoUrl !== '' ? $base . '/' . trim($seoUrl, '/') . '/' : '';
        if (!empty($GLOBALS['lireate_canonical_url'])) {
            $canonical = function_exists('lireate_seo_https_url')
                ? lireate_seo_https_url($GLOBALS['lireate_canonical_url'])
                : $GLOBALS['lireate_canonical_url'];
        }
        $title = isset($seo['seo_title']) ? $seo['seo_title'] : (isset($row['seo_title']) ? $row['seo_title'] : (isset($row['name']) ? $row['name'] : ''));
        $desc = isset($seo['seo_description']) ? $seo['seo_description'] : (isset($row['seo_description']) ? $row['seo_description'] : '');
        $displayDate = !empty($row['datetime']) ? date('d.m.Y', (int) $row['datetime']) : '';
        $iso = lireate_seo_datetime_to_iso(isset($row['datetime']) ? $row['datetime'] : 0, $displayDate);
        $body = isset($row['full_story']) ? (string) $row['full_story'] : '';
        $image = lireate_seo_first_https_image($body, '');
        $GLOBALS['lireate_seo_article'] = array(
            'title' => $title,
            'description' => $desc,
            'canonical' => $canonical,
            'date_iso' => $iso,
            'date_modified_iso' => $iso,
            'image' => $image,
            'body_html' => $body,
            'author' => defined('LIREATE_BLOG_AUTHOR_NAME') ? LIREATE_BLOG_AUTHOR_NAME : 'Юрий Горбачев',
            'author_role' => defined('LIREATE_BLOG_AUTHOR_ROLE') ? LIREATE_BLOG_AUTHOR_ROLE : 'Генеральный директор',
            'author_url' => $base . '/avtor/',
        );
        $fallbackImage = function_exists('lireate_seo_default_og_image')
            ? lireate_seo_default_og_image()
            : lireate_seo_https_url($base . '/template/images/OG_image.jpg');
        $GLOBALS['lireate_seo_og'] = array(
            'type' => 'article',
            'title' => $title,
            'description' => $desc,
            'url' => function_exists('lireate_seo_https_url') ? lireate_seo_https_url($canonical) : $canonical,
            'image' => $image !== '' ? lireate_seo_absolutize_image_url($image) : $fallbackImage,
        );
    }
}

if (!function_exists('lireate_seo_build_article_graph')) {
    function lireate_seo_build_article_graph($meta)
    {
        $base = lireate_seo_schema_public_base();
        $canonical = isset($meta['canonical']) ? $meta['canonical'] : '';
        $title = isset($meta['title']) ? $meta['title'] : '';
        $desc = isset($meta['description']) ? $meta['description'] : '';
        $iso = isset($meta['date_iso']) ? $meta['date_iso'] : '';
        $modified = isset($meta['date_modified_iso']) ? $meta['date_modified_iso'] : $iso;
        $image = isset($meta['image']) ? $meta['image'] : '';
        if ($image === '' && isset($meta['body_html'])) {
            $image = lireate_seo_first_https_image($meta['body_html'], isset($meta['cover_html']) ? $meta['cover_html'] : '');
        }
        $author = isset($meta['author']) ? $meta['author'] : 'Юрий Горбачев';
        $authorRole = isset($meta['author_role']) ? $meta['author_role'] : '';
        $authorUrl = isset($meta['author_url']) ? $meta['author_url'] : ($base . '/avtor/');
        $article = array(
            '@type' => 'BlogPosting',
            '@id' => $canonical !== '' ? $canonical . '#article' : null,
            'headline' => $title,
            'description' => $desc,
            'author' => array_filter(array(
                '@type' => 'Person',
                'name' => $author,
                'jobTitle' => $authorRole !== '' ? $authorRole : null,
                'url' => $authorUrl,
            )),
            'datePublished' => $iso !== '' ? $iso : null,
            'dateModified' => $modified !== '' ? $modified : null,
            'mainEntityOfPage' => $canonical !== '' ? array('@type' => 'WebPage', '@id' => $canonical) : null,
            'image' => $image !== '' ? array($image) : null,
            'publisher' => array('@id' => $base . '/#organization'),
        );
        $article = array_filter($article, function ($v) {
            return $v !== null && $v !== '';
        });
        $graph = array(
            '@context' => 'https://schema.org',
            '@graph' => array(
                array_filter(array(
                    '@type' => 'WebPage',
                    '@id' => $canonical,
                    'url' => $canonical,
                    'name' => $title,
                    'description' => $desc,
                    'isPartOf' => array('@type' => 'WebSite', 'url' => $base . '/', 'name' => 'ЛИРЕЙТ'),
                )),
                $article,
            ),
        );
        if ($canonical !== '') {
            $crumbName = isset($meta['breadcrumb_rubric']) ? trim((string) $meta['breadcrumb_rubric']) : '';
            $crumbUrl = isset($meta['breadcrumb_rubric_url']) ? trim((string) $meta['breadcrumb_rubric_url']) : '';
            if ($crumbUrl !== '' && $crumbUrl[0] === '/') {
                $crumbUrl = $base . $crumbUrl;
            } elseif ($crumbUrl === '') {
                $crumbUrl = $base . '/news/';
            }
            $crumbs = array(
                array('@type' => 'ListItem', 'position' => 1, 'name' => 'Главная', 'item' => $base . '/'),
                array('@type' => 'ListItem', 'position' => 2, 'name' => 'Блог', 'item' => $base . '/news/'),
            );
            if ($crumbName !== '') {
                $crumbs[] = array('@type' => 'ListItem', 'position' => 3, 'name' => $crumbName, 'item' => $crumbUrl);
                $crumbs[] = array('@type' => 'ListItem', 'position' => 4, 'name' => $title, 'item' => $canonical);
            } else {
                $crumbs[] = array('@type' => 'ListItem', 'position' => 3, 'name' => $title, 'item' => $canonical);
            }
            $graph['@graph'][] = array(
                '@type' => 'BreadcrumbList',
                '@id' => $canonical . '#breadcrumb',
                'itemListElement' => $crumbs,
            );
        }
        $faqSource = isset($meta['body_html']) ? $meta['body_html'] : '';
        $faq = lireate_seo_extract_faq_pairs($faqSource);
        if (!empty($faq)) {
            $mainEntity = array();
            foreach ($faq as $pair) {
                $mainEntity[] = array(
                    '@type' => 'Question',
                    'name' => $pair['question'],
                    'acceptedAnswer' => array(
                        '@type' => 'Answer',
                        'text' => $pair['answer'],
                    ),
                );
            }
            $graph['@graph'][] = array(
                '@type' => 'FAQPage',
                'mainEntity' => $mainEntity,
            );
        }
        return $graph;
    }
}

if (!function_exists('lireate_seo_render_article_schema_scripts')) {
    function lireate_seo_render_article_schema_scripts($meta)
    {
        if (!empty($GLOBALS['lireate_seo_schema_rendered'])) {
            return;
        }
        if (!empty($meta['body_html']) && empty($meta['image'])) {
            $meta['image'] = lireate_seo_first_https_image(
                $meta['body_html'],
                isset($meta['cover_html']) ? $meta['cover_html'] : ''
            );
        }
        $graph = lireate_seo_build_article_graph($meta);
        $json = lireate_seo_jsonld_encode($graph);
        if ($json === '') {
            return;
        }
        echo '<script type="application/ld+json">' . $json . "</script>\n";
        $GLOBALS['lireate_seo_schema_rendered'] = true;
    }
}

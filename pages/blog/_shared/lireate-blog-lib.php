<?php
/**
 * Хелперы рендера статей блога LIREATE.
 */
if (function_exists('lireate_blog_h')) {
    return;
}
require_once __DIR__ . '/lireate-blog-load.php';

if (!function_exists('lireate_blog_h')) {
function lireate_blog_h($s)
{
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
}
}

if (!function_exists('lireate_blog_strtolower')) {
function lireate_blog_strtolower($text)
{
    if (function_exists('mb_strtolower')) {
        return mb_strtolower($text, 'UTF-8');
    }
    return strtolower($text);
}
}

if (!function_exists('lireate_blog_strlen')) {
function lireate_blog_strlen($text)
{
    if (function_exists('mb_strlen')) {
        return mb_strlen($text, 'UTF-8');
    }
    return strlen($text);
}
}

if (!function_exists('lireate_blog_substr')) {
function lireate_blog_substr($text, $start, $len = 1)
{
    if (function_exists('mb_substr')) {
        return mb_substr($text, $start, $len, 'UTF-8');
    }
    return substr($text, $start, $len);
}
}

if (!function_exists('lireate_blog_slugify_heading')) {
function lireate_blog_slugify_heading($text)
{
    $text = strip_tags((string) $text);
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = lireate_blog_strtolower($text);
    $pairs = array(
        'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'e',
        'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm',
        'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u',
        'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch',
        'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
    );
    $out = '';
    $len = lireate_blog_strlen($text);
    for ($i = 0; $i < $len; $i++) {
        $ch = lireate_blog_substr($text, $i, 1);
        $low = lireate_blog_strtolower($ch);
        if (isset($pairs[$low])) {
            $out .= $pairs[$low];
        } elseif (preg_match('/[a-z0-9]/', $low)) {
            $out .= $low;
        } elseif ($out !== '' && substr($out, -1) !== '-') {
            $out .= '-';
        }
    }
    $out = preg_replace('/-+/', '-', $out);
    return trim($out, '-');
}
}

if (!function_exists('lireate_blog_ensure_h2_ids')) {
function lireate_blog_ensure_h2_ids($html)
{
    $html = (string) $html;
    if ($html === '') {
        return $html;
    }
    $used = array();
    return preg_replace_callback(
        '/<h2(\s[^>]*)?>(.*?)<\/h2>/isu',
        function ($m) use (&$used) {
            $attrs = isset($m[1]) ? $m[1] : '';
            $inner = $m[2];
            if (preg_match('/\bid\s*=\s*["\']([^"\']+)["\']/i', $attrs, $idm)) {
                $id = $idm[1];
            } else {
                $base = lireate_blog_slugify_heading($inner);
                if ($base === '') {
                    $base = 'section';
                }
                $id = $base;
                $n = 2;
                while (isset($used[$id])) {
                    $id = $base . '-' . $n;
                    $n++;
                }
                $attrs .= ' id="' . lireate_blog_h($id) . '"';
            }
            $used[$id] = true;
            return '<h2' . $attrs . '>' . $inner . '</h2>';
        },
        $html
    );
}
}

if (!function_exists('lireate_blog_extract_toc')) {
function lireate_blog_extract_toc($html)
{
    $items = array();
    if (!preg_match_all('/<h2[^>]*\sid=["\']([^"\']+)["\'][^>]*>(.*?)<\/h2>/isu', (string) $html, $m, PREG_SET_ORDER)) {
        return $items;
    }
    foreach ($m as $row) {
        $items[] = array(
            'id' => $row[1],
            'text' => trim(strip_tags($row[2])),
        );
    }
    return $items;
}
}

if (!function_exists('lireate_blog_date_display_to_iso')) {
function lireate_blog_date_display_to_iso($display)
{
    $display = trim((string) $display);
    if (preg_match('/^(\d{2})\.(\d{2})\.(\d{4})$/', $display, $m)) {
        return $m[3] . '-' . $m[2] . '-' . $m[1];
    }
    return '';
}
}

if (!function_exists('lireate_blog_index_path')) {
function lireate_blog_index_path()
{
    $candidates = array(
        lireate_blog_dir_up(__DIR__, 3) . '/data/blog-articles-index.json',
        (isset($_SERVER['DOCUMENT_ROOT']) ? rtrim($_SERVER['DOCUMENT_ROOT'], '/') : '') . '/data/blog-articles-index.json',
    );
    foreach ($candidates as $path) {
        if ($path !== '' && is_file($path)) {
            return $path;
        }
    }
    return $candidates[0];
}
}

if (!function_exists('lireate_blog_load_index')) {
function lireate_blog_load_index()
{
    $path = lireate_blog_index_path();
    if (!is_file($path)) {
        return array('articles' => array());
    }
    $data = json_decode(file_get_contents($path), true);
    if (!is_array($data) || !isset($data['articles']) || !is_array($data['articles'])) {
        return array('articles' => array());
    }
    return $data;
}
}

if (!function_exists('lireate_blog_public_origin')) {
function lireate_blog_public_origin()
{
    if (defined('LIREATE_BLOG_PUBLIC_ORIGIN') && LIREATE_BLOG_PUBLIC_ORIGIN !== '') {
        return rtrim((string) LIREATE_BLOG_PUBLIC_ORIGIN, '/');
    }
    return '';
}
}

if (!function_exists('lireate_blog_resolve_cover_url')) {
function lireate_blog_resolve_cover_url($cover)
{
    $cover = trim((string) $cover);
    if ($cover === '') {
        return '';
    }
    if (preg_match('#^https?://#i', $cover)) {
        return $cover;
    }
    $origin = lireate_blog_public_origin();
    if ($cover[0] === '/' && $origin !== '') {
        return $origin . $cover;
    }
    return $cover;
}
}

if (!function_exists('lireate_blog_docroot_fs_path')) {
function lireate_blog_docroot_fs_path($webPath)
{
    $webPath = trim((string) $webPath);
    if ($webPath === '' || preg_match('#^https?://#i', $webPath)) {
        return '';
    }
    $webPath = preg_replace('#\?.*$#', '', $webPath);
    $doc = lireate_blog_docroot();
    if ($doc === '') {
        return '';
    }
    return $doc . '/' . ltrim($webPath, '/');
}
}

if (!function_exists('lireate_blog_web_path_is_file')) {
function lireate_blog_web_path_is_file($webPath)
{
    if (preg_match('#^https?://#i', (string) $webPath)) {
        return true;
    }
    $fs = lireate_blog_docroot_fs_path($webPath);
    return $fs !== '' && is_file($fs);
}
}

if (!function_exists('lireate_blog_list_first_img_src_from_html')) {
function lireate_blog_list_first_img_src_from_html($html)
{
    $html = (string) $html;
    if ($html === '') {
        return '';
    }
    if (preg_match('/<img\b[^>]*\bsrc\s*=\s*["\']([^"\']+)["\']/i', $html, $m)) {
        return trim($m[1]);
    }
    return '';
}
}

if (!function_exists('lireate_blog_list_cover_candidate_paths')) {
function lireate_blog_list_cover_candidate_paths($row)
{
    $paths = array();
    $explicit = lireate_blog_list_row_get(
        $row,
        array('cover', 'image', 'img', 'preview', 'og_image', 'og:image', 'seo_image'),
        ''
    );
    if ($explicit !== '') {
        $paths[] = $explicit;
    }
    $body = lireate_blog_list_row_get($row, array('full_story', 'text', 'story'), '');
    if ($body === '') {
        $path = lireate_blog_list_seo_path($row);
        if ($path !== '') {
            $idx = lireate_blog_index_article_by_path($path);
            if (is_array($idx)) {
                $body = lireate_blog_list_row_get($idx, array('full_story', 'body_html'), '');
                if ($explicit === '') {
                    $fromIdx = lireate_blog_list_row_get($idx, array('cover', 'image'), '');
                    if ($fromIdx !== '') {
                        $paths[] = $fromIdx;
                    }
                }
            }
        }
    }
    $img = lireate_blog_list_first_img_src_from_html($body);
    if ($img !== '') {
        $basename = basename(parse_url($img, PHP_URL_PATH));
        if ($basename !== '' && $basename !== '.' && $basename !== '..') {
            $paths[] = '/template/images/blog/small/' . $basename;
            $paths[] = '/template/images/blog/' . $basename;
        }
        if (strpos($img, '/template/images/blog/') !== false) {
            $paths[] = $img;
        }
    }
    $seo = lireate_blog_list_row_get($row, array('seo_url', 'url', 'slug'), '');
    $seo = trim((string) $seo, '/');
    if ($seo !== '') {
        $paths[] = '/template/images/blog/small/' . $seo . '.jpg';
        $paths[] = '/template/images/blog/' . $seo . '.jpg';
    }
    $unique = array();
    foreach ($paths as $p) {
        $p = trim((string) $p);
        if ($p === '') {
            continue;
        }
        if (!in_array($p, $unique, true)) {
            $unique[] = $p;
        }
    }
    return $unique;
}
}

if (!function_exists('lireate_blog_list_cover_from_row')) {
function lireate_blog_list_cover_from_row($row)
{
    foreach (lireate_blog_list_cover_candidate_paths($row) as $path) {
        if (lireate_blog_web_path_is_file($path)) {
            return lireate_blog_resolve_cover_url($path);
        }
    }
    return '';
}
}

if (!function_exists('lireate_blog_render_service_footer')) {
function lireate_blog_render_service_footer()
{
    $doc = isset($_SERVER['DOCUMENT_ROOT']) ? rtrim((string) $_SERVER['DOCUMENT_ROOT'], '/') : '';
    $candidates = array();
    if ($doc !== '') {
        $candidates[] = $doc . '/template/lireate/lireate-service-footer.inc.php';
        $candidates[] = $doc . '/pages/services/_shared/lireate-service-footer.inc.php';
    }
    $candidates[] = lireate_blog_dir_up(__DIR__, 2) . '/services/_shared/lireate-service-footer.inc.php';
    $candidates[] = lireate_blog_dir_up(__DIR__, 3) . '/template/lireate/lireate-service-footer.inc.php';
    foreach ($candidates as $footer_path) {
        if ($footer_path !== '' && is_file($footer_path)) {
            include $footer_path;
            return true;
        }
    }
    return false;
}
}

if (!function_exists('lireate_blog_related_articles')) {
function lireate_blog_related_articles($current_url, $limit = 3)
{
    $current = '/' . trim((string) $current_url, '/');
    $data = lireate_blog_load_index();
    $rows = array();
    foreach ($data['articles'] as $row) {
        if (!is_array($row) || empty($row['url']) || empty($row['title'])) {
            continue;
        }
        $url = '/' . trim($row['url'], '/');
        if ($url === $current) {
            continue;
        }
        $rows[] = $row;
    }
    usort(
        $rows,
        function ($a, $b) {
            $da = lireate_blog_date_display_to_iso(isset($a['date_display']) ? $a['date_display'] : '');
            $db = lireate_blog_date_display_to_iso(isset($b['date_display']) ? $b['date_display'] : '');
            return strcmp($db, $da);
        }
    );
    return array_slice($rows, 0, max(0, (int) $limit));
}
}

if (!function_exists('lireate_blog_prepare_body_html')) {
function lireate_blog_prepare_body_html($html)
{
    return lireate_blog_ensure_h2_ids($html);
}
}

if (!function_exists('lireate_blog_html_has_collage_v1_cover_figure')) {
function lireate_blog_html_has_collage_v1_cover_figure($html)
{
    $html = (string) $html;
    if ($html === '') {
        return false;
    }
    if (preg_match('/<figure\b[^>]*\bclass="[^"]*\blx-article-cover\b[^"]*"[^>]*\bdata-cover-style="collage-v1"/i', $html)) {
        return true;
    }
    if (preg_match('/<figure\b[^>]*\bdata-cover-style="collage-v1"[^>]*\bclass="[^"]*\blx-article-cover\b/i', $html)) {
        return true;
    }
    return false;
}
}

if (!function_exists('lireate_blog_strip_collage_v1_cover_figure')) {
function lireate_blog_strip_collage_v1_cover_figure($html)
{
    $html = (string) $html;
    $patterns = array(
        '/<figure\b[^>]*\bclass="[^"]*\blx-article-cover\b[^"]*"[^>]*\bdata-cover-style="collage-v1"[^>]*>.*?<\/figure>\s*/is',
        '/<figure\b[^>]*\bdata-cover-style="collage-v1"[^>]*\bclass="[^"]*\blx-article-cover\b[^"]*"[^>]*>.*?<\/figure>\s*/is',
    );
    foreach ($patterns as $pattern) {
        $count = 0;
        $next = preg_replace($pattern, '', $html, 1, $count);
        if ($count > 0 && is_string($next)) {
            return $next;
        }
    }
    return $html;
}
}

if (!function_exists('lireate_blog_collage_cover_url_for_seo')) {
function lireate_blog_collage_cover_url_for_seo($seo_url)
{
    $seo = trim((string) $seo_url, '/');
    if ($seo === '') {
        return '';
    }
    $path = '/template/images/blog/' . $seo . '.jpg';
    if (lireate_blog_web_path_is_file($path)) {
        return lireate_blog_resolve_cover_url($path);
    }
    return '';
}
}

if (!function_exists('lireate_blog_extract_first_img_src')) {
function lireate_blog_extract_first_img_src($html)
{
    if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', (string) $html, $m)) {
        return $m[1];
    }
    return '';
}
}

/**
 * Collage-v1 cover: hero from /template/images/blog/{seo_url}.jpg or figure in body; strip duplicate figure.
 *
 * @return array{mode:bool,body:string,hero_html:string}
 */
if (!function_exists('lireate_blog_article_collage_prepare')) {
function lireate_blog_article_collage_prepare($row)
{
    $story = '';
    $cover_style = '';
    $seo = '';
    $title = '';
    if (is_array($row)) {
        $story = lireate_blog_list_row_get($row, array('full_story', 'text', 'story'), '');
        $cover_style = lireate_blog_list_row_get($row, array('cover_style'), '');
        $seo = lireate_blog_list_row_get($row, array('seo_url'), '');
        $title = lireate_blog_list_row_get($row, array('name', 'title'), '');
    }
    $has_marker = ($cover_style === 'collage-v1') || lireate_blog_html_has_collage_v1_cover_figure($story);
    if (!$has_marker) {
        return array(
            'mode' => false,
            'body' => $story,
            'hero_html' => '',
        );
    }
    $src = '';
    if (lireate_blog_html_has_collage_v1_cover_figure($story)) {
        if (preg_match(
            '/<figure\b[^>]*(?:class="[^"]*lx-article-cover[^"]*"[^>]*data-cover-style="collage-v1"|data-cover-style="collage-v1"[^>]*class="[^"]*lx-article-cover[^"]*")[^>]*>.*?<\/figure>/is',
            $story,
            $figure_match
        )) {
            $src = lireate_blog_extract_first_img_src($figure_match[0]);
            $story = lireate_blog_strip_collage_v1_cover_figure($story);
        }
    }
    if ($src === '' || strpos($src, '/template/images/blog/') === false) {
        $from_seo = lireate_blog_collage_cover_url_for_seo($seo);
        if ($from_seo !== '') {
            $src = $from_seo;
        }
    }
    if ($src === '') {
        return array(
            'mode' => false,
            'body' => $story,
            'hero_html' => '',
        );
    }
    $alt = $title !== '' ? $title : '';
    $hero = '<figure class="lireate-blog-collage-hero lx-article-cover" data-cover-style="collage-v1">'
        . '<img src="' . lireate_blog_h($src) . '" alt="' . lireate_blog_h($alt) . '"'
        . ' class="lireate-blog-collage-hero-img" width="860" height="400" loading="eager" decoding="async">'
        . '</figure>';
    return array(
        'mode' => true,
        'body' => $story,
        'hero_html' => $hero,
    );
}
}

if (!function_exists('lireate_blog_reading_time_minutes')) {
function lireate_blog_reading_time_minutes($html)
{
    $text = strip_tags((string) $html);
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = preg_replace('/\s+/u', ' ', trim($text));
    if ($text === '') {
        return 1;
    }
    $parts = preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY);
    $words = is_array($parts) ? count($parts) : 0;
    $wpm = defined('LIREATE_BLOG_READING_WPM') ? (int) LIREATE_BLOG_READING_WPM : 180;
    if ($wpm < 1) {
        $wpm = 180;
    }
    $mins = (int) ceil($words / $wpm);
    return $mins < 1 ? 1 : $mins;
}
}

if (!function_exists('lireate_blog_absolute_url')) {
function lireate_blog_absolute_url($path, $canonical_hint = '')
{
    $path = '/' . ltrim((string) $path, '/');
    $origin = defined('LIREATE_BLOG_PUBLIC_ORIGIN') ? trim((string) LIREATE_BLOG_PUBLIC_ORIGIN) : '';
    if ($origin !== '') {
        return rtrim($origin, '/') . $path;
    }
    $canonical_hint = trim((string) $canonical_hint);
    if ($canonical_hint !== '' && preg_match('#^https?://#i', $canonical_hint)) {
        $parts = parse_url($canonical_hint);
        if (!empty($parts['scheme']) && !empty($parts['host'])) {
            $base = $parts['scheme'] . '://' . $parts['host'];
            if (!empty($parts['port'])) {
                $base .= ':' . $parts['port'];
            }
            return $base . $path;
        }
    }
    return $path;
}
}

if (!function_exists('lireate_blog_article_jsonld')) {
function lireate_blog_article_jsonld($meta)
{
    $author = isset($meta['author']) && $meta['author'] !== ''
        ? $meta['author']
        : (defined('LIREATE_BLOG_AUTHOR_NAME') ? LIREATE_BLOG_AUTHOR_NAME : LIREATE_BLOG_DEFAULT_AUTHOR);
    $job_title = isset($meta['author_role']) && $meta['author_role'] !== ''
        ? $meta['author_role']
        : (defined('LIREATE_BLOG_AUTHOR_ROLE') ? LIREATE_BLOG_AUTHOR_ROLE : '');
    $avatar_path = defined('LIREATE_BLOG_AUTHOR_AVATAR_PATH') ? LIREATE_BLOG_AUTHOR_AVATAR_PATH : '';
    $canonical = isset($meta['canonical']) ? $meta['canonical'] : '';
    $author_image = $avatar_path !== ''
        ? lireate_blog_absolute_url($avatar_path . '.jpg', $canonical)
        : '';
    $iso = isset($meta['date_iso']) ? $meta['date_iso'] : '';
    if ($iso === '' && isset($meta['date_display'])) {
        $iso = lireate_blog_date_display_to_iso($meta['date_display']);
    }
    $modified = isset($meta['date_modified_iso']) && $meta['date_modified_iso'] !== ''
        ? $meta['date_modified_iso']
        : $iso;
    $headline = isset($meta['title']) ? $meta['title'] : '';
    $desc = isset($meta['description']) ? $meta['description'] : '';
    $url = isset($meta['canonical']) ? $meta['canonical'] : '';
    return array(
        '@context' => 'https://schema.org',
        '@type' => 'Article',
        'headline' => $headline,
        'description' => $desc,
        'author' => array_filter(
            array(
                '@type' => 'Person',
                'name' => $author,
                'jobTitle' => $job_title !== '' ? $job_title : null,
                'image' => $author_image !== '' ? $author_image : null,
            ),
            function ($v) {
                return $v !== null && $v !== '';
            }
        ),
        'datePublished' => $iso !== '' ? $iso : null,
        'dateModified' => $modified !== '' ? $modified : null,
        'mainEntityOfPage' => $url !== '' ? array('@type' => 'WebPage', '@id' => $url) : null,
        'publisher' => array(
            '@type' => 'Organization',
            'name' => 'LIREATE',
        ),
    );
}
}

/**
 * Рубрики /news (как на lireate.ru CMS).
 *
 * @return array<int, array{label:string,url:string,num?:int,category?:int}>
 */
if (!function_exists('lireate_blog_list_default_rubrics')) {
function lireate_blog_list_default_rubrics()
{
    return array(
        array('label' => 'Все категории', 'url' => '/news/'),
        array('label' => 'Новости', 'url' => '/news/?num=1&category=2', 'num' => 1, 'category' => 2),
        array('label' => 'Товарный знак', 'url' => '/news/?num=2&category=9', 'num' => 2, 'category' => 9),
        array('label' => 'Логотип', 'url' => '/news/?num=3&category=10', 'num' => 3, 'category' => 10),
        array('label' => 'Фирменный стиль', 'url' => '/news/?num=4&category=11', 'num' => 4, 'category' => 11),
        array('label' => 'Сайт', 'url' => '/news/?num=5&category=12', 'num' => 5, 'category' => 12),
        array('label' => 'Бренд', 'url' => '/news/?num=6&category=13', 'num' => 6, 'category' => 13),
        array('label' => 'Фирменное наименование', 'url' => '/news/?num=7&category=14', 'num' => 7, 'category' => 14),
        array('label' => 'Бизнес', 'url' => '/news/?num=8&category=15', 'num' => 8, 'category' => 15),
        array('label' => 'Франчайзинг и франшиза', 'url' => '/news/?num=9&category=16', 'num' => 9, 'category' => 16),
        array('label' => 'Авторское право', 'url' => '/news/?num=10&category=17', 'num' => 10, 'category' => 17),
        array('label' => 'Творческий псевдоним', 'url' => '/news/?num=11&category=18', 'num' => 11, 'category' => 18),
        array('label' => 'Программа ЭВМ', 'url' => '/news/?num=12&category=19', 'num' => 12, 'category' => 19),
        array('label' => 'Патент', 'url' => '/news/?num=13&category=20', 'num' => 13, 'category' => 20),
    );
}
}

if (!function_exists('lireate_blog_list_row_get')) {
function lireate_blog_list_row_get($row, $keys, $default = '')
{
    if (!is_array($row)) {
        return $default;
    }
    foreach ($keys as $key) {
        if (isset($row[$key]) && $row[$key] !== '' && $row[$key] !== null) {
            return $row[$key];
        }
    }
    return $default;
}
}

if (!function_exists('lireate_blog_list_format_date')) {
function lireate_blog_list_format_date($row)
{
    $raw = lireate_blog_list_row_get($row, array('date_display', 'date', 'datetime'), '');
    if (is_numeric($raw) && (int) $raw > 0) {
        return date('d.m.Y', (int) $raw);
    }
    $raw = trim((string) $raw);
    if ($raw !== '' && preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $raw)) {
        return $raw;
    }
    if ($raw !== '' && preg_match('/^\d{4}-\d{2}-\d{2}/', $raw)) {
        $ts = strtotime($raw);
        if ($ts) {
            return date('d.m.Y', $ts);
        }
    }
    return $raw;
}
}

if (!function_exists('lireate_blog_list_seo_path')) {
function lireate_blog_list_seo_path($row)
{
    $seo = lireate_blog_list_row_get($row, array('seo_url', 'url', 'slug'), '');
    $seo = trim((string) $seo, '/');
    if ($seo === '') {
        return '';
    }
    return '/' . $seo . '/';
}
}

if (!function_exists('lireate_blog_index_article_by_path')) {
function lireate_blog_index_article_by_path($path)
{
    static $map = null;
    $norm = '/' . trim((string) $path, '/') . '/';
    if ($norm === '//') {
        return null;
    }
    if ($map === null) {
        $map = array();
        foreach (lireate_blog_load_index()['articles'] as $article) {
            if (!is_array($article) || empty($article['url'])) {
                continue;
            }
            $key = '/' . trim((string) $article['url'], '/') . '/';
            $map[$key] = $article;
        }
    }
    return isset($map[$norm]) ? $map[$norm] : null;
}
}

/**
 * HTML для расчёта времени чтения: только full_story (CMS или index), не анонс.
 */
if (!function_exists('lireate_blog_list_body_for_reading_time')) {
function lireate_blog_list_body_for_reading_time($row)
{
    $body = lireate_blog_list_row_get($row, array('full_story', 'text', 'story'), '');
    if ($body !== '') {
        return (string) $body;
    }
    $path = lireate_blog_list_seo_path($row);
    if ($path !== '') {
        $idx = lireate_blog_index_article_by_path($path);
        if (is_array($idx)) {
            $from_idx = lireate_blog_list_row_get($idx, array('full_story', 'body_html'), '');
            if ($from_idx !== '') {
                return (string) $from_idx;
            }
        }
    }
    return '';
}
}

if (!function_exists('lireate_blog_list_read_mins_for_row')) {
function lireate_blog_list_read_mins_for_row($row)
{
    $preset = lireate_blog_list_row_get($row, array('reading_mins', 'read_mins'), '');
    if ($preset !== '' && (int) $preset > 0) {
        return (int) $preset;
    }
    $path = lireate_blog_list_seo_path($row);
    if ($path !== '') {
        $idx = lireate_blog_index_article_by_path($path);
        if (is_array($idx) && isset($idx['reading_mins']) && (int) $idx['reading_mins'] > 0) {
            return (int) $idx['reading_mins'];
        }
    }
    $body = lireate_blog_list_body_for_reading_time($row);
    if ($body !== '') {
        return lireate_blog_reading_time_minutes($body);
    }
    $title = lireate_blog_list_row_get($row, array('name', 'title', 'headline'), '');
    return lireate_blog_reading_time_minutes($title);
}
}

if (!function_exists('lireate_blog_list_excerpt_from_row')) {
function lireate_blog_list_excerpt_from_row($row)
{
    $text = lireate_blog_list_row_get(
        $row,
        array('excerpt', 'short_story', 'descr', 'description', 'announce', 'anons'),
        ''
    );
    if ($text !== '') {
        return trim(strip_tags((string) $text));
    }
    $full = lireate_blog_list_row_get($row, array('full_story', 'text'), '');
    if ($full === '') {
        return '';
    }
    $plain = trim(preg_replace('/\s+/u', ' ', strip_tags((string) $full)));
    if (lireate_blog_strlen($plain) <= 220) {
        return $plain;
    }
    return lireate_blog_substr($plain, 0, 217) . '…';
}
}

if (!function_exists('lireate_blog_list_normalize_row')) {
function lireate_blog_list_normalize_row($row)
{
    if (!is_array($row)) {
        return null;
    }
    $title = lireate_blog_list_row_get($row, array('name', 'title', 'headline'), '');
    $seo = lireate_blog_list_row_get($row, array('seo_url', 'url', 'slug'), '');
    if ($title === '' || $seo === '') {
        return null;
    }
    $seo = '/' . trim((string) $seo, '/') . '/';
    $tag = lireate_blog_list_row_get(
        $row,
        array('category_name', 'category', 'tag', 'rubric', 'cat_name'),
        ''
    );
    $tag_url = lireate_blog_list_row_get($row, array('category_url', 'cat_url', 'tag_url'), '');
    $author = lireate_blog_list_row_get($row, array('author', 'author_name'), '');
    if ($author === '') {
        $author = defined('LIREATE_BLOG_AUTHOR_NAME') ? LIREATE_BLOG_AUTHOR_NAME : LIREATE_BLOG_DEFAULT_AUTHOR;
    }
    $read_mins = lireate_blog_list_read_mins_for_row($row);
    $date_display = lireate_blog_list_format_date($row);
    return array(
        'url' => $seo,
        'title' => $title,
        'date_display' => $date_display,
        'date_iso' => lireate_blog_date_display_to_iso($date_display),
        'tag' => $tag,
        'tag_url' => $tag_url !== '' ? $tag_url : '/news/',
        'cover' => lireate_blog_list_cover_from_row($row),
        'excerpt' => lireate_blog_list_excerpt_from_row($row),
        'author' => $author,
        'read_mins' => $read_mins,
    );
}
}

/**
 * @param mixed $source
 * @return array<int, array<string, mixed>>
 */
if (!function_exists('lireate_blog_list_normalize_items')) {
function lireate_blog_list_normalize_items($source)
{
    $out = array();
    if (!is_array($source)) {
        return $out;
    }
    foreach ($source as $row) {
        $norm = lireate_blog_list_normalize_row($row);
        if ($norm !== null) {
            $out[] = $norm;
        }
    }
    return $out;
}
}

if (!function_exists('lireate_blog_list_items_from_index')) {
function lireate_blog_list_items_from_index()
{
    $data = lireate_blog_load_index();
    $items = array();
    foreach ($data['articles'] as $row) {
        if (!is_array($row)) {
            continue;
        }
        $norm = lireate_blog_list_normalize_row($row);
        if ($norm !== null) {
            $items[] = $norm;
        }
    }
    return $items;
}
}

/**
 * Собрать статьи из переменных CMS (список /news).
 *
 * @return array<int, array<string, mixed>>
 */
if (!function_exists('lireate_blog_list_collect_cms_items')) {
function lireate_blog_list_collect_cms_items()
{
    $bundle = lireate_blog_list_prepare_paginated_bundle();
    if (is_array($bundle) && !empty($bundle['items'])) {
        return $bundle['items'];
    }
    $items = lireate_blog_list_items_from_index();
    if (!empty($items)) {
        $per = (int) LIREATE_BLOG_LIST_PER_PAGE;
        $page = lireate_blog_list_page_from_request();
        $totalPages = max(1, (int) ceil(count($items) / $per));
        if ($page > $totalPages) {
            $page = $totalPages;
        }
        $offset = ($page - 1) * $per;
        $slice = array_slice($items, $offset, $per);
        $GLOBALS['lireate_blog_list_pagination'] = lireate_blog_list_build_pagination($page, $totalPages);
        $GLOBALS['lireate_blog_list_total_items'] = count($items);
        $GLOBALS['lireate_blog_list_total_pages'] = $totalPages;
        if ($page === 1 && !empty($slice)) {
            $GLOBALS['lireate_blog_list_featured'] = $slice[0];
        }
        return $slice;
    }
    return array();
}
}

if (!function_exists('lireate_blog_list_featured_from_cms')) {
function lireate_blog_list_featured_from_cms($items)
{
    if (isset($GLOBALS['lireate_blog_list_featured']) && is_array($GLOBALS['lireate_blog_list_featured'])) {
        $norm = lireate_blog_list_normalize_row($GLOBALS['lireate_blog_list_featured']);
        if ($norm !== null) {
            return $norm;
        }
    }
    $page = lireate_blog_list_page_from_request();
    if ($page !== 1) {
        return null;
    }
    if (!empty($items) && is_array($items)) {
        return $items[0];
    }
    return null;
}
}

if (!function_exists('lireate_blog_list_filters_from_cms')) {
function lireate_blog_list_filters_from_cms()
{
    if (isset($GLOBALS['lireate_blog_list_filters']) && is_array($GLOBALS['lireate_blog_list_filters'])) {
        return $GLOBALS['lireate_blog_list_filters'];
    }
    return lireate_blog_list_default_rubrics();
}
}

if (!function_exists('lireate_blog_list_pagination_from_cms')) {
function lireate_blog_list_pagination_from_cms()
{
    if (isset($GLOBALS['lireate_blog_list_pagination']) && is_array($GLOBALS['lireate_blog_list_pagination'])) {
        return $GLOBALS['lireate_blog_list_pagination'];
    }
    lireate_blog_list_prepare_paginated_bundle();
    if (isset($GLOBALS['lireate_blog_list_pagination']) && is_array($GLOBALS['lireate_blog_list_pagination'])) {
        return $GLOBALS['lireate_blog_list_pagination'];
    }
    $current = lireate_blog_list_page_from_request();
    $total = 1;
    foreach (array('count_page', 'total_pages', 'pages_count', 'max_page', 'lireate_blog_list_total_pages') as $key) {
        if (isset($GLOBALS[$key]) && is_numeric($GLOBALS[$key]) && (int) $GLOBALS[$key] > 0) {
            $total = (int) $GLOBALS[$key];
            break;
        }
    }
    return lireate_blog_list_build_pagination($current, $total);
}
}

if (!function_exists('lireate_blog_list_rubric_is_active')) {
function lireate_blog_list_rubric_is_active($rubric)
{
    $cat = isset($_GET['category']) ? (int) $_GET['category'] : 0;
    $num = isset($_GET['num']) ? (int) $_GET['num'] : 0;
    if (!is_array($rubric)) {
        return false;
    }
    if ($cat === 0 && $num === 0) {
        return !isset($rubric['category']) && !isset($rubric['num']);
    }
    if (isset($rubric['category']) && (int) $rubric['category'] === $cat) {
        return true;
    }
    if (isset($rubric['num']) && (int) $rubric['num'] === $num) {
        return true;
    }
    return false;
}
}

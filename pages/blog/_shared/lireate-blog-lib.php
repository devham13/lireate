<?php
/**
 * Хелперы рендера статей блога LIREATE.
 */
require_once __DIR__ . '/lireate-blog-config.php';

function lireate_blog_h($s)
{
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
}

function lireate_blog_strtolower($text)
{
    if (function_exists('mb_strtolower')) {
        return mb_strtolower($text, 'UTF-8');
    }
    return strtolower($text);
}

function lireate_blog_strlen($text)
{
    if (function_exists('mb_strlen')) {
        return mb_strlen($text, 'UTF-8');
    }
    return strlen($text);
}

function lireate_blog_substr($text, $start, $len = 1)
{
    if (function_exists('mb_substr')) {
        return mb_substr($text, $start, $len, 'UTF-8');
    }
    return substr($text, $start, $len);
}

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

function lireate_blog_date_display_to_iso($display)
{
    $display = trim((string) $display);
    if (preg_match('/^(\d{2})\.(\d{2})\.(\d{4})$/', $display, $m)) {
        return $m[3] . '-' . $m[2] . '-' . $m[1];
    }
    return '';
}

function lireate_blog_index_path()
{
    $candidates = array(
        dirname(__DIR__, 3) . '/data/blog-articles-index.json',
        (isset($_SERVER['DOCUMENT_ROOT']) ? rtrim($_SERVER['DOCUMENT_ROOT'], '/') : '') . '/data/blog-articles-index.json',
    );
    foreach ($candidates as $path) {
        if ($path !== '' && is_file($path)) {
            return $path;
        }
    }
    return $candidates[0];
}

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

function lireate_blog_public_origin()
{
    if (defined('LIREATE_BLOG_PUBLIC_ORIGIN') && LIREATE_BLOG_PUBLIC_ORIGIN !== '') {
        return rtrim((string) LIREATE_BLOG_PUBLIC_ORIGIN, '/');
    }
    return '';
}

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

function lireate_blog_render_service_footer()
{
    $doc = isset($_SERVER['DOCUMENT_ROOT']) ? rtrim((string) $_SERVER['DOCUMENT_ROOT'], '/') : '';
    $candidates = array();
    if ($doc !== '') {
        $candidates[] = $doc . '/template/lireate/lireate-service-footer.inc.php';
        $candidates[] = $doc . '/pages/services/_shared/lireate-service-footer.inc.php';
    }
    $candidates[] = dirname(__DIR__, 2) . '/services/_shared/lireate-service-footer.inc.php';
    $candidates[] = dirname(__DIR__, 3) . '/template/lireate/lireate-service-footer.inc.php';
    foreach ($candidates as $footer_path) {
        if ($footer_path !== '' && is_file($footer_path)) {
            include $footer_path;
            return true;
        }
    }
    return false;
}

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

function lireate_blog_prepare_body_html($html)
{
    return lireate_blog_ensure_h2_ids($html);
}

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

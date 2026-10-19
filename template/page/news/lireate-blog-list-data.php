<?php
/**
 * Список /news: загрузка из MySQL CMS, пагинация, подготовка карточек.
 * PHP 7 compatible.
 */

if (!defined('LIREATE_BLOG_LIST_PER_PAGE')) {
    define('LIREATE_BLOG_LIST_PER_PAGE', 12);
}

function lireate_blog_list_page_from_request()
{
    if (isset($_GET['page']) && is_numeric($_GET['page'])) {
        return max(1, (int) $_GET['page']);
    }
    if (isset($GLOBALS['page']) && is_numeric($GLOBALS['page'])) {
        return max(1, (int) $GLOBALS['page']);
    }
    return 1;
}

function lireate_blog_list_category_from_request()
{
    if (isset($_GET['category']) && is_numeric($_GET['category'])) {
        return (int) $_GET['category'];
    }
    return 0;
}

function lireate_blog_parse_db_constants($dbFile)
{
    $text = @file_get_contents($dbFile);
    if ($text === false) {
        return null;
    }
    $keys = array('DB_SERVER', 'DB_USER', 'DB_PASS', 'DB_NAME');
    $out = array();
    foreach ($keys as $key) {
        $pattern = '/define\s*\(\s*["\']' . $key . '["\']\s*,\s*["\']([^"\']*)["\']\s*\)/i';
        if (!preg_match($pattern, $text, $m)) {
            return null;
        }
        $out[$key] = $m[1];
    }
    return $out;
}

function lireate_blog_list_mysqli()
{
    if (function_exists('everestConnectDB')) {
        $db = everestConnectDB();
        if ($db instanceof mysqli) {
            return $db;
        }
    }
    $doc = lireate_blog_docroot();
    if ($doc === '') {
        return null;
    }
    $cfg = lireate_blog_parse_db_constants($doc . '/data/db.php');
    if ($cfg === null) {
        return null;
    }
    $mysqli = @new mysqli($cfg['DB_SERVER'], $cfg['DB_USER'], $cfg['DB_PASS'], $cfg['DB_NAME']);
    if ($mysqli->connect_errno) {
        return null;
    }
    if (!$mysqli->set_charset('utf8mb4')) {
        $mysqli->set_charset('utf8');
    }
    return $mysqli;
}

function lireate_blog_list_category_label_map()
{
    static $map = null;
    if ($map !== null) {
        return $map;
    }
    $map = array();
    foreach (lireate_blog_list_default_rubrics() as $rubric) {
        if (isset($rubric['category'])) {
            $map[(int) $rubric['category']] = $rubric['label'];
        }
    }
    return $map;
}

function lireate_blog_list_category_url_for_id($categoryId)
{
    foreach (lireate_blog_list_default_rubrics() as $rubric) {
        if (isset($rubric['category']) && (int) $rubric['category'] === (int) $categoryId) {
            return $rubric['url'];
        }
    }
    return '/news/?category=' . (int) $categoryId;
}

function lireate_blog_list_enrich_news_row($row)
{
    if (!is_array($row)) {
        return $row;
    }
    $catId = 0;
    if (isset($row['category'])) {
        $catId = (int) $row['category'];
    } elseif (isset($row['category_id'])) {
        $catId = (int) $row['category_id'];
    }
    if (empty($row['category_name']) && $catId > 0) {
        $map = lireate_blog_list_category_label_map();
        if (isset($map[$catId])) {
            $row['category_name'] = $map[$catId];
        }
    }
    if (empty($row['category_url']) && $catId > 0) {
        $row['category_url'] = lireate_blog_list_category_url_for_id($catId);
    }
    return $row;
}

function lireate_blog_list_try_get_news_rows($categoryId)
{
    if (!function_exists('get_news')) {
        return null;
    }
    $page = lireate_blog_list_page_from_request();
    $per = LIREATE_BLOG_LIST_PER_PAGE;
    $attempts = array(
        array($categoryId, $page, $per),
        array($categoryId, $page),
        array($page, $categoryId),
        array($categoryId),
        array(),
    );
    foreach ($attempts as $args) {
        $result = @call_user_func_array('get_news', $args);
        if (is_array($result) && !empty($result)) {
            $out = array();
            foreach ($result as $row) {
                $out[] = lireate_blog_list_enrich_news_row($row);
            }
            return $out;
        }
    }
    return null;
}

function lireate_blog_list_query_news_table($categoryId)
{
    $mysqli = lireate_blog_list_mysqli();
    if (!$mysqli instanceof mysqli) {
        return null;
    }
    $tables = array('news');
    $categoryTables = array('category', 'categories', 'news_category');
    $catTable = null;
    foreach ($categoryTables as $candidate) {
        $res = @$mysqli->query('SHOW TABLES LIKE "' . $mysqli->real_escape_string($candidate) . '"');
        if ($res && $res->num_rows > 0) {
            $catTable = $candidate;
            $res->free();
            break;
        }
        if ($res) {
            $res->free();
        }
    }
    $where = '`active` = 1';
    if ($categoryId > 0) {
        $where .= ' AND `category` = ' . (int) $categoryId;
    }
    $sql = 'SELECT `name`, `seo_url`, `short_story`, `full_story`, `category`, `datetime` FROM `news` WHERE ' . $where
        . ' ORDER BY `datetime` DESC, `id` DESC';
    $result = @$mysqli->query($sql);
    if (!$result) {
        return null;
    }
    $rows = array();
    while ($row = $result->fetch_assoc()) {
        if (!is_array($row)) {
            continue;
        }
        if (!empty($row['category']) && $catTable !== null) {
            $cid = (int) $row['category'];
            $catSql = 'SELECT `name` FROM `' . $catTable . '` WHERE `id` = ' . $cid . ' LIMIT 1';
            $catRes = @$mysqli->query($catSql);
            if ($catRes && ($catRow = $catRes->fetch_assoc()) && !empty($catRow['name'])) {
                $row['category_name'] = $catRow['name'];
            }
            if ($catRes) {
                $catRes->free();
            }
        }
        $rows[] = lireate_blog_list_enrich_news_row($row);
    }
    $result->free();
    return $rows;
}

function lireate_blog_list_raw_rows_from_globals()
{
    $best = null;
    $bestCount = 0;
    foreach (array('news_mass', 'news_list', 'news', 'news_article') as $key) {
        if (!isset($GLOBALS[$key]) || !is_array($GLOBALS[$key])) {
            continue;
        }
        $rows = $GLOBALS[$key];
        if ($key === 'news_article' && count($rows) <= 1) {
            continue;
        }
        $count = count($rows);
        if ($count > $bestCount) {
            $best = $rows;
            $bestCount = $count;
        }
    }
    if ($best === null) {
        return null;
    }
    $out = array();
    foreach ($best as $row) {
        $out[] = lireate_blog_list_enrich_news_row($row);
    }
    return $out;
}

function lireate_blog_list_fetch_all_raw_rows()
{
    $categoryId = lireate_blog_list_category_from_request();
    $rows = lireate_blog_list_query_news_table($categoryId);
    if (is_array($rows) && !empty($rows)) {
        return $rows;
    }
    $rows = lireate_blog_list_try_get_news_rows($categoryId);
    if (is_array($rows) && !empty($rows)) {
        return $rows;
    }
    $globalRows = lireate_blog_list_raw_rows_from_globals();
    if (is_array($globalRows) && !empty($globalRows)) {
        if ($categoryId > 0) {
            $filtered = array();
            foreach ($globalRows as $row) {
                $cid = 0;
                if (isset($row['category'])) {
                    $cid = (int) $row['category'];
                } elseif (isset($row['category_id'])) {
                    $cid = (int) $row['category_id'];
                }
                if ($cid === $categoryId) {
                    $filtered[] = $row;
                }
            }
            if (!empty($filtered)) {
                return $filtered;
            }
        }
        return $globalRows;
    }
    return null;
}

function lireate_blog_list_sort_rows_by_date($rows)
{
    if (!is_array($rows)) {
        return array();
    }
    usort(
        $rows,
        function ($a, $b) {
            $ta = isset($a['datetime']) ? (int) $a['datetime'] : 0;
            $tb = isset($b['datetime']) ? (int) $b['datetime'] : 0;
            if ($ta === $tb) {
                return 0;
            }
            return $ta > $tb ? -1 : 1;
        }
    );
    return $rows;
}

function lireate_blog_list_build_pagination($current, $totalPages)
{
    $totalPages = max(1, (int) $totalPages);
    $current = max(1, min((int) $current, $totalPages));
    if ($totalPages < 2) {
        return null;
    }
    $pages = array();
    $query = $_GET;
    unset($query['page']);
    $base = '/news/';
    for ($n = 1; $n <= $totalPages; $n++) {
        $query['page'] = $n;
        $pages[] = array(
            'num' => $n,
            'url' => $base . '?' . http_build_query($query),
            'current' => $n === $current,
        );
    }
    return array(
        'current' => $current,
        'total' => $totalPages,
        'pages' => $pages,
    );
}

function lireate_blog_list_prepare_paginated_bundle()
{
    static $bundle = null;
    static $done = false;
    if ($done) {
        return $bundle;
    }
    $done = true;
    $raw = lireate_blog_list_fetch_all_raw_rows();
    if (!is_array($raw) || empty($raw)) {
        $bundle = null;
        return null;
    }
    $raw = lireate_blog_list_sort_rows_by_date($raw);
    $per = (int) LIREATE_BLOG_LIST_PER_PAGE;
    if ($per < 1) {
        $per = 12;
    }
    $page = lireate_blog_list_page_from_request();
    $totalItems = count($raw);
    $totalPages = max(1, (int) ceil($totalItems / $per));
    if ($page > $totalPages) {
        $page = $totalPages;
    }
    $offset = ($page - 1) * $per;
    $slice = array_slice($raw, $offset, $per);
    $items = lireate_blog_list_normalize_items($slice);
    $featured = null;
    if ($page === 1 && !empty($items)) {
        $featured = $items[0];
    }
    $pagination = lireate_blog_list_build_pagination($page, $totalPages);
    $bundle = array(
        'items' => $items,
        'featured' => $featured,
        'pagination' => $pagination,
        'total_items' => $totalItems,
        'total_pages' => $totalPages,
        'page' => $page,
        'card_count' => count($items),
    );
    $GLOBALS['lireate_blog_list_pagination'] = $pagination;
    $GLOBALS['lireate_blog_list_total_items'] = $totalItems;
    $GLOBALS['lireate_blog_list_total_pages'] = $totalPages;
    if ($featured !== null) {
        $GLOBALS['lireate_blog_list_featured'] = $featured;
    }
    return $bundle;
}

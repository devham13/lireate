<?php
/**
 * Патч live HTML /kontaktyi/ для превью (вставка отрендеренного блока контактов).
 */
if (!function_exists('lireate_contacts_preview_absolutize_root_urls')) {
function lireate_contacts_preview_absolutize_root_urls($html, $origin)
{
    $origin = rtrim((string) $origin, '/');
    if ($origin === '') {
        return $html;
    }
    return preg_replace(
        '/(\s(?:href|src|action)=["\'])\/(?!\/)/i',
        '$1' . $origin . '/',
        $html
    );
}
}

if (!function_exists('lireate_contacts_preview_span_end')) {
function lireate_contacts_preview_span_end($html)
{
    if (!preg_match('/<div\s+class="title-ap"><h1>\s*Контакты\s+Лирейт\s*<\/h1>\s*<\/div>/iu', $html, $m, PREG_OFFSET_CAPTURE)) {
        return null;
    }
    $start = $m[0][1];
    if (!preg_match('/<div\s+class="row"\s+style="margin-bottom:\s*20px;"/iu', $html, $row, PREG_OFFSET_CAPTURE, $start)) {
        return null;
    }
    $divOpen = $row[0][1];
    $gt = strpos($html, '>', $divOpen);
    if ($gt === false) {
        return null;
    }
    $depth = 1;
    $i = $gt + 1;
    $len = strlen($html);
    while ($i < $len) {
        $nextDiv = stripos($html, '<div', $i);
        $nextClose = stripos($html, '</div>', $i);
        if ($nextClose === false) {
            return null;
        }
        if ($nextDiv !== false && $nextDiv < $nextClose) {
            $depth++;
            $gtInner = strpos($html, '>', $nextDiv);
            if ($gtInner === false) {
                return null;
            }
            $i = $gtInner + 1;
            continue;
        }
        $depth--;
        $i = $nextClose + 6;
        if ($depth === 0) {
            return array($start, $i);
        }
    }
    return null;
}
}

if (!function_exists('lireate_contacts_preview_patch_html')) {
function lireate_contacts_preview_patch_html($html, $inner_html)
{
    $marker = '<!-- lireate-contacts-preview:v1 -->';
    if (strpos($html, $marker) !== false) {
        return $html;
    }
    $span = lireate_contacts_preview_span_end($html);
    if ($span === null) {
        return $html;
    }
    list($start, $end) = $span;
    $section = '<section id="lireate-contacts">' . $marker . $inner_html . '</section>';
    $patched = substr($html, 0, $start) . $section . substr($html, $end);
    if (strpos($patched, 'lireate-contacts-body') === false) {
        if (preg_match('/<body\b[^>]*\bclass="([^"]*)"/i', $patched)) {
            $patched = preg_replace(
                '/(<body\b[^>]*\bclass=")([^"]*)(")/i',
                '$1$2 lireate-contacts-body$3',
                $patched,
                1
            );
        } else {
            $patched = preg_replace('/<body\b/i', '<body class="lireate-contacts-body"', $patched, 1);
        }
    }
    return $patched;
}
}

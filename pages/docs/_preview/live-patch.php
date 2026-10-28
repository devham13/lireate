<?php
if (!function_exists('lireate_docs_preview_absolutize_root_urls')) {
function lireate_docs_preview_absolutize_root_urls($html, $origin)
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

if (!function_exists('lireate_docs_preview_span_end')) {
function lireate_docs_preview_span_end($html)
{
    if (!preg_match('/<section\s+class="paddingSection\s+section_1">/iu', $html, $m, PREG_OFFSET_CAPTURE)) {
        return null;
    }
    $start = $m[0][1];
    $end = strripos($html, '</section>');
    if ($end === false || $end < $start) {
        return null;
    }
    return array($start, $end + 10);
}
}

if (!function_exists('lireate_docs_preview_patch_html')) {
function lireate_docs_preview_patch_html($html, $inner_html)
{
    $marker = '<!-- lireate-docs-preview:v1 -->';
    if (strpos($html, $marker) !== false) {
        return $html;
    }
    $span = lireate_docs_preview_span_end($html);
    if ($span === null) {
        return $html;
    }
    list($start, $end) = $span;
    $section = '<section id="lireate-docs">' . $marker . $inner_html . '</section>';
    $patched = substr($html, 0, $start) . $section . substr($html, $end);
    if (strpos($patched, 'lireate-docs-body') === false) {
        if (preg_match('/<body\b[^>]*\bclass="([^"]*)"/i', $patched)) {
            $patched = preg_replace(
                '/(<body\b[^>]*\bclass=")([^"]*)(")/i',
                '$1$2 lireate-docs-body$3',
                $patched,
                1
            );
        } else {
            $patched = preg_replace('/<body\b/i', '<body class="lireate-docs-body"', $patched, 1);
        }
    }
    return $patched;
}
}

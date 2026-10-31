<?php
/**
 * Патч live HTML /partners/ для превью (вставка отрендеренного блока партнёров).
 */
if (!function_exists('lireate_partners_preview_absolutize_root_urls')) {
function lireate_partners_preview_absolutize_root_urls($html, $origin)
{
    $origin = rtrim((string) $origin, '/');
    if ($origin === '') {
        return $html;
    }
    $html = preg_replace(
        '/(\s(?:href|src|action)=["\'])\/(?!\/)/i',
        '$1' . $origin . '/',
        $html
    );
    return preg_replace(
        '/(\sstyle="[^"]*background-image:url\()template\//i',
        '$1' . $origin . '/template/',
        $html
    );
}
}

if (!function_exists('lireate_partners_preview_span_end')) {
function lireate_partners_preview_span_end($html)
{
    if (!preg_match('/<section\s+class="pageTitle">.*?<h1\s+class="pageH1">\s*Наши\s+Партнёры\s*<\/h1>/ius', $html, $m, PREG_OFFSET_CAPTURE)) {
        return null;
    }
    $start = $m[0][1];
    $before = substr($html, max(0, $start - 20000), $start - max(0, $start - 20000));
    if (preg_match('/<style>.*?<\/style>\s*$/ius', $before, $st)) {
        if (strpos($st[0], '#partners') !== false || strpos($st[0], '.partner') !== false) {
            $start = $start - strlen($st[0]);
        }
    }
    if (!preg_match('/<section\s+id=["\']personsSlide["\'][^>]*>.*?<\/section>/ius', $html, $slide, PREG_OFFSET_CAPTURE, $start)) {
        return null;
    }
    $end = $slide[0][1] + strlen($slide[0][0]);
    return array($start, $end);
}
}

if (!function_exists('lireate_partners_preview_patch_html')) {
function lireate_partners_preview_patch_html($html, $inner_html)
{
    $marker = '<!-- lireate-partners-preview:v1 -->';
    if (strpos($html, $marker) !== false) {
        return $html;
    }
    $span = lireate_partners_preview_span_end($html);
    if ($span === null) {
        return $html;
    }
    list($start, $end) = $span;
    $section = '<section id="lireate-partners">' . $marker . $inner_html . '</section>';
    $patched = substr($html, 0, $start) . $section . substr($html, $end);
    if (strpos($patched, 'lireate-partners-body') === false) {
        if (preg_match('/<body\b[^>]*\bclass="([^"]*)"/i', $patched)) {
            $patched = preg_replace(
                '/(<body\b[^>]*\bclass=")([^"]*)(")/i',
                '$1$2 lireate-partners-body$3',
                $patched,
                1
            );
        } else {
            $patched = preg_replace('/<body\b/i', '<body class="lireate-partners-body"', $patched, 1);
        }
    }
    return $patched;
}
}

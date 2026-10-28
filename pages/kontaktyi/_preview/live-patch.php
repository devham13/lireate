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

if (!function_exists('lireate_contacts_preview_patch_html')) {
function lireate_contacts_preview_patch_html($html, $inner_html)
{
    $marker = '<!-- lireate-contacts-preview:v1 -->';
    if (strpos($html, $marker) !== false) {
        return $html;
    }
    $pattern = '/<div class="title-ap"><h1>\s*Контакты\s+Лирейт\s*<\/h1>\s*<\/div>.*?<div class="page_container">/su';
    $replacement = '<section id="lireate-contacts">' . $marker . $inner_html . '</section><div class="page_container">';
    $patched = preg_replace($pattern, $replacement, $html, 1, $count);
    if ($count < 1 || !is_string($patched)) {
        return $html;
    }
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

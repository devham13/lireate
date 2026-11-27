<?php
/**
 * Подключение Light V4 CSS для CMS-вставок (portfolio, reviews, …). PHP 5.6.
 */
if (!function_exists('lireate_cms_light_v4_print_css')) {
function lireate_cms_light_v4_print_css($extra_hrefs)
{
    static $printed = array();
    $v = '20261127r25';
    $base = array(
        '/template/css/lireate-cms-pages-light-v4.css',
    );
    if (is_array($extra_hrefs)) {
        $base = array_merge($base, $extra_hrefs);
    }
    foreach ($base as $href) {
        if (isset($printed[$href])) {
            continue;
        }
        $printed[$href] = true;
        echo '<link rel="stylesheet" href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8')
            . '?v=' . htmlspecialchars($v, ENT_QUOTES, 'UTF-8') . '">' . "\n";
    }
    echo "<script>if(document.body){document.body.classList.add('lireate-theme-light-v4');}</script>\n";
}
}
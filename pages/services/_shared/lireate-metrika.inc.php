<?php
/**
 * Yandex.Metrika для автономных страниц услуг LIREATE.
 * ID счётчика: переменная $lireate_metrika_id или env LIREATE_YANDEX_METRIKA_ID.
 * Цели reachGoal — assets/lireate-shared/lireate-metrika-goals.js
 */
if (defined('LIREATE_METRIKA_EMITTED')) {
    return;
}
if (!isset($lireate_metrika_id) || $lireate_metrika_id === '') {
    $lireate_metrika_id = getenv('LIREATE_YANDEX_METRIKA_ID') ?: '';
}
$lireate_metrika_id = preg_replace('/\D/', '', (string) $lireate_metrika_id);
if ($lireate_metrika_id === '') {
    return;
}
define('LIREATE_METRIKA_EMITTED', true);
?>
<!-- Yandex.Metrika counter -->
<script>
(function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
m[i].l=1*new Date();k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)})
(window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym");
ym(<?php echo (int) $lireate_metrika_id; ?>, "init", {
  clickmap: true,
  trackLinks: true,
  accurateTrackBounce: true,
  webvisor: true
});
</script>
<noscript><div><img src="https://mc.yandex.ru/watch/<?php echo (int) $lireate_metrika_id; ?>" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
<!-- /Yandex.Metrika counter -->

<?php
/**
 * Карта «рубрика / текст → услуга LIREATE» для блока CTA в статьях (без правки full_story).
 */
if (!function_exists('lireate_blog_service_catalog')) {
    function lireate_blog_service_catalog()
    {
        return array(
            array(
                'url' => '/registraciya_tovarnogo_znaka/',
                'title' => 'Регистрация товарного знака',
                'tags' => array('Товарный знак', 'Бренд', 'МКТУ'),
                'patterns' => '/товарн|знак|бренд|мкту|обозначен|роспатент|фипс/i',
            ),
            array(
                'url' => '/poisk_tovarnyh_znakov/',
                'title' => 'Поиск товарных знаков',
                'tags' => array('Товарный знак'),
                'patterns' => '/проверк.*знак|поиск.*знак|сходств/i',
            ),
            array(
                'url' => '/vozrazhenie_protiv_registracii_tovarnogo_znaka/',
                'title' => 'Возражение против регистрации ТЗ',
                'tags' => array('Товарный знак', 'Споры'),
                'patterns' => '/возражен|оспор|против регистрац/i',
            ),
            array(
                'url' => '/prodlenie_tovarnogo_znaka/',
                'title' => 'Продление товарного знака',
                'tags' => array('Товарный знак'),
                'patterns' => '/продлен|срок.*действ.*знак/i',
            ),
            array(
                'url' => '/patent',
                'trailing_slash' => false,
                'title' => 'Патентование изобретений',
                'tags' => array('Патент', 'Патенты'),
                'patterns' => '/патент|изобретен|полезн.*модел/i',
            ),
            array(
                'url' => '/zashchita_avtorskih_prav/',
                'title' => 'Защита авторских прав',
                'tags' => array('Авторское право', 'Контент'),
                'patterns' => '/авторск|правообладател|контент|логотип|дизайн/i',
            ),
            array(
                'url' => '/registraciya_tovarnogo_znaka_v_kitae/',
                'title' => 'Регистрация знака в Китае',
                'tags' => array('Международное', 'Товарный знак'),
                'patterns' => '/китай|мадрид|международн/i',
            ),
        );
    }
}

if (!function_exists('lireate_blog_service_for_article')) {
    function lireate_blog_service_for_article($title, $tag, $bodySnippet)
    {
        $title = (string) $title;
        $tag = (string) $tag;
        $bodySnippet = (string) $bodySnippet;
        $haystack = $title . "\n" . $tag . "\n" . mb_substr($bodySnippet, 0, 1200, 'UTF-8');
        $best = null;
        $bestScore = 0;
        foreach (lireate_blog_service_catalog() as $svc) {
            $score = 0;
            if ($tag !== '') {
                foreach ($svc['tags'] as $t) {
                    if (mb_stripos($tag, $t, 0, 'UTF-8') !== false || mb_stripos($t, $tag, 0, 'UTF-8') !== false) {
                        $score += 3;
                    }
                }
            }
            if (!empty($svc['patterns']) && preg_match($svc['patterns'], $haystack)) {
                $score += 2;
            }
            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $svc;
            }
        }
        if ($bestScore <= 0 || !is_array($best)) {
            return null;
        }
        $best['url'] = lireate_blog_service_href($best);
        return $best;
    }
}
<?php
define('LIREATE_USLUGI_VERSION', '2026-11-11-dark3');
define('LIREATE_USLUGI_ASSETS_BASE', '/template/page/uslugi');

/** Группы карточек хаба /uslugi/ (каждая карточка ведёт на страницу услуги). */
function lireate_uslugi_groups() {
    return [
        [
            'id' => 'lu-tz',
            'title' => 'Товарные знаки',
            'lead' => 'От проверки названия до свидетельства и защиты знака.',
            'cards' => [
                [
                    'title' => 'Регистрация товарного знака',
                    'href' => '/registraciya_tovarnogo_znaka/',
                    'tag' => 'Под ключ',
                    'text' => 'Проверка обозначения, подготовка заявки в Роспатент и ответы на запросы экспертизы. Подбираем классы МКТУ под ваш бизнес.',
                ],
                [
                    'title' => 'Регистрация с оплатой за результат',
                    'href' => '/registraciya_tovarnogo_znaka_s_oplatoy_za_rezultat/',
                    'tag' => 'Оплата после решения',
                    'text' => 'Основная часть оплаты — после положительного решения. Подходит, когда важно снизить риски на старте.',
                ],
                [
                    'title' => 'Проверка товарного знака',
                    'href' => '/findtz/',
                    'tag' => 'Поиск по реестру',
                    'text' => 'Поиск по базам Роспатента и оценка риска отказа до подачи заявки. Понятный отчёт для предпринимателя и юриста.',
                ],
                [
                    'title' => 'Продление товарного знака',
                    'href' => '/prodlenie_tovarnogo_znaka/',
                    'tag' => 'Сроки охраны',
                    'text' => 'Следим за сроком действия регистрации, готовим документы и подаём на продление без пропуска дат.',
                ],
                [
                    'title' => 'Возражение против регистрации',
                    'href' => '/vozrazhenie_protiv_registracii_tovarnogo_znaka/',
                    'tag' => 'Споры в ППС',
                    'text' => 'Если конкурент регистрирует сходное обозначение: готовим правовую позицию и ведём процедуру в Палате по патентным спорам.',
                ],
                [
                    'title' => 'Регистрация знака в Китае',
                    'href' => '/registraciya_tovarnogo_znaka_v_kitae/',
                    'tag' => 'КНР',
                    'text' => 'Выход на рынок Китая: подбор классов, подача и сопровождение заявки. Помогаем выстроить охрану бренда в Азии.',
                ],
                [
                    'title' => 'МКТУ и классы товаров и услуг',
                    'href' => '/mktu',
                    'tag' => 'Классификатор',
                    'text' => 'Подбор классов МКТУ и разбор пересечений с конкурентами — основа корректной заявки на знак.',
                ],
                [
                    'title' => 'Регистрация товарного знака: классическая схема',
                    'href' => '/registraciya_tovarnogo_znaka/',
                    'tag' => 'Этапы и стоимость',
                    'text' => 'Этапы регистрации, стоимость и формат работы с бюро — на одной странице.',
                ],
            ],
        ],
        [
            'id' => 'lu-patent',
            'title' => 'Патенты и авторское право',
            'lead' => 'Охрана технических решений, дизайна и контента.',
            'cards' => [
                [
                    'title' => 'Патентование изобретений',
                    'href' => '/patent',
                    'tag' => 'Изобретения',
                    'text' => 'Анализ технического решения, подготовка заявки и ведение делопроизводства до выдачи патента в России.',
                ],
                [
                    'title' => 'Патент на промышленный образец',
                    'href' => '/patent_prom_obr',
                    'tag' => 'Дизайн изделия',
                    'text' => 'Охрана внешнего вида изделия. Помогаем оформить права на продукт до выхода на рынок.',
                ],
                [
                    'title' => 'Защита авторских прав',
                    'href' => '/zashchita_avtorskih_prav/',
                    'tag' => 'Контент и дизайн',
                    'text' => 'Фиксация прав, досудебные претензии и сопровождение споров. Тексты, дизайн, видео и цифровые продукты.',
                ],
            ],
        ],
    ];
}

/** Плоский список (совместимость со старым кодом). */
function lireate_uslugi_cards() {
    $out = [];
    foreach (lireate_uslugi_groups() as $g) {
        foreach ($g['cards'] as $c) {
            $out[] = $c;
        }
    }
    return $out;
}

function lireate_uslugi_h($s) {
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
}

/** Мессенджеры: те же адреса, что на лендингах услуг. */
function lireate_uslugi_messengers() {
    $max = 'https://max.ru/u/f9LHodD0cOI8jsPagjibEve2cn5_xPW0X1sf3PhbS5TMijA78DnUeDEpUAI';
    $urls = $_SERVER['DOCUMENT_ROOT'] . '/template/page/news/lireate-messenger-urls.php';
    if (is_file($urls)) {
        require_once $urls;
    }
    if (defined('LIREATE_MAX_CHAT_URL')) {
        $max = LIREATE_MAX_CHAT_URL;
    }
    return [
        'tg' => 'https://t.me/lireate_patent',
        'wa' => 'https://wa.me/79600484957',
        'max' => $max,
    ];
}

function lireate_uslugi_icon($name) {
    $icons = [
        'tg' => '<svg class="lu-ico" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M9.78 18.65l.28-4.23 7.68-6.92c.34-.31-.07-.46-.52-.19L7.74 13.3 3.64 12c-.88-.25-.89-.86.2-1.3l15.97-6.16c.73-.33 1.43.18 1.15 1.3l-2.72 12.81c-.19.91-.74 1.13-1.5.71L12.6 16.3l-1.99 1.93c-.23.23-.42.42-.83.42z"/></svg>',
        'wa' => '<svg class="lu-ico" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M12 2C6.5 2 2 6.2 2 11.4c0 1.9.5 3.7 1.5 5.3L2 22l5.6-1.5c1.5.8 3.2 1.3 4.9 1.3 5.5 0 10-4.2 10-9.4S17.5 2 12 2zm5.8 13.5c-.2.6-1.2 1.1-1.7 1.1-.4 0-1.1.2-3.6-.9-3-1.3-5-4.4-5.1-4.6-.2-.3-1.2-1.6-1.2-3.1 0-1.5.8-2.2 1.1-2.5.3-.3.6-.4.8-.4.2 0 .4 0 .6.1.2 0 .4.2.5.5.2.5.6 1.7.7 1.8.1.1.1.3 0 .4 0 .1-.1.2-.2.3l-.3.3c-.1.1-.2.2-.1.4.1.2.5 1 1.1 1.6.8.7 1.5 1 1.7 1.1.2.1.3 0 .4-.1l.6-.7c.1-.2.3-.2.5-.1.8.3 2.8 1.3 3.2 1.5.3.2.5.3.6.4.1.2.1.7-.1 1.3z"/></svg>',
        'max' => '<svg class="lu-ico" viewBox="0 0 100 100" aria-hidden="true" focusable="false"><path fill="currentColor" d="M50.76 0.26c27.54 0 49.13 22.34 49.13 49.89S77.61 99.49 51.02 99.49c-9.44 0-14.01-1.33-21.37-6.54-.51-.36-1.2-.27-1.63.19-5.66 6.04-20.17 10.28-20.83 2.03 0-14.39-7.19-23.73-7.19-45.3C0 21.55 23.22 0.26 50.76 0.26zm.77 24.55c-13.06-.69-23.26 8.39-25.51 22.57-1.86 11.75 1.44 26.07 4.27 26.79 1.2.31 4.08-1.88 6.18-3.86.39-.37 1-.44 1.46-.15 3.27 2 6.97 3.5 11.05 3.71 13.41.7 25.3-9.8 26.01-23.21.7-13.41-10.02-25.14-23.43-25.84z"/></svg>',
        'lead' => '<svg class="lu-ico" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" d="M9 4h6a1 1 0 0 1 1 1v1H8V5a1 1 0 0 1 1-1zM8 5H6.5A1.5 1.5 0 0 0 5 6.5v13A1.5 1.5 0 0 0 6.5 21h11a1.5 1.5 0 0 0 1.5-1.5v-13A1.5 1.5 0 0 0 17.5 5H16M9 11h6M9 15h4"/></svg>',
        'arrow' => '<svg class="lu-ico lu-ico--arrow" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>',
    ];
    return isset($icons[$name]) ? $icons[$name] : '';
}

/** Подвал хаба /uslugi/ (Light V4 при lireate-theme-light-v4). */
function lireate_uslugi_render_footer() {
    $footer = $_SERVER['DOCUMENT_ROOT'] . '/template/page/news/lireate-blog-site-footer.inc.php';
    $lv4 = defined('LIREATE_LIGHT_V4_ASSET_VERSION') ? LIREATE_LIGHT_V4_ASSET_VERSION : '20261126r21';
    $css_rel = '/template/page/news/lireate-blog-footer-light-v4.css';
    $css = $_SERVER['DOCUMENT_ROOT'] . $css_rel;
    if (!is_file($css)) {
        $css_rel = '/template/page/news/lireate-blog-footer.css';
        $css = $_SERVER['DOCUMENT_ROOT'] . $css_rel;
    }
    if (is_file($css)) {
        echo '<!-- lireate_fg:uslugi-footer -->';
        echo '<link rel="stylesheet" href="' . $css_rel . '?v=' . lireate_uslugi_h($lv4) . '">';
    }
    if (is_file($footer)) {
        include $footer;
        return true;
    }
    return false;
}

/** Старое имя оставлено для совместимости. */
function lireate_uslugi_require_blog_footer() {
    return lireate_uslugi_render_footer();
}

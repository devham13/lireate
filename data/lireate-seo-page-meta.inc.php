<?php
/**
 * Статический title / description / og:image для CMS-страниц (когда правка БД отложена или для канона).
 */
if (!function_exists('lireate_seo_load_json_meta')) {
    function lireate_seo_load_json_meta($filename)
    {
        $dataDir = dirname(__FILE__);
        $path = $dataDir . '/' . $filename;
        if (!is_file($path)) {
            return array();
        }
        $raw = file_get_contents($path);
        if ($raw === false || $raw === '') {
            return array();
        }
        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : array();
    }
}

if (!function_exists('lireate_seo_mktu_class_meta')) {
    function lireate_seo_mktu_class_meta($classNum)
    {
        static $byClass = null;
        if ($byClass === null) {
            $byClass = lireate_seo_load_json_meta('lireate-seo-mktu-classes.json');
        }
        $key = (string) (int) $classNum;
        return isset($byClass[$key]) && is_array($byClass[$key]) ? $byClass[$key] : null;
    }
}

if (!function_exists('lireate_seo_static_page_meta')) {
    function lireate_seo_static_page_meta($slug)
    {
        $slug = trim((string) $slug, '/');
        if ($slug === '') {
            $slug = 'home';
        }
        if (preg_match('/^(\d{1,2})$/', $slug, $m)) {
            $mktu = lireate_seo_mktu_class_meta($m[1]);
            if (is_array($mktu)) {
                return $mktu;
            }
        }
        $pages = array(
            'avtor' => array(
                'title' => 'Юрий Горбачев — директор патентного бюро Лирейт',
                'description' => 'Профессиональный профиль директора патентного бюро Лирейт: специализация, публикации и опыт по интеллектуальным правам.',
                'image' => '/template/images/team/person/11.jpg',
                'cluster' => 'профиль директора',
            ),
            // audit07 C1: description only (title stays from $seo_arr)
            'portfolio' => array(
                'title' => 'Портфолио Лирейт — кейсы регистрации товарных знаков',
                'description' => 'Примеры работ патентного бюро Лирейт: зарегистрированные товарные знаки, кейсы клиентов и ссылки на свидетельства в реестре ФИПС.',
                'cluster' => 'кейсы регистрации',
            ),
            // LIREATE 2026-11-17: /contacts/ fell back to config.php default title
            'contacts' => array(
                'title' => 'Контакты патентного бюро Лирейт — телефоны и адрес',
                'description' => 'Как связаться с патентным бюро Лирейт: телефоны, мессенджеры, адрес офиса в Зеленодольске и консультации по России.',
                'cluster' => 'контакты Лирейт',
            ),
            // audit08 S2 (2026-11-18): generic CMS descriptions -> facts visible on the page
            'team' => array(
                'title' => 'Команда Лирейт — специалисты по интеллектуальным правам',
                'description' => 'Познакомьтесь с командой патентного бюро Лирейт: специализация, опыт, квалификация и направления работы специалистов.',
                'cluster' => 'команда Лирейт',
            ),
            'reviews' => array(
                'title' => 'Видеоотзывы клиентов о регистрации товарных знаков — Лирейт',
                'description' => 'Видеоотзывы клиентов патентного бюро «Лирейт» о регистрации товарных знаков: компании из разных городов России, ссылки на кейсы и номера свидетельств в реестре ФИПС.',
            ),
            'docs' => array(
                'title' => 'Документы и реквизиты ООО «ЛИРЕЙТ»',
                'description' => 'Документы ООО «ЛИРЕЙТ»: свидетельство о постановке на налоговый учёт, свидетельства патентного поверенного и на товарный знак «Лирейт», реквизиты компании.',
            ),
            'news' => array(
                'title' => 'Блог Лирейт — товарные знаки, патенты и защита прав',
                'description' => 'Практические статьи по интеллектуальной собственности: товарные знаки, патенты, авторские права, договоры и споры.',
                'cluster' => 'блог об ИС (статьи — lireate-news)',
            ),
            // audit07 C4 cases wave 1 (2026-11-17): /portfolio/<slug>/
            'portfolio/svoya-kultura' => array(
                'title' => 'Товарный знак «Своя Культура» — кейс и видеоотзыв | Лирейт',
                'description' => 'Регистрация товарного знака «Своя Культура» (г. Коломна): свидетельство № 658340, заявка 30.05.2017, регистрация 31.05.2018, срок 12 мес. Видеоотзыв клиента.',
            ),
            'portfolio/bentax' => array(
                'title' => 'Товарные знаки «Bentax» и «Shtainberg Chemy» — кейс и видеоотзыв | Лирейт',
                'description' => 'Регистрация товарных знаков «Bentax» и «Shtainberg Chemy» (г. Новосибирск): свидетельства № 739063 и № 739064 в реестре ФИПС. Видеоотзыв клиента.',
            ),
            'portfolio/avtopravo' => array(
                'title' => 'Товарный знак «Автоправо» — кейс и видеоотзыв | Лирейт',
                'description' => 'Регистрация товарного знака «Автоправо» (г. Казань): свидетельство № 742798, заявка 01.04.2019, регистрация 20.01.2020, срок 10 мес. Видеоотзыв клиента.',
            ),
            'portfolio/kingsname' => array(
                'title' => 'Товарный знак «KINGSNAME» — кейс и видеоотзыв | Лирейт',
                'description' => 'Регистрация товарного знака «KINGSNAME» (г. Грозный): свидетельство № 747597, заявка 15.03.2019, регистрация 18.02.2020, срок 12 мес. Видеоотзыв клиента.',
            ),
            'portfolio/doza' => array(
                'title' => 'Товарный знак «Doza» — кейс и видеоотзыв | Лирейт',
                'description' => 'Регистрация товарного знака «Doza» (г. Нальчик): свидетельство № 746998, заявка 04.06.2019, регистрация 13.02.2020, срок 8 мес. Видеоотзыв клиента.',
            ),
            'portfolio/happy-mind' => array(
                'title' => 'Товарный знак «Happy Mind» — кейс и видеоотзыв | Лирейт',
                'description' => 'Регистрация товарного знака «Happy Mind» (г. Москва): свидетельство № 749295, заявка 18.06.2019, регистрация 04.03.2020, срок 9 мес. Видеоотзыв клиента.',
            ),
            'portfolio/elisa-hotels' => array(
                'title' => 'Товарный знак «Elisa Hotels & Resorts» — кейс и видеоотзыв | Лирейт',
                'description' => 'Регистрация товарного знака «Elisa Hotels & Resorts» (г. Зеленоградск): свидетельство № 749133, заявка 29.07.2019, регистрация 03.03.2020, срок 8 мес. Видеоотзыв клиента.',
            ),
            'portfolio/strigoon' => array(
                'title' => 'Товарный знак «Strigoon» — кейс и видеоотзыв | Лирейт',
                'description' => 'Регистрация товарного знака «Strigoon» (с. Иглино): свидетельство № 746223, заявка 01.08.2019, регистрация 11.02.2020, срок 6 мес. Видеоотзыв клиента.',
            ),
            'portfolio/avenue-deluxe' => array(
                'title' => 'Товарный знак «Avenue deluxe» — кейс и видеоотзыв | Лирейт',
                'description' => 'Регистрация товарного знака «Avenue deluxe» (г. Череповец): свидетельство № 737337, заявка 26.04.2019, регистрация 04.12.2019, срок 8 мес. Видеоотзыв клиента.',
            ),
            'portfolio/emiz' => array(
                'title' => 'Товарный знак «Эмиз» — кейс и видеоотзыв | Лирейт',
                'description' => 'Регистрация товарного знака «Эмиз» (г. Казань): свидетельство № 743517, заявка 18.06.2019, регистрация 23.01.2020, срок 7 мес. Видеоотзыв клиента.',
            ),
            // audit07 cases wave 2 (2026-11-18)
            'portfolio/nerud-grupp' => array(
                'title' => 'Товарный знак «Неруд Групп» — кейс и видеоотзыв | Лирейт',
                'description' => 'Регистрация товарного знака «Неруд Групп» (г. Тула): свидетельство № 720437, заявка 24.12.2018, регистрация 17.07.2019, срок 7 мес. Видеоотзыв клиента.',
            ),
            'portfolio/estetic' => array(
                'title' => 'Товарный знак «Estetic» — кейс и видеоотзыв | Лирейт',
                'description' => 'Регистрация товарного знака «Estetic» (г. Краснодар): свидетельство № 721633, заявка 11.12.2018, регистрация 02.08.2019, срок 7 мес. Видеоотзыв клиента.',
            ),
            'portfolio/berti' => array(
                'title' => 'Товарный знак «Berti» — кейс и видеоотзыв | Лирейт',
                'description' => 'Регистрация товарного знака «Berti» (с. Курчалой): свидетельство № 713201, заявка 31.08.2018, регистрация 23.05.2019, срок 9 мес. Видеоотзыв клиента.',
            ),
            'portfolio/vf' => array(
                'title' => 'Товарный знак «VF» — кейс и видеоотзыв | Лирейт',
                'description' => 'Регистрация товарного знака «VF» (г. Владимир): свидетельство № 698481, заявка 08.06.2018, регистрация 15.02.2019, срок 9 мес. Видеоотзыв клиента.',
            ),
        );
        $jsonOverrides = lireate_seo_load_json_meta('lireate-seo-cms-overrides.json');
        if (is_array($jsonOverrides)) {
            foreach ($jsonOverrides as $key => $meta) {
                if (!is_string($key) || !is_array($meta)) {
                    continue;
                }
                $k = trim($key, '/');
                if (!isset($pages[$k])) {
                    $pages[$k] = $meta;
                } else {
                    $pages[$k] = array_merge($pages[$k], $meta);
                }
            }
        }
        return isset($pages[$slug]) ? $pages[$slug] : null;
    }
}

if (!function_exists('lireate_seo_apply_static_page_meta_to_cfg')) {
    /**
     * Вызывать из lireate-seo-pre-template.inc.php (после сборки $cfg из index.php).
     *
     * @param string $slug
     * @param array  $cfg       ссылка на $cfg
     * @param string $seo_title ссылка
     * @param string $seo_description ссылка
     */
    function lireate_seo_apply_static_page_meta_to_cfg($slug, &$cfg, &$seo_title, &$seo_description)
    {
        $meta = lireate_seo_static_page_meta($slug);
        if (!is_array($meta)) {
            return false;
        }
        if (!empty($meta['title'])) {
            $cfg['title'] = $meta['title'];
            $seo_title = $meta['title'];
        }
        if (!empty($meta['description'])) {
            $cfg['description'] = $meta['description'];
            $seo_description = $meta['description'];
        }
        return true;
    }
}

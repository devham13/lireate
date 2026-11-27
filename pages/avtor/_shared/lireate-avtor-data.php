<?php
/**
 * Контент страницы /avtor/ (факты с продакшена, без выдумок).
 */

if (!function_exists('lireate_avtor_specializations')) {
    function lireate_avtor_specializations()
    {
        return array(
            'товарные знаки: проверка обозначений, регистрация, споры;',
            'патентное право: изобретения, полезные модели, промышленные образцы;',
            'авторское право, смежные права и программы для ЭВМ;',
            'договоры отчуждения и лицензии на объекты интеллектуальной собственности;',
            'представительство в спорах, в том числе в суде;',
            'реклама и недобросовестная конкуренция.',
        );
    }
}

if (!function_exists('lireate_avtor_company_facts')) {
    function lireate_avtor_company_facts()
    {
        return array(
            'ООО «ЛИРЕЙТ» зарегистрировано 6 июля 2015 года. Основной вид деятельности по ЕГРЮЛ — деятельность в области права.',
            'По данным компании, она работает в сфере охраны интеллектуальной собственности с 2005 года. Начинала в составе юридической компании «Энтер».',
            'В команде 15 специалистов: юристы по регистрации ИС и сотрудники патентного отдела.',
            'В группе ВКонтакте с ноября 2024 по ноябрь 2026 года опубликовано 133 разбора дел бюро: регистрация знаков, защита от претензий и исков, взыскание компенсаций, патенты.',
        );
    }
}

if (!function_exists('lireate_avtor_publications')) {
    function lireate_avtor_publications()
    {
        return array(
            array(
                'href' => 'https://vc.ru/id694569',
                'title' => 'vc.ru',
                'text' => 'Например обзор дела СИП-236/2026 о знаке «Капитал»',
            ),
            array(
                'href' => 'https://t-j.ru/user2640218/',
                'title' => 'Т—Ж, «Сообщество Про»',
                'text' => 'В том числе статья «Регистрация ООО не защищает название бизнеса как бренд»',
            ),
            array(
                'href' => 'https://t-j.ru/law-practice-registratsiia-ooo-ne-zashchishchaet-nazvanie/',
                'title' => 'Т—Ж — регистрация ООО и бренд',
                'text' => 'law-practice-registratsiia-ooo-ne-zashchishchaet-nazvanie',
            ),
            array(
                'href' => 'https://www.klerk.ru/author/6739/',
                'title' => 'Клерк.ру',
                'text' => 'Страница автора',
            ),
            array(
                'href' => 'https://habr.com/ru/articles/813959/',
                'title' => 'Хабр',
                'text' => 'Разработка и регистрация товарных знаков',
            ),
            array(
                'href' => 'https://habr.com/ru/articles/894612/',
                'title' => 'Хабр — сторонние компоненты в ПО',
                'text' => 'Статья на Habr',
            ),
            array(
                'href' => 'https://tenchat.ru/gorbachev_yuriy',
                'title' => 'TenChat',
                'text' => 'Профиль Юрия Горбачева',
            ),
            array(
                'href' => 'https://vk.com/lireate',
                'title' => 'ВКонтакте — «Лирейт — патентное бюро»',
                'text' => 'Кейсы бюро',
            ),
        );
    }
}

if (!function_exists('lireate_avtor_person_same_as')) {
    function lireate_avtor_person_same_as()
    {
        return array(
            'https://vc.ru/id694569',
            'https://t-j.ru/user2640218/',
            'https://www.klerk.ru/author/6739/',
            'https://tenchat.ru/gorbachev_yuriy',
            'https://www.cossa.ru/profile/?ID=259138',
        );
    }
}
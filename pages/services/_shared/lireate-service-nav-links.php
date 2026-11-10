<?php
/**
 * Проверенные (HTTP 200) страницы услуг для выпадающего списка «Услуги».
 * Обновляйте список после curl-проверки перед добавлением URL.
 */
if (!function_exists('lireate_service_nav_links')) {
    function lireate_service_nav_links()
    {
        return [
            ['href' => '/findtz/', 'label' => 'Проверка товарного знака'],
            ['href' => '/registraciya_tovarnogo_znaka/', 'label' => 'Регистрация товарного знака'],
            ['href' => '/registraciya_tovarnogo_znaka_s_oplatoy_za_rezultat/', 'label' => 'Регистрация с оплатой за результат'],
            ['href' => '/prodlenie_tovarnogo_znaka/', 'label' => 'Продление товарного знака'],
            ['href' => '/registraciya_tovarnogo_znaka_v_kitae/', 'label' => 'Регистрация товарного знака в Китае'],
            ['href' => '/vozrazhenie_protiv_registracii_tovarnogo_znaka/', 'label' => 'Возражение против регистрации ТЗ'],
            ['href' => '/zashchita_avtorskih_prav/', 'label' => 'Защита авторских прав'],
        ];
    }
}

if (!function_exists('lireate_breadcrumb_schema_items')) {
    /**
     * Элементы BreadcrumbList без битого URL /uslugi/.
     *
     * @return array<int, array<string, mixed>>
     */
    function lireate_breadcrumb_schema_items($site_root, $current_name, $canonical)
    {
        return [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Главная', 'item' => rtrim($site_root, '/') . '/'],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Услуги'],
            ['@type' => 'ListItem', 'position' => 3, 'name' => $current_name, 'item' => $canonical],
        ];
    }
}

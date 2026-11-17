<?php
if (!function_exists('lireate_patent_breadcrumb_schema_items')) {
    function lireate_patent_breadcrumb_schema_items($site_root, $tool_name, $canonical)
    {
        $root = rtrim($site_root, '/');
        return [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Главная', 'item' => $root . '/'],
            [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => 'Патентные задачи',
                'item' => $root . '/patentnye-zadachi/',
            ],
            ['@type' => 'ListItem', 'position' => 3, 'name' => $tool_name, 'item' => $canonical],
        ];
    }
}
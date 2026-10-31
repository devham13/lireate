<?php
/**
 * Состав команды — единый источник lireate_contacts_team_members() (15 человек).
 */
if (!function_exists('lireate_team_members')) {
function lireate_team_members()
{
    $contacts_data = __DIR__ . '/../../kontaktyi/_shared/lireate-contacts-data.php';
    if (is_file($contacts_data)) {
        require_once $contacts_data;
    }
    if (function_exists('lireate_contacts_team_members')) {
        return lireate_contacts_team_members();
    }
    return array();
}
}

if (!function_exists('lireate_team_page_intro')) {
function lireate_team_page_intro()
{
    return 'Больше 10 человек объединились, чтобы помочь вам безопасно заниматься бизнесом.';
}
}

if (!function_exists('lireate_team_page_tagline')) {
function lireate_team_page_tagline()
{
    return 'Мы работаем с любовью';
}
}

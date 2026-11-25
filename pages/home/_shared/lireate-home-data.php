<?php
/**
 * Данные главной: команда (reuse contacts), этапы процесса.
 */
$lh_contacts_data = dirname(dirname(dirname(__DIR__))) . '/template/page/kontaktyi/lireate-contacts-data.php';
if (is_file($lh_contacts_data)) {
    require_once $lh_contacts_data;
}

if (!function_exists('lireate_home_team_group')) {
function lireate_home_team_group($role)
{
    $role_l = mb_strtolower((string) $role, 'UTF-8');
    if (strpos($role_l, 'партн') !== false || strpos($role_l, 'управляющ') !== false) {
        return 'management';
    }
    if (strpos($role_l, 'патент') !== false) {
        return 'patent';
    }
    if (strpos($role_l, 'регистрац') !== false || strpos($role_l, 'делопроизвод') !== false) {
        return 'registration';
    }
    return 'registration';
}
}

if (!function_exists('lireate_home_team_tag')) {
function lireate_home_team_tag($group)
{
    if ($group === 'management') {
        return 'руководство';
    }
    if ($group === 'patent') {
        return 'патенты';
    }
    return 'регистрация';
}
}

if (!function_exists('lireate_home_team_members')) {
function lireate_home_team_members()
{
    if (!function_exists('lireate_contacts_team_members')) {
        return array();
    }
    $rows = lireate_contacts_team_members();
    $out = array();
    foreach ($rows as $member) {
        $group = lireate_home_team_group(isset($member['role']) ? $member['role'] : '');
        $photo = isset($member['photo']) ? (string) $member['photo'] : '';
        if ($photo !== '' && preg_match('/\.jpg$/i', $photo) && !defined('LIREATE_PREVIEW_LIVE_ORIGIN')) {
            $doc = isset($_SERVER['DOCUMENT_ROOT']) ? rtrim((string) $_SERVER['DOCUMENT_ROOT'], '/') : '';
            $webp = preg_replace('/\.jpg$/i', '.webp', $photo);
            $candidate = $doc !== '' ? $doc . $webp : '';
            if ($candidate !== '' && is_file($candidate)) {
                $photo = $webp;
            }
        }
        if ($photo !== '' && function_exists('lireate_preview_live_url')) {
            $photo = lireate_preview_live_url($photo);
        }
        $out[] = array(
            'name' => isset($member['name']) ? $member['name'] : '',
            'role' => isset($member['role']) ? $member['role'] : '',
            'photo' => $photo,
            'group' => $group,
            'tag' => lireate_home_team_tag($group),
        );
    }
    return $out;
}
}

if (!function_exists('lireate_home_process_stages')) {
function lireate_home_process_stages()
{
  return array(
    array(
      'title' => 'Постановка задачи',
      'intro' => 'Начинаем с понимания вашей ситуации — чтобы выбрать подходящий способ защиты интеллектуальной собственности.',
      'tasks' => array(
        'Выясняем, что именно нужно защитить: бренд, разработку, программу или права по договору.',
        'Уточняем цели, сроки, текущие документы и вопросы клиента.',
        'Объясняем возможные пути работы и следующий шаг.',
      ),
      'result' => 'Понятный план действий',
      'resultText' => 'Вы понимаете, какие действия предстоят, какие данные понадобятся и с чего начинается работа.',
      'action' => 'Обсудить задачу',
      'url' => LIREATE_HOME_TELEGRAM_URL,
    ),
    array(
      'title' => 'Подготовка документов',
      'intro' => 'После согласования условий специалисты подготавливают документы применительно к выбранному виду защиты.',
      'tasks' => array(
        'Согласуем состав работ и исходные материалы, которые потребуются от клиента.',
        'Проверяем информацию, готовим необходимые заявления, описания и иные документы.',
        'Согласуем подготовленные материалы с вами перед подачей.',
      ),
      'result' => 'Согласованный комплект материалов',
      'resultText' => 'Вы видите, какие документы подготовлены, что согласовано и к какому этапу переходим дальше.',
      'action' => 'Услуги Лирейт',
      'url' => '/uslugi',
    ),
    array(
      'title' => 'Сопровождение до результата',
      'intro' => 'Не оставляем клиента один на один с процедурой: сопровождаем работу после подачи документов.',
      'tasks' => array(
        'Передаём документы в компетентное ведомство в рамках согласованной услуги.',
        'Контролируем движение заявки и при необходимости готовим ответы на запросы экспертизы.',
        'Сообщаем о ходе рассмотрения, итоговом решении и дальнейших действиях.',
      ),
      'result' => 'Прозрачный статус и итоговые документы',
      'resultText' => 'Вы получаете информацию о результате процедуры и материалы, предусмотренные согласованным объёмом работы.',
      'action' => 'Посмотреть портфолио',
      'url' => '/portfolio',
    ),
  );
}
}
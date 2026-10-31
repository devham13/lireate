<?php
/**
 * Точка входа CMS для /team/ (обёртка + fallback).
 */
$lireate_team_render_ok = false;

try {
    $doc = isset($_SERVER['DOCUMENT_ROOT']) ? rtrim((string) $_SERVER['DOCUMENT_ROOT'], '/') : '';
    $shared = $doc !== '' ? $doc . '/pages/team/_shared' : '';
    if ($shared === '' || !is_file($shared . '/lireate-team-page.inc.php')) {
        $shared = dirname(dirname(__DIR__)) . '/pages/team/_shared';
    }
    if (!is_file($shared . '/lireate-team-page.inc.php')) {
        throw new RuntimeException('lireate-team-page.inc.php not found');
    }
    include $shared . '/lireate-team-page.inc.php';
    if (function_exists('lireate_team_require_blog_footer')) {
        lireate_team_require_blog_footer();
    }
    $lireate_team_render_ok = true;
} catch (Exception $e) {
    error_log('lireate-team: ' . $e->getMessage());
} catch (Error $e) {
    error_log('lireate-team: ' . $e->getMessage());
}

if (!$lireate_team_render_ok) {
    echo '<!-- lireate-team-fallback:v1 -->';
    $doc = isset($_SERVER['DOCUMENT_ROOT']) ? rtrim((string) $_SERVER['DOCUMENT_ROOT'], '/') : '';
    $legacy = $doc !== '' ? $doc . '/template/page/team/lireate-team-legacy.inc.php' : '';
    if ($legacy !== '' && is_file($legacy)) {
        include $legacy;
    } else {
        echo '<p class="lireate-team-fallback-msg">Страница команды временно недоступна. Позвоните +7 (960) 048-49-57.</p>';
    }
}

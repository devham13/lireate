<?php
/* audit07 C4 cases wave 1: /portfolio/elisa-hotels/ (data and markup: data/lireate-cases-lib.php) */
$_lx_cases_lib = $_SERVER['DOCUMENT_ROOT'] . '/data/lireate-cases-lib.php';
if (is_file($_lx_cases_lib)) {
    require_once $_lx_cases_lib;
    lireate_cases_render('elisa-hotels');
}

<?php
/**
 * Minimal CMS main.php shell with live body-class PHP block (round 19, md5 9e2479f6 lineage).
 */
$temp = array('page' => 'home');
$_lx_home_v2 = false;
?><!DOCTYPE html>
<html lang="ru"><head><meta charset="utf-8"><title>home</title></head>
<body class="cms-default<?php /* lireate_design_cms_dark_pages_v2 body */ if (in_array($temp['page'], array('home','patent','news','portfolio','reviews','team','kontaktyi','o_kompanii','partners','docs'), true)) echo ' lireate-cms-page-dark';  /* lireate_design_mktu_lu_body_v1 */ if (in_array($temp['page'], array('patent','mktu'), true)) echo ' lireate-mktu-lu-body'; if ($temp['page']==='home') echo empty($_lx_home_v2) ? ' lireate-home-dark' : ' lireate-home-v2'; /* lireate_home_v2_20261125 body */?>">
<div class="page_container"></div>
</body></html>
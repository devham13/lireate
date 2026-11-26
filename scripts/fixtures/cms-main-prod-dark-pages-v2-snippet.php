<?php
/** Production-shaped dark page lists (no home; patent slugs present until patent Light V4 install). */
// head
$_lx_cms_dark = /* lireate_design_cms_dark_pages_v2 */ array('patent','patent_prom_obr','mktu','mktu1','reviews','portfolio','spasibo','uslugi','avtor','team','partners','kontaktyi','o_kompanii','contacts');
// body
/* lireate_design_cms_dark_pages_v2 body */ if (in_array($temp['page'], array('patent','patent_prom_obr','mktu','mktu1','reviews','portfolio','spasibo','uslugi','avtor','team','partners','kontaktyi','o_kompanii','contacts'), true)) echo ' lireate-cms-page-dark';
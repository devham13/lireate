<?php
/** Live-like body class conditionals (hosting main.php round 18 fixture). */
?>
<html><body class="cms-default
<?php if (in_array($temp['page'], array('home','news','portfolio','reviews'), true)) echo ' lireate-cms-page-dark'; ?>
<?php if ($temp['page']==='home') echo empty($_lx_home_v2) ? ' lireate-home-dark' : ' lireate-home-v2'; ?>
">
</body></html>
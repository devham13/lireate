<?php
/** Minimal live-style patent branches for installer tests (PHP 5.6). */
?>
<div class="wrapper">
<?php if($temp['page']=='patent'):?>
<div class="legacy-patent">old inventions</div>
<?php endif?>
<?php if($temp['page']=='patent_prom_obr'):?>
<div class="legacy-prom">old designs</div>
<?php endif?>
<div class="page_container">
<?php if($temp['apsw']){?>
<div class="txt-content">db</div>
<?php } ?>
</div>
</div>
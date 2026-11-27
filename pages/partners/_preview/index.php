<?php
$repo = dirname(__DIR__, 3);
require_once $repo . '/pages/_shared/lireate-preview-bootstrap.php';
$_SERVER['DOCUMENT_ROOT'] = $repo;
?><!DOCTYPE html>
<html lang="ru">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Партнёрам — preview company v5 + QA v6</title></head>
<body class="cms-default lireate-partners-body lireate-company-v5 lireate-screenshot-capture">
<?php require $repo . '/template/page/partners/lireate-partners-cms.inc.php'; ?>
</body>
</html>
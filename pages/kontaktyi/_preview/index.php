<?php
$repo = dirname(__DIR__, 3);
$_SERVER['DOCUMENT_ROOT'] = $repo;
require_once $repo . '/template/page/kontaktyi/lireate-contacts-config.php';
?>
<!DOCTYPE html>
<html lang="ru">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Контакты — preview Light V4</title></head>
<body class="cms-default lireate-contacts-body lireate-theme-light-v4">
<?php require $repo . '/template/page/kontaktyi/lireate-contacts-cms.inc.php'; ?>
</body>
</html>
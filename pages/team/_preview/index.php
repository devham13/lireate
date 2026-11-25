<?php
$repo = dirname(__DIR__, 3);
$_SERVER['DOCUMENT_ROOT'] = $repo;
require_once $repo . '/template/page/team/lireate-team-config.php';
?>
<!DOCTYPE html>
<html lang="ru">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Команда — preview Light V4</title></head>
<body class="cms-default lireate-team-body lireate-theme-light-v4">
<?php require $repo . '/template/page/team/lireate-team-cms.inc.php'; ?>
</body>
</html>
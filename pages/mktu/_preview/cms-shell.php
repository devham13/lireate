<?php
$repo = dirname(dirname(dirname(__DIR__)));
$fixtureCss = $repo . '/tests/fixtures/live-render/live-assets/template/css/lireate-cms-page-dark.css';
$qaCss = '/template/css/lireate-qa-v6.css?v=20261126c';
$darkCss = '/tests/fixtures/live-render/live-assets/template/css/lireate-cms-page-dark.css';
if (!is_file($fixtureCss)) {
    header('HTTP/1.0 500');
    echo 'fixture missing';
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>МКТУ — QA V6 contrast shell</title>
  <link rel="stylesheet" href="<?php echo htmlspecialchars($darkCss, ENT_QUOTES, 'UTF-8'); ?>">
  <link rel="stylesheet" href="<?php echo htmlspecialchars($qaCss, ENT_QUOTES, 'UTF-8'); ?>">
</head>
<body class="cms-default lireate-cms-page-dark lireate-mktu-lu-body lxq-mktu">
<div class="page_container">
  <div class="container content">
    <section class="mktu_body">
      <div class="conteinerY">
        <div class="title-ap">
          <h1 class="sub-title">Международная классификация товаров и услуг (МКТУ)</h1>
        </div>
        <div class="conteinerX">
          <div class="left_block">
            <div class="block_contact text-left">
              <h3>Горбачев Юрий</h3>
              <p>За 10 минут подробно расскажет о любой необходимой процедуре, связанной с интеллектуальной собственностью.</p>
              <div class="btn content-button red" style="display:inline-block;padding:10px 16px;background:#e32020;border-radius:8px;">Консультация</div>
            </div>
          </div>
          <div class="txt-content news-content">
            <h2>Класс 35 — реклама</h2>
            <p>Управление коммерческой деятельностью; <a href="/registraciya_tovarnogo_znaka/">регистрация товарного знака</a>.</p>
            <h2 id="mktu-search">Класс 42 — научные услуги</h2>
            <p>Разработка и внедрение программного обеспечения; консультации в области информационных технологий.</p>
            <label for="lx-mktu-search">Поиск по перечню</label>
            <input type="search" id="lx-mktu-search" placeholder="Введите товар или услугу" style="display:block;margin:12px 0;width:min(420px,100%);padding:10px 12px;">
          </div>
        </div>
      </div>
    </section>
  </div>
</div>
</body>
</html>
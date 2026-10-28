<?php
header('Content-Type: text/html; charset=utf-8');
$path = '/kontaktyi/';
$host = isset($_SERVER['HTTP_HOST']) ? preg_replace('/[^a-zA-Z0-9.\-:]/', '', $_SERVER['HTTP_HOST']) : '';
$site_root = ($host !== '' ? 'https://' . $host : '');
$canonical = $site_root . $path;
$seo_title = 'Контакты LIREATE — телефон, офис, команда';
$seo_description = 'Контакты LIREATE: телефоны +7 (499) 938-45-95 и +7 (960) 048-49-57, офис в Зеленодольске. Telegram, WhatsApp, Max — команда и реквизиты ООО «ЛИРЕЙТ».';
$og_type = 'website';
$og_image = $site_root . '/local/templates/lireate/images/og-default.jpg';
$lireate_phone_display = '+7 (960) 048-49-57';
$lireate_phone_tel = 'tel:+79600484957';
$lireate_phone2_display = '+7 (499) 938-45-95';
$lireate_phone2_tel = 'tel:+74999384595';
$lireate_email = 'info@' . 'lireate' . '.' . 'ru'; // pragma: allowlist secret
$lireate_telegram = 'https://t.me/lireate_patent';
$lireate_whatsapp = 'https://wa.me/79600484957';
require_once dirname(__DIR__) . '/_shared/lireate-service-messenger-include.inc.php';
$lireate_max = LIREATE_MAX_CHAT_URL;
$lireate_max_channel = LIREATE_MAX_CHANNEL_URL;
$lireate_metrika_id = getenv('LIREATE_YANDEX_METRIKA_ID') ?: '';
$lx_office_address_live = 'РТ, г. Зеленодольск ул. Королёва, 11Б этаж 2';
$lx_team = [
  ['name' => 'Горбачев Юрий Александрович', 'role' => 'Управляющий партнёр'],
  ['name' => 'Бураков Андрей Геннадьевич', 'role' => 'Управляющий партнёр'],
  ['name' => 'Габдрахманова эльвира дамировна', 'role' => 'Руководитель отдела регистрации интеллектуальной собственности'],
  ['name' => 'Захарова Ирина Николаевна', 'role' => 'Специалист по регистрации интеллектуальной собственности'],
  ['name' => 'Прокопьева Алия Раифовна', 'role' => 'Специалист по регистрации интеллектуальной собственности'],
  ['name' => 'Сенатова Валерия Станиславовна', 'role' => 'Специалист по регистрации интеллектуальной собственности'],
  ['name' => 'Томилова Раиса Николаевна', 'role' => 'Делопроизводитель отдела по регистрации интеллектуальной собственности'],
  ['name' => 'Ульданова Алёна Анатольева', 'role' => 'Специалист по регистрации интеллектуальной собственности'],
  ['name' => 'Азизова Гузель Рустамовна', 'role' => 'Специалист патентного отдела'],
  ['name' => 'Хайретдинов Рустем Афузалович', 'role' => 'Специалист патентного отдела'],
  ['name' => 'Яковлева Вероника Владиславовна', 'role' => 'Специалист патентного отдела'],
  ['name' => 'Квасова Анна Андреевна', 'role' => 'Специалист по регистрации интеллектуальной собственности'],
  ['name' => 'Адиятуллина Лилия Ильдаровна', 'role' => 'Специалист по регистрации интеллектуальной собственности'],
  ['name' => 'Гибадуллина Алия Юнусовна', 'role' => 'Специалист по регистрации интеллектуальной собственности'],
  ['name' => 'Ибрагимова Алиса Айратовна', 'role' => 'Специалист по регистрации интеллектуальной собственности'],
];
$lx_breadcrumb_current = 'Контакты';
$schema_graph = [
  '@context' => 'https://schema.org',
  '@graph' => [
    [
      '@type' => 'Organization',
      '@id' => $canonical . '#organization',
      'name' => 'LIREATE',
      'legalName' => 'Общество с ограниченной ответственностью «ЛИРЕЙТ»',
      'alternateName' => 'ООО «ЛИРЕЙТ»',
      'url' => $site_root . '/',
      'email' => $lireate_email,
      'taxID' => '1648040894',
      'identifier' => [
        '@type' => 'PropertyValue',
        'propertyID' => 'ОГРН',
        'value' => '1151673001578',
      ],
      'telephone' => ['+7-499-938-45-95', '+7-960-048-49-57'],
      'sameAs' => [$lireate_telegram, $lireate_whatsapp, $lireate_max, $lireate_max_channel],
    ],
    [
      '@type' => 'LocalBusiness',
      '@id' => $canonical . '#localbusiness',
      'name' => 'LIREATE',
      'url' => $canonical,
      'email' => $lireate_email,
      'telephone' => ['+7-499-938-45-95', '+7-960-048-49-57'],
      'parentOrganization' => ['@id' => $canonical . '#organization'],
      'address' => [
        '@type' => 'PostalAddress',
        'streetAddress' => 'ул. Королёва, 11Б, этаж 2',
        'addressLocality' => 'Зеленодольск',
        'addressRegion' => 'Республика Татарстан',
        'addressCountry' => 'RU',
      ],
      'areaServed' => 'RU',
    ],
  ],
];
?><!DOCTYPE html>
<html lang="ru" data-lireate-telegram="<?php echo htmlspecialchars($lireate_telegram, ENT_QUOTES, 'UTF-8'); ?>" data-lireate-whatsapp="<?php echo htmlspecialchars($lireate_whatsapp, ENT_QUOTES, 'UTF-8'); ?>" data-lireate-max="<?php echo htmlspecialchars($lireate_max, ENT_QUOTES, 'UTF-8'); ?>" data-lireate-metrika-id="<?php echo htmlspecialchars(preg_replace('/\D/', '', (string) $lireate_metrika_id), ENT_QUOTES, 'UTF-8'); ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo htmlspecialchars($seo_title, ENT_QUOTES, 'UTF-8'); ?></title>
  <meta name="description" content="<?php echo htmlspecialchars($seo_description, ENT_QUOTES, 'UTF-8'); ?>">
  <link rel="canonical" href="<?php echo htmlspecialchars($canonical, ENT_QUOTES, 'UTF-8'); ?>">
  <meta property="og:title" content="<?php echo htmlspecialchars($seo_title, ENT_QUOTES, 'UTF-8'); ?>">
  <meta property="og:description" content="<?php echo htmlspecialchars($seo_description, ENT_QUOTES, 'UTF-8'); ?>">
  <meta property="og:url" content="<?php echo htmlspecialchars($canonical, ENT_QUOTES, 'UTF-8'); ?>">
  <meta property="og:type" content="<?php echo htmlspecialchars($og_type, ENT_QUOTES, 'UTF-8'); ?>">
  <meta property="og:image" content="<?php echo htmlspecialchars($og_image, ENT_QUOTES, 'UTF-8'); ?>">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?php echo htmlspecialchars($seo_title, ENT_QUOTES, 'UTF-8'); ?>">
  <meta name="twitter:description" content="<?php echo htmlspecialchars($seo_description, ENT_QUOTES, 'UTF-8'); ?>">
  <link rel="stylesheet" href="assets/lireate-shared/lireate-service-dark.css">
  <link rel="stylesheet" href="assets/lireate-contacts.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
  <script type="application/ld+json"><?php echo json_encode($schema_graph, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?></script>
</head>
<body class="lireate-svc-body">
<div class="lx-bg-glow" aria-hidden="true"></div>

<main id="primary" class="lireate-svc-page kontaktyi-page" role="main" tabindex="-1">

<?php include __DIR__ . '/blocks/lireate-breadcrumbs.inc.php'; ?>

<section class="lx-hero lx-reveal" id="top">
  <div>
    <p class="lx-eyebrow">LIREATE · связь с командой</p>
    <h1>Контакты Лирейт</h1>
    <p class="lx-hero-lead">Мы перезвоним Вам в ближайшее время.</p>
    <div class="lx-contacts-cards">
      <article class="lx-contacts-card">
        <h3>Телефоны</h3>
        <p><a href="<?php echo htmlspecialchars($lireate_phone2_tel, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($lireate_phone2_display, ENT_QUOTES, 'UTF-8'); ?></a></p>
        <p style="margin-top:6px"><a href="<?php echo htmlspecialchars($lireate_phone_tel, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($lireate_phone_display, ENT_QUOTES, 'UTF-8'); ?></a></p>
      </article>
      <article class="lx-contacts-card">
        <h3>E-mail</h3>
        <p><a href="mailto:<?php echo htmlspecialchars($lireate_email, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($lireate_email, ENT_QUOTES, 'UTF-8'); ?></a></p>
      </article>
      <article class="lx-contacts-card">
        <h3>Адрес</h3>
        <p><?php echo htmlspecialchars($lx_office_address_live, ENT_QUOTES, 'UTF-8'); ?></p>
      </article>
    </div>
    <div class="lx-chip-row" aria-label="Мессенджеры">
      <a class="lx-chip-btn" id="link-telegram" href="<?php echo htmlspecialchars($lireate_telegram, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer">Telegram</a>
      <a class="lx-chip-btn" id="link-whatsapp" href="<?php echo htmlspecialchars($lireate_whatsapp, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer">WhatsApp</a>
      <a class="lx-chip-btn" id="link-max" href="<?php echo htmlspecialchars($lireate_max, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer">Написать в Max</a>
      <a class="lx-chip-btn lx-chip-btn--max-channel" id="link-max-channel" href="<?php echo htmlspecialchars($lireate_max_channel, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer">Канал в Max</a>
    </div>
  </div>
  <div class="lx-glass lx-glass-anim" aria-label="Как связаться">
    <div class="lx-panel-head"><span>Связь</span><span class="lx-live">LIREATE</span></div>
    <ul class="lx-feed">
      <li>Звонок или заявка — перезвоним в рабочее время</li>
      <li>Telegram и WhatsApp — быстрые вопросы по товарным знакам</li>
      <li>Max — чат и канал с новостями компании</li>
    </ul>
    <div class="lx-msg-grid">
      <a class="lx-msg-btn" href="<?php echo htmlspecialchars($lireate_telegram, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" data-lx-icon="telegram"><span>Telegram</span></a>
      <a class="lx-msg-btn" href="<?php echo htmlspecialchars($lireate_whatsapp, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" data-lx-icon="whatsapp"><span>WhatsApp</span></a>
      <a class="lx-msg-btn lx-msg-btn--max" href="<?php echo htmlspecialchars($lireate_max, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" data-lx-icon="max"><span>MAX</span></a>
    </div>
  </div>
</section>

<section class="lx-section lx-reveal" id="team">
  <span class="lx-section-label">Команда</span>
  <h2>Специалисты LIREATE</h2>
  <div class="lx-team-grid">
    <?php foreach ($lx_team as $member) : ?>
    <article class="lx-team-card">
      <h3><?php echo htmlspecialchars($member['name'], ENT_QUOTES, 'UTF-8'); ?></h3>
      <?php if ($member['role'] !== '') : ?>
      <p><?php echo htmlspecialchars($member['role'], ENT_QUOTES, 'UTF-8'); ?></p>
      <?php endif; ?>
    </article>
    <?php endforeach; ?>
  </div>
</section>

<section class="lx-section lx-reveal" id="requisites">
  <span class="lx-section-label">Реквизиты</span>
  <h2>ООО «ЛИРЕЙТ»</h2>
  <div class="lx-requisites-panel">
    <p>ООО «ЛИРЕЙТ» · ИНН 1648040894 · КПП 164801001 · ОГРН 1151673001578</p>
    <p>Юридический адрес: 422542, Татарстан, Зеленодольский р-н, г.&nbsp;Зеленодольск, ул.&nbsp;Королёва, 11Б, помещ.&nbsp;30-1005</p>
    <p>Почтовый адрес: 422540, г.&nbsp;Зеленодольск, ул.&nbsp;Карла Маркса, 24, оф.&nbsp;1</p>
  </div>
</section>

<section class="lx-section lx-reveal" id="contact">
  <div class="lx-contact">
    <div>
      <span class="lx-section-label">Заявка</span>
      <h2>Оставить запрос</h2>
      <p>Телефоны: <a href="<?php echo htmlspecialchars($lireate_phone2_tel, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($lireate_phone2_display, ENT_QUOTES, 'UTF-8'); ?></a> · <a href="<?php echo htmlspecialchars($lireate_phone_tel, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($lireate_phone_display, ENT_QUOTES, 'UTF-8'); ?></a></p>
      <div class="lx-msg-grid">
        <a class="lx-msg-btn" href="<?php echo htmlspecialchars($lireate_telegram, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" data-lx-icon="telegram"><span>Telegram</span></a>
        <a class="lx-msg-btn" href="<?php echo htmlspecialchars($lireate_whatsapp, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" data-lx-icon="whatsapp"><span>WhatsApp</span></a>
        <a class="lx-msg-btn lx-msg-btn--max" href="<?php echo htmlspecialchars($lireate_max, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" data-lx-icon="max"><span>MAX</span></a>
      </div>
    </div>
    <div class="lx-form-panel">
      <form id="lireate-lead-form" data-lead-topic="Заявка: контакты LIREATE" novalidate>
        <label for="lf-name">Имя</label>
        <input id="lf-name" name="name" type="text" required autocomplete="name" placeholder="Как к вам обращаться">
        <label for="lf-phone">Телефон</label>
        <input id="lf-phone" name="phone" type="tel" required autocomplete="tel" placeholder="+7 …">
        <label class="lx-consent"><input type="checkbox" id="lf-consent" required> Согласен на <a href="/private_data" target="_blank" rel="noopener noreferrer">обработку персональных данных</a> и с <a href="/politika_konfidencialnosti" target="_blank" rel="noopener noreferrer">политикой конфиденциальности</a>.</label>
        <button type="submit" class="lx-btn lx-btn-primary" style="width:100%;margin-top:12px;">Получить консультацию</button>
        <div class="lx-form-alt">
          <span class="lx-form-alt-label">или напишите нам</span>
          <div class="lx-form-alt-icons">
            <a id="form-alt-telegram" class="lx-form-alt-link" href="<?php echo htmlspecialchars($lireate_telegram, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" data-lx-icon="telegram" aria-label="Telegram"></a>
            <a id="form-alt-max" class="lx-form-alt-link lx-form-alt-link--max" href="<?php echo htmlspecialchars($lireate_max, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" data-lx-icon="max-white" aria-label="MAX"></a>
          </div>
        </div>
        <p class="lx-form-success" id="lf-success" role="status">Спасибо! Откроем мессенджер с готовым текстом — отправьте сообщение, чтобы заявка дошла до юриста.</p>
      </form>
    </div>
  </div>
</section>

<section class="lx-section lx-reveal" id="related">
  <span class="lx-section-label">LIREATE</span>
  <h2>Услуги и блог</h2>
  <div class="lx-related-grid">
    <a class="lx-related-card" href="/findtz/"><span class="lx-related-card-type">Услуга</span><strong>Проверка товарного знака</strong></a>
    <a class="lx-related-card" href="/registraciya_tovarnogo_znaka/"><span class="lx-related-card-type">Услуга</span><strong>Регистрация товарного знака</strong></a>
    <a class="lx-related-card" href="/prodlenie_tovarnogo_znaka/"><span class="lx-related-card-type">Услуга</span><strong>Продление товарного знака</strong></a>
    <a class="lx-related-card" href="/news/"><span class="lx-related-card-type">Блог</span><strong>Новости и статьи</strong></a>
    <a class="lx-related-card" href="/uslugi/"><span class="lx-related-card-type">Каталог</span><strong>Все услуги</strong></a>
  </div>
</section>

</main>

<?php include __DIR__ . '/lireate-service-footer.inc.php'; ?>
<?php include __DIR__ . '/blocks/lireate-sticky-mobile.inc.php'; ?>

<aside class="lx-dock" aria-label="Быстрый контакт">
  <a href="#contact" class="lx-dock-lead" title="Оставить заявку" aria-label="Оставить заявку"></a>
  <a id="dock-telegram" href="<?php echo htmlspecialchars($lireate_telegram, ENT_QUOTES, 'UTF-8'); ?>" title="Telegram" aria-label="Telegram" target="_blank" rel="noopener noreferrer"></a>
  <a id="dock-max" class="lx-dock-max" href="<?php echo htmlspecialchars($lireate_max, ENT_QUOTES, 'UTF-8'); ?>" title="MAX" aria-label="MAX" target="_blank" rel="noopener noreferrer"></a>
</aside>

<script src="assets/lireate-shared/lireate-reveal.js"></script>
<script src="assets/lireate-shared/lireate-mobile-bar.js"></script>
<script src="assets/lireate-shared/lireate-contact.js"></script>
<?php include dirname(__DIR__) . '/_shared/lireate-metrika.inc.php'; ?>
<script src="assets/lireate-shared/lireate-metrika-goals.js"></script>
</body>
</html>

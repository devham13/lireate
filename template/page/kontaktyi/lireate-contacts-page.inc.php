<?php
/**
 * Разметка страницы «Контакты» (/kontaktyi/) — тёмный стиль как /news.
 */
require_once __DIR__ . '/lireate-contacts-lib.php';

$lc_assets = LIREATE_CONTACTS_ASSETS_BASE;
$lc_v = LIREATE_CONTACTS_TEMPLATE_VERSION;
$lc_preview = !empty($GLOBALS['lireate_contacts_preview']);
$lc_team = lireate_contacts_team_members();
$lc_photo_origin = '';
if (isset($GLOBALS['lireate_contacts_photo_origin']) && is_string($GLOBALS['lireate_contacts_photo_origin'])) {
    $lc_photo_origin = rtrim($GLOBALS['lireate_contacts_photo_origin'], '/');
}
if ($lc_photo_origin !== '') {
    foreach ($lc_team as $i => $member) {
        if (isset($member['photo']) && strpos($member['photo'], 'http') !== 0) {
            $lc_team[$i]['photo'] = $lc_photo_origin . $member['photo'];
        }
    }
}
$lc_domain = 'li' . 're' . 'ate.ru';
$lc_email = 'info@' . $lc_domain;
$lc_address = 'РТ, г. Зеленодольск ул. Королёва, 11Б этаж 2';
$lc_phone1_display = '+7(499)938-45-95';
$lc_phone1_tel = 'tel:+7(499)938-45-95';
$lc_phone2_display = '+7(960)048-49-57';
$lc_phone2_tel = 'tel:+7(960)048-49-57';
$lc_map_src = 'https://api-maps.yandex.ru/services/constructor/1.0/js/?um=constructor%3A529a47dbf50b8ccb0e900378c49ad0fc6f16257c2341fdcaf42afb0b432a7454&amp;width=100%25&amp;height=400&amp;lang=ru_RU&amp;scroll=true';
?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Marmelad&family=Rubik:wght@400;600;700&family=Unbounded:wght@700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?php echo lireate_contacts_h($lc_assets); ?>/lireate-contacts.css?v=<?php echo lireate_contacts_h($lc_v); ?>">
<link rel="stylesheet" href="/template/page/news/lireate-blog-footer.css?v=<?php echo lireate_contacts_h($lc_v); ?>">
<script>
document.documentElement.classList.add('lireate-contacts-html');
document.documentElement.setAttribute('data-lireate-metrika-id', '<?php echo lireate_contacts_h(LIREATE_CONTACTS_METRIKA_ID); ?>');
if (document.body) { document.body.classList.add('lireate-contacts-body'); }
</script>
<div class="lireate-contacts-page" data-lireate-contacts-version="<?php echo lireate_contacts_h($lc_v); ?>">
  <header class="lireate-contacts-hero">
    <div class="lireate-contacts-wrap">
      <p class="lireate-contacts-kicker">LIREATE</p>
      <h1 class="lireate-contacts-h1">Контакты Лирейт</h1>
      <p class="lireate-contacts-lead">Мы перезвоним Вам в ближайшее время.</p>
      <div class="lireate-contacts-hero-actions" aria-label="Мессенджеры">
        <a class="lireate-contacts-pill lireate-contacts-pill--solid" id="lc-hero-max" href="<?php echo lireate_contacts_h(LIREATE_CONTACTS_MAX_CHAT_URL); ?>" target="_blank" rel="noopener noreferrer" data-lx-ym-goal="perehod_v_messendzher">Max</a>
        <a class="lireate-contacts-pill lireate-contacts-pill--outline" id="lc-hero-telegram" href="<?php echo lireate_contacts_h(LIREATE_CONTACTS_TELEGRAM_URL); ?>" target="_blank" rel="noopener noreferrer" data-lx-ym-goal="perehod_v_telegramm">Telegram</a>
        <a class="lireate-contacts-pill lireate-contacts-pill--outline" id="lc-hero-whatsapp" href="<?php echo lireate_contacts_h(LIREATE_CONTACTS_WHATSAPP_URL); ?>" target="_blank" rel="noopener noreferrer" data-lx-ym-goal="perehod_v_messendzher">WhatsApp</a>
        <a class="lireate-contacts-pill lireate-contacts-pill--outline" id="lc-hero-max-channel" href="<?php echo lireate_contacts_h(LIREATE_CONTACTS_MAX_CHANNEL_URL); ?>" target="_blank" rel="noopener noreferrer" data-lx-ym-goal="perehod_v_messendzher">Канал в Max</a>
      </div>
    </div>
  </header>

  <div class="lireate-contacts-wrap lireate-contacts-grid">
    <section class="lireate-contacts-card lireate-contacts-form-card" aria-labelledby="lc-form-title">
      <span class="lireate-contacts-badge">Заявка</span>
      <h2 class="lireate-contacts-card-title" id="lc-form-title">Написать Директору Лирейт</h2>
      <?php if (!$lc_preview) : ?>
      <script src="https://www.google.com/recaptcha/api.js?hl=ru"></script>
      <?php endif; ?>
      <form class="form-unit pb30 ajax_form lireate-contacts-form" method="POST" action="mail.php">
        <div class="mail_to">
          <input type="hidden" name="mail_token" value="410eefd63c084df22d849b9d7ae549e7">
          <input type="hidden" name="fuck_spamers" value="55c30f8059c1b725e4f2f2a9f387dd6f">
          <input class="js-ap-tomail" type="hidden" name="tomail" value="">
          <input type="hidden" name="mail" value="mailorder">
          <div class="pb30">
            <div data-frm="tel" class="form-group form-unit-group vError frm-input">
              <input name="tel" type="text" class="form-control form-unit-control ap-label-js lireate-contacts-input" placeholder="Введите телефон">
            </div>
            <div data-frm="otziv" class="form-group form-unit-group otzivError vError frm-input">
              <textarea class="form-control lireate-contacts-textarea" name="otziv" style="resize: none;"></textarea>
            </div>
            <?php if ($lc_preview) : ?>
            <div class="lireate-contacts-recaptcha-stub" aria-hidden="true">reCAPTCHA</div>
            <?php else : ?>
            <div class="g-recaptcha vError" data-frm="g-recaptcha" data-sitekey="<?php echo lireate_contacts_h(LIREATE_CONTACTS_RECAPTCHA_SITEKEY); ?>" style="text-indent: 0px;margin-bottom: 10px;"></div>
            <?php endif; ?>
          </div>
          <button type="submit" class="lireate-contacts-submit btn btn-gradient btn-primary content-button red btn-popup js-ap-btn">Отправить</button>
          <p class="lireate-contacts-consent">Нажимая на кнопку, вы даете <a href="/private_data">согласие на обработку персональных данных</a> и соглашаетесь c <a href="/politika_konfidencialnosti">политикой конфиденциальности</a></p>
        </div>
        <div class="s_mail_to modal_success lireate-contacts-success">
          <p class="m_success">Мы перезвоним Вам в ближайшее время.</p>
        </div>
      </form>
    </section>

    <section class="lireate-contacts-card lireate-contacts-info-card" aria-labelledby="lc-info-title">
      <span class="lireate-contacts-badge lireate-contacts-badge--green">Офис</span>
      <h2 class="lireate-contacts-card-title" id="lc-info-title">Как связаться</h2>
      <dl class="lireate-contacts-dl">
        <div><dt>Адрес</dt><dd><?php echo lireate_contacts_h($lc_address); ?></dd></div>
        <div><dt>Телефоны</dt><dd class="lireate-contacts-phones"><a href="<?php echo lireate_contacts_h($lc_phone1_tel); ?>"><?php echo lireate_contacts_h($lc_phone1_display); ?></a><span class="lireate-contacts-sep" aria-hidden="true">|</span><a href="<?php echo lireate_contacts_h($lc_phone2_tel); ?>"><?php echo lireate_contacts_h($lc_phone2_display); ?></a></dd></div>
        <div><dt>E-mail</dt><dd><a href="mailto:<?php echo lireate_contacts_h($lc_email); ?>"><?php echo lireate_contacts_h($lc_email); ?></a></dd></div>
      </dl>
      <div class="lireate-contacts-map" aria-label="Карта офиса">
        <script type="text/javascript" charset="utf-8" async src="<?php echo lireate_contacts_h($lc_map_src); ?>"></script>
      </div>
    </section>
  </div>

  <section class="lireate-contacts-wrap lireate-contacts-team" aria-labelledby="lc-team-title">
    <div class="lireate-contacts-team-head">
      <span class="lireate-contacts-badge">Команда</span>
      <h2 class="lireate-contacts-card-title" id="lc-team-title">Сотрудники LIREATE</h2>
    </div>
    <div class="lireate-contacts-team-grid">
      <?php foreach ($lc_team as $idx => $member) :
          $photo = $member['photo'];
          if ($lc_photo_origin !== '' && strpos($photo, 'http') !== 0) {
              $photo = $lc_photo_origin . $photo;
          }
      ?>
      <div class="lireate-contacts-team-card" role="button" tabindex="0" data-lc-team-index="<?php echo (int) $idx; ?>">
        <span class="lireate-contacts-team-photo" style="background-image:url('<?php echo lireate_contacts_h($photo); ?>')"></span>
        <span class="lireate-contacts-team-name"><?php echo lireate_contacts_h($member['name']); ?></span>
        <span class="lireate-contacts-team-role"><?php echo lireate_contacts_h($member['role']); ?></span>
      </div>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="lireate-contacts-wrap lireate-contacts-social">
    <div class="lireate-contacts-card lireate-contacts-social-card">
      <p class="lireate-contacts-social-label">Расскажите о нас друзьям</p>
      <div id="vk_groups" class="lireate-contacts-vk"></div>
      <script type="text/javascript">VK.Widgets.Group("vk_groups", {width: "auto", mode: 3, no_cover: 1}, 134889129);</script>
      <div class="lireate-contacts-share-row">
        <p>Рассказать о Лирейт:</p>
        <script src="//yastatic.net/es5-shims/0.0.2/es5-shims.min.js"></script>
        <script src="//yastatic.net/share2/share.js"></script>
        <div class="ya-share2" data-services="vkontakte,twitter,gplus,odnoklassniki"></div>
      </div>
    </div>
  </section>

  <div class="lireate-contacts-modal" id="lc-team-modal" hidden>
    <div class="lireate-contacts-modal-backdrop" data-lc-modal-close></div>
    <div class="lireate-contacts-modal-panel" role="dialog" aria-modal="true" aria-labelledby="lc-modal-name">
      <button type="button" class="lireate-contacts-modal-close" data-lc-modal-close aria-label="Закрыть">&times;</button>
      <div class="lireate-contacts-modal-photo-wrap">
        <img class="lireate-contacts-modal-photo" id="lc-modal-photo" src="" alt="">
      </div>
      <h3 class="lireate-contacts-modal-name" id="lc-modal-name"></h3>
      <p class="lireate-contacts-modal-role" id="lc-modal-role"></p>
    </div>
  </div>

  <script type="application/json" id="lc-team-json"><?php
  echo json_encode($lc_team, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);
  ?></script>
  <script src="<?php echo lireate_contacts_h($lc_assets); ?>/lireate-contacts.js?v=<?php echo lireate_contacts_h($lc_v); ?>"></script>
  <script src="<?php echo lireate_contacts_h($lc_assets); ?>/lireate-metrika-goals.js?v=<?php echo lireate_contacts_h($lc_v); ?>"></script>
</div>

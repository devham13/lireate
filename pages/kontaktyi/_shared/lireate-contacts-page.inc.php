<?php
/**
 * Разметка страницы «Контакты» (/contacts/) — тёмный стиль как /team.
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
$lc_address = 'РТ, г. Зеленодольск ул. Карла Маркса д.24. оф.1.';
$lc_phone1_display = '+7 (499) 938-45-95';
$lc_phone1_tel = 'tel:+74999384595';
$lc_phone2_display = '+7 (960) 048-49-57';
$lc_phone2_tel = 'tel:+79600484957';
$lc_map_src = 'https://api-maps.yandex.ru/services/constructor/1.0/js/?um=constructor%3Ad7e7b32ceb16a5f6568e233bb36f3fafc873135d2f7269ff6cc1168c9892c75c&amp;width=100%25&amp;height=400&amp;lang=ru_RU&amp;scroll=false';
$lc_footer_css = defined('LIREATE_CONTACTS_FOOTER_CSS_HREF')
    ? LIREATE_CONTACTS_FOOTER_CSS_HREF
    : '/template/page/news/lireate-blog-footer.css';
?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Marmelad&family=Rubik:wght@400;600;700&family=Unbounded:wght@700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?php echo lireate_contacts_h($lc_assets); ?>/lireate-contacts.css?v=<?php echo lireate_contacts_h($lc_v); ?>">
<link rel="stylesheet" href="<?php echo lireate_contacts_h($lc_footer_css); ?>?v=<?php echo lireate_contacts_h($lc_v); ?>">
<link rel="stylesheet" href="/template/css/lireate-company-pages-v5.css?v=20261126c">
<script>
document.documentElement.classList.add('lireate-contacts-html');
document.documentElement.setAttribute('data-lireate-metrika-id', '<?php echo lireate_contacts_h(LIREATE_CONTACTS_METRIKA_ID); ?>');
if (document.body) { document.body.classList.add('lireate-contacts-body'); }
</script>
<script>(function(){document.documentElement.classList.add('lireate-company-v5');if(document.body){document.body.classList.add('lireate-company-v5');}else{document.addEventListener('DOMContentLoaded',function(){document.body.classList.add('lireate-company-v5');},{once:true});}})();</script>
<div class="lireate-contacts-page" data-lireate-contacts-version="<?php echo lireate_contacts_h($lc_v); ?>">
  <header class="lireate-contacts-hero">
    <div class="lireate-contacts-wrap">
      <p class="lireate-contacts-kicker">LIREATE</p>
      <h1 class="lireate-contacts-h1">Контакты Лирейт</h1>
      <p class="lireate-contacts-lead">Мы перезвоним Вам в ближайшее время.</p>
      <div class="lireate-contacts-hero-actions" aria-label="Связаться с LIREATE">
        <a class="lireate-contacts-pill lireate-contacts-pill--solid" id="lc-hero-max" href="<?php echo lireate_contacts_h(LIREATE_CONTACTS_MAX_CHAT_URL); ?>" target="_blank" rel="noopener noreferrer" data-lx-ym-goal="perehod_v_messendzher">Max</a>
        <a class="lireate-contacts-pill lireate-contacts-pill--outline" id="lc-hero-telegram" href="<?php echo lireate_contacts_h(LIREATE_CONTACTS_TELEGRAM_URL); ?>" target="_blank" rel="noopener noreferrer" data-lx-ym-goal="perehod_v_telegramm">Telegram</a>
        <a class="lireate-contacts-pill lireate-contacts-pill--outline" id="lc-hero-whatsapp" href="<?php echo lireate_contacts_h(LIREATE_CONTACTS_WHATSAPP_URL); ?>" target="_blank" rel="noopener noreferrer" data-lx-ym-goal="perehod_v_messendzher">WhatsApp</a>
        <a class="lireate-contacts-pill lireate-contacts-pill--outline" id="lc-hero-phone" href="<?php echo lireate_contacts_h($lc_phone2_tel); ?>" data-lx-ym-goal="klik_po_nomeru_telefona"><?php echo lireate_contacts_h($lc_phone2_display); ?></a>
        <a class="lireate-contacts-pill lireate-contacts-pill--outline" id="lc-hero-phone2" href="<?php echo lireate_contacts_h($lc_phone1_tel); ?>" data-lx-ym-goal="klik_po_nomeru_telefona"><?php echo lireate_contacts_h($lc_phone1_display); ?></a>
      </div>

      <aside class="lc5-hero-media lc5-hero-media--compact" aria-label="ОФИС ЛИРЕЙТ">
        <div class="lc5-icon-shell" aria-hidden="true"><svg viewBox="0 0 120 120" aria-hidden="true"><path d="M60 108s37-38 37-69a37 37 0 1 0-74 0c0 31 37 69 37 69Z"/><circle cx="60" cy="42" r="14"/><path d="M36 107h48"/></svg></div>
        <div class="lc5-media-body">
          <span class="lc5-chip">ОФИС ЛИРЕЙТ</span>
          <strong>Зеленодольск</strong>
          <p>Республика Татарстан, ул. Карла Маркса, 24, офис 1.</p>
          <a href="#lc-form-title">Оставить обращение ↗</a>
        </div>
      </aside>
    </div>
  </header>

  <div class="lireate-contacts-wrap lireate-contacts-grid">
    <section class="lireate-contacts-card lireate-contacts-form-card" aria-labelledby="lc-form-title">
      <span class="lireate-contacts-badge">Заявка</span>
      <h2 class="lireate-contacts-card-title" id="lc-form-title">Оставить заявку</h2>
      <?php
      $lc_spam_guard = md5(date('m.Y'));
      $lc_mail_action = 'https://' . 'lireate.ru/mail.php';
      ?>
      <form
        id="lireate-lead-form"
        class="lx-mail-lead-form lireate-contacts-form"
        method="POST"
        action="<?php echo lireate_contacts_h($lc_mail_action); ?>"
        data-lx-mail-action="<?php echo lireate_contacts_h($lc_mail_action); ?>"
        data-lead-topic="Заявка со страницы Контакты"
        data-lx-success-text="Заявка принята. Мы свяжемся с вами в ближайшее время."
        novalidate
      >
        <input type="hidden" name="mail_token" value="503066424d5f7b5ad2097703ef3404d2">
        <input type="hidden" name="fuck_spamers" value="<?php echo lireate_contacts_h($lc_spam_guard); ?>">
        <input type="hidden" name="mail" value="mailorder">
        <input type="hidden" name="form-type" value="order">
        <input type="hidden" name="tomail" value="Заявка со страницы Контакты">
        <input type="hidden" name="consent_form" value="contacts-page">
        <label for="lf-name">Имя</label>
        <input id="lf-name" name="name" type="text" required autocomplete="name" class="lireate-contacts-input" placeholder="Как к вам обращаться">
        <label for="lf-phone">Телефон</label>
        <input id="lf-phone" name="tel" type="tel" required autocomplete="tel" class="lireate-contacts-input" placeholder="+7 …" inputmode="tel">
        <label class="lx-consent lireate-contacts-consent" for="lf-consent"><input type="checkbox" id="lf-consent" required> Согласен на <a href="/private_data" target="_blank" rel="noopener noreferrer">обработку персональных данных</a> и с <a href="/politika_konfidencialnosti" target="_blank" rel="noopener noreferrer">политикой конфиденциальности</a>.</label>
        <button type="submit" class="lireate-contacts-submit lx-btn lx-btn-primary lx-form-submit">Отправить</button>
        <p class="lx-form-success" id="lf-success" role="status" aria-live="polite"></p>
        <p class="lx-form-error" id="lf-error" role="alert" hidden></p>
      </form>
      <?php if (!$lc_preview) : ?>
      <script>window.lireateMailSpamGuard=<?php echo json_encode($lc_spam_guard); ?>;</script>
      <script src="/template/js/lireate-utm.js?v=20261117b1"></script>
      <script src="/registraciya_tovarnogo_znaka/assets/lireate-shared/lireate-lead-mail.js"></script>
      <?php endif; ?>
    </section>

    <section class="lireate-contacts-card lireate-contacts-info-card" aria-labelledby="lc-info-title">
      <span class="lireate-contacts-badge lireate-contacts-badge--green">Офис</span>
      <h2 class="lireate-contacts-card-title" id="lc-info-title">Как связаться</h2>
      <dl class="lireate-contacts-dl">
        <div><dt>Адрес</dt><dd><?php echo lireate_contacts_h($lc_address); ?><br>Также Вы можете обраться к нашим <a href="/partners" target="_blank" rel="noopener noreferrer">партнёрам</a>.</dd></div>
        <div><dt>Телефоны</dt><dd class="lireate-contacts-phones"><a href="<?php echo lireate_contacts_h($lc_phone1_tel); ?>" data-lx-ym-goal="klik_po_nomeru_telefona"><?php echo lireate_contacts_h($lc_phone1_display); ?></a><span class="lireate-contacts-sep" aria-hidden="true">|</span><a href="<?php echo lireate_contacts_h($lc_phone2_tel); ?>" data-lx-ym-goal="klik_po_nomeru_telefona"><?php echo lireate_contacts_h($lc_phone2_display); ?></a></dd></div>
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
      <p class="lireate-contacts-social-label">Подробнее о команде — на странице <a href="/team">Команда Лирейт</a>.</p>
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

  <section class="lireate-contacts-wrap lireate-contacts-social" aria-labelledby="lc-social-title">
    <div class="lireate-contacts-card lireate-contacts-social-card">
      <span class="lireate-contacts-badge">Соцсети</span>
      <h2 class="lireate-contacts-card-title" id="lc-social-title">Социальные сети</h2>
      <p class="lireate-contacts-social-label">Много интересной и важной информации Вы сможете найти в наших социальных сетях</p>
      <div class="lireate-contacts-social-grid">
        <a class="lireate-contacts-social-item" href="https://www.youtube.com/channel/UCpg1LWotCQWwzviPDb_9Rew" target="_blank" rel="noopener noreferrer">
          <span class="lireate-contacts-social-name">YouTube</span>
          <span class="lireate-contacts-social-desc">На нашем канале Вы можете ознакомится с отзывами наших клиентов и получить другую полезную информацию</span>
        </a>
        <a class="lireate-contacts-social-item" href="https://vk.com/lireate" target="_blank" rel="noopener noreferrer">
          <span class="lireate-contacts-social-name">Вконтакте</span>
          <span class="lireate-contacts-social-desc">В группе VK мы регулярно публикуем интересные статьи о юридической защите предпринимательской деятельности</span>
        </a>
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

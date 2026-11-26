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
$lc_footer_css = defined('LIREATE_CONTACTS_FOOTER_CSS_HREF')
    ? LIREATE_CONTACTS_FOOTER_CSS_HREF
    : '/template/page/news/lireate-blog-footer.css';
?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Marmelad&family=Rubik:wght@400;600;700&family=Unbounded:wght@700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?php echo lireate_contacts_h($lc_assets); ?>/lireate-contacts.css?v=<?php echo lireate_contacts_h($lc_v); ?>">
<link rel="stylesheet" href="<?php echo lireate_contacts_h($lc_footer_css); ?>?v=<?php echo lireate_contacts_h($lc_v); ?>">
<link rel="stylesheet" href="/template/css/lireate-qa-v6.css?v=20261126">
<link rel="stylesheet" href="/template/css/lireate-light-v4.css?v=<?php echo lireate_contacts_h($lc_v); ?>"><!-- lireate_cms_light_v4 -->
<script>
document.documentElement.classList.add('lireate-contacts-html');
document.documentElement.setAttribute('data-lireate-metrika-id', '<?php echo lireate_contacts_h(LIREATE_CONTACTS_METRIKA_ID); ?>');
if (document.body) { document.body.classList.add('lireate-contacts-body'); document.body.classList.add('lireate-theme-light-v4'); }
</script>
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
      <h2 class="lireate-contacts-card-title" id="lc-social-title">Мессенджеры и социальные сети</h2>
      <p class="lireate-contacts-social-label">Выберите удобный канал: консультации, полезные материалы и новости об интеллектуальной собственности.</p>
      <div class="lireate-contacts-social-grid">
        <a class="lireate-contacts-social-item lireate-contacts-social-item--tg" href="https://t.me/lireate_patent" target="_blank" rel="noopener noreferrer" aria-label="Telegram" data-lx-ym-goal="perehod_v_telegramm">
          <span class="lireate-contacts-social-icon" aria-hidden="true"><svg width="24" height="24" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M9.78 18.65l.28-4.23 7.68-6.92c.34-.31-.07-.46-.52-.19L7.74 13.3 3.64 12c-.88-.25-.89-.86.2-1.3l15.97-6.16c.73-.33 1.43.18 1.15 1.3l-2.72 12.81c-.19.91-.74 1.13-1.5.71L12.6 16.3l-1.99 1.93c-.23.23-.42.42-.83.42z"/></svg></span>
          <span class="lireate-contacts-social-name">Telegram</span>
          <span class="lireate-contacts-social-desc">Чат с патентным бюро и консультации</span>
          <span class="lireate-contacts-social-arrow" aria-hidden="true">↗</span>
        </a>
        <a class="lireate-contacts-social-item lireate-contacts-social-item--max" href="https://max.ru/channel_lireate" target="_blank" rel="noopener noreferrer" aria-label="MAX" data-lx-ym-goal="perehod_v_messendzher">
          <span class="lireate-contacts-social-icon" aria-hidden="true"><svg width="24" height="24" viewBox="0 0 100 100" aria-hidden="true" focusable="false"><path fill="currentColor" d="M50.76 0.26c27.54 0 49.13 22.34 49.13 49.89S77.61 99.49 51.02 99.49c-9.44 0-14.01-1.33-21.37-6.54-.51-.36-1.2-.27-1.63.19-5.66 6.04-20.17 10.28-20.83 2.03 0-14.39-7.19-23.73-7.19-45.3C0 21.55 23.22 0.26 50.76 0.26zm.77 24.55c-13.06-.69-23.26 8.39-25.51 22.57-1.86 11.75 1.44 26.07 4.27 26.79 1.2.31 4.08-1.88 6.18-3.86.39-.37 1-.44 1.46-.15 3.27 2 6.97 3.5 11.05 3.71 13.41.7 25.3-9.8 26.01-23.21.7-13.41-10.02-25.14-23.43-25.84z"/></svg></span>
          <span class="lireate-contacts-social-name">MAX</span>
          <span class="lireate-contacts-social-desc">Новости и связь с бюро</span>
          <span class="lireate-contacts-social-arrow" aria-hidden="true">↗</span>
        </a>
        <a class="lireate-contacts-social-item lireate-contacts-social-item--wa" href="https://wa.me/79600484957" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp" data-lx-ym-goal="perehod_v_messendzher">
          <span class="lireate-contacts-social-icon" aria-hidden="true"><svg width="24" height="24" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M12 2C6.5 2 2 6.2 2 11.4c0 1.9.5 3.7 1.5 5.3L2 22l5.6-1.5c1.5.8 3.2 1.3 4.9 1.3 5.5 0 10-4.2 10-9.4S17.5 2 12 2zm5.8 13.5c-.2.6-1.2 1.1-1.7 1.1-.4 0-1.1.2-3.6-.9-3-1.3-5-4.4-5.1-4.6-.2-.3-1.2-1.6-1.2-3.1 0-1.5.8-2.2 1.1-2.5.3-.3.6-.4.8-.4.2 0 .4 0 .6.1.2 0 .4.2.5.5.2.5.6 1.7.7 1.8.1.1.1.3 0 .4 0 .1-.1.2-.2.3l-.3.3c-.1.1-.2.2-.1.4.1.2.5 1 1.1 1.6.8.7 1.5 1 1.7 1.1.2.1.3 0 .4-.1l.6-.7c.1-.2.3-.2.5-.1.8.3 2.8 1.3 3.2 1.5.3.2.5.3.6.4.1.2.1.7-.1 1.3z"/></svg></span>
          <span class="lireate-contacts-social-name">WhatsApp</span>
          <span class="lireate-contacts-social-desc">Быстрый вопрос специалисту</span>
          <span class="lireate-contacts-social-arrow" aria-hidden="true">↗</span>
        </a>
        <a class="lireate-contacts-social-item lireate-contacts-social-item--vk" href="https://vk.ru/lireate" target="_blank" rel="noopener noreferrer" aria-label="ВКонтакте">
          <span class="lireate-contacts-social-icon" aria-hidden="true"><svg width="24" height="24" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M12.785 16.241s.288-.032.436-.194c.136-.148.132-.427.132-.427s-.02-1.304.58-1.496c.591-.19 1.35.956 2.155 1.378.605.318 1.064.248 1.064.248l2.141-.03s1.117-.07.587-.95c-.043-.073-.308-.65-1.588-1.838-1.344-1.245-1.163-.522.454-1.598.983-.656 1.377-1.055 1.254-1.224-.116-.155-.833-.114-.833-.114l-2.375.015s-.176-.024-.306.053c-.127.075-.208.249-.208.249s-.374 1.001-.87 1.852c-1.05 1.508-1.47 1.587-1.641 1.493-.399-.214-.299-.86-.299-1.319 0-1.434.216-2.03-.422-2.186-.212-.051-.368-.085-.911-.09-.697-.007-1.286.002-1.619.166-.222.115-.393.372-.289.387.129.018.421.078.576.285.2.264.193.855.193.855s.116 1.72-.27 1.936c-.266.175-.631-.182-1.414-1.283-.4-.54-.702-1.137-.702-1.137s-.058-.142-.162-.218c-.126-.093-.302-.122-.302-.122l-2.256.014s-.338.01-.462.156c-.111.13-.008.4-.008.4s1.77 4.146 3.773 6.24c1.836 1.918 3.927 1.794 3.927 1.794h.947z"/></svg></span>
          <span class="lireate-contacts-social-name">ВКонтакте</span>
          <span class="lireate-contacts-social-desc">Статьи, новости и юридические разборы</span>
          <span class="lireate-contacts-social-arrow" aria-hidden="true">↗</span>
        </a>
        <a class="lireate-contacts-social-item lireate-contacts-social-item--yt" href="https://www.youtube.com/channel/UCpg1LWotCQWwzviPDb_9Rew" target="_blank" rel="noopener noreferrer" aria-label="YouTube">
          <span class="lireate-contacts-social-icon" aria-hidden="true"><svg width="24" height="24" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M21.6 7.2a2.5 2.5 0 0 0-1.8-1.8C18.2 5 12 5 12 5s-6.2 0-7.8.4A2.5 2.5 0 0 0 2.4 7.2 26 26 0 0 0 2 12a26 26 0 0 0 .4 4.8 2.5 2.5 0 0 0 1.8 1.8C5.8 19 12 19 12 19s6.2 0 7.8-.4a2.5 2.5 0 0 0 1.8-1.8A26 26 0 0 0 22 12a26 26 0 0 0-.4-4.8zM10 15V9l5.2 3L10 15z"/></svg></span>
          <span class="lireate-contacts-social-name">YouTube</span>
          <span class="lireate-contacts-social-desc">Видеоотзывы и полезные материалы</span>
          <span class="lireate-contacts-social-arrow" aria-hidden="true">↗</span>
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
  <script src="/template/js/lireate-light-v4.js?v=<?php echo lireate_contacts_h($lc_v); ?>" defer></script><!-- lireate_cms_light_v4 -->
</div>

<?php
/**
 * Общий подвал страниц услуг LIREATE.
 * Синхронизируется в каталог услуги: lireate-service-footer.inc.php
 * Реквизиты: только с lireate.ru/contacts и политики (ИНН/ОГРН на сайте не найдены).
 */
$lx_footer_phone_display = isset($lireate_phone_display) ? $lireate_phone_display : '+7 (960) 048-49-57';
$lx_footer_phone_tel = isset($lireate_phone_tel) ? $lireate_phone_tel : 'tel:+79600484957';
$lx_footer_tg = isset($lireate_telegram) ? $lireate_telegram : 'https://t.me/lireate_patent';
$lx_footer_wa = isset($lireate_whatsapp) ? $lireate_whatsapp : 'https://wa.me/79600484957';
$lx_footer_max = isset($lireate_max) ? $lireate_max : 'https://max.ru/u/f9LHodD0cOI8jsPagjibEve2cn5_xPW0X1sf3PhbS5TMijA78DnUeDEpUAI';
?>
<footer class="lx-site-footer" role="contentinfo">
  <div class="lx-site-footer-inner">
    <div class="lx-site-footer-brand">
      <a class="lx-site-footer-logo" href="/">LIREATE</a>
      <p class="lx-site-footer-tagline">Патентное бюро «Лирейт» — регистрация и защита интеллектуальной собственности</p>
    </div>
    <div class="lx-site-footer-col">
      <h2 class="lx-site-footer-heading">Контакты</h2>
      <p><a href="<?php echo htmlspecialchars($lx_footer_phone_tel, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($lx_footer_phone_display, ENT_QUOTES, 'UTF-8'); ?></a></p>
      <p><a href="mailto:info@librend.ru">info@librend.ru</a></p>
      <p class="lx-site-footer-address">Адрес: РТ, г.&nbsp;Зеленодольск, ул.&nbsp;Карла Маркса, д.&nbsp;24, оф.&nbsp;1</p>
      <div class="lx-site-footer-social">
        <a href="<?php echo htmlspecialchars($lx_footer_tg, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" data-lx-icon="telegram" aria-label="Telegram"></a>
        <a href="<?php echo htmlspecialchars($lx_footer_max, ENT_QUOTES, 'UTF-8'); ?>" class="lx-site-footer-social--max" target="_blank" rel="noopener noreferrer" data-lx-icon="max-white" aria-label="MAX"></a>
        <a href="<?php echo htmlspecialchars($lx_footer_wa, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" data-lx-icon="whatsapp" aria-label="WhatsApp"></a>
      </div>
    </div>
    <div class="lx-site-footer-col">
      <h2 class="lx-site-footer-heading">Документы</h2>
      <ul class="lx-site-footer-links">
        <li><a href="/politika_konfidencialnosti">Политика конфиденциальности</a></li>
        <li><a href="/private_data">Согласие на обработку персональных данных</a></li>
        <li><a href="/contacts/">Контакты на сайте</a></li>
      </ul>
    </div>
  </div>
  <p class="lx-site-footer-legal">Информация на странице не является публичной офертой. © <?php echo date('Y'); ?> LIREATE</p>
</footer>

<?php
/**
 * Общий подвал страниц услуг LIREATE (компактная колонка).
 * Синхронизируется в каталог услуги: lireate-service-footer.inc.php
 */
require_once __DIR__ . '/lireate-messenger-urls.php';

$lx_footer_phone_display = isset($lireate_phone_display) ? $lireate_phone_display : '+7 (960) 048-49-57';
$lx_footer_phone_tel = isset($lireate_phone_tel) ? $lireate_phone_tel : 'tel:+79600484957';
$lx_footer_phone2_display = '+7 (499) 938-45-95';
$lx_footer_phone2_tel = 'tel:+74999384595';
$lx_footer_email = 'info@lireate.ru';
$lx_footer_tg = isset($lireate_telegram) ? $lireate_telegram : 'https://t.me/lireate_patent';
$lx_footer_wa = isset($lireate_whatsapp) ? $lireate_whatsapp : 'https://wa.me/79600484957';
$lx_footer_max_channel = defined('LIREATE_MAX_CHANNEL_URL') ? LIREATE_MAX_CHANNEL_URL : 'https://max.ru/channel_lireate';
$lx_footer_vk = 'https://vk.ru/lireate';

$lx_site_base = '';
if (isset($site_root) && is_string($site_root) && $site_root !== '') {
    $lx_site_base = rtrim($site_root, '/');
} elseif (!empty($_SERVER['HTTP_HOST'])) {
    $host = preg_replace('/[^a-zA-Z0-9.\-:]/', '', (string) $_SERVER['HTTP_HOST']);
    if ($host !== '') {
        $lx_site_base = 'https://' . $host;
    }
}

$lx_org_jsonld = [
    '@context' => 'https://schema.org',
    '@type' => 'Organization',
    'name' => 'LIREATE',
    'legalName' => 'Общество с ограниченной ответственностью «ЛИРЕЙТ»',
    'alternateName' => 'ООО «ЛИРЕЙТ»',
    'url' => $lx_site_base !== '' ? $lx_site_base . '/' : '/',
    'email' => $lx_footer_email,
    'taxID' => '1648040894',
    'identifier' => [
        '@type' => 'PropertyValue',
        'propertyID' => 'ОГРН',
        'value' => '1151673001578',
    ],
    'telephone' => ['+7-960-048-49-57', '+7-499-938-45-95'],
    'address' => [
        '@type' => 'PostalAddress',
        'streetAddress' => 'ул. Королёва, 11Б, помещ. 30-1005',
        'addressLocality' => 'Зеленодольск',
        'addressRegion' => 'Республика Татарстан',
        'postalCode' => '422542',
        'addressCountry' => 'RU',
    ],
];
?>
<footer class="lx-site-footer" role="contentinfo">
  <div class="lx-site-footer-inner">
    <p class="lx-site-footer-title">LIREATE • Товарные знаки • Интеллектуальная собственность</p>
    <p class="lx-site-footer-lead">Регистрация и защита товарных знаков, патентов и авторских прав.</p>
    <p class="lx-site-footer-meta">ООО «ЛИРЕЙТ»</p>
    <p class="lx-site-footer-meta">ИНН 1648040894 • КПП 164801001 • ОГРН 1151673001578</p>
    <p class="lx-site-footer-contact">
      <a href="mailto:<?php echo htmlspecialchars($lx_footer_email, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($lx_footer_email, ENT_QUOTES, 'UTF-8'); ?></a>
      <span class="lx-site-footer-dot" aria-hidden="true">•</span>
      <a href="<?php echo htmlspecialchars($lx_footer_phone_tel, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($lx_footer_phone_display, ENT_QUOTES, 'UTF-8'); ?></a>
      <span class="lx-site-footer-dot" aria-hidden="true">•</span>
      <a href="<?php echo htmlspecialchars($lx_footer_phone2_tel, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($lx_footer_phone2_display, ENT_QUOTES, 'UTF-8'); ?></a>
      <span class="lx-site-footer-dot" aria-hidden="true">•</span>
      <a href="<?php echo htmlspecialchars($lx_footer_tg, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer">Telegram</a>
    </p>
    <div class="lx-site-footer-social" aria-label="Соцсети и мессенджеры">
      <a class="lx-site-footer-social-btn lx-site-footer-social-btn--tg" href="<?php echo htmlspecialchars($lx_footer_tg, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" aria-label="Telegram">
        <svg class="lx-site-footer-social-svg" viewBox="0 0 24 24" width="22" height="22" aria-hidden="true" focusable="false"><path fill="currentColor" d="M9.78 18.65l.28-4.23 7.68-6.92c.34-.31-.07-.46-.52-.19L7.74 13.3 3.64 12c-.88-.25-.89-.86.2-1.3l15.97-6.16c.73-.33 1.43.18 1.15 1.3l-2.72 12.81c-.19.91-.74 1.13-1.5.71L12.6 16.3l-1.99 1.93c-.23.23-.42.42-.83.42z"/></svg>
      </a>
      <a class="lx-site-footer-social-btn lx-site-footer-social-btn--wa" href="<?php echo htmlspecialchars($lx_footer_wa, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp">
        <svg class="lx-site-footer-social-svg" viewBox="0 0 24 24" width="22" height="22" aria-hidden="true" focusable="false"><path fill="currentColor" d="M12 2C6.5 2 2 6.2 2 11.4c0 1.9.5 3.7 1.5 5.3L2 22l5.6-1.5c1.5.8 3.2 1.3 4.9 1.3 5.5 0 10-4.2 10-9.4S17.5 2 12 2zm5.8 13.5c-.2.6-1.2 1.1-1.7 1.1-.4 0-1.1.2-3.6-.9-3-1.3-5-4.4-5.1-4.6-.2-.3-1.2-1.6-1.2-3.1 0-1.5.8-2.2 1.1-2.5.3-.3.6-.4.8-.4.2 0 .4 0 .6.1.2 0 .4.2.5.5.2.5.6 1.7.7 1.8.1.1.1.3 0 .4 0 .1-.1.2-.2.3l-.3.3c-.1.1-.2.2-.1.4.1.2.5 1 1.1 1.6.8.7 1.5 1 1.7 1.1.2.1.3 0 .4-.1l.6-.7c.1-.2.3-.2.5-.1.8.3 2.8 1.3 3.2 1.5.3.2.5.3.6.4.1.2.1.7-.1 1.3z"/></svg>
      </a>
      <a class="lx-site-footer-social-btn lx-site-footer-social-btn--max" href="<?php echo htmlspecialchars($lx_footer_max_channel, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" aria-label="MAX">
        <svg class="lx-site-footer-social-svg lx-site-footer-social-svg--max" viewBox="0 0 100 100" width="22" height="22" aria-hidden="true" focusable="false"><path fill="currentColor" d="M50.76 0.26c27.54 0 49.13 22.34 49.13 49.89S77.61 99.49 51.02 99.49c-9.44 0-14.01-1.33-21.37-6.54-.51-.36-1.2-.27-1.63.19-5.66 6.04-20.17 10.28-20.83 2.03 0-14.39-7.19-23.73-7.19-45.3C0 21.55 23.22 0.26 50.76 0.26zm.77 24.55c-13.06-.69-23.26 8.39-25.51 22.57-1.86 11.75 1.44 26.07 4.27 26.79 1.2.31 4.08-1.88 6.18-3.86.39-.37 1-.44 1.46-.15 3.27 2 6.97 3.5 11.05 3.71 13.41.7 25.3-9.8 26.01-23.21.7-13.41-10.02-25.14-23.43-25.84z"/></svg>
      </a>
      <a class="lx-site-footer-social-btn lx-site-footer-social-btn--vk" href="<?php echo htmlspecialchars($lx_footer_vk, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" aria-label="ВКонтакте">
        <svg class="lx-site-footer-social-svg" viewBox="0 0 24 24" width="22" height="22" aria-hidden="true" focusable="false"><path fill="currentColor" d="M12.785 16.241s.288-.032.436-.194c.136-.148.132-.427.132-.427s-.02-1.304.58-1.496c.591-.19 1.35.956 2.155 1.378.605.318 1.064.248 1.064.248l2.141-.03s1.117-.07.587-.95c-.043-.073-.308-.65-1.588-1.838-1.344-1.245-1.163-.522.454-1.598.983-.656 1.377-1.055 1.254-1.224-.116-.155-.833-.114-.833-.114l-2.375.015s-.176-.024-.306.053c-.127.075-.208.249-.208.249s-.374 1.001-.87 1.852c-1.05 1.508-1.47 1.587-1.641 1.493-.399-.214-.299-.86-.299-1.319 0-1.434.216-2.03-.422-2.186-.212-.051-.368-.085-.911-.09-.697-.007-1.286.002-1.619.166-.222.115-.393.372-.289.387.129.018.421.078.576.285.2.264.193.855.193.855s.116 1.72-.27 1.936c-.266.175-.631-.182-1.414-1.283-.4-.54-.702-1.137-.702-1.137s-.058-.142-.162-.218c-.126-.093-.302-.122-.302-.122l-2.256.014s-.338.01-.462.156c-.111.13-.008.4-.008.4s1.77 4.146 3.773 6.24c1.836 1.918 3.927 1.794 3.927 1.794h.947z"/></svg>
      </a>
    </div>
    <div class="lx-site-footer-nav">
      <a href="/politika_konfidencialnosti">Политика конфиденциальности</a>
      <a href="/private_data">Согласие на обработку персональных данных</a>
      <a href="/news/">Блог</a>
      <details class="lx-site-footer-requisites">
        <summary>Реквизиты</summary>
        <div class="lx-site-footer-requisites-panel">
          <p>Общество с ограниченной ответственностью «ЛИРЕЙТ»</p>
          <p>Директор: Горбачев Юрий Александрович</p>
          <p>Юридический адрес: 422542, Татарстан Республика, Район Зеленодольский, г.&nbsp;Зеленодольск, ул.&nbsp;Королёва, 11Б, помещ.&nbsp;30-1005</p>
          <p>Почтовый адрес: 422540, Республика Татарстан, г.&nbsp;Зеленодольск, ул.&nbsp;Карла Маркса, дом&nbsp;24, офис&nbsp;1</p>
          <p>Р/с 40702810407500006834</p>
          <p>к/с 30101810745374525104</p>
          <p>БИК 044525104</p>
          <p>Банк: Филиал Точка Публичного акционерного общества Банка «Финансовая Корпорация Открытие»</p>
        </div>
      </details>
    </div>
    <p class="lx-site-footer-copy">© <?php echo date('Y'); ?> LIREATE</p>
  </div>
</footer>
<script type="application/ld+json"><?php echo json_encode($lx_org_jsonld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?></script>

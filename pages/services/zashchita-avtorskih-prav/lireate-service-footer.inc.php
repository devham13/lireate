<?php
/**
 * Общий подвал страниц услуг LIREATE.
 * Синхронизируется в каталог услуги: lireate-service-footer.inc.php
 */
$lx_footer_phone_display = isset($lireate_phone_display) ? $lireate_phone_display : '+7 (960) 048-49-57';
$lx_footer_phone_tel = isset($lireate_phone_tel) ? $lireate_phone_tel : 'tel:+79600484957';
$lx_footer_phone2_display = '+7 (499) 938-45-95';
$lx_footer_phone2_tel = 'tel:+74999384595';
$lx_footer_email = 'info@lireate.ru';
$lx_footer_tg = isset($lireate_telegram) ? $lireate_telegram : 'https://t.me/lireate_patent';
$lx_footer_wa = isset($lireate_whatsapp) ? $lireate_whatsapp : 'https://wa.me/79600484957';
$lx_footer_max = isset($lireate_max) ? $lireate_max : 'https://max.ru/u/f9LHodD0cOI8jsPagjibEve2cn5_xPW0X1sf3PhbS5TMijA78DnUeDEpUAI';

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
    <div class="lx-site-footer-brand">
      <a class="lx-site-footer-logo" href="/">LIREATE</a>
      <p class="lx-site-footer-tagline">ООО «ЛИРЕЙТ» — регистрация и защита интеллектуальной собственности</p>
      <p class="lx-site-footer-meta">Общество с ограниченной ответственностью «ЛИРЕЙТ»</p>
      <p class="lx-site-footer-meta">Директор: Горбачев Юрий Александрович</p>
      <p class="lx-site-footer-meta">ИНН/КПП: 1648040894 / 164801001 · ОГРН: 1151673001578</p>
    </div>
    <div class="lx-site-footer-col">
      <h2 class="lx-site-footer-heading">Контакты</h2>
      <p><a href="<?php echo htmlspecialchars($lx_footer_phone_tel, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($lx_footer_phone_display, ENT_QUOTES, 'UTF-8'); ?></a></p>
      <p><a href="<?php echo htmlspecialchars($lx_footer_phone2_tel, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($lx_footer_phone2_display, ENT_QUOTES, 'UTF-8'); ?></a></p>
      <p><a href="mailto:<?php echo htmlspecialchars($lx_footer_email, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($lx_footer_email, ENT_QUOTES, 'UTF-8'); ?></a></p>
      <p class="lx-site-footer-address">Юридический адрес: 422542, Татарстан Республика, Район Зеленодольский, г.&nbsp;Зеленодольск, ул.&nbsp;Королёва, 11Б, помещ.&nbsp;30-1005</p>
      <p class="lx-site-footer-address">Почтовый адрес: 422540, Республика Татарстан, г.&nbsp;Зеленодольск, ул.&nbsp;Карла Маркса, дом&nbsp;24, офис&nbsp;1</p>
      <div class="lx-site-footer-social">
        <a href="<?php echo htmlspecialchars($lx_footer_tg, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" data-lx-icon="telegram" aria-label="Telegram"></a>
        <a href="<?php echo htmlspecialchars($lx_footer_max, ENT_QUOTES, 'UTF-8'); ?>" class="lx-site-footer-social--max" target="_blank" rel="noopener noreferrer" data-lx-icon="max-white" aria-label="MAX"></a>
        <a href="<?php echo htmlspecialchars($lx_footer_wa, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" data-lx-icon="whatsapp" aria-label="WhatsApp"></a>
      </div>
      <details class="lx-site-footer-requisites">
        <summary>Реквизиты</summary>
        <p>Р/с 40702810407500006834</p>
        <p>к/с 30101810745374525104</p>
        <p>БИК 044525104</p>
        <p>Банк: Филиал Точка Публичного акционерного общества Банка «Финансовая Корпорация Открытие»</p>
      </details>
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
<script type="application/ld+json"><?php echo json_encode($lx_org_jsonld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?></script>

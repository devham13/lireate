<?php
/**
 * JSON-LD главной — parity с live (Organization + LegalService).
 */
$lh_schema_origin = '/';
if (defined('LIREATE_PREVIEW_LIVE_ORIGIN')) {
    $lh_schema_origin = LIREATE_PREVIEW_LIVE_ORIGIN . '/';
} elseif (defined('LIREATE_HOME_CANONICAL_ORIGIN') && LIREATE_HOME_CANONICAL_ORIGIN !== '') {
    $lh_schema_origin = rtrim((string) LIREATE_HOME_CANONICAL_ORIGIN, '/') . '/';
}
$lh_site_mail = 'info@' . 'lire' . 'ate.ru';
?>
<script type="application/ld+json">
<?php
echo json_encode(array(
    '@context' => 'https://schema.org',
    '@graph' => array(
        array(
            '@type' => 'Organization',
            '@id' => $lh_schema_origin . '#organization',
            'name' => 'ООО «ЛИРЕЙТ»',
            'legalName' => 'ООО «ЛИРЕЙТ»',
            'url' => $lh_schema_origin,
            'email' => $lh_site_mail,
            'telephone' => array('+7-499-938-45-95', '+7-960-048-49-57'),
            'address' => array(
                '@type' => 'PostalAddress',
                'streetAddress' => 'ул. Карла Маркса, 24, оф. 1',
                'addressLocality' => 'Зеленодольск',
                'addressCountry' => 'RU',
            ),
        ),
        array(
            '@type' => 'LegalService',
            '@id' => $lh_schema_origin . '#legalservice',
            'name' => 'ЛИРЕЙТ — патентное бюро',
            'url' => $lh_schema_origin,
            'provider' => array('@id' => $lh_schema_origin . '#organization'),
            'areaServed' => 'RU',
            'address' => array(
                '@type' => 'PostalAddress',
                'streetAddress' => 'ул. Королёва, 11Б, помещ. 30-1005',
                'addressLocality' => 'Зеленодольск',
                'addressCountry' => 'RU',
            ),
        ),
    ),
), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>
</script>
<script type="application/ld+json">
<?php
echo json_encode(array(
    '@context' => 'https://schema.org',
    '@type' => 'Organization',
    'name' => 'LIREATE',
    'legalName' => 'Общество с ограниченной ответственностью «ЛИРЕЙТ»',
    'alternateName' => 'ООО «ЛИРЕЙТ»',
    'url' => $lh_schema_origin,
    'email' => $lh_site_mail,
    'taxID' => '1648040894',
    'identifier' => array(
        '@type' => 'PropertyValue',
        'propertyID' => 'ОГРН',
        'value' => '1151673001578',
    ),
    'telephone' => array('+7-960-048-49-57', '+7-499-938-45-95'),
    'address' => array(
        '@type' => 'PostalAddress',
        'streetAddress' => 'ул. Королёва, 11Б, помещ. 30-1005',
        'addressLocality' => 'Зеленодольск',
        'addressRegion' => 'Республика Татарстан',
        'postalCode' => '422542',
        'addressCountry' => 'RU',
    ),
), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>
</script>
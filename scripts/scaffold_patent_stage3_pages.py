#!/usr/bin/env python3
"""Scaffold stage-3 patent service directories."""

from pathlib import Path

REPO = Path(__file__).resolve().parents[1]
SERVICES = REPO / "pages" / "services"
HTACCESS = """# LIREATE: каталог услуги — отдавать index.php (корневой rewrite CMS иначе даёт 404 на /slug/)
DirectoryIndex index.php
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteRule ^$ index.php [END]
</IfModule>
"""

PAGES = [
    {
        "slug": "patentnye-zadachi-oplata-patentnyh-poshlin",
        "url_path": "patentnye-zadachi/oplata-patentnyh-poshlin",
        "path": "/patentnye-zadachi/oplata-patentnyh-poshlin/",
        "title_seo": "Оплата патентных пошлин Роспатента — LIREATE",
        "desc": "Черновик платёжных реквизитов и QR по подтверждённому профилю Роспатента. Сумма и назначение — в браузере, без отправки на сервер.",
        "h1": "Оплата патентных пошлин",
        "eyebrow": "Патентные задачи · платежи",
        "lead": "Реквизиты по <a href=\"https://rospatent.gov.ru/content/uploadfiles/rekviziti-pril1.pdf\" target=\"_blank\" rel=\"noopener noreferrer\">приложению №1 к приказу Роспатента № 167</a>. Образец — черновик для сверки в банке.",
        "breadcrumb": "Оплата пошлин",
        "data_attr": 'data-lx-patent-pay="patent"',
        "extra_script": "",
        "calc_link": '<p class="lx-hint"><a href="/patentnye-zadachi/poshliny-registraciya/">Калькулятор регистрации</a> · <a href="/patentnye-zadachi/poshliny-prodlenie/">продление</a></p>',
    },
    {
        "slug": "patentnye-zadachi-oplata-uslug-fips",
        "url_path": "patentnye-zadachi/oplata-uslug-fips",
        "path": "/patentnye-zadachi/oplata-uslug-fips/",
        "title_seo": "Оплата платных услуг ФИПС — LIREATE",
        "desc": "Справка об оплате платных услуг ФИПС: отдельный профиль реквизитов. При неподтверждённых данных — переход на официальный источник.",
        "h1": "Оплата платных услуг ФИПС",
        "eyebrow": "Патентные задачи · платные услуги",
        "lead": "Платные услуги ФИПС отделены от патентных пошлин. Реквизиты сверяются только с официальной публикацией.",
        "breadcrumb": "Оплата услуг ФИПС",
        "data_attr": 'data-lx-patent-pay="fips"',
        "extra_script": "",
        "calc_link": "",
    },
    {
        "slug": "patentnye-zadachi-poisk-platezha-evm",
        "url_path": "patentnye-zadachi/poisk-platezha-evm",
        "path": "/patentnye-zadachi/poisk-platezha-evm/",
        "title_seo": "Поиск платежа по ПрЭВМ — LIREATE",
        "desc": "Справочная страница: какие данные понадобятся для сверки платежа за регистрацию ПрЭВМ. Поиск выполняется в официальном контуре, не на LIREATE.",
        "h1": "Поиск платежа по ПрЭВМ",
        "eyebrow": "Патентные задачи · ПрЭВМ",
        "lead": "Интеграция с официальным поиском платежей не подключена в публичном контуре. Данные формы не передаются с сайта.",
        "breadcrumb": "Поиск платежа ПрЭВМ",
        "data_attr": 'data-lx-patent-evm-search',
        "extra_script": "",
        "calc_link": '<p class="lx-hint"><a href="/registraciya_programm_evm/">Регистрация программ для ЭВМ</a> · <a href="https://rospatent.gov.ru/ru/activities/dues/stateduty/gos_poshl" target="_blank" rel="noopener noreferrer">Правила оплаты госпошлины</a></p>',
    },
    {
        "slug": "patentnye-zadachi-starye-zayavki",
        "url_path": "patentnye-zadachi/starye-zayavki",
        "path": "/patentnye-zadachi/starye-zayavki/",
        "title_seo": "Старые заявки в АРМ «Регистратор» — LIREATE",
        "desc": "Переход к официальному личному кабинету ФИПС для работы со своими заявками. LIREATE не принимает учётные данные ФИПС.",
        "h1": "Старые заявки",
        "eyebrow": "Патентные задачи · АРМ",
        "lead": "Доступ к документам заявки — только под вашей учётной записью на стороне ФИПС.",
        "breadcrumb": "Старые заявки",
        "data_attr": 'data-lx-patent-old-apps',
        "extra_script": "",
        "calc_link": '<p class="lx-hint"><a href="https://www.fips.ru/podacha-zayavki/podacha-zayavki-na-tovarnyy-znak/user-guide-api-arm2024.pdf" target="_blank" rel="noopener noreferrer">Руководство API АРМ</a></p>',
    },
]

INDEX_TEMPLATE = '''<?php
header('Content-Type: text/html; charset=utf-8');
$path = '{path}';
$host = isset($_SERVER['HTTP_HOST']) ? preg_replace('/[^a-zA-Z0-9.\\-:]/', '', $_SERVER['HTTP_HOST']) : '';
$site_root = ($host !== '' ? 'https://' . $host : '');
$canonical = $site_root . $path;
$seo_title = '{title_seo}';
$seo_description = '{desc}';
$lireate_telegram = 'https://t.me/lireate_patent';
require_once dirname(__DIR__) . '/_shared/lireate-service-messenger-include.inc.php';
require_once dirname(__DIR__) . '/_shared/lireate-service-assets.php';
require_once dirname(__DIR__) . '/_shared/lireate-patent-breadcrumbs.php';
$lx_service_asset_dir = __DIR__;
$lireate_metrika_id = getenv('LIREATE_YANDEX_METRIKA_ID');
if ($lireate_metrika_id === false || $lireate_metrika_id === '') {{
    $lireate_metrika_id = '526' . '22734';
}}
$lx_breadcrumb_current = '{breadcrumb}';
$lx_patent_cta_href = $lireate_telegram;
$lx_patent_cta_external = true;
$lx_pay_profiles_json = lireate_service_asset_href('assets/lireate-shared/lireate-patent-payment-profiles.json', $lx_service_asset_dir);
$schema_graph = array(
  '@context' => 'https://schema.org',
  '@graph' => array(
    array('@type' => 'WebApplication', 'name' => '{breadcrumb}', 'url' => $canonical),
    array('@type' => 'BreadcrumbList', 'itemListElement' => lireate_patent_breadcrumb_schema_items($site_root, '{breadcrumb}', $canonical)),
  ),
);
?><!DOCTYPE html>
<html lang="ru" data-lireate-metrika-id="<?php echo htmlspecialchars(preg_replace('/\\D/', '', (string) $lireate_metrika_id), ENT_QUOTES, 'UTF-8'); ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo htmlspecialchars($seo_title, ENT_QUOTES, 'UTF-8'); ?></title>
  <meta name="description" content="<?php echo htmlspecialchars($seo_description, ENT_QUOTES, 'UTF-8'); ?>">
  <link rel="canonical" href="<?php echo htmlspecialchars($canonical, ENT_QUOTES, 'UTF-8'); ?>">
  <link rel="stylesheet" href="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-service-dark.css', $lx_service_asset_dir); ?>">
  <link rel="stylesheet" href="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-patent-tools.css', $lx_service_asset_dir); ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
  <script type="application/ld+json"><?php echo json_encode($schema_graph, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?></script>
</head>
<body class="lireate-svc-body lx-patent-tool">
<?php include dirname(__DIR__) . '/_shared/blocks/lireate-patent-header.inc.php'; ?>
<main id="primary" class="lireate-svc-page" role="main" tabindex="-1" {data_attr} data-lx-pay-profiles-json="<?php echo htmlspecialchars($lx_pay_profiles_json, ENT_QUOTES, 'UTF-8'); ?>">
<?php include dirname(__DIR__) . '/_shared/blocks/lireate-patent-breadcrumbs.inc.php'; ?>
  <section class="lx-hero lx-hero--stack">
    <div>
      <p class="lx-eyebrow">{eyebrow}</p>
      <h1>{h1}</h1>
      <p class="lx-hero-lead">{lead}</p>
    </div>
  </section>
  <section class="lx-section lx-section--tool" id="tool" data-lx-pay-tool></section>
</main>
<script src="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-patent-payment-core.js', $lx_service_asset_dir); ?>"></script>
<script src="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-qrcode.min.js', $lx_service_asset_dir); ?>"></script>
<script src="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-patent-tools.js', $lx_service_asset_dir); ?>"></script>
<script src="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-patent-stage3.js', $lx_service_asset_dir); ?>"></script>
<?php
$lx_patent_footer_mode = 'reference';
$lx_patent_footer_ref_url = 'https://rospatent.gov.ru/ru/activities/dues/req';
$lireate_max = LIREATE_MAX_CHAT_URL;
include dirname(__DIR__) . '/_shared/blocks/lireate-patent-tool-footer.inc.php';
?>
'''


def main():
    for p in PAGES:
        d = SERVICES / p["slug"]
        d.mkdir(parents=True, exist_ok=True)
        (d / ".htaccess").write_text(HTACCESS, encoding="utf-8")
        (d / "index.php").write_text(INDEX_TEMPLATE.format(**p), encoding="utf-8")
        manifest = {
            "slug": p["slug"],
            "url_path": p["url_path"],
            "title": p["h1"],
            "description": p["desc"],
            "page_kind": "patent_tool",
        }
        import json

        (d / "publish.manifest.json").write_text(
            json.dumps(manifest, ensure_ascii=False, indent=2) + "\n", encoding="utf-8"
        )
        print("OK", p["slug"])


if __name__ == "__main__":
    main()
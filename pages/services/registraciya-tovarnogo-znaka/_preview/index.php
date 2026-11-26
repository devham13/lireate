<?php
/**
 * Nav V3 AFTER: CMS chrome + полный longread услуги (локальный рендер pages/services).
 */
$repo = dirname(__DIR__, 4);
$_SERVER['DOCUMENT_ROOT'] = $repo;
$_SERVER['REQUEST_URI'] = '/registraciya_tovarnogo_znaka/';
$_SERVER['HTTP_HOST'] = '127.0.0.1';
if (!defined('LIREATE_NAV_V3_ASSET_QS')) {
    define('LIREATE_NAV_V3_ASSET_QS', '20261126r5');
}
if (!defined('LIREATE_RTZ_CHROME_PREVIEW')) {
    define('LIREATE_RTZ_CHROME_PREVIEW', true);
}
require_once $repo . '/pages/_shared/lireate-preview-bootstrap.php';
require_once $repo . '/pages/_shared/lireate-public-cms-chrome-lib.php';
require_once $repo . '/pages/services/_shared/lireate-public-cms-chrome-wire.inc.php';

ob_start();
$svc_dir = dirname(__DIR__);
chdir($svc_dir);
include $svc_dir . '/index.php';
$full = ob_get_clean();

if (!preg_match('/<head[^>]*>(.*)<\/head>/is', $full, $head_match)) {
    http_response_code(500);
    echo 'Preview: cannot parse service head';
    return;
}
if (!preg_match('/<body[^>]*>(.*)<\/body>/is', $full, $body_match)) {
    http_response_code(500);
    echo 'Preview: cannot parse service body';
    return;
}
$rtz_body_class = 'lireate-svc-body registraciya-landing-body registraciya-pilot-v2 lx-tm-white lireate-theme-light-v4';
if (preg_match('/<body[^>]*class=["\']([^"\']*)["\']/i', $full, $bc)) {
    $rtz_body_class = trim($bc[1]);
}

preg_match_all('/<link[^>]+rel=["\']stylesheet["\'][^>]*>/i', $head_match[1], $link_tags);
preg_match_all('/<script[^>]+type=["\']application\/ld\+json["\'][^>]*>.*?<\/script>/is', $head_match[1], $schema_tags);
$title = 'Регистрация товарного знака — Nav V3 preview';
if (preg_match('/<title[^>]*>([^<]*)<\/title>/i', $head_match[1], $tm)) {
    $title = trim(html_entity_decode($tm[1], ENT_QUOTES, 'UTF-8'));
}

$body = $body_match[1];
$body = preg_replace('/<div class="lx-nav-shell">.*?<\/div>\s*/is', '', $body, 1);
$body = str_replace('href="/_shared/', 'href="/pages/services/_shared/', $body);
$body = str_replace('src="/_shared/', 'src="/pages/services/_shared/', $body);

?><!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></title>
  <meta name="robots" content="noindex,nofollow">
  <base href="/pages/services/registraciya-tovarnogo-znaka/">
<?php
foreach ($link_tags[0] as $tag) {
    $tag = str_replace('href="/_shared/', 'href="/pages/services/_shared/', $tag);
    echo $tag, "\n";
}
foreach ($schema_tags[0] as $tag) {
    echo $tag, "\n";
}
?>
  <style>
    body.lireate-rtz-nav-v3-preview .lx-nav-shell { display: none !important; }
    body.lireate-rtz-nav-v3-preview .lireate-public-cms-content {
      max-width: 100% !important;
      overflow-x: hidden !important;
      box-sizing: border-box;
    }
    body.lireate-rtz-nav-v3-preview .lireate-svc-page {
      max-width: 100% !important;
      overflow-x: hidden !important;
      box-sizing: border-box;
    }
    body.lireate-rtz-nav-v3-preview .lh4-subnav-in {
      padding-left: clamp(12px, 1.5vw, 24px);
      padding-right: clamp(12px, 1.5vw, 24px);
    }
    body.lireate-rtz-nav-v3-preview .lh4-subnav-links a {
      font-size: clamp(13px, 1vw, 16px);
    }
    body.lireate-rtz-nav-v3-preview .lh4-subnav-right {
      flex: 0 0 auto !important;
      min-width: max-content;
      gap: 8px;
    }
    body.lireate-rtz-nav-v3-preview .lh4-subnav-cta.btn.primary {
      display: inline-flex !important;
      white-space: nowrap;
    }
  </style>
</head>
<body class="<?php echo htmlspecialchars($rtz_body_class . ' lireate-public-cms-chrome lireate-rtz-nav-v3-preview lireate-screenshot-capture', ENT_QUOTES, 'UTF-8'); ?>">
<?php
lireate_public_cms_chrome_wire_open();
echo $body;
lireate_public_cms_chrome_wire_close();
$scripts = array();
if (preg_match_all('/<script[^>]+src=[^>]+><\/script>/i', $body_match[1], $scr)) {
    $scripts = $scr[0];
}
foreach ($scripts as $tag) {
    echo $tag, "\n";
}
?>
</body>
</html>
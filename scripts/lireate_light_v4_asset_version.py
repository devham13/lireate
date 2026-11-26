"""Single cache-bust token for Light V4 CMS + main.js (installers bump hosting URLs)."""

LIGHT_V4_ASSET_VERSION = "20261126r20"
MAIN_JS_QUERY_VERSION = "20261126r20"
# Home hero CSS only (template/css/lireate-home-light-v4.css): bumped separately so live
# cache-bust is a one-line change in template/main.php (head snippet marker
# lireate-home-light-v4-install-head:v1).
HOME_CSS_VERSION = "20261126r26"

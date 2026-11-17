#!/usr/bin/env bash
# Превью раздела «Патентные задачи» с вложенными url_path.
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
DOCROOT="$ROOT/.dev-docroot-patent"
PORT="${LIREATE_PREVIEW_PORT:-8891}"
rm -rf "$DOCROOT"
mkdir -p "$DOCROOT/_shared" "$DOCROOT/patentnye-zadachi/_shared"
ln -sfn "$ROOT/pages/services/_shared" "$DOCROOT/_shared"
ln -sfn "$ROOT/pages/services/_shared" "$DOCROOT/patentnye-zadachi/_shared"

# Каталог: файлы в корне /patentnye-zadachi/ (как после publish url_path patentnye-zadachi)
for item in "$ROOT/pages/services/patentnye-zadachi"/*; do
  base="$(basename "$item")"
  if [[ "$base" == "assets" ]]; then
    ln -sfn "$item" "$DOCROOT/patentnye-zadachi/assets"
  else
    ln -sfn "$item" "$DOCROOT/patentnye-zadachi/$base"
  fi
done

link_sub() {
  local slug="$1"
  local sub="$2"
  ln -sfn "$ROOT/pages/services/$slug" "$DOCROOT/patentnye-zadachi/$sub"
}
link_sub patentnye-zadachi-poshliny-registraciya poshliny-registraciya
link_sub patentnye-zadachi-poshliny-prodlenie poshliny-prodlenie
link_sub patentnye-zadachi-redaktor-mktu redaktor-mktu
link_sub patentnye-zadachi-adres-zayavitelya adres-zayavitelya
link_sub patentnye-zadachi-fail-slovesnogo-znaka fail-slovesnogo-znaka
link_sub patentnye-zadachi-poshliny-izmeneniya poshliny-izmeneniya
link_sub patentnye-zadachi-sokrashchenie-mktu sokrashchenie-mktu
link_sub patentnye-zadachi-kontakty-fips kontakty-fips
link_sub patentnye-zadachi-oplata-patentnyh-poshlin oplata-patentnyh-poshlin
link_sub patentnye-zadachi-oplata-uslug-fips oplata-uslug-fips
link_sub patentnye-zadachi-poisk-platezha-evm poisk-platezha-evm
link_sub patentnye-zadachi-starye-zayavki starye-zayavki

export LIREATE_PREVIEW_DOCROOT="$DOCROOT"
echo "Patent preview:"
echo "  http://127.0.0.1:${PORT}/patentnye-zadachi/"
echo "  http://127.0.0.1:${PORT}/patentnye-zadachi/poshliny-registraciya/"
exec php -S "127.0.0.1:${PORT}" -t "$DOCROOT" "$ROOT/scripts/lireate_preview_proxy_router.php"
#!/usr/bin/env bash
# Локальный просмотр услуг как на хостинге: /<url_path>/index.php + assets/
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
DOCROOT="$ROOT/.dev-docroot"
PORT="${LIREATE_PREVIEW_PORT:-8888}"
mkdir -p "$DOCROOT"
link() {
  local slug="$1" path="$2"
  ln -sfn "$ROOT/pages/services/$slug" "$DOCROOT/$path"
}
link zashchita-avtorskih-prav zashchita_avtorskih_prav
link prodlenie-tovarnogo-znaka prodlenie_tovarnogo_znaka
link registraciya-tovarnogo-znaka-v-kitae registraciya_tovarnogo_znaka_v_kitae
link proverka-tovarnogo-znaka findtz
link registraciya-tovarnogo-znaka registraciya_tovarnogo_znaka
ln -sfn "$ROOT/template" "$DOCROOT/template"
ln -sfn "$ROOT/pages/services/_shared" "$DOCROOT/_shared"
echo "Preview: http://127.0.0.1:${PORT}/findtz/ (and registraciya_tovarnogo_znaka/)"
exec php -S "127.0.0.1:${PORT}" -t "$DOCROOT"

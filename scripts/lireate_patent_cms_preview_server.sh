#!/usr/bin/env bash
# Превью /patent и /patent_prom_obr с sidebar V7 (pages/patents/_preview/shell.php).
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
DOCROOT="$ROOT/.dev-docroot-patent-cms"
PORT="${LIREATE_PREVIEW_PORT:-8892}"
rm -rf "$DOCROOT"
mkdir -p "$DOCROOT/patent" "$DOCROOT/patent_prom_obr"
ln -sfn "$ROOT/template" "$DOCROOT/template"
ln -sfn "$ROOT/pages" "$DOCROOT/pages"
ln -sfn "$ROOT/pages/patents/_preview/shell.php" "$DOCROOT/patent/index.php"
ln -sfn "$ROOT/pages/patents/_preview/shell.php" "$DOCROOT/patent_prom_obr/index.php"
export LIREATE_PREVIEW_DOCROOT="$DOCROOT"
echo "Patent CMS preview: http://127.0.0.1:${PORT}/patent/?page=patent"
echo "Prom: http://127.0.0.1:${PORT}/patent_prom_obr/?page=patent_prom_obr"
exec php -S "127.0.0.1:${PORT}" -t "$DOCROOT"
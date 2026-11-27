#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
DOCROOT="$ROOT/.dev-docroot-mktu"
PORT="${LIREATE_PREVIEW_PORT:-8893}"
rm -rf "$DOCROOT"
mkdir -p "$DOCROOT/mktu"
ln -sfn "$ROOT/template" "$DOCROOT/template"
ln -sfn "$ROOT/pages" "$DOCROOT/pages"
ln -sfn "$ROOT/pages/mktu/_preview/sidebar-shell.php" "$DOCROOT/mktu/index.php"
echo "MKTU sidebar preview: http://127.0.0.1:${PORT}/mktu/"
exec php -S "127.0.0.1:${PORT}" -t "$DOCROOT"
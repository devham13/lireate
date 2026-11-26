#!/usr/bin/env bash
# Реальный рендер: fixture docroot + index.php + template/main.php (не _preview).
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"
export LIREATE_MYSQL_HOST="${LIREATE_MYSQL_HOST:-127.0.0.1}"
export LIREATE_MYSQL_USER="${LIREATE_MYSQL_USER:-root}"
export LIREATE_MYSQL_PASSWORD="${LIREATE_MYSQL_PASSWORD:-root}"
export LIREATE_MYSQL_DATABASE="${LIREATE_MYSQL_DATABASE:-lireate_ci}"
DOCROOT="$(python3 scripts/prepare_light_v4_fixture_screenshots.py 2>&1 | tee /tmp/lireate-fixture-prepare.log | tail -1)"
if [[ ! -d "$DOCROOT" ]]; then
  echo "BLOCKER: fixture DOCROOT missing: $DOCROOT" >&2
  exit 1
fi
export LIREATE_FIXTURE_DOCROOT="$DOCROOT"
export LIREATE_PREVIEW_BASE="http://127.0.0.1:19080"
ROUTER="$ROOT/scripts/light_v4_main_render_router.php"
pkill -f 'php -S 127.0.0.1:19080' 2>/dev/null || true
sleep 0.5
php -S 127.0.0.1:19080 -t "$DOCROOT" "$ROUTER" &
SRV_PID=$!
trap 'kill "$SRV_PID" 2>/dev/null || true' EXIT
sleep 1
node scripts/capture_light_v4_screenshots.mjs
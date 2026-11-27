#!/usr/bin/env bash
# Запуск одного канала аудита: unlighthouse | siteone | indexlift | all
set -euo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"
TARGET="${1:-all}"
if [[ -z "${SEO_SITE_URL:-}" && -f .env ]]; then
  # shellcheck disable=SC1091
  set -a && source .env && set +a
fi
: "${SEO_SITE_URL:?SEO_SITE_URL missing in environment or .env}"
python3 init-env.py 2>/dev/null || true
mkdir -p vps/data/siteone vps/data/indexlift unlighthouse/reports/site

run_unlighthouse() {
  docker compose -f compose.yaml -f vps/compose.stack.yaml build unlighthouse
  docker compose -f compose.yaml -f vps/compose.stack.yaml run --rm --no-deps \
    -e "SEO_REPORT_DIR=/reports/site" unlighthouse npm run audit
}

run_siteone() {
  docker compose -f compose.yaml -f vps/compose.stack.yaml --profile runners build siteone
  docker compose -f compose.yaml -f vps/compose.stack.yaml --profile runners run --rm siteone
}

run_indexlift() {
  docker compose -f compose.yaml -f vps/compose.stack.yaml --profile runners build indexlift
  docker compose -f compose.yaml -f vps/compose.stack.yaml --profile runners run --rm indexlift
}

case "$TARGET" in
  unlighthouse) run_unlighthouse ;;
  siteone) run_siteone ;;
  indexlift) run_indexlift ;;
  all)
    run_unlighthouse
    run_siteone
    run_indexlift
    ;;
  *) echo "Usage: $0 [unlighthouse|siteone|indexlift|all]" >&2; exit 2 ;;
esac
echo "AUDIT_DONE:$TARGET:$(date -Is)"
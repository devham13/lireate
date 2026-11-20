#!/usr/bin/env bash
set -euo pipefail
SEO_TOOLS_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)"
cd "$SEO_TOOLS_DIR"
command -v docker >/dev/null || { echo 'Docker is required on the VPS.' >&2; exit 1; }
docker compose version >/dev/null
case "${1:-site}" in
  site) audit_command='audit'; audit_output='/reports/site' ;;
  smoke) audit_command='audit:smoke'; audit_output='/reports/smoke' ;;
  *) echo 'Usage: bash audit.sh [site|smoke]' >&2; exit 2 ;;
esac
python3 init-env.py
docker compose config --quiet
docker compose build unlighthouse
docker compose run --rm --no-deps -e "SEO_REPORT_DIR=$audit_output" unlighthouse npm run "$audit_command"

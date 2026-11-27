#!/usr/bin/env bash
# Продолжение установки на VPS (/opt/lireate-seo), без переустановки SEOnaut/MySQL.
set -euo pipefail

SEO_ROOT="${LIREATE_SEO_ROOT:-/opt/lireate-seo}"
REPO_URL="${LIREATE_OFFICE_GIT_URL:-https://github.com/devham13/lireate.git}"
REPO_REF="${LIREATE_OFFICE_GIT_REF:-main}"

command -v docker >/dev/null || { echo "Docker required" >&2; exit 1; }
docker compose version >/dev/null
[[ "$(uname -m)" == "x86_64" ]] || { echo "amd64 VPS required for pinned SEOnaut image" >&2; exit 1; }

mkdir -p "$SEO_ROOT"
cd "$SEO_ROOT"

if [[ ! -d office-repo/.git ]]; then
  git clone --depth 1 --branch "$REPO_REF" "$REPO_URL" office-repo
else
  git -C office-repo fetch origin "$REPO_REF"
  git -C office-repo checkout -B deploy "$REPO_REF"
  git -C office-repo reset --hard "origin/$REPO_REF" 2>/dev/null || git -C office-repo reset --hard "$REPO_REF"
fi

STACK="$SEO_ROOT/office-repo/tools/seo"
cd "$STACK"
python3 init-env.py

if ! grep -q '^CABINET_PASSWORD_HASH=' .env 2>/dev/null; then
  CABINET_PASS="${CABINET_PASSWORD:-$(openssl rand -hex 12)}"
  HASH="$(docker run --rm caddy:2.8-alpine caddy hash-password --plaintext "$CABINET_PASS" 2>/dev/null | tail -1)"
  {
    echo "CABINET_PORT=19080"
    echo "CABINET_USER=lireate"
    echo "CABINET_PASSWORD_HASH=$HASH"
  } >> .env
  echo "Cabinet login: lireate / $CABINET_PASS (stored hash in $STACK/.env)"
fi

docker compose -f compose.yaml -f vps/compose.stack.yaml config --quiet
docker compose -f compose.yaml -f vps/compose.stack.yaml up -d --wait db seonaut cabinet 2>/dev/null \
  || docker compose -f compose.yaml -f vps/compose.stack.yaml up -d db seonaut cabinet

if [[ $EUID -eq 0 ]]; then
  install -m 0644 /dev/stdin /etc/cron.d/lireate-seo-vps <<EOF
0 3 * * 0 root cd $STACK && bash vps/run-audit-all.sh unlighthouse >>$SEO_ROOT/logs/audit.log 2>&1
0 4 * * 1 root cd $STACK && bash vps/run-audit-all.sh siteone >>$SEO_ROOT/logs/audit.log 2>&1
0 5 * * * root cd $STACK && bash vps/run-audit-all.sh indexlift >>$SEO_ROOT/logs/audit.log 2>&1
0 6 * * 2 root cd $STACK && bash vps/agent/dispatch-github-fix.sh >>$SEO_ROOT/logs/dispatch.log 2>&1
EOF
  mkdir -p "$SEO_ROOT/logs"
  echo "Cron installed: /etc/cron.d/lireate-seo-vps"
fi

echo "OK: SEOnaut loopback :${SEONAUT_PORT:-19000}, cabinet loopback :${CABINET_PORT:-19080}"
echo "Next: bash vps/ssh/apply-hardening.sh (only after SSH key login works)"
echo "Full audit: bash vps/run-audit-all.sh all"
#!/usr/bin/env bash
# Установка на VPS, когда репозиторий уже в /opt/lireate-seo (не office-repo).
set -euo pipefail

SEO_ROOT="${LIREATE_SEO_ROOT:-/opt/lireate-seo}"
STACK="$SEO_ROOT/tools/seo"
GIT_REF="${LIREATE_OFFICE_GIT_REF:-cursor/lireate-seo-vps-0b9b}"

cd "$SEO_ROOT"
git fetch origin "$GIT_REF"
git checkout -B lireate-seo-control "origin/$GIT_REF"

cd "$STACK"
python3 init-env.py
mkdir -p "$SEO_ROOT/logs" vps/data/siteone vps/data/indexlift unlighthouse/reports/site

if ! grep -q '^CABINET_PASSWORD_HASH=' .env; then
  CABINET_PASS="${CABINET_PASSWORD:-$(openssl rand -hex 10)}"
  HASH="$(docker run --rm caddy:2.8-alpine caddy hash-password --plaintext "$CABINET_PASS")"
  {
    echo "CABINET_PORT=19080"
    echo "CABINET_BIND=127.0.0.1"
    echo "CABINET_PUBLIC_PORT=18443"
    echo "CABINET_PUBLIC_BIND=0.0.0.0"
    echo "CABINET_USER=lireate"
    echo "CABINET_PASSWORD_HASH=$HASH"
  } >> .env
  echo "CABINET_PASS=$CABINET_PASS" >"$SEO_ROOT/logs/cabinet-credentials.txt"
  chmod 600 "$SEO_ROOT/logs/cabinet-credentials.txt"
  echo "Cabinet: user=lireate pass=$CABINET_PASS"
fi

# Только новые сервисы — db/seonaut не пересоздаём без необходимости
export COMPOSE_PROJECT_NAME=lireate-seo-tools
docker compose -f compose.yaml -f vps/compose.stack.yaml up -d cabinet

install -m 0644 /dev/stdin /etc/cron.d/lireate-seo-vps <<EOF
SHELL=/bin/bash
0 3 * * 0 root cd $STACK && set -a && source .env && set +a && bash vps/run-audit-all.sh unlighthouse >>$SEO_ROOT/logs/audit.log 2>&1
0 4 * * 1 root cd $STACK && set -a && source .env && set +a && bash vps/run-audit-all.sh siteone >>$SEO_ROOT/logs/audit.log 2>&1
5 5 * * * root cd $STACK && set -a && source .env && set +a && bash vps/run-audit-all.sh indexlift >>$SEO_ROOT/logs/audit.log 2>&1
0 6 * * 2 root cd $STACK && set -a && source .env && set +a && bash vps/agent/dispatch-github-fix.sh >>$SEO_ROOT/logs/dispatch.log 2>&1
EOF

systemctl enable docker 2>/dev/null || true
echo "INSTALL_VPS_ROOT_DONE"
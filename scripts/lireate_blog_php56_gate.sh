#!/usr/bin/env bash
# PHP 5.6 gate: static scan, php -l, and blog --diag (Docker php:5.6-cli when needed).
set -euo pipefail
REPO_ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$REPO_ROOT"

if [[ -n "${LIREATE_PHP_BIN:-}" ]]; then
  :
elif [[ -x /opt/php/5.6/bin/php ]]; then
  export LIREATE_PHP_BIN=/opt/php/5.6/bin/php
elif command -v docker >/dev/null 2>&1 || sudo docker info >/dev/null 2>&1; then
  DOCKER_BIN="docker"
  if ! docker info >/dev/null 2>&1; then
    DOCKER_BIN="sudo docker"
  fi
  $DOCKER_BIN pull php:5.6-cli >/dev/null
  WRAP="$REPO_ROOT/scripts/.lireate-php56-docker-wrap.sh"
  cat > "$WRAP" <<EOF
#!/bin/sh
exec ${DOCKER_BIN} run --rm -i -v "${REPO_ROOT}:/work" -w /work php:5.6-cli php "\$@"
EOF
  chmod +x "$WRAP"
  export LIREATE_PHP_BIN="$WRAP"
else
  echo "BLOCKER: need /opt/php/5.6/bin/php, LIREATE_PHP_BIN, or docker with php:5.6-cli"
  exit 2
fi

export LIREATE_OFFICE_REPO="$REPO_ROOT"

echo "=== PHP 5.6 source scan ==="
python3 scripts/lireate_blog_php56_gate.py --scan-only

echo "=== php -l (5.6) ==="
python3 scripts/lireate_blog_php56_gate.py --lint-only

echo "=== blog --diag (5.6 sandbox) ==="
TMP="$(mktemp -d)"
DOC="$TMP/docroot"
mkdir -p "$DOC/template/page/news" "$DOC/data"
cp scripts/fixtures/cms-news-list-template.php "$DOC/template/page/news/blog.php"
echo '<?php' > "$DOC/data/db.php"
ENV="$TMP/.env"
echo "LIREATE_DOCROOT=$DOC" > "$ENV"
export LIREATE_ENV_FILE="$ENV"
python3 scripts/lireate_blog_install.py --diag

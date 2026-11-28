#!/usr/bin/env bash
# Blog SEO wave: template install + MySQL meta apply (REG.RU server-side).
# Run on the hosting shell (not from Cloud). Requires LIREATE_DOCROOT in autopub .env.
set -euo pipefail

AUTOPUB="${LIREATE_AUTOPUB_HOME:-$HOME/lireate-autopub}"
OFFICE="${LIREATE_OFFICE_REPO:-$AUTOPUB/office-repo}"
NEWS="${LIREATE_NEWS_REPO:-$AUTOPUB/repo}"
PHP56="${PHP_BIN:-/opt/php/5.6/bin/php}"
PY="$(cat "$AUTOPUB/.python-interpreter" 2>/dev/null || echo python3)"
STAMP="$(TZ=Europe/Moscow date +%Y%m%dT%H%M%S)"
BACKUP="$AUTOPUB/backups/blog-seo-20261127/$STAMP"
export LIREATE_ALLOW_PUBLISH="${LIREATE_ALLOW_PUBLISH:-yes}"

mkdir -p "$BACKUP"

echo "=== git office-repo ==="
cd "$OFFICE"
git fetch origin main
git checkout -B main origin/main
"$PY" scripts/lireate_blog_sync_template.py
"$PY" scripts/lireate_blog_install.py --apply

DOCROOT="${LIREATE_DOCROOT:-}"
if [[ -z "$DOCROOT" && -f "$AUTOPUB/.env" ]]; then
  # shellcheck disable=SC1090
  source "$AUTOPUB/.env"
  DOCROOT="${LIREATE_DOCROOT:-}"
fi
if [[ -z "$DOCROOT" || ! -d "$DOCROOT" ]]; then
  echo "BLOCKER: set LIREATE_DOCROOT in $AUTOPUB/.env" >&2
  exit 2
fi

echo "=== git lireate-news ==="
cd "$NEWS"
git fetch origin main
git checkout -B main origin/main

PLAN="$NEWS/data/blog-seo-grok-wave1.json"
test -f "$PLAN"

echo "=== dry-run SEO apply ==="
"$PHP56" "$NEWS/scripts/lireate_blog_seo_apply.php" "$DOCROOT" "$PLAN" --dry-run \
  --backup-dir "$BACKUP/news-meta-dry" | tee "$BACKUP/apply-dry-run.json"

echo "=== apply SEO meta ==="
"$PHP56" "$NEWS/scripts/lireate_blog_seo_apply.php" "$DOCROOT" "$PLAN" \
  --backup-dir "$BACKUP/news-meta"

touch "$DOCROOT/template/main.php" "$DOCROOT/index.php"
echo "BACKUP=$BACKUP"
echo "STATUS: BLOG_SEO_DEPLOY_DONE"
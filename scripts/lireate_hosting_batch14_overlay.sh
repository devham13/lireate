#!/usr/bin/env bash
# Restore v7 sidebar + seo-perf on /findtz/ and 13 patent tools after service publish.
set -euo pipefail
DOCROOT="${LIREATE_DOCROOT:?set LIREATE_DOCROOT}"
AUTOPUB="${LIREATE_AUTOPUB_HOME:-$HOME/lireate-autopub}"
OFFICE="${LIREATE_OFFICE_REPO:-$AUTOPUB/office-repo}"
PY="$(cat "$AUTOPUB/.python-interpreter" 2>/dev/null || echo python3)"
export LIREATE_ALLOW_PUBLISH=yes
STAMP="$(date -u +%Y%m%dT%H%M%SZ)"
BACKUP="$AUTOPUB/backups/batch14-overlay/$STAMP"
"$PY" "$OFFICE/scripts/lireate_hosting_service_post_publish.py" \
  --docroot "$DOCROOT" \
  --backup-root "$BACKUP" \
  --office-repo "$OFFICE" \
  "$@"
touch "$DOCROOT/template/main.php" "$DOCROOT/index.php"
echo "BACKUP=$BACKUP"
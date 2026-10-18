#!/usr/bin/env bash
# Local smoke tests for service hosting cron + menu (fake DOCROOT).
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
PY="${PY:-python3}"
TMP="$(mktemp -d)"
trap 'rm -rf "$TMP"' EXIT

DOCROOT="$TMP/docroot"
OFFICE="$ROOT"
STATE="$TMP/state/services-published.txt"
ENV_FILE="$TMP/.env"
mkdir -p "$DOCROOT" "$(dirname "$STATE")" "$TMP/var" "$TMP/logs"
echo "$PY" > "$TMP/.python-interpreter"

# Seed "already on hosting" China page (init must not republish).
mkdir -p "$DOCROOT/registraciya_tovarnogo_znaka_v_kitae"
echo '<?php echo "china";' > "$DOCROOT/registraciya_tovarnogo_znaka_v_kitae/index.php"

cat > "$ENV_FILE" <<EOF
LIREATE_DOCROOT=$DOCROOT
LIREATE_OFFICE_REPO=$OFFICE
LIREATE_PUBLIC_SITE_URL=http://127.0.0.1:8765
LIREATE_ALLOW_PUBLISH=yes
LIREATE_ALLOW_MENU_EDIT=yes
LIREATE_AUTOPUB_HOME=$TMP
LIREATE_SERVICES_STATE_TXT=$STATE
LIREATE_SERVICE_LOCK_FILE=$TMP/var/service-publish.lock
LIREATE_SERVICE_STATE_FILE=$TMP/state/services-published.json
EOF

export LIREATE_AUTOPUB_HOME="$TMP"
export LIREATE_ENV_FILE="$ENV_FILE"
export LIREATE_OFFICE_REPO="$OFFICE"
export LIREATE_DOCROOT="$DOCROOT"

echo "== init seeds existing docroot =="
bash "$ROOT/scripts/lireate_hosting_services_cron.sh" --init
grep -q 'registraciya-tovarnogo-znaka-v-kitae' "$STATE" || {
  echo "FAIL: init did not seed china slug"
  exit 1
}

echo "== menu dry-run =="
mkdir -p "$DOCROOT/templates"
cp "$ROOT/scripts/fixtures/cms-sidebar-snippet.php" "$DOCROOT/templates/sidebar.php"
"$PY" "$ROOT/scripts/lireate_hosting_menu_add.py" \
  --slug registraciya-tovarnogo-znaka-v-kitae \
  --office-repo "$OFFICE" \
  --env-file "$ENV_FILE" | grep -q 'STATUS: DRY_RUN' || {
  echo "FAIL: menu dry-run"
  exit 1
}

echo "== publish new test service =="
TEST_SLUG="cron-test-usluga"
TEST_DIR="$OFFICE/pages/services/$TEST_SLUG"
mkdir -p "$TEST_DIR/assets/lireate-shared"
cp "$OFFICE/pages/services/registraciya-tovarnogo-znaka-v-kitae/index.php" "$TEST_DIR/index.php"
cat > "$TEST_DIR/publish.manifest.json" <<EOF
{
  "slug": "$TEST_SLUG",
  "url_path": "uslugi/$TEST_SLUG",
  "title": "Тестовая услуга cron",
  "description": "Локальный тест автопубликации LIREATE.",
  "menu_after": "Регистрация товарного знака"
}
EOF

"$PY" -m http.server 8765 --directory "$DOCROOT" >/dev/null 2>&1 &
HTTP_PID=$!
sleep 2

export LIREATE_PUBLIC_SITE_URL="http://127.0.0.1:8765"
export LIREATE_ALLOW_PUBLISH=yes

"$PY" "$ROOT/scripts/lireate_hosting_publish_service.py" \
  --slug "$TEST_SLUG" --apply --skip-git-pull \
  --office-repo "$OFFICE" --env-file "$ENV_FILE" | grep -q 'STATUS: PUBLISHED' || {
  echo "FAIL: publish apply"
  kill "$HTTP_PID" 2>/dev/null || true
  exit 1
}

echo "== refuse foreign overwrite =="
mkdir -p "$DOCROOT/uslugi/foreign-block"
echo 'foreign' > "$DOCROOT/uslugi/foreign-block/index.php"
FOREIGN_SLUG="cron-test-foreign"
FOREIGN_DIR="$OFFICE/pages/services/$FOREIGN_SLUG"
mkdir -p "$FOREIGN_DIR"
cp "$TEST_DIR/index.php" "$FOREIGN_DIR/index.php"
cat > "$FOREIGN_DIR/publish.manifest.json" <<EOF
{
  "slug": "$FOREIGN_SLUG",
  "url_path": "uslugi/foreign-block",
  "title": "Foreign block test",
  "description": "Test foreign file refusal."
}
EOF
FOREIGN_OUT="$("$PY" "$ROOT/scripts/lireate_hosting_publish_service.py" \
  --slug "$FOREIGN_SLUG" --apply --skip-git-pull \
  --office-repo "$OFFICE" --env-file "$ENV_FILE" 2>&1)" || true
if echo "$FOREIGN_OUT" | grep -q 'refusing to overwrite foreign'; then
  echo "OK: foreign refusal"
else
  echo "FAIL: expected foreign file refusal"
  echo "$FOREIGN_OUT"
  kill "$HTTP_PID" 2>/dev/null || true
  exit 1
fi

kill "$HTTP_PID" 2>/dev/null || true

echo "== cron second run: NO_NEW_SERVICE (all candidates published or seeded) =="
echo "$TEST_SLUG" >> "$STATE"
rm -rf "$FOREIGN_DIR"
"$PY" - <<'PY' "$OFFICE" "$STATE"
import sys
from pathlib import Path

sys.path.insert(0, str(Path(sys.argv[1]) / "scripts"))
from lireate_hosting_services_lib import append_slug_to_state, list_service_dirs

office = Path(sys.argv[1])
state = Path(sys.argv[2])
for service_dir in list_service_dirs(office):
    if service_dir.name.startswith("cron-test-"):
        continue
    append_slug_to_state(state, service_dir.name)
PY
CRON_OUT="$(bash "$ROOT/scripts/lireate_hosting_services_cron.sh" 2>&1)" || true
echo "$CRON_OUT"
echo "$CRON_OUT" | grep -q 'STATUS: NO_NEW_SERVICE' || {
  echo "FAIL: expected NO_NEW_SERVICE"
  exit 1
}

rm -rf "$TEST_DIR"

echo "ALL TESTS PASSED"

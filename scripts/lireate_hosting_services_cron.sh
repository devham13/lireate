#!/usr/bin/env bash
# REG.RU CRON: sync office-repo, publish one new service page, optional sidebar menu item.
set -euo pipefail

AUTOPUB_HOME="${LIREATE_AUTOPUB_HOME:-$HOME/lireate-autopub}"
ENV_FILE="${LIREATE_ENV_FILE:-$AUTOPUB_HOME/.env}"
OFFICE_REPO="${LIREATE_OFFICE_REPO:-$AUTOPUB_HOME/office-repo}"
BRANCH="${LIREATE_OFFICE_BRANCH:-main}"
PY="$(cat "$AUTOPUB_HOME/.python-interpreter" 2>/dev/null || echo python3)"
LOG_DIR="$AUTOPUB_HOME/logs"
STATE_DIR="$AUTOPUB_HOME/state"
VAR_DIR="$AUTOPUB_HOME/var"
STATE_FILE="${LIREATE_SERVICES_STATE_TXT:-$STATE_DIR/services-published.txt}"
LOCK_FILE="${LIREATE_SERVICES_CRON_LOCK:-$VAR_DIR/services-cron.lock}"
INIT_MODE=0

export LIREATE_AUTOPUB_HOME="$AUTOPUB_HOME"
export LIREATE_ENV_FILE="$ENV_FILE"

mkdir -p "$LOG_DIR" "$STATE_DIR" "$VAR_DIR"

usage() {
  echo "Usage: $0 [--init]" >&2
  exit 2
}

while [[ $# -gt 0 ]]; do
  case "$1" in
    --init)
      INIT_MODE=1
      shift
      ;;
    -h|--help)
      usage
      ;;
    *)
      echo "Unknown argument: $1" >&2
      usage
      ;;
  esac
done

if [[ -f "$ENV_FILE" ]]; then
  set -a
  # shellcheck disable=SC1090
  source <(grep -E '^[A-Za-z_][A-Za-z0-9_]*=' "$ENV_FILE" | sed 's/^/export /')
  set +a
fi

OFFICE_REPO="${LIREATE_OFFICE_REPO:-$OFFICE_REPO}"
STATE_FILE="${LIREATE_SERVICES_STATE_TXT:-$STATE_FILE}"

DOCROOT="${LIREATE_DOCROOT:-}"
if [[ -z "$DOCROOT" || ! -d "$DOCROOT" ]]; then
  echo "STATUS: BLOCKER — LIREATE_DOCROOT missing or not a directory" >&2
  exit 1
fi

exec 9>"$LOCK_FILE"
if ! flock -n 9; then
  echo "STATUS: BLOCKER — another services cron holds the lock ($LOCK_FILE)"
  exit 1
fi

echo "=== LIREATE SERVICES CRON $(date -u +%Y-%m-%dT%H:%M:%SZ) ==="
echo "office_repo: $OFFICE_REPO"
echo "docroot: $DOCROOT"
echo "state: $STATE_FILE"

if [[ ! -d "$OFFICE_REPO/.git" ]]; then
  echo "STATUS: BLOCKER — office-repo is not a git checkout: $OFFICE_REPO"
  exit 1
fi

cd "$OFFICE_REPO"
if ! git fetch origin "$BRANCH" 2>&1; then
  echo "STATUS: BLOCKER — git fetch failed"
  exit 1
fi
if ! git checkout -B "$BRANCH" FETCH_HEAD 2>&1; then
  echo "STATUS: BLOCKER — git checkout failed"
  exit 1
fi
echo "git: synced $BRANCH @ $(git rev-parse --short HEAD)"

LIB="$OFFICE_REPO/scripts/lireate_hosting_services_lib.py"
PUBLISH="$OFFICE_REPO/scripts/lireate_hosting_publish_service.py"
MENU="$OFFICE_REPO/scripts/lireate_hosting_menu_add.py"

if [[ "$INIT_MODE" -eq 1 ]]; then
  echo "mode: --init (seed state only, no publish)"
  "$PY" - <<'PY' "$OFFICE_REPO" "$DOCROOT" "$STATE_FILE"
import sys
from pathlib import Path

sys.path.insert(0, str(Path(sys.argv[1]) / "scripts"))
from lireate_hosting_services_lib import seed_state_from_docroot

office_repo = Path(sys.argv[1])
docroot = Path(sys.argv[2])
state_file = Path(sys.argv[3])
state, seeded = seed_state_from_docroot(office_repo, docroot, state_file)
print("seeded: {0}".format(", ".join(seeded) if seeded else "(none)"))
print("state_count: {0}".format(len(state)))
PY
  echo "STATUS: INIT_DONE"
  exit 0
fi

readarray -t CANDIDATES < <(
  "$PY" - <<'PY' "$OFFICE_REPO" "$DOCROOT" "$STATE_FILE"
import sys
from pathlib import Path

sys.path.insert(0, str(Path(sys.argv[1]) / "scripts"))
from lireate_hosting_services_lib import (
    collect_publish_candidates,
    load_state_txt,
    save_state_txt,
    seed_state_from_docroot,
)

office_repo = Path(sys.argv[1])
docroot = Path(sys.argv[2])
state_file = Path(sys.argv[3])
state, _ = seed_state_from_docroot(office_repo, docroot, state_file)
state = load_state_txt(state_file)
candidates = collect_publish_candidates(office_repo, docroot, state)
if state != load_state_txt(state_file):
    save_state_txt(state_file, state)
for slug in candidates:
    print(slug)
PY
)

if [[ ${#CANDIDATES[@]} -eq 0 || -z "${CANDIDATES[0]:-}" ]]; then
  echo "STATUS: NO_NEW_SERVICE"
  exit 0
fi

SLUG="${CANDIDATES[0]}"
echo "publish_candidate: $SLUG"

if ! "$PY" "$PUBLISH" --slug "$SLUG" --apply --skip-git-pull --office-repo "$OFFICE_REPO" --env-file "$ENV_FILE"; then
  echo "STATUS: BLOCKER — publish failed for $SLUG"
  exit 1
fi

"$PY" - <<'PY' "$OFFICE_REPO" "$STATE_FILE" "$SLUG"
import sys
from pathlib import Path

sys.path.insert(0, str(Path(sys.argv[1]) / "scripts"))
from lireate_hosting_services_lib import append_slug_to_state

append_slug_to_state(Path(sys.argv[2]), sys.argv[3])
PY

MENU_ARGS=("$MENU" --slug "$SLUG" --office-repo "$OFFICE_REPO" --env-file "$ENV_FILE")
if [[ "${LIREATE_ALLOW_MENU_EDIT:-}" == "yes" ]]; then
  if ! "$PY" "${MENU_ARGS[@]}" --apply; then
    echo "STATUS: BLOCKER — menu apply failed for $SLUG"
    exit 1
  fi
else
  "$PY" "${MENU_ARGS[@]}" || true
  echo "menu: dry-run only (LIREATE_ALLOW_MENU_EDIT is not yes)"
fi

echo "STATUS: PUBLISHED_SERVICE $SLUG"
exit 0

#!/usr/bin/env bash
# Unit smoke: backup_dirty_worktree removes only untracked paths that exist in target commit.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
CRON="$ROOT/scripts/lireate_hosting_services_cron.sh"
PY="${PY:-python3}"
TMP="$(mktemp -d)"
trap 'rm -rf "$TMP"' EXIT

AUTOPUB_HOME="$TMP/autopub"
OFFICE_REPO="$TMP/office-repo"
mkdir -p "$AUTOPUB_HOME" "$OFFICE_REPO"

export AUTOPUB_HOME
export LIREATE_AUTOPUB_HOME="$AUTOPUB_HOME"
export PY

# shellcheck disable=SC1091
source <(sed -n '/^backup_dirty_worktree()/,/^}$/p' "$CRON")

cd "$OFFICE_REPO"
git init -q
git config user.email "test@example.test"
git config user.name "Test"

mkdir -p pages
echo "in-tree" > pages/in-tree.txt
echo "only-local" > only-local.txt
git add pages/in-tree.txt
git commit -m "seed" -q

TARGET="$(git rev-parse HEAD)"

# Untracked path that also exists in TARGET (blocks checkout without removal).
git rm --cached -q pages/in-tree.txt

backup_dirty_worktree "$TARGET"

if [[ -f pages/in-tree.txt ]]; then
  echo "FAIL: untracked in-tree.txt should be removed after backup"
  exit 1
fi
if [[ ! -f only-local.txt ]]; then
  echo "FAIL: only-local.txt must stay (not in target tree)"
  exit 1
fi

BK="$(find "$AUTOPUB_HOME/backups/office-repo-dirty" -mindepth 1 -maxdepth 1 -type d | head -1)"
if [[ -z "$BK" || ! -f "$BK/files.tgz" ]]; then
  echo "FAIL: backup archive missing under $AUTOPUB_HOME/backups/office-repo-dirty"
  exit 1
fi
if ! tar tzf "$BK/files.tgz" | grep -q 'pages/in-tree.txt'; then
  echo "FAIL: backup must contain pages/in-tree.txt"
  exit 1
fi

echo "OK: backup_dirty_worktree"
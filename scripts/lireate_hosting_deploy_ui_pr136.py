#!/usr/bin/env python3
"""Remote REG.RU deploy for UI PR #136 (SSH). No secrets in output."""
from __future__ import annotations

import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
sys.path.insert(0, str(ROOT / "shared"))

import paramiko  # noqa: E402
from credentials import get_credential, require_credential  # noqa: E402

REMOTE = r"""
set -euo pipefail
export LIREATE_AUTOPUB_HOME="${LIREATE_AUTOPUB_HOME:-$HOME/lireate-autopub}"
export LIREATE_ENV_FILE="${LIREATE_ENV_FILE:-$LIREATE_AUTOPUB_HOME/.env}"
if [[ ! -f "$LIREATE_ENV_FILE" ]]; then
  echo "BLOCKER: missing $LIREATE_ENV_FILE"
  exit 2
fi
set -a
# shellcheck disable=SC1090
source <(grep -E '^[A-Za-z_][A-Za-z0-9_]*=' "$LIREATE_ENV_FILE" | sed 's/^/export /')
set +a
PY="$(cat "$LIREATE_AUTOPUB_HOME/.python-interpreter" 2>/dev/null || echo python3)"
OFFICE="${LIREATE_OFFICE_REPO:-$LIREATE_AUTOPUB_HOME/office-repo}"
DOCROOT="${LIREATE_DOCROOT:-}"
if [[ ! -d "$OFFICE/.git" || -z "$DOCROOT" || ! -d "$DOCROOT" ]]; then
  echo "BLOCKER: office-repo or docroot invalid"
  exit 2
fi
cd "$OFFICE"
git fetch origin main
git checkout -f -B main origin/main
echo "git_main=$(git rev-parse --short HEAD)"
export LIREATE_ALLOW_PUBLISH=yes
echo "=== services cron (sync + perf patch) ==="
bash "$OFFICE/scripts/lireate_hosting_services_cron.sh" || true
echo "=== overlay (home + cms css) ==="
"$PY" "$OFFICE/scripts/lireate_cms_light_v4_overlay_install.py" --docroot "$DOCROOT" --apply
echo "=== pf (contacts css) ==="
"$PY" "$OFFICE/scripts/lireate_hosting_pf_install.py" --docroot "$DOCROOT" --apply
echo "=== services --update-all ==="
"$PY" "$OFFICE/scripts/lireate_hosting_publish_service.py" \
  --apply --skip-git-pull --update-all
echo "=== rollout verify ==="
"$PY" "$OFFICE/scripts/lireate_light_v4_hosting_rollout.py" --docroot "$DOCROOT" --office-repo "$OFFICE"
echo "DEPLOY_UI_PR136_DONE"
"""


def main() -> int:
    host = require_credential("SSH_HOST")
    port = int(get_credential("SSH_PORT", "22") or "22")
    user = require_credential("SSH_USER")
    password = require_credential("SSH_PASSWORD")
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(host, port=port, username=user, password=password, timeout=60)
    stdin, stdout, stderr = client.exec_command(REMOTE, timeout=3600)
    out = stdout.read().decode("utf-8", errors="replace")
    err = stderr.read().decode("utf-8", errors="replace")
    code = stdout.channel.recv_exit_status()
    safe = (out + err).replace(password, "***")
    print(safe)
    return code


if __name__ == "__main__":
    raise SystemExit(main())
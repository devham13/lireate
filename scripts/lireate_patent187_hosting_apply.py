#!/usr/bin/env python3
"""Deploy merged PR #187: patent-v4 hero (patent install + overlay + touch main)."""
from __future__ import print_function

import os
import subprocess
import sys
from datetime import datetime, timezone
from pathlib import Path

BACKUP_TAG = "step187-patent-v4-hero"


def ssh_base():
    key = os.environ.get("LIREATE_SSH_KEY", "").strip()
    host = os.environ.get("LIREATE_SSH_HOST", "").strip()
    user = os.environ.get("LIREATE_SSH_USER", "").strip()
    if not key or not host or not user:
        return None, "BLOCKER: LIREATE_SSH_KEY/HOST/USER"
    key_file = Path("/tmp/lireate_ssh_key_patent187")
    key_file.write_text(key + "\n", encoding="utf-8")
    key_file.chmod(0o600)
    base = (
        "ssh -i {key} -o StrictHostKeyChecking=no -o ConnectTimeout=40 "
        "{user}@{host} ".format(key=key_file, user=user, host=host)
    )
    return base, None


def run_ssh(base, remote_script):
    cmd = base + "'" + remote_script.replace("'", "'\"'\"'") + "'"
    proc = subprocess.run(cmd, shell=True, text=True, capture_output=True)
    out = (proc.stdout or "") + (proc.stderr or "")
    print(out)
    return proc.returncode, out


def main():
    if os.environ.get("LIREATE_ALLOW_PUBLISH", "").lower() not in ("yes", "1", "true"):
        print("BLOCKER: set LIREATE_ALLOW_PUBLISH=yes")
        return 2
    base, err = ssh_base()
    if err:
        print(err)
        return 2
    ts = datetime.now(timezone.utc).strftime("%Y%m%dT%H%M%SZ")
    remote = r"""
set -euo pipefail
AUTOPUB="${HOME}/lireate-autopub"
OFFICE="${AUTOPUB}/office-repo"
ENV="${AUTOPUB}/.env"
test -f "$ENV" || { echo BLOCKER: missing env; exit 2; }
set -a; . "$ENV"; set +a
DOCROOT="${LIREATE_DOCROOT:-}"
test -n "$DOCROOT" || { echo BLOCKER: LIREATE_DOCROOT; exit 2; }
PY="$(cat "${AUTOPUB}/.python-interpreter" 2>/dev/null || echo python3)"
PHP_BIN="${LIREATE_PHP_BIN:-$(command -v php 2>/dev/null || echo php)}"
PREBK="${AUTOPUB}/backups/""" + BACKUP_TAG + r"""/pre-""" + ts + r""""
mkdir -p "$PREBK/pages/patents/_shared" "$PREBK/template/css" "$PREBK/template/images/patent-v4"
for f in pages/patents/_shared/lireate-patent-data.php template/main.php \
  template/css/lireate-patent-light-v4.css template/images/patent-v4/patent-inventions.webp; do
  cp -a "${DOCROOT}/${f}" "${PREBK}/${f}" 2>/dev/null || true
done
cd "$OFFICE"
git fetch origin main
git checkout -B main origin/main
export LIREATE_ALLOW_PUBLISH=yes
export LIREATE_POST_PUBLISH_OVERLAY=yes
export LIREATE_OFFICE_REPO="$OFFICE"
BKROOT="${AUTOPUB}/backups/""" + BACKUP_TAG + r"""/installer"
"$PY" scripts/lireate_patent_light_v4_install.py --docroot "$DOCROOT" --backup-root "$BKROOT" --apply --php-bin "$PHP_BIN"
OVERLAY_BK="${AUTOPUB}/backups/""" + BACKUP_TAG + r"""/overlay-""" + ts + r""""
"$PY" scripts/lireate_hosting_service_post_publish.py --docroot "$DOCROOT" --backup-root "$OVERLAY_BK" --office-repo "$OFFICE" --apply --php-bin "$PHP_BIN"
touch "${DOCROOT}/template/main.php"
echo "PRE_BACKUP_DIR=${PREBK}"
echo "STATUS: PATENT187_APPLIED"
"""
    code, out = run_ssh(base, remote)
    if code != 0 or "PATENT187_APPLIED" not in out:
        print("BLOCKER: deploy failed", code)
        return code or 1
    for line in out.splitlines():
        if line.startswith(("PRE_BACKUP_DIR=", "APPLIED backup=")):
            print(line)
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
#!/usr/bin/env python3
"""Deploy PR #186: CMS/blog/contacts CSS + shared lireate-light-v4.css (no service index.php)."""
from __future__ import print_function

import json
import os
import subprocess
import sys
from datetime import datetime, timezone
from pathlib import Path

REPO = Path(__file__).resolve().parents[1]
BACKUP_TAG = "step186-footer"


def ssh_base():
    key = os.environ.get("LIREATE_SSH_KEY", "").strip()
    host = os.environ.get("LIREATE_SSH_HOST", "").strip()
    user = os.environ.get("LIREATE_SSH_USER", "").strip()
    if not key or not host or not user:
        return None, "BLOCKER: LIREATE_SSH_KEY/HOST/USER"
    key_file = Path("/tmp/lireate_ssh_key_footer186")
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
BK="${AUTOPUB}/backups/""" + BACKUP_TAG + r"""/""" + ts + r""""
mkdir -p "$BK/template/page/news" "$BK/template/page/kontaktyi" "$BK/template/css" "$BK/_shared"
for f in template/page/news/lireate-blog-list.css template/css/lireate-cms-pages-light-v4.css \
  template/page/kontaktyi/lireate-contacts.css _shared/lireate-light-v4.css; do
  cp -a "${DOCROOT}/${f}" "${BK}/${f}" 2>/dev/null || true
done
cd "$OFFICE"
git fetch origin main
git checkout -B main origin/main
export LIREATE_ALLOW_PUBLISH=yes
export LIREATE_ENV_FILE="$ENV"
export LIREATE_DOCROOT="$DOCROOT"
export LIREATE_OFFICE_REPO="$OFFICE"
"$PY" scripts/lireate_blog_sync_template.py
"$PY" scripts/lireate_blog_install.py --apply --env-file "$ENV"
"$PY" scripts/lireate_contacts_sync_template.py
"$PY" scripts/lireate_contacts_install.py --apply --env-file "$ENV"
"$PY" scripts/lireate_cms_light_v4_overlay_install.py --docroot "$DOCROOT" --apply
SRC="${OFFICE}/pages/services/_shared/lireate-light-v4.css"
cp -a "$SRC" "${DOCROOT}/_shared/lireate-light-v4.css"
STATE="${AUTOPUB}/services-published.json"
export LIREATE_OFFICE_REPO="$OFFICE"
"$PY" scripts/lireate_service_light_v4_css_hosting_sync.py || true
touch "${DOCROOT}/template/main.php"
echo "BACKUP_DIR=${BK}"
echo STATUS: FOOTER186_APPLIED
"""
    code, out = run_ssh(base, remote)
    if code != 0 or "FOOTER186_APPLIED" not in out:
        print("BLOCKER: deploy failed", code)
        return code or 1
    for line in out.splitlines():
        if line.startswith("BACKUP_DIR="):
            print(line)
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
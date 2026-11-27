#!/usr/bin/env python3
"""Deploy data/lireate-seo-* to production DOCROOT/data with backup and remote-drift guard."""

from __future__ import print_function

import argparse
import hashlib
import os
import subprocess
import sys
from datetime import datetime, timezone
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
DATA_DIR = ROOT / "data"
BACKUP_NAME = "seo-master-20261127"
BASELINE = DATA_DIR / "lireate-seo-data-baseline.sha256"

SEO_FILES = (
    "lireate-seo-head.inc.php",
    "lireate-seo-page-meta.inc.php",
    "lireate-seo-pre-template.inc.php",
    "lireate-seo-runtime.php",
    "lireate-seo-schema.inc.php",
    "lireate-seo-audit-meta.json",
    "lireate-seo-cms-overrides.json",
    "lireate-seo-mktu-classes.json",
)


def sha256_file(path):
    h = hashlib.sha256()
    with open(path, "rb") as fh:
        for chunk in iter(lambda: fh.read(1024 * 1024), b""):
            h.update(chunk)
    return h.hexdigest()


def load_baseline():
    mapping = {}
    if not BASELINE.is_file():
        return mapping
    for line in BASELINE.read_text(encoding="utf-8").splitlines():
        parts = line.strip().split()
        if len(parts) >= 2:
            mapping[parts[1]] = parts[0]
    return mapping


def ssh_env():
    key = os.environ.get("LIREATE_SSH_KEY", "").strip()
    host = os.environ.get("LIREATE_SSH_HOST", "").strip()
    user = os.environ.get("LIREATE_SSH_USER", "").strip()
    if not key or not host or not user:
        return None, "BLOCKER: LIREATE_SSH_KEY/HOST/USER not set"
    key_file = Path("/tmp/lireate_seo_ssh_key")
    key_file.write_text(key + "\n", encoding="utf-8")
    key_file.chmod(0o600)
    base = (
        "ssh -i {key} -o StrictHostKeyChecking=no -o ConnectTimeout=25 {user}@{host} ".format(
            key=key_file, user=user, host=host
        )
    )
    return base, None


def remote_sha(ssh_base, remote_path):
    cmd = ssh_base + "'sha256sum {p} 2>/dev/null || true'".format(p=remote_path)
    proc = subprocess.run(cmd, shell=True, capture_output=True, text=True)
    line = (proc.stdout or "").strip().split("\n")[0] if proc.stdout else ""
    parts = line.split()
    return parts[0] if len(parts) >= 2 else ""


def plan(docroot):
    rows = []
    for name in SEO_FILES:
        src = DATA_DIR / name
        if not src.is_file():
            return None, "BLOCKER: missing {0}".format(src)
        rows.append((src, "{0}/data/{1}".format(docroot.rstrip("/"), name)))
    return rows, None


def cmd_check(docroot):
    baseline = load_baseline()
    ssh_base, err = ssh_env()
    if err:
        print(err)
        return 2
    drift = []
    for name in SEO_FILES:
        local = DATA_DIR / name
        local_sha = sha256_file(local)
        remote_sha_val = remote_sha(ssh_base, "{0}/data/{1}".format(docroot, name))
        base_sha = baseline.get(name, "")
        if remote_sha_val and base_sha and remote_sha_val != base_sha and remote_sha_val != local_sha:
            drift.append(name)
    if drift:
        print("BLOCKER: remote newer or diverged (not baseline, not local): {0}".format(", ".join(drift)))
        return 2
    print("STATUS: SEO_DATA_DRIFT_OK")
    return 0


def cmd_apply(docroot):
    if os.environ.get("LIREATE_ALLOW_PUBLISH", "").lower() not in ("yes", "1", "true"):
        print("BLOCKER: LIREATE_ALLOW_PUBLISH!=yes")
        return 2
    if cmd_check(docroot) != 0:
        return 2
    ssh_base, err = ssh_env()
    if err:
        print(err)
        return 2
    ts = datetime.now(timezone.utc).strftime("%Y%m%d-%H%M%S")
    backup = "~/lireate-autopub/backups/{0}/{1}".format(BACKUP_NAME, ts)
    subprocess.run(
        ssh_base + "'mkdir -p {b}/data'".format(b=backup),
        shell=True,
        check=True,
    )
    rows, err = plan(docroot)
    if err:
        print(err)
        return 2
    for src, dst in rows:
        rel = "data/" + src.name
        subprocess.run(
            ssh_base + "'cp -a {d}/{r} {b}/{r} 2>/dev/null || true'".format(
                d=docroot, r=rel, b=backup
            ),
            shell=True,
        )
        subprocess.run(
            [
                "scp",
                "-i",
                "/tmp/lireate_seo_ssh_key",
                "-o",
                "StrictHostKeyChecking=no",
                str(src),
                "{0}@{1}:{2}".format(
                    os.environ["LIREATE_SSH_USER"],
                    os.environ["LIREATE_SSH_HOST"],
                    dst,
                ),
            ],
            check=True,
        )
        print("deployed {0}".format(rel))
    touch = ssh_base + (
        "'touch {d}/template/main.php {d}/index.php'".format(d=docroot.rstrip("/"))
    )
    subprocess.run(touch, shell=True, check=True)
    print("backup: {0}".format(backup))
    print("STATUS: SEO_DATA_APPLIED")
    return 0


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument(
        "--docroot",
        default=os.environ.get("LIREATE_REMOTE_SITE_ROOT", "").strip()
        or "/var/www/u3650956/data/www/public_html",
    )
    parser.add_argument("--check", action="store_true", help="Remote drift check only")
    parser.add_argument("--apply", action="store_true", help="Backup and deploy SEO data")
    args = parser.parse_args()
    if args.apply:
        return cmd_apply(args.docroot)
    return cmd_check(args.docroot)


if __name__ == "__main__":
    sys.exit(main())
#!/usr/bin/env python3
"""Применить install-continue на VPS 89.223.121.172 по SSH-ключу из env."""
from __future__ import annotations

import io
import os
import sys
from pathlib import Path

import paramiko

REPO_ROOT = Path(__file__).resolve().parents[3]
sys.path.insert(0, str(REPO_ROOT / "shared"))

VPS_HOST = os.environ.get("LIREATE_SEO_VPS_HOST", "89.223.121.172")


def connect() -> paramiko.SSHClient:
    key_s = os.environ["LIREATE_SSH_KEY"]
    user = os.environ.get("LIREATE_SEO_VPS_USER", os.environ.get("LIREATE_SSH_USER", "root"))
    key = paramiko.Ed25519Key.from_private_key(io.StringIO(key_s))
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(
        VPS_HOST,
        port=22,
        username=user,
        pkey=key,
        timeout=25,
        allow_agent=False,
        look_for_keys=False,
    )
    return client


def main() -> int:
    remote = f"""
set -euo pipefail
export LIREATE_SEO_ROOT=/opt/lireate-seo
export LIREATE_OFFICE_GIT_URL=https://github.com/devham13/lireate.git
export LIREATE_OFFICE_GIT_REF=main
if [[ ! -x "$LIREATE_SEO_ROOT/office-repo/tools/seo/vps/install-continue.sh" ]]; then
  mkdir -p "$LIREATE_SEO_ROOT"
  git clone --depth 1 https://github.com/devham13/lireate.git "$LIREATE_SEO_ROOT/office-repo"
fi
bash "$LIREATE_SEO_ROOT/office-repo/tools/seo/vps/install-continue.sh"
"""
    client = connect()
    try:
        stdin, stdout, stderr = client.exec_command(remote, timeout=3600)
        out = stdout.read().decode(errors="replace")
        err = stderr.read().decode(errors="replace")
        code = stdout.channel.recv_exit_status()
        print(out)
        if err.strip():
            print(err, file=sys.stderr)
        return code
    finally:
        client.close()


if __name__ == "__main__":
    raise SystemExit(main())
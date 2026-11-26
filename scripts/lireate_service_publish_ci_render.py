#!/usr/bin/env python3
"""CI: render published service pages (publish_service layout) — no PHP Fatal/Warning, closes </html>."""

from __future__ import annotations

import json
import os
import re
import shutil
import subprocess
import sys
import tempfile
from pathlib import Path
from unittest import mock

SCRIPT_DIR = Path(__file__).resolve().parent
REPO = SCRIPT_DIR.parent
sys.path.insert(0, str(SCRIPT_DIR))

import lireate_hosting_publish_service as pub  # noqa: E402
import lireate_service_sync_shared as sync_shared  # noqa: E402

SERVICES = REPO / "pages" / "services"
SKIP = {"_shared", "_template"}
FATAL_RE = re.compile(r"\b(Fatal error|Parse error)\b", re.I)
WARN_RE = re.compile(r"\bPHP (Warning|Notice|Deprecated):\b", re.I)


def list_slugs():
    out = []
    for child in sorted(SERVICES.iterdir()):
        if not child.is_dir() or child.name in SKIP:
            continue
        manifest = child / "publish.manifest.json"
        if manifest.is_file() and (child / "index.php").is_file():
            out.append(child.name)
    return out


def resolve_php():
    for key in ("LIREATE_PHP_BIN", "PHP_BIN"):
        val = os.environ.get(key, "").strip()
        if val and Path(val).is_file():
            return val
    return shutil.which("php") or "php"


def render_index(php_bin, docroot, url_path):
    index = docroot / url_path.strip("/") / "index.php"
    if not index.is_file():
        return False, ["missing index after publish: {0}".format(index)]
    env = os.environ.copy()
    env["DOCUMENT_ROOT"] = str(docroot)
    env["HTTP_HOST"] = "fixture-ci.test"
    env["HTTPS"] = "off"
    env["REQUEST_URI"] = "/" + url_path.strip("/") + "/"
    env["SCRIPT_FILENAME"] = str(index)
    proc = subprocess.run(
        [php_bin, "-d", "display_errors=1", "-d", "error_reporting=E_ALL", str(index)],
        cwd=str(index.parent),
        capture_output=True,
        text=True,
        env=env,
        timeout=120,
    )
    out = (proc.stdout or "") + (proc.stderr or "")
    errors = []
    if proc.returncode != 0:
        errors.append("exit {0}".format(proc.returncode))
    if FATAL_RE.search(out):
        errors.append("PHP fatal in output")
    if WARN_RE.search(out):
        errors.append("PHP warning/notice in output")
    if "</html>" not in out.lower():
        errors.append("missing </html>")
    if errors:
        snippet = out[-1200:] if len(out) > 1200 else out
        errors.append("tail: " + snippet.replace("\n", " ")[:400])
    return len(errors) == 0, errors


def main():
    php_bin = resolve_php()
    tmp = Path(tempfile.mkdtemp(prefix="lireate-svc-ci-"))
    docroot = tmp / "docroot"
    docroot.mkdir(parents=True)
    autopub = tmp / "autopub"
    autopub.mkdir(parents=True)
    state_file = autopub / "state" / "services-published.json"
    os.environ["LIREATE_ALLOW_PUBLISH"] = "yes"
    failed = []
    slugs = list_slugs()
    print("service publish CI render: {0} slug(s), php={1}".format(len(slugs), php_bin))
    for slug in slugs:
        sync_shared.sync_service(SERVICES / slug, dry_run=False)
        manifest = json.loads((SERVICES / slug / "publish.manifest.json").read_text(encoding="utf-8"))
        url_path = manifest.get("url_path") or slug.replace("-", "_")
        with mock.patch.object(pub, "_http_status", return_value=(200, None)):
            code = pub._publish_slug(
                slug,
                REPO,
                docroot,
                "https://fixture-ci.test",
                autopub,
                state_file,
                dry_run=False,
                allow=True,
                skip_git_pull=True,
                update_mode=False,
            )
        if code != 0:
            failed.append((slug, ["publish exit {0}".format(code)]))
            continue
        ok, errs = render_index(php_bin, docroot, url_path)
        if ok:
            print("  OK {0} → /{1}/".format(slug, url_path.strip("/")))
        else:
            print("  FAIL {0}: {1}".format(slug, "; ".join(errs[:2])))
            failed.append((slug, errs))
    shutil.rmtree(tmp, ignore_errors=True)
    if failed:
        print("BLOCKER: {0} service(s) failed render gate".format(len(failed)))
        for slug, errs in failed[:8]:
            print("  - {0}: {1}".format(slug, errs[0] if errs else "?"))
        return 1
    print("SERVICE PUBLISH RENDER GATE: PASS")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
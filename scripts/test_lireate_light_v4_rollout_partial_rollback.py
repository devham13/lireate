#!/usr/bin/env python3
"""Rollout --apply must restore docroot bytes after a mid-run failure."""

import hashlib
import os
import shutil
import stat
import subprocess
import sys
import tempfile
import unittest
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]


def docroot_fingerprint(root: Path) -> str:
    h = hashlib.md5()
    for path in sorted(root.rglob("*")):
        if path.is_file():
            rel = path.relative_to(root).as_posix().encode("utf-8")
            h.update(rel)
            h.update(path.read_bytes())
    return h.hexdigest()


class RolloutPartialRollbackTests(unittest.TestCase):
    def test_readonly_slug_restores_docroot(self):
        tmp = Path(tempfile.mkdtemp(prefix="lireate-rollout-partial-"))
        docroot = tmp / "docroot"
        autopub = tmp / "autopub"
        backups = autopub / "backups"
        docroot.mkdir(parents=True)
        backups.mkdir(parents=True)
        (docroot / "template").mkdir(parents=True)
        shutil.copy2(
            ROOT / "scripts" / "fixtures" / "cms-main-home-empty-page-container.php",
            docroot / "template" / "main.php",
        )
        fail_dir = docroot / "patentnye-zadachi" / "fail-slovesnogo-znaka"
        fail_dir.mkdir(parents=True)
        os.chmod(fail_dir, stat.S_IRUSR | stat.S_IXUSR | stat.S_IRGRP | stat.S_IXGRP)
        before = docroot_fingerprint(docroot)
        slugs = (
            "patentnye-zadachi-poshliny-registraciya,"
            "patentnye-zadachi-poshliny-prodlenie,"
            "patentnye-zadachi-fail-slovesnogo-znaka"
        )
        try:
            env = os.environ.copy()
            env["LIREATE_ROLLOUT_SLUGS"] = slugs
            env["LIREATE_PUBLIC_SITE_URL"] = "https://example.test"
            env["LIREATE_SKIP_SERVICE_HTTP_VERIFY"] = "yes"
            env["LIREATE_ALLOW_PUBLISH"] = "yes"
            env["LIREATE_DOCROOT"] = str(docroot)
            env["LIREATE_OFFICE_REPO"] = str(ROOT)
            env["LIREATE_AUTOPUB_HOME"] = str(autopub)
            proc = subprocess.run(
                [
                    sys.executable,
                    str(ROOT / "scripts" / "lireate_light_v4_hosting_rollout.py"),
                    "--docroot",
                    str(docroot),
                    "--office-repo",
                    str(ROOT),
                    "--backup-root",
                    str(backups),
                    "--apply",
                ],
                env=env,
                stdout=subprocess.PIPE,
                stderr=subprocess.PIPE,
                universal_newlines=True,
            )
            self.assertNotEqual(proc.returncode, 0, proc.stdout + proc.stderr)
            self.assertIn("ROLLBACK", proc.stdout + proc.stderr)
            after = docroot_fingerprint(docroot)
            self.assertEqual(before, after, "docroot must match pre-rollout fingerprint after rollback")
        finally:
            os.chmod(fail_dir, stat.S_IRWXU)
            shutil.rmtree(tmp, ignore_errors=True)


if __name__ == "__main__":
    unittest.main()
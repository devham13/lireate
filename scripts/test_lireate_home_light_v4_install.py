#!/usr/bin/env python3
"""Simulated DOCROOT: home Light V4 apply → rollback restores byte-identical main.php."""

import hashlib
import os
import re
import shutil
import subprocess
import sys
import tempfile
import unittest
from pathlib import Path

REPO_ROOT = Path(__file__).resolve().parents[1]
sys.path.insert(0, str(REPO_ROOT / "scripts"))

FIXTURE_MAIN = REPO_ROOT / "scripts/fixtures/cms-main-home-empty-page-container.php"


def file_md5(path):
    h = hashlib.md5()
    with open(path, "rb") as fp:
        h.update(fp.read())
    return h.hexdigest()


class HomeLightV4InstallTests(unittest.TestCase):
    def test_apply_rollback_restores_main_md5(self):
        tmp = Path(tempfile.mkdtemp(prefix="lireate-home-v4-"))
        backups = tmp / "backups"
        docroot = tmp / "docroot"
        main_rel = docroot / "template" / "main.php"
        try:
            main_rel.parent.mkdir(parents=True)
            shutil.copy(str(FIXTURE_MAIN), str(main_rel))
            before = file_md5(main_rel)

            env = os.environ.copy()
            env["LIREATE_OFFICE_REPO"] = str(REPO_ROOT)
            env["LIREATE_BACKUPS_ROOT"] = str(backups)

            apply = subprocess.run(
                [
                    sys.executable,
                    str(REPO_ROOT / "scripts/lireate_home_light_v4_install.py"),
                    "--docroot",
                    str(docroot),
                    "--apply",
                ],
                env=env,
                capture_output=True,
                text=True,
            )
            self.assertEqual(apply.returncode, 0, apply.stdout + apply.stderr)
            self.assertNotEqual(before, file_md5(main_rel), "apply should change main.php")

            m = re.search(r"backup:\s*(\S+)", apply.stdout)
            self.assertIsNotNone(m, apply.stdout)
            backup_dir = Path(m.group(1))
            backup_id = backup_dir.name

            rollback = subprocess.run(
                [
                    sys.executable,
                    str(REPO_ROOT / "scripts/lireate_home_light_v4_install.py"),
                    "--docroot",
                    str(docroot),
                    "--rollback",
                    "--backup-id",
                    backup_id,
                ],
                env=env,
                capture_output=True,
                text=True,
            )
            self.assertEqual(rollback.returncode, 0, rollback.stdout + rollback.stderr)
            after = file_md5(main_rel)
            self.assertEqual(before, after, "rollback must restore original main.php bytes")
        finally:
            shutil.rmtree(tmp, ignore_errors=True)


if __name__ == "__main__":
    unittest.main()
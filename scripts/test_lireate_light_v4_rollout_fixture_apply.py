#!/usr/bin/env python3
"""Rollout --apply smoke on one slug (fixture docroot, no live hosting)."""

import os
import shutil
import subprocess
import sys
import tempfile
import unittest
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]


class RolloutFixtureApplyTests(unittest.TestCase):
    def test_rollout_apply_one_slug(self):
        tmp = Path(tempfile.mkdtemp(prefix="lireate-rollout-apply-"))
        docroot = tmp / "docroot"
        backups = tmp / "backups"
        docroot.mkdir(parents=True)
        backups.mkdir(parents=True)
        tpl = docroot / "template"
        tpl.mkdir(parents=True)
        shutil.copy2(
            ROOT / "scripts" / "fixtures" / "cms-main-home-empty-page-container.php",
            tpl / "main.php",
        )
        try:
            env = os.environ.copy()
            env["LIREATE_ROLLOUT_SLUGS"] = "patentnye-zadachi"
            env["LIREATE_PUBLIC_SITE_URL"] = "https://example.test"
            env["LIREATE_SKIP_SERVICE_HTTP_VERIFY"] = "yes"
            env["LIREATE_ALLOW_PUBLISH"] = "yes"
            env["LIREATE_DOCROOT"] = str(docroot)
            env["LIREATE_OFFICE_REPO"] = str(ROOT)
            env["LIREATE_AUTOPUB_HOME"] = str(tmp)
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
            self.assertEqual(proc.returncode, 0, proc.stdout + proc.stderr)
            self.assertIn("LIGHT_V4_SERVICES_ROLLOUT_APPLIED", proc.stdout)
        finally:
            shutil.rmtree(tmp, ignore_errors=True)


if __name__ == "__main__":
    unittest.main()
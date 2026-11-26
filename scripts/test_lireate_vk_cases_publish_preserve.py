#!/usr/bin/env python3
"""Rollout/publish must not replace per-page vk-cases.json with generic shared copy."""

import hashlib
import json
import os
import shutil
import subprocess
import sys
import tempfile
import unittest
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
sys.path.insert(0, str(ROOT / "scripts"))

import lireate_hosting_publish_service as pub  # noqa: E402

LIVE_MD5 = {
    "zashchita-avtorskih-prav": "35f7c2f6603f3cf03e47c8b72bccb683",
    "vozrazhenie-protiv-registracii-tovarnogo-znaka": "34d7600aecbac29a11d154b5c109d7b0",
}


def md5(path):
    return hashlib.md5(path.read_bytes()).hexdigest()


class VkCasesPublishTests(unittest.TestCase):
    def test_apply_keeps_custom_vk_cases_md5(self):
        tmp = Path(tempfile.mkdtemp(prefix="lireate-vk-cases-"))
        docroot = tmp / "docroot"
        home = tmp / "autopub"
        state = home / "state" / "services-published.json"
        docroot.mkdir(parents=True)
        try:
            for slug, expected in LIVE_MD5.items():
                manifest = json.loads(
                    (ROOT / "pages" / "services" / slug / "publish.manifest.json").read_text(
                        encoding="utf-8"
                    )
                )
                url_path = manifest["url_path"]
                dest = docroot / url_path / "assets" / "lireate-shared"
                dest.mkdir(parents=True)
                custom = ROOT / "pages" / "services" / slug / "assets" / "lireate-shared" / "vk-cases.json"
                shutil.copy2(str(custom), str(dest / "vk-cases.json"))
                self.assertEqual(expected, md5(dest / "vk-cases.json"))
                os.environ["LIREATE_ALLOW_PUBLISH"] = "yes"
                os.environ["LIREATE_SKIP_SERVICE_HTTP_VERIFY"] = "yes"
                code = pub._publish_slug(
                    slug,
                    ROOT,
                    docroot,
                    "https://example.test",
                    home,
                    state,
                    dry_run=False,
                    allow=True,
                    skip_git_pull=True,
                    update_mode=True,
                )
                self.assertEqual(0, code, slug)
                self.assertEqual(
                    expected,
                    md5(dest / "vk-cases.json"),
                    "vk-cases.json md5 must match live after publish --apply",
                )
        finally:
            shutil.rmtree(tmp, ignore_errors=True)


if __name__ == "__main__":
    unittest.main()
#!/usr/bin/env python3
"""Rollout/publish must not replace per-page vk-cases.json with generic shared copy."""

import hashlib
import os
import shutil
import sys
import tempfile
import unittest
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
sys.path.insert(0, str(ROOT / "scripts"))

import lireate_hosting_publish_service as pub  # noqa: E402

PRESERVE_SLUGS = (
    "zashchita-avtorskih-prav",
    "vozrazhenie-protiv-registracii-tovarnogo-znaka",
)
GENERIC = ROOT / "pages" / "services" / "_shared" / "vk-cases.json"


def md5(path):
    return hashlib.md5(path.read_bytes()).hexdigest()


class VkCasesPublishTests(unittest.TestCase):
    def test_apply_keeps_slug_vk_cases_not_generic(self):
        if not GENERIC.is_file():
            self.skipTest("shared vk-cases missing")
        generic_md5 = md5(GENERIC)
        tmp = Path(tempfile.mkdtemp(prefix="lireate-vk-cases-"))
        docroot = tmp / "docroot"
        home = tmp / "autopub"
        state = home / "state" / "services-published.json"
        docroot.mkdir(parents=True)
        try:
            os.environ["LIREATE_ALLOW_PUBLISH"] = "yes"
            os.environ["LIREATE_SKIP_SERVICE_HTTP_VERIFY"] = "yes"
            for slug in PRESERVE_SLUGS:
                slug_src = ROOT / "pages" / "services" / slug / "assets" / "lireate-shared" / "vk-cases.json"
                if not slug_src.is_file():
                    self.skipTest("slug vk-cases missing: {0}".format(slug))
                slug_md5 = md5(slug_src)
                if slug_md5 == generic_md5:
                    self.fail("slug vk-cases must differ from _shared for test: {0}".format(slug))
                manifest_path = ROOT / "pages" / "services" / slug / "publish.manifest.json"
                url_path = __import__("json").loads(manifest_path.read_text(encoding="utf-8"))["url_path"]
                dest = docroot / url_path / "assets" / "lireate-shared"
                dest.mkdir(parents=True)
                shutil.copy2(str(GENERIC), str(dest / "vk-cases.json"))
                self.assertEqual(generic_md5, md5(dest / "vk-cases.json"))
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
                    slug_md5,
                    md5(dest / "vk-cases.json"),
                    "publish must deploy slug-specific vk-cases, not generic",
                )
        finally:
            shutil.rmtree(tmp, ignore_errors=True)


if __name__ == "__main__":
    unittest.main()
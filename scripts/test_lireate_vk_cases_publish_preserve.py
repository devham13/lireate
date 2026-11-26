#!/usr/bin/env python3
"""Publish must deploy slug-specific vk-cases.json, not overwrite with generic _shared."""

import hashlib
import os
import shutil
import sys
import tempfile
import unittest
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
FIXTURES = Path(__file__).resolve().parent / "fixtures" / "vk-cases-live"
sys.path.insert(0, str(ROOT / "scripts"))

import lireate_hosting_publish_service as pub  # noqa: E402

PRESERVE_SLUGS = (
    "zashchita-avtorskih-prav",
    "vozrazhenie-protiv-registracii-tovarnogo-znaka",
)
EXPECTED_LIVE_MD5 = {
    "zashchita-avtorskih-prav": "35f7c2f6603f3cf03e47c8b72bccb683",
    "vozrazhenie-protiv-registracii-tovarnogo-znaka": "34d7600aecbac29a11d154b5c109d7b0",
}
GENERIC = ROOT / "pages" / "services" / "_shared" / "vk-cases.json"


def md5(path):
    return hashlib.md5(path.read_bytes()).hexdigest()


class VkCasesPublishTests(unittest.TestCase):
    def test_repo_slug_vk_cases_match_live_md5(self):
        for slug, expected in EXPECTED_LIVE_MD5.items():
            repo_path = ROOT / "pages" / "services" / slug / "assets" / "lireate-shared" / "vk-cases.json"
            self.assertTrue(repo_path.is_file(), repo_path)
            self.assertEqual(expected, md5(repo_path), slug)

    def test_apply_deploys_slug_vk_cases_not_generic(self):
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
                fixture = FIXTURES / (slug + ".json")
                slug_src = ROOT / "pages" / "services" / slug / "assets" / "lireate-shared" / "vk-cases.json"
                self.assertTrue(fixture.is_file(), fixture)
                self.assertTrue(slug_src.is_file(), slug_src)
                slug_md5 = md5(fixture)
                self.assertEqual(slug_md5, md5(slug_src))
                self.assertNotEqual(slug_md5, generic_md5)
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
                self.assertEqual(slug_md5, md5(dest / "vk-cases.json"))
                self.assertEqual(EXPECTED_LIVE_MD5[slug], md5(dest / "vk-cases.json"))
        finally:
            shutil.rmtree(tmp, ignore_errors=True)


if __name__ == "__main__":
    unittest.main()
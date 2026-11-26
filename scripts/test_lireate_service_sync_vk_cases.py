#!/usr/bin/env python3
"""sync_shared must not replace slug-specific vk-cases.json with generic _shared."""

import hashlib
import unittest
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
import sys

sys.path.insert(0, str(ROOT / "scripts"))
import lireate_service_sync_shared as sync_shared  # noqa: E402

CUSTOM_SLUGS = (
    "zashchita-avtorskih-prav",
    "vozrazhenie-protiv-registracii-tovarnogo-znaka",
)


def md5(path):
    return hashlib.md5(path.read_bytes()).hexdigest()


class SyncVkCasesTests(unittest.TestCase):
    def test_sync_keeps_custom_vk_cases_in_repo(self):
        generic = ROOT / "pages" / "services" / "_shared" / "vk-cases.json"
        generic_md5 = md5(generic)
        for slug in CUSTOM_SLUGS:
            service = ROOT / "pages" / "services" / slug
            vk = service / "assets" / "lireate-shared" / "vk-cases.json"
            before = md5(vk)
            self.assertNotEqual(before, generic_md5, slug)
            sync_shared.sync_service(service, dry_run=False)
            self.assertEqual(before, md5(vk), slug)


if __name__ == "__main__":
    unittest.main()
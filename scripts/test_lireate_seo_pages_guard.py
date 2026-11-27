#!/usr/bin/env python3
import unittest
from pathlib import Path
import sys

sys.path.insert(0, str(Path(__file__).resolve().parent))
import lireate_seo_pages_guard_lib as guard  # noqa: E402


class SeoPagesGuardTests(unittest.TestCase):
    def test_mktu_and_overrides(self):
        self.assertEqual([], guard.validate_mktu_classes())
        errs = guard.validate_cms_overrides()
        self.assertEqual([], errs[:3], errs)


if __name__ == "__main__":
    unittest.main()
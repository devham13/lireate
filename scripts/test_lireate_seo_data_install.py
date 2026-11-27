#!/usr/bin/env python3
"""Tests for SEO data JSON/MKTU bundle."""

import json
import unittest
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
DATA = ROOT / "data"


class SeoDataBundleTests(unittest.TestCase):
    def test_mktu_classes_45(self):
        path = DATA / "lireate-seo-mktu-classes.json"
        data = json.loads(path.read_text(encoding="utf-8"))
        self.assertEqual(len(data), 45)
        for n in range(1, 46):
            key = str(n)
            self.assertIn(key, data)
            self.assertIn("title", data[key])
            self.assertIn("description", data[key])

    def test_cms_overrides_json(self):
        path = DATA / "lireate-seo-cms-overrides.json"
        data = json.loads(path.read_text(encoding="utf-8"))
        self.assertIn("home", data)
        self.assertIn("registraciya_tovarnogo_znaka", data)

    def test_audit_meta_news_preserved(self):
        path = DATA / "lireate-seo-audit-meta.json"
        data = json.loads(path.read_text(encoding="utf-8"))
        self.assertIsInstance(data.get("news"), dict)
        self.assertGreater(len(data["news"]), 5)


if __name__ == "__main__":
    unittest.main()
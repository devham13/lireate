#!/usr/bin/env python3
import sys
import unittest
from pathlib import Path
sys.path.insert(0, str(Path(__file__).resolve().parents[0]))
from lireate_sitemap_index_lib import build_sitemap_bundle, classify_path, load_config

class TestSitemapIndexLib(unittest.TestCase):
    def test_classify(self):
        self.assertEqual(classify_path("/mktu"), "mktu")
    def test_bundle(self):
        bundle = build_sitemap_bundle("https://example.com", [], load_config())
        self.assertIn("sitemap.xml", bundle)

if __name__ == "__main__":
    unittest.main()

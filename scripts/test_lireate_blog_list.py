#!/usr/bin/env python3
"""Tests for blog list reading time and index merge."""

import unittest
from pathlib import Path

REPO = Path(__file__).resolve().parents[1]
import sys

sys.path.insert(0, str(REPO / "scripts"))


class BlogListReadingTests(unittest.TestCase):
    @classmethod
    def setUpClass(cls):
        # Load PHP helpers via subprocess would be heavy; test index JSON contract.
        import json

        cls.index = json.loads((REPO / "data/blog-articles-index.json").read_text(encoding="utf-8"))

    def test_index_has_reading_mins(self):
        for row in self.index["articles"]:
            self.assertIn("reading_mins", row)
            self.assertGreaterEqual(int(row["reading_mins"]), 1)

    def test_reading_mins_not_all_one(self):
        mins = {int(a["reading_mins"]) for a in self.index["articles"]}
        self.assertTrue(len(mins) > 1 or max(mins) > 1)


if __name__ == "__main__":
    unittest.main()

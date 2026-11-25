#!/usr/bin/env python3
import sys
import unittest
from pathlib import Path

REPO = Path(__file__).resolve().parents[1]
sys.path.insert(0, str(REPO / "scripts"))

import lireate_main_js_consent_skip_patch as patch  # noqa: E402

LIVE_MAIN = REPO / "tests/fixtures/live-render/live-assets/template/js/main.js"


class MainJsConsentSkipPatchTests(unittest.TestCase):
    def test_patch_live_fixture_anchor(self):
        text = LIVE_MAIN.read_text(encoding="utf-8")
        planned, changed = patch.plan_patch(text)
        self.assertTrue(changed, "live main.js should accept consent skip patch")
        self.assertIn(patch.MARKER, planned)


if __name__ == "__main__":
    unittest.main()
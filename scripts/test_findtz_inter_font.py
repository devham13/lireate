#!/usr/bin/env python3
import os
import pathlib
import subprocess
import unittest

ROOT = pathlib.Path(__file__).resolve().parents[1]
FINDTZ = ROOT / "pages/services/proverka-tovarnogo-znaka"


class FindtzInterFontTests(unittest.TestCase):
    def test_font_files_and_license(self):
        inter = FINDTZ / "assets/fonts/inter"
        for name in ("inter-cyrillic.woff2", "inter-latin.woff2", "OFL.txt"):
            self.assertTrue((inter / name).is_file(), msg=name)

    def test_index_local_inter_no_google(self):
        html = (FINDTZ / "index.php").read_text(encoding="utf-8")
        self.assertIn("findtz-inter-font.css", html)
        self.assertIn("findtz-inter-family-v1.css", html)
        self.assertIn("lireate-findtz-inter-v1", html)
        self.assertNotIn("fonts.googleapis.com", html)

    def test_font_face_rules(self):
        css = (FINDTZ / "assets/findtz-inter-font.css").read_text(encoding="utf-8")
        self.assertIn("@font-face", css)
        self.assertIn("font-display: swap", css)
        self.assertIn("unicode-range", css)
        self.assertIn("font-weight: 100 900", css)

    def test_family_css_font_family_only(self):
        css = (FINDTZ / "assets/findtz-inter-family-v1.css").read_text(encoding="utf-8")
        self.assertNotIn("font-size", css)
        self.assertNotIn("line-height", css)
        self.assertNotIn("letter-spacing", css)
        self.assertNotIn("margin", css)

    @unittest.skipUnless(os.environ.get("LIREATE_PUBLIC_SITE_URL"), "need live /findtz")
    def test_playwright_layout_no_stray_gt(self):
        script = ROOT / "scripts/verify_findtz_font_layout.mjs"
        proc = subprocess.run(
            ["node", str(script)],
            cwd=ROOT,
            capture_output=True,
            text=True,
            timeout=300,
        )
        self.assertEqual(
            proc.returncode,
            0,
            msg=(proc.stdout or "") + (proc.stderr or ""),
        )


if __name__ == "__main__":
    unittest.main()
#!/usr/bin/env python3
"""Self-test: live-parity markers in rendered service HTML (registraciya + findtz)."""

import subprocess
import sys
import unittest
from pathlib import Path

REPO_ROOT = Path(__file__).resolve().parents[1]
sys.path.insert(0, str(REPO_ROOT / "scripts"))

import lireate_service_lint as lint  # noqa: E402

PARITY_SLUGS = (
    "registraciya-tovarnogo-znaka",
    "proverka-tovarnogo-znaka",
)


class ServiceLiveMarkersTests(unittest.TestCase):
    def test_shared_vk_cases_assets_exist(self):
        shared = REPO_ROOT / "pages" / "services" / "_shared"
        self.assertTrue((shared / "lireate-vk-cases.css").is_file())
        self.assertTrue((shared / "vk-cases.json").is_file())
        self.assertTrue((shared / "blocks" / "lireate-vk-cases.inc.php").is_file())

    def test_lint_passes_for_parity_slugs(self):
        for slug in PARITY_SLUGS:
            errors, warnings = lint.lint_slug(slug)
            if warnings:
                for w in warnings:
                    sys.stderr.write("WARN {0}: {1}\n".format(slug, w))
            self.assertEqual(errors, [], "lint errors for {0}: {1}".format(slug, errors))

    def test_parity_markers_in_output(self):
        for slug in PARITY_SLUGS:
            service_dir = REPO_ROOT / "pages" / "services" / slug
            raw = lint._read(service_dir / "index.php")
            rendered = lint._render_service_html(service_dir)
            parity_html = rendered if rendered else lint._expand_php_includes(raw, service_dir)
            self.assertTrue(parity_html, "no HTML to check for {0}".format(slug))
            issues = lint._lint_live_parity_markers(slug, parity_html)
            self.assertEqual(issues, [], "{0}: {1}".format(slug, issues))

    def test_hosting_publish_script_compiles(self):
        script = REPO_ROOT / "scripts" / "lireate_hosting_publish_service.py"
        proc = subprocess.run(
            [sys.executable, "-m", "py_compile", str(script)],
            capture_output=True,
            text=True,
        )
        self.assertEqual(proc.returncode, 0, proc.stderr or proc.stdout)


if __name__ == "__main__":
    raise SystemExit(unittest.main())

#!/usr/bin/env python3
import pathlib
import unittest

ROOT = pathlib.Path(__file__).resolve().parents[1]
SERVICES = ROOT / "pages" / "services"


class BreadcrumbPlainLinkTests(unittest.TestCase):
    def test_shared_block_has_plain_uslugi_link(self):
        text = (SERVICES / "_shared/blocks/lireate-breadcrumbs.inc.php").read_text(encoding="utf-8")
        self.assertIn('href="/uslugi/">Услуги</a>', text)
        self.assertNotIn("lx-breadcrumb-services", text)

    def test_registraciya_block_plain_uslugi(self):
        text = (SERVICES / "registraciya-tovarnogo-znaka/blocks/lireate-breadcrumbs.inc.php").read_text(
            encoding="utf-8"
        )
        self.assertIn('href="/uslugi/">Услуги</a>', text)
        self.assertNotIn("lx-breadcrumb-services", text)

    def test_service_indexes_no_dropdown_markup_or_js(self):
        for index in SERVICES.glob("*/index.php"):
            if index.parent.name.startswith("_"):
                continue
            body = index.read_text(encoding="utf-8")
            self.assertNotIn("lx-breadcrumb-services", body, msg=str(index))
            self.assertNotIn("lireate-breadcrumbs.js", body, msg=str(index))


if __name__ == "__main__":
    unittest.main()
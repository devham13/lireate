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

    def test_breadcrumbs_js_demotes_legacy_dropdown(self):
        js = (SERVICES / "_shared/lireate-breadcrumbs.js").read_text(encoding="utf-8")
        self.assertIn("lx-breadcrumb-services-trigger", js)
        self.assertIn('/uslugi/', js)

    def test_light_v4_css_hides_legacy_breadcrumb_trigger(self):
        css = (SERVICES / "_shared/lireate-light-v4.css").read_text(encoding="utf-8")
        self.assertIn("lx-breadcrumb-services-trigger", css)
        self.assertIn("display:none", css)

    def test_service_dark_neutralizes_legacy_breadcrumb_on_light_theme(self):
        css = (SERVICES / "_shared/lireate-service-dark.css").read_text(encoding="utf-8")
        self.assertIn("body.lireate-theme-light-v4", css)
        self.assertIn("lx-breadcrumb-services-trigger::after", css)
        self.assertIn('href="/uslugi/"', css)

    def test_service_indexes_no_dropdown_markup_or_js(self):
        for index in SERVICES.glob("*/index.php"):
            if index.parent.name.startswith("_"):
                continue
            body = index.read_text(encoding="utf-8")
            self.assertNotIn("lx-breadcrumb-services", body, msg=str(index))
            self.assertNotIn("lireate-breadcrumbs.js", body, msg=str(index))


if __name__ == "__main__":
    unittest.main()
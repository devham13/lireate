#!/usr/bin/env python3
"""WCAG contrast guards: company V5 vs live lireate-cms-page-dark shell."""

import os
import re
import unittest
from pathlib import Path
from urllib.parse import urljoin
from urllib.request import Request, urlopen

ROOT = Path(__file__).resolve().parents[1]
CSS = ROOT / "template" / "css" / "lireate-company-pages-v5.css"
LIVE_PAGES = (
    ("/partners", ".lireate-partners-lead"),
    ("/contacts/", ".lireate-contacts-lead"),
    ("/o_kompanii", ".lireate-about-lead"),
)


def _lum(channel):
    c = channel / 255.0
    return c / 12.92 if c <= 0.03928 else ((c + 0.055) / 1.055) ** 2.4


def contrast_ratio(fg, bg):
    l1 = 0.2126 * _lum(fg[0]) + 0.7152 * _lum(fg[1]) + 0.0722 * _lum(fg[2])
    l2 = 0.2126 * _lum(bg[0]) + 0.7152 * _lum(bg[1]) + 0.0722 * _lum(bg[2])
    lighter = max(l1, l2)
    darker = min(l1, l2)
    return (lighter + 0.05) / (darker + 0.05)


def parse_rgb(css_color):
    css_color = (css_color or "").strip().lower()
    m = re.match(r"rgba?\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)", css_color)
    if m:
        return (int(m.group(1)), int(m.group(2)), int(m.group(3)))
    return None


def site_origin():
    base = os.environ.get("LIREATE_PUBLIC_SITE_URL", "").strip().rstrip("/")
    if not base:
        host = os.environ.get("LIREATE_PUBLIC_SITE_HOST", "").strip()
        if host:
            base = "https://" + host.lstrip("/")
    return base


def fetch_live_html(path):
    origin = site_origin()
    if not origin:
        raise unittest.SkipTest("LIREATE_PUBLIC_SITE_HOST not set")
    url = urljoin(origin + "/", path.lstrip("/"))
    req = Request(url, headers={"User-Agent": "lireate-company-v5-contrast-test/1.0"})
    with urlopen(req, timeout=60) as resp:
        return resp.read().decode("utf-8", errors="replace")


class CompanyV5ContrastCssTests(unittest.TestCase):
    def test_readable_token_and_cms_dark_overrides(self):
        text = CSS.read_text(encoding="utf-8")
        self.assertIn("--lc5-readable:#2d4764", text.replace(" ", ""))
        self.assertIn("body.lireate-cms-page-dark.lireate-company-v5", text)
        self.assertIn(".container.content", text)
        self.assertIn(".lireate-partners-lead", text)
        self.assertIn(".lireate-contacts-lead", text)
        fg = (45, 71, 100)
        bg = (255, 255, 255)
        self.assertGreaterEqual(contrast_ratio(fg, bg), 7.0)

    def test_asset_version_query_in_templates(self):
        needle = "lireate-company-pages-v5.css?v=20261126c"
        for rel in (
            "template/page/partners/lireate-partners-page.inc.php",
            "template/page/kontaktyi/lireate-contacts-page.inc.php",
        ):
            body = (ROOT / rel).read_text(encoding="utf-8")
            self.assertIn(needle, body, rel)


class CompanyV5ContrastLiveTests(unittest.TestCase):
    def test_live_html_has_v5_and_lead_markup(self):
        for path, selector in LIVE_PAGES:
            html = fetch_live_html(path)
            class_name = selector.lstrip(".")
            self.assertIn(class_name, html, path)
            self.assertIn("lireate-company-v5", html, path)
            self.assertIn("lireate-company-pages-v5.css", html, path)

    def test_live_partners_and_contacts_lead_contrast_with_repo_css(self):
        try:
            from playwright.sync_api import sync_playwright
        except ImportError:
            raise unittest.SkipTest("playwright not installed")
        origin = site_origin()
        if not origin:
            raise unittest.SkipTest("LIREATE_PUBLIC_SITE_HOST not set")
        css_bytes = CSS.read_bytes()
        checks = (
            ("/partners", ".lireate-partners-lead"),
            ("/contacts/", ".lireate-contacts-lead"),
        )
        with sync_playwright() as pw:
            browser = pw.chromium.launch(args=["--no-sandbox"])
            page = browser.new_page(viewport={"width": 1440, "height": 900})

            def route_handler(route):
                req = route.request
                if "lireate-company-pages-v5.css" in req.url:
                    route.fulfill(
                        status=200,
                        body=css_bytes,
                        headers={"content-type": "text/css"},
                    )
                    return
                if any(
                    host in req.url
                    for host in (
                        "mc.yandex.ru",
                        "yandex.ru/metrika",
                        "google-analytics",
                        "googletagmanager",
                    )
                ):
                    route.abort()
                    return
                route.continue_()

            page.route("**/*", route_handler)
            for path, selector in checks:
                page.goto(origin + path, wait_until="domcontentloaded", timeout=90000)
                page.wait_for_timeout(1200)
                data = page.eval_on_selector(
                    selector,
                    """(el) => {
                        const fg = getComputedStyle(el).color;
                        let node = el.parentElement;
                        let bg = 'rgb(255, 255, 255)';
                        while (node) {
                            const c = getComputedStyle(node).backgroundColor;
                            if (c && c !== 'rgba(0, 0, 0, 0)' && c !== 'transparent') {
                                bg = c;
                                break;
                            }
                            node = node.parentElement;
                        }
                        return { fg, bg };
                    }""",
                )
                fg = parse_rgb(data["fg"])
                bg = parse_rgb(data["bg"]) or (255, 255, 255)
                self.assertIsNotNone(fg, (path, data))
                cr = contrast_ratio(fg, bg)
                self.assertGreaterEqual(
                    cr,
                    4.5,
                    "{} {} fg={} bg={} cr={:.2f}".format(path, selector, data["fg"], data["bg"], cr),
                )
                self.assertNotEqual(fg, (203, 213, 225), path + " still #cbd5e1")
                if path in ("/partners", "/contacts/"):
                    self.assertGreaterEqual(cr, 7.0, path)
            browser.close()


if __name__ == "__main__":
    unittest.main()
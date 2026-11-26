#!/usr/bin/env python3
import importlib.util
import re
from pathlib import Path
import shutil
import subprocess
import tempfile
import unittest

PATH = Path(__file__).resolve().parent / 'lireate_patent_light_v4_install.py'
REPO = PATH.parents[1]
FIXTURE_LEGACY = PATH.parent / 'fixtures/cms-main-patent-legacy-blocks.php'
FIXTURE_PROD_DARK = PATH.parent / 'fixtures/cms-main-prod-dark-pages-v2-snippet.php'
_DARK_V2 = 'lireate_design_cms_dark_pages_v2'
_ARRAY_SLUGS = re.compile(r"'(?:\\'|[^'])+'")


def prod_dark_v2_shape(text):
    """Drop home from dark v2 arrays only (production main.php shape)."""
    out = text
    pos = 0
    while True:
        idx = out.find(_DARK_V2, pos)
        if idx < 0:
            break
        sub = out[idx : idx + 320]
        am = re.search(r"array\s*\(([^)]*)\)", sub)
        if am:
            inner = am.group(1)
            if "'home'" in inner:
                new_inner = inner.replace("'home',", "").replace(",'home'", "")
                a0 = idx + am.start()
                a1 = idx + am.end()
                out = out[:a0] + "array(" + new_inner + ")" + out[a1:]
        pos = idx + len(_DARK_V2)
    return out


def dark_v2_arrays(text):
    items = []
    pos = 0
    while True:
        idx = text.find(_DARK_V2, pos)
        if idx < 0:
            break
        sub = text[idx : idx + 320]
        am = re.search(r"array\s*\(([^)]*)\)", sub)
        if am:
            items.append(_ARRAY_SLUGS.findall(am.group(1)))
        pos = idx + len(_DARK_V2)
    return items


def hero_dock_footer_slices(text):
    keys = (
        "lireate_design_cms_hero_assets_v1",
        "lireate_design_service_mobile_dock_v2 head",
        "lireate_design_cms_lu_footer_v1",
        "lireate_design_service_mobile_dock_v2 footer",
    )
    out = {}
    for key in keys:
        i = text.find(key)
        if i >= 0:
            out[key] = text[i : i + 220]
    return out
spec = importlib.util.spec_from_file_location('patinst', PATH)
module = importlib.util.module_from_spec(spec)
spec.loader.exec_module(module)


class TestPatentInstaller(unittest.TestCase):
    def test_patch_base_and_existing_chain(self):
        first = '<div class="page_container">\n<?php if($temp[\'apsw\']){?> old <?php } ?>'
        other = '<div class="page_container">\n<?php if($temp[\'page\']==\'o_kompanii\'){?> About <?php } elseif ($temp[\'apsw\']){?> Old <?php } ?>'
        for text in (first, other):
            planned = module.patch_main(text)
            self.assertEqual(module.patch_main(planned), planned)
            self.assertIn("$temp['page']=='patent'", planned)
            self.assertIn(module.MARKER, planned)
            p = subprocess.run(['php', '-l'], input=planned, text=True, capture_output=True)
            self.assertEqual(p.returncode, 0, p.stderr)

    def test_patch_live_legacy_branches_idempotent(self):
        text = FIXTURE_LEGACY.read_text(encoding='utf-8')
        once = module.patch_main(text)
        twice = module.patch_main(once)
        self.assertEqual(once, twice)
        self.assertIn("patent' && false", once)
        self.assertIn("patent_prom_obr' && false", once)
        self.assertIn(module.INCLUDE_SNIPPET, once)
        self.assertNotIn('legacy-patent', once.split('&& false')[0])

    def test_full_apply_idempotent_rollback(self):
        with tempfile.TemporaryDirectory() as tmp:
            root = Path(tmp)
            docroot = root / 'docroot'
            main = docroot / 'template/main.php'
            main.parent.mkdir(parents=True)
            orig = '<div class="page_container">\n<?php if($temp[\'apsw\']){?> legacy <?php } ?>'
            main.write_text(orig, encoding='utf-8')
            cmd = ['python3', str(PATH), '--docroot', str(docroot), '--backup-root', str(root / 'backup')]
            chk = subprocess.run(cmd, text=True, capture_output=True)
            self.assertEqual(chk.returncode, 0, chk.stderr)
            self.assertIn('DRY_RUN', chk.stdout)
            self.assertEqual(main.read_text(), orig)
            applied = subprocess.run(cmd + ['--apply'], text=True, capture_output=True)
            self.assertEqual(applied.returncode, 0, applied.stderr)
            self.assertIn('APPLIED', applied.stdout)
            self.assertIn(module.MARKER, main.read_text())
            second = subprocess.run(cmd + ['--apply'], text=True, capture_output=True)
            self.assertIn('NO_CHANGES', second.stdout)
            backup = next((root / 'backup').iterdir())
            rb = subprocess.run(cmd + ['--rollback', str(backup)], text=True, capture_output=True)
            self.assertEqual(rb.returncode, 0, rb.stderr)
            self.assertEqual(main.read_text(), orig)
            self.assertFalse((docroot / 'template/css/lireate-patent-light-v4.css').exists())

    def test_dark_exclusion_prod_snippet_only_dark_v2(self):
        src = FIXTURE_PROD_DARK.read_text(encoding='utf-8')
        hero_tail = (
            "<?php /* lireate_design_cms_hero_assets_v1 */ "
            "if (in_array($temp['page'], array('patent','patent_prom_obr'), true)): ?>"
        )
        src = src + "\n" + hero_tail
        before_hero = hero_dock_footer_slices(src)
        out = module.patch_main_dark_exclusion(src)
        self.assertIn(module.DARK_EXCLUDE_MARKER, out)
        for slugs in dark_v2_arrays(out):
            self.assertNotIn("'patent'", slugs)
            self.assertNotIn("'patent_prom_obr'", slugs)
            self.assertIn("'mktu'", slugs)
            self.assertIn("'reviews'", slugs)
        after = module.patch_main_dark_exclusion(out)
        self.assertEqual(out, after)
        self.assertIn("'patent'", before_hero["lireate_design_cms_hero_assets_v1"])

    def test_dark_exclusion_live_main_prod_shape(self):
        live_main = REPO / 'tests/fixtures/live-render/live-assets/template/main.php'
        if not live_main.is_file():
            self.skipTest('live-render main.php fixture missing')
        src = prod_dark_v2_shape(live_main.read_text(encoding='utf-8'))
        untouched = hero_dock_footer_slices(src)
        out = module.patch_main_dark_exclusion(src)
        self.assertGreaterEqual(out.count(module.DARK_EXCLUDE_MARKER), 1)
        for slugs in dark_v2_arrays(out):
            self.assertNotIn("'patent'", slugs)
            self.assertNotIn("'patent_prom_obr'", slugs)
        self.assertEqual(untouched, hero_dock_footer_slices(out))

    def test_legacy_fixture_apply_on_live_render_main(self):
        live_main = REPO / 'tests/fixtures/live-render/live-assets/template/main.php'
        if not live_main.is_file():
            self.skipTest('live-render main.php fixture missing')
        with tempfile.TemporaryDirectory() as tmp:
            root = Path(tmp)
            docroot = root / 'docroot'
            main = docroot / 'template/main.php'
            main.parent.mkdir(parents=True)
            shutil.copy2(live_main, main)
            prod = prod_dark_v2_shape(main.read_text(encoding='utf-8'))
            hero_snap = hero_dock_footer_slices(prod)
            main.write_text(prod, encoding='utf-8')
            cmd = [
                'python3', str(PATH),
                '--docroot', str(docroot),
                '--backup-root', str(root / 'backup'),
                '--apply',
            ]
            applied = subprocess.run(cmd, text=True, capture_output=True)
            self.assertEqual(applied.returncode, 0, applied.stdout + applied.stderr)
            patched = main.read_text(encoding='utf-8')
            self.assertIn("patent' && false", patched)
            self.assertIn("patent_prom_obr' && false", patched)
            self.assertIn(module.DARK_EXCLUDE_MARKER, patched)
            for slugs in dark_v2_arrays(patched):
                self.assertNotIn("'patent'", slugs)
            self.assertEqual(hero_snap, hero_dock_footer_slices(patched))
            lint = subprocess.run(['php', '-l', str(main)], capture_output=True, text=True)
            self.assertEqual(lint.returncode, 0, lint.stderr)


if __name__ == '__main__':
    unittest.main()
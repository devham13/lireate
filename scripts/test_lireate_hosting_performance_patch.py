import json
from pathlib import Path
import shutil
import sys
import tempfile
import unittest

sys.path.insert(0, str(Path(__file__).resolve().parent))
import lireate_hosting_performance_patch as patch

CMS = '''<!doctype html><html lang="ru"><head><meta charset="utf-8">
<title>Performance fixture</title>
<script src="//vk.com/js/api/openapi.js?146"></script>
<script>VK.init({apiId: 6156329, onlyWidgets: true});</script>
<script>window.analyticsID = 'keep-existing-id';</script>
</head><body><div id="lireate-home"><h1>Лирейт</h1></div>
<a id="whatsapp_icon" href="https://wa.me/79600484957"></a>
<a id="telegram_icon" href="https://t.me/lireate_patent"></a>
<a class="topBarLogo" href="/"> </a>
<footer class="lx-site-footer"><p class="lx-site-footer-copy">LIREATE</p></footer>
<script>window.parsedBeforeSDK = !window.VK;</script>
<form id="lead"><input name="phone"><button type="submit">Отправить</button></form>
<script>document.querySelector('#lead').onsubmit = function (e) {e.preventDefault(); window.formSubmitted = true;};</script>
</body></html>'''


class PerformancePatchTests(unittest.TestCase):
    def setUp(self):
        self.temp = tempfile.TemporaryDirectory()
        self.root = Path(self.temp.name) / 'docroot'
        (self.root / 'template').mkdir(parents=True)
        self.main = self.root / 'template/main.php'
        self.main.write_text(CMS)
        self.backups = Path(self.temp.name) / 'backups'

    def tearDown(self):
        self.temp.cleanup()

    def test_dry_run_leaves_site_unchanged_and_preserves_analytics(self):
        plan, _ = patch.build_plan(self.root)
        self.assertEqual(self.main.read_text(), CMS)
        text = plan[Path('template/main.php')].decode()
        self.assertIn("window.analyticsID = 'keep-existing-id';", text)
        self.assertIn('<script defer src="//vk.com/js/api/openapi.js?146">', text)
        self.assertIn('DOMContentLoaded', text)
        self.assertEqual(patch.patch_head(text, True), text)

    def test_inline_vk_widgets_keep_the_synchronous_sdk(self):
        (self.root / 'template/widget.php').write_text('<script>VK.Widgets.Group("widget", {});</script>')
        plan, notes = patch.build_plan(self.root)
        self.assertIn('VK defer skipped', notes[0])
        self.assertNotIn('<script defer src="//vk', plan[Path('template/main.php')].decode())

    def test_refuse_paths_and_symlinks_outside_docroot(self):
        with self.assertRaises(ValueError):
            patch.safe_path(self.root, '../outside.php')
        link = self.root / 'template/escape.php'
        link.symlink_to(Path(self.temp.name) / 'outside.php')
        with self.assertRaises(ValueError):
            patch.safe_path(self.root, 'template/escape.php')

    def test_bundle_preserves_order_urls_and_refreshes_after_css_change(self):
        css = self.root / 'template/css'
        css.mkdir()
        (css / 'a.css').write_text('a { background: url(../images/a.png); }')
        (css / 'b.css').write_text('a { color: red; }')
        original = '<head><link rel="stylesheet" href="/template/css/a.css"><link rel="stylesheet" href="/template/css/b.css"></head>'
        html, assets = patch.bundle_styles(original, self.root)
        self.assertEqual(len(assets), 1)
        combined = next(iter(assets.values())).decode()
        self.assertIn('url("/template/images/a.png")', combined)
        self.assertLess(combined.index('background:'), combined.index('color: red'))
        again, same_assets = patch.bundle_styles(html, self.root)
        self.assertEqual(again, html)
        self.assertEqual(same_assets, assets)
        (css / 'b.css').write_text('a { color: blue; }')
        updated, _ = patch.bundle_styles(html, self.root)
        self.assertNotEqual(updated, html)

    def test_bundle_skips_remote_import_media_php_and_commented_styles(self):
        css = self.root / 'template/css'
        css.mkdir()
        (css / 'a.css').write_text('a { color: red; }')
        local = '<link rel="stylesheet" href="/template/css/a.css">'
        for original in [
            '<head><!--' + local + local + '--></head>',
            '<head><?php echo \'' + local + local + '\'; ?></head>',
            '<head>' + local + '<link rel="stylesheet" href="https://example.com/style.css"></head>',
            '<head>' + local + '<link rel="stylesheet" media="print" href="/template/css/a.css"></head>',
        ]:
            self.assertEqual(patch.bundle_styles(original, self.root), (original, {}))
        (css / 'a.css').write_text('@import "x.css"; a { color: red; }')
        original = '<head>' + local + local + '</head>'
        self.assertEqual(patch.bundle_styles(original, self.root), (original, {}))

    @unittest.skipUnless(shutil.which('php'), 'PHP CLI available in CI/hosting')
    def test_apply_backs_up_originals_and_is_idempotent(self):
        plan, _ = patch.build_plan(self.root)
        backup = patch.apply_plan(self.root, plan, self.backups)
        self.assertEqual((backup / 'template/main.php').read_text(), CMS)
        records = json.loads((backup / 'manifest.json').read_text())['files']
        self.assertTrue(next(r for r in records if r['path'] == 'template/main.php')['existed'])
        again, _ = patch.build_plan(self.root)
        self.assertEqual(again, {})

    @unittest.skipUnless(shutil.which('php'), 'PHP CLI available in CI/hosting')
    def test_invalid_php_changes_no_file(self):
        plan, _ = patch.build_plan(self.root)
        plan[Path('template/main.php')] = b'<?php invalid syntax ;'
        with self.assertRaisesRegex(ValueError, 'PHP lint failed'):
            patch.apply_plan(self.root, plan, self.backups)
        self.assertEqual(self.main.read_text(), CMS)
        self.assertFalse((self.root / 'template/css/lireate-performance.css').exists())


if __name__ == '__main__':
    unittest.main()

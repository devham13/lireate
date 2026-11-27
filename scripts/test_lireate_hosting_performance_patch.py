import json
from pathlib import Path
import shutil
import sys
import tempfile
import unittest
from unittest import mock

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

    @unittest.skipUnless(shutil.which('php'), 'PHP CLI available in CI/hosting')
    def test_helper_is_published_before_layout_can_require_it(self):
        folder = Path('pages/blog/_shared')
        helper = folder / 'lireate-blog-performance.inc.php'
        layout = folder / 'lireate-blog-layout.inc.php'
        plan = {layout: b'<?php require __DIR__ . "/lireate-blog-performance.inc.php";', helper: b'<?php /* helper */'}
        replace = patch.os.replace
        def publish(source, destination):
            if Path(destination).name == layout.name:
                self.assertTrue((self.root / helper).is_file(), 'Helper must be live before its caller')
            return replace(source, destination)
        with mock.patch.object(patch.os, 'replace', side_effect=publish):
            patch.apply_plan(self.root, plan, self.backups)


class ShellTuningTests(unittest.TestCase):
    def setUp(self):
        self.temp = tempfile.TemporaryDirectory()
        self.root = Path(self.temp.name) / 'docroot'
        (self.root / 'template').mkdir(parents=True)
        self.main = self.root / 'template/main.php'
        self.main.write_text('<!doctype html><html><head></head><body></body></html>')

    def tearDown(self):
        self.temp.cleanup()

    def test_confirmed_shell_fixes_are_idempotent(self):
        hero = self.root / patch.HERO_WEBP
        hero.parent.mkdir(parents=True)
        hero.write_bytes(b'webp')
        self.main.write_text('''<!doctype html><html><head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta property="og:image" content="https://example.test/images/OG_image.jpg" />
<link href="https://fonts.googleapis.com/css?family=Roboto:400&subset=latin,cyrillic" rel="stylesheet" type="text/css">
<link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css">
<!-- seo-perf-20261120-v1 -->
<meta name="lireate-performance-patch" content="seo-perf-20261120-v1">
<link rel="stylesheet" href="/template/css/lireate-performance.css?v=seo-perf-20261120-v1">
<script defer src="/template/js/lireate-performance.js?v=seo-perf-20261120-v1"></script>
<link rel="stylesheet" href="https://cdn.callbackkiller.com/widget/cbk.css">
<!--<script src="https://cdn.callbackkiller.com/widget/cbk.js?wcb_code=1"></script>-->
</head><body>
<h1>Патентное бюро «Лирейт»</h1>
<h1 class="lh4-seo-h1">Патентное бюро «<span>Лирейт</span>»</h1>
<h3 style="margin-bottom: 0px;">Регистрация товарного знака с оплатой за результат</h>
</body></html>''')
        plan, _ = patch.build_plan(self.root)
        text = plan[Path('template/main.php')].decode()
        self.assertIn('https://example.test/template/images/OG_image.jpg', text)
        self.assertNotIn('https://example.test/images/OG_image.jpg', text)
        self.assertIn('display=swap', text)
        self.assertIn('media="print"', text)
        self.assertNotIn('swiper-bundle', text)
        self.assertNotIn('cbk.css', text)
        self.assertIn('оплатой за результат</h3>', text)
        self.assertIn('lireate-legacy-hero-title', text)
        self.assertIn('lh4-seo-h1', text)
        self.assertIn(patch.VERSION, text)
        self.assertNotIn('seo-perf-20261120-v1', text)
        self.assertIn('fetchpriority="high"', text)
        self.main.write_bytes(plan[Path('template/main.php')])
        again, _ = patch.build_plan(self.root)
        self.assertNotIn(Path('template/main.php'), again)

    def test_home_font_stylesheet_does_not_block_repeat_runs(self):
        rel = Path('template/page/home/lireate-home-page.inc.php')
        path = self.root / rel
        path.parent.mkdir(parents=True)
        path.write_text('<?php\n' + patch.HOME_FONT + '\n', encoding='utf-8')
        plan, _ = patch.build_plan(self.root)
        text = plan[rel].decode()
        self.assertIn('onload="this.media=\'all\'"', text)
        self.assertIn('<noscript>', text)
        path.write_bytes(plan[rel])
        again, _ = patch.build_plan(self.root)
        self.assertNotIn(rel, again)

    def test_fix_og_image_replaces_legacy_og_default_path(self):
        cms = self.root / 'data/page/dogovory.php'
        cms.parent.mkdir(parents=True)
        cms.write_text(
            '<meta property="og:image" content="https://example.test/local/templates/lireate/images/og-default.jpg">',
            encoding='utf-8',
        )
        plan, _ = patch.build_plan(self.root)
        self.assertIn('/template/images/OG_image.jpg', plan[cms.relative_to(self.root)].decode())

    def test_fix_og_image_on_docroot_slug_index_php(self):
        slug = self.root / 'dogovory' / 'index.php'
        slug.parent.mkdir(parents=True)
        slug.write_text(
            '<meta property="og:image" content="https://example.test/local/templates/lireate/images/og-default.jpg">',
            encoding='utf-8',
        )
        plan, _ = patch.build_plan(self.root)
        self.assertIn('/template/images/OG_image.jpg', plan[Path('dogovory/index.php')].decode())

    def test_root_index_glob_skips_batch_agent_slugs(self):
        for name in ('findtz', 'patentnye-zadachi'):
            p = self.root / name / 'index.php'
            p.parent.mkdir(parents=True)
            p.write_text(
                '<meta property="og:image" content="https://example.test/local/templates/lireate/images/og-default.jpg">',
                encoding='utf-8',
            )
        plan, _ = patch.build_plan(self.root)
        self.assertNotIn(Path('findtz/index.php'), plan)
        self.assertNotIn(Path('patentnye-zadachi/index.php'), plan)

    def test_service_longread_reserves_sidebar_and_repairs_og_image(self):
        html = ('<head><link rel="preconnect" href="https://fonts.googleapis.com"></head>'
                '<body class="lireate-svc-body">'
                '<meta property="og:image" content="https://example.test/local/templates/lireate/images/og-default.jpg">'
                '<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400&display=swap" rel="stylesheet">'
                '</body>')
        out = patch.tune_service(html)
        self.assertIn('class="lsv7-managed lireate-svc-body"', out)
        self.assertIn('/template/images/OG_image.jpg', out)
        self.assertNotIn('og-default.jpg', out)
        self.assertIn("media='all'", out)
        self.assertIn('<link rel="preconnect" href="https://fonts.googleapis.com">', out)
        self.assertEqual(patch.tune_service(out), out)
        patent = html.replace('<body class="lireate-svc-body">',
                              '<body class="lireate-svc-body"><?php include "lireate-patent-tasks-sidebar-v6.inc.php"; ?>')
        kept = patch.tune_service(patent)
        self.assertNotIn('lsv7-managed', kept)

    def test_self_closing_font_link_stays_valid_and_legacy_heading_in_includes(self):
        tag = '<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans" />'
        out = patch.relax_google_fonts(tag)
        self.assertNotIn('/ onload', out)
        self.assertIn('media="print" onload="this.media=\'all\'"', out)
        broken = tag.replace('/>', 'media="print" / onload="this.media=\'all\'">')
        self.assertNotIn('/ onload', patch.relax_google_fonts(broken))
        rel = Path('template/page/home/lireate-home-hero.inc.php')
        path = self.root / rel
        path.parent.mkdir(parents=True)
        path.write_text('<h1>Патентное бюро «Лирейт»</h1>\n', encoding='utf-8')
        plan, _ = patch.build_plan(self.root)
        self.assertIn('lireate-legacy-hero-title', plan[rel].decode())

    def test_theme_url_links_bundle_across_a_comment(self):
        css = self.root / 'template/css'
        css.mkdir()
        (css / 'a.css').write_text('a { color: red; }')
        (css / 'b.css').write_text('b { color: blue; }')
        original = ('<head><link rel="stylesheet" href="{theme_url}/css/a.css">'
                    '<!-- legacy -->'
                    '<link rel="stylesheet" href="{theme_url}/css/b.css"></head>')
        html, assets = patch.bundle_styles(original, self.root)
        self.assertEqual(len(assets), 1)
        combined = next(iter(assets.values())).decode()
        self.assertLess(combined.index('color: red'), combined.index('color: blue'))
        self.assertIn('data-lireate-css-bundle="1"', html)


if __name__ == '__main__':
    unittest.main()

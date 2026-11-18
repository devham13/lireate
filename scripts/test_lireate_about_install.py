#!/usr/bin/env python3
import sys
import tempfile
import unittest
from pathlib import Path

REPO_ROOT = Path(__file__).resolve().parents[1]
sys.path.insert(0, str(REPO_ROOT / "scripts"))

import lireate_about_install_lib as lib  # noqa: E402
import lireate_cms_main_body_class as cms_body  # noqa: E402


MAIN_FRAGMENT = '<?php\n<body class="cms-default<?php if($temp[\'page\']==\'docs\') echo \' lireate-docs-body\';?><?php if($temp[\'page\']==\'kontaktyi\') echo \' lireate-contacts-body\';?><?php if($temp[\'page\']==\'partners\') echo \' lireate-partners-body\';?><?php if($temp[\'page\']==\'team\') echo \' lireate-team-body\';?>">\n\t\t\t<div class="page_container">\n\t\t\t\t<?php if($temp[\'apsw\']){?>\n\t\t\t\t\t<div class="title-ap"><h1><?=$temp[\'db_page\'][0][\'name\'];?></h1></div>\n\t\t\t\t\t<div class="txt-content"><?=$temp[\'db_page\'][0][\'text\'];?></div>\n\t\t\t\t<?php }else{ ?>\n\t\t\t\t\t<?php include(\'./template/page/\'.$temp[\'page\'].\'.php\'); ?>\n\t\t\t\t<?php }; ?>\n\t\t\t</div><!-- /page_container -->\n<?php\n$_lx_cms_dark = array(\'home\',\'patent\',\'patent_prom_obr\',\'mktu\',\'mktu1\',\'reviews\',\'portfolio\',\'spasibo\',\'uslugi\',\'avtor\',\'team\',\'partners\',\'kontaktyi\');\nif (in_array($temp[\'page\'], array(\'reviews\',\'portfolio\',\'spasibo\',\'uslugi\',\'avtor\',\'team\',\'partners\',\'kontaktyi\'), true)):\n?>\n'


class AboutInstallLibTests(unittest.TestCase):
    def test_patch_inserts_hook_and_classes(self):
        planned = lib.plan_about_template_content(MAIN_FRAGMENT)
        self.assertIn(lib.INSTALL_MARKER, planned)
        self.assertIn(lib.CMS_INCLUDE, planned)
        self.assertIn("} elseif ($temp['apsw']){?>", planned)
        self.assertIn(cms_body.ABOUT_BODY_COND, planned)
        self.assertIn("'o_kompanii'", planned)
        self.assertIn('<div class="page_container">', planned)
        once = lib.plan_about_template_content(planned)
        self.assertEqual(once, planned)

    def test_unpatch_restores_apsw(self):
        planned = lib.plan_about_template_content(MAIN_FRAGMENT)
        restored, changed = lib.unpatch_about_content_hook(planned)
        self.assertTrue(changed)
        self.assertIn(lib.APSW_NEEDLE, restored)
        self.assertNotIn(lib.INSTALL_MARKER, restored)

    def test_copy_plan_includes_shared_and_wrappers(self):
        rels = [str(rel).replace("\\", "/") for _src, rel in lib.copy_plan(REPO_ROOT)]
        self.assertIn("pages/o_kompanii/_shared/lireate-about-page.inc.php", rels)
        self.assertIn("template/page/o_kompanii/lireate-about-page.inc.php", rels)
        self.assertIn("template/page/o_kompanii.php", rels)
        self.assertIn("template/page/o_kompanii/index.php", rels)


if __name__ == "__main__":
    unittest.main()

#!/usr/bin/env python3
import importlib.util
from pathlib import Path
import subprocess
import tempfile
import unittest

PATH = Path(__file__).resolve().parent / 'lireate_patent_light_v4_install.py'
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
            self.assertIn("$temp['apsw']", planned)
            p = subprocess.run(['php', '-l'], input=planned, text=True, capture_output=True)
            # php -l on stdin supported, but an HTML snippet before php is syntactically valid.
            self.assertEqual(p.returncode,0,p.stderr)

    def test_full_apply_idempotent_rollback(self):
        with tempfile.TemporaryDirectory() as tmp:
            root = Path(tmp)
            docroot = root / 'docroot'
            main = docroot / 'template/main.php'
            main.parent.mkdir(parents=True)
            orig = '<div class="page_container">\n<?php if($temp[\'apsw\']){?> legacy <?php } ?>'
            main.write_text(orig,encoding='utf-8')
            cmd = ['python3', str(PATH), '--docroot', str(docroot), '--backup-root', str(root/'backup')]
            chk = subprocess.run(cmd, text=True, capture_output=True)
            self.assertEqual(chk.returncode,0,chk.stderr)
            self.assertIn('DRY_RUN',chk.stdout)
            self.assertEqual(main.read_text(),orig)
            applied = subprocess.run(cmd+['--apply'], text=True, capture_output=True)
            self.assertEqual(applied.returncode,0,applied.stderr)
            self.assertIn('APPLIED',applied.stdout)
            self.assertIn(module.MARKER, main.read_text())
            second = subprocess.run(cmd+['--apply'],text=True,capture_output=True)
            self.assertIn('NO_CHANGES',second.stdout)
            backup = next((root/'backup').iterdir())
            rb = subprocess.run(cmd+['--rollback',str(backup)],text=True,capture_output=True)
            self.assertEqual(rb.returncode,0,rb.stderr)
            self.assertEqual(main.read_text(),orig)
            self.assertFalse((docroot/'template/css/lireate-patent-light-v4.css').exists())

if __name__=='__main__':unittest.main()

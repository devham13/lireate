#!/usr/bin/env python3
"""Publish apply → rollback restores pre-publish bytes."""

import hashlib
import json
import os
import shutil
import subprocess
import sys
import tempfile
import unittest
from pathlib import Path
from unittest import mock

REPO_ROOT = Path(__file__).resolve().parents[1]
sys.path.insert(0, str(REPO_ROOT / "scripts"))

import lireate_hosting_publish_service as pub  # noqa: E402
import lireate_hosting_service_rollback_lib as rb  # noqa: E402


def md5(path):
    h = hashlib.md5()
    with open(path, "rb") as fp:
        h.update(fp.read())
    return h.hexdigest()


class ServiceRollbackTests(unittest.TestCase):
    def setUp(self):
        self.tmp = Path(tempfile.mkdtemp(prefix="lireate-svc-rb-"))
        self.office_repo = self.tmp / "office-repo"
        self.docroot = self.tmp / "docroot"
        self.home = self.tmp / "autopub"
        self.state_file = self.home / "state" / "services-published.json"
        self.slug = "demo-service"
        service_dir = self.office_repo / "pages" / "services" / self.slug
        service_dir.mkdir(parents=True)
        (service_dir / "index.php").write_text("<?php echo 'after';", encoding="utf-8")
        (service_dir / "publish.manifest.json").write_text(
            json.dumps({"slug": self.slug, "url_path": "uslugi/demo-service"}) + "\n",
            encoding="utf-8",
        )
        shared = self.office_repo / "pages" / "services" / "_shared"
        shared.mkdir(parents=True)
        (shared / "lireate-messenger-urls.php").write_text("<?php\n", encoding="utf-8")
        (shared / "service-directory.htaccess").write_text("DirectoryIndex index.php\n", encoding="utf-8")
        self.docroot.mkdir(parents=True)

    def tearDown(self):
        shutil.rmtree(self.tmp, ignore_errors=True)

    @mock.patch.object(pub, "_http_status", return_value=(200, None))
    def test_apply_rollback_removes_first_publish(self, _http):
        dest = self.docroot / "uslugi" / "demo-service"
        index = dest / "index.php"
        self.assertFalse(index.is_file())

        os.environ["LIREATE_ALLOW_PUBLISH"] = "yes"
        code = pub._publish_slug(
            self.slug,
            self.office_repo,
            self.docroot,
            "https://example.test",
            self.home,
            self.state_file,
            dry_run=False,
            allow=True,
            skip_git_pull=True,
            update_mode=False,
        )
        self.assertEqual(0, code)
        self.assertTrue(index.is_file())
        after_pub = md5(index)

        backup_root = self.home / "backups" / "services" / self.slug
        stamp_dir = sorted(p for p in backup_root.iterdir() if p.is_dir())[-1]
        manifest = rb.load_rollback_manifest(stamp_dir)
        self.assertIsNotNone(manifest)
        rb.restore_service_publish(self.docroot, stamp_dir, manifest)
        self.assertFalse(index.is_file(), "rollback must remove files from first publish")

        # second pass: update restores overwritten bytes
        index.write_text("<?php echo 'legacy';", encoding="utf-8")
        before = md5(index)
        (self.office_repo / "pages/services/demo-service/index.php").write_text(
            "<?php echo 'new';", encoding="utf-8"
        )
        code = pub._publish_slug(
            self.slug,
            self.office_repo,
            self.docroot,
            "https://example.test",
            self.home,
            self.state_file,
            dry_run=False,
            allow=True,
            skip_git_pull=True,
            update_mode=True,
        )
        self.assertEqual(0, code)
        self.assertNotEqual(before, md5(index))
        stamp_dir = sorted(p for p in backup_root.iterdir() if p.is_dir())[-1]
        manifest = rb.load_rollback_manifest(stamp_dir)
        rb.restore_service_publish(self.docroot, stamp_dir, manifest)
        self.assertEqual(before, md5(index))
        _ = after_pub


if __name__ == "__main__":
    unittest.main()
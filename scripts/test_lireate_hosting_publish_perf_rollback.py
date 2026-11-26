#!/usr/bin/env python3
"""Rollback order: performance first, then publish."""

import hashlib
import json
import os
import shutil
import sys
import tempfile
import unittest
from pathlib import Path
from unittest import mock

ROOT = Path(__file__).resolve().parents[1]
sys.path.insert(0, str(ROOT / "scripts"))

import lireate_hosting_publish_service as pub  # noqa: E402
import lireate_hosting_service_rollback_lib as rb  # noqa: E402


def md5(path):
    return hashlib.md5(path.read_bytes()).hexdigest()


class PublishPerfRollbackTests(unittest.TestCase):
    def setUp(self):
        self.tmp = Path(tempfile.mkdtemp(prefix="lireate-pub-perf-"))
        self.office = self.tmp / "office"
        self.docroot = self.tmp / "docroot"
        self.home = self.tmp / "autopub"
        self.slug = "demo-service"
        service_dir = self.office / "pages/services" / self.slug
        service_dir.mkdir(parents=True)
        (service_dir / "index.php").write_text("<?php echo 'published';", encoding="utf-8")
        (service_dir / "publish.manifest.json").write_text(
            json.dumps({"slug": self.slug, "url_path": "uslugi/demo-service"}) + "\n",
            encoding="utf-8",
        )
        shared = self.office / "pages/services/_shared"
        shared.mkdir(parents=True)
        (shared / "service-directory.htaccess").write_text("DirectoryIndex index.php\n", encoding="utf-8")
        (shared / "lireate-messenger-urls.php").write_text("<?php\n", encoding="utf-8")
        self.docroot.mkdir(parents=True)
        self.state = self.home / "state/services-published.json"
        dest = self.docroot / "uslugi/demo-service"
        dest.mkdir(parents=True)
        self.index = dest / "index.php"
        self.index.write_text("<?php echo 'legacy';", encoding="utf-8")
        self.legacy_md5 = md5(self.index)

    def tearDown(self):
        shutil.rmtree(self.tmp, ignore_errors=True)

    @mock.patch.object(pub, "_http_status", return_value=(200, None))
    def test_perf_then_publish_rollback_restores_legacy(self, _http):
        os.environ["LIREATE_ALLOW_PUBLISH"] = "yes"
        self.state.parent.mkdir(parents=True, exist_ok=True)
        self.state.write_text(
            json.dumps({self.slug: {"url_path": "uslugi/demo-service"}}) + "\n",
            encoding="utf-8",
        )
        code = pub._publish_slug(
            self.slug,
            self.office,
            self.docroot,
            "https://example.test",
            self.home,
            self.state,
            dry_run=False,
            allow=True,
            skip_git_pull=True,
            update_mode=True,
        )
        self.assertEqual(0, code)
        published_bytes = self.index.read_bytes()
        published_md5 = md5(self.index)
        self.assertNotEqual(self.legacy_md5, published_md5)

        backup_root = self.home / "backups/services" / self.slug
        stamp_dir = sorted(p for p in backup_root.iterdir() if p.is_dir())[-1]
        self.assertTrue((stamp_dir / "index.php").is_file())

        self.index.write_text("<?php echo 'perf-patched';", encoding="utf-8")
        pre_perf_bytes = published_bytes
        perf_backup = self.home / "backups/performance" / "test-stamp"
        rel = "uslugi/demo-service/index.php"
        (perf_backup / rel).parent.mkdir(parents=True, exist_ok=True)
        (perf_backup / rel).write_bytes(pre_perf_bytes)
        (perf_backup / "manifest.json").write_text(
            json.dumps(
                {
                    "files": [
                        {
                            "path": rel,
                            "existed": True,
                            "before": hashlib.sha256(pre_perf_bytes).hexdigest(),
                        }
                    ]
                }
            ),
            encoding="utf-8",
        )

        manifest = rb.load_rollback_manifest(stamp_dir)
        rb.rollback_performance_patch(self.docroot, perf_backup)
        self.assertEqual(published_md5, md5(self.index))
        rb.restore_service_publish(self.docroot, stamp_dir, manifest)
        self.assertEqual(self.legacy_md5, md5(self.index))


if __name__ == "__main__":
    unittest.main()
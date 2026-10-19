#!/usr/bin/env python3
"""Local tests for lireate_hosting_publish_service (no network)."""

import json
import os
import shutil
import sys
import tempfile
import unittest
from pathlib import Path
from unittest import mock

REPO_ROOT = Path(__file__).resolve().parents[1]
sys.path.insert(0, str(REPO_ROOT / "scripts"))

import lireate_hosting_publish_service as pub  # noqa: E402


class HostingPublishTests(unittest.TestCase):
    def setUp(self):
        self.tmp = Path(tempfile.mkdtemp(prefix="lireate-pub-"))
        self.office_repo = self.tmp / "office-repo"
        self.docroot = self.tmp / "docroot"
        self.home = self.tmp / "autopub"
        self.state_file = self.home / "state" / "services-published.json"
        slug = "demo-service"
        self.slug = slug
        service_dir = self.office_repo / "pages" / "services" / slug
        service_dir.mkdir(parents=True)
        (service_dir / "index.php").write_text("<?php echo 'ok';", encoding="utf-8")
        (service_dir / "publish.manifest.json").write_text(
            json.dumps({"slug": slug, "url_path": "uslugi/demo-service"}) + "\n",
            encoding="utf-8",
        )
        shared = self.office_repo / "pages" / "services" / "_shared"
        shared.mkdir(parents=True)
        (shared / "lireate-service-dark.css").write_text("body{}", encoding="utf-8")
        (shared / "lireate-contact.js").write_text("// test", encoding="utf-8")
        (shared / "lireate-messenger-urls.php").write_text(
            "<?php define('LIREATE_MAX_CHANNEL_URL', 'https://example.test');\n",
            encoding="utf-8",
        )
        (shared / "service-directory.htaccess").write_text(
            "DirectoryIndex index.php\n", encoding="utf-8"
        )
        self.docroot.mkdir(parents=True)

    def tearDown(self):
        shutil.rmtree(self.tmp, ignore_errors=True)

    def _publish(self, update=False, apply=True):
        os.environ["LIREATE_ALLOW_PUBLISH"] = "yes"
        return pub._publish_slug(
            self.slug,
            self.office_repo,
            self.docroot,
            "https://example.test",
            self.home,
            self.state_file,
            dry_run=not apply,
            allow=True,
            skip_git_pull=True,
            update_mode=update,
        )

    @mock.patch.object(pub, "_http_status", return_value=(200, None))
    def test_first_publish_writes_marker(self, _http):
        code = self._publish(apply=True)
        self.assertEqual(code, 0)
        dest = self.docroot / "uslugi" / "demo-service"
        self.assertTrue((dest / "index.php").is_file())
        self.assertTrue((dest / ".htaccess").is_file())
        self.assertIn("DirectoryIndex", (dest / ".htaccess").read_text(encoding="utf-8"))
        self.assertTrue((dest / ".lireate-managed.json").is_file())
        self.assertTrue((dest / "assets" / "lireate-shared" / "lireate-contact.js").is_file())
        self.assertTrue((self.docroot / "_shared" / "lireate-messenger-urls.php").is_file())
        self.assertTrue(self.state_file.is_file())
        state = json.loads(self.state_file.read_text(encoding="utf-8"))
        self.assertIn(self.slug, state)

    @mock.patch.object(pub, "_http_status", return_value=(404, None))
    def test_http_failure_does_not_write_json_state(self, _http):
        code = self._publish(apply=True)
        self.assertEqual(code, 1)
        dest = self.docroot / "uslugi" / "demo-service"
        self.assertTrue((dest / "index.php").is_file())
        self.assertFalse(self.state_file.is_file())

    @mock.patch.object(pub, "_http_status", return_value=(200, None))
    def test_foreign_file_blocks_full_publish(self, _http):
        dest = self.docroot / "uslugi" / "demo-service"
        dest.mkdir(parents=True)
        (dest / "index.php").write_text("legacy foreign", encoding="utf-8")
        code = self._publish(apply=True)
        self.assertEqual(code, 1)
        self.assertEqual((dest / "index.php").read_text(encoding="utf-8"), "legacy foreign")

    @mock.patch.object(pub, "_http_status", return_value=(200, None))
    def test_update_skips_foreign_but_updates_managed(self, _http):
        self._publish(apply=True)
        dest = self.docroot / "uslugi" / "demo-service"
        (dest / "foreign.txt").write_text("keep", encoding="utf-8")
        (self.office_repo / "pages" / "services" / self.slug / "index.php").write_text(
            "<?php echo 'v2';", encoding="utf-8"
        )
        code = self._publish(update=True, apply=True)
        self.assertEqual(code, 0)
        self.assertIn("v2", (dest / "index.php").read_text(encoding="utf-8"))
        self.assertEqual((dest / "foreign.txt").read_text(encoding="utf-8"), "keep")


if __name__ == "__main__":
    raise SystemExit(unittest.main())

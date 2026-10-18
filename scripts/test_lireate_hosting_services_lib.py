#!/usr/bin/env python3
"""Tests for lireate_hosting_services_lib."""

import json
import shutil
import sys
import tempfile
import unittest
from pathlib import Path

REPO_ROOT = Path(__file__).resolve().parents[1]
sys.path.insert(0, str(REPO_ROOT / "scripts"))

from lireate_hosting_services_lib import (  # noqa: E402
    collect_publish_candidates,
    load_state_txt,
    repair_service_htaccess,
    save_state_txt,
)


class HostingServicesLibTests(unittest.TestCase):
    def setUp(self):
        self.tmp = Path(tempfile.mkdtemp(prefix="lireate-lib-"))
        self.office_repo = self.tmp / "office-repo"
        self.docroot = self.tmp / "docroot"
        self.state_file = self.tmp / "state.txt"
        self.slug = "retry-me"
        service_dir = self.office_repo / "pages" / "services" / self.slug
        service_dir.mkdir(parents=True)
        (service_dir / "index.php").write_text("<?php", encoding="utf-8")
        (service_dir / "publish.manifest.json").write_text(
            json.dumps({"slug": self.slug, "url_path": "uslugi/retry-me"}) + "\n",
            encoding="utf-8",
        )
        shared = self.office_repo / "pages" / "services" / "_shared"
        shared.mkdir(parents=True)
        (shared / "service-directory.htaccess").write_text(
            "DirectoryIndex index.php\n", encoding="utf-8"
        )
        self.docroot.mkdir(parents=True)

    def tearDown(self):
        shutil.rmtree(self.tmp, ignore_errors=True)

    def test_partial_deploy_stays_candidate(self):
        dest = self.docroot / "uslugi" / "retry-me"
        dest.mkdir(parents=True)
        (dest / "index.php").write_text("orphan", encoding="utf-8")
        state = load_state_txt(self.state_file)
        candidates = collect_publish_candidates(self.office_repo, self.docroot, state)
        self.assertIn(self.slug, candidates)
        self.assertNotIn(self.slug, state)

    def test_repair_writes_htaccess(self):
        dest = self.docroot / "uslugi" / "retry-me"
        dest.mkdir(parents=True)
        (dest / "index.php").write_text("x", encoding="utf-8")
        fixed = repair_service_htaccess(self.office_repo, self.docroot, dry_run=False)
        self.assertEqual(fixed, ["uslugi/retry-me"])
        self.assertTrue((dest / ".htaccess").is_file())

    def test_in_state_not_candidate(self):
        save_state_txt(self.state_file, {self.slug})
        state = load_state_txt(self.state_file)
        candidates = collect_publish_candidates(self.office_repo, self.docroot, state)
        self.assertNotIn(self.slug, candidates)


if __name__ == "__main__":
    raise SystemExit(unittest.main())

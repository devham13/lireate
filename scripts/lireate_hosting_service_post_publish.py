#!/usr/bin/env python3
"""Re-apply sitewide overlays stripped by service publish --update.

After ``lireate_hosting_publish_service.py --update`` copies a repo ``index.php``,
standalone entry points lose head injections from PR #133 (sidebar v7) and the SEO
performance patch. This module re-runs both installers idempotently (dry-run
by default).
"""
from __future__ import print_function

import argparse
import os
import sys
from pathlib import Path

_SCRIPTS = Path(__file__).resolve().parent
if str(_SCRIPTS) not in sys.path:
    sys.path.insert(0, str(_SCRIPTS))


def _truthy(name):
    return os.environ.get(name, "").strip().lower() in ("1", "yes", "true", "on")


def apply_overlays(docroot, backup_root, apply=False, php_bin=None, office_repo=None):
    """Run sidebar v7 then performance patch (order required for stable heads)."""
    docroot = Path(docroot).expanduser().resolve()
    backup_root = Path(backup_root).expanduser().resolve()
    if not docroot.is_dir():
        raise ValueError("BLOCKER DOCROOT missing: {0}".format(docroot))
    if backup_root == docroot or docroot in backup_root.parents:
        raise ValueError("BLOCKER backup must be outside DOCROOT")
    office_repo = Path(office_repo or _SCRIPTS.parent).resolve()
    sidebar_backup = backup_root / "sidebar-v7"
    perf_backup = backup_root / "performance"
    php = php_bin or os.environ.get("LIREATE_PHP_BIN") or "php"

    import lireate_sitewide_sidebar_v7_install as sidebar_v7
    import lireate_hosting_performance_patch as perf_patch

    old_root = sidebar_v7.ROOT
    old_repo = perf_patch.REPO
    try:
        sidebar_v7.ROOT = office_repo
        perf_patch.REPO = office_repo
        sidebar_argv = [
            "--docroot", str(docroot),
            "--backup-root", str(sidebar_backup),
            "--php-bin", php,
        ]
        if apply:
            sidebar_argv.append("--apply")
        code = sidebar_v7.run(sidebar_argv)
        if code not in (0,):
            return code
        plan, notes = perf_patch.build_plan(docroot)
        for note in notes:
            print("WARN: " + note)
        print("post_publish perf: {0}, files={1}".format(perf_patch.VERSION, len(plan)))
        if not plan:
            print("STATUS: POST_PUBLISH_PERF_UP_TO_DATE")
        elif not apply:
            print("STATUS: POST_PUBLISH_PERF_DRY_RUN")
        else:
            backup = perf_patch.apply_plan(docroot, plan, perf_backup)
            print("STATUS: POST_PUBLISH_PERF_APPLIED backup={0}".format(backup))
    finally:
        sidebar_v7.ROOT = old_root
        perf_patch.REPO = old_repo
    if apply:
        print("STATUS: POST_PUBLISH_OVERLAY_APPLIED")
    else:
        print("STATUS: POST_PUBLISH_OVERLAY_DRY_RUN")
    return 0


def main(argv=None):
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--docroot", type=Path, required=True)
    parser.add_argument("--backup-root", type=Path, required=True)
    parser.add_argument("--office-repo", type=Path, default=None)
    parser.add_argument("--apply", action="store_true")
    parser.add_argument("--php-bin", default=None)
    args = parser.parse_args(argv)
    allow = _truthy("LIREATE_ALLOW_PUBLISH")
    if args.apply and not allow:
        print("BLOCKER: set LIREATE_ALLOW_PUBLISH=yes to apply")
        return 2
    return apply_overlays(
        args.docroot,
        args.backup_root,
        apply=args.apply,
        php_bin=args.php_bin,
        office_repo=args.office_repo,
    )


if __name__ == "__main__":
    raise SystemExit(main())
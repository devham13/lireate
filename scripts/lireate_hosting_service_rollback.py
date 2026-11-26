#!/usr/bin/env python3
"""Rollback published service tree from publish backup (+ optional performance patch)."""

import argparse
import json
import os
import sys
from pathlib import Path

SCRIPT_DIR = Path(__file__).resolve().parent
sys.path.insert(0, str(SCRIPT_DIR))

import lireate_hosting_service_rollback_lib as rb  # noqa: E402


def default_home():
    return Path(os.environ.get("LIREATE_AUTOPUB_HOME", str(Path.home() / "lireate-autopub")))


def cmd_rollback(args):
    home = Path(args.home or default_home()).expanduser()
    docroot = Path(args.docroot).expanduser().resolve()
    backup_dir = rb.backup_dir_for(home, args.slug, args.rollback.strip())
    if not backup_dir.is_dir():
        raise SystemExit("BLOCKER: backup not found: {0}".format(backup_dir))
    manifest = rb.load_rollback_manifest(backup_dir)
    if not manifest:
        raise SystemExit("BLOCKER: rollback-manifest.json missing in {0}".format(backup_dir))

    if args.perf_backup:
        perf_dir = Path(args.perf_backup).expanduser()
        if not perf_dir.is_dir():
            perf_parent = home / "backups" / "performance" / args.perf_backup.strip()
            perf_dir = perf_parent if perf_parent.is_dir() else perf_dir
        perf_restored, err = rb.rollback_performance_patch(docroot, perf_dir)
        if err:
            print("WARN: performance rollback: {0}".format(err))
        else:
            print("performance restored: {0} files".format(len(perf_restored)))

    restored, removed = rb.restore_service_publish(docroot, backup_dir, manifest)
    print("restored: {0}".format(len(restored)))
    for rel in removed:
        print("removed new: {0}".format(rel))
    print("STATUS: SERVICE_ROLLBACK_OK slug={0} stamp={1}".format(args.slug, args.rollback))
    return 0


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--slug", required=True)
    parser.add_argument("--rollback", required=True, metavar="UTCSTAMP")
    parser.add_argument("--docroot", required=True)
    parser.add_argument("--home", default="")
    parser.add_argument(
        "--perf-backup",
        default="",
        help="Path or stamp under ~/lireate-autopub/backups/performance/",
    )
    args = parser.parse_args()
    return cmd_rollback(args)


if __name__ == "__main__":
    raise SystemExit(main())
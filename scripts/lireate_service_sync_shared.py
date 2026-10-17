#!/usr/bin/env python3
"""Copy pages/services/_shared/* into each service's assets/lireate-shared/."""

import argparse
import hashlib
import shutil
import sys
from pathlib import Path

REPO_ROOT = Path(__file__).resolve().parents[1]
SHARED = REPO_ROOT / "pages" / "services" / "_shared"
SERVICES = REPO_ROOT / "pages" / "services"
SKIP_DIRS = {"_shared", "_template"}
DEST_REL = Path("assets") / "lireate-shared"


def _sha256(path):
    h = hashlib.sha256()
    with path.open("rb") as fh:
        for chunk in iter(lambda: fh.read(65536), b""):
            h.update(chunk)
    return h.hexdigest()


def sync_service(service_dir, dry_run=False):
    dest = service_dir / DEST_REL
    changed = []
    for src in sorted(SHARED.glob("*")):
        if not src.is_file() or src.name.startswith("."):
            continue
        if src.suffix.lower() not in (".css", ".js"):
            continue
        dst = dest / src.name
        if dst.is_file() and _sha256(src) == _sha256(dst):
            continue
        changed.append(dst.relative_to(service_dir))
        if not dry_run:
            dest.mkdir(parents=True, exist_ok=True)
            shutil.copy2(str(src), str(dst))
    return changed


def main():
    parser = argparse.ArgumentParser(description="Sync LIREATE service shared assets")
    parser.add_argument("--slug", help="Only sync one service slug")
    parser.add_argument("--dry-run", action="store_true")
    args = parser.parse_args()

    if not SHARED.is_dir():
        print("BLOCKER: missing pages/services/_shared/")
        return 2

    targets = []
    if args.slug:
        d = SERVICES / args.slug
        if not d.is_dir():
            print("BLOCKER: unknown slug {0}".format(args.slug))
            return 2
        targets.append(d)
    else:
        for child in sorted(SERVICES.iterdir()):
            if not child.is_dir() or child.name in SKIP_DIRS:
                continue
            if (child / "index.php").is_file() or (child / "index.html").is_file():
                targets.append(child)

    total = 0
    for service_dir in targets:
        changed = sync_service(service_dir, dry_run=args.dry_run)
        if changed:
            print("{0}: {1}".format(service_dir.name, ", ".join(str(c) for c in changed)))
            total += len(changed)
    if args.dry_run and total:
        print("dry-run: would update {0} file(s)".format(total))
    return 0


if __name__ == "__main__":
    raise SystemExit(main())

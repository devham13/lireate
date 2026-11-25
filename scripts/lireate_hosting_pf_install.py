#!/usr/bin/env python3
"""Install hosting portfolio/reviews/uslugi package into DOCROOT (dry-run + backup)."""

import argparse
import shutil
import sys
from datetime import datetime
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
PKG = ROOT / 'packages' / 'lireate-hosting-pf-20261125'

COPY_PLAN = (
    ('portfolio.php', Path('portfolio.php')),
    ('reviews.php', Path('reviews.php')),
    ('portfolio', Path('portfolio')),
    ('uslugi', Path('template/page/uslugi')),
)


def backup_path(docroot: Path, rel: Path) -> Path:
    stamp = datetime.utcnow().strftime('%Y%m%dT%H%M%SZ')
    return docroot / '.lireate-backup-pf' / stamp / rel


def plan(docroot: Path):
    rows = []
    for src_name, rel in COPY_PLAN:
        src = PKG / src_name
        dst = docroot / rel
        rows.append((src, dst))
    return rows


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--docroot', type=Path, required=True)
    parser.add_argument('--apply', action='store_true')
    args = parser.parse_args()
    docroot = args.docroot.resolve()
    exit_code = 0
    for src, dst in plan(docroot):
        if not src.exists():
            print('BLOCKER: missing package file {0}'.format(src.relative_to(ROOT)))
            return 2
        print('{0} -> {1}'.format(src.relative_to(ROOT), dst))
    if not args.apply:
        print('STATUS: PF_INSTALL_DRY_RUN')
        return 0
    for src, dst in plan(docroot):
        if dst.exists():
            bak = backup_path(docroot, dst.relative_to(docroot))
            bak.parent.mkdir(parents=True, exist_ok=True)
            if dst.is_dir():
                if bak.exists():
                    shutil.rmtree(bak)
                shutil.copytree(dst, bak)
            else:
                shutil.copy2(dst, bak)
            print('backup: {0}'.format(bak))
        dst.parent.mkdir(parents=True, exist_ok=True)
        if src.is_dir():
            if dst.exists():
                shutil.rmtree(dst)
            shutil.copytree(src, dst)
        else:
            shutil.copy2(src, dst)
    print('STATUS: PF_INSTALL_APPLIED')
    return exit_code


if __name__ == '__main__':
    sys.exit(main())
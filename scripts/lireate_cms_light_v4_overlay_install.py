#!/usr/bin/env python3
"""Copy template/css|js lireate-light-v4.* to docroot — CMS overlay assets (dry-run default)."""
import argparse
import shutil
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
ASSETS = (
    Path('template/css/lireate-light-v4.css'),
    Path('template/js/lireate-light-v4.js'),
)


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--docroot', type=Path, required=True)
    parser.add_argument('--apply', action='store_true')
    args = parser.parse_args()
    docroot = args.docroot.resolve()
    plan = []
    for rel in ASSETS:
        src = ROOT / rel
        if not src.is_file():
            print('BLOCKER: missing {0}'.format(rel))
            return 2
        dst = docroot / rel
        plan.append((src, dst))
    for src, dst in plan:
        print('{0} -> {1}'.format(src.relative_to(ROOT), dst))
        if args.apply:
            dst.parent.mkdir(parents=True, exist_ok=True)
            shutil.copy2(src, dst)
    print('STATUS: CMS_OVERLAY_{0}'.format('APPLIED' if args.apply else 'DRY_RUN'))
    return 0


if __name__ == '__main__':
    sys.exit(main())
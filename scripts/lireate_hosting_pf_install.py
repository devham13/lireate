#!/usr/bin/env python3
"""Install portfolio/reviews/uslugi Light V4 templates into DOCROOT/template/page (not site root)."""

import argparse
import shutil
import sys
from datetime import datetime, timezone
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
PKG = ROOT / 'packages' / 'lireate-hosting-pf-20261125'

REPO_FILES = (
    Path('template/page/portfolio.php'),
    Path('template/page/reviews.php'),
    Path('template/page/lireate-portfolio-cms.inc.php'),
    Path('template/page/lireate-reviews-cms.inc.php'),
    Path('template/page/lireate-cms-db.inc.php'),
    Path('pages/_shared/lireate-cms-light-v4-head.inc.php'),
    Path('template/css/lireate-portfolio-light-v4.css'),
    Path('template/css/lireate-reviews-light-v4.css'),
    Path('template/css/lireate-cms-pages-light-v4.css'),
)

PORTFOLIO_CASE_REL = Path('template/page/portfolio')
USLUGI_REL = Path('template/page/uslugi')
USLUGI_PKG_FILES = (
    'index.php',
    'lireate-uslugi-cms.inc.php',
    'lireate-uslugi-lib.php',
    'lireate-uslugi.css',
)


def backup_root(docroot):
    return docroot / '.lireate-backup-pf'


def make_backup_id():
    return datetime.now(timezone.utc).strftime('%Y%m%dT%H%M%SZ')


def plan_install(docroot):
    rows = []
    for rel in REPO_FILES:
        src = ROOT / rel
        if not src.is_file():
            return None, 'BLOCKER: missing {0}'.format(rel)
        rows.append((src, docroot / rel))

    pf_dir = ROOT / PORTFOLIO_CASE_REL
    if pf_dir.is_dir():
        for path in sorted(pf_dir.glob('*.php')):
            rel = PORTFOLIO_CASE_REL / path.name
            rows.append((path, docroot / rel))

    uslugi_pkg = PKG / 'uslugi'
    for name in USLUGI_PKG_FILES:
        src = uslugi_pkg / name
        if not src.is_file():
            return None, 'BLOCKER: missing package uslugi/{0}'.format(name)
        rows.append((src, docroot / USLUGI_REL / name))

    return rows, None


def cmd_apply(docroot, backups_root):
    plan, err = plan_install(docroot)
    if err:
        print(err)
        return 2
    backup_id = make_backup_id()
    backup_dir = backups_root / backup_id
    manifest = []
    for _src, dst in plan:
        if dst.is_file():
            rel = dst.relative_to(docroot)
            bak = backup_dir / rel
            bak.parent.mkdir(parents=True, exist_ok=True)
            if dst.is_file():
                shutil.copy2(dst, bak)
                manifest.append(str(rel))
    backup_dir.mkdir(parents=True, exist_ok=True)
    (backup_dir / 'manifest.txt').write_text('\n'.join(manifest) + '\n', encoding='utf-8')

    for src, dst in plan:
        dst.parent.mkdir(parents=True, exist_ok=True)
        shutil.copy2(src, dst)
        print('{0} -> {1}'.format(src.relative_to(ROOT), dst))

    print('backup: {0}'.format(backup_dir))
    print('rollback: python3 scripts/lireate_hosting_pf_install.py --docroot ... --rollback --backup-id {0}'.format(backup_id))
    print('STATUS: PF_INSTALL_APPLIED')
    return 0


def cmd_rollback(docroot, backups_root, backup_id):
    if not backup_id:
        print('BLOCKER: pass --backup-id')
        return 2
    backup_dir = backups_root / backup_id
    manifest = backup_dir / 'manifest.txt'
    if not manifest.is_file():
        print('BLOCKER: manifest missing: {0}'.format(manifest))
        return 2
    for line in manifest.read_text(encoding='utf-8').splitlines():
        rel = line.strip()
        if rel == '':
            continue
        src = backup_dir / rel
        dst = docroot / rel
        if not src.is_file():
            print('WARN: skip missing backup {0}'.format(rel))
            continue
        dst.parent.mkdir(parents=True, exist_ok=True)
        shutil.copy2(src, dst)
        print('restored {0}'.format(rel))
    print('STATUS: PF_INSTALL_ROLLBACK_OK')
    return 0


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--docroot', type=Path, required=True)
    parser.add_argument('--apply', action='store_true')
    parser.add_argument('--rollback', action='store_true')
    parser.add_argument('--backup-id', default='')
    parser.add_argument('--backups-root', type=Path, default=None)
    args = parser.parse_args()
    docroot = args.docroot.resolve()
    backups_root = args.backups_root
    if backups_root is None:
        backups_root = backup_root(docroot)
    else:
        backups_root = backups_root.resolve()

    if args.rollback:
        return cmd_rollback(docroot, backups_root, args.backup_id.strip())

    plan, err = plan_install(docroot)
    if err:
        print(err)
        return 2
    for src, dst in plan:
        print('{0} -> {1}'.format(src.relative_to(ROOT), dst))
    if not args.apply:
        print('backup dir on apply: {0}/<UTCSTAMP>/manifest.txt'.format(backups_root))
        print('STATUS: PF_INSTALL_DRY_RUN')
        return 0
    return cmd_apply(docroot, backups_root)


if __name__ == '__main__':
    sys.exit(main())
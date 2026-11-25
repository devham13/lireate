#!/usr/bin/env python3
"""Copy Light V4 CMS overlay assets to docroot (dry-run default, backup + rollback)."""
import argparse
import shutil
import sys
from datetime import datetime, timezone
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]

SINGLE_FILES = (
    Path('template/css/lireate-light-v4.css'),
    Path('template/js/lireate-light-v4.js'),
    Path('template/css/lireate-cms-pages-light-v4.css'),
    Path('template/css/lireate-home-light-v4.css'),
    Path('template/css/lireate-home-preview-chrome.css'),
    Path('template/css/lireate-portfolio-light-v4.css'),
    Path('template/css/lireate-reviews-light-v4.css'),
    Path('template/page/lireate-portfolio-cms.inc.php'),
    Path('template/page/lireate-reviews-cms.inc.php'),
    Path('pages/_shared/lireate-cms-light-v4-head.inc.php'),
)

DIR_TREES = (
    Path('template/page/team'),
    Path('template/page/o_kompanii'),
    Path('template/page/partners'),
    Path('template/page/docs'),
    Path('template/page/kontaktyi'),
)

SHARED_PAGE_SECTIONS = (
    'team',
    'o_kompanii',
    'partners',
    'docs',
    'kontaktyi',
)


def backup_root(docroot):
    return docroot / '.lireate-backup-cms-overlay'


def make_backup_id():
    return datetime.now(timezone.utc).strftime('%Y%m%dT%H%M%SZ')


def iter_overlay_files(repo):
    for rel in SINGLE_FILES:
        src = repo / rel
        if not src.is_file():
            yield None, rel, 'BLOCKER: missing {0}'.format(rel)
            return
        yield src, rel, None
    for rel_dir in DIR_TREES:
        base = repo / rel_dir
        if not base.is_dir():
            yield None, rel_dir, 'BLOCKER: missing dir {0}'.format(rel_dir)
            return
        for path in sorted(base.rglob('*')):
            if path.is_file():
                yield path, path.relative_to(repo), None
    for section in SHARED_PAGE_SECTIONS:
        shared = repo / 'pages' / section / '_shared'
        if not shared.is_dir():
            continue
        for path in sorted(shared.iterdir()):
            if path.is_file() and path.suffix in ('.php', '.css', '.js'):
                rel_tpl = Path('template/page') / section / path.name
                rel_shared = Path('pages') / section / '_shared' / path.name
                yield path, rel_tpl, None
                yield path, rel_shared, None


def plan_install(docroot):
    plan = []
    for src, rel, err in iter_overlay_files(ROOT):
        if err:
            return None, err
        plan.append((src, docroot / rel))
    return plan, None


def cmd_apply(docroot, backups_root):
    plan, err = plan_install(docroot)
    if err:
        print(err)
        return 2
    backup_id = make_backup_id()
    backup_dir = backups_root / backup_id
    backup_dir.mkdir(parents=True, exist_ok=True)
    manifest = []
    for _src, dst in plan:
        if dst.is_file():
            rel = dst.relative_to(docroot)
            bak = backup_dir / rel
            bak.parent.mkdir(parents=True, exist_ok=True)
            shutil.copy2(dst, bak)
            manifest.append(str(rel))
    (backup_dir / 'manifest.txt').write_text('\n'.join(manifest) + '\n', encoding='utf-8')
    for src, dst in plan:
        dst.parent.mkdir(parents=True, exist_ok=True)
        shutil.copy2(src, dst)
        print('{0} -> {1}'.format(src.relative_to(ROOT), dst))
    print('backup: {0}'.format(backup_dir))
    print('STATUS: CMS_OVERLAY_APPLIED')
    return 0


def cmd_rollback(docroot, backups_root, backup_id):
    if not backup_id:
        print('BLOCKER: pass --backup-id from backup directory name')
        return 2
    backup_dir = backups_root / backup_id
    if not backup_dir.is_dir():
        print('BLOCKER: backup not found: {0}'.format(backup_dir))
        return 2
    manifest = backup_dir / 'manifest.txt'
    if not manifest.is_file():
        print('BLOCKER: manifest.txt missing in backup')
        return 2
    for line in manifest.read_text(encoding='utf-8').splitlines():
        rel = line.strip()
        if rel == '':
            continue
        src = backup_dir / rel
        dst = docroot / rel
        if not src.is_file():
            print('WARN: skip missing backup file {0}'.format(rel))
            continue
        dst.parent.mkdir(parents=True, exist_ok=True)
        shutil.copy2(src, dst)
        print('restored {0}'.format(rel))
    print('STATUS: CMS_OVERLAY_ROLLBACK_OK')
    return 0


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--docroot', type=Path, required=True)
    parser.add_argument('--apply', action='store_true')
    parser.add_argument('--rollback', action='store_true')
    parser.add_argument('--backup-id', default='')
    parser.add_argument(
        '--backups-root',
        type=Path,
        default=None,
        help='default: DOCROOT/.lireate-backup-cms-overlay',
    )
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
    if args.apply:
        return cmd_apply(docroot, backups_root)
    print('backup dir on apply: {0}/<UTCSTAMP>/'.format(backups_root))
    print('rollback: python3 scripts/lireate_cms_light_v4_overlay_install.py --docroot ... --rollback --backup-id <UTCSTAMP>')
    print('STATUS: CMS_OVERLAY_DRY_RUN')
    return 0


if __name__ == '__main__':
    sys.exit(main())
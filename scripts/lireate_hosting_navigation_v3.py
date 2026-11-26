#!/usr/bin/env python3
"""Install LIREATE Dual Navigation V3 in the existing PHP CMS.

Dry run by default; --apply copies CSS/JS and adds one global <head> include.
Backups stay outside DOCROOT. Never changes data/db.php, links, forms or analytics.
"""
import argparse
import datetime
import json
import os
from pathlib import Path
import re
import shutil
import subprocess
import tempfile

REPO = Path(__file__).resolve().parents[1]
VERSION = 'lireate-dual-nav-v3-20261126'
NAV_ASSET_QS = '20261126r5'
ASSETS = [
    Path('template/css/lireate-navigation-dual-v3.css'),
    Path('template/css/lireate-public-cms-chrome.css'),
    Path('template/css/lireate-global-subnav-light-v4.css'),
    Path('template/js/lireate-navigation-dual-v3.js'),
]
MAIN = Path('template/main.php')
HEAD = (
    '<!-- ' + VERSION + ' -->\n'
    '<link rel="stylesheet" href="/template/css/lireate-navigation-dual-v3.css?v=' + NAV_ASSET_QS + '">\n'
    '<link rel="stylesheet" href="/template/css/lireate-public-cms-chrome.css?v=' + NAV_ASSET_QS + '">\n'
    '<link rel="stylesheet" href="/template/css/lireate-global-subnav-light-v4.css?v=' + NAV_ASSET_QS + '">\n'
    '<script defer src="/template/js/lireate-navigation-dual-v3.js?v=' + NAV_ASSET_QS + '"></script>\n'
)


def safe_path(docroot, rel):
    result = docroot / rel
    if result.is_symlink():
        raise ValueError('Symlinks are not supported: ' + str(result))
    if result.exists() and not result.is_file():
        raise ValueError('Not a regular file: ' + str(result))
    return result


def build_plan(docroot):
    main = safe_path(docroot, MAIN)
    if not main.is_file():
        raise ValueError('BLOCKER: CMS template/main.php not found')
    original = main.read_text(encoding='utf-8')
    if VERSION in original:
        changed = original
    else:
        if len(re.findall(r'</head\s*>', original, flags=re.I)) != 1:
            raise ValueError('BLOCKER: expected one </head> in CMS template/main.php')
        changed = re.sub(r'</head\s*>', lambda m: HEAD + m.group(0),
                         original, count=1, flags=re.I)
    plan = {MAIN: changed.encode('utf-8')}
    for rel in ASSETS:
        src = REPO / rel
        if not src.is_file():
            raise ValueError('BLOCKER: missing source asset ' + str(src))
        plan[rel] = src.read_bytes()
    return {rel: data for rel, data in plan.items()
            if not safe_path(docroot, rel).is_file()
            or safe_path(docroot, rel).read_bytes() != data}


def atomic_write(path, data):
    path.parent.mkdir(parents=True, exist_ok=True)
    fd, tmp = tempfile.mkstemp(prefix='.ln3-', dir=str(path.parent))
    try:
        with os.fdopen(fd, 'wb') as f:
            f.write(data)
        os.chmod(tmp, path.stat().st_mode & 0o777 if path.exists() else 0o644)
        os.replace(tmp, str(path))
    finally:
        if os.path.exists(tmp):
            os.unlink(tmp)


def rollback(docroot, backup):
    manifest = json.loads((backup / 'manifest.json').read_text(encoding='utf-8'))
    for entry in manifest['files']:
        rel = Path(entry['path'])
        if rel not in ASSETS + [MAIN]:
            raise ValueError('BLOCKER: unexpected manifest path ' + str(rel))
        dest = safe_path(docroot, rel)
        if entry['existed']:
            atomic_write(dest, (backup / rel).read_bytes())
        elif dest.exists():
            dest.unlink()
    print('ROLLED_BACK', backup)


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--docroot', required=True)
    parser.add_argument('--backup-root', help='must be outside DOCROOT')
    parser.add_argument('--apply', action='store_true')
    parser.add_argument('--php-bin', default=os.environ.get('LIREATE_PHP_BIN', 'php'))
    parser.add_argument('--rollback', help='path to a previous backup directory')
    args = parser.parse_args()
    docroot = Path(args.docroot).expanduser().resolve()
    backups = (Path(args.backup_root).expanduser().resolve()
               if args.backup_root else docroot.parent / 'lireate-nav-backups')
    if docroot == backups or docroot in backups.parents:
        raise ValueError('BLOCKER: backups must be outside DOCROOT')
    if args.rollback:
        rollback(docroot, Path(args.rollback).expanduser().resolve())
        return 0
    plan = build_plan(docroot)
    for rel in plan:
        print(('APPLY' if args.apply else 'DRY_RUN'), rel)
    if not plan:
        print('NO_CHANGES')
        return 0
    if not args.apply:
        return 0
    stamp = datetime.datetime.now(datetime.timezone.utc).strftime('%Y%m%dT%H%M%SZ')
    backup = backups / stamp
    backup.mkdir(parents=True, exist_ok=False)
    manifest = {'version': VERSION, 'files': []}
    for rel in plan:
        target = safe_path(docroot, rel)
        existed = target.is_file()
        manifest['files'].append({'path': str(rel), 'existed': existed})
        if existed:
            dest = backup / rel
            dest.parent.mkdir(parents=True, exist_ok=True)
            shutil.copy2(str(target), str(dest))
    (backup / 'manifest.json').write_text(
        json.dumps(manifest, ensure_ascii=False, indent=2), encoding='utf-8')
    try:
        for rel, data in plan.items():
            atomic_write(safe_path(docroot, rel), data)
        php = shutil.which(args.php_bin) or (
            args.php_bin if Path(args.php_bin).is_file() else None)
        if not php:
            raise RuntimeError('PHP binary not found; specify --php-bin')
        result = subprocess.run([php, '-l', str(docroot / MAIN)],
                                capture_output=True, text=True)
        if result.returncode != 0:
            raise RuntimeError('PHP lint failed: ' + result.stdout + result.stderr)
    except Exception:
        rollback(docroot, backup)
        raise
    print('APPLIED backup=' + str(backup))
    return 0


if __name__ == '__main__':
    raise SystemExit(main())

#!/usr/bin/env python3
"""
План выкладки Light V4 для страниц услуг на REG.RU (только хостинг, не Cloud).

После каждой услуги: lireate_hosting_performance_patch.py (seo-perf-20261120-v1).
"""
from __future__ import annotations

import argparse
import subprocess
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
EXCLUDE = frozenset({'registraciya-tovarnogo-znaka-s-oplatoy-za-rezultat'})


def service_slugs():
    base = ROOT / 'pages' / 'services'
    slugs = []
    for path in sorted(base.iterdir()):
        if not path.is_dir() or path.name.startswith('_'):
            continue
        if path.name in EXCLUDE:
            continue
        if not (path / 'publish.manifest.json').is_file():
            continue
        slugs.append(path.name)
    return slugs


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--docroot', type=Path, required=True)
    parser.add_argument('--office-repo', type=Path, default=ROOT)
    parser.add_argument('--backup-root', type=Path, default=Path.home() / 'lireate-autopub' / 'backups')
    parser.add_argument('--apply', action='store_true', help='run publish + perf patch (hosting only)')
    args = parser.parse_args()
    docroot = args.docroot.expanduser().resolve()
    office = args.office_repo.expanduser().resolve()
    publish = office / 'scripts' / 'lireate_hosting_publish_service.py'
    perf = office / 'scripts' / 'lireate_hosting_performance_patch.py'
    slugs = service_slugs()
    print('services: {0} (exclude: {1})'.format(len(slugs), ','.join(sorted(EXCLUDE))))
    for slug in slugs:
        cmd_pub = [
            sys.executable,
            str(publish),
            '--slug',
            slug,
            '--update',
            '--apply',
            '--skip-git-pull',
            '--docroot',
            str(docroot),
            '--office-repo',
            str(office),
        ]
        cmd_perf = [
            sys.executable,
            str(perf),
            '--docroot',
            str(docroot),
            '--backup-root',
            str(args.backup_root),
            '--apply',
        ]
        print('')
        print('=== {0} ==='.format(slug))
        print(' '.join(cmd_pub))
        print(' '.join(cmd_perf))
        if args.apply:
            subprocess.check_call(cmd_pub)
            subprocess.check_call(cmd_perf)
    print('')
    print('STATUS: LIGHT_V4_SERVICES_ROLLOUT_PLAN_OK')
    return 0


if __name__ == '__main__':
    raise SystemExit(main())
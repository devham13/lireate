#!/usr/bin/env python3
"""
План выкладки Light V4 на REG.RU: услуги + напоминание про blog/home (main.php).

Услуги: publish_service через LIREATE_DOCROOT (env), dry-run = --update без --apply.
"""
from __future__ import annotations

import argparse
import os
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


def publish_env(docroot, office, apply):
    env = os.environ.copy()
    env['LIREATE_DOCROOT'] = str(docroot)
    env['LIREATE_OFFICE_REPO'] = str(office)
    if apply:
        env['LIREATE_ALLOW_PUBLISH'] = 'yes'
    return env


def run_publish(publish, slug, env, apply):
    cmd = [
        sys.executable,
        str(publish),
        '--slug',
        slug,
        '--update',
        '--skip-git-pull',
    ]
    if apply:
        cmd.append('--apply')
    proc = subprocess.run(cmd, env=env, capture_output=True, text=True)
    if proc.returncode != 0:
        sys.stderr.write(proc.stdout)
        sys.stderr.write(proc.stderr)
        raise subprocess.CalledProcessError(proc.returncode, cmd)
    return proc.stdout


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--docroot', type=Path, required=True)
    parser.add_argument('--office-repo', type=Path, default=ROOT)
    parser.add_argument('--backup-root', type=Path, default=Path.home() / 'lireate-autopub' / 'backups')
    parser.add_argument('--apply', action='store_true', help='publish + perf patch (hosting only)')
    args = parser.parse_args()
    docroot = args.docroot.expanduser().resolve()
    if not docroot.is_dir():
        print('BLOCKER: docroot is not a directory: {0}'.format(docroot))
        return 2
    office = args.office_repo.expanduser().resolve()
    publish = office / 'scripts' / 'lireate_hosting_publish_service.py'
    perf = office / 'scripts' / 'lireate_hosting_performance_patch.py'
    slugs = service_slugs()
    env = publish_env(docroot, office, args.apply)

    print('=== ORDER (main.php) ===')
    print('1) lireate_blog_install.py --check|--apply  (news templates, not main.php)')
    print('2) lireate_home_light_v4_install.py --check|--apply  (main.php home)')
    print('3) lireate_cms_light_v4_overlay_install.py / lireate_hosting_pf_install.py')
    print('')
    print('=== BLOG (dry-run) ===')
    print('python3 scripts/lireate_blog_install.py --check --docroot {0}'.format(docroot))
    print('')
    print('services: {0} (exclude: {1})'.format(len(slugs), ','.join(sorted(EXCLUDE))))

    for slug in slugs:
        print('')
        print('=== {0} ==='.format(slug))
        try:
            out = run_publish(publish, slug, env, args.apply)
            if out.strip():
                print(out.rstrip())
            if args.apply:
                subprocess.check_call(
                    [
                        sys.executable,
                        str(perf),
                        '--docroot',
                        str(docroot),
                        '--backup-root',
                        str(args.backup_root),
                        '--apply',
                    ],
                    env=env,
                )
        except subprocess.CalledProcessError:
            print('BLOCKER: publish failed for slug {0}'.format(slug))
            return 1
    print('')
    print('STATUS: LIGHT_V4_SERVICES_ROLLOUT_{0}'.format('APPLIED' if args.apply else 'DRY_RUN_OK'))
    return 0


if __name__ == '__main__':
    raise SystemExit(main())
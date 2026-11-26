#!/usr/bin/env python3
"""
План выкладки Light V4 на REG.RU: услуги + напоминание про blog/home (main.php).

Услуги: publish_service через LIREATE_DOCROOT (env), dry-run = --update без --apply.
"""
import argparse
import os
import re
import subprocess
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
# Rollout: 20 slugs (s-oplatoy вне menu/publish по политике). CI render — все 21 manifest.
EXCLUDE = frozenset({'registraciya-tovarnogo-znaka-s-oplatoy-za-rezultat'})
BACKUP_STAMP_RE = re.compile(r"backup:\s*(\S+)")
PERF_BACKUP_RE = re.compile(r"PERFORMANCE_PATCH_APPLIED;\s*backup:\s*(\S+)")


def service_slugs():
    only = os.environ.get('LIREATE_ROLLOUT_SLUGS', '').strip()
    if only:
        return [s.strip() for s in only.split(',') if s.strip()]
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


def parse_backup_stamp(stdout):
    for match in BACKUP_STAMP_RE.finditer(stdout or ''):
        stamp = Path(match.group(1)).name
        if stamp:
            return stamp
    return ''


def parse_perf_backup_ref(stdout):
    match = PERF_BACKUP_RE.search(stdout or '')
    if not match:
        return ''
    return match.group(1).strip()


def _exc_output(exc):
    if isinstance(exc, subprocess.CalledProcessError):
        out = getattr(exc, 'output', None)
        if out is None:
            out = getattr(exc, 'stdout', None)
        err = getattr(exc, 'stderr', None) or ''
        return (out or '') + (err or '')
    return ''


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
    proc = subprocess.run(
        cmd,
        env=env,
        stdout=subprocess.PIPE,
        stderr=subprocess.PIPE,
        universal_newlines=True,
    )
    combined = (proc.stdout or '') + '\n' + (proc.stderr or '')
    if proc.returncode != 0:
        sys.stderr.write(proc.stdout or '')
        sys.stderr.write(proc.stderr or '')
        raise subprocess.CalledProcessError(
            proc.returncode, cmd, output=proc.stdout, stderr=proc.stderr
        )
    return combined


def rollback_slug(office, docroot, autopub_home, slug, pub_stamp, perf_stamp=''):
    rollback = office / 'scripts' / 'lireate_hosting_service_rollback.py'
    if not rollback.is_file():
        print('WARN: rollback script missing, cannot unwind partial rollout')
        return
    if not pub_stamp:
        print('WARN: no backup stamp for slug {0}, skip rollback'.format(slug))
        return
    print('ROLLBACK slug={0} publish={1} perf={2}'.format(slug, pub_stamp, perf_stamp or '-'))
    cmd = [
        sys.executable,
        str(rollback),
        '--docroot',
        str(docroot),
        '--slug',
        slug,
        '--rollback',
        pub_stamp,
        '--home',
        str(autopub_home),
    ]
    if perf_stamp:
        cmd.extend(['--perf-backup', perf_stamp])
    subprocess.run(cmd, check=False)


def rollback_applied(office, docroot, autopub_home, applied):
    for entry in reversed(applied):
        if len(entry) == 2:
            slug, pub_stamp, perf_stamp = entry[0], entry[1], ''
        else:
            slug, pub_stamp, perf_stamp = entry
        rollback_slug(office, docroot, autopub_home, slug, pub_stamp, perf_stamp)


def unwind_partial(office, docroot, autopub_home, applied, failed_slug, failed_pub='', failed_perf=''):
    if applied:
        print('=== ROLLBACK partial rollout ({0} prior slug(s)) ==='.format(len(applied)))
    rollback_applied(office, docroot, autopub_home, applied)
    if failed_pub:
        rollback_slug(office, docroot, autopub_home, failed_slug, failed_pub, failed_perf or '')


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
    autopub_home = Path(os.environ.get('LIREATE_AUTOPUB_HOME', str(Path.home() / 'lireate-autopub')))

    print('=== ORDER (hosting docroot) ===')
    print('1) lireate_cms_light_v4_overlay_install.py --check|--apply')
    print('2) lireate_hosting_pf_install.py --check|--apply  (template/page only; no uslugi/)')
    print('3) lireate_blog_install.py --check|--apply  (/news templates, not main.php)')
    print('4) lireate_home_light_v4_install.py --check|--apply  (main.php home)')
    print('5) lireate_light_v4_hosting_rollout.py --docroot ... [--apply]  (published services)')
    print('Env: LIREATE_PUBLIC_SITE_URL (HTTP verify for blog/home), LIREATE_DOCROOT, LIREATE_OFFICE_REPO')
    print('')
    print('=== OVERLAY (dry-run) ===')
    print('python3 scripts/lireate_cms_light_v4_overlay_install.py --docroot {0}'.format(docroot))
    print('')
    print('=== PF (dry-run) ===')
    print('python3 scripts/lireate_hosting_pf_install.py --docroot {0}'.format(docroot))
    print('')
    print('=== BLOG (dry-run) ===')
    print('python3 scripts/lireate_blog_install.py --check')
    print('')
    print('services: {0} (exclude: {1})'.format(len(slugs), ','.join(sorted(EXCLUDE))))

    print('')
    print('=== PREFLIGHT (all slugs, no --apply) ===')
    for slug in slugs:
        try:
            run_publish(publish, slug, publish_env(docroot, office, False), False)
        except subprocess.CalledProcessError:
            print('BLOCKER: preflight publish plan failed for slug {0}'.format(slug))
            return 1

    if not args.apply:
        for slug in slugs:
            print('')
            print('=== {0} ==='.format(slug))
            try:
                out = run_publish(publish, slug, env, False)
                if out.strip():
                    print(out.rstrip())
            except subprocess.CalledProcessError:
                print('BLOCKER: publish plan failed for slug {0}'.format(slug))
                return 1
    else:
        applied = []
        for slug in slugs:
            print('')
            print('=== APPLY {0} ==='.format(slug))
            pub_stamp = ''
            perf_stamp = ''
            try:
                out = run_publish(publish, slug, env, True)
                if out.strip():
                    print(out.rstrip())
                pub_stamp = parse_backup_stamp(out)
                perf_proc = subprocess.run(
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
                    stdout=subprocess.PIPE,
                    stderr=subprocess.PIPE,
                    universal_newlines=True,
                )
                perf_out = (perf_proc.stdout or '') + '\n' + (perf_proc.stderr or '')
                if perf_proc.returncode != 0:
                    sys.stderr.write(perf_proc.stdout or '')
                    sys.stderr.write(perf_proc.stderr or '')
                    raise subprocess.CalledProcessError(
                        perf_proc.returncode,
                        perf_proc.args,
                        output=perf_proc.stdout,
                        stderr=perf_proc.stderr,
                    )
                if perf_out.strip():
                    print(perf_out.rstrip())
                perf_stamp = parse_perf_backup_ref(perf_out)
                applied.append((slug, pub_stamp, perf_stamp))
            except (subprocess.CalledProcessError, OSError) as exc:
                extra = _exc_output(exc)
                if not pub_stamp:
                    pub_stamp = parse_backup_stamp(extra)
                if not perf_stamp:
                    perf_stamp = parse_perf_backup_ref(extra)
                print('BLOCKER: publish failed for slug {0}: {1}'.format(slug, exc))
                unwind_partial(office, docroot, autopub_home, applied, slug, pub_stamp, perf_stamp)
                return 1

    print('')
    print('STATUS: LIGHT_V4_SERVICES_ROLLOUT_{0}'.format('APPLIED' if args.apply else 'DRY_RUN_OK'))
    return 0


if __name__ == '__main__':
    raise SystemExit(main())
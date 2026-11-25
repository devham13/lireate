#!/usr/bin/env python3
"""LIREATE Light V4 — preview-safe batch migration of existing service entrypoints.

Dry-run by default. Only touches body class and asset <link>/<script> in index.php.
Do not invoke from hosting CRON. Review diff and test before any merge.
"""
import argparse
import re
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
SERVICES = ROOT / "pages" / "services"
CSS = """  <link rel="stylesheet" href="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-light-v4.css', $lx_service_asset_dir); ?>"><!-- lireate_light_v4 -->\n"""
JS = """<script src="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-light-v4.js', $lx_service_asset_dir); ?>"></script><!-- lireate_light_v4 -->\n"""
BODY_RE = re.compile(r'(<body\b[^>]*\bclass=")([^"]*)(")', re.I)


def stage(index):
    original = index.read_text(encoding="utf-8")
    if 'lireate-theme-light-v4' in original:
        return 'ready', original
    if 'lireate_service_asset_href' not in original or '</head>' not in original or '</body>' not in original:
        return 'BLOCKER: unknown service shell', original
    match = BODY_RE.search(original)
    if not match or 'lireate-svc-body' not in match.group(2):
        return 'BLOCKER: unrecognised <body> classes', original
    updated = BODY_RE.sub(lambda m: m.group(1) + m.group(2) + ' lireate-theme-light-v4' + m.group(3), original, count=1)
    updated = updated.replace('</head>', CSS + '</head>', 1)
    updated = updated.replace('</body>', JS + '</body>', 1)
    return 'pending', updated


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument('--apply', action='store_true', help='Write edits; without this only report')
    parser.add_argument('--slug', help='Only one service folder')
    args = parser.parse_args()
    dirs = [SERVICES / args.slug] if args.slug else sorted(
        p for p in SERVICES.iterdir() if p.is_dir() and not p.name.startswith('_')
    )
    failed = []
    pending = []
    for folder in dirs:
        path = folder / 'index.php'
        if not path.is_file():
            continue
        status, new_text = stage(path)
        print('{0}: {1}'.format(folder.name, status))
        if status.startswith('BLOCKER'):
            failed.append(folder.name)
        elif status == 'pending':
            pending.append(folder.name)
            if args.apply:
                path.write_text(new_text, encoding='utf-8')
    print('pending_changes={0} blockers={1} applied={2}'.format(
        len(pending), len(failed), bool(args.apply)
    ))
    if failed:
        return 2
    return 0


if __name__ == '__main__':
    raise SystemExit(main())

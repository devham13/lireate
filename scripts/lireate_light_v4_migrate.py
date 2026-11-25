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
PATENT_FOOTER_INC = 'lireate-patent-tool-footer.inc.php'


def _shell_kind(text):
    if 'lireate_service_asset_href' not in text or '</head>' not in text:
        return None
    if '</body>' in text:
        return 'standard'
    if PATENT_FOOTER_INC in text and BODY_RE.search(text) and 'lireate-svc-body' in (BODY_RE.search(text).group(2)):
        return 'patent'
    return None


def stage(index):
    original = index.read_text(encoding="utf-8")
    if 'lireate-theme-light-v4' in original:
        return 'ready', original
    shell = _shell_kind(original)
    if shell is None:
        return 'BLOCKER: unknown service shell', original
    match = BODY_RE.search(original)
    if not match or 'lireate-svc-body' not in match.group(2):
        return 'BLOCKER: unrecognised <body> classes', original
    updated = BODY_RE.sub(
        lambda m: m.group(1) + m.group(2) + ' lireate-theme-light-v4' + m.group(3),
        original,
        count=1,
    )
    updated = updated.replace('</head>', CSS + '</head>', 1)
    if shell == 'standard':
        updated = updated.replace('</body>', JS + '</body>', 1)
    else:
        needle = "include dirname(__DIR__) . '/_shared/blocks/" + PATENT_FOOTER_INC
        if needle not in updated:
            needle = "include dirname(__DIR__) . '/_shared/blocks/" + PATENT_FOOTER_INC
        if PATENT_FOOTER_INC not in updated:
            return 'BLOCKER: patent footer hook missing', original
        # Inject shared JS before patent tool footer include (closes </body> in block).
        lines = updated.splitlines(keepends=True)
        out = []
        injected = False
        for line in lines:
            if not injected and PATENT_FOOTER_INC in line and 'include' in line:
                out.append(JS)
                injected = True
            out.append(line)
        if not injected:
            return 'BLOCKER: patent footer hook missing', original
        updated = ''.join(out)
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

#!/usr/bin/env python3
"""Install two redesigned CMS patent views. Dry run default; --apply with backup/rollback.

Live main.php renders /patent and /patent_prom_obr in dedicated page branches (not apsw).
"""
from __future__ import print_function

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
MARKER = 'lireate-patent-light-v4-install:v1'
DARK_EXCLUDE_MARKER = 'lireate-patent-light-v4-dark-exclude:v1'
DARK_PAGES_NEEDLE = "array('home','patent','patent_prom_obr',"
DARK_PAGES_REPL = "/* " + DARK_EXCLUDE_MARKER + " */ array('home',"
MAIN = Path('template/main.php')
FILES = [
    Path('pages/patents/_shared/lireate-patent-data.php'),
    Path('pages/patents/_shared/lireate-patent-page.inc.php'),
    Path('template/css/lireate-patent-light-v4.css'),
    Path('template/js/lireate-patent-light-v4.js'),
    Path('template/images/patent-v4/patent-inventions.webp'),
    Path('template/images/patent-v4/patent-industrial-design.webp'),
]
INCLUDE_SNIPPET = (
    "require $_SERVER['DOCUMENT_ROOT'].'/pages/patents/_shared/lireate-patent-page.inc.php';"
)
LEGACY_PATENT_OPEN = re.compile(
    r"<\?php\s+if\s*\(\s*\$temp\['page'\]\s*==\s*'patent'\s*\)\s*:\s*\?>",
    re.I,
)
LEGACY_PROM_OPEN = re.compile(
    r"<\?php\s+if\s*\(\s*\$temp\['page'\]\s*==\s*'patent_prom_obr'\s*\)\s*:\s*\?>",
    re.I,
)
LEGACY_DISABLED = re.compile(
    r"\$temp\['page'\]\s*==\s*'(patent|patent_prom_obr)'\s*&&\s*false",
    re.I,
)
APSW_PATTERN = re.compile(
    r'<\?php\s*(?P<prefix>\}\s*else\s*if|\}\s*elseif|if)\s*\(\s*\$temp\[\'apsw\'\]\s*\)\s*\{\s*\?>',
    re.I,
)


def _hook(page_key):
    return (
        "<?php /* " + MARKER + " */ if($temp['page']=='" + page_key + "'){ "
        "$lireate_patent_key='" + page_key + "'; " + INCLUDE_SNIPPET + " } ?>\n"
    )


def _disable_legacy_open(match, page_key):
    return "<?php if($temp['page']=='" + page_key + "' && false): /* legacy */ ?>"


def patch_main_dark_exclusion(text):
    if DARK_EXCLUDE_MARKER in text:
        return text
    if DARK_PAGES_NEEDLE not in text:
        return text
    return text.replace(DARK_PAGES_NEEDLE, DARK_PAGES_REPL)


def patch_main_legacy_blocks(text):
    out = text
    if INCLUDE_SNIPPET in out and MARKER in out:
        return out
    if LEGACY_PATENT_OPEN.search(out) and not re.search(
        r"\$temp\['page'\]\s*==\s*'patent'\s*&&\s*false", out
    ):
        out = LEGACY_PATENT_OPEN.sub(
            _hook('patent') + _disable_legacy_open(None, 'patent'), out, count=1
        )
    if LEGACY_PROM_OPEN.search(out) and not re.search(
        r"\$temp\['page'\]\s*==\s*'patent_prom_obr'\s*&&\s*false", out
    ):
        out = LEGACY_PROM_OPEN.sub(
            _hook('patent_prom_obr') + _disable_legacy_open(None, 'patent_prom_obr'),
            out,
            count=1,
        )
    if out == text:
        return None
    return out


def patch_main_apsw_fallback(text):
    m = APSW_PATTERN.search(text)
    if m is None:
        return None
    container = text.find('<div class="page_container">')
    if container < 0 or m.start() < container:
        return None
    prefix = m.group('prefix').strip().lower()
    open_cond = 'if' if prefix == 'if' else '} elseif'
    replacement = (
        "<?php /* " + MARKER + " */ " + open_cond +
        "($temp['page']=='patent'||$temp['page']=='patent_prom_obr'){ "
        "$lireate_patent_key=$temp['page']; " + INCLUDE_SNIPPET + " "
        "} elseif ($temp['apsw']){ ?>"
    )
    return text[:m.start()] + replacement + text[m.end():]


def patch_main(text):
    if MARKER in text and INCLUDE_SNIPPET in text and DARK_EXCLUDE_MARKER in text:
        return text
    if MARKER in text and INCLUDE_SNIPPET in text:
        return patch_main_dark_exclusion(text)
    text = patch_main_dark_exclusion(text)
    legacy = patch_main_legacy_blocks(text)
    if legacy is not None:
        return patch_main_dark_exclusion(legacy)
    fallback = patch_main_apsw_fallback(text)
    if fallback is not None:
        return fallback
    raise ValueError(
        "BLOCKER: no patent legacy branches and no page_container/apsw hook in template/main.php"
    )


def safety(docroot, rel):
    p = docroot / rel
    if p.is_symlink() or (p.exists() and not p.is_file()):
        raise ValueError('BLOCKER: unsafe path: ' + str(p))
    return p


def build(docroot):
    orig = safety(docroot, MAIN)
    if not orig.is_file():
        raise ValueError('BLOCKER: no template/main.php in DOCROOT')
    base = orig.read_text(encoding='utf-8')
    next_main = patch_main(base)
    plan = {MAIN: next_main.encode('utf-8')}
    for f in FILES:
        src = REPO / f
        if not src.is_file():
            raise ValueError('BLOCKER: missing source file: ' + str(src))
        plan[f] = src.read_bytes()
    return {k: v for k, v in plan.items() if not safety(docroot, k).is_file() or safety(docroot, k).read_bytes() != v}


def atomic_write(dst, data):
    dst.parent.mkdir(parents=True, exist_ok=True)
    fd, temp = tempfile.mkstemp(prefix='.lireate-patent-', dir=str(dst.parent))
    try:
        with os.fdopen(fd, 'wb') as f:
            f.write(data)
        os.chmod(temp, dst.stat().st_mode & 0o777 if dst.is_file() else 0o644)
        os.replace(temp, str(dst))
    finally:
        if os.path.exists(temp):
            os.unlink(temp)


def restore(docroot, folder):
    manifest = json.loads((folder / 'manifest.json').read_text(encoding='utf-8'))
    allowed = set(FILES + [MAIN])
    for item in manifest['files']:
        rel = Path(item['path'])
        if rel not in allowed:
            raise ValueError('Unrecognized backup entry: ' + str(rel))
        dst = safety(docroot, rel)
        if item['existed']:
            atomic_write(dst, (folder / rel).read_bytes())
        elif dst.is_file():
            dst.unlink()
    print('ROLLED_BACK', folder)


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--docroot', required=True)
    parser.add_argument('--backup-root')
    parser.add_argument('--apply', action='store_true')
    parser.add_argument('--rollback', help='full path to previous backup')
    parser.add_argument('--php-bin', default='php')
    args = parser.parse_args()
    docroot = Path(args.docroot).resolve()
    backup_root = Path(args.backup_root).resolve() if args.backup_root else docroot.parent / 'lireate-patent-backups'
    if backup_root == docroot or docroot in backup_root.parents:
        raise ValueError('BACKUP DIRECTORY MUST BE OUTSIDE DOCROOT')
    if args.rollback:
        restore(docroot, Path(args.rollback).resolve())
        return 0
    plan = build(docroot)
    for rel in plan:
        print(('APPLY' if args.apply else 'DRY_RUN'), rel)
    if not plan:
        print('NO_CHANGES')
        return 0
    if not args.apply:
        return 0
    date = datetime.datetime.now(datetime.timezone.utc).strftime('%Y%m%dT%H%M%SZ')
    folder = backup_root / date
    folder.mkdir(parents=True, exist_ok=False)
    records = []
    for rel in plan:
        p = safety(docroot, rel)
        records.append({'path': str(rel), 'existed': p.is_file()})
        if p.is_file():
            target = folder / rel
            target.parent.mkdir(parents=True, exist_ok=True)
            shutil.copy2(p, target)
    (folder / 'manifest.json').write_text(json.dumps({'files': records}, indent=2), encoding='utf-8')
    try:
        for rel, data in plan.items():
            atomic_write(safety(docroot, rel), data)
        php = shutil.which(args.php_bin) or (args.php_bin if Path(args.php_bin).is_file() else None)
        if not php:
            raise RuntimeError('No PHP binary: pass --php-bin')
        for rel in [MAIN] + [p for p in FILES if p.suffix == '.php']:
            result = subprocess.run([php, '-l', str(docroot / rel)], stdout=subprocess.PIPE, stderr=subprocess.PIPE, text=True)
            if result.returncode != 0:
                raise RuntimeError('PHP lint failed in ' + str(rel) + ': ' + result.stdout + result.stderr)
    except Exception:
        restore(docroot, folder)
        raise
    print('APPLIED backup=' + str(folder))
    return 0


if __name__ == '__main__':
    raise SystemExit(main())
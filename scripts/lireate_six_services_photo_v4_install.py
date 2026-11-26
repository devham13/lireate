#!/usr/bin/env python3
"""Safe, reversible HTML-head asset installer for six existing LIREATE CMS routes.

The original PHP services, legal contents, SEO tags, forms and tracking remain in place.
Dry-run by default. No git checkout, hosting credentials or automatic deploy.
"""
import argparse
from datetime import datetime, timezone
import hashlib
import json
import os
from pathlib import Path
import re
import shutil
import subprocess
import tempfile

SOURCE = Path(__file__).resolve().parent.parent
MARKER = 'lireate-six-services-photo-v4-20261126'
ROUTES = (
    'findtz', 'registraciya_tovarnogo_znaka', 'prodlenie_tovarnogo_znaka',
    'vozrazhenie_protiv_registracii_tovarnogo_znaka',
    'zashchita_avtorskih_prav', 'dogovory',
)
ASSETS = (
    Path('template/css/lireate-six-services-photo-v4.css'),
    Path('template/js/lireate-six-services-photo-v4.js'),
) + tuple(Path('template/images/services-v4/' + n + '.webp') for n in (
    'specialist-certificate', 'specialist-registraciya', 'specialist-prolongation',
    'specialist-opposition', 'specialist-copyright'))
INJECTION = (
    '\n<!-- ' + MARKER + ' -->\n'
    '<link rel="stylesheet" href="/template/css/lireate-six-services-photo-v4.css?v=20261126">\n'
    '<script defer src="/template/js/lireate-six-services-photo-v4.js?v=20261126"></script>\n'
)
HEAD_CLOSE = re.compile(r'</head\s*>', re.IGNORECASE)


def sha(data):
    return hashlib.sha256(data).hexdigest()


def target(root, relative):
    if relative.is_absolute() or '..' in relative.parts:
        raise ValueError('Unsafe relative path: ' + str(relative))
    p = root / relative
    if p.is_symlink() or any(par.is_symlink() for par in p.parents if par != root):
        raise ValueError('Symlink not allowed: ' + str(p))
    if p.exists() and not p.is_file():
        raise ValueError('Not a regular file: ' + str(p))
    return p


def patch_php(content, relative):
    if MARKER in content:
        return content
    if len(HEAD_CLOSE.findall(content)) != 1:
        raise ValueError('Expected one literal </head> in ' + str(relative))
    return HEAD_CLOSE.sub(lambda match: INJECTION + match.group(0), content, count=1)


def plan(root):
    result = {}
    for rel in ASSETS:
        local = SOURCE / rel
        if not local.is_file():
            raise ValueError('Missing repository asset: ' + str(rel))
        result[rel] = local.read_bytes()
    # CMS main.php catches routes without a direct standalone file.
    # Also inject individual service entrypoints, because several are standalone.
    main_rel = Path('template/main.php')
    scripts = [main_rel] if target(root, main_rel).is_file() else []
    matched = []
    for slug in ROUTES:
        rel = Path(slug) / 'index.php'
        if target(root, rel).is_file():
            scripts.append(rel)
            matched.append(slug)
    if not scripts:
        raise ValueError('BLOCKER: no template/main.php or /slug/index.php found')
    for rel in scripts:
        txt = target(root, rel).read_text(encoding='utf-8')
        result[rel] = patch_php(txt, rel).encode('utf-8')
    changes = {r:b for r,b in result.items() if (not target(root,r).exists() or target(root,r).read_bytes() != b)}
    return changes, scripts, matched


def write_atomically(path, data):
    path.parent.mkdir(parents=True,exist_ok=True)
    fd,tmp=tempfile.mkstemp(prefix='.lireate-photo-v4-',dir=str(path.parent))
    try:
        with os.fdopen(fd,'wb') as h: h.write(data)
        os.chmod(tmp, (path.stat().st_mode & 0o777) if path.exists() else 0o644)
        os.replace(tmp,path)
    finally:
        if os.path.exists(tmp): os.unlink(tmp)


def lint_php(code, php_bin):
    if not shutil.which(php_bin) and not Path(php_bin).is_file():
        raise ValueError('Cannot locate PHP executable: '+php_bin)
    with tempfile.NamedTemporaryFile('wb',suffix='.php',delete=False) as tmp:
        tmp.write(code);name=tmp.name
    try:
        p=subprocess.run([php_bin,'-l',name],capture_output=True,text=True,timeout=30)
        if p.returncode: raise ValueError('php -l failed: '+p.stderr+' '+p.stdout)
    finally:
        os.unlink(name)


def rollback(root, backup, allow_drift=False):
    meta=json.loads((backup/'manifest.json').read_text(encoding='utf-8'))
    files=meta['files']
    for item in files:
        rel=Path(item['path']); dest=target(root,rel)
        if dest.is_file() and sha(dest.read_bytes())!=item['after_sha256'] and not allow_drift:
            raise ValueError('BLOCKER: changed since install: '+str(rel)+'; use --force-rollback after review')
    for item in files:
        rel=Path(item['path']);dest=target(root,rel)
        if item['existed']:
            write_atomically(dest,(backup/rel).read_bytes())
        elif dest.exists():dest.unlink()
    print('RESTORED',backup)


def main():
    ap=argparse.ArgumentParser(description=__doc__)
    ap.add_argument('--docroot',required=True)
    ap.add_argument('--backup-root',required=True)
    ap.add_argument('--apply',action='store_true')
    ap.add_argument('--rollback',metavar='BACKUP_DIRECTORY')
    ap.add_argument('--force-rollback',action='store_true')
    ap.add_argument('--php-bin',default=os.environ.get('LIREATE_PHP_BIN','php'))
    args=ap.parse_args()
    root=Path(args.docroot).expanduser().resolve()
    backups=Path(args.backup_root).expanduser().resolve()
    if backups==root or root in backups.parents:
        raise ValueError('Backups must be OUTSIDE DOCROOT')
    if args.rollback:
        rollback(root,Path(args.rollback).expanduser().resolve(),args.force_rollback);return 0
    changes,scripts,matched=plan(root)
    print('ENTRYPOINTS:',', '.join(str(x) for x in scripts))
    print('DIRECT SERVICE ENTRIES:',', '.join(matched) or '(none: CMS routes)')
    for rel in changes:print(('APPLY' if args.apply else 'DRY_RUN'),rel)
    if not changes:print('NO_CHANGES');return 0
    # syntax preflight must pass BEFORE any write.
    for rel,data in changes.items():
        if rel.suffix=='.php': lint_php(data,args.php_bin)
    if not args.apply:
        print('CHECK_OK, no files changed');return 0
    stamp=datetime.now(timezone.utc).strftime('%Y%m%dT%H%M%SZ')
    backup=backups/stamp
    backup.mkdir(parents=True,exist_ok=False)
    meta={'marker':MARKER,'files':[]}
    for rel,data in changes.items():
        current=target(root,rel)
        existed=current.is_file()
        meta['files'].append({'path':str(rel),'existed':existed,'after_sha256':sha(data)})
        if existed:
            f=backup/rel;f.parent.mkdir(parents=True,exist_ok=True);shutil.copy2(current,f)
    (backup/'manifest.json').write_text(json.dumps(meta,ensure_ascii=False,indent=2),encoding='utf-8')
    try:
        for rel,data in changes.items():write_atomically(target(root,rel),data)
        for rel in scripts: lint_php(target(root,rel).read_bytes(),args.php_bin)
    except Exception:
        rollback(root,backup,allow_drift=True)
        raise
    print('APPLIED_BACKUP:',backup)
    return 0

if __name__=='__main__':
    raise SystemExit(main())

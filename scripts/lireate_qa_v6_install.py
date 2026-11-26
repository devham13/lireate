#!/usr/bin/env python3
"""LIREATE QA V6 guarded host installer.

Dry-run by default. --apply only after browser QA; never overwrite drifted
production PHP. --with-tools includes 13 standalone /patentnye-zadachi/
entry points. Existing CMS and services have different deployment roots.
"""
import argparse
import datetime
import hashlib
import json
import os
from pathlib import Path
import re
import shutil
import subprocess
import tempfile

REPO = Path(__file__).resolve().parents[1]
MARK = "lireate-qa-v6-20261126"
HEAD = ('<!-- ' + MARK + ' -->\n'
        '<link rel="stylesheet" href="/template/css/lireate-qa-v6.css?v=20261126">\n'
        '<script defer src="/template/js/lireate-qa-v6.js?v=20261126"></script>\n')
MAIN = Path('template/main.php')

# All paths relative to actual web DOCROOT.
ASSETS = {
    'template/css/lireate-qa-v6.css':'template/css/lireate-qa-v6.css',
    'template/js/lireate-qa-v6.js':'template/js/lireate-qa-v6.js',
    'template/lireate/lireate-patent-tasks-sidebar-v6.inc.php':'template/lireate/lireate-patent-tasks-sidebar-v6.inc.php',
    'template/images/logo/logo-light-v5.svg':'template/images/logo/logo-light-v5.svg',
}
CMS = {
    'template/page/kontaktyi/lireate-contacts-page.inc.php':'2c36b465077012f5a5844811664f949d1f486c9c',
    'template/page/partners/lireate-partners-page.inc.php':'5b952e2a7c1323a32f0e5307e6c663315c1e73f2',
    'template/page/news/lireate-blog-list.inc.php':'24d0e795da24727d2b286a67c03c0e88a04c1afc',
    'template/page/news/lireate-blog-lib.php':'4e96d8ac5221a4376a6655eb9ac4c2f7772c3dec',
    'template/page/news/lireate-blog-site-footer.inc.php':'437db7282bbf6e010ecfc76a87b4d6a688d1f20f',
    'template/lireate/lireate-service-footer.inc.php':'1754dff4fb54acb2c7a927ef3af2f70b636cc500',
}
# src-relative path -> (live-relative path, reviewed main blob SHA)
TOOLS = {
 'pages/services/patentnye-zadachi/index.php':('patentnye-zadachi/index.php','f78fca98dd5b26b3b91f59e97f6909838b6602f9'),
 'pages/services/patentnye-zadachi-adres-zayavitelya/index.php':('patentnye-zadachi/adres-zayavitelya/index.php','64deb45047c37b0802c7162576e58dc341d2d47d'),
 'pages/services/patentnye-zadachi-fail-slovesnogo-znaka/index.php':('patentnye-zadachi/fail-slovesnogo-znaka/index.php','94223f2d73f36d407ceea4ea6f6f1f6cb9490af2'),
 'pages/services/patentnye-zadachi-kontakty-fips/index.php':('patentnye-zadachi/kontakty-fips/index.php','db9407d92cf47d1e7c737dc204e30fd946b82087'),
 'pages/services/patentnye-zadachi-oplata-patentnyh-poshlin/index.php':('patentnye-zadachi/oplata-patentnyh-poshlin/index.php','ac47092ebf24339dbc5d1aae19e84609d81e9038'),
 'pages/services/patentnye-zadachi-oplata-uslug-fips/index.php':('patentnye-zadachi/oplata-uslug-fips/index.php','7713f3201b44539d256840c671d353bd6a23eb10'),
 'pages/services/patentnye-zadachi-poisk-platezha-evm/index.php':('patentnye-zadachi/poisk-platezha-evm/index.php','586883a4928e9e1f6c9aba6dee9628d170087c14'),
 'pages/services/patentnye-zadachi-poshliny-izmeneniya/index.php':('patentnye-zadachi/poshliny-izmeneniya/index.php','f0bad8e3b65de49a835d56667f08ae60d86e7c21'),
 'pages/services/patentnye-zadachi-poshliny-prodlenie/index.php':('patentnye-zadachi/poshliny-prodlenie/index.php','e80c03b9adc54195ad95f4cabdec5954e7f045ca'),
 'pages/services/patentnye-zadachi-poshliny-registraciya/index.php':('patentnye-zadachi/poshliny-registraciya/index.php','facc751ad09b8be6d26e39331cb4138e0b91d59e'),
 'pages/services/patentnye-zadachi-redaktor-mktu/index.php':('patentnye-zadachi/redaktor-mktu/index.php','d2aaef6682b80bdf91ec517b44e1d16645213fa0'),
 'pages/services/patentnye-zadachi-sokrashchenie-mktu/index.php':('patentnye-zadachi/sokrashchenie-mktu/index.php','f9e3ef197fcaecb4963228cf11df5903ae01a0fe'),
 'pages/services/patentnye-zadachi-starye-zayavki/index.php':('patentnye-zadachi/starye-zayavki/index.php','966fb107ca130c174e11fe2c8b2bf7485d1aabf4'),
}
POSSIBLE = {MAIN} | {Path(p) for p in ASSETS} | {Path(p) for p in CMS} | {Path(v[0]) for v in TOOLS.values()}

def blob_sha(data):
    return hashlib.sha1(b"blob " + str(len(data)).encode('ascii') + b'\0' + data).hexdigest()

def dest(docroot, relative):
    if relative not in POSSIBLE:
        raise ValueError("BLOCKER unexpected target path " + str(relative))
    result = docroot / relative
    if result.is_symlink() or any(p.is_symlink() for p in result.parents if p != docroot and docroot in p.parents):
        raise ValueError("BLOCKER symlink in path " + str(relative))
    if result.exists() and not result.is_file():
        raise ValueError("BLOCKER target not a regular file " + str(relative))
    return result

def build_plan(docroot, with_tools=False):
    main = dest(docroot, MAIN)
    if not main.is_file():
        raise ValueError("BLOCKER CMS template/main.php missing")
    current = main.read_text(encoding='utf-8')
    if MARK in current:
        new = current
    else:
        if len(re.findall(r'</head\s*>', current, re.I)) != 1:
            raise ValueError("BLOCKER expected one closing head in template/main.php")
        new = re.sub(r'</head\s*>', lambda m: HEAD + m.group(0), current, count=1, flags=re.I)
    desired = {MAIN:new.encode('utf-8')}
    expected = {}
    for live, source in ASSETS.items():
        file = REPO / source
        if not file.is_file():
            raise ValueError("BLOCKER missing asset in PR " + source)
        desired[Path(live)] = file.read_bytes()
    for live, before in CMS.items():
        src = REPO / live
        if not src.is_file():
            raise ValueError("BLOCKER missing CMS page in PR " + live)
        desired[Path(live)] = src.read_bytes()
        expected[Path(live)] = before
    if with_tools:
        for source, (live, before) in TOOLS.items():
            src = REPO / source
            if not src.is_file():
                raise ValueError("BLOCKER missing published tool source " + source)
            desired[Path(live)] = src.read_bytes()
            expected[Path(live)] = before
    plan = {}
    for relative, data in desired.items():
        target = dest(docroot, relative)
        if target.is_file():
            old = target.read_bytes()
            if old == data:
                continue
            if relative in expected and blob_sha(old) != expected[relative]:
                raise ValueError("BLOCKER live PHP has newer/different modifications: " + str(relative) +
                    "; do not overwrite; integrate patch in Cursor and re-review")
            if relative != MAIN and relative not in expected:
                raise ValueError("BLOCKER unexpected existing shared asset: " + str(relative))
        elif relative in expected:
            raise ValueError("BLOCKER expected published PHP not found: " + str(relative))
        plan[relative] = data
    return plan

def write_atomic(target, data):
    target.parent.mkdir(parents=True, exist_ok=True)
    fd, temp = tempfile.mkstemp(prefix='.lqv6-', dir=str(target.parent))
    try:
        with os.fdopen(fd, 'wb') as stream: stream.write(data)
        os.chmod(temp, target.stat().st_mode & 0o777 if target.is_file() else 0o644)
        os.replace(temp, target)
    finally:
        if os.path.exists(temp): os.unlink(temp)

def save_backup(docroot, plan, backup):
    backup.mkdir(parents=True, exist_ok=False)
    entries = []
    for relative in plan:
        target = dest(docroot, relative)
        present = target.is_file()
        entries.append({'path':str(relative),'existed':present})
        if present:
            archived = backup / relative
            archived.parent.mkdir(parents=True, exist_ok=True)
            shutil.copy2(target, archived)
    (backup/'manifest.json').write_text(json.dumps({'version':MARK,'files':entries}, indent=2, ensure_ascii=False),encoding='utf-8')

def rollback(docroot, backup):
    m=json.loads((backup/'manifest.json').read_text(encoding='utf-8'))
    if m.get('version') != MARK: raise ValueError("BLOCKER backup version mismatch")
    for e in m['files']:
        rel=Path(e['path'])
        target=dest(docroot,rel)
        if e['existed']:
            archived=backup/rel
            if not archived.is_file(): raise ValueError("BLOCKER missing backup "+str(rel))
            write_atomic(target, archived.read_bytes())
        elif target.is_file():
            target.unlink()
    print("ROLLED_BACK",backup)

def main(argv=None):
    p=argparse.ArgumentParser(description=__doc__)
    p.add_argument('--docroot',required=True)
    p.add_argument('--backup-root')
    p.add_argument('--with-tools',action='store_true',help='include 13 already published standalone tool scripts')
    p.add_argument('--apply',action='store_true')
    p.add_argument('--php-bin',default='php')
    p.add_argument('--rollback',metavar='BACKUP_DIR')
    args=p.parse_args(argv)
    docroot=Path(args.docroot).expanduser().resolve()
    if not docroot.is_dir(): raise ValueError("BLOCKER DOCROOT not found")
    backup_root=(Path(args.backup_root).expanduser().resolve() if args.backup_root else docroot.parent/'lireate-qa-v6-backups')
    if backup_root==docroot or docroot in backup_root.parents: raise ValueError("BLOCKER backups must be outside DOCROOT")
    if args.rollback:
        rollback(docroot,Path(args.rollback).expanduser().resolve());return 0
    plan=build_plan(docroot,args.with_tools)
    for rel in plan:print(("APPLY" if args.apply else "DRY_RUN"),rel)
    if not plan:print("NO_CHANGES");return 0
    if not args.apply:print("DRY_RUN_ONLY");return 0
    php=shutil.which(args.php_bin) or (args.php_bin if Path(args.php_bin).is_file() else None)
    if not php: raise ValueError("BLOCKER PHP CLI not found")
    backup=backup_root/datetime.datetime.now(datetime.timezone.utc).strftime('%Y%m%dT%H%M%SZ')
    save_backup(docroot,plan,backup)
    try:
        for rel,data in plan.items():write_atomic(dest(docroot,rel),data)
        for rel in plan:
            if rel.suffix != '.php':continue
            check=subprocess.run([php,'-l',str(docroot/rel)],capture_output=True,text=True,timeout=25)
            if check.returncode:raise RuntimeError("PHP lint failed "+str(rel)+": "+check.stdout+check.stderr)
    except Exception:
        rollback(docroot,backup)
        raise
    print("INSTALLED backup="+str(backup));return 0

if __name__=='__main__':raise SystemExit(main())

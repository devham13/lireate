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
import sys
import tempfile

_SCRIPTS = Path(__file__).resolve().parent
if str(_SCRIPTS) not in sys.path:
    sys.path.insert(0, str(_SCRIPTS))
from lireate_hosting_services_lib import load_manifest

REPO = Path(__file__).resolve().parents[1]
MARK = "lireate-qa-v6-20261126"
QA_CACHE = "20261126d"
HEAD = ('<!-- ' + MARK + ' -->\n'
        '<link rel="stylesheet" href="/template/css/lireate-qa-v6.css?v=' + QA_CACHE + '">\n'
        '<script defer src="/template/js/lireate-qa-v6.js?v=' + QA_CACHE + '"></script>\n')
QA_CACHE_CSS_RE = re.compile(r'/template/css/lireate-qa-v6\.css\?v=20261126(?:[a-d])?', re.I)
QA_CACHE_JS_RE = re.compile(r'/template/js/lireate-qa-v6\.js\?v=20261126(?:[a-d])?', re.I)
MAIN = Path('template/main.php')

# All paths relative to actual web DOCROOT.
ASSETS = {
    'template/css/lireate-qa-v6.css':'template/css/lireate-qa-v6.css',
    'template/js/lireate-qa-v6.js':'template/js/lireate-qa-v6.js',
    'template/lireate/lireate-patent-tasks-sidebar-v6.inc.php':'template/lireate/lireate-patent-tasks-sidebar-v6.inc.php',
    'template/images/logo/logo-light-v5.svg':'template/images/logo/logo-light-v5.svg',
}
# Host state after QA V6 deploy commit a4ca4e8 (assets already on prod).
_ASSET_DEPLOYED_BLOB = {
    'template/css/lireate-qa-v6.css': '99b971aacf6e2b0cc2d83f41a20e294eb5156dae',
    'template/js/lireate-qa-v6.js': 'ae8096f30e26993ea667f142c3ad00f6e7b8ac94',
    'template/lireate/lireate-patent-tasks-sidebar-v6.inc.php': '685055a5772c0c6bca191b253430dd30a2271be4',
    'template/images/logo/logo-light-v5.svg': 'e62f67284d5eb62df3fe7579f3d201cd71228101',
}
# Host after PR #132 deploy 70d7a08 (CSS on prod may be rolled back to a4 blob above).
_ASSET_V70_BLOB = {
    'template/css/lireate-qa-v6.css': 'b21fe7e9544e1b12bac6750b3f83210d269651f2',
}
ASSET_LIVE = frozenset(Path(p) for p in ASSETS)
# Reviewed main-branch blobs (pre-PR #131 for company pages).
_CMS_MAIN_BLOB = {
    'template/page/kontaktyi/lireate-contacts-page.inc.php': '2c36b465077012f5a5844811664f949d1f486c9c',
    'template/page/partners/lireate-partners-page.inc.php': '5b952e2a7c1323a32f0e5307e6c663315c1e73f2',
    'template/page/news/lireate-blog-list.inc.php': '24d0e795da24727d2b286a67c03c0e88a04c1afc',
    'template/page/news/lireate-blog-lib.php': '4e96d8ac5221a4376a6655eb9ac4c2f7772c3dec',
    'template/page/news/lireate-blog-site-footer.inc.php': '437db7282bbf6e010ecfc76a87b4d6a688d1f20f',
    'template/lireate/lireate-service-footer.inc.php': '1754dff4fb54acb2c7a927ef3af2f70b636cc500',
}
# CMS pages rendered from pages/*/_shared on REG.RU (prod commit 15bf93c).
_SHARED_PROD_BLOB = {
    'pages/kontaktyi/_shared/lireate-contacts-page.inc.php': 'aa4691425bc8e984d017d89e216f3e5c405dbec8',
    'pages/partners/_shared/lireate-partners-page.inc.php': 'cc1d97119752c2cccbec61c9239ec1397a2f1b76',
    'pages/blog/_shared/lireate-blog-list.inc.php': 'a9890f05e3a859ca8c829be139f2f9bde1c51c4f',
    'pages/blog/_shared/lireate-blog-lib.php': '4e96d8ac5221a4376a6655eb9ac4c2f7772c3dec',
    'pages/blog/_shared/lireate-blog-site-footer.inc.php': '437db7282bbf6e010ecfc76a87b4d6a688d1f20f',
}
SHARED_CMS = dict(_SHARED_PROD_BLOB)
SERVICE_SLUG_FORBIDDEN = re.compile(
    r'^(?:everest|reg-tov-znak|lireatecom|copy)(?:[^a-z0-9_]|$)',
    re.I,
)
V7_MARK = 'lireate-sitewide-sidebar-v7-20261126'
V7_HEAD_BLOCK_RE = re.compile(
    r"\n<!--\s*" + re.escape(V7_MARK) + r"\s*-->\s*"
    r'<link rel="stylesheet" href="/template/css/lireate-sitewide-sidebar-v7\.css\?v=20261126">\s*'
    r'<script defer src="/template/js/lireate-sitewide-sidebar-v7\.js\?v=20261126"></script>\s*\n',
    re.IGNORECASE | re.DOTALL,
)
REQUISITES_DETAILS_RE = re.compile(
    r'<details\s+class="lx-site-footer-requisites"\s*>'
    r'\s*<summary>Реквизиты</summary>\s*'
    r'<div\s+class="lx-site-footer-requisites-panel">.*?</div>\s*'
    r'</details>',
    re.DOTALL | re.IGNORECASE,
)
REQUISITES_LINK = '<a class="lx-site-footer-documents-link" href="/docs">Реквизиты</a>'
_EXTRA_POSSIBLE = set()

# Live production after PR #131 (commit 15bf93c) and prod footer (d8b9d8f4).
_CMS_PROD_BLOB = {
    'template/page/kontaktyi/lireate-contacts-page.inc.php': 'aa4691425bc8e984d017d89e216f3e5c405dbec8',
    'template/page/partners/lireate-partners-page.inc.php': 'cc1d97119752c2cccbec61c9239ec1397a2f1b76',
    'template/lireate/lireate-service-footer.inc.php': 'd8b9d8f4d5a698ac10d1a3ef7fc3b58cadb2561d',
}
# template/page/* after QA V6 deploy a4ca4e8 (still valid live base on host).
_CMS_A4CA4E8_BLOB = {
    'template/page/kontaktyi/lireate-contacts-page.inc.php': '1d5ce72f715025fbac9cd87e5de699d410608a74',
    'template/page/partners/lireate-partners-page.inc.php': '7ce2fa7617e9b3c9eda7d63671ea21989c37f29f',
    'template/page/news/lireate-blog-list.inc.php': '2d0907d7e9f58ee12df8d48a0cb50a652233fd89',
}
_SHARED_A4CA4E8_BLOB = {
    'pages/kontaktyi/_shared/lireate-contacts-page.inc.php': '1d5ce72f715025fbac9cd87e5de699d410608a74',
    'pages/partners/_shared/lireate-partners-page.inc.php': '7ce2fa7617e9b3c9eda7d63671ea21989c37f29f',
}
# patentnye-zadachi/*/index.php on host after a4ca4e8 (strip perf+v7 to match).
_TOOL_DEPLOYED_A4 = {
    'patentnye-zadachi/index.php': '69ea47dc1f196d51d24258bea39660edcca7fe8e',
    'patentnye-zadachi/adres-zayavitelya/index.php': 'e24999194feef2ef313b112e2994d864e6512c71',
    'patentnye-zadachi/fail-slovesnogo-znaka/index.php': 'f82398b31caf3bf72df0a2c001bcefc74a827f7b',
    'patentnye-zadachi/kontakty-fips/index.php': '2bf78fb547551dc49e565cad5ff09a40be6863f7',
    'patentnye-zadachi/oplata-patentnyh-poshlin/index.php': '0f6e386567f97717cf00961561ec8f5a3d346928',
    'patentnye-zadachi/oplata-uslug-fips/index.php': '1f01c7198a2db78f810f87fcff8fa3bbc988da30',
    'patentnye-zadachi/poisk-platezha-evm/index.php': '8a3e755ebffbd7bb2d8e8ea26cb3efaa6611b80e',
    'patentnye-zadachi/poshliny-izmeneniya/index.php': 'e40741ff7db661476fb5a3e739b350d19cdc9841',
    'patentnye-zadachi/poshliny-prodlenie/index.php': '876d964089205d530b6be3cba140355f82dc4212',
    'patentnye-zadachi/poshliny-registraciya/index.php': 'e19b6e078ecab26f3e60e7370fb5dd6fa442ad77',
    'patentnye-zadachi/redaktor-mktu/index.php': 'f726dc13f1e030dddf52cba641507f66072f48a7',
    'patentnye-zadachi/sokrashchenie-mktu/index.php': 'c800677ec9f27156d2a5fd2e8426ae340c7f0b01',
    'patentnye-zadachi/starye-zayavki/index.php': '64101551fe25b9ba4b00840100700cb6adf9e753',
}

def _allowed_main_prod(path):
    allowed = {_CMS_MAIN_BLOB[path]}
    prod = _CMS_PROD_BLOB.get(path)
    if prod:
        allowed.add(prod)
    a4 = _CMS_A4CA4E8_BLOB.get(path)
    if a4:
        allowed.add(a4)
    return frozenset(allowed)

def _allowed_shared(path):
    target = blob_sha((REPO / path).read_bytes())
    allowed = {target}
    prod = _SHARED_PROD_BLOB.get(path)
    if prod:
        allowed.add(prod)
    a4 = _SHARED_A4CA4E8_BLOB.get(path)
    if a4:
        allowed.add(a4)
    return frozenset(allowed)

BASE_ALLOWED = {live: _allowed_main_prod(live) for live in _CMS_MAIN_BLOB}
CMS = dict(_CMS_MAIN_BLOB)
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
TOOL_LIVE = {Path(v[0]) for v in TOOLS.values()}
POSSIBLE = {MAIN} | {Path(p) for p in ASSETS} | {Path(p) for p in CMS} | TOOL_LIVE
PERF_MARK = 'seo-perf-20261120-v1'
PERF_BLOCK = (
    '<!-- ' + PERF_MARK + ' -->\n'
    '<meta name="lireate-performance-patch" content="' + PERF_MARK + '">\n'
    '<link rel="stylesheet" href="/template/css/lireate-performance.css?v=' + PERF_MARK + '">\n'
    '<script defer src="/template/js/lireate-performance.js?v=' + PERF_MARK + '"></script>'
)
PERF_BLOCK_RE = re.compile(
    r'\n?<!-- seo-perf-20261120-v1 -->\s*'
    r'<meta name="lireate-performance-patch" content="seo-perf-20261120-v1">\s*'
    r'<link rel="stylesheet" href="/template/css/lireate-performance\.css\?v=seo-perf-20261120-v1">\s*'
    r'<script defer src="/template/js/lireate-performance\.js\?v=seo-perf-20261120-v1"></script>\n?',
    re.I,
)

def blob_sha(data):
    return hashlib.sha1(b"blob " + str(len(data)).encode('ascii') + b'\0' + data).hexdigest()

BASE_ALLOWED.update({live: _allowed_shared(live) for live in SHARED_CMS})
for live in CMS:
    src = REPO / live
    if src.is_file():
        BASE_ALLOWED[live] = BASE_ALLOWED[live] | {blob_sha(src.read_bytes())}

def strip_perf(data):
    text = data.decode('utf-8')
    stripped = PERF_BLOCK_RE.sub('', text, count=1)
    if stripped == text:
        return data
    return stripped.encode('utf-8')

def strip_v7(data):
    text = data.decode('utf-8')
    stripped = V7_HEAD_BLOCK_RE.sub('', text, count=1)
    if stripped == text:
        return data
    return stripped.encode('utf-8')

def strip_ephemeral(data):
    return strip_v7(strip_perf(data))

def extract_perf_block(text):
    match = PERF_BLOCK_RE.search(text)
    if not match:
        return ''
    return match.group(0)

def merge_perf_into_head(text_bytes, perf_block):
    if not perf_block:
        return text_bytes
    text = text_bytes.decode('utf-8')
    if PERF_MARK in text:
        return text_bytes
    marker = '<!-- lireate_light_v4 -->'
    if marker in text:
        pos = text.find(marker) + len(marker)
        block = perf_block
        if not block.startswith('\n'):
            block = '\n\n' + block.lstrip('\n')
        if not block.endswith('\n'):
            block += '\n'
        return (text[:pos] + block + text[pos:]).encode('utf-8')
    return re.sub(
        r'</head\s*>',
        lambda m: perf_block + '\n' + m.group(0),
        text,
        count=1,
        flags=re.I,
    ).encode('utf-8')

def extract_v7_block(text):
    match = V7_HEAD_BLOCK_RE.search(text)
    if not match:
        return ''
    return match.group(0)

def merge_v7_before_head(text_bytes, v7_block):
    if not v7_block:
        return text_bytes
    text = text_bytes.decode('utf-8')
    if V7_MARK in text:
        return text_bytes
    block = v7_block
    if not block.endswith('\n'):
        block += '\n'
    return re.sub(
        r'</head\s*>',
        lambda m: block + m.group(0),
        text,
        count=1,
        flags=re.I,
    ).encode('utf-8')

def patch_service_footer_requisites(data):
    text = data.decode('utf-8')
    if 'href="/docs">Реквизиты</a>' in text:
        return data
    new_text, count = REQUISITES_DETAILS_RE.subn(REQUISITES_LINK, text, count=1)
    if count == 0:
        return data
    return new_text.encode('utf-8')

def service_footer_needs_patch(data):
    text = data.decode('utf-8')
    return 'href="/docs">Реквизиты</a>' not in text and REQUISITES_DETAILS_RE.search(text) is not None

def service_footer_live_allowed(old, repo_bytes):
    if not service_footer_needs_patch(old):
        return True
    allowed = {blob_sha(repo_bytes), blob_sha(patch_service_footer_requisites(repo_bytes))}
    digest = blob_sha(old)
    if digest in allowed:
        return True
    stripped = strip_ephemeral(old)
    return stripped != old and blob_sha(stripped) in allowed

def discover_service_footer_paths():
    services_root = REPO / 'pages' / 'services'
    if not services_root.is_dir():
        return []
    out = []
    for service_dir in sorted(services_root.iterdir()):
        if not service_dir.is_dir():
            continue
        slug = service_dir.name
        if slug in {'_template', '_shared'} or slug.startswith('.'):
            continue
        if SERVICE_SLUG_FORBIDDEN.match(slug):
            continue
        if not (service_dir / 'index.php').is_file() and not (service_dir / 'index.html').is_file():
            continue
        manifest = load_manifest(service_dir)
        url_path = manifest.get('url_path') or slug
        if '..' in url_path.split('/'):
            continue
        candidates = (
            service_dir / 'lireate-service-footer.inc.php',
            service_dir / 'assets' / 'lireate-shared' / 'lireate-service-footer.inc.php',
        )
        for repo_file in candidates:
            if not repo_file.is_file():
                continue
            repo_bytes = repo_file.read_bytes()
            rel_suffix = repo_file.relative_to(service_dir)
            live_rel = Path(url_path) / rel_suffix
            out.append((live_rel, repo_bytes))
    return out

def register_possible(rel_paths):
    global _EXTRA_POSSIBLE
    _EXTRA_POSSIBLE = frozenset(_EXTRA_POSSIBLE | {Path(p) for p in rel_paths})

def bump_qa_cache_query(text):
    text = QA_CACHE_CSS_RE.sub('/template/css/lireate-qa-v6.css?v=' + QA_CACHE, text)
    text = QA_CACHE_JS_RE.sub('/template/js/lireate-qa-v6.js?v=' + QA_CACHE, text)
    return text

def asset_target_sha(live_key):
    source = ASSETS[live_key]
    return blob_sha((REPO / source).read_bytes())

def asset_allowed_set(live_key):
    allowed = {asset_target_sha(live_key)}
    deployed = _ASSET_DEPLOYED_BLOB.get(live_key)
    if deployed:
        allowed.add(deployed)
    v70 = _ASSET_V70_BLOB.get(live_key)
    if v70:
        allowed.add(v70)
    return allowed

def asset_live_allowed(old, live_key):
    allowed = asset_allowed_set(live_key)
    if blob_sha(old) in allowed:
        return True
    return False

def manifest_target(docroot, rel_str):
    rel = Path(rel_str)
    if rel.is_absolute() or '..' in rel.parts:
        raise ValueError("BLOCKER manifest path invalid: " + rel_str)
    docroot_res = docroot.resolve()
    target = (docroot_res / rel).resolve()
    try:
        target.relative_to(docroot_res)
    except ValueError:
        raise ValueError("BLOCKER manifest path escapes docroot: " + rel_str)
    if target.is_symlink():
        raise ValueError("BLOCKER manifest symlink target: " + rel_str)
    for parent in target.parents:
        if parent == docroot_res:
            break
        if parent.is_symlink():
            raise ValueError("BLOCKER manifest symlink in path: " + rel_str)
    if target.exists() and not target.is_file():
        raise ValueError("BLOCKER manifest target not a file: " + rel_str)
    return target

def live_matches_allowed(old, relative, before=None):
    allowed = BASE_ALLOWED.get(str(relative))
    if allowed is None:
        allowed = frozenset({before}) if before else frozenset()
    digest = blob_sha(old)
    if digest in allowed:
        return True
    stripped = strip_ephemeral(old)
    if stripped != old and blob_sha(stripped) in allowed:
        return True
    return False

def tool_live_allowed(old, before, live_rel):
    allowed = {before}
    a4 = _TOOL_DEPLOYED_A4.get(str(live_rel))
    if a4:
        allowed.add(a4)
    allowed = frozenset(allowed)
    if blob_sha(old) in allowed:
        return True
    stripped = strip_ephemeral(old)
    return stripped != old and blob_sha(stripped) in allowed

def dest(docroot, relative):
    rel = Path(relative)
    if rel not in POSSIBLE and rel not in _EXTRA_POSSIBLE:
        raise ValueError("BLOCKER unexpected target path " + str(relative))
    result = docroot / relative
    if result.is_symlink() or any(p.is_symlink() for p in result.parents if p != docroot and docroot in p.parents):
        raise ValueError("BLOCKER symlink in path " + str(relative))
    if result.exists() and not result.is_file():
        raise ValueError("BLOCKER target not a regular file " + str(relative))
    return result

def build_plan(docroot, with_tools=False, with_shared=False):
    register_possible([])
    main = dest(docroot, MAIN)
    if not main.is_file():
        raise ValueError("BLOCKER CMS template/main.php missing")
    current = main.read_text(encoding='utf-8')
    if MARK in current:
        new = bump_qa_cache_query(current)
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
    service_footer_targets = {}
    if with_shared:
        shared_paths = []
        for live in SHARED_CMS:
            src = REPO / live
            if not src.is_file():
                raise ValueError("BLOCKER missing shared CMS page in PR " + live)
            desired[Path(live)] = src.read_bytes()
            shared_paths.append(Path(live))
        register_possible(shared_paths)
        for live_rel, repo_bytes in discover_service_footer_paths():
            register_possible([live_rel])
            target = dest(docroot, live_rel)
            if not target.is_file():
                continue
            service_footer_targets[live_rel] = repo_bytes
            old = target.read_bytes()
            new_bytes = patch_service_footer_requisites(old)
            if new_bytes == old:
                continue
            desired[live_rel] = new_bytes
    if with_tools:
        for source, (live, before) in TOOLS.items():
            src = REPO / source
            if not src.is_file():
                raise ValueError("BLOCKER missing published tool source " + source)
            live_path = Path(live)
            repo_data = src.read_bytes()
            target = dest(docroot, live_path)
            if target.is_file():
                old_text = target.read_text(encoding='utf-8')
                perf = extract_perf_block(old_text)
                v7 = extract_v7_block(old_text)
                merged = merge_perf_into_head(repo_data, perf)
                desired[live_path] = merge_v7_before_head(merged, v7)
            else:
                desired[live_path] = repo_data
            expected[live_path] = before
    plan = {}
    for relative, data in desired.items():
        target = dest(docroot, relative)
        if target.is_file():
            old = target.read_bytes()
            if old == data:
                continue
            str_rel = str(relative)
            if relative in service_footer_targets:
                if not service_footer_live_allowed(old, service_footer_targets[relative]):
                    raise ValueError("BLOCKER live service footer drift: " + str_rel)
            elif relative in expected:
                before = expected[relative]
                if relative in TOOL_LIVE:
                    ok = tool_live_allowed(old, before, relative)
                else:
                    ok = live_matches_allowed(old, relative, before)
                if not ok:
                    raise ValueError("BLOCKER live PHP has newer/different modifications: " + str_rel +
                        "; do not overwrite; integrate patch in Cursor and re-review")
            elif str_rel in SHARED_CMS:
                if not live_matches_allowed(old, relative):
                    raise ValueError("BLOCKER live shared CMS drift: " + str_rel)
            elif relative in ASSET_LIVE:
                if not asset_live_allowed(old, str_rel):
                    raise ValueError("BLOCKER live asset drift: " + str_rel)
            elif relative != MAIN:
                raise ValueError("BLOCKER unexpected existing shared asset: " + str_rel)
        elif relative in expected or relative in service_footer_targets:
            raise ValueError("BLOCKER expected published PHP not found: " + str(relative))
        plan[relative] = data
    return plan

def write_atomic(target, data):
    target.parent.mkdir(parents=True, exist_ok=True)
    mode = (target.stat().st_mode & 0o777) if target.is_file() else 0o644
    fd, temp = tempfile.mkstemp(prefix='.lqv6-', dir=str(target.parent))
    try:
        with os.fdopen(fd, 'wb') as stream:
            stream.write(data)
        os.chmod(temp, mode)
        os.replace(temp, target)
        os.chmod(target, mode)
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
    backup = backup.resolve()
    m = json.loads((backup / 'manifest.json').read_text(encoding='utf-8'))
    if m.get('version') != MARK:
        raise ValueError("BLOCKER backup version mismatch")
    ops = []
    for e in m['files']:
        rel_str = e['path']
        target = manifest_target(docroot, rel_str)
        archived = (backup / rel_str).resolve()
        try:
            archived.relative_to(backup)
        except ValueError:
            raise ValueError("BLOCKER backup archive escapes backup dir: " + rel_str)
        if e.get('existed'):
            if not archived.is_file():
                raise ValueError("BLOCKER missing backup " + rel_str)
            ops.append(('restore', target, archived.read_bytes()))
        else:
            ops.append(('delete', target, None))
    for kind, target, payload in ops:
        if kind == 'restore':
            write_atomic(target, payload)
        elif target.is_file():
            target.unlink()
    print("ROLLED_BACK", backup)

def main(argv=None):
    p=argparse.ArgumentParser(description=__doc__)
    p.add_argument('--docroot',required=True)
    p.add_argument('--backup-root')
    p.add_argument('--with-tools',action='store_true',help='include 13 already published standalone tool scripts')
    p.add_argument('--with-shared',action='store_true',help='pages/*/_shared CMS fragments and /docs service footers')
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
    plan=build_plan(docroot,args.with_tools,args.with_shared)
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

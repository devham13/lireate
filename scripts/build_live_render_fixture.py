#!/usr/bin/env python3
"""Build static live-render fixtures (tests/fixtures/live-render) from a real PHP render stand.

The stand serves two docroots with PHP 5.6 + a SQLite copy of public CMS tables:
  BEFORE = live snapshot (data/db.php stubbed), AFTER = same snapshot after the installers.
This script fetches the rendered HTML, sanitises anti-spam/CSRF values, and rewrites asset
URLs so the pages open from disk:
  * AFTER CSS/JS whose content equals a file in this repo -> relative path to that repo file
    (edit the CSS in the branch, re-run the screenshot script, see the change);
  * other local CSS/JS -> vendored copies in tests/fixtures/live-render/live-assets*/
    (their url()/@import rewritten to https://lireate.ru/...);
  * images, fonts, page links -> absolute https://lireate.ru/...
Usage:
  python3 scripts/build_live_render_fixture.py --before-url http://127.0.0.1:19190 \
      --after-url http://127.0.0.1:19191 --before-root <docroot> --after-root <docroot> [--sides after]
"""
import argparse, hashlib, json, os, posixpath, re, subprocess, sys, urllib.request

REPO = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
OUT = os.path.join(REPO, 'tests', 'fixtures', 'live-render')
LIVE = 'https://lireate.ru'
PAGES = {
    'home': '/',
    'article': '/proverka_tovarnogo_znaka_pered_registraciey_chek_list_po_reestram_rospatenta/',
    'reviews': '/reviews',
    'team': '/team/',
    'portfolio': '/portfolio',
    'poshliny': '/patentnye-zadachi/poshliny-registraciya/',
    'prodlenie': '/prodlenie_tovarnogo_znaka/',
}
SECRET_INPUT = re.compile(r'(<input\b[^>]*\bname="(?:fuck_spamers|mail_token|[^"]*(?:csrf|token|antispam|spam)[^"]*)"[^>]*\bvalue=")[^"]*(")', re.I)
SECRET_INPUT2 = re.compile(r'(<input\b[^>]*\bvalue=")[^"]*("[^>]*\bname="(?:fuck_spamers|mail_token|[^"]*(?:csrf|token|antispam|spam)[^"]*)")', re.I)
SECRET_JS = re.compile(r'((?:lireateMailSpamGuard|mailToken|csrfToken|spamGuard)\s*=\s*["\'])[^"\']*(["\'])', re.I)
ATTR = re.compile(r'\b(href|src|data-src|data-bg|poster|data-href)=(["\'])(.*?)\2', re.S)
SRCSET = re.compile(r'\b(srcset|data-srcset)=(["\'])(.*?)\2', re.S)
CSSURL = re.compile(r'url\(\s*(["\']?)([^)"\']+)\1\s*\)')
IMPORT = re.compile(r'@import\s+(["\'])([^"\']+)\1')


def md5(p):
    with open(p, 'rb') as f:
        return hashlib.md5(f.read()).hexdigest()


def repo_index():
    files = subprocess.check_output(['git', '-C', REPO, 'ls-files'], text=True).split('\n')
    idx = {}
    for f in files:
        if not f.endswith(('.css', '.js')) or f.startswith('tests/fixtures/live-render/'):
            continue
        p = os.path.join(REPO, f)
        if os.path.isfile(p):
            idx.setdefault(md5(p), []).append(f)
    return idx


def pick(cands, docpath):
    """Prefer the installer source (pages/*/_shared) over deployed mirrors, then the exact path."""
    rel = docpath.lstrip('/')
    base = posixpath.basename(docpath)
    if rel in cands and rel.startswith(('template/css/', 'template/js/')):
        return rel  # overlay/home installers copy these 1:1 from the repo
    for pref in ('pages/services/_shared/', 'pages/blog/_shared/', 'pages/team/_shared/'):
        for c in cands:
            if c.startswith(pref) and posixpath.basename(c) == base:
                return c
    for c in cands:
        if c == rel:
            return c
    for c in cands:
        if c.startswith('template/') and posixpath.basename(c) == base:
            return c
    return sorted(cands, key=len)[0]


def resolve(val, page):
    v = val.strip()
    if not v or v.startswith(('#', 'mailto:', 'tel:', 'javascript:', 'data:', '//')):
        return None
    m = re.match(r'^https?://(?:www\.)?lireate\.ru(/.*)?$', v)
    if m:
        v = m.group(1) or '/'
    elif re.match(r'^[a-z][a-z0-9+.-]*:', v, re.I):
        return None
    path, sep, q = v.partition('?')
    path, hsep, frag = path.partition('#')
    if not path.startswith('/'):
        base = page if page.endswith('/') else posixpath.dirname(page) + '/'
        path = posixpath.normpath(base + path) + ('/' if path.endswith('/') else '')
    return path, (sep + q if sep else '') + (hsep + frag if hsep else '')


class Builder:
    def __init__(self, roots, idx):
        self.roots, self.idx = roots, idx
        self.manifest = {'before': {}, 'after': {}}

    def vendor(self, side, docpath):
        src = os.path.join(self.roots[side], docpath.lstrip('/'))
        other = 'after' if side == 'before' else 'before'
        op = os.path.join(self.roots[other], docpath.lstrip('/'))
        same = os.path.isfile(op) and md5(op) == md5(src)
        sub = 'live-assets' if same else 'live-assets-' + side
        dst = os.path.join(OUT, sub, docpath.lstrip('/'))
        os.makedirs(os.path.dirname(dst), exist_ok=True)
        with open(src, 'rb') as f:
            data = f.read()
        if docpath.endswith('.css'):
            txt = data.decode('utf-8', 'replace')
            def fix(m, quote_group=1, url_group=2, imp=False):
                r = resolve(m.group(url_group), docpath)
                if not r:
                    return m.group(0)
                new = LIVE + r[0] + r[1]
                return ('@import "%s"' % new) if imp else 'url("%s")' % new
            txt = CSSURL.sub(fix, txt)
            txt = IMPORT.sub(lambda m: fix(m, imp=True), txt)
            data = txt.encode('utf-8')
        with open(dst, 'wb') as f:
            f.write(data)
        return posixpath.join(sub, docpath.lstrip('/'))

    def asset(self, side, docpath):
        """Return path relative to tests/fixtures/live-render for a local css/js, or None."""
        src = os.path.join(self.roots[side], docpath.lstrip('/'))
        if not os.path.isfile(src):
            return None
        if side == 'after':
            cands = self.idx.get(md5(src))
            if cands:
                use = pick(cands, docpath)
                self.manifest[side][docpath] = {'edit': use, 'identical_copies': sorted(c for c in cands if c != use)}
                return '../../' + use
        r = self.vendor(side, docpath)
        self.manifest[side][docpath] = 'tests/fixtures/live-render/' + r
        return r

    def rewrite_url(self, side, val, page, html_dir_depth=1):
        r = resolve(val, page)
        if not r:
            return val
        path, tail = r
        if path.endswith(('.css', '.js')):
            a = self.asset(side, path)
            if a:
                rel = a[6:] if a.startswith('../../') else a
                prefix = '../' if not a.startswith('../../') else '../../../../'
                return prefix + rel + tail
        if side == 'after' and re.search(r'\.(png|jpe?g|webp|svg|gif|ico|woff2?)$', path, re.I):
            src = os.path.join(self.roots[side], path.lstrip('/'))
            if os.path.isfile(src):
                c = self.idx_all().get(md5(src))
                if c:
                    return '../../../../' + pick(c, path) + tail
        return LIVE + path + tail

    _all = None
    def idx_all(self):
        if Builder._all is None:
            files = subprocess.check_output(['git', '-C', REPO, 'ls-files'], text=True).split('\n')
            d = {}
            for f in files:
                if f.lower().endswith(('.png', '.jpg', '.jpeg', '.webp', '.svg', '.gif', '.ico', '.woff', '.woff2')) and not f.startswith('tests/fixtures/'):
                    p = os.path.join(REPO, f)
                    if os.path.isfile(p):
                        d.setdefault(md5(p), []).append(f)
            Builder._all = d
        return Builder._all

    def page(self, side, base_url, name, url):
        req = urllib.request.Request(base_url + url, headers={'Host': 'lireate.ru'})
        try:
            html = urllib.request.urlopen(req, timeout=120).read().decode('utf-8', 'replace')
        except urllib.error.HTTPError as e:  # legacy CMS answers 404 status on some articles but renders the page
            html = e.read().decode('utf-8', 'replace')
        html = SECRET_INPUT.sub(r'\1FIXTURE\2', html)
        html = SECRET_INPUT2.sub(r'\1FIXTURE\2', html)
        html = SECRET_JS.sub(r'\1FIXTURE\2', html)
        html = ATTR.sub(lambda m: '%s=%s%s%s' % (m.group(1), m.group(2), self.rewrite_url(side, m.group(3), url), m.group(2)), html)
        def srcset(m):
            parts = []
            for item in m.group(3).split(','):
                bits = item.strip().split()
                if bits:
                    bits[0] = self.rewrite_url(side, bits[0], url)
                parts.append(' '.join(bits))
            return '%s=%s%s%s' % (m.group(1), m.group(2), ', '.join(parts), m.group(2))
        html = SRCSET.sub(srcset, html)
        html = CSSURL.sub(lambda m: 'url(%s%s%s)' % (m.group(1), self.rewrite_url(side, m.group(2), url), m.group(1)), html)
        html = ('<!-- live-render fixture: %s %s (rendered from PHP 5.6 stand; see README.md) -->\n' % (side.upper(), url)) + html
        d = os.path.join(OUT, side)
        os.makedirs(d, exist_ok=True)
        with open(os.path.join(d, name + '.html'), 'w', encoding='utf-8') as f:
            f.write(html)


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument('--before-url', default='http://127.0.0.1:19190')
    ap.add_argument('--after-url', default='http://127.0.0.1:19191')
    ap.add_argument('--before-root', required=True)
    ap.add_argument('--after-root', required=True)
    ap.add_argument('--sides', default='before,after')
    a = ap.parse_args()
    b = Builder({'before': a.before_root, 'after': a.after_root}, repo_index())
    for side in a.sides.split(','):
        for name, url in PAGES.items():
            b.page(side, a.before_url if side == 'before' else a.after_url, name, url)
            print(side, name, 'ok')
    mpath = os.path.join(OUT, 'asset-map.json')
    old = {}
    if os.path.isfile(mpath):
        old = json.load(open(mpath))
    old.update({k: dict(sorted(v.items())) for k, v in b.manifest.items() if v})
    with open(mpath, 'w') as f:
        json.dump(old, f, ensure_ascii=False, indent=1, sort_keys=True)
        f.write('\n')


if __name__ == '__main__':
    sys.exit(main())

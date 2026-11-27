#!/usr/bin/env python3
"""Apply the reviewed mobile fixes on the hosting, with backups and PHP lint.

Only named template assets and managed service entry points are changed.
No database, credentials, analytics IDs, form handlers or site text are replaced.
"""
import argparse
import base64
import hashlib
import json
import os
from pathlib import Path
import re
import shutil
import subprocess
import tempfile
from urllib.parse import urljoin, urlsplit
from datetime import datetime, timezone
from lireate_blog_install_lib import resolve_site_php_bin

REPO = Path(__file__).resolve().parents[1]
VERSION = 'seo-perf-20261127-v2'
HEAD = ('\n<!-- ' + VERSION + ' -->\n'
        '<meta name="lireate-performance-patch" content="' + VERSION + '">\n'
        '<link rel="stylesheet" href="/template/css/lireate-performance.css?v=' + VERSION + '">\n'
        '<script defer src="/template/js/lireate-performance.js?v=' + VERSION + '"></script>\n')
MARKER_BLOCK = re.compile(
    r'\n?<!-- (?P<ver>seo-perf-\d{8}-v\d+) -->\s*'
    r'<meta name="lireate-performance-patch" content="(?P=ver)">\s*'
    r'<link rel="stylesheet" href="/template/css/lireate-performance\.css\?v=(?P=ver)">\s*'
    r'<script defer src="/template/js/lireate-performance\.js\?v=(?P=ver)"></script>\n?',
    re.I)
EARLY_MARK = VERSION + '-early'
HERO_WEBP = 'template/images/home/gabdrakhmanova-elvira-2026-11.webp'
EARLY = ('\n<!-- ' + EARLY_MARK + ' -->\n'
         "<?php if (isset($temp['page']) && $temp['page'] === 'home') { ?>\n"
         '<link rel="preload" as="image" href="/' + HERO_WEBP + '" type="image/webp" fetchpriority="high">\n'
         "<?php } ?>\n")
HOME_FONT = (
    '<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800'
    '&family=Inter+Tight:wght@500;600;700;800&display=swap" rel="stylesheet">'
)
HOME_FONT_PATHS = (
    Path('template/page/home/lireate-home-page.inc.php'),
    Path('pages/home/_shared/lireate-home-page.inc.php'),
)
VK_PAIR = re.compile(
    r'(<script\b[^>]*\bsrc=["\'](?:https?:)?//vk\.com/js/api/openapi\.js\?\d+["\'][^>]*>\s*</script>)'
    r'\s*<script\b[^>]*>\s*(VK\.init\(\s*\{[^}]*\}\s*\)\s*;)\s*</script>', re.I)
COVER_ANCHOR = "$lb_cover_html = isset($lireate_blog_cover_html) ? $lireate_blog_cover_html : '';"
COVER_FIX = ("\nrequire_once __DIR__ . '/lireate-blog-performance.inc.php';"
             "\n$lb_cover_html = lireate_blog_performance_cover($lb_cover_html);")
LINK = re.compile(r'<link\b[^>]*>', re.I)
BUNDLE = re.compile(r'<!--lireate-performance-css-v1:([A-Za-z0-9+/=]+)--><link\b[^>]*data-lireate-css-bundle="1"[^>]*>', re.I)


def bundle_styles(text, docroot):
    """Combine adjacent static local styles, preserving cascade and relative URLs.

    Source tags stay encoded in a comment so the next CRON can rebuild bundles
    after CSS updates. PHP conditionals, remote styles, imports and media stay intact.
    """
    text = BUNDLE.sub(lambda m: base64.b64decode(m.group(1)).decode('utf-8'), text)
    assets = {}

    def stylesheet(tag):
        attrs = dict((k.lower(), v) for k, _, v in re.findall(r'([\w-]+)\s*=\s*(["\'])(.*?)\2', tag, re.S))
        if attrs.get('rel', '').lower() != 'stylesheet' or attrs.get('media', 'all').lower() != 'all':
            return None
        if any(k in attrs for k in ['integrity', 'title', 'disabled', 'onload']):
            return None
        if set(attrs) - {'rel', 'href', 'type', 'media'} or re.search(r'\sdisabled\b', tag, re.I):
            return None
        href = attrs.get('href', '')
        if href.startswith('{theme_url}/'):
            href = '/template/' + href[len('{theme_url}/'):]
        parsed = urlsplit(href)
        if parsed.netloc and parsed.netloc not in ['lireate.ru', 'www.lireate.ru']:
            return None
        if not parsed.path.startswith('/template/') or not parsed.path.endswith('.css'):
            return None
        if 'lireate-performance' in parsed.path or 'lireate-bundle-' in parsed.path:
            return None
        path = safe_path(docroot, parsed.path.lstrip('/'))
        if not path.is_file():
            return None
        css = path.read_text(encoding='utf-8')
        if re.search(r'@import\b', css, re.I):
            return None
        css = re.sub(r'^\s*@charset\s+["\'][^"\']+["\'];', '', css, flags=re.I)
        def resource(match):
            value = match.group(2).strip()
            if value.startswith(('/', '#', 'data:')) or urlsplit(value).scheme:
                return match.group(0)
            return 'url("' + urljoin(parsed.path, value) + '")'
        css = re.sub(r'url\(\s*(["\']?)([^)"\']+)\1\s*\)', resource, css, flags=re.I)
        return '\n/* ' + parsed.path + ' */\n' + css

    head_end = re.search(r'</head\s*>', text, re.I)
    limit = head_end.start() if head_end else 0
    blocked = [(m.start(), m.end()) for m in re.finditer(
        r'<!--.*?-->|<script\b[^>]*>.*?</script\s*>|<\?.*?\?>', text[:limit], re.I | re.S)]
    matches = [m for m in LINK.finditer(text[:limit])
               if not any(start <= m.start() < end for start, end in blocked)]
    groups = []
    current = []
    for match in matches:
        css = stylesheet(match.group(0))
        gap = text[current[-1][0].end():match.start()] if current else ''
        if current and (css is None or re.sub(r'<!--.*?-->', '', gap, flags=re.S).strip()):
            groups.append(current)
            current = []
        if css is not None:
            current.append((match, css))
    if current:
        groups.append(current)
    for group in reversed(groups):
        if len(group) < 2:
            continue
        start, end = group[0][0].start(), group[-1][0].end()
        source = text[start:end]
        css = '\n'.join(item[1] for item in group).encode('utf-8')
        relative = Path('template/css/lireate-bundle-' + digest(css)[:16] + '.css')
        assets[relative] = css
        original = base64.b64encode(source.encode('utf-8')).decode('ascii')
        tag = ('<!--lireate-performance-css-v1:' + original + '-->'
               '<link rel="stylesheet" href="/' + str(relative) + '" data-lireate-css-bundle="1">')
        text = text[:start] + tag + text[end:]
    return text, assets


def digest(data):
    return hashlib.sha256(data).hexdigest()


def patch_head(text, defer_vk=False):
    if defer_vk:
        def pair(match):
            tag, init = match.groups()
            if re.search(r'\b(?:async|defer)\b', tag, re.I):
                return match.group(0)
            tag = tag.replace('<script', '<script defer', 1)
            return (tag + '\n<script>document.addEventListener("DOMContentLoaded", function () {'
                    ' if (window.VK && window.VK.init) { ' + init + ' } });</script>')
        text = VK_PAIR.sub(pair, text)

    def keep_current(match):
        if match.group('ver') == VERSION:
            return match.group(0)
        return ''

    text = MARKER_BLOCK.sub(keep_current, text)
    if VERSION not in text:
        if not re.search(r'</head\s*>', text, re.I):
            raise ValueError('Expected closing head tag is missing')
        text = re.sub(r'</head\s*>', lambda m: HEAD + m.group(0), text, count=1, flags=re.I)
    return text


def uncommented(text):
    return re.sub(r'<!--.*?-->', '', text, flags=re.S)


def fix_og_image(text):
    """Point og:image at the file that actually exists."""
    text = re.sub(
        r'(?<!/template)(/images/OG_image\.jpg)',
        '/template/images/OG_image.jpg',
        text)
    return text.replace(
        '/local/templates/lireate/images/og-default.jpg',
        '/template/images/OG_image.jpg')


def relax_google_fonts(text):
    """Stop legacy Google Fonts stylesheets from blocking the first paint."""
    text = text.replace('media="print" / onload', 'media="print" onload')
    text = text.replace("media='print' / onload", "media='print' onload")
    pattern = re.compile(
        r'<link\b[^>]*href=(["\'])([^"\']*fonts\.googleapis\.com[^"\']*)\1[^>]*>',
        re.I)

    def repl(match):
        if text.rfind('<noscript>', 0, match.start()) > text.rfind('</noscript>', 0, match.start()):
            return match.group(0)
        tag = match.group(0)
        quote = match.group(1)
        href = match.group(2)
        if '/css' not in href or re.search(r'\brel\s*=\s*["\']preconnect["\']', tag, re.I):
            return match.group(0)
        if 'display=' not in href:
            href = href + ('&' if '?' in href else '?') + 'display=swap'
            tag = tag.replace(match.group(2), href, 1)
        if re.search(r'\bonload\s*=', tag, re.I):
            return tag

        def with_attrs(source, extra):
            return re.sub(r'\s*/?\s*>$', '', source.rstrip()) + extra + '>'

        if re.search(r'\bmedia\s*=', tag, re.I):
            tag = re.sub(r'\smedia=(["\']).*?\1', ' media="print"', tag, count=1, flags=re.I)
            tag = with_attrs(tag, ' onload="this.media=\'all\'"')
        else:
            tag = with_attrs(tag, ' media="print" onload="this.media=\'all\'"')
        return tag + '<noscript><link rel="stylesheet" href=' + quote + href + quote + '></noscript>'

    return pattern.sub(repl, text)


def remove_unused_styles(text):
    visible = uncommented(text)
    without_swiper_link = re.sub(r'<link\b[^>]*swiper-bundle\.min\.css[^>]*>', '', visible, flags=re.I)
    if 'swiper-bundle.min.css' in text and 'swiper' not in without_swiper_link.lower():
        text = re.sub(
            r'\n?[ \t]*<link\b[^>]*href=["\']https://unpkg\.com/swiper/swiper-bundle\.min\.css["\'][^>]*>\n?',
            '\n', text, count=1)
    if 'cdn.callbackkiller.com/widget/cbk.css' in text and 'cbk.js' not in uncommented(text):
        text = re.sub(
            r'\n?[ \t]*<link\b[^>]*href=["\']https://cdn\.callbackkiller\.com/widget/cbk\.css["\'][^>]*>\n?',
            '\n', text, count=1)
    return text


def fix_legacy_markup(text):
    text = text.replace(
        'Регистрация товарного знака с оплатой за результат</h>',
        'Регистрация товарного знака с оплатой за результат</h3>',
        1)
    text = text.replace(
        '<h1>Патентное бюро «Лирейт»</h1>',
        '<p class="lireate-legacy-hero-title">Патентное бюро «Лирейт»</p>',
        1)
    return text


def inject_early(text, docroot):
    if EARLY_MARK in text:
        return text
    hero = safe_path(docroot, HERO_WEBP)
    if not hero.is_file():
        return text
    viewport = re.search(r'<meta\b[^>]*name=["\']viewport["\'][^>]*>', text, re.I)
    if viewport:
        at = viewport.end()
    else:
        head = re.search(r'<head\b[^>]*>', text, re.I)
        if not head:
            return text
        at = head.end()
    return text[:at] + EARLY + text[at:]


def relax_home_font(text):
    if HOME_FONT not in text:
        return text
    href = HOME_FONT.split('href="', 1)[1].split('"', 1)[0]
    return text.replace(
        HOME_FONT,
        '<link href="' + href + '" rel="stylesheet" media="print" onload="this.media=\'all\'">'
        '<noscript><link rel="stylesheet" href="' + href + '" media="all"></noscript>',
        1)


def fix_og_protocol(text):
    return re.sub(
        r'(<meta\b[^>]*\bproperty=["\']og:image["\'][^>]*\bcontent=["\'])http://',
        r'\1https://',
        text,
        flags=re.I)


def tune_service(text):
    """Fixes confirmed on published longreads: a 404 social image and a desktop
    shift when the shared sidebar is inserted after the first paint.
    Patent tools already print their sidebar in the HTML, so they keep their layout.
    """
    text = text.replace(
        '/local/templates/lireate/images/og-default.jpg',
        '/template/images/OG_image.jpg')
    text = relax_google_fonts(text)
    if ('lsv7-managed' in text or 'leftSideBar' in text
            or 'lireate-patent-tasks-sidebar' in text or 'screenshot-capture' in text):
        return text

    def add(match):
        tag = match.group(0)
        if re.search(r'\bclass\s*=', tag, re.I):
            return re.sub(r'(\bclass\s*=\s*["\'])', r'\1lsv7-managed ', tag, count=1, flags=re.I)
        return tag[:-1] + ' class="lsv7-managed">'

    return re.sub(r'<body\b[^>]*>', add, text, count=1, flags=re.I)


def tune_shell(text, docroot):
    text = fix_og_protocol(fix_og_image(text))
    text = relax_google_fonts(text)
    text = remove_unused_styles(text)
    text = fix_legacy_markup(text)
    text = text.replace('console.log(topBarButtonsDiv);', '')
    return inject_early(text, docroot)


def patch_cover_layout(text):
    if 'lireate_blog_performance_cover($lb_cover_html)' in text:
        return text
    if COVER_ANCHOR not in text:
        raise ValueError('Expected blog cover anchor is missing')
    return text.replace(COVER_ANCHOR, COVER_ANCHOR + COVER_FIX, 1)


def safe_path(docroot, relative):
    path = docroot / relative
    try:
        path.resolve().relative_to(docroot.resolve())
    except ValueError:
        raise ValueError('Path leaves docroot: ' + str(relative))
    if path.is_symlink():
        raise ValueError('Refusing to replace a symlink: ' + str(relative))
    return path


def build_plan(docroot):
    plan = {}
    notes = []
    for rel in ['template/css/lireate-performance.css', 'template/js/lireate-performance.js']:
        plan[Path(rel)] = (REPO / rel).read_bytes()
    for source in sorted((REPO / 'template/images/blog').glob('*.webp')):
        # Only the four reviewed covers, not the author assets or arbitrary files.
        if source.stem.replace('-430', '') in {
            'registraciya-brenda-odezhdy-mktu', 'kak-zapatentovat-nazvanie-i-logotip',
            'proverka_tovarnogo_znaka_pered_registraciey_chek_list_po_reestram_rospatenta',
            'rospatent-fips-proverit-tovarnyy-znak-onlayn-besplatno',
        }:
            plan[source.relative_to(REPO)] = source.read_bytes()
    main_rel = Path('template/main.php')
    main = safe_path(docroot, main_rel)
    if not main.is_file():
        raise ValueError('CMS template/main.php not found; no files will be changed')
    # Inline widgets on other templates might depend on synchronous VK. In that
    # case keep the SDK order rather than risk breaking an unreviewed widget.
    widgets = any('VK.Widgets.' in p.read_text(encoding='utf-8', errors='replace')
                  for p in (docroot / 'template').rglob('*.php'))
    if widgets:
        notes.append('VK defer skipped: inline VK widgets require a separate review')
    original = main.read_text(encoding='utf-8')
    bundled, assets = bundle_styles(original, docroot)
    plan.update(assets)
    changed = tune_shell(patch_head(bundled, defer_vk=not widgets), docroot)
    plan[main_rel] = changed.encode('utf-8')
    for root_name in ('data', 'template'):
        folder = docroot / root_name
        if not folder.is_dir():
            continue
        for path in folder.rglob('*.php'):
            if not path.is_file() or path.is_symlink():
                continue
            rel = path.resolve().relative_to(docroot.resolve())
            if rel == main_rel or rel in plan:
                continue
            raw = path.read_bytes()
            try:
                updated = fix_legacy_markup(fix_og_image(raw.decode('utf-8')))
            except UnicodeError:
                continue
            if updated.encode('utf-8') != raw:
                plan[rel] = updated.encode('utf-8')
    skip_root_slugs = frozenset({'findtz', 'patentnye-zadachi'})
    for path in sorted(docroot.glob('*/index.php')):
        if not path.is_file() or path.is_symlink():
            continue
        try:
            rel = path.resolve().relative_to(docroot.resolve())
        except ValueError:
            continue
        if rel.parts and rel.parts[0] in skip_root_slugs:
            continue
        if rel == main_rel or rel in plan:
            continue
        raw = path.read_bytes()
        try:
            updated = fix_legacy_markup(fix_og_image(raw.decode('utf-8')))
        except UnicodeError:
            continue
        if updated.encode('utf-8') != raw:
            plan[rel] = updated.encode('utf-8')
    for rel in HOME_FONT_PATHS:
        home_inc = safe_path(docroot, rel)
        if not home_inc.is_file() and rel not in plan:
            continue
        raw = plan[rel] if rel in plan else home_inc.read_bytes()
        updated = relax_home_font(raw.decode('utf-8'))
        if updated.encode('utf-8') != raw:
            plan[rel] = updated.encode('utf-8')
    layouts_found = 0
    for layout_rel in [Path('pages/blog/_shared/lireate-blog-layout.inc.php'),
                       Path('template/page/news/lireate-blog-layout.inc.php')]:
        layout = safe_path(docroot, layout_rel)
        if not layout.is_file():
            continue
        layouts_found += 1
        try:
            plan[layout_rel] = patch_cover_layout(layout.read_text(encoding='utf-8')).encode('utf-8')
            helper = Path('pages/blog/_shared/lireate-blog-performance.inc.php')
            plan[layout_rel.parent / helper.name] = (REPO / helper).read_bytes()
        except ValueError as exc:
            notes.append('Blog cover patch skipped: ' + str(exc))
    if not layouts_found:
        notes.append('Blog layout not found; cover patch skipped')
    for manifest in sorted((REPO / 'pages/services').glob('*/publish.manifest.json')):
        data = json.loads(manifest.read_text(encoding='utf-8'))
        url = data.get('url_path', '').strip('/')
        if not url:
            continue
        rel = Path(url) / 'index.php'
        path = safe_path(docroot, rel)
        if path.is_file() or rel in plan:
            text = plan[rel].decode('utf-8') if rel in plan else path.read_text(encoding='utf-8')
            if 'lireate-service-dark.css' in text and re.search(r'</head\s*>', text, re.I):
                plan[rel] = patch_head(tune_service(text)).encode('utf-8')
    return {rel: data for rel, data in plan.items()
            if not safe_path(docroot, rel).is_file() or safe_path(docroot, rel).read_bytes() != data}, notes


def stage_file(path, data, mode=0o644):
    path.parent.mkdir(parents=True, exist_ok=True)
    fd, name = tempfile.mkstemp(prefix='.lireate-performance-', suffix=path.suffix, dir=str(path.parent))
    with os.fdopen(fd, 'wb') as stream:
        stream.write(data)
    os.chmod(name, mode)
    return Path(name)


def apply_plan(docroot, plan, backup_root):
    php = shutil.which(resolve_site_php_bin())
    if not php:
        raise ValueError('PHP CLI is required to lint the staged PHP before applying')
    staged = {}
    originals = {}
    modes = {}
    try:
        for rel, data in plan.items():
            path = safe_path(docroot, rel)
            originals[rel] = path.read_bytes() if path.is_file() else None
            modes[rel] = (path.stat().st_mode & 0o777) if path.exists() else 0o644
            staged[rel] = stage_file(path, data, modes[rel])
            if path.suffix == '.php':
                result = subprocess.run(
                    [php, '-l', str(staged[rel])],
                    stdout=subprocess.PIPE,
                    stderr=subprocess.PIPE,
                    universal_newlines=True,
                )
                if result.returncode:
                    raise ValueError('PHP lint failed: ' + str(rel) + '\n' + result.stdout + result.stderr)
        stamp = datetime.now(timezone.utc).strftime('%Y%m%dT%H%M%S%fZ')
        backup = backup_root / stamp
        backup.mkdir(parents=True, mode=0o700)
        records = []
        for rel, before in originals.items():
            if before is not None:
                target = backup / rel
                target.parent.mkdir(parents=True, exist_ok=True)
                target.write_bytes(before)
            records.append({'path': str(rel), 'existed': before is not None,
                            'mode': modes[rel],
                            'before': digest(before) if before is not None else None,
                            'after': digest(plan[rel])})
        (backup / 'manifest.json').write_text(json.dumps({'version': VERSION, 'files': records}, indent=2))
        applied = []
        try:
            # Publish assets and PHP helpers before templates refer to them.
            templates = {'main.php', 'index.php', 'lireate-blog-layout.inc.php'}
            for rel in sorted(plan, key=lambda p: (p.name in templates, str(p))):
                os.replace(str(staged[rel]), str(safe_path(docroot, rel)))
                applied.append(rel)
        except Exception:
            for rel in reversed(applied):
                target = safe_path(docroot, rel)
                if originals[rel] is None:
                    target.unlink()
                else:
                    os.replace(str(stage_file(target, originals[rel], modes[rel])), str(target))
            raise
        return backup
    finally:
        for path in staged.values():
            if path.exists():
                path.unlink()


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--docroot', type=Path, required=True)
    parser.add_argument('--backup-root', type=Path, required=True)
    parser.add_argument('--apply', action='store_true')
    args = parser.parse_args()
    plan, notes = build_plan(args.docroot.resolve())
    for note in notes:
        print('WARN: ' + note)
    print('performance_patch: {0}, changed_files: {1}'.format(VERSION, len(plan)))
    for path in plan:
        print('  ' + str(path))
    if plan and args.apply:
        backup = apply_plan(args.docroot.resolve(), plan, args.backup_root)
        print('STATUS: PERFORMANCE_PATCH_APPLIED; backup: ' + str(backup))
    elif not plan:
        print('STATUS: PERFORMANCE_PATCH_UP_TO_DATE')
    else:
        print('STATUS: PERFORMANCE_PATCH_DRY_RUN')
    return 0


if __name__ == '__main__':
    raise SystemExit(main())

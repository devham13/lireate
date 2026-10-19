"""Blog template install helpers (used by lireate_blog_install.py and tests)."""

import difflib
import json
import re
import shutil
from datetime import datetime, timezone
from pathlib import Path

INSTALL_MARKER = "lireate-blog-install:v2"
INSTALL_MARKERS_LEGACY = (
    "lireate-blog-install:v2",
    "lireate-blog-install:v1",
)
BODY_CLASS = "lireate-blog-body"
CMS_INCLUDE = "/template/page/news/lireate-blog-cms.inc.php"

SKIP_PATH_PARTS = {"data", "db.php"}
FORBIDDEN_TOUCH = {".htaccess"}

LEGACY_BLOCK_RE = re.compile(
    r"<h1\b[^>]*>.*?</h1>\s*"
    r"(?:<div\s+id=[\"']articleInfo[\"'][^>]*>.*?</div>\s*)?"
    r"(?:<a\s+[^>]*class=[\"'][^\"']*returnBlog[^\"']*[\"'][^>]*>.*?</a>\s*)?"
    r"<div\s+id=[\"']textBox[\"'][^>]*>.*?</div>",
    re.DOTALL | re.IGNORECASE,
)

BODY_RE = re.compile(r"<body\b([^>]*)>", re.IGNORECASE)


def load_env_file(env_path):
    from lireate_dotenv import load_dotenv

    load_dotenv(env_path, override=False)


def resolve_paths(office_repo, env_file):
    office_repo = Path(office_repo).resolve()
    env_file = Path(env_file).expanduser().resolve()
    autopub_home = Path(
        __import__("os").environ.get("LIREATE_AUTOPUB_HOME") or env_file.parent
    ).expanduser()
    docroot = __import__("os").environ.get("LIREATE_DOCROOT", "").strip()
    if not docroot:
        raise SystemExit("BLOCKER: LIREATE_DOCROOT not set in {0}".format(env_file))
    docroot = Path(docroot).expanduser().resolve()
    if not docroot.is_dir():
        raise SystemExit("BLOCKER: LIREATE_DOCROOT is not a directory: {0}".format(docroot))
    backups_root = autopub_home / "backups"
    return office_repo, docroot, backups_root, env_file


def copy_plan(office_repo):
    """Files to copy from office-repo into docroot (relative paths)."""
    by_dest = {}
    repo = office_repo
    news_dir = repo / "template" / "page" / "news"
    if news_dir.is_dir():
        for path in sorted(news_dir.glob("lireate-blog*")):
            if path.is_file():
                by_dest[Path("template/page/news") / path.name] = path
        cms = news_dir / "lireate-blog-cms.inc.php"
        if cms.is_file():
            by_dest[Path("template/page/news/lireate-blog-cms.inc.php")] = cms
    lireate_dir = repo / "template" / "lireate"
    if lireate_dir.is_dir():
        for name in ("lireate-service-footer.inc.php", "lireate-messenger-urls.php"):
            src = lireate_dir / name
            if src.is_file():
                by_dest[Path("template/lireate") / name] = src
    shared_blog = repo / "pages" / "blog" / "_shared"
    if shared_blog.is_dir():
        for path in sorted(shared_blog.glob("*")):
            if path.is_file() and path.suffix in (".php", ".css", ".js"):
                by_dest[Path("pages/blog/_shared") / path.name] = path
    index = repo / "data" / "blog-articles-index.json"
    if index.is_file():
        by_dest[Path("data/blog-articles-index.json")] = index
    author_dir = repo / "template" / "images" / "blog" / "author"
    if author_dir.is_dir():
        for path in sorted(author_dir.iterdir()):
            if path.is_file():
                by_dest[Path("template/images/blog/author") / path.name] = path
    return [(src, rel) for rel, src in sorted(by_dest.items(), key=lambda x: str(x[0]))]


def _iter_php_under_template(docroot):
    template = docroot / "template"
    if not template.is_dir():
        return
    for path in template.rglob("*.php"):
        if any(p in FORBIDDEN_TOUCH for p in path.parts):
            continue
        if path.name == "db.php":
            continue
        rel = path.relative_to(docroot)
        if "data" in rel.parts and path.name == "db.php":
            continue
        yield path


def find_news_template(docroot):
    """Locate CMS PHP that renders single news (full_story / textBox)."""
    candidates = []
    for path in _iter_php_under_template(docroot):
        try:
            text = path.read_text(encoding="utf-8", errors="replace")
        except OSError:
            continue
        if INSTALL_MARKER in text or "lireate-blog-install:v1" in text:
            score = 100
        elif "news_article[0]" in text:
            score = 90
        elif 'id="textBox"' in text or "id='textBox'" in text:
            score = 80
        elif "full_story" in text:
            score = 60
        elif "articleInfo" in text:
            score = 40
        else:
            continue
        if path.name == "newStyle.php":
            score += 20
        if "template/page/news" in str(path).replace("\\", "/"):
            score += 5
        candidates.append((score, path))
    if not candidates:
        return None
    candidates.sort(key=lambda x: (-x[0], len(str(x[1]))))
    return candidates[0][1]


def _replacement_block_legacy():
    return (
        "<?php /* %s */ ?>\n"
        "<?php\n"
        "if (!isset($lireate_news_row) || !is_array($lireate_news_row)) {\n"
        "    $lireate_news_row = array(\n"
        "        'name' => isset($name) ? $name : (isset($news['name']) ? $news['name'] : ''),\n"
        "        'full_story' => isset($full_story) ? $full_story : (isset($news['full_story']) ? $news['full_story'] : ''),\n"
        "        'date' => isset($date) ? $date : (isset($news['date']) ? $news['date'] : ''),\n"
        "        'category_name' => isset($category_name) ? $category_name : (isset($news['category_name']) ? $news['category_name'] : ''),\n"
        "        'category_url' => isset($category_url) ? $category_url : '/news/',\n"
        "        'seo_url' => isset($seo_url) ? $seo_url : (isset($news['seo_url']) ? $news['seo_url'] : ''),\n"
        "    );\n"
        "}\n"
        "$lireate_blog_cover_html = '';\n"
        "require $_SERVER['DOCUMENT_ROOT'] . '%s';\n"
        "?>\n"
    ) % (INSTALL_MARKER, CMS_INCLUDE)


def _replacement_block_newstyle():
    return (
        "<?php /* %s */ ?>\n"
        "<?php\n"
        "$_lb_art = (isset($news_article[0]) && is_array($news_article[0])) ? $news_article[0] : array();\n"
        "$_lb_seo = '';\n"
        "if (!empty($_lb_art['seo_url'])) {\n"
        "    $_lb_seo = $_lb_art['seo_url'];\n"
        "} elseif (!empty($_SERVER['REQUEST_URI'])) {\n"
        "    $_lb_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);\n"
        "    $_lb_seo = $_lb_path !== null ? trim($_lb_path, '/') : '';\n"
        "}\n"
        "$_lb_story = isset($_lb_art['full_story']) ? $_lb_art['full_story'] : '';\n"
        "$lireate_blog_cover_html = '';\n"
        "if (preg_match('/<img[^>]+src=\"([^\"]*\\\\/template\\\\/images\\\\/blog\\\\/[^\"]+)\"/i', $_lb_story, $_lb_m)) {\n"
        "    $lireate_blog_cover_html = '<img src=\"' . htmlspecialchars($_lb_m[1], ENT_QUOTES, 'UTF-8') . '\" alt=\"\" class=\"lireate-blog-cover-img\">';\n"
        "    $_lb_story = preg_replace('/<img[^>]+src=\"[^\"]*\\\\/template\\\\/images\\\\/blog\\\\/[^\"]+\"[^>]*>/i', '', $_lb_story, 1);\n"
        "}\n"
        "$lireate_news_row = array(\n"
        "    'name' => isset($_lb_art['name']) ? $_lb_art['name'] : '',\n"
        "    'full_story' => $_lb_story,\n"
        "    'date' => !empty($_lb_art['datetime']) ? date('d.m.Y', (int) $_lb_art['datetime']) : '',\n"
        "    'category_name' => isset($category) ? $category : '',\n"
        "    'category_url' => isset($catUrl) ? $catUrl : '/news/',\n"
        "    'seo_url' => $_lb_seo,\n"
        ");\n"
        "require $_SERVER['DOCUMENT_ROOT'] . '%s';\n"
        "?>\n"
    ) % (INSTALL_MARKER, CMS_INCLUDE)


def _patch_is_current(text):
    if INSTALL_MARKER not in text:
        return False
    if "news_article[0]" in text:
        return "$_lb_art" in text
    return True


def _strip_broken_install_blocks(text):
    """Remove a partial v1 inject so newStyle can be re-patched."""
    if 'id="textBox"' not in text and "id='textBox'" not in text:
        return text
    for marker in INSTALL_MARKERS_LEGACY:
        if marker not in text:
            continue
        if "$_lb_art" in text:
            return text
        stripped = re.sub(
            r"<\?php\s*/\*\s*" + re.escape(marker) + r"\s*\*/\s*\?>.*?lireate-blog-cms\.inc\.php';\s*\?>\s*",
            "",
            text,
            count=1,
            flags=re.DOTALL | re.IGNORECASE,
        )
        if stripped != text:
            return stripped
    return text


def _span_newstyle_article_block(text):
    if "news_article[0]" not in text:
        return None
    h1 = re.search(r"<h1\b", text, re.IGNORECASE)
    if not h1:
        return None
    rel_start = h1.start()
    tbox = re.search(
        r'<div\s+id=["\']textBox["\'][^>]*>',
        text[rel_start:],
        re.IGNORECASE,
    )
    if not tbox:
        return None
    open_end = rel_start + tbox.end()
    depth = 1
    pos = open_end
    lower = text.lower()
    while pos < len(text) and depth > 0:
        next_open = lower.find("<div", pos)
        next_close = lower.find("</div>", pos)
        if next_close == -1:
            return None
        if next_open != -1 and next_open < next_close:
            depth += 1
            pos = next_open + 4
        else:
            depth -= 1
            end = next_close + len("</div>")
            if depth == 0:
                return (rel_start, end)
            pos = next_close + 6
    return None


def patch_news_template_content(text):
    """Return (new_text, changed, description)."""
    if _patch_is_current(text):
        return text, False, "already patched (marker present)"
    text = _strip_broken_install_blocks(text)
    span = _span_newstyle_article_block(text)
    if span:
        repl = _replacement_block_newstyle()
        new_text = text[: span[0]] + repl + text[span[1] :]
        return new_text, new_text != text, "replaced newStyle block (h1 through #textBox)"
    repl = _replacement_block_legacy()
    if LEGACY_BLOCK_RE.search(text):
        new_text = LEGACY_BLOCK_RE.sub(repl, text, count=1)
        return new_text, new_text != text, "replaced legacy h1/articleInfo/textBox block"
    return text, False, "article block not found"


def html_tags_balanced(fragment, tags=("div", "section")):
    for tag in tags:
        opens = len(re.findall(r"<" + tag + r"\b", fragment, re.IGNORECASE))
        closes = len(re.findall(r"</" + tag + r">", fragment, re.IGNORECASE))
        if opens != closes:
            return False
    return True


def patch_body_class_content(text):
    """Add lireate-blog-body to <body> tag if missing."""
    if not BODY_RE.search(text):
        return text, False

    def body_repl(match):
        attrs = match.group(1)
        if BODY_CLASS in attrs:
            return match.group(0)
        if re.search(r'\bclass\s*=\s*["\']', attrs, re.I):
            new_attrs = re.sub(
                r'class\s*=\s*(["\'])([^"\']*)(["\'])',
                lambda cm: "class={0}{1} {2}{3}".format(
                    cm.group(1), cm.group(2).rstrip(), BODY_CLASS, cm.group(3)
                ),
                attrs,
                count=1,
                flags=re.I,
            )
            return "<body{0}>".format(new_attrs)
        return "<body class=\"{0}\"{1}>".format(BODY_CLASS, attrs)

    new_text = BODY_RE.sub(body_repl, text, count=1)
    return new_text, new_text != text


def plan_template_content(text):
    """Apply news + body patches for preview (idempotent)."""
    text, _, _ = patch_news_template_content(text)
    text, _ = patch_body_class_content(text)
    return text


def apply_template_patch(docroot, template_path):
    """Write patched template; return (changed, notes)."""
    rel = template_path.relative_to(docroot)
    if "data" in rel.parts and template_path.name == "db.php":
        raise SystemExit("BLOCKER: refusing to patch data/db.php")
    if template_path.name == ".htaccess" and template_path.parent == docroot:
        raise SystemExit("BLOCKER: refusing to patch docroot .htaccess")
    original = template_path.read_text(encoding="utf-8", errors="replace")
    planned = plan_template_content(original)
    if planned == original:
        return False, ["template already up to date"]
    notes = []
    if INSTALL_MARKER not in original:
        notes.append("cms include (lireate-blog-cms.inc.php)")
    if BODY_CLASS not in original and BODY_CLASS in planned:
        notes.append("body class {0}".format(BODY_CLASS))
    template_path.write_text(planned, encoding="utf-8")
    return True, notes


def apply_copies(office_repo, docroot, pairs):
    """Copy files into docroot; return list of newly created relative paths."""
    created = []
    for src, rel in pairs:
        if rel.parts == ("data", "db.php") or rel == Path(".htaccess"):
            raise SystemExit("BLOCKER: forbidden destination {0}".format(rel))
        dst = docroot / rel
        existed = dst.is_file()
        dst.parent.mkdir(parents=True, exist_ok=True)
        shutil.copy2(str(src), str(dst))
        if not existed:
            created.append(rel)
    return created


def site_base_url():
    import os

    for key in (
        "LIREATE_PUBLIC_SITE_URL",
        "PUBLIC_SITE_URL",
        "LIREATE_BLOG_PUBLIC_ORIGIN",
    ):
        val = os.environ.get(key, "").strip().rstrip("/")
        if val:
            return val
    host = os.environ.get("PUBLIC_SITE_HOST", "").strip()
    if host:
        return "https://{0}".format(host)
    return ""


def backup_dir_for_id(backups_root, backup_id):
    return backups_root / ("blog-install-" + backup_id)


def restore_backup_full(backup_dir, docroot):
    restore_backup(backup_dir, docroot)
    created_path = backup_dir / "created.json"
    if created_path.is_file():
        data = json.loads(created_path.read_text(encoding="utf-8"))
        for rel in data.get("created") or []:
            target = docroot / rel
            if target.is_file():
                target.unlink()


def sync_office_blog_template(office_repo):
    import subprocess
    import sys

    script = office_repo / "scripts" / "lireate_blog_sync_template.py"
    if not script.is_file():
        raise SystemExit("BLOCKER: missing {0}".format(script))
    subprocess.run([sys.executable, str(script)], cwd=str(office_repo), check=True)


def unified_diff(old, new, path_label):
    old_lines = old.splitlines(keepends=True)
    new_lines = new.splitlines(keepends=True)
    return "".join(
        difflib.unified_diff(
            old_lines,
            new_lines,
            fromfile=path_label + " (before)",
            tofile=path_label + " (after)",
        )
    )


def probe_article_path(office_repo):
    index = office_repo / "data" / "blog-articles-index.json"
    if index.is_file():
        data = json.loads(index.read_text(encoding="utf-8"))
        articles = data.get("articles") or []
        if articles and articles[0].get("url"):
            u = articles[0]["url"]
            return u if u.startswith("/") else "/" + u
    return "/kak_zapolnit_zayavku_na_tovarnyiy_znak/"


def http_probe(base_url, path, timeout=25):
    import urllib.error
    import urllib.request

    url = base_url.rstrip("/") + path
    req = urllib.request.Request(url, headers={"User-Agent": "lireate-blog-install/1.0"})
    try:
        with urllib.request.urlopen(req, timeout=timeout) as resp:
            body = resp.read().decode("utf-8", errors="replace")
            return resp.status, body, url
    except urllib.error.HTTPError as e:
        body = e.read().decode("utf-8", errors="replace")
        return e.code, body, url


def verify_http(base_url, article_path):
    errors = []
    for path in (article_path, "/news/"):
        status, body, url = http_probe(base_url, path)
        if status != 200:
            errors.append("{0}: HTTP {1}".format(url, status))
            continue
        if "lx-site-footer" not in body:
            errors.append("{0}: missing lx-site-footer".format(url))
        if path == article_path:
            if "lireate-blog-toc" not in body and "data-lx-blog-toc" not in body:
                errors.append("{0}: missing TOC markup".format(url))
    return errors


def backup_files(backup_dir, docroot, rel_paths):
    manifest = []
    backup_dir.mkdir(parents=True, exist_ok=True)
    for rel in rel_paths:
        rel = Path(rel)
        src = docroot / rel
        if not src.exists():
            continue
        dst = backup_dir / rel
        dst.parent.mkdir(parents=True, exist_ok=True)
        if src.is_file():
            shutil.copy2(str(src), str(dst))
            manifest.append(str(rel).replace("\\", "/"))
    (backup_dir / "manifest.json").write_text(
        json.dumps({"files": manifest}, ensure_ascii=False, indent=2) + "\n",
        encoding="utf-8",
    )
    return manifest


def write_created_manifest(backup_dir, created_rels):
    payload = {
        "created": [str(r).replace("\\", "/") for r in created_rels],
    }
    (backup_dir / "created.json").write_text(
        json.dumps(payload, ensure_ascii=False, indent=2) + "\n",
        encoding="utf-8",
    )


def restore_backup(backup_dir, docroot):
    manifest_path = backup_dir / "manifest.json"
    if not manifest_path.is_file():
        raise SystemExit("BLOCKER: no manifest in backup {0}".format(backup_dir))
    manifest = json.loads(manifest_path.read_text(encoding="utf-8"))
    for rel in manifest.get("files") or []:
        src = backup_dir / rel
        dst = docroot / rel
        if src.is_file():
            dst.parent.mkdir(parents=True, exist_ok=True)
            shutil.copy2(str(src), str(dst))
        elif dst.is_file():
            dst.unlink()


def make_backup_id():
    return datetime.now(timezone.utc).strftime("%Y%m%d-%H%M%S")

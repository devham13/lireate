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
LIST_INSTALL_MARKER = "lireate-blog-list-install:v3"
LIST_INSTALL_MARKER_LEGACY_V1 = "lireate-blog-list-install:v1"
LIST_INSTALL_MARKER_LEGACY_V2 = "lireate-blog-list-install:v2"
LIST_INSTALL_MARKERS_ALL = (
    LIST_INSTALL_MARKER,
    LIST_INSTALL_MARKER_LEGACY_V2,
    LIST_INSTALL_MARKER_LEGACY_V1,
)
LIST_FOOTER_AFTER_SECTION_RE = re.compile(
    r"<\?php\s*"
    r"(?:if\s*\(\s*function_exists\s*\(\s*['\"]lireate_blog_require_lib['\"]\s*\)\s*\)\s*\{[^}]*\}\s*)?"
    r"if\s*\(\s*function_exists\s*\(\s*['\"]lireate_blog_render_service_footer['\"]\s*\)\s*\)\s*\{[^}]*"
    r"lireate_blog_render_service_footer\s*\(\s*\)\s*;\s*"
    r"\}\s*"
    r"\?>\s*",
    re.IGNORECASE | re.DOTALL,
)
LIST_FALLBACK_MARKER = "lireate-blog-list-fallback:v1"
FOOTER_FIX_CSS_MARKER = "lireate footer/max fix:"
FOOTER_FIX_CSS_RULE = "#blog .lx-site-footer p.lx-site-footer-title{font-size:16px"
COLLAGE_V1_CSS_MARKER = ".lireate-blog-collage-hero-wrap"
COLLAGE_V1_HERO_ATTR = 'data-lireate-collage-hero="1"'
LIST_LEGACY_MARKER = "lireate-blog-list-legacy:snapshot"
LIST_LEGACY_REL = Path("template/page/news/lireate-blog-list-legacy.inc.php")
BODY_CLASS = "lireate-blog-body"
CMS_INCLUDE = "/template/page/news/lireate-blog-cms.inc.php"
LIST_CMS_INCLUDE = "/template/page/news/lireate-blog-list-cms.inc.php"
LIST_SECTION_MARKERS = ('id="blog"', "Самое свежее и интересное", "articlesBlock")

SKIP_PATH_PARTS = {"data", "db.php"}
FORBIDDEN_TOUCH = {".htaccess"}


def php_single_quoted_literal(value):
    """Escape a value for insertion as a PHP single-quoted string literal."""
    s = str(value)
    return "'" + s.replace("\\", "\\\\").replace("'", "\\'") + "'"


def fill_php_runner_template(template, replacements):
    """Replace __KEY__ tokens; safe for PHP sources that contain ``{`` / ``}`` braces."""
    out = template
    for key, repl in replacements.items():
        token = "__{0}__".format(key)
        if token not in out:
            raise ValueError("missing placeholder {0} in PHP runner template".format(token))
        out = out.replace(token, repl)
    return out


def build_php_list_diag_runner(sandbox_docroot, list_include_path, news_snippet):
    """Build php-cli runner for patched CMS list template (--diag)."""
    doc_lit = php_single_quoted_literal(str(Path(sandbox_docroot).resolve()))
    list_lit = php_single_quoted_literal(str(Path(list_include_path).resolve()))
    template = (
        "<?php\n"
        "ini_set('display_errors', '1');\n"
        "error_reporting(E_ALL);\n"
        "$_SERVER['DOCUMENT_ROOT'] = __DOC__;\n"
        "$_SERVER['REQUEST_URI'] = '/news/';\n"
        "$_SERVER['SCRIPT_FILENAME'] = __LIST__;\n"
        "$_GET = array();\n"
        "__NEWS__\n"
        "ob_start();\n"
        "include __LIST__;\n"
        "echo ob_get_clean();\n"
    )
    return fill_php_runner_template(
        template,
        {"DOC": doc_lit, "LIST": list_lit, "NEWS": news_snippet},
    )


def build_php_article_collage_diag_runner(sandbox_docroot, lib_php_path, layout_php_path):
    """Build php-cli runner for collage-v1 vs legacy article render (--diag)."""
    doc_lit = php_single_quoted_literal(str(Path(sandbox_docroot).resolve()))
    lib_lit = php_single_quoted_literal(str(Path(lib_php_path).resolve()))
    layout_lit = php_single_quoted_literal(str(Path(layout_php_path).resolve()))
    template = (
        "<?php\n"
        "ini_set('display_errors', '1');\n"
        "error_reporting(E_ALL);\n"
        "$_SERVER['DOCUMENT_ROOT'] = __DOC__;\n"
        "require __LIB__;\n"
        "function lireate_blog_diag_render_article($row, $label) {\n"
        "    $prep = lireate_blog_article_collage_prepare($row);\n"
        "    $lireate_blog_title = 'Diag ' . $label;\n"
        "    $lireate_blog_body_html = lireate_blog_prepare_body_html($prep['body']);\n"
        "    $lireate_blog_collage_mode = !empty($prep['mode']);\n"
        "    $lireate_blog_collage_hero_html = isset($prep['hero_html']) ? $prep['hero_html'] : '';\n"
        "    $lireate_blog_cover_html = '';\n"
        "    $lireate_blog_date_display = '19.10.2026';\n"
        "    $lireate_blog_date_iso = '2026-10-19';\n"
        "    $lireate_blog_tag = 'Тест';\n"
        "    $lireate_blog_tag_url = '/news/';\n"
        "    $lireate_blog_current_url = '/diag-article/';\n"
        "    ob_start();\n"
        "    include __LAYOUT__;\n"
        "    return ob_get_clean();\n"
        "}\n"
        "$collage_story = '<figure class=\"lx-article-cover\" data-cover-style=\"collage-v1\">'\n"
        "    . '<img src=\"/template/images/blog/diag-collage-v1.jpg\" alt=\"Diag\" width=\"860\" height=\"400\"></figure>'\n"
        "    . '<p>Collage body</p>';\n"
        "$legacy_story = '<p>Legacy body without collage cover.</p><h2>Section</h2>';\n"
        "echo \"<!-- lireate-blog-diag-article-section:collage -->\\n\";\n"
        "echo lireate_blog_diag_render_article(array(\n"
        "    'full_story' => $collage_story,\n"
        "    'cover_style' => 'collage-v1',\n"
        "    'seo_url' => 'diag-collage-v1',\n"
        "    'name' => 'Collage diag',\n"
        "), 'collage');\n"
        "echo \"<!-- lireate-blog-diag-article-section:legacy -->\\n\";\n"
        "echo lireate_blog_diag_render_article(array(\n"
        "    'full_story' => $legacy_story,\n"
        "    'name' => 'Legacy diag',\n"
        "), 'legacy');\n"
    )
    return fill_php_runner_template(
        template,
        {"DOC": doc_lit, "LIB": lib_lit, "LAYOUT": layout_lit},
    )


PHP56_SOURCE_FORBIDDEN = (
    (re.compile(r"dirname\s*\([^)]+,\s*\d"), "dirname() with levels (PHP 7+)"),
    (re.compile(r"\?\?"), "null coalesce ?? (PHP 7+)"),
    (re.compile(r"\?->"), "nullsafe ?-> (PHP 8+)"),
    (re.compile(r"\bfn\s*\("), "arrow function fn() (PHP 7.4+)"),
    (re.compile(r"\bstr_contains\s*\("), "str_contains() (PHP 8+)"),
)

PHP56_DEPLOY_REL_DIRS = (
    Path("pages/blog/_shared"),
    Path("pages/services/_shared"),
    Path("template/page/news"),
)


def iter_deploy_php_sources(office_repo):
    repo = Path(office_repo).resolve()
    for rel_dir in PHP56_DEPLOY_REL_DIRS:
        base = repo / rel_dir
        if not base.is_dir():
            continue
        for path in sorted(base.rglob("*.php")):
            if path.is_file():
                yield path


def scan_php56_forbidden_sources(office_repo):
    violations = []
    for path in iter_deploy_php_sources(office_repo):
        try:
            text = path.read_text(encoding="utf-8", errors="replace")
        except OSError as exc:
            violations.append("{0}: unreadable ({1})".format(path, exc))
            continue
        for pattern, label in PHP56_SOURCE_FORBIDDEN:
            if pattern.search(text):
                violations.append("{0}: {1}".format(path.relative_to(office_repo), label))
                break
    return violations


def php_version_string(php_bin):
    import subprocess

    try:
        proc = subprocess.run(
            [php_bin, "-r", "echo PHP_VERSION;"],
            capture_output=True,
            text=True,
            timeout=30,
        )
    except (OSError, subprocess.SubprocessError):
        return None
    if proc.returncode != 0:
        return None
    return (proc.stdout or "").strip()


def resolve_site_php_bin():
    import os

    explicit = (os.environ.get("LIREATE_PHP_BIN") or "").strip()
    if explicit:
        return explicit
    p56 = Path("/opt/php/5.6/bin/php")
    if p56.is_file():
        return str(p56)
    wrapper = Path.home() / "php-bin" / "lireate.ru" / "php"
    if wrapper.is_file():
        try:
            first = wrapper.read_text(encoding="utf-8", errors="replace").splitlines()[0]
            if first.startswith("#!"):
                interp = first[2:].strip()
                if interp:
                    interp_path = Path(interp)
                    if interp_path.name == "php-cgi" or interp_path.name.endswith("-cgi"):
                        cli = interp_path.parent / "php"
                        if cli.is_file():
                            return str(cli)
                    if interp_path.is_file():
                        return str(interp_path)
        except OSError:
            pass
    return "php"


def resolve_diag_php_bin():
    import os

    explicit = (os.environ.get("LIREATE_PHP_BIN") or "").strip()
    if explicit:
        return explicit
    return resolve_site_php_bin()


def assert_diag_php_matches_site(php_bin):
    site_bin = resolve_site_php_bin()
    site_ver = php_version_string(site_bin)
    diag_ver = php_version_string(php_bin)
    if site_ver and diag_ver and site_ver != diag_ver:
        print(
            "BLOCKER: diag PHP {0} ({1}) != site PHP {2} ({3})".format(
                php_bin, diag_ver, site_bin, site_ver
            )
        )
        return 2
    return 0


def _php_bin_uses_docker(php_bin):
    try:
        wrap = Path(php_bin).read_text(encoding="utf-8", errors="replace")
    except OSError:
        return False
    return "docker run" in wrap


def host_to_php_fs_path(php_bin, office_repo, path):
    repo = Path(office_repo).resolve()
    abs_path = Path(path).resolve()
    if _php_bin_uses_docker(php_bin):
        rel = abs_path.relative_to(repo)
        return "/work/{0}".format(str(rel).replace("\\", "/"))
    return str(abs_path)


def _php_lint_target_path(php_bin, office_repo, path):
    return host_to_php_fs_path(php_bin, office_repo, path)


def lint_deployed_php_with_bin(php_bin, office_repo):
    violations = scan_php56_forbidden_sources(office_repo)
    if violations:
        print("PHP 5.6 source scan FAIL:")
        for line in violations[:30]:
            print("  - {0}".format(line))
        return 1
    import subprocess

    for path in iter_deploy_php_sources(office_repo):
        lint_path = _php_lint_target_path(php_bin, office_repo, path)
        proc = subprocess.run(
            [php_bin, "-l", lint_path],
            capture_output=True,
            text=True,
            timeout=30,
        )
        if proc.returncode != 0:
            print("php -l FAIL: {0}".format(path))
            print((proc.stderr or proc.stdout or "").strip())
            return 1
    return 0


def resolve_php_error_log_path(docroot=None, autopub_home=None):
    candidates = []
    if docroot:
        candidates.append(Path(docroot) / "PHP_errors.log")
    home = Path.home()
    candidates.append(home / "www" / "public_html" / "PHP_errors.log")
    if autopub_home:
        candidates.append(Path(autopub_home) / "logs" / "php-error.log")
    candidates.extend(
        [
            home / "logs" / "error_log",
            home / "logs" / "php-error.log",
        ]
    )
    if docroot:
        candidates.append(Path(docroot) / "error_log")
    for path in candidates:
        if path and path.is_file():
            return path
    return None


def snapshot_php_error_log(docroot=None, autopub_home=None):
    path = resolve_php_error_log_path(docroot, autopub_home)
    if path is None:
        return None
    try:
        return (str(path), path.stat().st_size)
    except OSError:
        return None


def check_php_error_log_since_deploy(snapshot, docroot=None, autopub_home=None):
    if snapshot:
        path = Path(snapshot[0])
        offset = snapshot[1]
    else:
        path = resolve_php_error_log_path(docroot, autopub_home)
        offset = 0
    if path is None or not path.is_file():
        return []
    try:
        with path.open("rb") as fh:
            fh.seek(offset)
            chunk = fh.read()
    except OSError:
        return []
    text = chunk.decode("utf-8", errors="replace")
    fatals = [line.strip() for line in text.splitlines() if "PHP Fatal" in line]
    if not fatals:
        return []
    return [
        "php error log ({0}): {1} new PHP Fatal line(s), last: {2}".format(
            path, len(fatals), fatals[-1][:240]
        )
    ]


def probe_article_paths(office_repo, max_count=5):
    paths = []
    index = Path(office_repo) / "data" / "blog-articles-index.json"
    if index.is_file():
        data = json.loads(index.read_text(encoding="utf-8"))
        for article in (data.get("articles") or [])[:max_count]:
            url = (article.get("url") or "").strip()
            if not url:
                continue
            paths.append(url if url.startswith("/") else "/" + url)
    if not paths:
        paths.append(probe_article_path(office_repo))
    seen = set()
    unique = []
    for path in paths:
        if path not in seen:
            seen.add(path)
            unique.append(path)
    return unique


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


BLOG_SHARED_TO_TEMPLATE = (
    "lireate-blog-config.php",
    "lireate-blog-lib.php",
    "lireate-blog-article.css",
    "lireate-blog-article.js",
    "lireate-blog-footer.css",
    "lireate-blog-list.css",
)


def copy_plan(office_repo):
    """Files to copy from office-repo into docroot (relative paths).

    PHP/CSS under template/page/news are taken from pages/blog/_shared when present
    so --apply/--diag never need to mutate tracked files in office-repo (sync script).
    """
    by_dest = {}
    repo = Path(office_repo)
    news_dir = repo / "template" / "page" / "news"
    shared_blog = repo / "pages" / "blog" / "_shared"
    for name in BLOG_SHARED_TO_TEMPLATE:
        src = shared_blog / name
        if src.is_file():
            by_dest[Path("template/page/news") / name] = src
    if news_dir.is_dir():
        for path in sorted(news_dir.glob("lireate-blog*")):
            if not path.is_file():
                continue
            rel = Path("template/page/news") / path.name
            if rel not in by_dest:
                by_dest[rel] = path
        cms = news_dir / "lireate-blog-cms.inc.php"
        if cms.is_file():
            by_dest[Path("template/page/news/lireate-blog-cms.inc.php")] = cms
    footer_src = repo / "pages" / "services" / "_shared" / "lireate-service-footer.inc.php"
    messenger_src = repo / "pages" / "services" / "_shared" / "lireate-messenger-urls.php"
    if footer_src.is_file():
        by_dest[Path("template/lireate/lireate-service-footer.inc.php")] = footer_src
    else:
        lireate_dir = repo / "template" / "lireate"
        if lireate_dir.is_dir():
            src = lireate_dir / "lireate-service-footer.inc.php"
            if src.is_file():
                by_dest[Path("template/lireate/lireate-service-footer.inc.php")] = src
    if messenger_src.is_file():
        by_dest[Path("template/lireate/lireate-messenger-urls.php")] = messenger_src
    else:
        lireate_dir = repo / "template" / "lireate"
        if lireate_dir.is_dir():
            src = lireate_dir / "lireate-messenger-urls.php"
            if src.is_file():
                by_dest[Path("template/lireate/lireate-messenger-urls.php")] = src
    if shared_blog.is_dir():
        for path in sorted(shared_blog.glob("*")):
            if path.is_file() and path.suffix in (".php", ".css", ".js"):
                by_dest[Path("pages/blog/_shared") / path.name] = path
    list_data = shared_blog / "lireate-blog-list-data.php"
    if list_data.is_file():
        by_dest[Path("template/page/news/lireate-blog-list-data.php")] = list_data
    list_cms = news_dir / "lireate-blog-list-cms.inc.php"
    if list_cms.is_file():
        by_dest[Path("template/page/news/lireate-blog-list-cms.inc.php")] = list_cms
    list_legacy = news_dir / "lireate-blog-list-legacy.inc.php"
    if list_legacy.is_file():
        by_dest[LIST_LEGACY_REL] = list_legacy
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


def find_news_list_template(docroot):
    """Locate CMS PHP that renders /news list (section#blog)."""
    candidates = []
    for path in _iter_php_under_template(docroot):
        try:
            text = path.read_text(encoding="utf-8", errors="replace")
        except OSError:
            continue
        if "news_article[0]" in text:
            continue
        if any(m in text for m in LIST_INSTALL_MARKERS_ALL):
            score = 110
            if LIST_SECTION_MARKERS[2] in text:
                score += 5
            if path.name in ("news.php", "blog.php", "newsList.php"):
                score += 15
            candidates.append((score, path))
            continue
        if not all(marker in text for marker in (LIST_SECTION_MARKERS[0], LIST_SECTION_MARKERS[1])):
            continue
        score = 70
        if any(m in text for m in LIST_INSTALL_MARKERS_ALL):
            score = 100
        if LIST_SECTION_MARKERS[2] in text:
            score += 10
        if path.name in ("news.php", "blog.php", "newsList.php"):
            score += 15
        candidates.append((score, path))
    if not candidates:
        return None
    candidates.sort(key=lambda x: (-x[0], len(str(x[1]))))
    return candidates[0][1]


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
        "if (!function_exists('lireate_blog_require_lib')) {\n"
        "    require_once $_SERVER['DOCUMENT_ROOT'] . '/template/page/news/lireate-blog-lib.php';\n"
        "}\n"
        "lireate_blog_require_lib();\n"
        "$_lb_row_preview = array(\n"
        "    'full_story' => $_lb_story,\n"
        "    'seo_url' => $_lb_seo,\n"
        "    'name' => isset($_lb_art['name']) ? $_lb_art['name'] : '',\n"
        "    'cover_style' => isset($_lb_art['cover_style']) ? $_lb_art['cover_style'] : '',\n"
        ");\n"
        "$_lb_collage = lireate_blog_article_collage_prepare($_lb_row_preview);\n"
        "$_lb_story = $_lb_collage['body'];\n"
        "$lireate_blog_collage_mode = !empty($_lb_collage['mode']);\n"
        "$lireate_blog_collage_hero_html = isset($_lb_collage['hero_html']) ? $_lb_collage['hero_html'] : '';\n"
        "if (!$_lb_collage['mode'] && preg_match('/<img[^>]+src=\"([^\"]*\\\\/template\\\\/images\\\\/blog\\\\/[^\"]+)\"/i', $_lb_story, $_lb_m)) {\n"
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


def _span_blog_section(text):
    match = re.search(r"<section\s+id=[\"']blog[\"'][^>]*>", text, re.IGNORECASE)
    if not match:
        return None
    start = match.start()
    open_end = match.end()
    depth = 1
    pos = open_end
    lower = text.lower()
    while pos < len(text) and depth > 0:
        next_open = lower.find("<section", pos)
        next_close = lower.find("</section>", pos)
        if next_close == -1:
            return None
        if next_open != -1 and next_open < next_close:
            depth += 1
            pos = next_open + 8
        else:
            depth -= 1
            end = next_close + len("</section>")
            if depth == 0:
                return (start, end)
            pos = next_close + 9
    return None


def _replacement_block_list():
    return (
        "<section id=\"blog\">\n"
        "<?php /* %s */ ?>\n"
        "<?php require $_SERVER['DOCUMENT_ROOT'] . '%s'; ?>\n"
        "</section>\n"
    ) % (LIST_INSTALL_MARKER, LIST_CMS_INCLUDE)


def extract_blog_section_legacy_inner(text):
    """Inner HTML/PHP of section#blog for fallback snapshot."""
    span = _span_blog_section(text)
    if not span:
        return None
    block = text[span[0] : span[1]]
    match = re.search(
        r"<section\s+id=[\"']blog[\"'][^>]*>(.*)</section>\s*$",
        block,
        flags=re.IGNORECASE | re.DOTALL,
    )
    if not match:
        return None
    return match.group(1).strip()


def write_list_legacy_snapshot(docroot, inner_html):
    if not inner_html:
        return None
    rel = LIST_LEGACY_REL
    path = docroot / rel
    path.parent.mkdir(parents=True, exist_ok=True)
    payload = (
        "<?php\n"
        "/** Auto-snapshot of CMS /news list (lireate_blog_install). */\n"
        "/* %s */\n"
        "?>\n"
        "%s\n"
    ) % (LIST_LEGACY_MARKER, inner_html)
    path.write_text(payload, encoding="utf-8")
    return rel


def patch_news_list_template_content(text):
    """Return (new_text, changed, description)."""
    if LIST_INSTALL_MARKER in text:
        return text, False, "already patched (list marker present)"
    span = _span_blog_section(text)
    if not span:
        return text, False, "section#blog not found"
    repl = _replacement_block_list()
    tail = text[span[1] :]
    tail = LIST_FOOTER_AFTER_SECTION_RE.sub("", tail, count=1)
    new_text = text[: span[0]] + repl + tail
    if LIST_INSTALL_MARKER_LEGACY_V2 in text and LIST_INSTALL_MARKER not in new_text:
        return new_text, True, "upgraded list patch v2→v3 (footer inside cms include)"
    return new_text, new_text != text, "replaced section#blog with lireate-blog-list-cms.inc.php"


def plan_list_template_content(text):
    """List template: section#blog patch + optional body class (same as --check / --apply)."""
    text, list_changed, list_desc = patch_news_list_template_content(text)
    text, body_changed = patch_body_class_content(text)
    return text


def list_template_patch_status(original, planned):
    """Human-readable status for list template (check/apply must match)."""
    if LIST_INSTALL_MARKER in original and planned == original:
        return "no changes needed (list include already present)"
    if planned == original:
        if LIST_INSTALL_MARKER in original:
            return "no changes needed (list include already present)"
        return "no changes needed (section#blog not found or not patchable)"
    if LIST_INSTALL_MARKER not in original:
        return "would patch"
    return "would patch"


def plan_template_content(text):
    """Apply news + body patches for preview (idempotent)."""
    text, _, _ = patch_news_template_content(text)
    text, _ = patch_body_class_content(text)
    return text


def apply_list_template_patch(docroot, template_path):
    """Write patched list template; return (changed, notes)."""
    rel = template_path.relative_to(docroot)
    if "data" in rel.parts and template_path.name == "db.php":
        raise SystemExit("BLOCKER: refusing to patch data/db.php")
    if template_path.name == ".htaccess" and template_path.parent == docroot:
        raise SystemExit("BLOCKER: refusing to patch docroot .htaccess")
    original = template_path.read_text(encoding="utf-8", errors="replace")
    planned = plan_list_template_content(original)
    status = list_template_patch_status(original, planned)
    if planned == original:
        return False, ["list: {0}".format(status)]
    inner = extract_blog_section_legacy_inner(original)
    if inner and LIST_INSTALL_MARKER not in original:
        write_list_legacy_snapshot(docroot, inner)
    notes = []
    if LIST_INSTALL_MARKER not in original:
        notes.append("list: patched section#blog → lireate-blog-list-cms.inc.php")
        if inner:
            notes.append("list: saved legacy snapshot ({0})".format(LIST_LEGACY_REL))
    elif BODY_CLASS not in original and BODY_CLASS in planned:
        notes.append("list: added body class {0}".format(BODY_CLASS))
    template_path.write_text(planned, encoding="utf-8")
    return True, notes


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
        return False, ["article: no changes needed (include + body class already present)"]
    notes = []
    if INSTALL_MARKER not in original:
        notes.append("article: patched cms include (lireate-blog-cms.inc.php)")
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
    """No-op: copy_plan reads from pages/blog/_shared (does not write office-repo)."""
    return None


def build_diag_sandbox(live_docroot, office_repo):
    """Temp docroot: blog assets from office-repo; patched list CMS template like --apply."""
    import os
    import tempfile

    live_docroot = Path(live_docroot).resolve()
    office_repo = Path(office_repo).resolve()
    diag_parent = Path(office_repo) / ".lireate-diag-tmp"
    diag_parent.mkdir(parents=True, exist_ok=True)
    root = Path(tempfile.mkdtemp(prefix="lireate-blog-diag-", dir=str(diag_parent)))
    sandbox = root / "docroot"
    sandbox.mkdir(parents=True)

    for src, rel in copy_plan(office_repo):
        dst = sandbox / rel
        dst.parent.mkdir(parents=True, exist_ok=True)
        shutil.copy2(str(src), str(dst))

    list_rel = None
    list_tpl_live = find_news_list_template(live_docroot)
    if list_tpl_live is not None and list_tpl_live.is_file():
        try:
            original = list_tpl_live.read_text(encoding="utf-8", errors="replace")
            planned = plan_list_template_content(original)
            list_rel = list_tpl_live.relative_to(live_docroot)
            dest = sandbox / list_rel
            dest.parent.mkdir(parents=True, exist_ok=True)
            dest.write_text(planned, encoding="utf-8")
        except (OSError, ValueError):
            list_rel = None

    for rel in (Path("data/db.php"),):
        live_file = live_docroot / rel
        if live_file.is_file():
            dst = sandbox / rel
            dst.parent.mkdir(parents=True, exist_ok=True)
            if dst.exists() or dst.is_symlink():
                dst.unlink()
            os.symlink(str(live_file), str(dst))

    if list_rel is None:
        for rel in (Path("template/page/news/blog.php"), Path("template/page/news/index.php")):
            live_file = live_docroot / rel
            if live_file.is_file():
                dst = sandbox / rel
                dst.parent.mkdir(parents=True, exist_ok=True)
                if dst.exists() or dst.is_symlink():
                    dst.unlink()
                os.symlink(str(live_file), str(dst))
                if list_rel is None and rel.name in ("blog.php", "index.php"):
                    try:
                        original = live_file.read_text(encoding="utf-8", errors="replace")
                        planned = plan_list_template_content(original)
                        dest.write_text(planned, encoding="utf-8")
                        list_rel = rel
                    except OSError:
                        pass

    return sandbox, root, list_rel


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


def http_probe(base_url, path, timeout=25, cache_bust=None):
    import urllib.error
    import urllib.parse
    import urllib.request

    url = base_url.rstrip("/") + path
    if cache_bust:
        parsed = urllib.parse.urlparse(url)
        query = urllib.parse.parse_qs(parsed.query, keep_blank_values=True)
        query["_lireate_blog_install"] = [str(cache_bust)]
        new_q = urllib.parse.urlencode(query, doseq=True)
        url = urllib.parse.urlunparse(
            (parsed.scheme, parsed.netloc, parsed.path, parsed.params, new_q, parsed.fragment)
        )
    req = urllib.request.Request(
        url,
        headers={
            "User-Agent": "lireate-blog-install/1.0",
            "Cache-Control": "no-cache",
            "Pragma": "no-cache",
        },
    )
    try:
        with urllib.request.urlopen(req, timeout=timeout) as resp:
            body = resp.read().decode("utf-8", errors="replace")
            headers = {k.lower(): v for k, v in resp.headers.items()}
            return resp.status, body, url, headers
    except urllib.error.HTTPError as e:
        body = e.read().decode("utf-8", errors="replace")
        headers = {k.lower(): v for k, v in e.headers.items()}
        return e.code, body, url, headers


def _php_error_tail(docroot, autopub_home, max_lines=40):
    path = resolve_php_error_log_path(docroot, autopub_home)
    if path is None:
        return None, []
    try:
        lines = path.read_text(encoding="utf-8", errors="replace").splitlines()
        if lines:
            return str(path), lines[-max_lines:]
    except OSError:
        pass
    return None, []


def _cache_header_note(headers):
    keys = (
        "x-cache",
        "x-cache-status",
        "cf-cache-status",
        "age",
        "cache-control",
        "x-varnish",
        "x-litespeed-cache",
    )
    parts = []
    for key in keys:
        if key in headers:
            parts.append("{0}: {1}".format(key, headers[key]))
    return "; ".join(parts) if parts else "no cache headers"


def diagnose_http_body(body, headers):
    body = body or ""
    markers = {
        "lireate-blog-install": INSTALL_MARKER in body or "lireate-blog-install" in body,
        "lireate-blog-body": BODY_CLASS in body,
        "lireate-blog-page": "lireate-blog-page" in body,
        "lireate-blog-list-page": "lireate-blog-list-page" in body,
        "lireate-blog-list-fallback": LIST_FALLBACK_MARKER in body,
        "lx-site-footer": "lx-site-footer" in body,
        "toc": ("lireate-blog-toc" in body or "data-lx-blog-toc" in body),
    }
    tail = body[-300:] if len(body) > 300 else body
    issues = re.findall(
        r"(?:Fatal error|Parse error|Warning):\s*[^\n<]+",
        body,
        flags=re.IGNORECASE,
    )
    return {
        "markers": markers,
        "tail": tail,
        "php_issues": issues[:8],
        "cache": _cache_header_note(headers or {}),
        "length": len(body),
    }


def save_verify_html(logs_dir, backup_id, index, url, status, body):
    logs_dir = Path(logs_dir)
    logs_dir.mkdir(parents=True, exist_ok=True)
    safe = re.sub(r"[^a-zA-Z0-9._-]+", "_", url.replace("https://", "").replace("http://", ""))
    path = logs_dir / "blog-install-{0}-{1}-{2}.html".format(backup_id, index, safe[:80])
    header = "<!-- status: {0} url: {1} -->\n".format(status, url)
    path.write_text(header + body, encoding="utf-8", errors="replace")
    return path


def print_verify_diagnosis(label, url, status, diag, html_path=None, error_log=None):
    print("=== HTTP DIAG: {0} ===".format(label))
    print("url: {0}".format(url))
    print("status: {0}".format(status))
    if html_path:
        print("saved: {0}".format(html_path))
    print("cache: {0}".format(diag.get("cache", "")))
    print("length: {0}".format(diag.get("length", 0)))
    markers = diag.get("markers") or {}
    print(
        "markers: install={0} body_class={1} blog_page={2} footer={3} toc={4}".format(
            markers.get("lireate-blog-install"),
            markers.get("lireate-blog-body"),
            markers.get("lireate-blog-page"),
            markers.get("lx-site-footer"),
            markers.get("toc"),
        )
    )
    if diag.get("php_issues"):
        print("php in HTML:")
        for line in diag["php_issues"]:
            print("  {0}".format(line.strip()))
    print("body tail (300 chars):")
    print(diag.get("tail", ""))
    if error_log:
        print("php error_log ({0}), last lines:".format(error_log[0]))
        for line in error_log[1][-8:]:
            print("  {0}".format(line))


def _verify_single_http_page(
    base_url,
    label,
    path,
    cache_bust,
    logs_dir,
    backup_id,
    log_index,
    docroot,
    autopub_home,
    err_log_path,
    err_log_lines,
):
    status, body, url, headers = http_probe(base_url, path, cache_bust=cache_bust)
    diag = diagnose_http_body(body, headers)
    html_path = None
    if logs_dir and backup_id:
        html_path = save_verify_html(logs_dir, backup_id, log_index, url, status, body)

    page_errors = []
    if status != 200:
        page_errors.append("HTTP {0}".format(status))
    if diag.get("php_issues"):
        page_errors.append("PHP errors in HTML")
    if label == "news_list":
        if diag["markers"].get("lireate-blog-list-fallback"):
            page_errors.append("list rendered in fallback mode ({0})".format(LIST_FALLBACK_MARKER))
        if not diag["markers"].get("lireate-blog-list-page"):
            page_errors.append("missing lireate-blog-list-page (list patch/CMS not active?)")
        if not diag["markers"].get("lx-site-footer"):
            page_errors.append("missing lx-site-footer")
    else:
        if not diag["markers"].get("lireate-blog-page"):
            page_errors.append("missing lireate-blog-page (patch/CMS not active?)")
        if not diag["markers"].get("lx-site-footer"):
            page_errors.append("missing lx-site-footer")
        if not diag["markers"].get("toc"):
            page_errors.append("missing TOC markup")

    if page_errors:
        print_verify_diagnosis(
            label,
            url,
            status,
            diag,
            html_path=html_path,
            error_log=(err_log_path, err_log_lines) if err_log_lines else None,
        )
        return "{0}: {1}".format(url, "; ".join(page_errors))
    return None


def verify_http(
    base_url,
    article_path,
    logs_dir=None,
    backup_id=None,
    docroot=None,
    autopub_home=None,
):
    return verify_http_deploy(
        base_url,
        [article_path] if isinstance(article_path, str) else list(article_path),
        logs_dir=logs_dir,
        backup_id=backup_id,
        docroot=docroot,
        autopub_home=autopub_home,
        deploy_log_snapshot=None,
        wait_seconds=0,
        rounds=1,
    )


def verify_http_deploy(
    base_url,
    article_paths,
    logs_dir=None,
    backup_id=None,
    docroot=None,
    autopub_home=None,
    deploy_log_snapshot=None,
    wait_seconds=90,
    rounds=2,
):
    import time

    errors = []
    err_log_path, err_log_lines = _php_error_tail(docroot, autopub_home)
    log_index = 0
    for round_idx in range(1, max(1, int(rounds)) + 1):
        if round_idx > 1 and wait_seconds > 0:
            print(
                "HTTP verify round {0}/{1}: waiting {2}s for cache/propagation...".format(
                    round_idx, rounds, wait_seconds
                )
            )
            time.sleep(wait_seconds)
        else:
            print("HTTP verify round {0}/{1}".format(round_idx, rounds))
        cache_bust = "{0}-r{1}".format(int(time.time()), round_idx)
        round_errors = []
        for article_path in article_paths:
            log_index += 1
            err = _verify_single_http_page(
                base_url,
                "article",
                article_path,
                cache_bust,
                logs_dir,
                backup_id,
                log_index,
                docroot,
                autopub_home,
                err_log_path,
                err_log_lines,
            )
            if err:
                round_errors.append(err)
        log_index += 1
        err = _verify_single_http_page(
            base_url,
            "news_list",
            "/news/",
            cache_bust,
            logs_dir,
            backup_id,
            log_index,
            docroot,
            autopub_home,
            err_log_path,
            err_log_lines,
        )
        if err:
            round_errors.append(err)
        log_errors = check_php_error_log_since_deploy(
            deploy_log_snapshot, docroot=docroot, autopub_home=autopub_home
        )
        for line in log_errors:
            print("=== PHP ERROR LOG ===")
            print(line)
        round_errors.extend(log_errors)
        if round_errors:
            errors.extend(round_errors)
    return errors


def run_php_list_diag(live_docroot, office_repo=None):
    """php-cli list render via patched CMS list template (same path as live /news)."""
    import os
    import subprocess

    live_docroot = Path(live_docroot).resolve()
    repo = Path(office_repo) if office_repo is not None else Path(__file__).resolve().parents[1]
    sandbox, tmp_root, list_rel = build_diag_sandbox(live_docroot, repo)
    cms = sandbox / "template/page/news/lireate-blog-list-cms.inc.php"
    if not cms.is_file():
        shutil.rmtree(tmp_root, ignore_errors=True)
        print("BLOCKER: missing list cms in office-repo sandbox ({0})".format(cms))
        return 2
    if list_rel is None:
        list_tpl_live = find_news_list_template(live_docroot)
        if list_tpl_live is not None:
            try:
                list_rel = list_tpl_live.relative_to(live_docroot)
            except ValueError:
                list_rel = None
    if list_rel is None:
        shutil.rmtree(tmp_root, ignore_errors=True)
        print("BLOCKER: cannot locate CMS /news list template under {0}/template".format(live_docroot))
        return 2

    list_include = sandbox / list_rel
    if not list_include.is_file():
        shutil.rmtree(tmp_root, ignore_errors=True)
        print("BLOCKER: patched list template missing in sandbox ({0})".format(list_include))
        return 2

    php_bin = resolve_diag_php_bin()
    site_ver = php_version_string(resolve_site_php_bin())
    diag_ver = php_version_string(php_bin)
    print("site_php_version: {0}".format(site_ver or "n/a"))
    print("diag_php_version: {0}".format(diag_ver or "n/a"))
    ver_code = assert_diag_php_matches_site(php_bin)
    if ver_code != 0:
        shutil.rmtree(tmp_root, ignore_errors=True)
        return ver_code
    lint_code = lint_deployed_php_with_bin(php_bin, repo)
    if lint_code != 0:
        shutil.rmtree(tmp_root, ignore_errors=True)
        return lint_code
    fixture = repo / "scripts/fixtures/cms-news-list-template.php"
    news_snippet = "$news = array();"
    if fixture.is_file():
        text = fixture.read_text(encoding="utf-8", errors="replace")
        m = re.search(r"\$news\s*=\s*array\s*\(.*?\)\s*;", text, re.DOTALL)
        if m:
            news_snippet = m.group(0)

    runner = tmp_root / "run-list.php"
    runner.write_text(
        build_php_list_diag_runner(
            host_to_php_fs_path(php_bin, repo, sandbox),
            host_to_php_fs_path(php_bin, repo, list_include),
            news_snippet,
        ),
        encoding="utf-8",
    )

    print("=== PHP LIST DIAG (patched CMS list template) ===")
    print("live_docroot: {0}".format(live_docroot))
    print("sandbox_docroot: {0}".format(sandbox))
    print("php: {0}".format(php_bin))
    print("cms_template: {0}".format(list_rel))
    print("list_cms_inc: {0} (from office-repo copy)".format(cms.relative_to(sandbox)))

    proc = subprocess.run(
        [
            php_bin,
            "-d",
            "display_errors=1",
            "-d",
            "error_reporting=E_ALL",
            host_to_php_fs_path(php_bin, repo, runner),
        ],
        capture_output=True,
        text=True,
        timeout=90,
    )
    stdout = proc.stdout or ""
    stderr = proc.stderr or ""
    combined = stdout + stderr

    if stderr:
        print("--- stderr (full) ---")
        print(stderr.rstrip())

    tail_lines = stdout.splitlines()
    print("--- stdout (last 60 lines) ---")
    print("\n".join(tail_lines[-60:]))

    card_titles = len(re.findall(r"lireate-blog-list-card-title", stdout))
    page_links = len(re.findall(r"lireate-blog-list-page-link", stdout))
    total_items = None
    total_pages = None
    m_items = re.search(r"lireate-blog-list-diag-total-items:(\d+)", stdout)
    m_pages = re.search(r"lireate-blog-list-diag-total-pages:(\d+)", stdout)
    if m_items:
        total_items = int(m_items.group(1))
    if m_pages:
        total_pages = int(m_pages.group(1))
    covers_ok = None
    covers_missing = None
    m_ok = re.search(r"lireate-blog-list-diag-covers-ok:(\d+)", stdout)
    m_miss = re.search(r"lireate-blog-list-diag-covers-missing:(\d+)", stdout)
    if m_ok:
        covers_ok = int(m_ok.group(1))
    if m_miss:
        covers_missing = int(m_miss.group(1))
    print(
        "diag_counts: card_titles={0} pagination_links={1} total_items={2} total_pages={3} covers_ok={4} covers_missing={5}".format(
            card_titles,
            page_links,
            total_items if total_items is not None else "n/a",
            total_pages if total_pages is not None else "n/a",
            covers_ok if covers_ok is not None else "n/a",
            covers_missing if covers_missing is not None else "n/a",
        )
    )

    fail_reasons = []
    if proc.returncode != 0:
        fail_reasons.append("php exit {0}".format(proc.returncode))
    if re.search(r"(?i)Fatal error|Parse error", combined):
        fail_reasons.append("PHP fatal/parse in output")
    if LIST_FALLBACK_MARKER in stdout:
        fail_reasons.append("fallback marker {0}".format(LIST_FALLBACK_MARKER))
    if "lireate-blog-list-page" not in stdout:
        fail_reasons.append("missing lireate-blog-list-page")
    if "lx-site-footer" not in stdout:
        fail_reasons.append("missing lx-site-footer")

    footer_css_path = sandbox / "template/page/news/lireate-blog-footer.css"
    footer_css_text = ""
    if footer_css_path.is_file():
        footer_css_text = footer_css_path.read_text(encoding="utf-8", errors="replace")
    footer_fix_ok = FOOTER_FIX_CSS_MARKER in footer_css_text and FOOTER_FIX_CSS_RULE in footer_css_text
    footer_link_ok = "lireate-blog-footer.css?v=2026-10-19n" in stdout
    print(
        "diag_footer_css: marker={0} rule={1} html_link_19m={2}".format(
            FOOTER_FIX_CSS_MARKER in footer_css_text,
            FOOTER_FIX_CSS_RULE in footer_css_text,
            footer_link_ok,
        )
    )
    if not footer_fix_ok:
        fail_reasons.append("missing footer fix.css rules in deployed lireate-blog-footer.css")
    if not footer_link_ok:
        fail_reasons.append("missing lireate-blog-footer.css?v=2026-10-19n in list HTML")

    article_code = run_php_article_collage_diag(sandbox, repo, php_bin)
    if article_code != 0:
        fail_reasons.append("article collage-v1 diag failed")

    if fail_reasons:
        print("RESULT: FAIL ({0})".format("; ".join(fail_reasons)))
        code = 1
    else:
        print("RESULT: PASS")
        code = 0

    shutil.rmtree(tmp_root, ignore_errors=True)
    return code


def run_php_article_collage_diag(sandbox_docroot, office_repo, php_bin="php"):
    """Render collage-v1 and legacy article snippets; assert hero vs classic header."""
    import subprocess

    sandbox = Path(sandbox_docroot).resolve()
    repo = Path(office_repo).resolve()
    shared = repo / "pages/blog/_shared"
    layout = shared / "lireate-blog-layout.inc.php"
    if not layout.is_file():
        print("BLOCKER: missing {0}".format(layout))
        return 2

    runner = sandbox.parent / "run-article-collage-diag.php"
    runner.write_text(
        build_php_article_collage_diag_runner(
            host_to_php_fs_path(php_bin, repo, sandbox),
            host_to_php_fs_path(php_bin, repo, shared / "lireate-blog-lib.php"),
            host_to_php_fs_path(php_bin, repo, layout),
        ),
        encoding="utf-8",
    )

    print("=== PHP ARTICLE COLLAGE DIAG ===")
    proc = subprocess.run(
        [
            php_bin,
            "-d",
            "display_errors=1",
            "-d",
            "error_reporting=E_ALL",
            host_to_php_fs_path(php_bin, repo, runner),
        ],
        capture_output=True,
        text=True,
        timeout=60,
    )
    stdout = proc.stdout or ""
    stderr = proc.stderr or ""
    if stderr:
        print("--- article diag stderr ---")
        print(stderr.rstrip())

    article_css_path = sandbox / "template/page/news/lireate-blog-article.css"
    article_css = ""
    if article_css_path.is_file():
        article_css = article_css_path.read_text(encoding="utf-8", errors="replace")

    collage_chunk = ""
    legacy_chunk = ""
    if "<!-- lireate-blog-diag-article-section:collage -->" in stdout:
        parts = stdout.split("<!-- lireate-blog-diag-article-section:legacy -->", 1)
        collage_chunk = parts[0]
        legacy_chunk = parts[1] if len(parts) > 1 else ""
    collage_hero = COLLAGE_V1_HERO_ATTR in collage_chunk
    collage_no_dup = True
    if 'id="lireate-blog-prose"' in collage_chunk:
        prose_tail = collage_chunk.split('id="lireate-blog-prose"', 1)[1]
        collage_no_dup = "lx-article-cover" not in prose_tail
    legacy_no_hero = COLLAGE_V1_HERO_ATTR not in legacy_chunk
    legacy_header = "lireate-blog-header" in legacy_chunk and "lireate-blog-h1" in legacy_chunk
    css_ok = COLLAGE_V1_CSS_MARKER in article_css
    print(
        "diag_article_collage: hero_wrap={0} prose_no_dup_figure={1} legacy_no_hero={2} legacy_header={3} css_marker={4}".format(
            collage_hero,
            collage_no_dup,
            legacy_no_hero,
            legacy_header,
            css_ok,
        )
    )

    fail = []
    if proc.returncode != 0:
        fail.append("php exit {0}".format(proc.returncode))
    if not collage_hero:
        fail.append("collage hero missing")
    if not collage_no_dup:
        fail.append("collage duplicate figure in prose")
    if not legacy_no_hero:
        fail.append("legacy has collage hero")
    if not legacy_header:
        fail.append("legacy header missing")
    if not css_ok:
        fail.append("article css missing collage rules")

    if fail:
        print("ARTICLE COLLAGE RESULT: FAIL ({0})".format("; ".join(fail)))
        return 1
    print("ARTICLE COLLAGE RESULT: PASS")
    return 0


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

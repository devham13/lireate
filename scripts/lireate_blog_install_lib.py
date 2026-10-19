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


def build_php_article_collage_diag_runner(sandbox_docroot, load_php_path, layout_php_path):
    """Build php-cli runner for collage-v1 vs legacy article render (--diag)."""
    doc_lit = php_single_quoted_literal(str(Path(sandbox_docroot).resolve()))
    load_lit = php_single_quoted_literal(str(Path(load_php_path).resolve()))
    layout_lit = php_single_quoted_literal(str(Path(layout_php_path).resolve()))
    template = (
        "<?php\n"
        "ini_set('display_errors', '1');\n"
        "error_reporting(E_ALL);\n"
        "$_SERVER['DOCUMENT_ROOT'] = __DOC__;\n"
        "require __LOAD__;\n"
        "lireate_blog_require_lib();\n"
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
        "    $html = ob_get_clean();\n"
        "    ob_start();\n"
        "    lireate_blog_render_service_footer();\n"
        "    $html .= ob_get_clean();\n"
        "    return $html;\n"
        "}\n"
        "$collage_story = '<figure class=\"lx-article-cover\" data-cover-style=\"collage-v1\">'\n"
        "    . '<img src=\"/template/images/blog/diag-collage-v1.jpg\" alt=\"Diag\" width=\"860\" height=\"400\"></figure>'\n"
        "    . '<p>Collage body</p>';\n"
        "$legacy_story = '<p>Legacy body without collage cover.</p>'\n"
    "    . '<h3>Legacy section one</h3><p>Text.</p><h3>Legacy section two</h3><p>More.</p>';\n"
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
        {"DOC": doc_lit, "LOAD": load_lit, "LAYOUT": layout_lit},
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


PHP_DIAG_DEFAULT_TIMEZONE = "Europe/Moscow"
PHP_ISSUE_LINE_RE = re.compile(
    r"(?im)(?:^|<br\s*/?>|\n)\s*(?:<b>)?"
    r"(Fatal error|Parse error|Warning|Notice|Deprecated)"
    r"(?:</b>)?\s*:\s*[^\n<]+"
    r"(?:\s+in\s+(?:<b>)?[^\n<]+?(?:</b>)?\s+on line\s+(?:<b>)?\d+(?:</b>)?)?"
)
PHP_ISSUE_IGNORABLE_DIAG_RE = (
    re.compile(r"date\(\)\s*:\s*It is not safe to rely on the system's timezone settings", re.I),
    re.compile(r"timezone settings", re.I),
    re.compile(r"\bmysqli?_?", re.I),
    re.compile(r"\bmysql_connect\b", re.I),
    re.compile(r"Connection refused|Can't connect to local MySQL", re.I),
)


def _php_ini_candidate_paths(docroot=None):
    import os

    paths = []
    docroot = Path(docroot).expanduser() if docroot else None
    if docroot is not None and docroot.is_dir():
        paths.append(docroot / ".user.ini")
        paths.append(docroot / "php.ini")
    autopub = (os.environ.get("LIREATE_AUTOPUB_HOME") or "").strip()
    if autopub:
        paths.append(Path(autopub).expanduser() / "php-bin" / "lireate.ru" / "php.ini")
        paths.append(Path(autopub).expanduser() / "php-bin" / "lireate.ru" / ".user.ini")
    paths.append(Path.home() / "php-bin" / "lireate.ru" / "php.ini")
    paths.append(Path.home() / "php-bin" / "lireate.ru" / ".user.ini")
    seen = set()
    out = []
    for path in paths:
        key = str(path)
        if key not in seen:
            seen.add(key)
            out.append(path)
    return out


def _read_ini_directive(paths, directive):
    needle = directive.lower()
    for path in paths:
        if not path.is_file():
            continue
        try:
            text = path.read_text(encoding="utf-8", errors="replace")
        except OSError:
            continue
        for line in text.splitlines():
            line = line.strip()
            if not line or line.startswith(";") or line.startswith("#"):
                continue
            if line.lower().startswith(needle + "="):
                return line.split("=", 1)[1].strip().strip('"').strip("'")
    return None


def _ini_bool_off(value):
    if value is None:
        return True
    return str(value).strip().lower() in ("0", "off", "false", "no", "")


def resolve_php_diag_ini_settings(docroot=None, error_log_path=None):
    """CLI -d flags for --diag (match live: no HTML errors, log to file)."""
    candidates = _php_ini_candidate_paths(docroot)
    tz = _read_ini_directive(candidates, "date.timezone") or PHP_DIAG_DEFAULT_TIMEZONE
    display_errors = _read_ini_directive(candidates, "display_errors")
    if display_errors is None:
        display_errors = "0"
    log_errors = _read_ini_directive(candidates, "log_errors")
    if log_errors is None:
        log_errors = "1"
    error_reporting = _read_ini_directive(candidates, "error_reporting") or "E_ALL"
    settings = {
        "date.timezone": tz,
        "display_errors": display_errors,
        "log_errors": log_errors,
        "error_reporting": error_reporting,
    }
    if error_log_path:
        settings["error_log"] = str(error_log_path)
    return settings


def format_php_diag_ini_note(docroot=None, error_log_path=None):
    settings = resolve_php_diag_ini_settings(docroot, error_log_path=error_log_path)
    return ", ".join("{0}={1}".format(k, settings[k]) for k in sorted(settings.keys()))


def build_php_diag_argv(php_bin, office_repo, runner_path, docroot=None, error_log_path=None):
    argv = [php_bin]
    for key, val in resolve_php_diag_ini_settings(
        docroot, error_log_path=error_log_path
    ).items():
        argv.extend(["-d", "{0}={1}".format(key, val)])
    argv.append(host_to_php_fs_path(php_bin, office_repo, runner_path))
    return argv


PHP_LOG_ENTRY_RE = re.compile(
    r"PHP\s+(Fatal error|Parse error|Warning|Notice|Deprecated):\s+(.+?)\s+in\s+(.+?)\s+on line\s+(\d+)",
    re.IGNORECASE,
)
PHP_LOG_BLOCKING_RE = re.compile(
    r"(Fatal error|Parse error|Cannot redeclare|Failed opening required|"
    r"Allowed memory size|Call to undefined function)",
    re.IGNORECASE,
)


def php_error_signature(level, message, filepath, line_no):
    base = Path(filepath).name if filepath else ""
    msg = re.sub(r"\s+", " ", (message or "").strip())
    return "{0}: {1} in {2} on line {3}".format(
        level.capitalize() if level else "Error",
        msg[:200],
        base,
        line_no,
    )


def parse_php_error_log(text):
    blocking = []
    info = []
    signatures = set()
    for raw_line in (text or "").splitlines():
        line = raw_line.strip()
        if not line:
            continue
        m = PHP_LOG_ENTRY_RE.search(line)
        if m:
            level, message, filepath, line_no = m.groups()
            sig = php_error_signature(level, message, filepath, line_no)
            entry = {"line": line, "signature": sig, "level": level}
            signatures.add(sig)
            if PHP_LOG_BLOCKING_RE.search(line) or level.lower() in (
                "fatal error",
                "parse error",
            ):
                blocking.append(entry)
            else:
                info.append(entry)
            continue
        if PHP_LOG_BLOCKING_RE.search(line):
            entry = {"line": line, "signature": line[:200], "level": "fatal"}
            blocking.append(entry)
            signatures.add(entry["signature"])
    return {"blocking": blocking, "info": info, "signatures": signatures}


def print_php_log_diag_summary(label, log_parsed, max_info=6):
    if not log_parsed:
        return
    blocking = log_parsed.get("blocking") or []
    info = log_parsed.get("info") or []
    if blocking:
        print("php_log_{0}_blocking ({1}):".format(label, len(blocking)))
        for item in blocking[:8]:
            print("  {0}".format(item.get("line") or item.get("signature")))
    if info:
        print("php_log_{0}_info ({1}):".format(label, len(info)))
        for item in info[:max_info]:
            print("  {0}".format(item.get("line") or item.get("signature")))


def diff_new_log_signatures(baseline_signatures, current_log_parsed):
    base = set(baseline_signatures or [])
    current = set((current_log_parsed or {}).get("signatures") or [])
    return sorted(current - base)


def php_issue_is_ignorable_in_diag(message):
    text = message or ""
    return any(pattern.search(text) for pattern in PHP_ISSUE_IGNORABLE_DIAG_RE)


def extract_php_html_issues(body):
    body = body or ""
    issues = []
    for match in PHP_ISSUE_LINE_RE.finditer(body):
        line = match.group(0).strip()
        line = re.sub(r"<[^>]+>", "", line)
        line = re.sub(r"\s+", " ", line).strip()
        if not line:
            continue
        start = max(0, match.start() - 150)
        end = min(len(body), match.end() + 150)
        context = body[start:end]
        context = re.sub(r"\s+", " ", context).strip()
        if len(context) > 300:
            context = context[:300] + "…"
        issues.append({"line": line, "context": context})
    return issues


def filter_php_issues_for_diag(issues):
    blocking = []
    ignored = []
    for item in issues:
        msg = item.get("line") or ""
        if php_issue_is_ignorable_in_diag(msg):
            ignored.append(item)
        else:
            blocking.append(item)
    return blocking, ignored


def print_php_render_issue_details(body, diag_mode=False, label="render"):
    issues = extract_php_html_issues(body)
    if diag_mode:
        blocking, ignored = filter_php_issues_for_diag(issues)
    else:
        blocking, ignored = issues, []
    if ignored:
        print("php_{0}_ignored ({1}):".format(label, len(ignored)))
        for item in ignored[:5]:
            print("  {0}".format(item.get("line", "")))
    if blocking:
        print("php_{0}_errors ({1}):".format(label, len(blocking)))
        for item in blocking[:8]:
            print("  line: {0}".format(item.get("line", "")))
            print("  context: {0}".format(item.get("context", "")))
    return blocking


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


DOCROOT_MESSENGER_BUNDLE_NAMES = (
    "lireate-messenger-urls.php",
    "lireate-messenger-load.php",
    "lireate-messenger-require.inc.php",
    "lireate-service-messenger.inc.php",
    "lireate-service-messenger-include.inc.php",
)

BLOG_SHARED_TO_TEMPLATE = (
    "lireate-blog-config.php",
    "lireate-blog-load.php",
    "lireate-blog-lib.php",
    "lireate-blog-article.css",
    "lireate-blog-article.js",
    "lireate-blog-footer.css",
    "lireate-blog-list.css",
)


def deploy_docroot_messenger_shared(office_repo, docroot):
    """Copy messenger bundle to docroot/_shared (live layout for blog footer + services)."""
    docroot = Path(docroot).resolve()
    shared_src = Path(office_repo).resolve() / "pages" / "services" / "_shared"
    dest_dir = docroot / "_shared"
    if not shared_src.is_dir():
        return []
    created = []
    dest_dir.mkdir(parents=True, exist_ok=True)
    for name in DOCROOT_MESSENGER_BUNDLE_NAMES:
        src = shared_src / name
        if not src.is_file():
            continue
        dst = dest_dir / name
        existed = dst.is_file()
        shutil.copy2(str(src), str(dst))
        if not existed:
            created.append(Path("_shared") / name)
    return created


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
    services_shared = repo / "pages" / "services" / "_shared"
    for name in DOCROOT_MESSENGER_BUNDLE_NAMES:
        src = services_shared / name
        if src.is_file():
            by_dest[Path("template/lireate") / name] = src
    if messenger_src.is_file() and Path("template/lireate/lireate-messenger-urls.php") not in by_dest:
        by_dest[Path("template/lireate/lireate-messenger-urls.php")] = messenger_src
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
        "if (isset($news_article[0]) && is_array($news_article[0]) && !array_key_exists('category', $news_article[0])) {\n"
        "    $news_article[0]['category'] = isset($category) ? $category : '';\n"
        "}\n"
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
        "require_once $_SERVER['DOCUMENT_ROOT'] . '/template/page/news/lireate-blog-load.php';\n"
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

    article_rel = None
    article_unpatched_path = root / "article-template-unpatched.php"
    article_tpl_live = find_news_template(live_docroot)
    if article_tpl_live is not None and article_tpl_live.is_file():
        try:
            original = article_tpl_live.read_text(encoding="utf-8", errors="replace")
            article_unpatched_path.write_text(original, encoding="utf-8")
            planned = plan_template_content(original)
            article_rel = article_tpl_live.relative_to(live_docroot)
            dest = sandbox / article_rel
            dest.parent.mkdir(parents=True, exist_ok=True)
            dest.write_text(planned, encoding="utf-8")
        except (OSError, ValueError):
            article_rel = None

    if article_rel is None:
        fixture_newstyle = office_repo / "scripts/fixtures/cms-newstyle-article-snippet.php"
        if fixture_newstyle.is_file():
            article_rel = Path("template/page/newStyle.php")
            dest = sandbox / article_rel
            dest.parent.mkdir(parents=True, exist_ok=True)
            snippet = fixture_newstyle.read_text(encoding="utf-8", errors="replace")
            planned, _changed, _desc = patch_news_template_content(snippet)
            dest.write_text(planned, encoding="utf-8")

    stage_service_diag_tree(sandbox, office_repo)
    deploy_docroot_messenger_shared(office_repo, sandbox)

    return sandbox, root, list_rel, article_rel


SERVICE_DIAG_SLUG = "registraciya-tovarnogo-znaka-v-kitae"


def stage_service_diag_tree(sandbox, office_repo):
    """Copy service + docroot/_shared like live hosting (PHP smoke, not repo-only paths)."""
    repo = Path(office_repo).resolve()
    slug = SERVICE_DIAG_SLUG
    svc_src = repo / "pages/services" / slug
    if not (svc_src / "index.php").is_file():
        return
    web_path = probe_service_web_path(repo).strip("/")
    if web_path:
        svc_dst = sandbox / web_path
    else:
        svc_dst = sandbox / slug.replace("-", "_")
    shared_src = repo / "pages/services/_shared"
    docroot_shared = sandbox / "_shared"
    for path in svc_src.rglob("*"):
        if not path.is_file():
            continue
        rel = path.relative_to(svc_src)
        target = svc_dst / rel
        target.parent.mkdir(parents=True, exist_ok=True)
        shutil.copy2(str(path), str(target))
    messenger_names = DOCROOT_MESSENGER_BUNDLE_NAMES
    if shared_src.is_dir():
        docroot_shared.mkdir(parents=True, exist_ok=True)
        for name in messenger_names:
            src = shared_src / name
            if src.is_file():
                shutil.copy2(str(src), str(docroot_shared / name))
        pages_shared = sandbox / "pages/services/_shared"
        pages_shared.mkdir(parents=True, exist_ok=True)
        for name in messenger_names:
            src = shared_src / name
            if src.is_file():
                shutil.copy2(str(src), str(pages_shared / name))


def resolve_service_diag_index(sandbox, office_repo):
    web_path = probe_service_web_path(office_repo).strip("/")
    if web_path:
        idx = sandbox / web_path / "index.php"
        if idx.is_file():
            return idx
    slug = SERVICE_DIAG_SLUG
    fallback = sandbox / "pages/services" / slug / "index.php"
    if fallback.is_file():
        return fallback
    return sandbox / web_path / "index.php" if web_path else fallback


def probe_service_web_path(office_repo):
    slug = SERVICE_DIAG_SLUG
    idx = Path(office_repo) / "pages/services" / slug / "index.php"
    if not idx.is_file():
        return "/registraciya_tovarnogo_znaka_v_kitae/"
    text = idx.read_text(encoding="utf-8", errors="replace")
    m = re.search(r"\$path\s*=\s*['\"]([^'\"]+)['\"]", text)
    if m:
        return m.group(1)
    return "/" + slug.replace("-", "_") + "/"


def build_php_http_verify_articles_diag_runner(
    sandbox_docroot, article_include_path, slug_paths, bodies_php_array_literal
):
    doc_lit = php_single_quoted_literal(str(Path(sandbox_docroot).resolve()))
    art_lit = php_single_quoted_literal(str(Path(article_include_path).resolve()))
    slugs_php = ",\n".join(php_single_quoted_literal(p.strip("/")) for p in slug_paths)
    template = (
        "<?php\n"
        "ini_set('display_errors', '1');\n"
        "error_reporting(E_ALL);\n"
        "$_SERVER['DOCUMENT_ROOT'] = __DOC__;\n"
        "$default_story = __DEFAULT__;\n"
        "$bodies = __BODIES__;\n"
        "$slugs = array(\n__SLUGS__\n);\n"
        "$category = 'Товарный знак';\n"
        "$catUrl = '/news/';\n"
        "foreach ($slugs as $slug) {\n"
        "  $story = isset($bodies[$slug]) ? $bodies[$slug] : $default_story;\n"
        "  $_SERVER['REQUEST_URI'] = '/' . $slug . '/';\n"
        "  $_SERVER['SCRIPT_FILENAME'] = __ARTICLE__;\n"
        "  $news_article = array(array(\n"
        "    'name' => 'HTTP verify diag ' . $slug,\n"
        "    'full_story' => $story,\n"
        "    'datetime' => 1760832000,\n"
        "    'seo_url' => $slug,\n"
        "  ));\n"
        "  echo \"<!-- lireate-http-verify-slug:\" . $slug . \" -->\\n\";\n"
        "  ob_start();\n"
        "  include __ARTICLE__;\n"
        "  echo ob_get_clean();\n"
        "  echo \"\\n<!-- /lireate-http-verify-slug:\" . $slug . \" -->\\n\";\n"
        "}\n"
    )
    return fill_php_runner_template(
        template,
        {
            "DOC": doc_lit,
            "ARTICLE": art_lit,
            "SLUGS": slugs_php,
            "DEFAULT": php_single_quoted_literal(DEFAULT_HTTP_VERIFY_ARTICLE_BODY),
            "BODIES": bodies_php_array_literal,
        },
    )


def _php_array_literal_from_map(string_map):
    lines = ["array("]
    for key, val in sorted(string_map.items()):
        lines.append(
            "  {0} => {1},".format(
                php_single_quoted_literal(str(key)),
                php_single_quoted_literal(str(val)),
            )
        )
    lines.append(")")
    return "\n".join(lines)


def capture_unpatched_newstyle_log_baseline(
    tmp_root, sandbox, repo, php_bin, article_rel, error_log_path
):
    unpatched = Path(tmp_root) / "article-template-unpatched.php"
    if not unpatched.is_file() or article_rel is None:
        return set()
    slug = probe_article_path(repo).strip("/")
    runner = Path(tmp_root) / "run-newstyle-baseline.php"
    runner.write_text(
        build_php_newstyle_diag_runner(
            host_to_php_fs_path(php_bin, repo, sandbox),
            host_to_php_fs_path(php_bin, repo, unpatched),
            slug,
        ),
        encoding="utf-8",
    )
    print("=== PHP NEWSTYLE BASELINE (unpatched template, error_log only) ===")
    _code, _stdout, _combined, log_parsed = _php_run_capture(
        php_bin,
        repo,
        runner,
        "newstyle-baseline",
        docroot=sandbox,
        error_log_path=error_log_path,
    )
    print_php_log_diag_summary("baseline", log_parsed)
    return set(log_parsed.get("signatures") or [])


def run_php_http_verify_articles_diag(
    sandbox, repo, php_bin, article_rel, error_log_path=None, baseline_log_signatures=None
):
    if article_rel is None:
        print("BLOCKER: no article CMS template for HTTP verify article diag")
        return 2
    article_include = sandbox / article_rel
    if not article_include.is_file():
        print("BLOCKER: article template missing in sandbox ({0})".format(article_include))
        return 2
    slug_paths = probe_article_paths(repo)
    bodies = load_http_verify_article_bodies_fixture(repo)
    bodies_lit = _php_array_literal_from_map(bodies)
    runner = sandbox.parent / "run-http-verify-articles-diag.php"
    runner.write_text(
        build_php_http_verify_articles_diag_runner(
            host_to_php_fs_path(php_bin, repo, sandbox),
            host_to_php_fs_path(php_bin, repo, article_include),
            slug_paths,
            bodies_lit,
        ),
        encoding="utf-8",
    )
    print("=== PHP HTTP VERIFY ARTICLES DIAG (same markers as --apply) ===")
    print("paths: {0}".format(", ".join(slug_paths)))
    code, stdout, combined, log_parsed = _php_run_capture(
        php_bin,
        repo,
        runner,
        "http-verify-articles",
        docroot=sandbox,
        error_log_path=error_log_path,
    )
    fail = []
    if code != 0:
        fail.append("php exit {0}".format(code))
    if re.search(r"(?i)Fatal error|Parse error|Cannot redeclare", combined):
        fail.append("PHP fatal/parse/redeclare")
    for slug_path in slug_paths:
        slug = slug_path.strip("/")
        start = "<!-- lireate-http-verify-slug:{0} -->".format(slug)
        end = "<!-- /lireate-http-verify-slug:{0} -->".format(slug)
        if start not in stdout:
            fail.append("missing render chunk for {0}".format(slug))
            continue
        chunk = stdout.split(start, 1)[1].split(end, 1)[0]
        diag = diagnose_http_body(chunk, {})
        markers = diag.get("markers") or {}
        marker_errs = verify_article_marker_errors(markers)
        print(
            "  {0}: toc={1} footer={2} blog_page={3}{4}".format(
                slug,
                markers.get("toc"),
                markers.get("lx-site-footer"),
                markers.get("lireate-blog-page"),
                (" FAIL: " + "; ".join(marker_errs)) if marker_errs else " OK",
            )
        )
        if marker_errs:
            fail.append("{0}: {1}".format(slug, "; ".join(marker_errs)))
    new_sigs = diff_new_log_signatures(baseline_log_signatures, log_parsed)
    if log_parsed.get("blocking"):
        for item in log_parsed["blocking"]:
            fail.append("PHP log fatal: {0}".format(item.get("signature") or item.get("line")))
    if new_sigs:
        print("php_log_new_vs_baseline ({0}):".format(len(new_sigs)))
        for sig in new_sigs[:12]:
            print("  {0}".format(sig))
        fail.append(
            "new PHP notice/warning vs unpatched template ({0})".format(len(new_sigs))
        )
    if fail:
        print_php_log_diag_summary("http-verify-articles", log_parsed)
        print("HTTP VERIFY ARTICLES RESULT: FAIL ({0})".format("; ".join(fail)))
        return 1
    print("HTTP VERIFY ARTICLES RESULT: PASS")
    return 0


def build_php_newstyle_diag_runner(sandbox_docroot, article_include_path, article_slug):
    doc_lit = php_single_quoted_literal(str(Path(sandbox_docroot).resolve()))
    art_lit = php_single_quoted_literal(str(Path(article_include_path).resolve()))
    slug_lit = php_single_quoted_literal(str(article_slug).strip("/"))
    template = (
        "<?php\n"
        "ini_set('display_errors', '1');\n"
        "error_reporting(E_ALL);\n"
        "$_SERVER['DOCUMENT_ROOT'] = __DOC__;\n"
        "$_SERVER['REQUEST_URI'] = '/' . __SLUG__ . '/';\n"
        "$_SERVER['SCRIPT_FILENAME'] = __ARTICLE__;\n"
        "$news_article = array(array(\n"
        "  'name' => 'Diag article',\n"
        "  'full_story' => '<p>Lead</p><h2>Section one</h2><p>Body for diag.</p>',\n"
        "  'datetime' => 1760832000,\n"
        "  'seo_url' => __SLUG__,\n"
        "));\n"
        "$category = 'Товарный знак';\n"
        "$catUrl = '/news/';\n"
        "ob_start();\n"
        "include __ARTICLE__;\n"
        "echo ob_get_clean();\n"
    )
    return fill_php_runner_template(
        template,
        {"DOC": doc_lit, "ARTICLE": art_lit, "SLUG": slug_lit},
    )


def build_php_service_diag_runner(sandbox_docroot, service_index_path, web_path):
    doc_lit = php_single_quoted_literal(str(Path(sandbox_docroot).resolve()))
    idx_lit = php_single_quoted_literal(str(Path(service_index_path).resolve()))
    path_lit = php_single_quoted_literal(str(web_path))
    template = (
        "<?php\n"
        "ini_set('display_errors', '1');\n"
        "error_reporting(E_ALL);\n"
        "$_SERVER['DOCUMENT_ROOT'] = __DOC__;\n"
        "$_SERVER['HTTP_HOST'] = 'lireate.ru';\n"
        "$_SERVER['REQUEST_URI'] = __PATH__;\n"
        "$_SERVER['SCRIPT_FILENAME'] = __INDEX__;\n"
        "ob_start();\n"
        "include __INDEX__;\n"
        "echo ob_get_clean();\n"
    )
    return fill_php_runner_template(
        template,
        {"DOC": doc_lit, "INDEX": idx_lit, "PATH": path_lit},
    )


def _php_run_capture(php_bin, repo, runner_path, label, docroot=None, error_log_path=None):
    import subprocess

    log_path = Path(error_log_path) if error_log_path else None
    if log_path is not None:
        log_path.parent.mkdir(parents=True, exist_ok=True)
        log_path.write_text("", encoding="utf-8")
    proc = subprocess.run(
        build_php_diag_argv(
            php_bin, repo, runner_path, docroot=docroot, error_log_path=log_path
        ),
        capture_output=True,
        text=True,
        timeout=90,
    )
    stdout = proc.stdout or ""
    stderr = proc.stderr or ""
    combined = stdout + stderr
    log_parsed = {"blocking": [], "info": [], "signatures": set()}
    if log_path is not None and log_path.is_file():
        try:
            log_parsed = parse_php_error_log(log_path.read_text(encoding="utf-8", errors="replace"))
        except OSError:
            pass
    if stderr and not _ini_bool_off(resolve_php_diag_ini_settings(docroot).get("display_errors")):
        print("--- {0} stderr ---".format(label))
        print(stderr.rstrip())
    return proc.returncode, stdout, combined, log_parsed


def run_php_newstyle_path_diag(
    sandbox, repo, php_bin, article_rel, error_log_path=None, baseline_log_signatures=None
):
    if article_rel is None:
        print("BLOCKER: no article CMS template for newStyle diag")
        return 2
    article_include = sandbox / article_rel
    if not article_include.is_file():
        print("BLOCKER: article template missing in sandbox ({0})".format(article_include))
        return 2
    slug = probe_article_path(repo).strip("/")
    runner = sandbox.parent / "run-newstyle-diag.php"
    runner.write_text(
        build_php_newstyle_diag_runner(
            host_to_php_fs_path(php_bin, repo, sandbox),
            host_to_php_fs_path(php_bin, repo, article_include),
            slug,
        ),
        encoding="utf-8",
    )
    print("=== PHP NEWSTYLE ARTICLE DIAG (dual lib tree) ===")
    print("article_template: {0}".format(article_rel))
    code, stdout, combined, log_parsed = _php_run_capture(
        php_bin,
        repo,
        runner,
        "newstyle",
        docroot=sandbox,
        error_log_path=error_log_path,
    )
    fail = []
    if code != 0:
        fail.append("php exit {0}".format(code))
    if re.search(r"(?i)Fatal error|Parse error|Cannot redeclare", combined):
        fail.append("PHP fatal/parse/redeclare in cli output")
    new_sigs = diff_new_log_signatures(baseline_log_signatures, log_parsed)
    marker_errors, diag = verify_render_html_errors(
        stdout,
        {},
        "article",
        diag_sandbox=True,
        diag_log_parsed=log_parsed,
        new_log_signatures=new_sigs,
    )
    if marker_errors:
        fail.extend(marker_errors)
    markers = diag.get("markers") or {}
    print(
        "newstyle_markers: blog_page={0} footer={1} toc={2}".format(
            markers.get("lireate-blog-page"),
            markers.get("lx-site-footer"),
            markers.get("toc"),
        )
    )
    print_php_log_diag_summary("newstyle", log_parsed)
    if fail:
        print("NEWSTYLE RESULT: FAIL ({0})".format("; ".join(fail)))
        print("--- stdout tail ---")
        print("\n".join(stdout.splitlines()[-40:]))
        return 1
    print("NEWSTYLE RESULT: PASS")
    return 0


def run_php_service_page_diag(sandbox, repo, php_bin):
    idx = resolve_service_diag_index(sandbox, repo)
    if not idx.is_file():
        print("WARN: service diag skipped (no {0})".format(idx))
        return 0
    web_path = probe_service_web_path(repo)
    runner = sandbox.parent / "run-service-diag.php"
    runner.write_text(
        build_php_service_diag_runner(
            host_to_php_fs_path(php_bin, repo, sandbox),
            host_to_php_fs_path(php_bin, repo, idx),
            web_path,
        ),
        encoding="utf-8",
    )
    print("=== PHP SERVICE PAGE DIAG ===")
    print("service: {0} ({1})".format(SERVICE_DIAG_SLUG, web_path))
    code, stdout, combined, _log_parsed = _php_run_capture(
        php_bin, repo, runner, "service", docroot=sandbox
    )
    fail = []
    if code != 0:
        fail.append("php exit {0}".format(code))
    if re.search(r"(?i)Fatal error|Parse error|Cannot redeclare", combined):
        fail.append("PHP fatal/parse/redeclare")
    if "lx-nav-pill" not in stdout and "lx-service" not in stdout:
        fail.append("missing lx service markers")
    if fail:
        print("SERVICE RESULT: FAIL ({0})".format("; ".join(fail)))
        print("--- stdout tail ---")
        print("\n".join(stdout.splitlines()[-30:]))
        return 1
    print("SERVICE RESULT: PASS")
    return 0


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
    issues = [item["line"] for item in extract_php_html_issues(body)[:12]]
    return {
        "markers": markers,
        "tail": tail,
        "php_issues": issues,
        "cache": _cache_header_note(headers or {}),
        "length": len(body),
    }


def verify_news_list_marker_errors(markers):
    """Same content rules as HTTP verify for /news/ (no status line)."""
    markers = markers or {}
    page_errors = []
    if markers.get("lireate-blog-list-fallback"):
        page_errors.append("list rendered in fallback mode ({0})".format(LIST_FALLBACK_MARKER))
    if not markers.get("lireate-blog-list-page"):
        page_errors.append("missing lireate-blog-list-page (list patch/CMS not active?)")
    if not markers.get("lx-site-footer"):
        page_errors.append("missing lx-site-footer")
    return page_errors


def verify_article_marker_errors(markers):
    """Same content rules as HTTP verify for article URLs (no status line)."""
    markers = markers or {}
    page_errors = []
    if not markers.get("lireate-blog-page"):
        page_errors.append("missing lireate-blog-page (patch/CMS not active?)")
    if not markers.get("lx-site-footer"):
        page_errors.append("missing lx-site-footer")
    if not markers.get("toc"):
        page_errors.append("missing TOC markup")
    return page_errors


def verify_render_html_errors(
    body,
    headers,
    page_kind,
    diag_sandbox=False,
    diag_log_parsed=None,
    new_log_signatures=None,
):
    """Return (errors, diag) using the same marker rules as live HTTP verify."""
    diag = diagnose_http_body(body, headers or {})
    markers = diag.get("markers") or {}
    raw_issues = extract_php_html_issues(body)
    errs = []
    if diag_sandbox:
        log_parsed = diag_log_parsed or {}
        for item in log_parsed.get("blocking") or []:
            errs.append(
                "PHP log fatal: {0}".format(
                    (item.get("signature") or item.get("line") or "")[:240]
                )
            )
        for sig in new_log_signatures or []:
            errs.append("new PHP notice/warning vs unpatched template: {0}".format(sig[:240]))
    else:
        blocking = raw_issues
        if blocking:
            first = blocking[0].get("line", "PHP error in HTML")
            errs.append("PHP errors in HTML: {0}".format(first[:240]))
        diag["php_issues_blocking"] = [item.get("line") for item in blocking]
    if page_kind == "news_list":
        errs.extend(verify_news_list_marker_errors(markers))
    else:
        errs.extend(verify_article_marker_errors(markers))
    return errs, diag


def load_http_verify_article_bodies_fixture(office_repo):
    path = Path(office_repo) / "scripts" / "fixtures" / "blog-http-verify-article-bodies.json"
    if not path.is_file():
        return {}
    try:
        data = json.loads(path.read_text(encoding="utf-8"))
    except (json.JSONDecodeError, OSError):
        return {}
    return data if isinstance(data, dict) else {}


DEFAULT_HTTP_VERIFY_ARTICLE_BODY = (
    "<p>Lead paragraph for verify.</p><h2>Раздел для оглавления</h2><p>Body text.</p>"
)

DOCROOT_SHARED_PHP_NAMES = (
    "lireate-messenger-urls.php",
    "lireate-messenger-load.php",
    "lireate-messenger-require.inc.php",
)
SERVICE_FOOTER_NAME = "lireate-service-footer.inc.php"
SERVICE_STICKY_REL = Path("blocks") / "lireate-sticky-mobile.inc.php"


def _docroot_shared_rel_dirs(url_path):
    parts = [p for p in str(url_path).strip("/").split("/") if p]
    rels = [Path("_shared")]
    if len(parts) > 1:
        rels.append(Path("/".join(parts[:-1])) / "_shared")
    seen = set()
    unique = []
    for rel in rels:
        key = str(rel).replace("\\", "/")
        if key not in seen:
            seen.add(key)
            unique.append(rel)
    return unique


def plan_published_service_refresh(office_repo, docroot):
    """Pairs (src office-repo file, relative path under docroot) for live service dirs."""
    from lireate_hosting_services_lib import (
        dest_dir_for_manifest,
        dest_looks_published,
        list_service_dirs,
        load_manifest,
    )

    office_repo = Path(office_repo).resolve()
    docroot = Path(docroot).resolve()
    shared = office_repo / "pages" / "services" / "_shared"
    planned = []
    seen_dest = set()

    def add_copy(src, rel):
        rel_key = str(rel).replace("\\", "/")
        if rel_key in seen_dest or not src.is_file():
            return
        seen_dest.add(rel_key)
        planned.append((src, Path(rel)))

    for service_dir in list_service_dirs(office_repo):
        try:
            manifest = load_manifest(service_dir)
        except (ValueError, json.JSONDecodeError):
            continue
        dest = dest_dir_for_manifest(docroot, manifest)
        if not dest_looks_published(dest):
            continue
        url_path = manifest["url_path"]
        footer_src = service_dir / SERVICE_FOOTER_NAME
        if not footer_src.is_file():
            footer_src = shared / SERVICE_FOOTER_NAME
        if footer_src.is_file():
            add_copy(footer_src, Path(url_path) / SERVICE_FOOTER_NAME)
        sticky_src = service_dir / SERVICE_STICKY_REL
        if sticky_src.is_file():
            add_copy(sticky_src, Path(url_path) / SERVICE_STICKY_REL)
        for rel_dir in _docroot_shared_rel_dirs(url_path):
            for name in DOCROOT_SHARED_PHP_NAMES:
                src = shared / name
                if src.is_file():
                    add_copy(src, rel_dir / name)
    return planned


def refresh_published_service_includes(office_repo, docroot, dry_run=False):
    planned = plan_published_service_refresh(office_repo, docroot)
    if dry_run:
        return [rel for _src, rel in planned], []
    updated = []
    for src, rel in planned:
        dst = docroot / rel
        dst.parent.mkdir(parents=True, exist_ok=True)
        shutil.copy2(str(src), str(dst))
        updated.append(rel)
    return [rel for _src, rel in planned], updated


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
    marker_errors, _diag2 = verify_render_html_errors(
        body, headers, "news_list" if label == "news_list" else "article"
    )
    page_errors.extend(marker_errors)

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
            if any(_verify_error_is_server_fault(e) for e in round_errors):
                print(
                    "HTTP verify: server error (5xx) — aborting further rounds (immediate rollback)"
                )
                return errors
    return errors


def _verify_error_is_server_fault(err_line):
    """True when verify reported HTTP 5xx for a checked URL."""
    if not err_line:
        return False
    m = re.search(r"HTTP\s+(\d{3})", str(err_line))
    if not m:
        return False
    code = int(m.group(1))
    return 500 <= code <= 599


def run_php_list_diag(live_docroot, office_repo=None):
    """php-cli list render via patched CMS list template (same path as live /news)."""
    import os
    import subprocess

    live_docroot = Path(live_docroot).resolve()
    repo = Path(office_repo) if office_repo is not None else Path(__file__).resolve().parents[1]
    sandbox, tmp_root, list_rel, article_rel = build_diag_sandbox(live_docroot, repo)
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
    error_log_path = tmp_root / "php-diag-errors.log"
    print(
        "diag_php_ini: {0}".format(
            format_php_diag_ini_note(sandbox, error_log_path=error_log_path)
        )
    )
    print("cms_template: {0}".format(list_rel))
    print("list_cms_inc: {0} (from office-repo copy)".format(cms.relative_to(sandbox)))

    error_log_path.write_text("", encoding="utf-8")
    proc = subprocess.run(
        build_php_diag_argv(
            php_bin, repo, runner, docroot=sandbox, error_log_path=error_log_path
        ),
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
    if re.search(r"(?i)Fatal error|Parse error|Cannot redeclare", combined):
        fail_reasons.append("PHP fatal/parse/redeclare in output")
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

    list_log_parsed = parse_php_error_log(
        error_log_path.read_text(encoding="utf-8", errors="replace")
        if error_log_path.is_file()
        else ""
    )
    if list_log_parsed.get("blocking"):
        fail_reasons.append("PHP log fatal during list render")
        print_php_log_diag_summary("list", list_log_parsed)

    baseline_log_signatures = capture_unpatched_newstyle_log_baseline(
        tmp_root, sandbox, repo, php_bin, article_rel, error_log_path
    )

    article_code = run_php_article_collage_diag(
        sandbox, repo, php_bin, error_log_path=error_log_path
    )
    if article_code != 0:
        fail_reasons.append("article collage-v1 diag failed")

    if (
        run_php_newstyle_path_diag(
            sandbox,
            repo,
            php_bin,
            article_rel,
            error_log_path=error_log_path,
            baseline_log_signatures=baseline_log_signatures,
        )
        != 0
    ):
        fail_reasons.append("newStyle article render diag failed")
    if (
        run_php_http_verify_articles_diag(
            sandbox,
            repo,
            php_bin,
            article_rel,
            error_log_path=error_log_path,
            baseline_log_signatures=baseline_log_signatures,
        )
        != 0
    ):
        fail_reasons.append("HTTP verify article paths diag failed")
    if run_php_service_page_diag(sandbox, repo, php_bin) != 0:
        fail_reasons.append("service page render diag failed")

    if fail_reasons:
        print("RESULT: FAIL ({0})".format("; ".join(fail_reasons)))
        code = 1
    else:
        print("RESULT: PASS")
        code = 0

    shutil.rmtree(tmp_root, ignore_errors=True)
    return code


def run_php_article_collage_diag(sandbox_docroot, office_repo, php_bin="php", error_log_path=None):
    """Render collage-v1 and legacy article snippets; assert hero vs classic header."""
    sandbox = Path(sandbox_docroot).resolve()
    repo = Path(office_repo).resolve()
    shared = repo / "pages/blog/_shared"
    layout = shared / "lireate-blog-layout.inc.php"
    if not layout.is_file():
        print("BLOCKER: missing {0}".format(layout))
        return 2

    load_php = sandbox / "template/page/news/lireate-blog-load.php"
    if not load_php.is_file():
        load_php = shared / "lireate-blog-load.php"
    runner = sandbox.parent / "run-article-collage-diag.php"
    runner.write_text(
        build_php_article_collage_diag_runner(
            host_to_php_fs_path(php_bin, repo, sandbox),
            host_to_php_fs_path(php_bin, repo, load_php),
            host_to_php_fs_path(php_bin, repo, layout),
        ),
        encoding="utf-8",
    )

    print("=== PHP ARTICLE COLLAGE DIAG ===")
    proc_code, stdout, _combined, log_parsed = _php_run_capture(
        php_bin,
        repo,
        runner,
        "article-collage",
        docroot=sandbox,
        error_log_path=error_log_path,
    )
    print_php_log_diag_summary("collage", log_parsed)

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
    legacy_diag = diagnose_http_body(legacy_chunk, {})
    legacy_marker_errors = verify_article_marker_errors(legacy_diag.get("markers") or {})
    legacy_toc = (legacy_diag.get("markers") or {}).get("toc")
    css_ok = COLLAGE_V1_CSS_MARKER in article_css
    print(
        "diag_article_collage: hero_wrap={0} prose_no_dup_figure={1} legacy_no_hero={2} legacy_header={3} legacy_toc={4} css_marker={5}".format(
            collage_hero,
            collage_no_dup,
            legacy_no_hero,
            legacy_header,
            legacy_toc,
            css_ok,
        )
    )

    fail = []
    if proc_code != 0:
        fail.append("php exit {0}".format(proc_code))
    if log_parsed.get("blocking"):
        for item in log_parsed["blocking"]:
            fail.append("PHP log fatal: {0}".format(item.get("signature") or item.get("line")))
    if not collage_hero:
        fail.append("collage hero missing")
    if not collage_no_dup:
        fail.append("collage duplicate figure in prose")
    if not legacy_no_hero:
        fail.append("legacy has collage hero")
    if not legacy_header:
        fail.append("legacy header missing")
    if legacy_marker_errors:
        fail.extend(legacy_marker_errors)
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

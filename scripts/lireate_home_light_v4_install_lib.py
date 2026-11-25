"""Home page Light V4 install helpers (CMS / and $temp[page]==home)."""

import difflib
import json
import os
import re
import shutil
import subprocess
import tempfile
from datetime import datetime, timezone
from pathlib import Path

import lireate_cms_main_body_class as cms_body

INSTALL_MARKER = "lireate-home-light-v4-install:v1"
HEAD_MARKER = "lireate-home-light-v4-install-head:v1"
LEGACY_MARKER = "lireate-home-light-v4-legacy:snapshot"
BODY_CLASS_COND = (
    "<?php if($temp['page']=='home' || (isset($_SERVER['REQUEST_URI']) && "
    "preg_match('#^/(?:index\\.php)?$#', parse_url((string)$_SERVER['REQUEST_URI'], PHP_URL_PATH)?:''))) "
    "echo ' lireate-home-light-v4 lireate-theme-light-v4';?>"
)
CMS_INCLUDE = "/template/page/home/lireate-home-cms.inc.php"
SHARED_DOCROOT_REL = Path("pages/home/_shared")
TEMPLATE_REL = Path("template/page/home")
LEGACY_REL = TEMPLATE_REL / "lireate-home-legacy.inc.php"
MAIN_REL = Path("template/main.php")
HOME_V2_FLAG_REL = Path("template/page/home/lireate-home-v2.on")
HOME_V2_FLAG_DISABLED_SUFFIX = ".lireate-light-v4-disabled"
FORBIDDEN_TOUCH = {".htaccess"}
LIREATE_HOME_WRAPPER_RE = re.compile(r'<div[^>]*\bid=["\']lireate-home["\'][^>]*>', re.IGNORECASE)
PAGE_CONTAINER_RE = re.compile(r"<div\s+class=\"page_container\">", re.IGNORECASE)
HOME_COND_RE = re.compile(r"<\?php\s+if\s*\(\s*\$temp\['page'\]\s*==\s*'home'\s*\)\s*\{", re.IGNORECASE)
MAIN_SCREEN_HERO_RE = re.compile(
    r"<div[^>]*class=\"[^\"]*\bmain-screen\b[^\"]*\blireate-hero-v4\b[^\"]*\"[^>]*>",
    re.IGNORECASE,
)
HEAD_CLOSE_RE = re.compile(r"</head>", re.IGNORECASE)
SECTION_MARKERS = (INSTALL_MARKER, "lireate-hero-v4", "page_container", "main-screen")


def unified_diff(original, planned, label):
    return "".join(
        difflib.unified_diff(
            original.splitlines(keepends=True),
            planned.splitlines(keepends=True),
            fromfile=label + " (before)",
            tofile=label + " (after)",
        )
    )


def resolve_php_bin():
    explicit = os.environ.get("PHP_BIN", "").strip()
    for cand in (explicit, "php", "php7.4", "php8.3", "php5.6"):
        if not cand:
            continue
        if shutil.which(cand):
            return cand
    return "php"


def run_subprocess(args, timeout=30):
    kwargs = dict(stdout=subprocess.PIPE, stderr=subprocess.PIPE, timeout=timeout)
    try:
        return subprocess.run(args, **kwargs, text=True)
    except TypeError:
        return subprocess.run(args, **kwargs, universal_newlines=True)


def lint_php_content(php_bin, content, label="patched.php"):
    php_bin = resolve_php_bin() if not php_bin else php_bin
    tmp = tempfile.NamedTemporaryFile("w", suffix=".php", delete=False, encoding="utf-8")
    try:
        tmp.write(content)
        tmp.close()
        proc = run_subprocess([php_bin, "-l", tmp.name])
        if proc.returncode != 0:
            msg = (proc.stderr or proc.stdout or "").strip()
            return "{0}: {1}".format(label, msg)
        return None
    finally:
        try:
            os.unlink(tmp.name)
        except OSError:
            pass


def find_main_template(docroot):
    path = Path(docroot) / MAIN_REL
    if path.is_file():
        return path
    candidates = []
    for p in (Path(docroot) / "template").rglob("main.php"):
        try:
            text = p.read_text(encoding="utf-8", errors="replace")
        except OSError:
            continue
        if INSTALL_MARKER in text or "lireate-hero-v4" in text or "page_container" in text:
            score = 100
            if p.name == "main.php":
                score += 20
            candidates.append((score, p))
    if not candidates:
        return None
    candidates.sort(key=lambda x: (-x[0], len(str(x[1]))))
    return candidates[0][1]


def copy_plan(office_repo):
    repo = Path(office_repo).resolve()
    pairs = []
    shared = repo / "pages" / "home" / "_shared"
    if shared.is_dir():
        for path in sorted(shared.iterdir()):
            if path.is_file() and path.suffix == ".php":
                pairs.append((path, TEMPLATE_REL / path.name))
                pairs.append((path, SHARED_DOCROOT_REL / path.name))
    for rel in (
        Path("template/css/lireate-home-light-v4.css"),
        Path("template/js/lireate-home-light-v4.js"),
        Path("pages/services/_shared/lireate-lead-consent-fields.inc.php"),
        Path("pages/services/_shared/lireate-lead-consent-marketing.js"),
        Path("template/js/lireate-home-consent-marketing.js"),
    ):
        src = repo / rel
        if src.is_file():
            pairs.append((src, rel))
    return pairs


def _replacement_block():
    return (
        "<section id=\"lireate-home-light-v4-wrap\">\n"
        "<?php /* %s */ ?>\n"
        "<?php require $_SERVER['DOCUMENT_ROOT'] . '%s'; ?>\n"
        "</section>\n"
    ) % (INSTALL_MARKER, CMS_INCLUDE)


def _head_snippet():
    version = "20261125a"
    return (
        "<?php if($temp['page']=='home' || (isset($_SERVER['REQUEST_URI']) && "
        "preg_match('#^/(?:index\\.php)?$#', parse_url((string)$_SERVER['REQUEST_URI'], PHP_URL_PATH)?:''))){ ?>\n"
        "<link rel=\"stylesheet\" href=\"/template/css/lireate-home-light-v4.css?v=%s\"><!-- %s -->\n"
        "<link rel=\"stylesheet\" href=\"/template/css/lireate-light-v4.css?v=%s\">\n"
        "<script src=\"/template/js/lireate-home-light-v4.js?v=%s\" defer></script>\n"
        "<script src=\"/template/js/lireate-light-v4.js?v=%s\" defer></script>\n"
        "<?php } ?>\n"
    ) % (version, HEAD_MARKER, version, version, version)


def patch_head_assets(text):
    if HEAD_MARKER in text:
        return text, False
    m = HEAD_CLOSE_RE.search(text)
    if not m:
        return text, False
    snippet = _head_snippet()
    pos = m.start()
    return text[:pos] + snippet + text[pos:], True


def _index_after_balanced_div(text, div_open):
    gt = text.find(">", div_open)
    if gt == -1:
        return None
    depth = 1
    i = gt + 1
    n = len(text)
    while i < n:
        next_div = text.find("<div", i)
        next_close = text.find("</div>", i)
        if next_close == -1:
            return None
        if next_div != -1 and next_div < next_close:
            depth += 1
            gt_inner = text.find(">", next_div)
            if gt_inner == -1:
                return None
            i = gt_inner + 1
            continue
        depth -= 1
        i = next_close + len("</div>")
        if depth == 0:
            return i
    return None


def _span_main_hero_block(text):
    hero = MAIN_SCREEN_HERO_RE.search(text)
    if not hero:
        return None
    end = _index_after_balanced_div(text, hero.start())
    if end is None:
        return None
    return (hero.start(), end)


def _span_patched_section(text):
    match = re.search(
        r"<section\s+id=[\"']lireate-home-light-v4-wrap[\"'][^>]*>.*?</section>",
        text,
        flags=re.IGNORECASE | re.DOTALL,
    )
    if match:
        return (match.start(), match.end())
    return None


def _page_container_home_hook():
    return (
        "<div class=\"page_container\">\n"
        "<?php if($temp['page']=='home'){ ?>\n"
        "%s"
        "<?php } ?>\n"
    ) % _replacement_block()


def _strip_patched_section(text):
    span = _span_patched_section(text)
    if not span:
        return text, False
    return text[: span[0]] + text[span[1] :], True


def _home_light_v4_after_wrapper_insert(text):
    m = LIREATE_HOME_WRAPPER_RE.search(text)
    if not m:
        return None
    end = _index_after_balanced_div(text, m.start())
    if end is None:
        return None
    text, _ = _strip_patched_section(text)
    block = "<?php if($temp['page']=='home'){ ?>\n" + _replacement_block() + "<?php } ?>\n"
    return text[:end] + block + text[end:], True, "inserted home Light V4 after #lireate-home wrapper"


def disable_home_v2_flag(docroot, backup_dir):
    docroot = Path(docroot)
    flag = docroot / HOME_V2_FLAG_REL
    if not flag.is_file():
        return None
    backup_dir = Path(backup_dir)
    bak = backup_dir / HOME_V2_FLAG_REL
    bak.parent.mkdir(parents=True, exist_ok=True)
    shutil.copy2(flag, bak)
    disabled = flag.with_name(flag.name + HOME_V2_FLAG_DISABLED_SUFFIX)
    if disabled.is_file():
        disabled.unlink()
    flag.rename(disabled)
    return HOME_V2_FLAG_REL


def restore_home_v2_flag(docroot, backup_dir):
    docroot = Path(docroot)
    disabled = docroot / HOME_V2_FLAG_REL.with_name(
        HOME_V2_FLAG_REL.name + HOME_V2_FLAG_DISABLED_SUFFIX
    )
    flag = docroot / HOME_V2_FLAG_REL
    bak = Path(backup_dir) / HOME_V2_FLAG_REL
    if bak.is_file():
        flag.parent.mkdir(parents=True, exist_ok=True)
        shutil.copy2(bak, flag)
        if disabled.is_file():
            disabled.unlink()
        return HOME_V2_FLAG_REL
    if disabled.is_file():
        disabled.rename(flag)
        return HOME_V2_FLAG_REL
    return None


def patch_home_main_content(text):
    if INSTALL_MARKER in text:
        m = LIREATE_HOME_WRAPPER_RE.search(text)
        if m:
            wrap_end = _index_after_balanced_div(text, m.start())
            span = _span_patched_section(text)
            if wrap_end is not None and span and span[0] > m.start() and span[1] <= wrap_end:
                moved = _home_light_v4_after_wrapper_insert(text)
                if moved is not None:
                    return moved
        span = _span_patched_section(text)
        if span:
            repl = _replacement_block()
            new_text = text[: span[0]] + repl + text[span[1] :]
            return new_text, new_text != text, "refreshed existing home include block"
        return text, False, "already patched (marker present)"

    outside = _home_light_v4_after_wrapper_insert(text)
    if outside is not None:
        return outside

    hero_span = _span_main_hero_block(text)
    if hero_span:
        repl = _replacement_block()
        new_text = text[: hero_span[0]] + repl + text[hero_span[1] :]
        return new_text, True, "replaced main-screen lireate-hero-v4 block"

    home_cond = HOME_COND_RE.search(text)
    if home_cond:
        close = text.find("<?php }", home_cond.end())
        if close != -1:
            repl = "<?php if($temp['page']=='home'){ ?>\n" + _replacement_block() + "<?php }"
            new_text = text[: home_cond.start()] + repl + text[close + len("<?php }") :]
            return new_text, True, "replaced legacy home conditional block"

    pc = PAGE_CONTAINER_RE.search(text)
    if pc:
        rest = text[pc.end() :]
        close_comment = re.match(r"\s*</div>\s*<!--\s*/page_container\s*-->", rest, re.IGNORECASE)
        if close_comment:
            inner = (
                "<div class=\"page_container\">\n"
                "<?php if($temp['page']=='home'){ ?>\n"
                + _replacement_block()
                + "<?php } ?>\n"
                "</div><!-- /page_container -->"
            )
            new_text = text[: pc.start()] + inner + text[pc.end() + close_comment.end() :]
            return new_text, True, "inserted home cms include into empty page_container"
        return (
            text,
            False,
            "BLOCKER: page_container is not empty and no lireate-hero-v4/home conditional — manual main.php hook required",
        )

    return text, False, "BLOCKER: no page_container, main-screen hero, or home conditional in template/main.php"


def patch_body_class_conditional(text):
    return cms_body.patch_home_light_v4_body_conditional(text)


def plan_home_main_content(text):
    text, _, _ = patch_home_main_content(text)
    text, _ = patch_body_class_conditional(text)
    text, _ = cms_body.patch_home_light_v4_suppress_dark_body(text)
    text, _ = patch_head_assets(text)
    return text


def home_template_patch_status(original, planned):
    if INSTALL_MARKER in original and planned == original:
        return "no changes needed (home include already present)"
    if planned == original:
        return "no changes needed or BLOCKER (see patch_home_main_content message)"
    return "would patch"


def validate_planned_php(php_bin, original, planned, label):
    if planned == original:
        return None
    return lint_php_content(php_bin, planned, label)


def apply_copies(office_repo, docroot, pairs):
    created = []
    for src, rel in pairs:
        if rel.parts == ("data", "db.php") or rel == Path(".htaccess"):
            raise SystemExit("BLOCKER: forbidden destination {0}".format(rel))
        dst = Path(docroot) / rel
        existed = dst.is_file()
        dst.parent.mkdir(parents=True, exist_ok=True)
        shutil.copy2(str(src), str(dst))
        if not existed:
            created.append(rel)
    return created


def validate_home_patch_planned(docroot, template_path, php_bin=None):
    if template_path is None:
        return
    original = template_path.read_text(encoding="utf-8", errors="replace")
    planned = plan_home_main_content(original)
    if planned == original:
        return
    lint_err = validate_planned_php(
        php_bin, original, planned, str(template_path.relative_to(docroot))
    )
    if lint_err:
        raise SystemExit("BLOCKER: php -l failed after planned patch\n{0}".format(lint_err))


def apply_home_patch(docroot, template_path, php_bin=None, skip_lint=False):
    rel = template_path.relative_to(docroot)
    original = template_path.read_text(encoding="utf-8", errors="replace")
    planned = plan_home_main_content(original)
    status = home_template_patch_status(original, planned)
    notes = []
    if planned == original:
        _, _, msg = patch_home_main_content(original)
        notes.append("home: {0}".format(msg if msg else status))
        return False, notes, []

    if not skip_lint:
        lint_err = validate_planned_php(php_bin, original, planned, str(rel))
        if lint_err:
            raise SystemExit("BLOCKER: php -l failed after planned patch\n{0}".format(lint_err))
    template_path.write_text(planned, encoding="utf-8")
    notes.append("home: patched template/main.php for Light V4 home")
    if BODY_CLASS_COND not in original and BODY_CLASS_COND in planned:
        notes.append("body class conditional for home / root URI")
    if HEAD_MARKER not in original and HEAD_MARKER in planned:
        notes.append("head assets for home Light V4")
    return True, notes, []


def apply_home_install(office_repo, docroot, php_bin=None, backup_dir=None):
    office_repo = Path(office_repo).resolve()
    docroot = Path(docroot).resolve()
    pairs = copy_plan(office_repo)
    template = find_main_template(docroot)
    validate_home_patch_planned(docroot, template, php_bin=php_bin)
    created = apply_copies(office_repo, docroot, pairs)
    notes = []
    if backup_dir is not None:
        flag_rel = disable_home_v2_flag(docroot, backup_dir)
        if flag_rel is not None:
            notes.append("home: disabled legacy flag {0}".format(flag_rel))
            created.append(
                HOME_V2_FLAG_REL.with_name(
                    HOME_V2_FLAG_REL.name + HOME_V2_FLAG_DISABLED_SUFFIX
                )
            )
    if template is not None:
        _changed, patch_notes, patch_created = apply_home_patch(
            docroot, template, php_bin=php_bin, skip_lint=True
        )
        notes.extend(patch_notes)
        created.extend(patch_created)
    else:
        notes.append(
            "WARN: template/main.php not found — copy assets manually and patch main.php"
        )
    return created, notes, template


def make_backup_id():
    return datetime.now(timezone.utc).strftime("%Y%m%dT%H%M%SZ")


def write_created_manifest(backup_dir, created_rels):
    backup_dir.mkdir(parents=True, exist_ok=True)
    payload = {"created": [str(r).replace("\\", "/") for r in created_rels]}
    (backup_dir / "created.json").write_text(
        json.dumps(payload, ensure_ascii=False, indent=2) + "\n",
        encoding="utf-8",
    )


def remove_created_files(docroot, backup_dir):
    import lireate_install_rollback_lib as rollback_lib

    return rollback_lib.remove_created_from_backup(backup_dir, docroot)


def backup_files(backup_dir, docroot, rel_paths):
    backup_dir.mkdir(parents=True, exist_ok=True)
    manifest = []
    for rel in rel_paths:
        src = Path(docroot) / rel
        if not src.is_file():
            continue
        dst = backup_dir / rel
        dst.parent.mkdir(parents=True, exist_ok=True)
        shutil.copy2(str(src), str(dst))
        manifest.append(str(rel))
    (backup_dir / "manifest.txt").write_text("\n".join(manifest) + "\n", encoding="utf-8")
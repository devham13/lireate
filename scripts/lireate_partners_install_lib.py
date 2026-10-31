"""Partners page install helpers (CMS /partners/)."""

import difflib
import json
import os
import re
import shutil
import subprocess
import tempfile
from datetime import datetime, timezone
from pathlib import Path

INSTALL_MARKER = "lireate-partners-install:v1"
LEGACY_MARKER = "lireate-partners-legacy:snapshot"
BODY_CLASS = "lireate-partners-body"
BODY_CLASS_COND = "<?php if($temp['page']=='partners') echo ' lireate-partners-body';?>"
CMS_INCLUDE = "/template/page/partners/lireate-partners-cms.inc.php"
SHARED_DOCROOT_REL = Path("pages/partners/_shared")
TEMPLATE_REL = Path("template/page/partners")
LEGACY_REL = TEMPLATE_REL / "lireate-partners-legacy.inc.php"
SECTION_MARKERS = ("Наши Партнёры", "pageTitleDisc", 'id="partners"', "personsSlide")
FORBIDDEN_TOUCH = {".htaccess"}

PAGE_TITLE_PARTNERS_RE = re.compile(
    r'<section\s+class="pageTitle">\s*.*?<h1\s+class="pageH1">\s*Наши\s+Партнёры\s*</h1>',
    re.IGNORECASE | re.DOTALL,
)
PERSONS_SLIDE_RE = re.compile(
    r'<section\s+id=["\']personsSlide["\'][^>]*>.*?</section>',
    re.IGNORECASE | re.DOTALL,
)
import lireate_cms_main_body_class as cms_body


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


def _iter_php_under_template(docroot):
    template = docroot / "template"
    if not template.is_dir():
        return
    for path in template.rglob("*.php"):
        if any(p in FORBIDDEN_TOUCH for p in path.parts):
            continue
        if path.name == "db.php":
            continue
        yield path


def find_partners_template(docroot):
    candidates = []
    for path in _iter_php_under_template(docroot):
        try:
            text = path.read_text(encoding="utf-8", errors="replace")
        except OSError:
            continue
        if INSTALL_MARKER in text:
            score = 120
            if path.name == "main.php":
                score += 40
            elif "partner" in path.name.lower():
                score += 10
            candidates.append((score, path))
            continue
        if not all(m in text for m in SECTION_MARKERS):
            continue
        score = 80
        if path.name == "main.php":
            score += 40
        if "partner" in path.name.lower():
            score += 15
        if "page/partners" in str(path).replace("\\", "/"):
            score += 10
        candidates.append((score, path))
    if not candidates:
        return None
    candidates.sort(key=lambda x: (-x[0], len(str(x[1]))))
    return candidates[0][1]


def copy_plan(office_repo):
    repo = Path(office_repo).resolve()
    shared = repo / "pages" / "partners" / "_shared"
    pairs = []
    if not shared.is_dir():
        return pairs
    for path in sorted(shared.iterdir()):
        if path.is_file() and path.suffix in (".php", ".css", ".js"):
            pairs.append((path, TEMPLATE_REL / path.name))
            pairs.append((path, SHARED_DOCROOT_REL / path.name))
    metrika = repo / "pages" / "services" / "_shared" / "lireate-metrika-goals.js"
    if metrika.is_file():
        pairs.append((metrika, TEMPLATE_REL / "lireate-metrika-goals.js"))
    return pairs


def _replacement_block():
    return (
        '<section id="lireate-partners">\n'
        "<?php /* %s */ ?>\n"
        "<?php require $_SERVER['DOCUMENT_ROOT'] . '%s'; ?>\n"
        "</section>\n"
    ) % (INSTALL_MARKER, CMS_INCLUDE)


def _partners_block_start(text):
    m = PAGE_TITLE_PARTNERS_RE.search(text)
    if not m:
        return None
    start = m.start()
    before = text[max(0, start - 20000) : start]
    styles = list(re.finditer(r"<style>.*?</style>\s*$", before, re.IGNORECASE | re.DOTALL))
    if styles:
        st = styles[-1]
        blob = st.group(0)
        if "#partners" in blob or ".partner" in blob or "pageTitleDisc" in blob:
            start = start - (len(before) - st.start())
    return start


def _span_partners_block(text):
    if INSTALL_MARKER in text:
        match = re.search(
            r'<section\s+id=["\']lireate-partners["\'][^>]*>.*?</section>',
            text,
            flags=re.IGNORECASE | re.DOTALL,
        )
        if match:
            return (match.start(), match.end())
    start = _partners_block_start(text)
    if start is None:
        return None
    slide = PERSONS_SLIDE_RE.search(text, start)
    if not slide:
        return None
    return (start, slide.end())


def extract_partners_legacy_inner(text):
    span = _span_partners_block(text)
    if not span:
        return None
    if INSTALL_MARKER in text:
        block = text[span[0] : span[1]]
        match = re.search(
            r'<section\s+id=["\']lireate-partners["\'][^>]*>(.*)</section>',
            block,
            flags=re.IGNORECASE | re.DOTALL,
        )
        return match.group(1).strip() if match else None
    return text[span[0] : span[1]].strip()


def write_legacy_snapshot(docroot, inner_html):
    if not inner_html:
        return None
    path = docroot / LEGACY_REL
    path.parent.mkdir(parents=True, exist_ok=True)
    payload = (
        "<?php\n"
        "/** Auto-snapshot of CMS /partners block (lireate_partners_install). */\n"
        "/* %s */\n"
        "?>\n"
        "%s\n"
    ) % (LEGACY_MARKER, inner_html)
    path.write_text(payload, encoding="utf-8")
    return LEGACY_REL


def _strip_global_body_class(text):
    return cms_body.remove_static_body_class(text, BODY_CLASS)


def patch_body_class_conditional(text):
    return cms_body.patch_partners_body_conditional(text)


def patch_partners_template_content(text):
    if INSTALL_MARKER in text:
        return text, False, "already patched (partners marker present)"
    span = _span_partners_block(text)
    if not span:
        return text, False, "partners block not found"
    repl = _replacement_block()
    new_text = text[: span[0]] + repl + text[span[1] :]
    return new_text, new_text != text, "replaced legacy partners block with lireate-partners-cms.inc.php"


def plan_partners_template_content(text):
    text, _, _ = patch_partners_template_content(text)
    text, _ = patch_body_class_conditional(text)
    return text


def validate_planned_php(php_bin, original, planned, label):
    if planned == original:
        return None
    return lint_php_content(php_bin, planned, label)


def partners_template_patch_status(original, planned):
    if INSTALL_MARKER in original and planned == original:
        return "no changes needed (partners include already present)"
    if planned == original:
        return "no changes needed (partners block not found or not patchable)"
    return "would patch"


def apply_copies(office_repo, docroot, pairs):
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


def validate_partners_patch_planned(docroot, template_path, php_bin=None):
    if template_path is None:
        return
    original = template_path.read_text(encoding="utf-8", errors="replace")
    planned = plan_partners_template_content(original)
    if planned == original:
        return
    lint_err = validate_planned_php(
        php_bin, original, planned, str(template_path.relative_to(docroot))
    )
    if lint_err:
        raise SystemExit("BLOCKER: php -l failed after planned patch\n{0}".format(lint_err))


def apply_partners_patch(docroot, template_path, php_bin=None, skip_lint=False):
    rel = template_path.relative_to(docroot)
    if "data" in rel.parts and template_path.name == "db.php":
        raise SystemExit("BLOCKER: refusing to patch data/db.php")
    original = template_path.read_text(encoding="utf-8", errors="replace")
    planned = plan_partners_template_content(original)
    status = partners_template_patch_status(original, planned)
    if planned == original:
        return False, ["partners: {0}".format(status)], []
    if not skip_lint:
        lint_err = validate_planned_php(php_bin, original, planned, str(rel))
        if lint_err:
            raise SystemExit("BLOCKER: php -l failed after planned patch\n{0}".format(lint_err))
    inner = extract_partners_legacy_inner(original)
    created_legacy = []
    if inner and INSTALL_MARKER not in original:
        legacy_path = docroot / LEGACY_REL
        if not legacy_path.is_file():
            created_legacy.append(LEGACY_REL)
        write_legacy_snapshot(docroot, inner)
    template_path.write_text(planned, encoding="utf-8")
    notes = ["partners: patched legacy block → lireate-partners-cms.inc.php"]
    if inner:
        notes.append("partners: saved legacy snapshot ({0})".format(LEGACY_REL))
    if BODY_CLASS_COND not in original and BODY_CLASS_COND in planned:
        notes.append("body class conditional for partners")
    return True, notes, created_legacy


def rollback_partners_patch(docroot, template_path):
    original = template_path.read_text(encoding="utf-8", errors="replace")
    if INSTALL_MARKER not in original:
        return False, ["partners: not patched"]
    legacy_path = docroot / LEGACY_REL
    if not legacy_path.is_file():
        return False, ["partners: legacy snapshot missing"]
    legacy_inner = legacy_path.read_text(encoding="utf-8", errors="replace")
    if LEGACY_MARKER not in legacy_inner:
        return False, ["partners: invalid legacy snapshot"]
    html_part = legacy_inner.split("?>", 1)[-1].strip()
    span = _span_partners_block(original)
    if not span:
        return False, ["partners: patched section not found for rollback"]
    restored = original[: span[0]] + html_part + original[span[1] :]
    restored = cms_body.remove_body_conditional_snippets(restored, (BODY_CLASS_COND,))
    restored = _strip_global_body_class(restored)
    template_path.write_text(restored, encoding="utf-8")
    return True, ["partners: restored legacy block from snapshot"]


def make_backup_id():
    return datetime.now(timezone.utc).strftime("%Y%m%dT%H%M%SZ")


def write_created_manifest(backup_dir, created_rels):
    backup_dir.mkdir(parents=True, exist_ok=True)
    payload = {"created": [str(r).replace("\\", "/") for r in created_rels]}
    (backup_dir / "created.json").write_text(
        json.dumps(payload, ensure_ascii=False, indent=2) + "\n",
        encoding="utf-8"
    )


def remove_created_files(docroot, backup_dir):
    created_path = backup_dir / "created.json"
    if not created_path.is_file():
        return []
    notes = []
    data = json.loads(created_path.read_text(encoding="utf-8"))
    for rel in data.get("created") or []:
        target = docroot / rel
        if target.is_file():
            target.unlink()
            notes.append("removed created file: {0}".format(rel))
        parent = target.parent
        if parent.is_dir() and not any(parent.iterdir()):
            try:
                parent.rmdir()
            except OSError:
                pass
    return notes


def backup_files(backup_dir, docroot, rel_paths):
    backup_dir.mkdir(parents=True, exist_ok=True)
    manifest = []
    for rel in rel_paths:
        src = docroot / rel
        if not src.is_file():
            continue
        dst = backup_dir / rel
        dst.parent.mkdir(parents=True, exist_ok=True)
        shutil.copy2(str(src), str(dst))
        manifest.append(str(rel))
    (backup_dir / "manifest.txt").write_text("\n".join(manifest) + "\n", encoding="utf-8")


def apply_partners_install(office_repo, docroot, php_bin=None):
    office_repo = Path(office_repo).resolve()
    docroot = Path(docroot).resolve()
    pairs = copy_plan(office_repo)
    template = find_partners_template(docroot)
    validate_partners_patch_planned(docroot, template, php_bin=php_bin)
    created = apply_copies(office_repo, docroot, pairs)
    notes = []
    if template is not None:
        _changed, patch_notes, patch_created = apply_partners_patch(
            docroot, template, php_bin=php_bin, skip_lint=True
        )
        notes.extend(patch_notes)
        created.extend(patch_created)
    else:
        notes.append("WARN: partners CMS template not found under template/")
    return created, notes, template


def rollback_partners_install(docroot, template_path):
    docroot = Path(docroot).resolve()
    notes = []
    if template_path is not None:
        _changed, patch_notes = rollback_partners_patch(docroot, template_path)
        notes.extend(patch_notes)
    legacy = docroot / LEGACY_REL
    if legacy.is_file():
        legacy.unlink()
        notes.append("removed legacy snapshot")
    return notes

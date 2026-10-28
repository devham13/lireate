"""Contacts page install helpers (CMS /kontaktyi/)."""

import difflib
import json
import os
import re
import shutil
import subprocess
import tempfile
from datetime import datetime, timezone
from pathlib import Path

INSTALL_MARKER = "lireate-contacts-install:v1"
LEGACY_MARKER = "lireate-contacts-legacy:snapshot"
BODY_CLASS = "lireate-contacts-body"
BODY_CLASS_COND = "<?php if($temp['page']=='kontaktyi') echo ' lireate-contacts-body';?>"
CMS_INCLUDE = "/template/page/kontaktyi/lireate-contacts-cms.inc.php"
SHARED_DOCROOT_REL = Path("pages/kontaktyi/_shared")
TEMPLATE_REL = Path("template/page/kontaktyi")
LEGACY_REL = TEMPLATE_REL / "lireate-contacts-legacy.inc.php"
SECTION_MARKERS = ("Контакты Лирейт", "title-ap", "order_form_content")
FORBIDDEN_TOUCH = {".htaccess"}
PAGE_CONTAINER_RE = re.compile(r'<div\s+class="page_container">', re.IGNORECASE)
TITLE_AP_RE = re.compile(
    r'<div\s+class="title-ap"><h1>\s*Контакты\s+Лирейт\s*</h1>\s*</div>',
    re.IGNORECASE,
)
INNER_CONTACTS_ROW_RE = re.compile(
    r'<div\s+class="row"\s+style="margin-bottom:\s*20px;"',
    re.IGNORECASE,
)
BODY_RE = re.compile(r"(<body\b[^>]*\bclass=[\"'])([^\"']*)([\"'])", re.IGNORECASE)


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
    """subprocess.run compatible with Python 3.6 (no capture_output)."""
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


def find_contacts_template(docroot):
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
            elif "kontakty" in path.name.lower() or "contact" in path.name.lower():
                score += 10
            candidates.append((score, path))
            continue
        if not all(m in text for m in SECTION_MARKERS):
            continue
        score = 80
        if path.name == "main.php":
            score += 40
        if "kontakty" in path.name.lower() or "contact" in path.name.lower():
            score += 15
        if "page/kontaktyi" in str(path).replace("\\", "/"):
            score += 10
        candidates.append((score, path))
    if not candidates:
        return None
    candidates.sort(key=lambda x: (-x[0], len(str(x[1]))))
    return candidates[0][1]


def copy_plan(office_repo):
    repo = Path(office_repo).resolve()
    shared = repo / "pages" / "kontaktyi" / "_shared"
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
        "<section id=\"lireate-contacts\">\n"
        "<?php /* %s */ ?>\n"
        "<?php require $_SERVER['DOCUMENT_ROOT'] . '%s'; ?>\n"
        "</section>\n"
    ) % (INSTALL_MARKER, CMS_INCLUDE)


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


def _legacy_block_end_before_wrappers(text, start):
    inner = INNER_CONTACTS_ROW_RE.search(text, start)
    if inner:
        end = _index_after_balanced_div(text, inner.start())
        if end is not None:
            return end
    pc = PAGE_CONTAINER_RE.search(text, start)
    if not pc:
        return None
    before = text[start : pc.start()]
    closes = list(re.finditer(r"</div>", before, re.IGNORECASE))
    if len(closes) < 2:
        return None
    return start + closes[-2].end()


def _span_contacts_block(text):
    if INSTALL_MARKER in text:
        match = re.search(
            r"<section\s+id=[\"']lireate-contacts[\"'][^>]*>.*?</section>",
            text,
            flags=re.IGNORECASE | re.DOTALL,
        )
        if match:
            return (match.start(), match.end())
    start_match = TITLE_AP_RE.search(text)
    if not start_match:
        return None
    end = _legacy_block_end_before_wrappers(text, start_match.start())
    if end is None:
        return None
    return (start_match.start(), end)


def extract_contacts_legacy_inner(text):
    span = _span_contacts_block(text)
    if not span:
        return None
    if INSTALL_MARKER in text:
        block = text[span[0] : span[1]]
        match = re.search(
            r"<section\s+id=[\"']lireate-contacts[\"'][^>]*>(.*)</section>",
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
        "/** Auto-snapshot of CMS /kontaktyi block (lireate_contacts_install). */\n"
        "/* %s */\n"
        "?>\n"
        "%s\n"
    ) % (LEGACY_MARKER, inner_html)
    path.write_text(payload, encoding="utf-8")
    return LEGACY_REL


def _strip_global_body_class(text):
    def repl(match):
        classes = match.group(2)
        classes = classes.replace(" " + BODY_CLASS, "").replace(BODY_CLASS + " ", "")
        if classes.strip() == BODY_CLASS:
            classes = ""
        return match.group(1) + classes.strip() + match.group(3)

    return BODY_RE.sub(repl, text, count=1)


def patch_body_class_conditional(text):
    if BODY_CLASS_COND in text:
        return text, False
    text = _strip_global_body_class(text)

    def repl(match):
        return match.group(1) + match.group(2) + BODY_CLASS_COND + match.group(3)

    new_text, count = BODY_RE.subn(repl, text, count=1)
    if count:
        return new_text, True
    body_open = re.search(r"<body\b([^>]*)>", text, re.IGNORECASE)
    if not body_open:
        return text, False
    insert = (
        '<body class="cms-default' + BODY_CLASS_COND + '"'
    )
    new_text = text[: body_open.start()] + insert + text[body_open.end() - 1 :]
    return new_text, True


def patch_contacts_template_content(text):
    if INSTALL_MARKER in text:
        return text, False, "already patched (contacts marker present)"
    span = _span_contacts_block(text)
    if not span:
        return text, False, "contacts block not found"
    repl = _replacement_block()
    new_text = text[: span[0]] + repl + text[span[1] :]
    return new_text, new_text != text, "replaced legacy contacts block with lireate-contacts-cms.inc.php"


def plan_contacts_template_content(text):
    text, _, _ = patch_contacts_template_content(text)
    text, _ = patch_body_class_conditional(text)
    return text


def validate_planned_php(php_bin, original, planned, label):
    if planned == original:
        return None
    return lint_php_content(php_bin, planned, label)


def contacts_template_patch_status(original, planned):
    if INSTALL_MARKER in original and planned == original:
        return "no changes needed (contacts include already present)"
    if planned == original:
        return "no changes needed (contacts block not found or not patchable)"
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


def apply_contacts_patch(docroot, template_path, php_bin=None):
    rel = template_path.relative_to(docroot)
    if "data" in rel.parts and template_path.name == "db.php":
        raise SystemExit("BLOCKER: refusing to patch data/db.php")
    original = template_path.read_text(encoding="utf-8", errors="replace")
    planned = plan_contacts_template_content(original)
    status = contacts_template_patch_status(original, planned)
    if planned == original:
        return False, ["contacts: {0}".format(status)], []
    lint_err = validate_planned_php(php_bin, original, planned, str(rel))
    if lint_err:
        raise SystemExit("BLOCKER: php -l failed after planned patch\n{0}".format(lint_err))
    inner = extract_contacts_legacy_inner(original)
    created_legacy = []
    if inner and INSTALL_MARKER not in original:
        legacy_path = docroot / LEGACY_REL
        if not legacy_path.is_file():
            created_legacy.append(LEGACY_REL)
        write_legacy_snapshot(docroot, inner)
    template_path.write_text(planned, encoding="utf-8")
    notes = ["contacts: patched legacy block → lireate-contacts-cms.inc.php"]
    if inner:
        notes.append("contacts: saved legacy snapshot ({0})".format(LEGACY_REL))
    if BODY_CLASS_COND not in original and BODY_CLASS_COND in planned:
        notes.append("body class conditional for kontaktyi")
    return True, notes, created_legacy


def rollback_contacts_patch(docroot, template_path):
    original = template_path.read_text(encoding="utf-8", errors="replace")
    if INSTALL_MARKER not in original:
        return False, ["contacts: not patched"]
    legacy_path = docroot / LEGACY_REL
    if not legacy_path.is_file():
        return False, ["contacts: legacy snapshot missing"]
    legacy_inner = legacy_path.read_text(encoding="utf-8", errors="replace")
    if LEGACY_MARKER not in legacy_inner:
        return False, ["contacts: invalid legacy snapshot"]
    html_part = legacy_inner.split("?>", 1)[-1].strip()
    span = _span_contacts_block(original)
    if not span:
        return False, ["contacts: patched section not found for rollback"]
    restored = original[: span[0]] + html_part + original[span[1] :]
    restored = restored.replace(BODY_CLASS_COND, "")
    restored = _strip_global_body_class(restored)
    template_path.write_text(restored, encoding="utf-8")
    return True, ["contacts: restored legacy block from snapshot"]


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


def apply_contacts_install(office_repo, docroot, php_bin=None):
    office_repo = Path(office_repo).resolve()
    docroot = Path(docroot).resolve()
    pairs = copy_plan(office_repo)
    created = apply_copies(office_repo, docroot, pairs)
    notes = []
    template = find_contacts_template(docroot)
    if template is not None:
        _changed, patch_notes, patch_created = apply_contacts_patch(
            docroot, template, php_bin=php_bin
        )
        notes.extend(patch_notes)
        created.extend(patch_created)
    else:
        notes.append("WARN: contacts CMS template not found under template/")
    return created, notes, template


def rollback_contacts_install(docroot, template_path):
    docroot = Path(docroot).resolve()
    notes = []
    if template_path is not None:
        _changed, patch_notes = rollback_contacts_patch(docroot, template_path)
        notes.extend(patch_notes)
    legacy = docroot / LEGACY_REL
    if legacy.is_file():
        legacy.unlink()
        notes.append("removed legacy snapshot")
    return notes

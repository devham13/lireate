"""Contacts page install helpers (CMS /kontaktyi/)."""

import difflib
import re
import shutil
from datetime import datetime, timezone
from pathlib import Path

INSTALL_MARKER = "lireate-contacts-install:v1"
LEGACY_MARKER = "lireate-contacts-legacy:snapshot"
BODY_CLASS = "lireate-contacts-body"
CMS_INCLUDE = "/template/page/kontaktyi/lireate-contacts-cms.inc.php"
LEGACY_REL = Path("template/page/kontaktyi/lireate-contacts-legacy.inc.php")
SECTION_MARKERS = ("Контакты Лирейт", "title-ap", "order_form_content")
FORBIDDEN_TOUCH = {".htaccess"}


def unified_diff(original, planned, label):
    return "".join(
        difflib.unified_diff(
            original.splitlines(keepends=True),
            planned.splitlines(keepends=True),
            fromfile=label + " (before)",
            tofile=label + " (after)",
        )
    )


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
            if "kontakty" in path.name.lower() or "contact" in path.name.lower():
                score += 10
            candidates.append((score, path))
            continue
        if not all(m in text for m in SECTION_MARKERS):
            continue
        score = 80
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
            pairs.append((path, Path("template/page/kontaktyi") / path.name))
    metrika = repo / "pages" / "services" / "_shared" / "lireate-metrika-goals.js"
    if metrika.is_file():
        pairs.append((metrika, Path("template/page/kontaktyi/lireate-metrika-goals.js")))
    return pairs


def _replacement_block():
    return (
        "<section id=\"lireate-contacts\">\n"
        "<?php /* %s */ ?>\n"
        "<?php require $_SERVER['DOCUMENT_ROOT'] . '%s'; ?>\n"
        "</section>\n"
    ) % (INSTALL_MARKER, CMS_INCLUDE)


def _span_contacts_block(text):
    if INSTALL_MARKER in text:
        match = re.search(
            r"<section\s+id=[\"']lireate-contacts[\"'][^>]*>.*?</section>",
            text,
            flags=re.IGNORECASE | re.DOTALL,
        )
        if match:
            return (match.start(), match.end())
    start_match = re.search(
        r'<div\s+class="title-ap"><h1>\s*Контакты\s+Лирейт\s*</h1>\s*</div>',
        text,
        flags=re.IGNORECASE,
    )
    if not start_match:
        return None
    end_match = re.search(r'<div\s+class="page_container">', text[start_match.end() :], re.IGNORECASE)
    if not end_match:
        return None
    end = start_match.end() + end_match.start()
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


BODY_RE = re.compile(r"(<body\b[^>]*\bclass=[\"'])([^\"']*)([\"'])", re.IGNORECASE)


def patch_body_class_content(text):
    if BODY_CLASS in text:
        return text, False

    def repl(match):
        classes = match.group(2)
        if BODY_CLASS in classes.split():
            return match.group(0)
        return match.group(1) + classes + " " + BODY_CLASS + match.group(3)

    new_text, count = BODY_RE.subn(repl, text, count=1)
    if count:
        return new_text, True
    body_open = re.search(r"<body\b([^>]*)>", text, re.IGNORECASE)
    if not body_open:
        return text, False
    insert = '<body class="{0}"'.format(BODY_CLASS)
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
    text, _ = patch_body_class_content(text)
    return text


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


def apply_contacts_patch(docroot, template_path):
    rel = template_path.relative_to(docroot)
    if "data" in rel.parts and template_path.name == "db.php":
        raise SystemExit("BLOCKER: refusing to patch data/db.php")
    original = template_path.read_text(encoding="utf-8", errors="replace")
    planned = plan_contacts_template_content(original)
    status = contacts_template_patch_status(original, planned)
    if planned == original:
        return False, ["contacts: {0}".format(status)]
    inner = extract_contacts_legacy_inner(original)
    if inner and INSTALL_MARKER not in original:
        write_legacy_snapshot(docroot, inner)
    template_path.write_text(planned, encoding="utf-8")
    notes = ["contacts: patched legacy block → lireate-contacts-cms.inc.php"]
    if inner:
        notes.append("contacts: saved legacy snapshot ({0})".format(LEGACY_REL))
    if BODY_CLASS not in original and BODY_CLASS in planned:
        notes.append("body class {0}".format(BODY_CLASS))
    return True, notes


def rollback_contacts_patch(docroot, template_path):
    original = template_path.read_text(encoding="utf-8", errors="replace")
    if INSTALL_MARKER not in original:
        return False, ["contacts: not patched"]
    legacy_path = docroot / LEGACY_REL
    if not legacy_path.is_file():
        return False, ["contacts: legacy snapshot missing"]
    legacy_inner = legacy_path.read_text(encoding="utf-8", errors="replace")
    marker_pos = legacy_inner.find(LEGACY_MARKER)
    if marker_pos == -1:
        return False, ["contacts: invalid legacy snapshot"]
    html_part = legacy_inner.split("?>", 1)[-1].strip()
    span = _span_contacts_block(original)
    if not span:
        return False, ["contacts: patched section not found for rollback"]
    restored = original[: span[0]] + html_part + original[span[1] :]
    restored = restored.replace(" " + BODY_CLASS, "").replace(BODY_CLASS + " ", "")
    template_path.write_text(restored, encoding="utf-8")
    return True, ["contacts: restored legacy block from snapshot"]


def make_backup_id():
    return datetime.now(timezone.utc).strftime("%Y%m%dT%H%M%SZ")


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


def apply_contacts_install(office_repo, docroot):
    office_repo = Path(office_repo).resolve()
    docroot = Path(docroot).resolve()
    pairs = copy_plan(office_repo)
    created = apply_copies(office_repo, docroot, pairs)
    notes = []
    template = find_contacts_template(docroot)
    if template is not None:
        _changed, patch_notes = apply_contacts_patch(docroot, template)
        notes.extend(patch_notes)
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

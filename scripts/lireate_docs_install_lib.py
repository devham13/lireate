"""Docs page install helpers (CMS /docs/)."""

import difflib
import json
import os
import re
import shutil
import subprocess
import tempfile
from datetime import datetime, timezone
from pathlib import Path

INSTALL_MARKER = "lireate-docs-install:v1"
LEGACY_MARKER = "lireate-docs-legacy:snapshot"
BODY_CLASS = "lireate-docs-body"
BODY_CLASS_COND = "<?php if($temp['page']=='docs') echo ' lireate-docs-body';?>"
CMS_INCLUDE = "/template/page/docs/lireate-docs-cms.inc.php"
SHARED_DOCROOT_REL = Path("pages/docs/_shared")
TEMPLATE_REL = Path("template/page/docs")
LEGACY_REL = TEMPLATE_REL / "lireate-docs-legacy.inc.php"
MAIN_REL = Path("template/main.php")
SECTION_MARKERS = ("Документы компании:", "paddingSection section_1", "Реквизиты компании:")
INDEX_START_RE = re.compile(r'<section\s+class="paddingSection\s+section_1">', re.IGNORECASE)
import lireate_cms_main_body_class as cms_body
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


def validate_planned_php(php_bin, original, planned, label):
    if planned == original:
        return None
    return lint_php_content(php_bin, planned, label)


def copy_plan(office_repo):
    repo = Path(office_repo).resolve()
    shared = repo / "pages" / "docs" / "_shared"
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


def find_docs_index_template(docroot):
    path = docroot / TEMPLATE_REL / "index.php"
    if not path.is_file():
        candidates = []
        for p in (docroot / "template").rglob("index.php"):
            if "docs" not in str(p).replace("\\", "/"):
                continue
            try:
                text = p.read_text(encoding="utf-8", errors="replace")
            except OSError:
                continue
            if all(m in text for m in SECTION_MARKERS) or INSTALL_MARKER in text:
                candidates.append(p)
        if not candidates:
            return None
        candidates.sort(key=lambda x: len(str(x)))
        return candidates[0]
    text = path.read_text(encoding="utf-8", errors="replace")
    if INSTALL_MARKER in text or all(m in text for m in SECTION_MARKERS):
        return path
    return None


def find_main_template(docroot):
    path = docroot / MAIN_REL
    if not path.is_file():
        return None
    return path


def _replacement_block():
    return (
        "<section id=\"lireate-docs\">\n"
        "<?php /* %s */ ?>\n"
        "<?php require $_SERVER['DOCUMENT_ROOT'] . '%s'; ?>\n"
        "</section>\n"
    ) % (INSTALL_MARKER, CMS_INCLUDE)


def _span_docs_index_block(text):
    if INSTALL_MARKER in text:
        match = re.search(
            r"<section\s+id=[\"']lireate-docs[\"'][^>]*>.*?</section>",
            text,
            flags=re.IGNORECASE | re.DOTALL,
        )
        if match:
            return (match.start(), match.end())
    start = INDEX_START_RE.search(text)
    if not start:
        return None
    end = text.rfind("</section>")
    if end == -1 or end < start.start():
        return None
    return (start.start(), end + len("</section>"))


def extract_docs_legacy_inner(text):
    span = _span_docs_index_block(text)
    if not span:
        return None
    if INSTALL_MARKER in text:
        block = text[span[0] : span[1]]
        match = re.search(
            r"<section\s+id=[\"']lireate-docs[\"'][^>]*>(.*)</section>",
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
        "/** Auto-snapshot of CMS /docs block (lireate_docs_install). */\n"
        "/* %s */\n"
        "?>\n"
        "%s\n"
    ) % (LEGACY_MARKER, inner_html)
    path.write_text(payload, encoding="utf-8")
    return LEGACY_REL


def _strip_global_body_class(text):
    return cms_body.remove_static_body_class(text, BODY_CLASS)


def patch_body_class_conditional(text):
    return cms_body.patch_docs_body_conditional(text)


def patch_docs_index_content(text):
    if INSTALL_MARKER in text:
        return text, False, "already patched (docs marker present)"
    span = _span_docs_index_block(text)
    if not span:
        return text, False, "docs block not found in index.php"
    repl = _replacement_block()
    new_text = text[: span[0]] + repl + text[span[1] :]
    return new_text, new_text != text, "replaced legacy docs sections with lireate-docs-cms.inc.php"


def plan_docs_index_content(text):
    text, _, _ = patch_docs_index_content(text)
    return text


def plan_main_body_content(text):
    text, _ = patch_body_class_conditional(text)
    return text


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


def validate_docs_patches_planned(docroot, index_path, main_path, php_bin=None):
    if index_path is not None:
        original = index_path.read_text(encoding="utf-8", errors="replace")
        planned = plan_docs_index_content(original)
        if planned != original:
            lint_err = validate_planned_php(
                php_bin, original, planned, str(index_path.relative_to(docroot))
            )
            if lint_err:
                raise SystemExit("BLOCKER: php -l failed for docs index.php\n{0}".format(lint_err))
    if main_path is not None:
        original = main_path.read_text(encoding="utf-8", errors="replace")
        planned = plan_main_body_content(original)
        if planned != original:
            lint_err = validate_planned_php(
                php_bin, original, planned, str(main_path.relative_to(docroot))
            )
            if lint_err:
                raise SystemExit("BLOCKER: php -l failed for template/main.php\n{0}".format(lint_err))


def apply_docs_patches(docroot, index_path, main_path, php_bin=None, skip_lint=False):
    notes = []
    created_legacy = []
    pending = []

    if index_path is not None:
        original = index_path.read_text(encoding="utf-8", errors="replace")
        planned = plan_docs_index_content(original)
        if planned != original:
            pending.append(("index", index_path, original, planned))

    if main_path is not None:
        original = main_path.read_text(encoding="utf-8", errors="replace")
        planned = plan_main_body_content(original)
        if planned != original:
            pending.append(("main", main_path, original, planned))

    if not skip_lint:
        validate_docs_patches_planned(docroot, index_path, main_path, php_bin=php_bin)

    for kind, path, original, planned in pending:
        if kind == "index":
            inner = extract_docs_legacy_inner(original)
            if inner:
                legacy_path = docroot / LEGACY_REL
                if not legacy_path.is_file():
                    created_legacy.append(LEGACY_REL)
                write_legacy_snapshot(docroot, inner)
            path.write_text(planned, encoding="utf-8")
            notes.append("docs: patched template/page/docs/index.php")
        else:
            path.write_text(planned, encoding="utf-8")
            if BODY_CLASS_COND not in original:
                notes.append("docs: body class conditional for /docs/")
    return notes, created_legacy


def rollback_docs_index(docroot, index_path):
    original = index_path.read_text(encoding="utf-8", errors="replace")
    if INSTALL_MARKER not in original:
        return False, ["docs index: not patched"]
    legacy_path = docroot / LEGACY_REL
    if not legacy_path.is_file():
        return False, ["docs index: legacy snapshot missing"]
    legacy_inner = legacy_path.read_text(encoding="utf-8", errors="replace")
    if LEGACY_MARKER not in legacy_inner:
        return False, ["docs index: invalid legacy snapshot"]
    html_part = legacy_inner.split("?>", 1)[-1].strip()
    span = _span_docs_index_block(original)
    if not span:
        return False, ["docs index: patched section not found"]
    restored = original[: span[0]] + html_part + original[span[1] :]
    index_path.write_text(restored, encoding="utf-8")
    return True, ["docs index: restored legacy from snapshot"]


def rollback_main_body(main_path):
    original = main_path.read_text(encoding="utf-8", errors="replace")
    restored = cms_body.remove_body_conditional_snippets(original, (BODY_CLASS_COND,))
    restored = _strip_global_body_class(restored)
    if restored == original:
        return False, ["main.php: body class unchanged"]
    main_path.write_text(restored, encoding="utf-8")
    return True, ["main.php: removed docs body class snippet"]


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


def apply_docs_install(office_repo, docroot, php_bin=None):
    office_repo = Path(office_repo).resolve()
    docroot = Path(docroot).resolve()
    pairs = copy_plan(office_repo)
    index_path = find_docs_index_template(docroot)
    main_path = find_main_template(docroot)
    notes = []
    if index_path is None:
        notes.append("WARN: template/page/docs/index.php not found")
    if main_path is None:
        notes.append("WARN: template/main.php not found")
    validate_docs_patches_planned(docroot, index_path, main_path, php_bin=php_bin)
    created = apply_copies(office_repo, docroot, pairs)
    patch_notes, patch_created = apply_docs_patches(
        docroot, index_path, main_path, php_bin=php_bin, skip_lint=True
    )
    notes.extend(patch_notes)
    created.extend(patch_created)
    return created, notes, index_path, main_path


def rollback_docs_install(docroot, index_path, main_path):
    docroot = Path(docroot).resolve()
    notes = []
    if index_path is not None:
        _changed, n = rollback_docs_index(docroot, index_path)
        notes.extend(n)
    if main_path is not None:
        _changed, n = rollback_main_body(main_path)
        notes.extend(n)
    legacy = docroot / LEGACY_REL
    if legacy.is_file():
        legacy.unlink()
        notes.append("removed legacy snapshot")
    return notes

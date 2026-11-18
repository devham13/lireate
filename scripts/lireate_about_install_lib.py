"""About page install helpers (CMS /o_kompanii)."""
from __future__ import print_function

import datetime
import json
import shutil
import subprocess
import sys
import tempfile
from pathlib import Path

if str(Path(__file__).resolve().parent) not in sys.path:
    sys.path.insert(0, str(Path(__file__).resolve().parent))

import lireate_cms_main_body_class as cms_body  # noqa: E402

INSTALL_MARKER = "lireate-about-install:v1"
CMS_INCLUDE = "/template/page/o_kompanii/lireate-about-cms.inc.php"
SHARED_DOCROOT_REL = Path("pages/o_kompanii/_shared")
TEMPLATE_REL = Path("template/page/o_kompanii")
BODY_CLASS = "lireate-about-body"
APSW_NEEDLE = "<?php if($temp['apsw']){?>"
PAGE_CONTAINER = '<div class="page_container">'


def unified_diff(original, planned, label):
    import difflib

    return "".join(
        difflib.unified_diff(
            original.splitlines(True),
            planned.splitlines(True),
            fromfile=label,
            tofile=label + ".planned",
        )
    )


def resolve_php_bin():
    for candidate in ("php", "php8.3", "php8.2", "php7.4"):
        path = shutil.which(candidate)
        if path:
            return path
    return "php"


def run_subprocess(args, timeout=30):
    return subprocess.run(args, capture_output=True, text=True, timeout=timeout)


def lint_php_content(php_bin, content, label="patched.php"):
    tmp = tempfile.NamedTemporaryFile("w", suffix=".php", delete=False, encoding="utf-8")
    try:
        tmp.write(content)
        tmp.close()
        proc = run_subprocess([php_bin, "-l", tmp.name])
        if proc.returncode != 0:
            return (proc.stdout or "") + (proc.stderr or "") or "php -l failed for {0}".format(label)
        return None
    finally:
        Path(tmp.name).unlink(missing_ok=True)


def find_about_template(docroot):
    """Prefer template/main.php — the CMS shell that renders /o_kompanii from DB."""
    docroot = Path(docroot)
    main = docroot / "template" / "main.php"
    if main.is_file():
        return main
    return None


def copy_plan(office_repo):
    repo = Path(office_repo).resolve()
    shared = repo / "pages" / "o_kompanii" / "_shared"
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
    for rel in (
        Path("template/page/o_kompanii.php"),
        Path("template/page/o_kompanii/index.php"),
    ):
        src = repo / rel
        if src.is_file():
            pairs.append((src, rel))
    return pairs


def about_content_hook():
    return (
        "<?php if($temp['page']=='o_kompanii'){ ?>\n"
        "					<section id=\"lireate-about\">\n"
        "<?php /* %s */ ?>\n"
        "<?php require $_SERVER['DOCUMENT_ROOT'] . '%s'; ?>\n"
        "					</section>\n"
        "				<?php } elseif ($temp['apsw']){?>"
    ) % (INSTALL_MARKER, CMS_INCLUDE)


def patch_about_content_hook(text):
    if INSTALL_MARKER in text:
        return text, False
    idx = text.find(PAGE_CONTAINER)
    if idx < 0:
        return text, False
    apsw = text.find(APSW_NEEDLE, idx)
    if apsw < 0:
        return text, False
    hook = about_content_hook()
    planned = text[:apsw] + hook + text[apsw + len(APSW_NEEDLE):]
    return planned, planned != text


def unpatch_about_content_hook(text):
    hook = about_content_hook()
    if hook not in text:
        return text, False
    return text.replace(hook, APSW_NEEDLE, 1), True


def plan_about_template_content(text):
    text, _ = patch_about_content_hook(text)
    text, _ = cms_body.patch_about_body_conditional(text)
    text, _ = cms_body.patch_cms_dark_and_static_page_slugs(text, ["o_kompanii"])
    return text


def validate_planned_php(php_bin, original, planned, label):
    if planned == original:
        return None
    return lint_php_content(php_bin, planned, label)


def about_template_patch_status(original, planned):
    if INSTALL_MARKER in original and planned == original:
        return "no changes needed (about hook already present)"
    if planned == original:
        return "no changes needed (page_container/apsw block not found)"
    return "would patch"


def apply_copies(office_repo, docroot, pairs):
    created = []
    docroot = Path(docroot)
    for src, rel in pairs:
        dest = docroot / rel
        existed = dest.is_file()
        dest.parent.mkdir(parents=True, exist_ok=True)
        shutil.copy2(str(src), str(dest))
        if not existed:
            created.append(rel)
    return created


def validate_about_patch_planned(docroot, template_path, php_bin=None):
    if template_path is None:
        return
    if php_bin is None:
        php_bin = resolve_php_bin()
    original = template_path.read_text(encoding="utf-8", errors="replace")
    planned = plan_about_template_content(original)
    if planned == original:
        return
    lint_err = validate_planned_php(
        php_bin, original, planned, str(template_path.relative_to(docroot))
    )
    if lint_err:
        raise SystemExit("BLOCKER: php -l failed after planned patch\n{0}".format(lint_err))


def apply_about_patch(docroot, template_path, php_bin=None, skip_lint=False):
    rel = template_path.relative_to(docroot)
    if "data" in rel.parts and template_path.name == "db.php":
        raise SystemExit("BLOCKER: refusing to patch data/db.php")
    original = template_path.read_text(encoding="utf-8", errors="replace")
    planned = plan_about_template_content(original)
    status = about_template_patch_status(original, planned)
    if planned == original:
        return False, ["about: {0}".format(status)], []
    if not skip_lint:
        lint_err = validate_planned_php(php_bin, original, planned, str(rel))
        if lint_err:
            raise SystemExit("BLOCKER: php -l failed after planned patch\n{0}".format(lint_err))
    template_path.write_text(planned, encoding="utf-8")
    notes = ["about: patched page_container hook → lireate-about-cms.inc.php"]
    if cms_body.ABOUT_BODY_COND not in original and cms_body.ABOUT_BODY_COND in planned:
        notes.append("body class conditional for o_kompanii")
    return True, notes, []


def apply_about_install(office_repo, docroot, php_bin=None):
    if php_bin is None:
        php_bin = resolve_php_bin()
    docroot = Path(docroot)
    pairs = copy_plan(office_repo)
    template = find_about_template(docroot)
    validate_about_patch_planned(docroot, template, php_bin=php_bin)
    created = apply_copies(office_repo, docroot, pairs)
    notes = ["about: copied {0} files".format(len(pairs))]
    if template is not None:
        _changed, patch_notes, patch_created = apply_about_patch(
            docroot, template, php_bin=php_bin
        )
        notes.extend(patch_notes)
        created.extend(patch_created)
    return created, notes, template


def rollback_about_patch(docroot, template_path):
    if template_path is None or not template_path.is_file():
        return False, ["about: template not found"]
    original = template_path.read_text(encoding="utf-8", errors="replace")
    planned, changed = unpatch_about_content_hook(original)
    planned = cms_body.remove_body_conditional_snippets(planned, (cms_body.ABOUT_BODY_COND,))
    if planned == original:
        return False, ["about: not patched"]
    template_path.write_text(planned, encoding="utf-8")
    return True, ["about: hook and body class removed"]


def make_backup_id():
    return datetime.datetime.utcnow().strftime("%Y%m%dT%H%M%SZ")


def write_created_manifest(backup_dir, created_rels):
    backup_dir = Path(backup_dir)
    backup_dir.mkdir(parents=True, exist_ok=True)
    payload = [str(rel) for rel in created_rels]
    (backup_dir / "created.json").write_text(
        json.dumps(payload, ensure_ascii=False, indent=2) + "\n", encoding="utf-8"
    )


def backup_files(backup_dir, docroot, rel_paths):
    backup_dir = Path(backup_dir)
    docroot = Path(docroot)
    backup_dir.mkdir(parents=True, exist_ok=True)
    lines = []
    for rel in rel_paths:
        rel = Path(rel)
        src = docroot / rel
        if not src.is_file():
            continue
        dest = backup_dir / rel
        dest.parent.mkdir(parents=True, exist_ok=True)
        shutil.copy2(str(src), str(dest))
        lines.append(str(rel).replace("\\", "/"))
    (backup_dir / "manifest.txt").write_text("\n".join(lines) + "\n", encoding="utf-8")


def remove_created_files(docroot, backup_dir):
    notes = []
    created = Path(backup_dir) / "created.json"
    if not created.is_file():
        return notes
    try:
        rels = json.loads(created.read_text(encoding="utf-8"))
    except ValueError:
        return notes
    docroot = Path(docroot)
    for rel in rels:
        path = docroot / rel
        if path.is_file():
            path.unlink()
            notes.append("removed created {0}".format(rel))
    return notes

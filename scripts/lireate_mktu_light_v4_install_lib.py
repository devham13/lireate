"""Patch hosting main.php head for /mktu Light V4 CSS (no data/db.php)."""

import re
import shutil
import subprocess
import tempfile
from pathlib import Path

import lireate_light_v4_asset_version as ver

HEAD_MARKER = "lireate-mktu-light-v4-install-head:v1"
LEGACY_HEAD = "lireate_design_mktu_lu_head_v1"
MAIN_REL = Path("template/main.php")
MKTU_CSS = Path("template/css/lireate-mktu-light-v4.css")

HEAD_BLOCK_TEMPLATE = """\t<?php /* %s */ $_lx_mktu_lv4 = (isset($_SERVER['REQUEST_URI']) && preg_match('#^/mktu(?:[/?]|$)#', $_SERVER['REQUEST_URI'])) || in_array($temp['page'], array('mktu','mktu1'), true); if ($_lx_mktu_lv4): $__lx_mktu_v = '%s'; ?>
\t<link rel="stylesheet" href="/template/css/lireate-light-v4.css?v=<?php echo htmlspecialchars($__lx_mktu_v, ENT_QUOTES, 'UTF-8'); ?>">
\t<link rel="stylesheet" href="/template/css/lireate-mktu-light-v4.css?v=<?php echo htmlspecialchars($__lx_mktu_v, ENT_QUOTES, 'UTF-8'); ?>">
\t<link rel="stylesheet" href="/template/css/lireate-cms-pages-light-v4.css?v=<?php echo htmlspecialchars($__lx_mktu_v, ENT_QUOTES, 'UTF-8'); ?>">
\t<script>if(document.body){document.body.classList.add('lireate-theme-light-v4');}</script>
\t<?php endif; ?>
""" % (HEAD_MARKER, ver.LIGHT_V4_ASSET_VERSION)


def resolve_php_bin():
    import os
    import shutil as sh

    explicit = os.environ.get("PHP_BIN", "").strip()
    for cand in (explicit, "php", "php8.3", "php5.6"):
        if cand and sh.which(cand):
            return cand
    return "php"


def lint_php_content(php_bin, content, label="patched.php"):
    tmp = tempfile.NamedTemporaryFile("w", suffix=".php", delete=False, encoding="utf-8")
    try:
        tmp.write(content)
        tmp.close()
        proc = subprocess.run(
            [php_bin, "-l", tmp.name],
            stdout=subprocess.PIPE,
            stderr=subprocess.PIPE,
            universal_newlines=True,
        )
        if proc.returncode != 0:
            return "{0}: {1}".format(label, (proc.stderr or proc.stdout).strip())
        return None
    finally:
        try:
            import os

            os.unlink(tmp.name)
        except OSError:
            pass


def plan_main_head_patch(text):
    if HEAD_MARKER in text:
        return text, False, "already patched"
    if LEGACY_HEAD not in text:
        return text, False, "BLOCKER: missing {0} in main.php".format(LEGACY_HEAD)
    idx = text.find(LEGACY_HEAD)
    endif = text.find("<?php endif; ?>", idx)
    if endif == -1:
        return text, False, "BLOCKER: cannot find end of mktu lu head block"
    insert_at = endif + len("<?php endif; ?>")
    new_text = text[:insert_at] + "\n" + HEAD_BLOCK_TEMPLATE + text[insert_at:]
    return new_text, True, "would inject mktu light v4 head assets"


def copy_plan(office_repo):
    css = office_repo / MKTU_CSS
    if not css.is_file():
        raise ValueError("missing {0}".format(MKTU_CSS))
    return [(css, MKTU_CSS)]


def apply_css_copies(office_repo, docroot, pairs):
    created = []
    for src, rel in pairs:
        dst = Path(docroot) / rel
        existed = dst.is_file()
        dst.parent.mkdir(parents=True, exist_ok=True)
        shutil.copy2(src, dst)
        if not existed:
            created.append(rel)
    return created
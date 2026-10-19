"""Live-faithful PHP 5.6 php-cgi gate (rsync docroot slice + same ops as --apply)."""

from __future__ import annotations

import os
import shutil
import subprocess
import tempfile
from pathlib import Path

import lireate_blog_install_lib as lib


def resolve_php_cgi_bin():
    for candidate in (
        os.environ.get("LIREATE_PHP_CGI_BIN", "").strip(),
        "/opt/php/5.6/bin/php-cgi",
        "/opt/php/5.6/bin/php",
    ):
        if candidate and Path(candidate).is_file():
            return candidate
    return None


def _published_service_rel_paths(office_repo, live_docroot):
    from lireate_hosting_services_lib import (
        dest_dir_for_manifest,
        dest_looks_published,
        list_service_dirs,
        load_manifest,
    )

    live_docroot = Path(live_docroot).resolve()
    rels = []
    for service_dir in list_service_dirs(Path(office_repo)):
        try:
            manifest = load_manifest(service_dir)
        except (ValueError, OSError):
            continue
        dest = dest_dir_for_manifest(live_docroot, manifest)
        if dest_looks_published(dest):
            rels.append(manifest["url_path"].strip("/"))
    return rels[:2]


def live_docroot_slice_relpaths(office_repo, live_docroot):
    rels = ["template", Path("pages") / "blog", "_shared"]
    for url_path in _published_service_rel_paths(office_repo, live_docroot):
        rels.append(url_path)
    return rels


def copy_live_docroot_slice(live_docroot, sandbox, office_repo):
    live_docroot = Path(live_docroot).resolve()
    sandbox = Path(sandbox).resolve()
    sandbox.mkdir(parents=True, exist_ok=True)
    for rel in live_docroot_slice_relpaths(office_repo, live_docroot):
        src = live_docroot / rel
        dst = sandbox / rel
        if not src.exists():
            continue
        if dst.exists() or dst.is_symlink():
            if dst.is_dir() and not dst.is_symlink():
                shutil.rmtree(dst)
            else:
                dst.unlink()
        if src.is_dir():
            shutil.copytree(src, dst, symlinks=True)
        else:
            dst.parent.mkdir(parents=True, exist_ok=True)
            shutil.copy2(str(src), str(dst))


def simulate_blog_apply_on_docroot(office_repo, docroot):
    """Same file operations as cmd_apply (no backup / HTTP)."""
    docroot = Path(docroot).resolve()
    pairs = lib.copy_plan(office_repo)
    lib.apply_copies(office_repo, docroot, pairs)
    lib.deploy_docroot_messenger_shared(office_repo, docroot)
    lib.refresh_published_service_includes(office_repo, docroot)
    template = lib.find_news_template(docroot)
    list_template = lib.find_news_list_template(docroot)
    if template is not None:
        lib.apply_template_patch(docroot, template)
    if list_template is not None:
        lib.apply_list_template_patch(docroot, list_template)


def _cgi_env(docroot, script_path, request_uri):
    return {
        "DOCUMENT_ROOT": str(docroot),
        "SCRIPT_FILENAME": str(script_path),
        "REQUEST_URI": request_uri,
        "QUERY_STRING": "",
        "REQUEST_METHOD": "GET",
        "SERVER_NAME": "lireate.ru",
        "SERVER_PORT": "443",
        "HTTPS": "on",
        "REDIRECT_STATUS": "200",
        "GATEWAY_INTERFACE": "CGI/1.1",
    }


def run_php_cgi_get(cgi_bin, docroot, script_path, request_uri, error_log_path):
    error_log_path = Path(error_log_path)
    error_log_path.parent.mkdir(parents=True, exist_ok=True)
    error_log_path.write_text("", encoding="utf-8")
    argv = [
        cgi_bin,
        "-d",
        "display_errors=0",
        "-d",
        "log_errors=1",
        "-d",
        "error_log={0}".format(error_log_path),
        "-d",
        "date.timezone=Europe/Moscow",
    ]
    env = os.environ.copy()
    env.update(_cgi_env(docroot, script_path, request_uri))
    proc = subprocess.run(
        argv,
        env=env,
        capture_output=True,
        text=True,
        timeout=120,
        cwd=str(docroot),
    )
    combined = (proc.stdout or "") + (proc.stderr or "")
    log_text = ""
    if error_log_path.is_file():
        log_text = error_log_path.read_text(encoding="utf-8", errors="replace")
    return proc.returncode, combined, lib.parse_php_error_log(log_text)


def _cgi_suite_targets(docroot, office_repo):
    docroot = Path(docroot)
    repo = Path(office_repo)
    targets = []
    article_tpl = lib.find_news_template(docroot)
    if article_tpl is not None:
        for path in lib.probe_article_paths(repo)[:5]:
            uri = path if path.startswith("/") else "/" + path
            if not uri.endswith("/"):
                uri += "/"
            targets.append(("article", uri, article_tpl))
    list_tpl = lib.find_news_list_template(docroot)
    if list_tpl is not None:
        targets.append(("news_list", "/news/", list_tpl))
    from lireate_hosting_services_lib import (
        dest_dir_for_manifest,
        dest_looks_published,
        list_service_dirs,
        load_manifest,
    )

    for service_dir in list_service_dirs(repo):
        try:
            manifest = load_manifest(service_dir)
        except (ValueError, OSError):
            continue
        dest = dest_dir_for_manifest(docroot, manifest)
        if not dest_looks_published(dest):
            continue
        idx = dest / "index.php"
        if not idx.is_file():
            continue
        uri = "/" + manifest["url_path"].strip("/") + "/"
        targets.append(("service", uri, idx))
        if sum(1 for t in targets if t[0] == "service") >= 2:
            break
    return targets


def run_cgi_suite(cgi_bin, docroot, office_repo, error_log_path):
    failures = []
    all_sigs = set()
    blocking = []
    for label, uri, script in _cgi_suite_targets(docroot, office_repo):
        code, body, parsed = run_php_cgi_get(
            cgi_bin, docroot, script, uri, error_log_path
        )
        all_sigs.update(parsed.get("signatures") or set())
        blocking.extend(parsed.get("blocking") or [])
        status_500 = " 500 " in body[:200] or "Status: 500" in body[:300]
        fatal_in_body = "Fatal error" in body or "Parse error" in body
        print(
            "  cgi {0} {1}: exit={2} http500={3} fatal_in_body={4} log_info={5}".format(
                label,
                uri,
                code,
                status_500,
                fatal_in_body,
                len(parsed.get("info") or []),
            )
        )
        if code != 0 or status_500 or fatal_in_body:
            failures.append("{0} {1}: cgi exit {2}".format(label, uri, code))
        if parsed.get("blocking"):
            for item in parsed["blocking"]:
                failures.append(
                    "{0} {1}: {2}".format(
                        label, uri, item.get("signature") or item.get("line")
                    )
                )
    return failures, all_sigs, blocking


_DUAL_LIB_GUARD_SNIPPET = (
    "if (!function_exists('lireate_blog_dir_up')) {\n"
    "    require_once dirname(__FILE__) . '/lireate-blog-load.php';\n"
    "}"
)
_DUAL_LIB_BREAK_SNIPPET = "require_once dirname(__FILE__) . '/lireate-blog-load.php';"

_SHARED_LOAD_DIR_UP_GUARD = (
    "if (!function_exists('lireate_blog_dir_up')) {\n"
    "    function lireate_blog_dir_up($path, $levels)\n"
    "    {\n"
    "        $path = (string) $path;\n"
    "        $levels = (int) $levels;\n"
    "        while ($levels > 0 && $path !== '') {\n"
    "            $path = dirname($path);\n"
    "            $levels -= 1;\n"
    "        }\n"
    "        return $path;\n"
    "    }\n"
    "}"
)
_SHARED_LOAD_DIR_UP_UNGUARDED = (
    "function lireate_blog_dir_up($path, $levels)\n"
    "{\n"
    "    $path = (string) $path;\n"
    "    $levels = (int) $levels;\n"
    "    while ($levels > 0 && $path !== '') {\n"
    "        $path = dirname($path);\n"
    "        $levels -= 1;\n"
    "    }\n"
    "    return $path;\n"
    "}"
)
_DUAL_GATE_PROBE_REL = Path("template") / "page" / "news" / "lireate-blog-dual-gate-probe.php"


def inject_dual_lib_redeclare_breakage_like_2b890fb(docroot):
    """
    Test helper: revert #51 dual-lib guard (2b890fb) and drop shared load.php dir_up guard
    so template + pages/blog load.php both execute (Cannot redeclare lireate_blog_dir_up).
    """
    docroot = Path(docroot).resolve()
    lib_path = docroot / "pages" / "blog" / "_shared" / "lireate-blog-lib.php"
    if not lib_path.is_file():
        raise FileNotFoundError(lib_path)
    text = lib_path.read_text(encoding="utf-8")
    if _DUAL_LIB_GUARD_SNIPPET not in text:
        raise ValueError("dual-lib guard missing in {0}".format(lib_path))
    lib_path.write_text(text.replace(_DUAL_LIB_GUARD_SNIPPET, _DUAL_LIB_BREAK_SNIPPET, 1), encoding="utf-8")

    load_path = docroot / "pages" / "blog" / "_shared" / "lireate-blog-load.php"
    if not load_path.is_file():
        raise FileNotFoundError(load_path)
    load_text = load_path.read_text(encoding="utf-8")
    if _SHARED_LOAD_DIR_UP_GUARD not in load_text:
        raise ValueError("shared load dir_up guard missing in {0}".format(load_path))
    load_path.write_text(
        load_text.replace(_SHARED_LOAD_DIR_UP_GUARD, _SHARED_LOAD_DIR_UP_UNGUARDED, 1),
        encoding="utf-8",
    )


def gate_selftest_detects_2b890fb_dual_lib_redeclare(cgi_bin, docroot, office_repo, error_log_path):
    """
    Returns True when CGI probe fails after injecting 2b890fb-style dual-tree redeclare breakage.
    Used by unit tests to prove the live gate catches that regression class.
    """
    docroot = Path(docroot).resolve()
    inject_dual_lib_redeclare_breakage_like_2b890fb(docroot)
    probe = docroot / _DUAL_GATE_PROBE_REL
    probe.parent.mkdir(parents=True, exist_ok=True)
    doc_lit = lib.php_single_quoted_literal(str(docroot))
    probe.write_text(
        "<?php\n"
        "$_SERVER['DOCUMENT_ROOT'] = {doc};\n"
        "require_once $_SERVER['DOCUMENT_ROOT'] . '/template/page/news/lireate-blog-load.php';\n"
        "require_once $_SERVER['DOCUMENT_ROOT'] . '/pages/blog/_shared/lireate-blog-load.php';\n"
        "require_once $_SERVER['DOCUMENT_ROOT'] . '/template/page/news/lireate-blog-lib.php';\n"
        "require_once $_SERVER['DOCUMENT_ROOT'] . '/pages/blog/_shared/lireate-blog-lib.php';\n".format(
            doc=doc_lit
        ),
        encoding="utf-8",
    )
    error_log_path = Path(error_log_path)
    error_log_path.parent.mkdir(parents=True, exist_ok=True)
    error_log_path.write_text("", encoding="utf-8")
    argv = [
        cgi_bin,
        "-d",
        "display_errors=0",
        "-d",
        "log_errors=1",
        "-d",
        "error_log={0}".format(error_log_path),
        "-d",
        "date.timezone=Europe/Moscow",
        str(probe),
    ]
    proc = subprocess.run(
        argv,
        capture_output=True,
        text=True,
        timeout=120,
        cwd=str(docroot),
    )
    log_text = error_log_path.read_text(encoding="utf-8", errors="replace")
    log_parsed = lib.parse_php_error_log(log_text)
    haystack = ((proc.stdout or "") + (proc.stderr or "") + " " + log_text).lower()
    for item in log_parsed.get("blocking") or []:
        haystack += " " + (item.get("signature") or item.get("line") or "").lower()
    if "redeclare" in haystack or "cannot redeclare" in haystack:
        return True
    if proc.returncode != 0 and ("fatal error" in haystack or "parse error" in haystack):
        return True
    return False


def run_php_cgi_live_gate_diag(live_docroot, office_repo):
    cgi_bin = resolve_php_cgi_bin()
    if cgi_bin is None:
        print("BLOCKER: PHP 5.6 php-cgi required (/opt/php/5.6/bin/php-cgi)")
        return 2
    live_docroot = Path(live_docroot).resolve()
    office_repo = Path(office_repo).resolve()
    tmp = Path(tempfile.mkdtemp(prefix="lireate-cgi-gate-"))
    log_path = tmp / "cgi-error.log"
    try:
        baseline_root = tmp / "baseline"
        post_root = tmp / "post-apply"
        print("=== PHP CGI LIVE GATE (php-cgi 5.6, docroot slice rsync) ===")
        print("php_cgi: {0}".format(cgi_bin))
        copy_live_docroot_slice(live_docroot, baseline_root, office_repo)
        copy_live_docroot_slice(live_docroot, post_root, office_repo)
        print("simulate --apply on post-apply sandbox...")
        simulate_blog_apply_on_docroot(office_repo, post_root)
        print("--- baseline cgi suite (current live files) ---")
        base_fail, base_sigs, _base_block = run_cgi_suite(
            cgi_bin, baseline_root, office_repo, log_path
        )
        lib.print_php_log_diag_summary("cgi-baseline", lib.parse_php_error_log(log_path.read_text(encoding="utf-8", errors="replace")))
        print("--- post-apply cgi suite (office-repo deploy) ---")
        post_fail, post_sigs, _post_block = run_cgi_suite(
            cgi_bin, post_root, office_repo, log_path
        )
        lib.print_php_log_diag_summary("cgi-post-apply", lib.parse_php_error_log(log_path.read_text(encoding="utf-8", errors="replace")))
        new_sigs = lib.diff_new_log_signatures(base_sigs, {"signatures": post_sigs})
        if new_sigs:
            print("cgi_new_log_vs_baseline ({0}):".format(len(new_sigs)))
            for sig in new_sigs[:20]:
                print("  {0}".format(sig))
        fail = list(post_fail)
        if new_sigs:
            fail.append(
                "new log signatures vs live baseline ({0})".format(len(new_sigs))
            )
        if fail:
            print("CGI LIVE GATE RESULT: FAIL")
            for line in fail[:30]:
                print("  - {0}".format(line))
            return 1
        print("CGI LIVE GATE RESULT: PASS")
        return 0
    finally:
        shutil.rmtree(tmp, ignore_errors=True)

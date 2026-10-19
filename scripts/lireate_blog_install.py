#!/usr/bin/env python3
"""Install LIREATE blog CMS shell into hosting DOCROOT (check / apply / rollback)."""

import argparse
import os
import sys
from pathlib import Path

SCRIPT_DIR = Path(__file__).resolve().parent
REPO_ROOT = SCRIPT_DIR.parent
if str(SCRIPT_DIR) not in sys.path:
    sys.path.insert(0, str(SCRIPT_DIR))

import lireate_blog_install_lib as lib  # noqa: E402


def resolve_office_repo():
    explicit = os.environ.get("LIREATE_OFFICE_REPO", "").strip()
    if explicit:
        return Path(explicit).expanduser().resolve()
    autopub = os.environ.get("LIREATE_AUTOPUB_HOME", "").strip()
    if autopub:
        candidate = Path(autopub).expanduser() / "office-repo"
        if candidate.is_dir():
            return candidate.resolve()
    return REPO_ROOT


def default_env_file_path():
    explicit = os.environ.get("LIREATE_ENV_FILE", "").strip()
    if explicit:
        return Path(explicit).expanduser()
    autopub = os.environ.get("LIREATE_AUTOPUB_HOME", "").strip()
    if not autopub:
        autopub = str(Path.home() / "lireate-autopub")
    return Path(autopub).expanduser() / ".env"


def bootstrap_env(env_file):
    env_file = Path(env_file).expanduser()
    if not env_file.is_file():
        raise SystemExit("BLOCKER: env file not found: {0}".format(env_file))
    lib.load_env_file(env_file)
    return env_file


def print_copy_plan(pairs, docroot):
    print("=== COPY (office-repo → DOCROOT) ===")
    if not pairs:
        print("WARN: nothing to copy — run lireate_blog_sync_template.py in office-repo")
        return
    for src, rel in pairs:
        print("{0}  →  {1}".format(src, docroot / rel))


def cmd_check(office_repo, docroot):
    pairs = lib.copy_plan(office_repo)
    template = lib.find_news_template(docroot)
    list_template = lib.find_news_list_template(docroot)
    print("docroot: {0}".format(docroot))
    print("office_repo: {0}".format(office_repo))
    print_copy_plan(pairs, docroot)
    print("")
    print("=== CMS ARTICLE TEMPLATE PATCH ===")
    if template is None:
        print("WARN: no CMS article template found under {0}/template".format(docroot))
    else:
        rel = template.relative_to(docroot)
        print("file: {0}".format(rel))
        original = template.read_text(encoding="utf-8", errors="replace")
        planned = lib.plan_template_content(original)
        if planned == original:
            print("status: no changes needed (include + body class already present)")
        else:
            print("status: would patch")
            diff = lib.unified_diff(original, planned, str(rel))
            if diff:
                print(diff.rstrip())
            else:
                print("(empty diff)")
    print("")
    print("=== CMS NEWS LIST TEMPLATE PATCH ===")
    if list_template is None:
        print("WARN: no CMS /news list template found under {0}/template".format(docroot))
    else:
        rel = list_template.relative_to(docroot)
        print("file: {0}".format(rel))
        original = list_template.read_text(encoding="utf-8", errors="replace")
        planned = lib.plan_list_template_content(original)
        status = lib.list_template_patch_status(original, planned)
        print("status: {0}".format(status))
        if planned != original:
            diff = lib.unified_diff(original, planned, str(rel))
            if diff:
                print(diff.rstrip())
            else:
                print("(empty diff)")
    print("")
    print("SKIP (never touched): data/db.php, docroot/.htaccess")
    return 0


def cmd_diag(docroot, office_repo):
    return lib.run_php_list_diag(docroot, office_repo)


def cmd_refresh_service_footers(office_repo, docroot, backups_root):
    planned, _updated = lib.plan_published_service_refresh(office_repo, docroot)
    if not planned:
        print("No published service directories to refresh.")
        return 0
    backup_id = lib.make_backup_id()
    backup_dir = lib.backup_dir_for_id(backups_root, backup_id)
    to_backup = [rel for _src, rel in planned if (docroot / rel).is_file()]
    lib.backup_files(backup_dir, docroot, to_backup)
    _planned, updated = lib.refresh_published_service_includes(office_repo, docroot)
    print("backup: {0}".format(backup_dir))
    print("refreshed {0} file(s):".format(len(updated)))
    for rel in updated:
        print("  {0}".format(rel))
    print("STATUS: OK (backup {0})".format(backup_id))
    return 0


def cmd_apply(office_repo, docroot, backups_root):
    pairs = lib.copy_plan(office_repo)
    print("deploy sources: pages/blog/_shared → template/page/news (office-repo git not modified)")
    if not pairs:
        raise SystemExit("BLOCKER: copy plan is empty")

    template = lib.find_news_template(docroot)
    list_template = lib.find_news_list_template(docroot)
    if template is None and list_template is None:
        raise SystemExit(
            "BLOCKER: no CMS news templates under {0}/template".format(docroot)
        )

    print("=== PHP render gate (pre-apply, dual lib tree sandbox) ===")
    gate_code = lib.run_php_list_diag(docroot, office_repo)
    if gate_code != 0:
        raise SystemExit("BLOCKER: PHP render gate failed (exit {0})".format(gate_code))

    backup_id = lib.make_backup_id()
    backup_dir = lib.backup_dir_for_id(backups_root, backup_id)

    to_backup = [rel for _, rel in pairs if (docroot / rel).is_file()]
    if template is not None:
        rel_template = template.relative_to(docroot)
        if rel_template not in to_backup:
            to_backup.append(rel_template)
    if list_template is not None:
        rel_list = list_template.relative_to(docroot)
        if rel_list not in to_backup:
            to_backup.append(rel_list)

    service_refresh = lib.plan_published_service_refresh(office_repo, docroot)
    for _src, rel in service_refresh:
        if (docroot / rel).is_file() and rel not in to_backup:
            to_backup.append(rel)

    lib.backup_files(backup_dir, docroot, to_backup)
    created = lib.apply_copies(office_repo, docroot, pairs)
    messenger_created = lib.deploy_docroot_messenger_shared(office_repo, docroot)
    created.extend(messenger_created)
    lib.write_created_manifest(backup_dir, created)

    _planned_svc, service_updated = lib.refresh_published_service_includes(office_repo, docroot)
    if service_updated:
        print(
            "service includes refreshed: {0} file(s) (footer/sticky/docroot _shared messenger)".format(
                len(service_updated)
            )
        )

    patch_notes = []
    if template is not None:
        _changed, notes = lib.apply_template_patch(docroot, template)
        patch_notes.extend(notes)
    if list_template is not None:
        _changed, notes = lib.apply_list_template_patch(docroot, list_template)
        patch_notes.extend(notes)
    print("backup: {0}".format(backup_dir))
    print("template patch: {0}".format(", ".join(patch_notes) if patch_notes else "ok"))

    base = lib.site_base_url()
    if not base:
        print("WARN: no public URL in env — skipping HTTP checks")
        print("STATUS: OK (files deployed, backup {0})".format(backup_id))
        return 0

    article_paths = lib.probe_article_paths(office_repo)
    logs_dir = Path(
        __import__("os").environ.get("LIREATE_AUTOPUB_HOME") or backups_root.parent
    ).expanduser() / "logs"
    deploy_log_snapshot = lib.snapshot_php_error_log(
        docroot=docroot, autopub_home=backups_root.parent
    )
    errors = lib.verify_http_deploy(
        base,
        article_paths,
        logs_dir=logs_dir,
        backup_id=backup_id,
        docroot=docroot,
        autopub_home=backups_root.parent,
        deploy_log_snapshot=deploy_log_snapshot,
        wait_seconds=90,
        rounds=2,
    )
    if errors:
        print("HTTP verify failed:")
        for err in errors:
            print("  - {0}".format(err))
        print("rolling back backup {0}...".format(backup_id))
        verify_paths = list(article_paths) + ["/news/"]
        rollback_errors = lib.restore_backup_full(
            backup_dir,
            docroot,
            site_base_url=base,
            verify_paths=verify_paths,
            autopub_home=backups_root.parent,
        )
        if rollback_errors:
            print("STATUS: ROLLED BACK WITH HTTP ERRORS ({0})".format(backup_id))
            return 1
        print("STATUS: ROLLED BACK ({0})".format(backup_id))
        return 1

    print("HTTP OK: {0} articles {1}, {0}/news/".format(base, ", ".join(article_paths)))
    print("STATUS: OK (backup {0})".format(backup_id))
    return 0


def cmd_rollback(backups_root, docroot, backup_id):
    backup_dir = lib.backup_dir_for_id(backups_root, backup_id)
    if not backup_dir.is_dir():
        raise SystemExit("BLOCKER: backup not found: {0}".format(backup_dir))
    lib.restore_backup_full(backup_dir, docroot)
    print("STATUS: restored backup {0}".format(backup_id))
    return 0


def main(argv=None):
    parser = argparse.ArgumentParser(description="LIREATE blog template install for hosting DOCROOT")
    parser.add_argument(
        "--env-file",
        default=os.environ.get("LIREATE_ENV_FILE", ""),
        help="Path to .env with LIREATE_DOCROOT (default: $LIREATE_ENV_FILE or ~/lireate-autopub/.env)",
    )
    parser.add_argument(
        "--check",
        action="store_true",
        help="Read-only: copy plan and CMS patch diff (default when neither --apply nor --rollback)",
    )
    parser.add_argument(
        "--apply",
        action="store_true",
        help="Copy files, patch CMS template, verify HTTP (rollback on failure)",
    )
    parser.add_argument(
        "--rollback",
        metavar="TIMESTAMP",
        help="Restore backup blog-install-TIMESTAMP under ~/lireate-autopub/backups/",
    )
    parser.add_argument(
        "--diag",
        action="store_true",
        help="php-cli render of lireate-blog-list-cms.inc.php (display_errors=1)",
    )
    parser.add_argument(
        "--refresh-published-service-footers",
        action="store_true",
        help="Copy footer/sticky + docroot _shared messenger into published service dirs (with backup)",
    )
    args = parser.parse_args(argv)

    env_file = args.env_file.strip()
    if not env_file:
        env_path = default_env_file_path()
    else:
        env_path = Path(env_file).expanduser()
    bootstrap_env(str(env_path))
    office_repo = resolve_office_repo()
    office_repo, docroot, backups_root, _ = lib.resolve_paths(office_repo, str(env_path))

    if args.rollback:
        return cmd_rollback(backups_root, docroot, args.rollback.strip())
    if args.diag:
        return cmd_diag(docroot, office_repo)
    if args.refresh_published_service_footers:
        return cmd_refresh_service_footers(office_repo, docroot, backups_root)
    if args.apply:
        return cmd_apply(office_repo, docroot, backups_root)
    return cmd_check(office_repo, docroot)


if __name__ == "__main__":
    raise SystemExit(main())

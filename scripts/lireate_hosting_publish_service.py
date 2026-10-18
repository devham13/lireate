#!/usr/bin/env python3
"""
Публикация страниц услуг LIREATE на REG.RU: git pull office-repo → копирование в DOCROOT.

Запуск только на хостинге (Shell ISPmanager / CRON). Cursor Cloud не имеет доступа к сайту.

По умолчанию — dry-run (без записи). Реальная публикация: LIREATE_ALLOW_PUBLISH=yes и флаг --apply.
"""

import argparse
import hashlib
import json
import logging
import os
import shutil
import subprocess
import sys
from contextlib import contextmanager
from datetime import datetime, timezone
from pathlib import Path

REPO_ROOT = Path(__file__).resolve().parents[1]
sys.path.insert(0, str(REPO_ROOT / "scripts"))

from lireate_dotenv import load_dotenv  # noqa: E402

SKIP_NAMES = {
    "publish.manifest.json",
    "README.md",
    ".gitkeep",
    ".DS_Store",
}

SHARED_SKIP_FILENAMES = frozenset(
    {
        "service-directory.htaccess",
    }
)

MANAGED_MARKER = ".lireate-managed.json"
SHARED_ASSETS_PREFIX = Path("assets") / "lireate-shared"
SERVICE_DIR_HTACCESS_REL = ".htaccess"
SERVICE_DIR_HTACCESS_SHARED = Path("pages") / "services" / "_shared" / "service-directory.htaccess"


def _shared_asset_files(office_repo):
    shared_dir = office_repo / "pages" / "services" / "_shared"
    if not shared_dir.is_dir():
        return []
    out = []
    for path in sorted(shared_dir.iterdir()):
        if not path.is_file() or path.name.startswith("."):
            continue
        if path.name in SHARED_SKIP_FILENAMES:
            continue
        rel = SHARED_ASSETS_PREFIX / path.name
        out.append((path, rel))
    return out


def _canonical_service_htaccess_path(office_repo):
    path = office_repo / SERVICE_DIR_HTACCESS_SHARED
    if path.is_file():
        return path
    return None


def _ensure_htaccess_in_plan(plan, service_dir, office_repo):
    """Every service URL dir needs .htaccess so DirectoryIndex beats CMS root rewrite."""
    rel = SERVICE_DIR_HTACCESS_REL
    for item in plan:
        if item["rel"] == rel:
            return
    src = service_dir / rel
    if not src.is_file():
        src = _canonical_service_htaccess_path(office_repo)
    if not src or not src.is_file():
        return
    plan.append(
        {
            "rel": rel,
            "src": str(src),
            "sha256": _sha256_file(src),
            "size": src.stat().st_size,
        }
    )


def _build_publish_plan(service_dir, office_repo):
    plan = []
    for rel in _iter_source_files(service_dir):
        src = service_dir / rel
        plan.append(
            {
                "rel": str(rel).replace("\\", "/"),
                "src": str(src),
                "sha256": _sha256_file(src),
                "size": src.stat().st_size,
            }
        )
    _ensure_htaccess_in_plan(plan, service_dir, office_repo)
    for src, rel in _shared_asset_files(office_repo):
        dst_rel = str(rel).replace("\\", "/")
        if any(item["rel"] == dst_rel for item in plan):
            continue
        plan.append(
            {
                "rel": dst_rel,
                "src": str(src),
                "sha256": _sha256_file(src),
                "size": src.stat().st_size,
            }
        )
    return plan


def require_python(min_version):
    if sys.version_info < min_version:
        raise SystemExit(
            "Python {0}.{1}+ required, got {2}".format(
                min_version[0], min_version[1], sys.version.split()[0]
            )
        )


def _truthy(name):
    v = (os.environ.get(name) or "").strip().lower()
    return v in ("1", "yes", "true", "on")


def _default_autopub_home():
    return Path(os.environ.get("LIREATE_AUTOPUB_HOME") or (Path.home() / "lireate-autopub"))


def _sha256_file(path):
    h = hashlib.sha256()
    with path.open("rb") as fh:
        for chunk in iter(lambda: fh.read(65536), b""):
            h.update(chunk)
    return h.hexdigest()


def _setup_logging(log_path):
    log_path = Path(log_path)
    log_path.parent.mkdir(parents=True, exist_ok=True)
    handlers = [logging.StreamHandler(sys.stdout)]
    try:
        handlers.insert(0, logging.FileHandler(str(log_path), encoding="utf-8"))
    except TypeError:
        handlers.insert(0, logging.FileHandler(str(log_path)))
    logging.basicConfig(
        level=logging.INFO,
        format="%(asctime)s %(levelname)s %(message)s",
        handlers=handlers,
    )


@contextmanager
def _file_lock(lock_path):
    lock_path = Path(lock_path)
    lock_path.parent.mkdir(parents=True, exist_ok=True)
    import fcntl

    fh = lock_path.open("a+", encoding="utf-8")
    try:
        fcntl.flock(fh.fileno(), fcntl.LOCK_EX | fcntl.LOCK_NB)
    except IOError:
        fh.close()
        raise SystemExit("STATUS: BLOCKER another service publish run holds the lock")
    try:
        fh.seek(0)
        fh.truncate()
        fh.write("pid={0}\n".format(os.getpid()))
        fh.flush()
        yield
    finally:
        try:
            fcntl.flock(fh.fileno(), fcntl.LOCK_UN)
        finally:
            fh.close()


def _load_manifest(service_dir):
    manifest_path = service_dir / "publish.manifest.json"
    if manifest_path.is_file():
        data = json.loads(manifest_path.read_text(encoding="utf-8"))
    else:
        slug = service_dir.name
        data = {"slug": slug, "url_path": "uslugi/{0}".format(slug)}
    slug = (data.get("slug") or service_dir.name).strip()
    url_path = (data.get("url_path") or "uslugi/{0}".format(slug)).strip().strip("/")
    if ".." in url_path.split("/"):
        raise ValueError("url_path must not contain ..")
    return slug, url_path, data


def _iter_source_files(service_dir):
    for path in sorted(service_dir.rglob("*")):
        if not path.is_file():
            continue
        rel = path.relative_to(service_dir)
        if rel.name in SKIP_NAMES:
            continue
        if rel.name == MANAGED_MARKER:
            continue
        yield rel


def _load_managed_marker(dest_dir):
    marker = dest_dir / MANAGED_MARKER
    if not marker.is_file():
        return None
    try:
        return json.loads(marker.read_text(encoding="utf-8"))
    except (json.JSONDecodeError, OSError):
        return None


def _http_status(url, timeout=20):
    """Return (status_code or None, error_detail for logs)."""
    import ssl
    import urllib.error
    import urllib.request

    headers = {"User-Agent": "lireate-service-publish/2.0"}
    errors = []

    def _try_urllib():
        try:
            ctx = ssl.create_default_context()
            req = urllib.request.Request(url, method="GET", headers=headers)
            with urllib.request.urlopen(req, timeout=timeout, context=ctx) as resp:
                return resp.getcode(), None
        except urllib.error.HTTPError as exc:
            return exc.code, None
        except Exception as exc:
            errors.append("urllib: {0}".format(exc))
            return None, None

    code, _ = _try_urllib()
    if code is not None:
        return code, None

    curl_bin = shutil.which("curl")
    if curl_bin:
        try:
            proc = subprocess.run(
                [
                    curl_bin,
                    "-sS",
                    "-o",
                    "/dev/null",
                    "-w",
                    "%{http_code}",
                    "--max-time",
                    str(int(timeout)),
                    "-A",
                    headers["User-Agent"],
                    "-L",
                    url,
                ],
                stdout=subprocess.PIPE,
                stderr=subprocess.PIPE,
                universal_newlines=True,
                check=False,
            )
            if proc.returncode == 0:
                raw = (proc.stdout or "").strip()
                if raw.isdigit():
                    return int(raw), None
            err = (proc.stderr or proc.stdout or "curl exit {0}".format(proc.returncode)).strip()
            errors.append("curl: {0}".format(err))
        except Exception as exc:
            errors.append("curl: {0}".format(exc))

    detail = "; ".join(errors) if errors else "no response"
    logging.warning("HTTP check failed for %s: %s", url, detail)
    return None, detail


def _git_pull(office_repo):
    if not (office_repo / ".git").is_dir():
        return False, "not a git repo: {0}".format(office_repo)
    try:
        subprocess.run(
            ["git", "-C", str(office_repo), "fetch", "--all", "--prune"],
            check=True,
            stdout=subprocess.PIPE,
            stderr=subprocess.PIPE,
            universal_newlines=True,
        )
        branch = (
            subprocess.check_output(
                ["git", "-C", str(office_repo), "rev-parse", "--abbrev-ref", "HEAD"],
                universal_newlines=True,
            ).strip()
        )
        subprocess.run(
            ["git", "-C", str(office_repo), "pull", "--ff-only", "origin", branch],
            check=True,
            stdout=subprocess.PIPE,
            stderr=subprocess.PIPE,
            universal_newlines=True,
        )
        return True, "pulled branch {0}".format(branch)
    except subprocess.CalledProcessError as exc:
        return False, (exc.stderr or exc.stdout or str(exc)).strip()


def _print_status(code, detail=""):
    msg = "STATUS: {0}".format(code)
    if detail:
        msg = "{0} — {1}".format(msg, detail)
    print(msg)
    logging.info(msg)


def _load_state(state_file):
    if not state_file.is_file():
        return {}
    try:
        return json.loads(state_file.read_text(encoding="utf-8"))
    except json.JSONDecodeError:
        return {}


def _shared_rel(rel):
    prefix = str(SHARED_ASSETS_PREFIX).replace("\\", "/") + "/"
    return rel == str(SHARED_ASSETS_PREFIX).replace("\\", "/") or rel.startswith(prefix)


def _allowed_update_rels(existing_managed, plan, slug_in_state):
    allowed = set()
    if existing_managed:
        allowed |= set((existing_managed.get("files") or {}).keys())
    for item in plan:
        if _shared_rel(item["rel"]):
            allowed.add(item["rel"])
    if slug_in_state and not existing_managed:
        allowed |= {item["rel"] for item in plan}
    return allowed


def _foreign_blocking_files(plan, existing_managed, update_mode, allowed_update):
    foreign = []
    for item in plan:
        dst = Path(item["dst"])
        if not dst.is_file():
            continue
        rel = item["rel"]
        if update_mode:
            continue
        if existing_managed and rel in (existing_managed.get("files") or {}):
            continue
        foreign.append(rel)
    return foreign


def _publish_slug(
    slug,
    office_repo,
    docroot_path,
    site,
    home,
    state_file,
    dry_run,
    allow,
    skip_git_pull,
    update_mode,
):
    service_dir = office_repo / "pages" / "services" / slug
    index_php = service_dir / "index.php"
    index_html = service_dir / "index.html"
    if not service_dir.is_dir() or not (index_php.is_file() or index_html.is_file()):
        _print_status("BLOCKER", "missing pages/services/{0}/index.php or index.html".format(slug))
        return 2

    try:
        slug, url_path, manifest = _load_manifest(service_dir)
    except ValueError as exc:
        _print_status("BLOCKER", str(exc))
        return 2

    dest_dir = docroot_path / url_path
    public_url = "{0}/{1}/".format(site, url_path)
    plan = _build_publish_plan(service_dir, office_repo)
    for item in plan:
        item["dst"] = str(dest_dir / item["rel"])

    state = _load_state(state_file)
    slug_in_state = slug in state
    existing_managed = _load_managed_marker(dest_dir) if docroot_path and docroot_path.is_dir() else None
    allowed_update = _allowed_update_rels(existing_managed, plan, slug_in_state)

    mode_label = "UPDATE" if update_mode else "PUBLISH"
    print("=== SERVICE {0} PLAN ===".format(mode_label))
    print("slug: {0}".format(slug))
    print("office_repo: {0}".format(office_repo))
    print("source: {0}".format(service_dir))
    print("url_path: {0}".format(url_path))
    print("dest_dir: {0}".format(dest_dir))
    print("public_url: {0}".format(public_url))
    print("files: {0}".format(len(plan)))
    for item in plan:
        print("  - {0} ({1} bytes)".format(item["rel"], item["size"]))

    if update_mode and not allowed_update:
        _print_status(
            "BLOCKER",
            "update mode requires prior publish (.lireate-managed.json or state entry for {0})".format(slug),
        )
        return 1

    if dry_run:
        if not docroot_path or not docroot_path.is_dir():
            _print_status("DRY_RUN", "set LIREATE_DOCROOT for target checks")
            return 0
        foreign = _foreign_blocking_files(plan, existing_managed, update_mode, allowed_update)
        if foreign:
            print("WARNING: would BLOCK on foreign files: {0}".format(", ".join(foreign)))
        if update_mode:
            to_write = [item["rel"] for item in plan if item["rel"] in allowed_update or not Path(item["dst"]).is_file()]
            print("UPDATE would write: {0}".format(", ".join(to_write) if to_write else "(none)"))
        _print_status("DRY_RUN", "no writes (use --apply and LIREATE_ALLOW_PUBLISH=yes)")
        return 0

    if not allow:
        _print_status("BLOCKER", "LIREATE_ALLOW_PUBLISH is not yes")
        return 1

    if not docroot_path or not docroot_path.is_dir():
        _print_status("BLOCKER", "set LIREATE_DOCROOT to existing directory")
        return 1

    if not update_mode:
        foreign = _foreign_blocking_files(plan, existing_managed, update_mode, allowed_update)
        if foreign:
            _print_status(
                "BLOCKER",
                "refusing to overwrite foreign files: {0}".format(", ".join(foreign)),
            )
            return 1

    ts = datetime.now(timezone.utc).strftime("%Y%m%dT%H%M%SZ")
    backup_root = home / "backups" / "services" / slug / ts
    items_to_copy = []
    for item in plan:
        rel = item["rel"]
        dst = Path(item["dst"])
        if update_mode and dst.is_file() and rel not in allowed_update:
            print("SKIP (foreign): {0}".format(rel))
            continue
        items_to_copy.append(item)

    if update_mode and dest_dir.is_dir() and items_to_copy:
        backup_root.mkdir(parents=True, exist_ok=True)
        for item in items_to_copy:
            dst = Path(item["dst"])
            if dst.is_file():
                bdst = backup_root / item["rel"]
                bdst.parent.mkdir(parents=True, exist_ok=True)
                shutil.copy2(str(dst), str(bdst))
        logging.info("backup: %s", backup_root)

    if not update_mode and dest_dir.is_dir() and existing_managed:
        backup_root.mkdir(parents=True, exist_ok=True)
        for rel in existing_managed.get("files", {}):
            src = dest_dir / rel
            if src.is_file():
                bdst = backup_root / rel
                bdst.parent.mkdir(parents=True, exist_ok=True)
                shutil.copy2(str(src), str(bdst))
        logging.info("backup: %s", backup_root)

    dest_dir.mkdir(parents=True, exist_ok=True)
    for item in items_to_copy:
        src = Path(item["src"])
        dst = Path(item["dst"])
        dst.parent.mkdir(parents=True, exist_ok=True)
        shutil.copy2(str(src), str(dst))

    marker_files = {item["rel"]: {"sha256": item["sha256"], "size": item["size"]} for item in plan}
    if existing_managed and update_mode:
        prev_files = existing_managed.get("files") or {}
        for rel, meta in prev_files.items():
            if rel not in marker_files:
                marker_files[rel] = meta

    marker = {
        "slug": slug,
        "url_path": url_path,
        "published_at": ts,
        "source_repo": str(office_repo),
        "files": marker_files,
    }
    (dest_dir / MANAGED_MARKER).write_text(
        json.dumps(marker, ensure_ascii=False, indent=2) + "\n",
        encoding="utf-8",
    )

    st, http_err = _http_status(public_url)
    if st != 200:
        detail = "published files but HTTP {0} for {1}".format(st, public_url)
        if http_err:
            detail = "{0} ({1})".format(detail, http_err)
        _print_status("BLOCKER", detail)
        return 1

    state = _load_state(state_file)
    state[slug] = {
        "url_path": url_path,
        "public_url": public_url,
        "published_at": ts,
        "dest_dir": str(dest_dir),
        "http_verified_at": ts,
    }
    state_file.parent.mkdir(parents=True, exist_ok=True)
    state_file.write_text(json.dumps(state, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")

    status_word = "UPDATED" if update_mode else "PUBLISHED"
    _print_status(status_word, public_url)
    return 0


def main():
    require_python((3, 6))

    parser = argparse.ArgumentParser(description="LIREATE service page publish (REG.RU hosting)")
    parser.add_argument("--slug", required=False, help="Service slug (folder under pages/services/)")
    parser.add_argument("--apply", action="store_true", help="Write files (requires LIREATE_ALLOW_PUBLISH=yes)")
    parser.add_argument(
        "--update",
        action="store_true",
        help="Safe re-publish: overwrite only files from .lireate-managed.json / state (+ lireate-shared)",
    )
    parser.add_argument(
        "--update-all",
        action="store_true",
        help="Run --update for every slug in services-published.json state",
    )
    parser.add_argument("--skip-git-pull", action="store_true")
    parser.add_argument(
        "--repair",
        action="store_true",
        help="Copy missing per-directory .htaccess into published service dirs (idempotent)",
    )
    parser.add_argument("--office-repo", type=Path, default=None)
    parser.add_argument("--env-file", type=Path, default=None)
    args = parser.parse_args()

    home = _default_autopub_home()
    env_file = args.env_file or Path(os.environ.get("LIREATE_ENV_FILE") or (home / ".env"))
    if env_file.is_file():
        load_dotenv(env_file, override=False)

    slug = (args.slug or os.environ.get("LIREATE_SERVICE_SLUG") or "").strip()
    if args.update_all:
        slug = ""
    elif args.repair and not slug:
        slug = ""
    elif not slug and not args.repair:
        _print_status("BLOCKER", "pass --slug or set LIREATE_SERVICE_SLUG (or use --update-all / --repair)")
        return 2

    office_repo = args.office_repo or Path(
        os.environ.get("LIREATE_OFFICE_REPO") or (home / "office-repo")
    ).expanduser().resolve()

    docroot = (os.environ.get("LIREATE_DOCROOT") or "").strip()
    docroot_path = Path(docroot).expanduser().resolve() if docroot else None
    site = (os.environ.get("LIREATE_PUBLIC_SITE_URL") or os.environ.get("PUBLIC_SITE_URL") or "").strip().rstrip("/")
    if not site:
        host = (os.environ.get("PUBLIC_SITE_HOST") or "").strip()
        if host:
            site = "https://{0}".format(host)
    allow = _truthy("LIREATE_ALLOW_PUBLISH")
    dry_run = not args.apply

    log_file = Path(os.environ.get("LIREATE_SERVICE_LOG_FILE") or (home / "logs" / "service-publish.log"))
    lock_file = Path(os.environ.get("LIREATE_SERVICE_LOCK_FILE") or (home / "var" / "service-publish.lock"))
    state_file = Path(os.environ.get("LIREATE_SERVICE_STATE_FILE") or (home / "state" / "services-published.json"))

    if not dry_run:
        _setup_logging(log_file)

    update_mode = bool(args.update or args.update_all)
    if args.update and args.update_all:
        _print_status("BLOCKER", "use either --update or --update-all, not both")
        return 2

    if args.repair:
        from lireate_hosting_services_lib import load_state_txt, repair_service_htaccess

        if not docroot_path or not docroot_path.is_dir():
            _print_status("BLOCKER", "set LIREATE_DOCROOT to existing directory")
            return 1
        state_txt = Path(
            os.environ.get("LIREATE_SERVICES_STATE_TXT") or (home / "state" / "services-published.txt")
        )
        slugs = None
        if slug:
            slugs = {slug}
        elif state_txt.is_file():
            slugs = load_state_txt(state_txt)
        repaired = repair_service_htaccess(
            office_repo,
            docroot_path,
            slugs=slugs,
            dry_run=dry_run,
        )
        if repaired:
            _print_status("REPAIRED", ", ".join(repaired))
        else:
            _print_status("REPAIR_OK", "no missing .htaccess")
        return 0

    with _file_lock(lock_file):
        if not args.skip_git_pull and not dry_run:
            ok, detail = _git_pull(office_repo)
            if not ok:
                _print_status("BLOCKER", "git pull failed: {0}".format(detail))
                return 1
            logging.info("git: %s", detail)

        if args.update_all:
            state = _load_state(state_file)
            slugs = sorted(state.keys())
            if not slugs:
                _print_status("BLOCKER", "no slugs in state file for --update-all")
                return 2
            last_code = 0
            for one_slug in slugs:
                code = _publish_slug(
                    one_slug,
                    office_repo,
                    docroot_path,
                    site,
                    home,
                    state_file,
                    dry_run,
                    allow,
                    args.skip_git_pull,
                    True,
                )
                if code != 0:
                    return code
                last_code = code
            return last_code

        return _publish_slug(
            slug,
            office_repo,
            docroot_path,
            site,
            home,
            state_file,
            dry_run,
            allow,
            args.skip_git_pull,
            update_mode,
        )


if __name__ == "__main__":
    raise SystemExit(main())

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

MANAGED_MARKER = ".lireate-managed.json"
SHARED_ASSETS_PREFIX = Path("assets") / "lireate-shared"


def _shared_asset_files(office_repo):
    shared_dir = office_repo / "pages" / "services" / "_shared"
    if not shared_dir.is_dir():
        return []
    out = []
    for path in sorted(shared_dir.iterdir()):
        if not path.is_file() or path.name.startswith("."):
            continue
        rel = SHARED_ASSETS_PREFIX / path.name
        out.append((path, rel))
    return out


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
    try:
        import urllib.request

        req = urllib.request.Request(url, method="GET", headers={"User-Agent": "lireate-service-publish/1.0"})
        with urllib.request.urlopen(req, timeout=timeout) as resp:
            return resp.getcode()
    except Exception:
        return None


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


def main():
    require_python((3, 6))

    parser = argparse.ArgumentParser(description="LIREATE service page publish (REG.RU hosting)")
    parser.add_argument("--slug", required=False, help="Service slug (folder under pages/services/)")
    parser.add_argument("--apply", action="store_true", help="Write files (requires LIREATE_ALLOW_PUBLISH=yes)")
    parser.add_argument("--skip-git-pull", action="store_true")
    parser.add_argument("--office-repo", type=Path, default=None)
    parser.add_argument("--env-file", type=Path, default=None)
    args = parser.parse_args()

    home = _default_autopub_home()
    env_file = args.env_file or Path(os.environ.get("LIREATE_ENV_FILE") or (home / ".env"))
    if env_file.is_file():
        load_dotenv(env_file, override=False)

    slug = (args.slug or os.environ.get("LIREATE_SERVICE_SLUG") or "").strip()
    if not slug:
        _print_status("BLOCKER", "pass --slug or set LIREATE_SERVICE_SLUG")
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

    dest_dir = docroot_path / url_path if docroot_path else Path(url_path)
    public_url = "{0}/{1}/".format(site, url_path)
    plan = _build_publish_plan(service_dir, office_repo)
    for item in plan:
        item["dst"] = str(dest_dir / item["rel"])

    print("=== SERVICE PUBLISH PLAN ===")
    print("slug: {0}".format(slug))
    print("office_repo: {0}".format(office_repo))
    print("source: {0}".format(service_dir))
    print("url_path: {0}".format(url_path))
    print("dest_dir: {0}".format(dest_dir))
    print("public_url: {0}".format(public_url))
    print("files: {0}".format(len(plan)))
    for item in plan:
        print("  - {0} ({1} bytes)".format(item["rel"], item["size"]))

    if dry_run:
        if not docroot_path or not docroot_path.is_dir():
            _print_status("DRY_RUN", "set LIREATE_DOCROOT for target checks")
            return 0
        existing_managed = _load_managed_marker(dest_dir)
        foreign = []
        for item in plan:
            dst = Path(item["dst"])
            if not dst.is_file():
                continue
            rel = item["rel"]
            if existing_managed and rel in (existing_managed.get("files") or {}):
                continue
            foreign.append(rel)
        if foreign:
            print("WARNING: would BLOCK on foreign files: {0}".format(", ".join(foreign)))
        _print_status("DRY_RUN", "no writes (use --apply and LIREATE_ALLOW_PUBLISH=yes to publish)")
        return 0

    if not allow:
        _print_status("BLOCKER", "LIREATE_ALLOW_PUBLISH is not yes")
        return 1

    if not docroot_path or not docroot_path.is_dir():
        _print_status("BLOCKER", "set LIREATE_DOCROOT to existing directory")
        return 1

    with _file_lock(lock_file):
        if not args.skip_git_pull:
            ok, detail = _git_pull(office_repo)
            if not ok:
                _print_status("BLOCKER", "git pull failed: {0}".format(detail))
                return 1
            logging.info("git: %s", detail)
            service_dir = office_repo / "pages" / "services" / slug
            if not service_dir.is_dir():
                _print_status("BLOCKER", "service dir missing after pull")
                return 2
            slug, url_path, manifest = _load_manifest(service_dir)
            dest_dir = docroot_path / url_path
            plan = _build_publish_plan(service_dir, office_repo)
            for item in plan:
                item["dst"] = str(dest_dir / item["rel"])

        existing_managed = _load_managed_marker(dest_dir)
        foreign = []
        for item in plan:
            dst = Path(item["dst"])
            if not dst.is_file():
                continue
            rel = item["rel"]
            if existing_managed and rel in (existing_managed.get("files") or {}):
                continue
            foreign.append(rel)
        if foreign:
            _print_status(
                "BLOCKER",
                "refusing to overwrite foreign files: {0}".format(", ".join(foreign)),
            )
            return 1

        ts = datetime.now(timezone.utc).strftime("%Y%m%dT%H%M%SZ")
        backup_root = home / "backups" / "services" / slug / ts
        if dest_dir.is_dir() and existing_managed:
            backup_root.mkdir(parents=True, exist_ok=True)
            for rel in existing_managed.get("files", {}):
                src = dest_dir / rel
                if src.is_file():
                    bdst = backup_root / rel
                    bdst.parent.mkdir(parents=True, exist_ok=True)
                    shutil.copy2(str(src), str(bdst))
            logging.info("backup: %s", backup_root)

        dest_dir.mkdir(parents=True, exist_ok=True)
        for item in plan:
            src = Path(item["src"])
            dst = Path(item["dst"])
            dst.parent.mkdir(parents=True, exist_ok=True)
            shutil.copy2(str(src), str(dst))

        marker = {
            "slug": slug,
            "url_path": url_path,
            "published_at": ts,
            "source_repo": str(office_repo),
            "files": {item["rel"]: {"sha256": item["sha256"], "size": item["size"]} for item in plan},
        }
        (dest_dir / MANAGED_MARKER).write_text(
            json.dumps(marker, ensure_ascii=False, indent=2) + "\n",
            encoding="utf-8",
        )

        state = {}
        if state_file.is_file():
            try:
                state = json.loads(state_file.read_text(encoding="utf-8"))
            except json.JSONDecodeError:
                state = {}
        state[slug] = {
            "url_path": url_path,
            "public_url": public_url,
            "published_at": ts,
            "dest_dir": str(dest_dir),
        }
        state_file.parent.mkdir(parents=True, exist_ok=True)
        state_file.write_text(json.dumps(state, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")

        st = _http_status(public_url)
        if st != 200:
            _print_status("BLOCKER", "published files but HTTP {0} for {1}".format(st, public_url))
            return 1

        _print_status("PUBLISHED", public_url)
        return 0


if __name__ == "__main__":
    raise SystemExit(main())

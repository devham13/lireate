"""Shared helpers for LIREATE service-page hosting cron (state, discovery, git sync)."""

from __future__ import annotations

import json
import re
import subprocess
from pathlib import Path

SKIP_SERVICE_DIRS = {"_template", "_shared"}
MANAGED_MARKER = ".lireate-managed.json"
SERVICE_DIR_HTACCESS = ".htaccess"
SERVICE_HTACCESS_CANONICAL = Path("pages") / "services" / "_shared" / "service-directory.htaccess"
DEFAULT_MAX_HTTP_RETRIES = 48

SECTION_RE = re.compile(r"^##\s+(S\d+)\.\s+", re.M)
FIELD_RE = re.compile(
    r"^-\s+\*\*(slug|status|menu_after):\*\*\s+`?([^`\n]+)`?\s*$",
    re.M,
)


def load_manifest(service_dir: Path) -> dict:
    manifest_path = service_dir / "publish.manifest.json"
    slug = service_dir.name
    if manifest_path.is_file():
        data = json.loads(manifest_path.read_text(encoding="utf-8"))
    else:
        data = {"slug": slug, "url_path": "uslugi/{0}".format(slug)}
    slug = (data.get("slug") or slug).strip()
    url_path = (data.get("url_path") or "uslugi/{0}".format(slug)).strip().strip("/")
    if ".." in url_path.split("/"):
        raise ValueError("url_path must not contain ..")
    data["slug"] = slug
    data["url_path"] = url_path
    return data


def list_service_dirs(office_repo: Path) -> list[Path]:
    services_root = office_repo / "pages" / "services"
    if not services_root.is_dir():
        return []
    out: list[Path] = []
    for path in sorted(services_root.iterdir()):
        if not path.is_dir():
            continue
        if path.name in SKIP_SERVICE_DIRS or path.name.startswith("."):
            continue
        if (path / "index.php").is_file() or (path / "index.html").is_file():
            out.append(path)
    return out


def parse_queue(office_repo: Path) -> list[dict[str, str]]:
    queue_path = office_repo / "pages" / "services" / "queue.md"
    if not queue_path.is_file():
        return []
    text = queue_path.read_text(encoding="utf-8")
    entries: list[dict[str, str]] = []
    parts = SECTION_RE.split(text)
    if len(parts) < 3:
        return entries
    it = iter(parts[1:])
    for entry_id in it:
        body = next(it, "")
        fields = {m.group(1): m.group(2).strip() for m in FIELD_RE.finditer(body)}
        slug = fields.get("slug", "").strip()
        if not slug:
            continue
        entries.append(
            {
                "id": entry_id,
                "slug": slug,
                "status": fields.get("status", "").strip().lower(),
                "menu_after": fields.get("menu_after", "").strip(),
            }
        )
    return entries


def queue_slug_order(office_repo: Path) -> list[str]:
    return [e["slug"] for e in parse_queue(office_repo)]


def menu_after_for_slug(office_repo: Path, slug: str) -> str:
    for entry in parse_queue(office_repo):
        if entry["slug"] == slug and entry.get("menu_after"):
            return entry["menu_after"]
    return ""


def load_state_txt(state_file: Path) -> set[str]:
    if not state_file.is_file():
        return set()
    slugs: set[str] = set()
    for line in state_file.read_text(encoding="utf-8").splitlines():
        line = line.strip()
        if not line or line.startswith("#"):
            continue
        slugs.add(line.split()[0])
    return slugs


def save_state_txt(state_file: Path, slugs: set[str]) -> None:
    state_file.parent.mkdir(parents=True, exist_ok=True)
    lines = sorted(slugs)
    state_file.write_text("\n".join(lines) + ("\n" if lines else ""), encoding="utf-8")


def dest_dir_for_manifest(docroot: Path, manifest: dict) -> Path:
    return docroot / manifest["url_path"]


def dest_looks_published(dest_dir: Path) -> bool:
    """True when DOCROOT already has a service index (legacy / --init seeding)."""
    if not dest_dir.is_dir():
        return False
    if (dest_dir / "index.php").is_file() or (dest_dir / "index.html").is_file():
        return True
    return False


def canonical_htaccess_source(office_repo: Path) -> Path | None:
    path = office_repo / SERVICE_HTACCESS_CANONICAL
    if path.is_file():
        return path
    return None


def service_htaccess_source(office_repo: Path, service_dir: Path) -> Path | None:
    local = service_dir / SERVICE_DIR_HTACCESS
    if local.is_file():
        return local
    return canonical_htaccess_source(office_repo)


def repair_service_htaccess(
    office_repo: Path,
    docroot: Path,
    slugs: set[str] | None = None,
    dry_run: bool = True,
) -> list[str]:
    """Ensure .htaccess exists in published service dirs. Returns repaired url_paths."""
    repaired: list[str] = []
    for service_dir in list_service_dirs(office_repo):
        slug = service_dir.name
        if slugs is not None and slug not in slugs:
            continue
        try:
            manifest = load_manifest(service_dir)
        except (ValueError, json.JSONDecodeError):
            continue
        dest = dest_dir_for_manifest(docroot, manifest)
        if not dest.is_dir():
            continue
        if not (
            (dest / "index.php").is_file()
            or (dest / "index.html").is_file()
            or (dest / MANAGED_MARKER).is_file()
        ):
            continue
        ht_dest = dest / SERVICE_DIR_HTACCESS
        src = service_htaccess_source(office_repo, service_dir)
        if not src:
            continue
        if ht_dest.is_file():
            continue
        if dry_run:
            repaired.append(manifest["url_path"])
            continue
        import shutil

        shutil.copy2(str(src), str(ht_dest))
        repaired.append(manifest["url_path"])
    return repaired


def load_retry_state(retry_file: Path) -> dict[str, int]:
    if not retry_file.is_file():
        return {}
    try:
        data = json.loads(retry_file.read_text(encoding="utf-8"))
    except json.JSONDecodeError:
        return {}
    if not isinstance(data, dict):
        return {}
    return {str(k): int(v) for k, v in data.items() if isinstance(v, int) or str(v).isdigit()}


def save_retry_state(retry_file: Path, data: dict[str, int]) -> None:
    retry_file.parent.mkdir(parents=True, exist_ok=True)
    retry_file.write_text(json.dumps(data, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")


def record_publish_failure(retry_file: Path, slug: str, max_retries: int) -> tuple[int, bool]:
    counts = load_retry_state(retry_file)
    n = counts.get(slug, 0) + 1
    counts[slug] = n
    save_retry_state(retry_file, counts)
    return n, n >= max_retries


def clear_publish_failure(retry_file: Path, slug: str) -> None:
    counts = load_retry_state(retry_file)
    if slug in counts:
        del counts[slug]
        save_retry_state(retry_file, counts)


def git_sync_office_repo(office_repo: Path, branch: str = "main") -> tuple[bool, str]:
    if not (office_repo / ".git").is_dir():
        return False, "not a git repo: {0}".format(office_repo)
    try:
        subprocess.run(
            ["git", "-C", str(office_repo), "fetch", "origin", branch],
            check=True,
            stdout=subprocess.PIPE,
            stderr=subprocess.PIPE,
            universal_newlines=True,
        )
        subprocess.run(
            [
                "git",
                "-C",
                str(office_repo),
                "checkout",
                "-B",
                branch,
                "FETCH_HEAD",
            ],
            check=True,
            stdout=subprocess.PIPE,
            stderr=subprocess.PIPE,
            universal_newlines=True,
        )
        head = subprocess.check_output(
            ["git", "-C", str(office_repo), "rev-parse", "--short", "HEAD"],
            universal_newlines=True,
        ).strip()
        return True, "synced main @ {0}".format(head)
    except subprocess.CalledProcessError as exc:
        detail = (exc.stderr or exc.stdout or str(exc)).strip()
        return False, detail


def seed_state_from_docroot(
    office_repo: Path,
    docroot: Path,
    state_file: Path,
) -> tuple[set[str], list[str]]:
    """Add slugs whose url_path already exists in DOCROOT. Returns (state, newly_seeded)."""
    state = load_state_txt(state_file)
    seeded: list[str] = []
    for service_dir in list_service_dirs(office_repo):
        slug = service_dir.name
        try:
            manifest = load_manifest(service_dir)
        except (ValueError, json.JSONDecodeError):
            continue
        dest = dest_dir_for_manifest(docroot, manifest)
        if dest_looks_published(dest) and slug not in state:
            state.add(slug)
            seeded.append(slug)
    if seeded:
        save_state_txt(state_file, state)
    return state, seeded


def collect_publish_candidates(
    office_repo: Path,
    docroot: Path,
    state: set[str],
) -> list[str]:
    """Slugs in repo that are not yet in CRON state (services-published.txt).

    Partial deploys (files on disk but publish/HTTP failed) stay retryable until
    a successful cron run appends the slug to state.
    """
    order = queue_slug_order(office_repo)
    order_index = {s: i for i, s in enumerate(order)}
    candidates: list[str] = []

    for service_dir in list_service_dirs(office_repo):
        slug = service_dir.name
        if slug in state:
            continue
        try:
            load_manifest(service_dir)
        except (ValueError, json.JSONDecodeError):
            continue
        candidates.append(slug)

    candidates.sort(key=lambda s: (order_index.get(s, 10**9), s))
    return candidates


def append_slug_to_state(state_file: Path, slug: str) -> None:
    state = load_state_txt(state_file)
    if slug in state:
        return
    state.add(slug)
    save_state_txt(state_file, state)

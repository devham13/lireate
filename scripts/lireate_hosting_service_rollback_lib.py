"""Restore a service publish from ~/lireate-autopub/backups/services/<slug>/<stamp>/."""

import hashlib
import json
import shutil
from pathlib import Path

MANAGED_MARKER = ".lireate-managed.json"


def backup_dir_for(home, slug, stamp):
    root = Path(home).expanduser() / "backups" / "services" / slug
    candidate = root / stamp
    if candidate.is_dir():
        return candidate
    alt = root / ("publish-" + stamp)
    return alt if alt.is_dir() else candidate


def load_rollback_manifest(backup_dir):
    path = Path(backup_dir) / "rollback-manifest.json"
    if not path.is_file():
        return None
    return json.loads(path.read_text(encoding="utf-8"))


def _target_for_rel(docroot, dest_dir, rel, scope):
    rel_path = Path(rel.replace("\\", "/"))
    if scope == "docroot":
        return Path(docroot) / rel_path
    return Path(dest_dir) / rel_path


def _prune_empty_dirs(root):
    root = Path(root)
    if not root.is_dir():
        return
    for path in sorted(root.rglob("*"), key=lambda p: len(p.parts), reverse=True):
        if path.is_dir() and path != root:
            try:
                next(path.iterdir())
            except StopIteration:
                path.rmdir()


def restore_service_publish(docroot, backup_dir, manifest):
    docroot = Path(docroot).resolve()
    backup_dir = Path(backup_dir)
    dest_dir = docroot / manifest["url_path"].strip("/")
    restored = []

    for path in sorted(backup_dir.rglob("*")):
        if not path.is_file() or path.name == "rollback-manifest.json":
            continue
        rel = path.relative_to(backup_dir).as_posix()
        scope = "docroot" if rel.startswith("_shared/") else "service"
        target = _target_for_rel(docroot, dest_dir, rel, scope)
        target.parent.mkdir(parents=True, exist_ok=True)
        shutil.copy2(str(path), str(target))
        restored.append(rel)

    removed = []
    for item in manifest.get("written", []):
        rel = item["rel"]
        scope = item.get("scope", "service")
        if item.get("existed_before"):
            continue
        target = _target_for_rel(docroot, dest_dir, rel, scope)
        if target.is_file():
            backup_file = backup_dir / rel
            if not backup_file.is_file():
                target.unlink()
                removed.append(rel)

    if manifest.get("managed_created"):
        managed = dest_dir / MANAGED_MARKER
        if managed.is_file() and not (backup_dir / MANAGED_MARKER).is_file():
            managed.unlink()
            removed.append(MANAGED_MARKER)

    _prune_empty_dirs(dest_dir)
    return restored, removed


def rollback_performance_patch(docroot, perf_backup_dir, expected_rel_paths=None):
    docroot = Path(docroot).resolve()
    perf_backup_dir = Path(perf_backup_dir)
    manifest_path = perf_backup_dir / "manifest.json"
    if not manifest_path.is_file():
        return [], "missing performance manifest"
    manifest = json.loads(manifest_path.read_text(encoding="utf-8"))
    restored = []
    for record in manifest.get("files", []):
        rel = record["path"]
        if expected_rel_paths is not None and rel not in expected_rel_paths:
            continue
        backup_file = perf_backup_dir / rel
        target = docroot / rel
        if record.get("existed") and backup_file.is_file():
            digest_before = record.get("before")
            if target.is_file() and digest_before:
                current = hashlib.sha256(target.read_bytes()).hexdigest()
                if current != digest_before:
                    pass
            target.parent.mkdir(parents=True, exist_ok=True)
            shutil.copy2(str(backup_file), str(target))
            restored.append(rel)
        elif not record.get("existed") and target.is_file():
            target.unlink()
            restored.append(rel + " (removed)")
    return restored, None
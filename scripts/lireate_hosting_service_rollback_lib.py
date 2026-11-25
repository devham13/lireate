"""Restore a service publish from ~/lireate-autopub/backups/services/<slug>/<stamp>/."""

from __future__ import annotations

import json
import shutil
from pathlib import Path


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
        if item.get("existed_before"):
            continue
        target = _target_for_rel(
            docroot, dest_dir, item["rel"], item.get("scope", "service")
        )
        if target.is_file():
            target.unlink()
            removed.append(item["rel"])
    return restored, removed


def rollback_performance_patch(docroot, perf_backup_dir):
    docroot = Path(docroot).resolve()
    perf_backup_dir = Path(perf_backup_dir)
    manifest_path = perf_backup_dir / "manifest.json"
    if not manifest_path.is_file():
        return [], "missing performance manifest"
    manifest = json.loads(manifest_path.read_text(encoding="utf-8"))
    restored = []
    for record in manifest.get("files", []):
        rel = record["path"]
        backup_file = perf_backup_dir / rel
        target = docroot / rel
        if record.get("existed") and backup_file.is_file():
            target.parent.mkdir(parents=True, exist_ok=True)
            shutil.copy2(str(backup_file), str(target))
            restored.append(rel)
        elif not record.get("existed") and target.is_file():
            target.unlink()
            restored.append(rel + " (removed)")
    return restored, None
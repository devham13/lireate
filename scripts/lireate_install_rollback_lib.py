"""Shared rollback helpers: created.json + prune empty dirs."""
import json
import shutil
from pathlib import Path


def autopub_backups_root(name):
    return Path.home() / "lireate-autopub" / "backups" / name


def write_created_manifest(backup_dir, created_rels):
    payload = {"created": [str(r).replace("\\", "/") for r in created_rels]}
    (Path(backup_dir) / "created.json").write_text(
        json.dumps(payload, ensure_ascii=False, indent=2) + "\n",
        encoding="utf-8",
    )


def _prune_empty_parents(docroot, rels, notes):
    parents = set()
    for rel in rels:
        parent = Path(rel).parent
        while str(parent) not in (".", ""):
            parents.add(parent)
            parent = parent.parent
    docroot = Path(docroot)
    for parent in sorted(parents, key=lambda p: len(str(p)), reverse=True):
        full = docroot / parent
        if full.is_dir() and not any(full.iterdir()):
            try:
                full.rmdir()
                notes.append("removed empty dir {0}".format(parent))
            except OSError:
                pass


def remove_created_from_backup(backup_dir, docroot):
    """Delete paths recorded in created.json (files or trees); prune empty parents."""
    created_path = Path(backup_dir) / "created.json"
    if not created_path.is_file():
        return []
    try:
        payload = json.loads(created_path.read_text(encoding="utf-8"))
        rels = payload.get("created", [])
    except ValueError:
        return []
    docroot = Path(docroot)
    notes = []
    for rel in sorted(rels, key=lambda r: len(str(r)), reverse=True):
        path = docroot / rel
        if path.is_file():
            path.unlink()
            notes.append("removed created {0}".format(rel))
        elif path.is_dir():
            shutil.rmtree(path, ignore_errors=True)
            notes.append("removed created dir {0}".format(rel))
    _prune_empty_parents(docroot, rels, notes)
    return notes
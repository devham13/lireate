"""Build a DOCROOT tree for real index.php + template/main.php render (CI + screenshots)."""

from __future__ import annotations

import os
import shutil
import subprocess
import sys
from pathlib import Path

REPO = Path(__file__).resolve().parents[1]
FIXTURE = REPO / "scripts" / "fixtures" / "cms-docroot"

COPY_REL_DIRS = (
    "template",
    "pages",
    "shared",
    "_shared",
)


def mysql_env():
    return {
        "host": os.environ.get("LIREATE_MYSQL_HOST", "127.0.0.1"),
        "user": os.environ.get("LIREATE_MYSQL_USER", "root"),
        "password": os.environ.get("LIREATE_MYSQL_PASSWORD", "root"),
        "database": os.environ.get("LIREATE_MYSQL_DATABASE", "lireate_ci"),
    }


def mysql_cli_args():
    e = mysql_env()
    return [
        "-h",
        e["host"],
        "-u",
        e["user"],
        f"-p{e['password']}",
        e["database"],
    ]


def load_sql_file(sql_path):
    mysql = shutil.which("mysql")
    if not mysql:
        raise SystemExit("BLOCKER: mysql client not found for fixture DB")
    env = os.environ.copy()
    proc = subprocess.run(
        [mysql] + mysql_cli_args(),
        input=sql_path.read_bytes(),
        capture_output=True,
        check=False,
    )
    if proc.returncode != 0:
        raise SystemExit(
            "BLOCKER: mysql load failed for {0}: {1}".format(
                sql_path, (proc.stderr or proc.stdout).decode("utf-8", errors="replace")
            )
        )


def provision_mysql_schema():
    load_sql_file(FIXTURE / "schema.sql")
    load_sql_file(FIXTURE / "seed.sql")


def build_fixture_docroot(target: Path, office_repo: Path | None = None, mysql: bool = True):
    office_repo = Path(office_repo or REPO).resolve()
    target = Path(target).resolve()
    if target.exists():
        shutil.rmtree(target)
    target.mkdir(parents=True)
    for name in COPY_REL_DIRS:
        src = office_repo / name
        if src.is_dir():
            shutil.copytree(src, target / name, symlinks=True)
    (target / "data").mkdir(parents=True, exist_ok=True)
    shutil.copy2(FIXTURE / "data-db.php", target / "data" / "db.php")
    shutil.copy2(FIXTURE / "index.php", target / "index.php")
    (target / "template").mkdir(parents=True, exist_ok=True)
    shutil.copy2(FIXTURE / "template-main.php", target / "template" / "main.php")
    if mysql:
        provision_mysql_schema()
    return target


if __name__ == "__main__":
    out = Path(sys.argv[1] if len(sys.argv) > 1 else "/tmp/lireate-fixture-docroot")
    build_fixture_docroot(out)
    print("STATUS: FIXTURE_DOCROOT_OK {0}".format(out))
"""Load KEY=VALUE lines from a .env file into os.environ (stdlib, Py3.6+)."""

import os
from pathlib import Path


def load_dotenv(path, override=False):
    path = Path(path)
    if not path.is_file():
        return False
    for raw_line in path.read_text(encoding="utf-8").splitlines():
        line = raw_line.strip()
        if not line or line.startswith("#"):
            continue
        if line.startswith("export "):
            line = line[7:].strip()
        if "=" not in line:
            continue
        key, val = line.split("=", 1)
        key = key.strip()
        val = val.strip()
        if len(val) >= 2 and val[0] == val[-1] and val[0] in ("'", '"'):
            val = val[1:-1]
        if not override and key in os.environ:
            continue
        os.environ[key] = val
    return True


def load_env_file(path, override=False):
    """Alias used by install scripts (same as load_dotenv)."""
    return load_dotenv(path, override=override)

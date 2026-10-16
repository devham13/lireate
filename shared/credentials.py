"""Credential loading for local and Cloud Cursor runs.

Real secrets must come from environment variables in Cloud Agents.
For local-only runs, values may be mirrored in hosting-credentials.local,
which is ignored by git.

REG.RU / lireate.ru: prefer ``LIREATE_*`` secrets (see hosting-credentials.env.example).
"""

from __future__ import annotations

import os
from pathlib import Path


LOCAL_CREDENTIALS = Path(__file__).with_name("hosting-credentials.local")

# LIREATE_* Cloud Secrets → canonical names used by deploy and agents.
_LIREATE_ALIASES: tuple[tuple[str, str], ...] = (
    ("LIREATE_PUBLIC_SITE_URL", "PUBLIC_SITE_URL"),
    ("LIREATE_PUBLIC_SITE_URL", "WP_SITE_URL"),
    ("LIREATE_REMOTE_SITE_ROOT", "REMOTE_SITE_ROOT"),
    ("LIREATE_SFTP_HOST", "SSH_HOST"),
    ("LIREATE_SFTP_HOST", "SFTP_HOST"),
    ("LIREATE_SFTP_HOST", "FTP_HOST"),
    ("LIREATE_SFTP_PORT", "SSH_PORT"),
    ("LIREATE_SFTP_PORT", "SFTP_PORT"),
    ("LIREATE_SFTP_PORT", "FTP_PORT"),
    ("LIREATE_SFTP_USER", "SSH_USER"),
    ("LIREATE_SFTP_USER", "SFTP_USER"),
    ("LIREATE_SFTP_USER", "FTP_USER"),
    ("LIREATE_SFTP_PASSWORD", "SSH_PASSWORD"),
    ("LIREATE_SFTP_PASSWORD", "SFTP_PASSWORD"),
    ("LIREATE_SFTP_PASSWORD", "FTP_PASSWORD"),
)


def _read_local_credentials() -> dict[str, str]:
    if not LOCAL_CREDENTIALS.exists():
        return {}

    values: dict[str, str] = {}
    for raw_line in LOCAL_CREDENTIALS.read_text(encoding="utf-8").splitlines():
        line = raw_line.strip()
        if not line or line.startswith("#") or "=" not in line:
            continue
        key, value = line.split("=", 1)
        values[key.strip()] = value.strip()
    return values


_LOCAL_VALUES = _read_local_credentials()


def _raw_value(name: str) -> str | None:
    return os.environ.get(name) or _LOCAL_VALUES.get(name)


def _lireate_value_for(standard_name: str) -> str | None:
    for lireate_key, target in _LIREATE_ALIASES:
        if target != standard_name:
            continue
        value = _raw_value(lireate_key)
        if value and value.strip():
            return value.strip()
    return None


def apply_lireate_hosting_aliases(values: dict[str, str]) -> dict[str, str]:
    """Overlay canonical hosting keys from LIREATE_* when those secrets are set."""
    out = dict(values)
    for lireate_key, target in _LIREATE_ALIASES:
        source = (values.get(lireate_key) or "").strip()
        if source:
            out[target] = source
    return out


def get_credential(name: str, default: str | None = None) -> str | None:
    lireate = _lireate_value_for(name)
    if lireate is not None:
        return lireate
    direct = _raw_value(name)
    if direct is not None and str(direct).strip():
        return str(direct).strip()
    return default


def require_credential(name: str) -> str:
    value = get_credential(name)
    if not value:
        raise RuntimeError(
            f"Missing required credential {name}. Set it as a Cursor Cloud "
            "secret/env var (or LIREATE_* alias) or in shared/hosting-credentials.local."
        )
    return value


def public_site_url() -> str:
    return get_credential("PUBLIC_SITE_URL") or require_credential("WP_SITE_URL")


def publishing_enabled() -> bool:
    """When LIREATE_ALLOW_PUBLISH is set, deploy/Yura must respect it."""
    raw = _raw_value("LIREATE_ALLOW_PUBLISH")
    if raw is None or not str(raw).strip():
        return True
    return str(raw).strip().lower() in ("1", "true", "yes", "on")


def lireate_hosting_configured() -> bool:
    return bool(_raw_value("LIREATE_SFTP_HOST") and _raw_value("LIREATE_SFTP_USER"))

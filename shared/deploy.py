#!/usr/bin/env python3
"""Deploy a Nero Network page template via SFTP/SSH and WP-CLI."""

from __future__ import annotations

import argparse
import ftplib
import shlex
import sys
import urllib.error
import urllib.request
from pathlib import Path

import paramiko

from credentials import get_credential, public_site_url, publishing_enabled, require_credential


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(description="Deploy a Nero Network page template safely.")
    parser.add_argument("--slug", required=True)
    parser.add_argument("--title", required=True)
    parser.add_argument("--description", required=True)
    parser.add_argument("--local-path", required=True)
    parser.add_argument("--skip-live-check", action="store_true")
    return parser.parse_args()


def ssh_host() -> str:
    return (
        get_credential("SSH_HOST")
        or get_credential("SFTP_HOST")
        or get_credential("FTP_HOST")
        or require_credential("WP_SITE_URL").replace("https://", "").replace("http://", "").strip("/")
    )


def ssh_port() -> int:
    return int(get_credential("SSH_PORT") or get_credential("SFTP_PORT") or "22")


def ssh_credentials() -> list[tuple[str, str]]:
    pairs: list[tuple[str, str]] = []
    for user_key, pass_key in (("SSH_USER", "SSH_PASSWORD"), ("SFTP_USER", "SFTP_PASSWORD")):
        user = get_credential(user_key)
        password = get_credential(pass_key)
        if user and password and (user, password) not in pairs:
            pairs.append((user, password))
    if not pairs:
        raise RuntimeError("Missing SSH/SFTP credentials.")
    return pairs


def connect_ssh() -> paramiko.SSHClient:
    host = ssh_host()
    port = ssh_port()
    last_error: Exception | None = None
    for user, password in ssh_credentials():
        client = paramiko.SSHClient()
        client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
        try:
            client.connect(host, port=port, username=user, password=password, timeout=20)
            print(f"SSH connected: {user}@{host}:{port}")
            return client
        except Exception as exc:
            last_error = exc
            client.close()
    raise RuntimeError(f"SSH connection failed for {host}:{port}") from last_error


def run_remote(ssh: paramiko.SSHClient, command: str) -> tuple[str, str, int]:
    stdin, stdout, stderr = ssh.exec_command(command)
    exit_code = stdout.channel.recv_exit_status()
    out = stdout.read().decode("utf-8", errors="ignore")
    err = stderr.read().decode("utf-8", errors="ignore")
    return out, err, exit_code


def wp_command(remote_site_root: str) -> str:
    wp_cli = get_credential("WP_CLI_BIN", "/usr/local/bin/wp") or "/usr/local/bin/wp"
    php_bin = get_credential("PHP_BIN", "")
    if php_bin and not wp_cli.startswith("/"):
        wp_cli = f"{shlex.quote(php_bin)} {shlex.quote(wp_cli)}"
    else:
        wp_cli = shlex.quote(wp_cli)
    return f"cd {shlex.quote(remote_site_root)} && {wp_cli}"


def resolve_theme_directory(ssh: paramiko.SSHClient, remote_site_root: str) -> str:
    wp_cmd = wp_command(remote_site_root)
    out, err, code = run_remote(ssh, f"{wp_cmd} eval 'echo get_stylesheet_directory();'")
    theme_dir = out.strip()
    if code == 0 and theme_dir.startswith("/"):
        print(f"Theme directory (runtime): {theme_dir}")
        return theme_dir

    fallback = get_credential("SSH_THEME_PATH")
    if fallback:
        print(f"Theme directory (fallback): {fallback}")
        return fallback.rstrip("/")

    theme_slug = require_credential("WP_THEME_SLUG")
    remote_themes = get_credential("REMOTE_WP_THEMES", "wp-content/themes").rstrip("/")
    if remote_themes.startswith("/"):
        return f"{remote_themes}/{theme_slug}"
    return f"{remote_site_root.rstrip('/')}/{remote_themes.strip('/')}/{theme_slug}"


def upload_via_sftp(ssh: paramiko.SSHClient, local_path: Path, remote_file: str) -> None:
    remote_dir = str(Path(remote_file).parent)
    run_remote(ssh, f"mkdir -p {shlex.quote(remote_dir)}")
    with ssh.open_sftp() as sftp:
        sftp.put(str(local_path), remote_file)
    run_remote(ssh, f"chmod 644 {shlex.quote(remote_file)}")


def upload_via_ftp(local_path: Path, remote_file: str, remote_site_root: str) -> None:
    host = get_credential("FTP_HOST") or ssh_host()
    port = int(get_credential("FTP_PORT", "21") or "21")
    user = require_credential("FTP_USER")
    password = require_credential("FTP_PASSWORD")

    if remote_file.startswith(remote_site_root):
        ftp_path = remote_file[len(remote_site_root) :].lstrip("/")
    else:
        ftp_path = remote_file.lstrip("/")

    print(f"Uploading via FTP to {ftp_path}...")
    with ftplib.FTP(timeout=20) as ftp:
        ftp.connect(host, port)
        ftp.login(user, password)
        parts = ftp_path.split("/")
        for part in parts[:-1]:
            try:
                ftp.cwd(part)
            except ftplib.error_perm:
                ftp.mkd(part)
                ftp.cwd(part)
        with local_path.open("rb") as handle:
            ftp.storbinary(f"STOR {parts[-1]}", handle)
    print("FTP upload successful.")


def create_or_update_page(
    ssh: paramiko.SSHClient,
    remote_site_root: str,
    slug: str,
    title: str,
    description: str,
) -> str:
    wp_cmd = wp_command(remote_site_root)
    check_cmd = f"{wp_cmd} post list --name={shlex.quote(slug)} --post_type=page --format=ids"
    out, err, code = run_remote(ssh, check_cmd)
    existing_id = out.strip()
    quoted_template = shlex.quote(f"page-{slug}.php")

    if existing_id:
        print(f"Page exists (ID: {existing_id}). Updating...")
        cmd = (
            f"{wp_cmd} post update {existing_id} "
            f"--post_title={shlex.quote(title)} "
            f"--post_excerpt={shlex.quote(description)} "
            f"--page_template={quoted_template} "
            f"--post_status=publish"
        )
    else:
        print("Page does not exist. Creating...")
        cmd = (
            f"{wp_cmd} post create --post_type=page "
            f"--post_title={shlex.quote(title)} "
            f"--post_name={shlex.quote(slug)} "
            f"--post_excerpt={shlex.quote(description)} "
            f"--post_status=publish "
            f"--page_template={quoted_template}"
        )

    out, err, code = run_remote(ssh, cmd)
    if code != 0:
        raise RuntimeError(err.strip() or out.strip() or f"WP-CLI failed with exit code {code}")
    print(out.strip() or err.strip())
    return existing_id or out.strip()


def verify_live(url: str, slug: str) -> None:
    markers = (
        'id="primary"',
        f"{slug}-page",
        "kpmg-gateway-hero-canvas",
    )
    request = urllib.request.Request(url, headers={"User-Agent": "NeroNetworkDeploy/1.0"})
    with urllib.request.urlopen(request, timeout=20) as response:
        html = response.read().decode("utf-8", errors="ignore")
    missing = [marker for marker in markers if marker not in html]
    if missing:
        raise RuntimeError(f"Live page missing markers: {', '.join(missing)}")
    print(f"Live check OK: {url}")


def main() -> int:
    args = parse_args()
    if not publishing_enabled():
        print("Deploy blocked: LIREATE_ALLOW_PUBLISH is not enabled.")
        return 1

    local_path = Path(args.local_path)
    if not local_path.exists():
        print(f"Local template not found: {local_path}")
        return 1

    remote_site_root = require_credential("REMOTE_SITE_ROOT")
    slug = args.slug
    remote_filename = f"page-{slug}.php"

    ssh = connect_ssh()
    try:
        theme_dir = resolve_theme_directory(ssh, remote_site_root)
        remote_file = f"{theme_dir.rstrip('/')}/{remote_filename}"

        print(f"Uploading via SFTP to {remote_file}...")
        upload_via_sftp(ssh, local_path, remote_file)

        create_or_update_page(ssh, remote_site_root, slug, args.title, args.description)

        wp_cmd = wp_command(remote_site_root)
        run_remote(ssh, f"{wp_cmd} cache flush")
        print("Cache flushed.")
    finally:
        ssh.close()

    public_url = f"{public_site_url().rstrip('/')}/{slug}/"
    if not args.skip_live_check:
        try:
            verify_live(public_url, slug)
        except (urllib.error.URLError, RuntimeError) as exc:
            print(f"Live check warning: {exc}")

    print(f"Published: {public_url}")
    return 0


if __name__ == "__main__":
    try:
        raise SystemExit(main())
    except Exception as exc:
        print(f"Deploy failed: {exc}")
        raise SystemExit(1) from exc

#!/usr/bin/env python3
"""Verify vendored skills, tool configuration and credential initialization."""
import hashlib
import json
import os
from pathlib import Path
import stat
import subprocess
import sys
import tempfile


def main():
    tools = Path(__file__).resolve().parent
    root = tools.parent.parent
    lock = json.loads((tools / 'marketing-skills.lock.json').read_text())
    for relative, expected in lock['files_sha256'].items():
        path = root / relative
        if not path.is_file() or hashlib.sha256(path.read_bytes()).hexdigest() != expected:
            raise SystemExit('Upstream skill mismatch: ' + relative)
    for skill in lock['skills']:
        text = (root / '.cursor' / 'skills' / skill / 'SKILL.md').read_text()
        if f'name: {skill}\n' not in text:
            raise SystemExit('Incorrect skill frontmatter: ' + skill)
    package = json.loads((tools / 'unlighthouse' / 'package.json').read_text())
    npm_lock = json.loads((tools / 'unlighthouse' / 'package-lock.json').read_text())
    expected = package['dependencies']['@unlighthouse/cli']
    actual = npm_lock['packages']['node_modules/@unlighthouse/cli']['version']
    if expected != actual:
        raise SystemExit('Unlighthouse version differs from lockfile')
    with tempfile.TemporaryDirectory(prefix='lireate-seo-env-') as temp:
        args = [sys.executable, str(tools / 'init-env.py'), '--directory', temp]
        subprocess.run(args, check=True, capture_output=True, text=True)
        envfile = Path(temp) / '.env'
        first = envfile.read_bytes()
        subprocess.run(args, check=True, capture_output=True, text=True)
        if envfile.read_bytes() != first:
            raise SystemExit('Existing credentials were replaced')
        values = dict(line.split('=', 1) for line in first.decode().splitlines())
        database = values['SEONAUT_DATABASE_PASSWORD']
        admin = values['SEONAUT_ROOT_PASSWORD']
        if len(database) < 64 or len(admin) < 64 or database == admin:
            raise SystemExit('Credential generation failed')
        if os.name == 'posix' and stat.S_IMODE(envfile.stat().st_mode) != 0o600:
            raise SystemExit('Credentials file permissions must be 0600')
    for script in ['start.sh', 'audit.sh']:
        subprocess.run(['bash', '-n', str(tools / script)], check=True)
    print(f'OK: {len(lock["skills"])} skills, {len(lock["files_sha256"])} upstream files, Unlighthouse {actual}, settings and shell scripts.')


if __name__ == '__main__':
    main()

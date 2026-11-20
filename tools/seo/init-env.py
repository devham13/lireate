#!/usr/bin/env python3
"""Create private SEO tool settings without replacing existing credentials."""
import argparse
import os
from pathlib import Path
import secrets


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--directory', type=Path, default=Path(__file__).resolve().parent)
    args = parser.parse_args()
    args.directory.mkdir(parents=True, exist_ok=True)
    target = args.directory / '.env'
    settings = '\n'.join([
        'SEO_SITE_URL=https://lireate.ru',
        'SEONAUT_PORT=19000',
        'SEONAUT_SERVER_URL=http://localhost:19000',
        'SEONAUT_DATABASE_PASSWORD=' + secrets.token_hex(32),
        'SEONAUT_ROOT_PASSWORD=' + secrets.token_hex(32),
        '',
    ])
    try:
        fd = os.open(target, os.O_WRONLY | os.O_CREAT | os.O_EXCL, 0o600)
    except FileExistsError:
        print('Existing .env retained; credentials were not changed.')
        return
    with os.fdopen(fd, 'w', encoding='utf-8') as stream:
        stream.write(settings)
    print('Created private .env. Credentials are stored only in that file.')


if __name__ == '__main__':
    main()

import importlib.util
import tempfile
from pathlib import Path
import subprocess
import sys

script=Path(__file__).with_name('lireate_six_services_photo_v4_install.py')
spec=importlib.util.spec_from_file_location('installer',str(script));installer=importlib.util.module_from_spec(spec);spec.loader.exec_module(installer)
with tempfile.TemporaryDirectory() as t:
    root=Path(t)/'www';(root/'template').mkdir(parents=True)
    (root/'template/main.php').write_text('<?php $ok=true; ?><html><head><title>x</title></head><body></body></html>',encoding='utf-8')
    for slug in ('findtz','dogovory'):
      (root/slug).mkdir(parents=True)
      (root/slug/'index.php').write_text('<?php ?><html><head><title>'+slug+'</title></head><body><main class="lireate-svc-page"><section class="lx-hero"></section></main></body></html>',encoding='utf-8')
    backup=Path(t)/'backup'
    args=[sys.executable,str(script),'--docroot',str(root),'--backup-root',str(backup)]
    r=subprocess.run(args,capture_output=True,text=True)
    assert r.returncode==0,r.stderr
    assert 'DRY_RUN' in r.stdout
    assert installer.MARKER not in (root/'template/main.php').read_text()
    r=subprocess.run(args+['--apply'],capture_output=True,text=True)
    assert r.returncode==0,r.stderr
    assert installer.MARKER in (root/'template/main.php').read_text()
    assert (root/'template/images/services-v4/specialist-certificate.webp').is_file()
    assert len(list(backup.iterdir()))==1
    r=subprocess.run(args,capture_output=True,text=True)
    assert r.returncode==0 and 'NO_CHANGES' in r.stdout,r.stdout+r.stderr
    r=subprocess.run(args+['--rollback',str(next(backup.iterdir()))],capture_output=True,text=True)
    assert r.returncode==0,r.stderr
    assert installer.MARKER not in (root/'template/main.php').read_text()
    assert not (root/'template/css/lireate-six-services-photo-v4.css').exists()
print('OK: dry-run, apply, repeat NO_CHANGES, rollback')

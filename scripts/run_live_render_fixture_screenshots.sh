#!/usr/bin/env bash
# Screenshots + contrast/geometry metrics for tests/fixtures/live-render/{before,after}/*.html
# (static HTML captured from the real PHP 5.6 render of lireate.ru; see tests/fixtures/live-render/README.md).
#
# AFTER pages link CSS/JS straight from this branch (template/css/*, pages/*/_shared/*), so a CSS edit
# in the branch is visible on the next run without rebuilding the fixture.
#
# Usage: scripts/run_live_render_fixture_screenshots.sh [--sides after|before|before,after] [--pages home,article,...]
#                                                       [--out DIR] [--fail-on-contrast]
# Env:   LIREATE_FIXTURE_PROXY=http://user:pass@host:port   proxy for https://lireate.ru images (optional)
#        LIREATE_FIXTURE_OFFLINE=1                          block every non-local request (images then missing)
#        LIREATE_CHROME=/usr/bin/google-chrome              use a system Chrome instead of playwright's chromium
#        LIREATE_FIXTURE_PORT=19390                         local static server port
# Needs: python3 + playwright (pip install playwright && python3 -m playwright install chromium).
# Analytics/widgets (Metrika, Roistat, VK, callbackkiller, ...) are always blocked: fixtures must not hit live counters.
set -euo pipefail
REPO="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
SIDES="before,after"; PAGES="home,article,reviews,team,portfolio,poshliny,prodlenie"
OUT="${RUNNER_TEMP:-/tmp}/live-render-shots"; FAIL_ON_CONTRAST=0
while [ $# -gt 0 ]; do
  case "$1" in
    --sides) SIDES="$2"; shift 2;;
    --pages) PAGES="$2"; shift 2;;
    --out) OUT="$2"; shift 2;;
    --fail-on-contrast) FAIL_ON_CONTRAST=1; shift;;
    -h|--help) sed -n '2,17p' "$0"; exit 0;;
    *) echo "unknown arg: $1" >&2; exit 2;;
  esac
done
PORT="${LIREATE_FIXTURE_PORT:-19390}"
python3 -c 'import playwright' 2>/dev/null || { echo "BLOCKER: pip install playwright && python3 -m playwright install chromium" >&2; exit 2; }
mkdir -p "$OUT"
python3 -m http.server "$PORT" --bind 127.0.0.1 --directory "$REPO" >"$OUT/http.log" 2>&1 &
SRV=$!
trap 'kill $SRV 2>/dev/null || true' EXIT
for _ in $(seq 1 50); do curl -fsS -o /dev/null "http://127.0.0.1:$PORT/tests/fixtures/live-render/README.md" 2>/dev/null && break; sleep 0.1; done
export LRF_REPO="$REPO" LRF_PORT="$PORT" LRF_SIDES="$SIDES" LRF_PAGES="$PAGES" LRF_OUT="$OUT" LRF_FAIL="$FAIL_ON_CONTRAST"
python3 - <<'PY'
import json, os, sys, urllib.parse
from playwright.sync_api import sync_playwright

PORT = os.environ['LRF_PORT']; OUT = os.environ['LRF_OUT']
REPO_ROOT = os.environ.get('LRF_REPO', os.getcwd())
SIDES = os.environ['LRF_SIDES'].split(','); PAGES = os.environ['LRF_PAGES'].split(',')
OFFLINE = os.environ.get('LIREATE_FIXTURE_OFFLINE') == '1'
ALLOW = ('lireate.ru', 'fonts.googleapis.com', 'fonts.gstatic.com', 'code.jquery.com', 'unpkg.com', 'cdn.jsdelivr.net')

CONTRAST_JS = r"""(sel) => {
 function parse(c){const m=c.match(/rgba?\(([^)]+)\)/); if(!m) return null; const p=m[1].split(',').map(s=>parseFloat(s)); return {r:p[0],g:p[1],b:p[2],a:p.length>3?p[3]:1};}
 function lum(c){const f=v=>{v/=255;return v<=0.03928?v/12.92:Math.pow((v+0.055)/1.055,2.4)}; return 0.2126*f(c.r)+0.7152*f(c.g)+0.0722*f(c.b);}
 function bg(el){let layers=[]; while(el){const s=getComputedStyle(el); const c=parse(s.backgroundColor); if(s.backgroundImage&&s.backgroundImage!=='none') layers.push({img:s.backgroundImage.slice(0,60)}); if(c&&c.a>0){layers.push(c); if(c.a>=1) break;} el=el.parentElement;}
   let res={r:255,g:255,b:255}; for(let i=layers.length-1;i>=0;i--){const l=layers[i]; if(l.img) continue; res={r:l.r*l.a+res.r*(1-l.a),g:l.g*l.a+res.g*(1-l.a),b:l.b*l.a+res.b*(1-l.a)};} return {c:res,imgs:layers.filter(l=>l.img).map(l=>l.img)};}
 const root=document.querySelector(sel); if(!root) return {missing:sel, items:[]};
 const out=[]; [root,...root.querySelectorAll('p,a,span,summary,li,small,div,h1,h2,h3,h4,time,strong,b,td,th,label,button')].forEach(el=>{ const t=[...el.childNodes].filter(n=>n.nodeType===3).map(n=>n.textContent.trim()).join(' ').trim(); if(!t) return; const s=getComputedStyle(el); if(s.visibility==='hidden'||s.display==='none') return; const r=el.getBoundingClientRect(); if(r.width===0||r.height===0) return; const fg=parse(s.color); const b=bg(el); const L1=lum(fg),L2=lum(b.c); const cr=(Math.max(L1,L2)+0.05)/(Math.min(L1,L2)+0.05); out.push({t:t.slice(0,60),tag:el.tagName,cls:el.className.toString().slice(0,40),color:s.color,bg:`rgb(${b.c.r|0},${b.c.g|0},${b.c.b|0})`,imgs:b.imgs,fs:s.fontSize,y:Math.round(r.top+scrollY),cr:+cr.toFixed(2)}); });
 return {items:out};
}"""
OVERLAP_JS = r"""(sel)=>{const els=[...document.querySelectorAll(sel)].filter(e=>{const r=e.getBoundingClientRect();return r.width>0&&r.height>0}); let ov=0; const rs=els.map(e=>e.getBoundingClientRect()); for(let i=0;i<rs.length;i++)for(let j=i+1;j<rs.length;j++){const a=rs[i],b=rs[j]; const x=Math.min(a.right,b.right)-Math.max(a.left,b.left), y=Math.min(a.bottom,b.bottom)-Math.max(a.top,b.top); if(x>2&&y>2) ov++;} return {n:els.length,overlaps:ov,minW:rs.length?Math.round(Math.min(...rs.map(r=>r.width))):null,maxW:rs.length?Math.round(Math.max(...rs.map(r=>r.width))):null}}"""
INFO_JS = r"""()=>{const q=s=>{const e=document.querySelector(s); if(!e) return null; const r=e.getBoundingClientRect(); const cs=getComputedStyle(e); return {y:Math.round(r.top+scrollY),h:Math.round(r.height),display:cs.display,color:cs.color,bg:cs.backgroundColor}};
 const h1=[...document.querySelectorAll('h1')].map(e=>{const r=e.getBoundingClientRect();return {t:e.textContent.trim().slice(0,60),x:Math.round(r.x),y:Math.round(r.top+scrollY),w:Math.round(r.width),h:Math.round(r.height),color:getComputedStyle(e).color}});
 return {scrollHeight:document.documentElement.scrollHeight, hscroll:document.documentElement.scrollWidth>innerWidth, body:document.body.className, bodyBg:getComputedStyle(document.body).backgroundColor,
   h1, legacyHome:q('#lireate-home'), lightV4Home:q('#lireate-home-light-v4'), sidebar:q('#leftSideBar'),
   ym:(document.documentElement.innerHTML.match(/ym\(\d+,\s*["']init["']/g)||[]).length, noindex:!!document.querySelector('meta[name=robots][content*=noindex]')}}"""
GRIDS = {'reviews': ['.review_box', '.lx-rv-case'], 'portfolio': ['.lx-pf-case', '.region_list']}

proxy = None
pu = os.environ.get('LIREATE_FIXTURE_PROXY')
if pu:
    u = urllib.parse.urlparse(pu)
    proxy = {'server': f'{u.scheme}://{u.hostname}:{u.port}', 'bypass': '127.0.0.1,localhost'}
    if u.username:
        proxy.update(username=urllib.parse.unquote(u.username), password=urllib.parse.unquote(u.password or ''))

def route(r):
    host = urllib.parse.urlparse(r.request.url).hostname or ''
    if host in ('127.0.0.1', 'localhost'):
        # Root-relative live assets (e.g. url(/template/images/logo/logo.svg) in repo CSS) are not in the repo:
        # serve them same-origin from lireate.ru so CORS-mode loads (mask-image, fonts) behave as on the live site.
        path = urllib.parse.urlparse(r.request.url).path
        if not OFFLINE and path.startswith('/template/images/') and not os.path.isfile(os.path.join(REPO_ROOT, path.lstrip('/'))):
            try:
                return r.fulfill(response=r.fetch(url='https://lireate.ru' + path))
            except Exception:
                return r.abort()
        return r.continue_()
    if OFFLINE or not any(host == a or host.endswith('.' + a) for a in ALLOW):
        return r.abort()
    return r.continue_()

res = {}; worst = 0
with sync_playwright() as p:
    kw = {'args': ['--no-sandbox']}
    if os.environ.get('LIREATE_CHROME'): kw['executable_path'] = os.environ['LIREATE_CHROME']
    if proxy: kw['proxy'] = proxy
    b = p.chromium.launch(**kw)
    for side in SIDES:
        for w, h in ((1440, 900), (390, 844)):
            ctx = b.new_context(viewport={'width': w, 'height': h}, ignore_https_errors=True)
            ctx.route('**/*', route)
            pg = ctx.new_page()
            for name in PAGES:
                url = f'http://127.0.0.1:{PORT}/tests/fixtures/live-render/{side}/{name}.html'
                key = f'{side}-{name}-{w}'
                try:
                    pg.goto(url, wait_until='load', timeout=120000)
                    pg.wait_for_timeout(1200)
                    pg.evaluate("document.querySelectorAll('.reveal').forEach(e=>e.classList.add('visible'))")
                    pg.screenshot(path=f'{OUT}/{key}.png')
                    pg.screenshot(path=f'{OUT}/{key}-full.png', full_page=True)
                    info = pg.evaluate(INFO_JS)
                    pg.evaluate("document.querySelectorAll('details').forEach(d=>d.open=true)")
                    c = pg.evaluate(CONTRAST_JS, 'body')
                    low = [i for i in c['items'] if i['cr'] < 4.5 and not i['imgs'] and len(i['t']) > 1 and i['t'] != '•']
                    info['low_contrast_count'] = len(low)
                    info['low_contrast'] = sorted(low, key=lambda i: i['cr'])[:80]
                    if name in GRIDS:
                        info['grid'] = {s: pg.evaluate(OVERLAP_JS, s) for s in GRIDS[name]}
                    res[key] = info
                    if side == 'after': worst = max(worst, len(low))
                    print(f"{key}: h={info['scrollHeight']} low<4.5={len(low)} body_bg={info['bodyBg']} hscroll={info['hscroll']}"
                          + (f" grid={info['grid']}" if 'grid' in info else '')
                          + (f" legacyHome={info['legacyHome']} lightV4={info['lightV4Home']}" if name == 'home' else ''))
                except Exception as e:
                    print(f'{key}: FAIL {str(e)[:200]}'); res[key] = {'error': str(e)[:500]}
            ctx.close()
    b.close()
with open(f'{OUT}/metrics.json', 'w') as f:
    json.dump(res, f, ensure_ascii=False, indent=1)
print('screenshots + metrics.json ->', OUT)
if os.environ.get('LRF_FAIL') == '1' and worst:
    print('FAIL: AFTER pages have text below 4.5:1'); sys.exit(1)
PY

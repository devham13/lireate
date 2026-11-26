/* Browser QA for universal LIREATE sidebar.
 * CHROME=/usr/bin/chromium BASE=https://lireate.ru node scripts/lireate_sitewide_sidebar_v7_audit.mjs
 * Requires puppeteer-core already used by project tooling.
 */
import puppeteer from 'puppeteer-core';
import fs from 'node:fs';
import path from 'node:path';

const BASE=(process.env.BASE||'https://lireate.ru').replace(/\/+$/,'');
const CHROME=process.env.CHROME||'/usr/local/bin/google-chrome';
const LIMIT=Math.max(1,Math.min(1000,Number(process.env.LSV7_LIMIT||'500')));
const MANIFEST=path.resolve('pages/services');
const routes=new Set([
 '/', '/findtz/', '/patent', '/patent_prom_obr', '/mktu', '/news',
 '/o_kompanii', '/team','/partners','/docs','/contacts/','/reviews','/portfolio',
 '/patentnye-zadachi/', '/dogovory/','/uslugi/',
 '/registraciya_tovarnogo_znaka/','/prodlenie_tovarnogo_znaka/',
 '/vozrazhenie_protiv_registracii_tovarnogo_znaka/',
 '/zashchita_avtorskih_prav/'
]);
for(const slug of ['svoya-kultura','bentax','avtopravo','kingsname','doza',
'happy-mind','elisa-hotels','strigoon','avenue-deluxe','emiz','nerud-grupp',
'estetic','berti','vf'])routes.add('/portfolio/'+slug+'/');
if(fs.existsSync(MANIFEST)){
  for(const slug of fs.readdirSync(MANIFEST)){
    const f=path.join(MANIFEST,slug,'publish.manifest.json');
    if(!fs.existsSync(f))continue;
    const cfg=JSON.parse(fs.readFileSync(f,'utf8'));
    if(cfg.url_path)routes.add('/'+cfg.url_path.replace(/^\/+|\/+$/g,'')+'/');
  }
}
async function appendSitemap(url,depth=0){
  if(depth>2)return;
  try{
    const response=await fetch(url,{signal:AbortSignal.timeout(13000)});
    if(!response.ok)return;
    const xml=await response.text();
    const loc=[...xml.matchAll(/<loc>\s*([^<]+)\s*<\/loc>/gi)].map(x=>x[1].replaceAll('&amp;','&'));
    for(const item of loc){
      const u=new URL(item,BASE);
      if(u.origin!==new URL(BASE).origin)continue;
      if(/\.xml(?:$|\?)/i.test(u.pathname)){await appendSitemap(u.href,depth+1);continue;}
      if(/\.(pdf|png|jpe?g|webp|gif|svg|mp4|mp3|css|js|zip|docx?|xlsx?)$/i.test(u.pathname))continue;
      if(/\/(admin|api|ajax|login|wp-admin|bitrix|cron)\b/i.test(u.pathname))continue;
      routes.add(u.pathname||'/');
      if(routes.size>=LIMIT)break;
    }
  }catch(e){console.warn('sitemap unavailable:',url,e.message);}
}
await appendSitemap(BASE+'/sitemap.xml');
const all=[...routes].slice(0,LIMIT);
const browser=await puppeteer.launch({executablePath:CHROME,headless:true,args:['--no-sandbox','--disable-dev-shm-usage']});
const results=[];
for(let i=0;i<all.length;i++){
  const route=all[i];const url=BASE+route;
  const page=await browser.newPage();const jsErrors=[];
  page.on('pageerror',e=>jsErrors.push(String(e.message).slice(0,150)));
  const record={url,desktop:null,mobile:null,issues:[]};
  try{
    for(const width of [1440,390]){
      await page.setViewport({width,height:width===1440?900:844,deviceScaleFactor:1,isMobile:false});
      const resp=await page.goto(url,{waitUntil:'domcontentloaded',timeout:25000});
      if(!resp){record.issues.push('missing response '+width);continue;}
      if(resp.status()>=400){record.issues.push('HTTP '+resp.status()+' at '+width);continue;}
      const contentType=resp.headers()['content-type']||'';
      if(!contentType.includes('text/html'))continue;
      try{await page.waitForFunction(()=>!!document.getElementById('leftSideBar') ||
           !!document.body?.getAttribute('data-lsv7-error'),{timeout:9000});}catch{}
      const state=await page.evaluate(()=>{
        const side=document.getElementById('leftSideBar'),doc=document.documentElement;
        const count=document.querySelectorAll('#leftSideBar').length;
        const sidebar=side?side.getBoundingClientRect():null;
        const css=side?getComputedStyle(side):null;
        return{count,width:sidebar?.width??0,left:sidebar?.left??null,
          display:css?.display??null,visibility:css?.visibility??null,
          injected:!!side?.getAttribute('data-lireate-sidebar-origin'),
          error:document.body.getAttribute('data-lsv7-error'),
          hasBurger:!!document.querySelector('#lsv7-mobile-toggle,#lireatePublicCmsBurger,.hamburger'),
          horizontalOverflow:doc.scrollWidth>innerWidth+3,
          text:side?.textContent?.slice(0,45)||''};
      });
      if(width===1440)record.desktop=state;else record.mobile=state;
      if(state.count!==1)record.issues.push('sidebar count '+state.count+' at '+width);
      if(width===1440 && (state.width<70||state.display==='none'||state.visibility==='hidden'||state.left>8))
         record.issues.push('sidebar not visible on desktop');
      if(width===390 && !state.hasBurger)record.issues.push('mobile sidebar toggle missing');
      if(state.horizontalOverflow)record.issues.push('horizontal overflow '+width);
      if(state.error)record.issues.push('fragment load failed');
    }
  }catch(e){record.issues.push('browser navigation: '+e.message);}
  if(jsErrors.length)record.issues.push('JS: '+jsErrors.join(' | '));
  results.push(record);
  console.log((i+1)+'/'+all.length+' '+route+': '+(record.issues.length?record.issues.join('; '):'OK'));
  await page.close();
}
await browser.close();
const report={date:new Date().toISOString(),base:BASE,urls:all.length,failed:results.filter(x=>x.issues.length).length,results};
const outfile=process.env.LSV7_REPORT||'lireate-sidebar-v7-audit.json';
fs.writeFileSync(outfile,JSON.stringify(report,null,2));
console.log('Saved '+outfile+' failed='+report.failed+'/'+report.urls);
process.exit(report.failed?1:0);

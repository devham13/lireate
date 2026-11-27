/* LIREATE portfolio V5 — progressive enhancement, 15 specific routes, ES5-compatible.
 * Does not remove source case facts, certificates, videos, original CTAs or forms.
 */
(function () {
 "use strict";
 var path=decodeURIComponent(window.location.pathname).replace(/\/+$/,"")||"/";
 var root="/portfolio";
 if(path!==root && path.indexOf(root+"/")!==0)return;
 var maxUrl="https://max.ru/channel_lireate";
 var cases=[
 ["svoya-kultura","Своя Культура","г. Коломна","СвояКультура","658340"],
 ["bentax","Bentax и Shtainberg Chemy","г. Новосибирск","Bentax","739063, 739064"],
 ["avtopravo","Автоправо","г. Казань","Автоправо","742798"],
 ["kingsname","KINGSNAME","г. Грозный","KingsName","747597"],
 ["doza","Doza","г. Нальчик","Doza","746998"],
 ["happy-mind","Happy Mind","г. Москва","HappyMind","749295"],
 ["elisa-hotels","Elisa Hotels & Resorts","г. Зеленоградск","ElisaHotelsResorts","749133"],
 ["strigoon","Strigoon","с. Иглино","Strigoon","746223"],
 ["avenue-deluxe","Avenue deluxe","г. Череповец","AvenueDeluxe","737337"],
 ["emiz","Эмиз","г. Казань","Эмиз","743517"],
 ["nerud-grupp","Неруд Групп","г. Тула","НерудГрупп","720437"],
 ["estetic","Estetic","г. Краснодар","Estetic","721633"],
 ["berti","Berti","с. Курчалой","Berti","713201"],
 ["vf","VF","г. Владимир","VF","698481"]
 ];
 function one(s,ctx){return(ctx||document).querySelector(s);}
 function el(tag,cl,txt){var x=document.createElement(tag);if(cl)x.className=cl;if(txt!==undefined)x.textContent=txt;return x;}
 function logo(c){return "/template/images/portfolio/"+encodeURIComponent(c[3])+"/logo.jpg";}
 function escText(s){return(s||"").replace(/\s+/g," ").trim();}
 function hasCityLabel(item,city){
  if(one(".lxpf5-featured-city",item))return true;
  var needle=escText(city).toLocaleLowerCase("ru");
  if(!needle)return false;
  return escText(item.textContent).toLocaleLowerCase("ru").indexOf(needle)!==-1;
 }
 function btn(parent,url,name,cl,external){
   var a=el("a","lxpf5-btn "+cl,name);a.href=url;
   if(external){a.target="_blank";a.rel="noopener noreferrer";}
   parent.appendChild(a);return a;
 }
 function messengerPanel(){
  if(one(".lxpf5-contact"))return;
  var s=el("section","lxpf5-contact");s.setAttribute("aria-label","Консультация в мессенджере");
  var wrap=el("div","lxpf5-contact-inner");s.appendChild(wrap);
  var text=el("div","lxpf5-contact-copy");
  text.appendChild(el("span","lxpf5-eyebrow","НА СВЯЗИ"));
  text.appendChild(el("h2",null,"Обсудим регистрацию вашего товарного знака"));
  text.appendChild(el("p",null,"Расскажите о задаче — поможем определить следующий шаг."));
  wrap.appendChild(text);
  var controls=el("div","lxpf5-messengers");
  btn(controls,"https://t.me/lireate_patent","Telegram ↗","lxpf5-msg",true).setAttribute("data-lx-ym-goal","perehod_v_telegramm");
  btn(controls,maxUrl,"MAX ↗","lxpf5-msg",true).setAttribute("data-lx-ym-goal","perehod_v_messendzher");
  btn(controls,"https://wa.me/79600484957","WhatsApp ↗","lxpf5-msg",true).setAttribute("data-lx-ym-goal","perehod_v_messendzher");
  wrap.appendChild(controls);
  var anchor=one("#portfolio")||one(".lx-case-cta")||one(".register-tz")||one(".main-content")||one("article.lx-case")||one("main")||one(".page_container");
  if(anchor&&anchor.parentNode)anchor.parentNode.insertBefore(s,anchor.nextSibling);
 }
 function index(){
   var face=one("#face"),pf=one("#portfolio");
   if(!face||!pf)return;
   document.body.classList.add("lxpf5-index","lxpf5-ready");
   var features=one(".lx-pf-cases");
   if(features&&features.parentNode&&pf.parentNode){pf.parentNode.insertBefore(features,pf);}
   var list=one(".lx-pf-cases ul");
   if(list){
    list.classList.add("lxpf5-featured-grid");
    var li=list.querySelectorAll("li");
    Array.prototype.forEach.call(li,function(item){
      var link=one('a[href*="/portfolio/"]',item);
      if(!link)return;
      var slug=(link.pathname||"").replace(/\/+$/,"").split("/").pop();
      var c=cases.filter(function(v){return v[0]===slug;})[0];
      if(!c)return;
      item.classList.add("lxpf5-featured-card");
      var cover=el("a","lxpf5-featured-cover");cover.href="/portfolio/"+c[0]+"/";
      var image=el("img");image.loading="lazy";image.decoding="async";image.src=logo(c);image.alt="Товарный знак "+c[1];
      image.onerror=function(){cover.classList.add("lxpf5-cover-fallback");image.style.display="none";cover.textContent=c[1].slice(0,2).toUpperCase();};
      cover.appendChild(image);item.insertBefore(cover,item.firstChild);
      if(!hasCityLabel(item,c[2])){item.appendChild(el("span","lxpf5-featured-city",c[2]));}
      var bottom=el("span","lxpf5-featured-certificate","Свидетельство № "+c[4]+" · Смотреть кейс ↗");item.appendChild(bottom);
    });
    var heading=one(".lx-pf-cases-title");
    if(heading)heading.textContent="Кейсы, которые говорят за нас";
    var searchWrap=el("div","lxpf5-featured-search");
    var inp=el("input");inp.type="search";inp.placeholder="Найти кейс или город";inp.setAttribute("aria-label","Поиск по четырнадцати кейсам");
    searchWrap.appendChild(inp);
    list.parentNode.insertBefore(searchWrap,list);
    var empty=el("p","lxpf5-empty","Ничего не найдено. Измените поисковый запрос.");
    empty.hidden=true;list.parentNode.insertBefore(empty,list.nextSibling);
    inp.addEventListener("input",function(){
       var term=inp.value.toLocaleLowerCase("ru").trim(),visible=0;
       Array.prototype.forEach.call(list.children,function(li){
         var match=escText(li.textContent).toLocaleLowerCase("ru").indexOf(term)!==-1;
         li.hidden=!match;if(match)visible++;
       });empty.hidden=visible!==0;
    });
   }
   var regionHead=el("div","lxpf5-region-intro");
   regionHead.appendChild(el("span","lxpf5-eyebrow","ГЕОГРАФИЯ РЕГИСТРАЦИЙ"));
   regionHead.appendChild(el("h2",null,"Зарегистрированные знаки по регионам"));
   regionHead.appendChild(el("p",null,"Выберите регион, город и откройте свидетельство в реестре ФИПС."));
   var content=one(".contentBox",pf);
   if(content)pf.insertBefore(regionHead,content);
   messengerPanel();
 }
 function detailLegacy(c,original){
   document.body.classList.add("lxpf5-detail","lxpf5-ready");
   var container=one(".case-hero__container",original)||original;
   var title=one(".case-hero__title",container);
   var kicker=el("span","lxpf5-eyebrow lxpf5-case-kicker","LIREATE · ПОРТФОЛИО · ТОВАРНЫЕ ЗНАКИ");
   if(title)container.insertBefore(kicker,title);
   var figure=one(".case-hero__logo",container);
   if(figure){
      var figTitle=el("div","lxpf5-logo-caption");
      figTitle.appendChild(el("span","lxpf5-trademark-label","Кейс регистрации"));
      figTitle.appendChild(el("strong",null,c[1]));
      figTitle.appendChild(el("span",null,"Зарегистрированный товарный знак · "+c[2]));
      figure.appendChild(figTitle);
   }
   var actions=one(".case-hero__actions",container);
   if(actions){
     btn(actions,"https://t.me/lireate_patent","Написать в Telegram ↗","lxpf5-hero-msg",true).setAttribute("data-lx-ym-goal","perehod_v_telegramm");
   }
   var fact=el("div","lxpf5-hero-fact");
   fact.appendChild(el("span",null,"Свидетельство ФИПС"));
   fact.appendChild(el("strong",null,"№ "+c[4]));
   fact.appendChild(el("span",null,c[2]));
   container.appendChild(fact);
   messengerPanel();
 }
 function detailLxCase(c){
   var article=one("article.lx-case");
   var hero=article?one(".lx-case-hero",article):null;
   if(!article||!hero)return false;
   document.body.classList.add("lxpf5-detail","lxpf5-ready","lxpf5-lx-case-body");
   article.classList.add("lxpf5-lx-case");
   hero.classList.add("lxpf5-lx-case-hero");
   var figure=one(".lx-case-logo",hero);
   if(figure){
     figure.classList.add("lxpf5-lx-case-logo");
     var figTitle=el("div","lxpf5-logo-caption");
     figTitle.appendChild(el("span","lxpf5-trademark-label","Кейс регистрации"));
     figTitle.appendChild(el("strong",null,c[1]));
     figTitle.appendChild(el("span",null,"Зарегистрированный товарный знак · "+c[2]));
     figure.appendChild(figTitle);
   }
   var textCol=one(".lx-case-logo",hero)?hero.querySelector(".lx-case-logo + div"):one("div",hero);
   if(textCol){
     var kicker=one(".lxpf5-case-kicker",textCol);
     if(!kicker){
       kicker=el("span","lxpf5-eyebrow lxpf5-case-kicker","LIREATE · ПОРТФОЛИО · ТОВАРНЫЕ ЗНАКИ");
       var h1=one("h1",textCol);
       if(h1)textCol.insertBefore(kicker,h1);else textCol.insertBefore(kicker,textCol.firstChild);
     }
     var fact=one(".lxpf5-hero-fact",textCol);
     if(!fact){
       fact=el("div","lxpf5-hero-fact");
       fact.appendChild(el("span",null,"Свидетельство ФИПС"));
       fact.appendChild(el("strong",null,"№ "+c[4]));
       fact.appendChild(el("span",null,c[2]));
       var actions=one(".lx-case-actions",textCol);
       if(actions)textCol.insertBefore(fact,actions);else textCol.appendChild(fact);
     }
     var actions=one(".lx-case-actions",textCol);
     if(actions){
       btn(actions,"https://t.me/lireate_patent","Написать в Telegram ↗","lxpf5-hero-msg",true).setAttribute("data-lx-ym-goal","perehod_v_telegramm");
     }
   }
   messengerPanel();
   return true;
 }
 function detail(c){
   if(detailLxCase(c))return;
   var original=one(".case-hero");
   if(!original)return;
   detailLegacy(c,original);
 }
 function injectLateCaseOverrides(){
  if(one("#lxpf5-case-override")||!one("article.lx-case"))return;
  var s=el("style");
  s.id="lxpf5-case-override";
  s.textContent="body.lireate-cms-page-dark.lx-portfolio-v5.lxpf5-ready .page_container article.lx-case h1{color:#1f212c!important}"
   +"body.lireate-cms-page-dark.lx-portfolio-v5.lxpf5-ready .page_container article.lx-case .lx-case-lead{color:#3d4f66!important}"
   +"body.lireate-cms-page-dark.lx-portfolio-v5.lxpf5-ready .page_container article.lx-case .lx-case-sub{color:#52637b!important}"
   +"body.lireate-cms-page-dark.lx-portfolio-v5.lxpf5-ready .page_container article.lx-case .lx-case-section h2{color:#1f212c!important}";
  document.body.appendChild(s);
 }
 function init(){
   if(document.documentElement.hasAttribute("data-lxpf5-init"))return;
   var c=cases.filter(function(v){return "/portfolio/"+v[0]===path;})[0];
   if(path!==root&&!c)return;
   document.documentElement.setAttribute("data-lxpf5-init","1");
   if(document.body)document.body.classList.add("lx-portfolio-v5");
   if(c)detail(c);else index();
   injectLateCaseOverrides();
 }
 if(document.readyState==="loading")document.addEventListener("DOMContentLoaded",init);else init();
})();
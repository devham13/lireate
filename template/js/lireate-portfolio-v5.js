/* LIREATE portfolio V5 — progressive enhancement, 15 specific routes, ES5-compatible.
 * Does not remove source case facts, certificates, videos, original CTAs or forms.
 */
(function () {
 "use strict";
 var path=decodeURIComponent(window.location.pathname).replace(/\/+$/,"")||"/";
 var root="/portfolio";
 if(path!==root && path.indexOf(root+"/")!==0)return;
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
  btn(controls,"https://max.ru/u/f9LHodD0cOI8jsPagjibEve2cn5_xPW0X1sf3PhbS5TMijA78DnUeDEpUAI","MAX ↗","lxpf5-msg",true).setAttribute("data-lx-ym-goal","perehod_v_messendzher");
  btn(controls,"https://wa.me/79600484957","WhatsApp ↗","lxpf5-msg",true).setAttribute("data-lx-ym-goal","perehod_v_messendzher");
  wrap.appendChild(controls);
  var anchor=one("#portfolio")||one("main")||one(".page_container");
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
      var city=el("span","lxpf5-featured-city",c[2]);item.appendChild(city);
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
 function detail(c){
   var h1=one("main h1")||one(".page_container h1");
   if(!h1)return;
   var main=h1.closest("main")||one("main")||one(".page_container");
   if(!main)return;
   document.body.classList.add("lxpf5-detail","lxpf5-ready");
   var hero=el("section","lxpf5-detail-hero");hero.setAttribute("aria-label","Кейс из портфолио Лирейт");
   var copy=el("div","lxpf5-detail-copy");
   var breadcrumbs=el("nav","lxpf5-breadcrumbs");breadcrumbs.setAttribute("aria-label","Хлебные крошки");
   var home=el("a",null,"Главная");home.href="/";breadcrumbs.appendChild(home);
   breadcrumbs.appendChild(el("span",null,"/"));
   var portfolio=el("a",null,"Портфолио");portfolio.href="/portfolio/";breadcrumbs.appendChild(portfolio);
   breadcrumbs.appendChild(el("span",null,"/ "+c[1]));
   copy.appendChild(breadcrumbs);
   copy.appendChild(el("span","lxpf5-eyebrow","LIREATE · РЕГИСТРАЦИЯ ТОВАРНОГО ЗНАКА"));
   h1.classList.add("lxpf5-hero-title");copy.appendChild(h1);
   var ps=main.querySelectorAll("p"),intro=null;
   Array.prototype.forEach.call(ps,function(p){
     if(!intro&&p.textContent.indexOf("Бюро «Лирейт» зарегистрировало")!==-1)intro=p;
   });
   if(intro){intro.classList.add("lxpf5-hero-lead");copy.appendChild(intro);}
   else{copy.appendChild(el("p","lxpf5-hero-lead","Практика патентного бюро «Лирейт»: сопровождение регистрации товарных знаков."));}
   var actions=el("div","lxpf5-hero-actions");
   btn(actions,"https://t.me/lireate_patent","Обсудить свой знак ↗","lxpf5-red",true).setAttribute("data-lx-ym-goal","perehod_v_telegramm");
   btn(actions,"/registraciya_tovarnogo_znaka/","Услуга регистрации","lxpf5-outline",false);
   copy.appendChild(actions);
   var mini=el("div","lxpf5-hero-fact");mini.appendChild(el("span",null,"Свидетельство ФИПС"));mini.appendChild(el("strong",null,"№ "+c[4]));mini.appendChild(el("span",null,c[2]));copy.appendChild(mini);
   hero.appendChild(copy);
   var card=el("figure","lxpf5-trademark-card");
   var imageBox=el("div","lxpf5-trademark-photo");
   var image=el("img");image.src=logo(c);image.alt="Зарегистрированный товарный знак «"+c[1]+"»";image.decoding="async";
   image.onerror=function(){image.style.display="none";imageBox.appendChild(el("span","lxpf5-logo-fallback",c[1]));};
   imageBox.appendChild(image);card.appendChild(imageBox);
   var cap=el("figcaption","lxpf5-trademark-caption");
   cap.appendChild(el("span","lxpf5-trademark-label","Кейс регистрации"));
   cap.appendChild(el("strong",null,c[1]));
   cap.appendChild(el("span",null,"Зарегистрированный товарный знак клиента · "+c[2]));
   var link=el("a",null,"Другие работы Лирейт ↗");link.href="/portfolio/";cap.appendChild(link);
   card.appendChild(cap);hero.appendChild(card);
   // Place above case content but below existing breadcrumbs if they are first.
   var first=main.firstElementChild;
   if(first&&first.tagName==="NAV"&&/breadcrumb/i.test(first.className)){main.insertBefore(hero,first.nextSibling);}
   else{main.insertBefore(hero,first);}
   messengerPanel();
 }
 function init(){
   if(document.documentElement.hasAttribute("data-lxpf5-init"))return;
   var c=cases.filter(function(v){return "/portfolio/"+v[0]===path;})[0];
   if(path!==root&&!c)return;
   document.documentElement.setAttribute("data-lxpf5-init","1");
   if(c)detail(c);else index();
 }
 if(document.readyState==="loading")document.addEventListener("DOMContentLoaded",init);else init();
})();

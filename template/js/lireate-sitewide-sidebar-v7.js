/* LIREATE Sitewide Sidebar V7 (public pages only).
 * Existing CMS menus are never duplicated. A missing sidebar is requested from
 * the same-origin PHP fragment. Works for CMS + standalone services and tools.
 * No analytics, forms, SEO tags or existing top-menu elements are changed.
 */
(function(){
"use strict";
var FRAGMENT="/template/lireate/lireate-sidebar-v7.html";
var FRAGMENT_PHP="/template/lireate/lireate-sidebar-v7-fragment.php";
var SKIP=/^\/(?:admin|bitrix|wp-admin|wp-json|api|auth|login|personal|ajax|cron|vendor|upload|assets|template)\b/i;
if(SKIP.test(location.pathname))return;
var body=document.body;
function init(){
 body=document.body;
 if(!body || body.getAttribute("data-lsv7-loaded"))return;
 body.setAttribute("data-lsv7-loaded","1");
 var existing=document.getElementById("leftSideBar");
 if(existing){
   body.classList.add("lsv7-sidebar-present");
   var style=window.getComputedStyle(existing);
   existing.classList.add("lsv7-sidebar");
   if(!existing.getAttribute("data-lireate-sidebar-origin")){
     existing.setAttribute("data-lireate-sidebar-origin","server-inline");
   }
   body.classList.add("lsv7-managed");
   if(window.innerWidth>1150 && (style.display==="none" || style.visibility==="hidden")){
     body.classList.add("lsv7-recovered");
   }
   bind(existing);
   return;
 }
 function loadFragment(url){
   if(window.fetch){
     return fetch(url,{credentials:"same-origin",cache:"default"}).then(function(res){
       if(!res.ok)throw new Error("Sidebar fragment HTTP "+res.status+" "+url);
       return res.text();
     });
   }
   return new Promise(function(resolve,reject){
     var xhr=new XMLHttpRequest();xhr.open("GET",url,true);
     xhr.onload=function(){if(xhr.status===200)resolve(xhr.responseText);else reject(new Error("HTTP "+xhr.status+" "+url));};
     xhr.onerror=function(){reject(new Error("Network error "+url));};xhr.send();
   });
 }
 var request=loadFragment(FRAGMENT).catch(function(){
    return loadFragment(FRAGMENT_PHP);
 });
 request.then(function(html){
   if(document.getElementById("leftSideBar"))return;
   var wrap=document.createElement("div");wrap.innerHTML=html;
   var sidebar=wrap.querySelector("#leftSideBar");
   if(!sidebar)throw new Error("Missing #leftSideBar in sidebar fragment");
   sidebar.classList.add("lsv7-sidebar");
   sidebar.setAttribute("data-lireate-sidebar-origin","sitewide-v7");
   // Mark first; layout CSS applies as soon as inserted.
   body.classList.add("lsv7-managed","lsv7-injected");
   body.insertBefore(sidebar,body.firstChild);
   bind(sidebar);
 }).catch(function(err){
   body.removeAttribute("data-lsv7-loaded");
   body.setAttribute("data-lsv7-error","fragment-unavailable");
   if(window.console && console.warn)console.warn("[LIREATE sidebar]",err.message||err);
 });
}
function ensureExpertPortrait(sidebar){
 if(document.body&&document.body.classList.contains("lx-photo-v4"))return;
 if(sidebar.querySelector(".lsv7-sidebar-expert"))return;
 var head=sidebar.querySelector(".lpc-sidebar-head");
 var logo=sidebar.querySelector("a.leftSideLogo");
 var html='<figure class="lsv7-sidebar-expert" aria-label="Эксперт LIREATE">'+
   '<img src="/template/images/team/person/11.webp" width="64" height="64" alt="Горбачев Юрий Александрович — управляющий партнёр LIREATE" loading="lazy" decoding="async">'+
   '<figcaption class="lsv7-sidebar-expert-caption"><strong>Горбачев Юрий Александрович</strong><span>Управляющий партнёр</span></figcaption></figure>';
 var wrap=document.createElement("div");
 wrap.innerHTML=html;
 var fig=wrap.firstChild;
 if(head&&head.parentNode){
   head.parentNode.insertBefore(fig,head.nextSibling);
 }else if(logo&&logo.parentNode){
   logo.parentNode.insertBefore(fig,logo.nextSibling);
 }else{
   sidebar.insertBefore(fig,sidebar.firstChild);
 }
}
function bind(sidebar){
 ensureExpertPortrait(sidebar);
 var page=(location.pathname||"/").replace(/\/+$/,"")||"/";
 var questions=Array.prototype.slice.call(sidebar.querySelectorAll(".js-question"));
 var answers=Array.prototype.slice.call(sidebar.querySelectorAll(".js-answer"));
 questions.forEach(function(q,i){
   var answer=answers[i];if(!answer)return;
   var id="lsv7-nav-"+i;
   answer.id=id;
   answer.hidden=true;
   answer.style.height="";
   q.setAttribute("role","button");
   q.setAttribute("tabindex","0");
   q.setAttribute("aria-controls",id);
   q.setAttribute("aria-expanded","false");
   function toggle(){
     var open=q.getAttribute("aria-expanded")!=="true";
     questions.forEach(function(other,j){
        other.setAttribute("aria-expanded","false");
        if(answers[j])answers[j].hidden=true;
     });
     q.setAttribute("aria-expanded",open?"true":"false");
     answer.hidden=!open;
   }
   q.addEventListener("click",toggle);
   q.addEventListener("keydown",function(ev){
     if(ev.key==="Enter"||ev.key===" "){ev.preventDefault();toggle();}
   });
 });
 var links=sidebar.querySelectorAll('a[href]');
 Array.prototype.forEach.call(links,function(link){
   if(link.protocol!=="http:"&&link.protocol!=="https:")return;
   if(link.hostname!==location.hostname)return;
   var dest=link.pathname.replace(/\/+$/,"")||"/";
   var current=(dest===page) || (dest==="/patentnye-zadachi" && page.indexOf(dest+"/")===0);
   if(!current)return;
   link.classList.add("lsv7-current","lsn-current");
   link.setAttribute("aria-current","page");
   var group=link.closest(".js-answer");
   if(group&&group.previousElementSibling){
      group.hidden=false;
      group.previousElementSibling.setAttribute("aria-expanded","true");
   }
 });
 var collapse=sidebar.querySelector("#collapseSide");
 if(collapse){
   collapse.addEventListener("click",function(){
      var isCollapsed=body.classList.toggle("lsv7-collapsed");
      collapse.setAttribute("aria-pressed",isCollapsed?"true":"false");
      collapse.setAttribute("aria-label",isCollapsed?"Развернуть левое меню":"Свернуть левое меню");
   });
 }
 var burger=document.createElement("button");
 burger.type="button";
 burger.id="lsv7-mobile-toggle";
 burger.className="lsv7-mobile-toggle";
 burger.setAttribute("aria-controls","leftSideBar");
 burger.setAttribute("aria-expanded","false");
 burger.setAttribute("aria-label","Открыть меню сайта");
 burger.textContent="☰ Меню";
 var veil=document.createElement("div");
 veil.className="lsv7-veil";
 veil.hidden=true;
 veil.setAttribute("aria-hidden","true");
 body.appendChild(veil);
 body.appendChild(burger);
 function open(value){
   body.classList.toggle("lsv7-open",value);
   burger.setAttribute("aria-expanded",value?"true":"false");
   burger.setAttribute("aria-label",value?"Закрыть меню сайта":"Открыть меню сайта");
   burger.textContent=value?"✕ Закрыть":"☰ Меню";
   veil.hidden=!value;
 }
 burger.addEventListener("click",function(){open(!body.classList.contains("lsv7-open"));});
 veil.addEventListener("click",function(){open(false);});
 document.addEventListener("keydown",function(ev){if(ev.key==="Escape")open(false);});
 sidebar.addEventListener("click",function(ev){var t=ev.target.closest("a");if(t)open(false);});
 window.addEventListener("resize",function(){if(window.innerWidth>1150)open(false);},{passive:true});
}
if(document.readyState==="loading")document.addEventListener("DOMContentLoaded",init,{once:true});else init();
})();

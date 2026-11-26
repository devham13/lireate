/* LIREATE Sitewide Sidebar V7 (public pages only).
 * Existing CMS menus are never duplicated. A missing sidebar is requested from
 * the same-origin PHP fragment. Works for CMS + standalone services and tools.
 * No analytics, forms, SEO tags or existing top-menu elements are changed.
 */
(function(){
"use strict";
var FRAGMENT="/template/lireate/lireate-sidebar-v7-fragment.php";
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
   // A CMS sidebar should be managed by its existing navigation controller.
   // Repair only truly display:none sidebars on desktop; never replace markup.
   if(window.innerWidth>1150 && (style.display==="none" || style.visibility==="hidden")){
     existing.classList.add("lsv7-sidebar");
     existing.setAttribute("data-lireate-sidebar-origin","recovered");
     body.classList.add("lsv7-managed","lsv7-recovered");
     bind(existing);
   }
   return;
 }
 var request;
 if(window.fetch){
   request=fetch(FRAGMENT,{credentials:"same-origin",cache:"default"}).then(function(res){
     if(!res.ok)throw new Error("Sidebar fragment HTTP "+res.status);
     return res.text();
   });
 }else{
   request=new Promise(function(resolve,reject){
     var xhr=new XMLHttpRequest();xhr.open("GET",FRAGMENT,true);
     xhr.onload=function(){if(xhr.status===200)resolve(xhr.responseText);else reject(new Error("HTTP "+xhr.status));};
     xhr.onerror=function(){reject(new Error("Network error"));};xhr.send();
   });
 }
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
function bind(sidebar){
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

/* LIREATE QA V6 — progressive enhancement for approved pages.
 * Does not replace server-rendered content, forms, metrics or URLs.
 */
(function(){
  "use strict";
  var path=(location.pathname||"/").replace(/\/+$/,"")||"/";
  function select(s,root){return(root||document).querySelector(s);}
  function all(s,root){return Array.prototype.slice.call((root||document).querySelectorAll(s));}
  function initFooter(){
    all("footer .lx-site-footer-requisites, .lx-site-footer-nav .lx-site-footer-requisites").forEach(function(details){
       if(!details.parentNode)return;
       var a=document.createElement("a");
       a.href="/docs";a.className="lx-site-footer-documents-link";
       a.textContent="Реквизиты";
       a.setAttribute("aria-label","Реквизиты компании — перейти к документам");
       details.parentNode.replaceChild(a,details);
    });
  }
  function initPatentTools(){
     if(path!=="/patentnye-zadachi" && path.indexOf("/patentnye-zadachi/")!==0)return;
     document.body.classList.add("lxq-patent-tasks");
     var sidebar=select("#leftSideBar.lxq-tools-sidebar");
     if(!sidebar)return;
     all(".js-question",sidebar).forEach(function(q,i){
        var panel=q.nextElementSibling;
        if(!panel||!panel.classList.contains("js-answer"))return;
        var id="lxq-sidenav-group-"+i;
        panel.id=id;panel.hidden=true;
        q.setAttribute("role","button");q.setAttribute("tabindex","0");
        q.setAttribute("aria-controls",id);q.setAttribute("aria-expanded","false");
        function toggle(){
           var open=q.getAttribute("aria-expanded")!=="true";
           q.setAttribute("aria-expanded",open?"true":"false");
           panel.hidden=!open;
        }
        q.addEventListener("click",toggle);
        q.addEventListener("keydown",function(e){if(e.key==="Enter"||e.key===" "){e.preventDefault();toggle();}});
     });
     all("a[href]",sidebar).forEach(function(a){
        var target=(new URL(a.getAttribute("href"),location.href)).pathname.replace(/\/+$/,"")||"/";
        if(target===path){a.setAttribute("aria-current","page");var panel=a.closest(".js-answer");if(panel&&panel.previousElementSibling){panel.hidden=false;panel.previousElementSibling.setAttribute("aria-expanded","true");}}
     });
     var btn=document.createElement("button");
     btn.type="button";btn.className="lxq-mobile-nav-toggle";btn.textContent="☰ Меню";
     btn.setAttribute("aria-label","Открыть левое меню");
     btn.setAttribute("aria-expanded","false");btn.setAttribute("aria-controls","leftSideBar");
     var overlay=document.createElement("div");overlay.className="lxq-overlay";overlay.hidden=true;
     document.body.appendChild(overlay);document.body.appendChild(btn);
     function open(status){
        document.body.classList.toggle("lxq-sidebar-open",status);
        overlay.hidden=!status;btn.setAttribute("aria-expanded",status?"true":"false");
        btn.textContent=status?"✕ Закрыть":"☰ Меню";
     }
     btn.addEventListener("click",function(){open(!document.body.classList.contains("lxq-sidebar-open"));});
     overlay.addEventListener("click",function(){open(false);});
     document.addEventListener("keydown",function(e){if(e.key==="Escape")open(false);});
     all("a",sidebar).forEach(function(a){a.addEventListener("click",function(){open(false);});});
  }
  function initMktu(){
    if(path!=="/mktu")return;
    document.body.classList.add("lxq-mktu");
  }
  function patentClean(){
    if(path!=="/patent"&&path!=="/patent_prom_obr")return;
    document.body.classList.add("lxq-patent-legal");
    var main=select("main")||select(".page_container");
    if(!main)return;
    var children=Array.prototype.slice.call(main.children);
    children.forEach(function(item,i){
       // Remove only proven-empty direct section placeholders, never inputs,
       // forms, images, SVGs, iframes, contentful headings, or CMS popups.
       if(!/^(SECTION|DIV)$/.test(item.tagName))return;
       if(item.id && /contact|form|modal|map|faq|hero|certificate/i.test(item.id))return;
       if((item.textContent||"").trim())return;
       if(select("form,iframe,img,picture,video,svg,canvas,button,input,a,[role='application']",item))return;
       if(item.getBoundingClientRect().height>36){
          item.classList.add("lxq-verified-empty");
       }
    });
  }
  function covers(){
     all(".lireate-blog-list-card-cover img, .lireate-blog-list-featured-cover img").forEach(function(img){
       img.addEventListener("error",function(){
         if(img.parentNode){img.parentNode.classList.add("lireate-blog-list-card-cover--fallback");}
         img.hidden=true;
       },{once:true});
       if(img.complete && img.naturalWidth===0)img.dispatchEvent(new Event("error"));
     });
  }
  function start(){initFooter();initPatentTools();initMktu();patentClean();covers();}
  if(document.readyState==="loading")document.addEventListener("DOMContentLoaded",start,{once:true});else start();
})();

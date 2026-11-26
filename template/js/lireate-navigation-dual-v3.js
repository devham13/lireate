/* LIREATE Dual Navigation V3: mark visible homepage section. CMS menu is left intact. */
(function(){
 "use strict";
 function init(){
  var root=document.getElementById("lireate-home-light-v4");
  if(!root)return;
  var nav=root.querySelector(".lh4-subnav-links");
  if(!nav)return;
  var items=Array.prototype.filter.call(
   Array.prototype.map.call(nav.querySelectorAll('a[href^="#"]'),function(link){
    return {link:link,target:root.querySelector(link.getAttribute("href"))};
   }),function(item){return !!item.target;}
  );
  if(!items.length)return;
  function active(item){
   items.forEach(function(x){var chosen=x===item;
    x.link.classList.toggle("ln3-active",chosen);
    if(chosen)x.link.setAttribute("aria-current","location");else x.link.removeAttribute("aria-current");
   });
  }
  function update(){
   var choice=items[0],distance=Infinity,limit=window.innerHeight*.28;
   items.forEach(function(item){
    var rect=item.target.getBoundingClientRect();
    if(rect.bottom>limit){var d=Math.abs(rect.top-limit);if(d<distance){choice=item;distance=d;}}
   });
   active(choice);
  }
  var ticking=false;
  window.addEventListener("scroll",function(){
   if(ticking)return;ticking=true;
   (window.requestAnimationFrame||function(cb){return setTimeout(cb,16);})(function(){ticking=false;update();});
  },{passive:true});
  window.addEventListener("resize",update,{passive:true});
  items.forEach(function(item){item.link.addEventListener("click",function(){active(item);});});
  update();
 }
 if(document.readyState==="loading")document.addEventListener("DOMContentLoaded",init);
 else init();
})();

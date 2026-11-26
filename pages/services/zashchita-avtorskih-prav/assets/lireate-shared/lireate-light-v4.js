/* LIREATE Light V4 motion — progressive, ES5 and reduced-motion safe. */
(function(){
  "use strict";
  var body=document.body;
  if(!body||!body.classList.contains("lireate-theme-light-v4"))return;
  var reduce=false;
  try{reduce=window.matchMedia("(prefers-reduced-motion: reduce)").matches}catch(e){}
  var targets=document.querySelectorAll(".lx-card-grid .lx-card, .lx-pilot-journey-track .lx-pilot-step, .lx-related-grid .lx-related-card, .l4-service-illustration-card");
  var each=function(list,fn){Array.prototype.forEach.call(list,fn)};
  if(!reduce&&"IntersectionObserver" in window){
    body.classList.add("l4-motion-ready");
    var io=new IntersectionObserver(function(entries){
      each(entries,function(entry){if(entry.isIntersecting){entry.target.classList.add("l4-in");io.unobserve(entry.target)}});
    },{rootMargin:"0px 0px 0px 0px",threshold:.06});
    each(targets,function(el,i){el.classList.add("l4-reveal");el.style.transitionDelay=((i%4)*70)+"ms";io.observe(el)});
    /* Never lose readable content when animations cannot complete. */
    setTimeout(function(){each(targets,function(el){el.classList.add("l4-in")})},2600);
  }
  var nav=document.querySelector(".lx-nav-pill");
  if(nav){var ticking=false;function update(){
    ticking=false;nav.classList.toggle("l4-nav-scrolled",(window.pageYOffset||0)>20);
  }
    window.addEventListener("scroll",function(){if(!ticking){ticking=true;(window.requestAnimationFrame||setTimeout)(update)}},{passive:true});update();
  }
})();

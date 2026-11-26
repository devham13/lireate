/* LIREATE six CMS service pages: home-style expert card in hero.
   Keep CMS page metadata, content, forms, analytics and navigation unchanged. */
(function () {
  'use strict';
  var BASE='/template/images/services-v4/';
  var PAGES={
    '/findtz':{photo:'specialist-certificate.webp',role:'Проверка товарных знаков',next:'Оценим риски сходства и поможем определить следующий шаг перед подачей заявки.'},
    '/registraciya_tovarnogo_znaka':{photo:'specialist-registraciya.webp',role:'Регистрация товарных знаков',next:'Изучим ваш знак и товары, подскажем маршрут регистрации и перечень документов.'},
    '/prodlenie_tovarnogo_znaka':{photo:'specialist-prolongation.webp',role:'Продление товарных знаков',next:'Проверим срок охраны и объясним, какие документы необходимы для продления.'},
    '/vozrazhenie_protiv_registracii_tovarnogo_znaka':{photo:'specialist-opposition.webp',role:'Споры о товарных знаках',next:'Разберём основания возражения, документы и возможные варианты защиты.'},
    '/zashchita_avtorskih_prav':{photo:'specialist-copyright.webp',role:'Защита авторских прав',next:'Оценим доказательства авторства, нарушение и способы защиты ваших прав.'},
    '/dogovory':{photo:'specialist-registraciya.webp',role:'Договоры в сфере интеллектуальной собственности',next:'Опишите объект прав и задачу сделки — подберём подходящий вид договора.'}
  };
  var CHEST={focus:'50% 20%',zoom:'1',origin:'50% 24%'};
  var PHOTO_FRAME={
    'specialist-certificate.webp':{focus:'50% 24%',zoom:'1',origin:'50% 28%'},
    'specialist-registraciya.webp':CHEST,
    'specialist-prolongation.webp':CHEST,
    'specialist-opposition.webp':CHEST,
    'specialist-copyright.webp':CHEST
  };
  function make(tag,cls,text){var e=document.createElement(tag);if(cls)e.className=cls;if(text)e.textContent=text;return e;}
  function link(url,label){var a=make('a','',label+' ↗');a.href=url;return a;}
  function init(){
    var path=(window.location.pathname||'').replace(/\/+$/,'').toLowerCase();
    var config=PAGES[path];if(!config)return;
    var page=document.querySelector('.lireate-svc-page');if(!page)return;
    var hero=page.querySelector('.lx-hero');if(!hero || hero.querySelector('.lx-photo-v4-expert-card'))return;
    var text=[].slice.call(hero.children).filter(function(n){return n.tagName==='DIV' && !n.classList.contains('lx-hero-mascot-slot') && !n.classList.contains('lx-glass');})[0];
    if(!text)return;
    document.body.classList.add('lx-photo-v4');
    text.classList.add('lx-photo-v4-copy');
    var stack=make('aside','lx-photo-v4-expert-stack');stack.setAttribute('aria-label','Команда Лирейт');
    var card=make('article','lx-photo-v4-expert-card');
    var media=make('div','lx-photo-v4-expert-media');
    media.setAttribute('data-photo',config.photo.replace(/\.webp$/,''));
    var frame=PHOTO_FRAME[config.photo]||CHEST;
    media.style.setProperty('--pv-photo-focus',frame.focus);
    media.style.setProperty('--pv-photo-zoom',frame.zoom);
    media.style.setProperty('--pv-photo-origin',frame.origin);
    var img=document.createElement('img');img.src=BASE+config.photo;img.alt='Фотография специалиста Лирейт';img.loading='eager';img.decoding='async';
    img.setAttribute('fetchpriority','high');
    img.onerror=function(){stack.style.display='none';};
    media.appendChild(img);card.appendChild(media);
    var info=make('div','lx-photo-v4-expert-info');
    info.appendChild(make('span','lx-photo-v4-expert-role',config.role));
    // No unsupported identity inference from portraits. Name can be added later after CMS verification.
    info.appendChild(make('h2','lx-photo-v4-expert-name','Специалист Лирейт'));
    info.appendChild(make('p','lx-photo-v4-expert-desc','Консультации, сопровождение заявок и защита интеллектуальной собственности.'));
    var links=make('nav','lx-photo-v4-expert-links');links.setAttribute('aria-label','Информация о команде');
    links.appendChild(link('/team','Команда Лирейт'));links.appendChild(link('/o_kompanii','О компании'));
    info.appendChild(links);card.appendChild(info);stack.appendChild(card);
    var note=make('div','lx-photo-v4-expert-next');
    note.appendChild(make('strong','','Понятный первый шаг'));
    note.appendChild(make('p','',config.next));stack.appendChild(note);
    hero.insertBefore(stack,text.nextSibling);
    var oldPanel=hero.querySelector('.lx-glass');
    if(oldPanel){oldPanel.classList.add('lx-photo-v4-summary');hero.parentNode.insertBefore(oldPanel,hero.nextSibling);}
  }
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init);else init();
})();

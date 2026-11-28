/* LIREATE six CMS service pages: home-style expert card in hero.
   Keep CMS page metadata, content, forms, analytics and navigation unchanged. */
(function () {
  'use strict';
  var BASE='/template/images/services-v4/';
  var ASSET_VER='20261128-findtz6';
  var POS_REG_SPECIALIST='Специалист по регистрации интеллектуальной собственности';
  var PAGES={
    '/findtz':{photo:'findtz-hero.webp',photoBase:'/template/images/findtz/',photoJpg:'findtz-hero.jpg',role:'Проверка товарных знаков',name:'',position:'Проверка товарных знаков',captionShort:'Проверим обозначение по реестру до уплаты пошлин',next:'Оценим риски сходства и поможем определить следующий шаг перед подачей заявки.'},
    '/registraciya_tovarnogo_znaka':{photo:'specialist-registraciya.webp',role:'Регистрация товарных знаков',name:'Квасова Анна',position:POS_REG_SPECIALIST,next:'Изучим ваш знак и товары, подскажем маршрут регистрации и перечень документов.'},
    '/prodlenie_tovarnogo_znaka':{photo:'specialist-prolongation.webp',role:'Продление товарных знаков',name:'Ульданова Алёна',position:POS_REG_SPECIALIST,next:'Проверим срок охраны и объясним, какие документы необходимы для продления.'},
    '/vozrazhenie_protiv_registracii_tovarnogo_znaka':{photo:'specialist-opposition.webp',role:'Споры о товарных знаках',name:'Ульданова Алёна',position:POS_REG_SPECIALIST,next:'Разберём основания возражения, документы и возможные варианты защиты.'},
    '/zashchita_avtorskih_prav':{photo:'specialist-copyright.webp',role:'Защита авторских прав',name:'Ульданова Алёна',position:POS_REG_SPECIALIST,next:'Оценим доказательства авторства, нарушение и способы защиты ваших прав.'},
    '/dogovory':{photo:'specialist-registraciya.webp',role:'Договоры в сфере интеллектуальной собственности',name:'Квасова Анна',position:POS_REG_SPECIALIST,next:'Опишите объект прав и задачу сделки — подберём подходящий вид договора.'}
  };
  function make(tag,cls,text){var e=document.createElement(tag);if(cls)e.className=cls;if(text)e.textContent=text;return e;}
  function link(url,label){var a=make('a','',label+' ↗');a.href=url;return a;}
  function init(){
    var path=(window.location.pathname||'').replace(/\/+$/,'').toLowerCase();
    var config=PAGES[path];if(!config)return;
    var page=document.querySelector('.lireate-svc-page');if(!page)return;
    var hero=page.querySelector('.lx-hero');if(!hero)return;
    var existing=hero.querySelector('.lx-photo-v4-expert-card');
    if(existing){
      document.body.classList.add('lx-photo-v4');
      var oldPanelOnly=hero.querySelector('.lx-glass');
      if(oldPanelOnly && !oldPanelOnly.classList.contains('lx-photo-v4-summary')){
        oldPanelOnly.classList.add('lx-photo-v4-summary');
        hero.parentNode.insertBefore(oldPanelOnly,hero.nextSibling);
      }
      return;
    }
    var text=[].slice.call(hero.children).filter(function(n){return n.tagName==='DIV' && !n.classList.contains('lx-hero-mascot-slot') && !n.classList.contains('lx-glass');})[0];
    if(!text)return;
    document.body.classList.add('lx-photo-v4');
    text.classList.add('lx-photo-v4-copy');
    var stack=make('aside','lx-photo-v4-expert-stack');stack.setAttribute('aria-label','Команда Лирейт');
    var card=make('article','lx-photo-v4-expert-card');
    var media=make('div','lx-photo-v4-expert-media');
    if(path==='/findtz')media.classList.add('lx-photo-v4-expert-media--findtz');
    media.setAttribute('data-photo',config.photo.replace(/\.webp$/,''));
    var assetBase=config.photoBase||BASE;
    var img=document.createElement('img');
    img.alt=config.name?('Фотография: '+config.name):'Сотрудник LIREATE — экспертное сопровождение';
    img.loading='eager';img.decoding='async';
    img.setAttribute('fetchpriority','high');
    if(config.photoJpg){
      var pic=document.createElement('picture');
      var srcWebp=document.createElement('source');
      srcWebp.type='image/webp';
      srcWebp.srcSet=assetBase+config.photo+'?v='+ASSET_VER;
      img.src=assetBase+config.photoJpg+'?v='+ASSET_VER;
      pic.appendChild(srcWebp);pic.appendChild(img);media.appendChild(pic);
    }else{
      img.src=assetBase+config.photo+'?v='+ASSET_VER;
      media.appendChild(img);
    }
    img.onerror=function(){stack.style.display='none';};
    if(path==='/findtz'){
      var cap=make('div','lx-photo-v4-expert-caption');
      cap.appendChild(make('span','lx-photo-v4-expert-caption-kicker','LIREATE / ЭКСПЕРТНОЕ СОПРОВОЖДЕНИЕ'));
      cap.appendChild(make('p','lx-photo-v4-expert-caption-lead', config.captionShort || config.role));
      media.appendChild(cap);
    }
    card.appendChild(media);
  var note=null;
    if(path!=='/findtz'){
      var info=make('div','lx-photo-v4-expert-info');
      info.appendChild(make('span','lx-photo-v4-expert-role',config.position||config.role));
      if(config.name){info.appendChild(make('h2','lx-photo-v4-expert-name',config.name));}
      info.appendChild(make('p','lx-photo-v4-expert-desc','Консультации, сопровождение заявок и защита интеллектуальной собственности.'));
      var links=make('nav','lx-photo-v4-expert-links');links.setAttribute('aria-label','Информация о команде');
      links.appendChild(link('/team','Команда Лирейт'));links.appendChild(link('/o_kompanii','О компании'));
      info.appendChild(links);card.appendChild(info);
      note=make('div','lx-photo-v4-expert-next');
      note.appendChild(make('strong','','Понятный первый шаг'));
      note.appendChild(make('p','',config.next));
    }
    stack.appendChild(card);
    if(note){stack.appendChild(note);}
    hero.insertBefore(stack,text.nextSibling);
    var oldPanel=hero.querySelector('.lx-glass');
    if(oldPanel){oldPanel.classList.add('lx-photo-v4-summary');hero.parentNode.insertBefore(oldPanel,hero.nextSibling);}
  }
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init);else init();
})();

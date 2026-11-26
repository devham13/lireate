<!DOCTYPE html>
<html lang="ru" data-lireate-metrika-id="52622734">
<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="yandex-verification" content="4a1ac572179ffaab" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>{title}</title>
	<meta name="keywords" content="{keywords}">
	<meta name="description" content="{description}">
	<?php
	$_lireate_seo_head = dirname(__DIR__) . '/data/lireate-seo-head.inc.php';
	if (is_file($_lireate_seo_head)) {
		require_once $_lireate_seo_head;
		lireate_seo_render_head_tags();
	}
	?>
	<meta name="yandex-verification" content="8fc61a53fea72860" />
	<!-- преврашает ссылку в массив с отдельными строками -->
	<?php $uri_parts = explode('?', $_SERVER['REQUEST_URI'], 2);?>
	

    <link rel='stylesheet' href='{theme_url}/css/main/style3.css' type='text/css'>
	
	<?php if (empty($GLOBALS['lireate_seo_og_done'])) : ?>
	<meta property="og:url" content='<?="https://". $_SERVER["SERVER_NAME"] . $uri_parts[0];?>'/>
	<meta property="og:type" content="website"/>
	<meta property="og:title" content="{title}"/>
	<meta property="og:description" content="{description}"/>
	<?php endif; ?>
	<meta name="yandex-verification" content="8fc61a53fea72860" />
	<script type="text/javascript">!function(){var t=document.createElement("script");t.type="text/javascript",t.async=!0,t.src="https://vk.com/js/api/openapi.js?160",t.onload=function(){VK.Retargeting.Init("VK-RTRG-361460-5Q5Pt"),VK.Retargeting.Hit()},document.head.appendChild(t)}();</script><noscript><img src="https://vk.com/rtrg?p=VK-RTRG-361460-5Q5Pt" style="position:fixed; left:-999px;" alt=""/></noscript>
	
	<!-- Добавлено 13.05.2021 -->
	<meta name="facebook-domain-verification" content="jk2b84r99ipq5jaziyii1vht3a6uuc" />
	 
    
	 <?php /* lireate_design_og_image_guard_v1 */ if (empty($GLOBALS['lireate_seo_og_done'])) : ?>
	 <?php if($temp['news_id']!=false){
		$news_article = get_news_article($db, 'news', '"'.$temp['news_page'].'"');
		$img_pos = strpos($news_article[0]['full_story'],'src=');
		if($img_pos){
			//$img = substr($news_article[0]['full_story'], $img_pos+5, strpos($news_article[0]['full_story'],'"',$img_pos+6) - ($img_pos+5));
			$img = substr($news_article[0]['full_story'], $img_pos+27, strpos($news_article[0]['full_story'],'"',$img_pos+6) - ($img_pos+27));
			$img = '/template/images/blog/small/'.$img;
			?>
			<meta property="og:image" content='http://lireate.ru<?=$img?>'/>
			<meta property="og:image:type" content='<?=((strpos($img,".jpg")!=false)||(strpos($img,".jpeg")!=false))?"image/jpeg":"image/png"?>' />
			<meta property="og:image:width" content="1220" />
			<meta property="og:image:height" content="640" />
		<?php } ?>
	<?php }else{?>
		<meta property="og:image" content="http://lireate.ru/images/FC.jpg" />
		<meta property="og:image:type" content="image/jpeg" />
		<meta property="og:image:width" content="1220" />
		<meta property="og:image:height" content="640" />
	<?php  }?> 
	<?php endif; ?> 
	

	<!-- Для рекламы в tiktok 23.09.2020 -->
	<script>
          (function() {
            var ta = document.createElement('script'); ta.type = 'text/javascript'; ta.async = true;
            ta.src = 'https://analytics.tiktok.com/i18n/pixel/sdk.js?sdkid=BTLH17DGJ425LCBEKDF0';
            var s = document.getElementsByTagName('script')[0];
            s.parentNode.insertBefore(ta, s);
          })();
    </script>
  
	<meta name='wmail-verification' content='49048b1386238f29d8051d1731c9cc80' />

    
	<meta name="google-site-verification" content="JKhCY_NDFWi_i1dHO2-PA3HuGBbY7D_jjXgz_IOVqS8" />
	<link href='https://fonts.googleapis.com/css?family=Roboto:400,300,300italic,400italic,700,700italic&subset=latin,cyrillic' rel='stylesheet' type='text/css'>
	<link rel="stylesheet" type="text/css" href="{theme_url}/libs/awesome/css/font-awesome.min.css">
	<link rel="stylesheet" type="text/css" href="{theme_url}/libs/bootstrap/css/bootstrap.css">
	<!--<link rel="stylesheet" type="text/css" href="{theme_url}/libs/liMarquee/liMarquee.css">-->
	<?php if(in_array($temp['page'], array('quiz','spasibo_quiz'))){?>
		<link rel='stylesheet' href='{theme_url}/css/quiz/main.css?ver=1.1' type='text/css'>
        <link rel='stylesheet' href='{theme_url}/css/quiz/media.css?ver=1.1' type='text/css'>
        <!-- Marquiz script start -->
        <script src="//script.marquiz.ru/v1.js" type="application/javascript"></script>
        <script>
        document.addEventListener("DOMContentLoaded", function() {
          Marquiz.init({ id: '5b3c722dacbf240042f12b10' });
        });
        </script>
        <!-- Marquiz script end -->
    <?php }else{ ?>
        <link rel="stylesheet" href="{theme_url}/css/styles-carousel.css" type="text/css">
    	<link rel="stylesheet" href="{theme_url}/css/general-style.css" type="text/css">
	    <link rel="stylesheet" type="text/css" href="{theme_url}/css/main.css">
	    <link rel="stylesheet" type="text/css" href="{theme_url}/css/media.css">
	   
	<?php } ?>
	
	
	<link rel="shortcut icon" href="/favicon.svg" type="image/x-icon" />
	
	<!-- Top.Mail.Ru counter -->
    <script type="text/javascript">
    var _tmr = window._tmr || (window._tmr = []);
    _tmr.push({id: "3287696", type: "pageView", start: (new Date()).getTime()});
    (function (d, w, id) {
      if (d.getElementById(id)) return;
      var ts = d.createElement("script"); ts.type = "text/javascript"; ts.async = true; ts.id = id;
      ts.src = "https://top-fwz1.mail.ru/js/code.js";
      var f = function () {var s = d.getElementsByTagName("script")[0]; s.parentNode.insertBefore(ts, s);};
      if (w.opera == "[object Opera]") { d.addEventListener("DOMContentLoaded", f, false); } else { f(); }
    })(document, window, "tmr-code");
    </script>
    <noscript><div><img src="https://top-fwz1.mail.ru/counter?id=3287696;js=na" style="position:absolute;left:-9999px;" alt="Top.Mail.Ru" /></div></noscript>
    <!-- /Top.Mail.Ru counter -->

	
  
	<script
      src="https://code.jquery.com/jquery-1.12.4.min.js"
      integrity="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ="
      crossorigin="anonymous">
	</script>
	
	
	
	<!--<script src="{theme_url}/js/carousel.js" type="text/javascript"></script>-->
    <script src="/template/js/bootstrap.min.js" type="text/javascript"></script>
	<script src="/template/js/image-scale.min.js" type="text/javascript"></script>
	<script src="/template/js/jquery.maskedinput.min.js" type="text/javascript"></script>
	 <!-- SWIPER CAROUSEL-->
	    
        <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css">
	<!--<script src="{theme_url}/libs/liMarquee/jquery.liMarquee.js" type="text/javascript"></script>-->
    <script src="/template/js/main.js?v=20261120fc1" type="text/javascript"></script>
    <script src="/template/js/lireate-utm.js?v=20261117b1" type="text/javascript"></script>
    
	
	
	<link rel='stylesheet' id='sdTheme-fonts-css'  href='//fonts.googleapis.com/css?family=Open+Sans:300,400,700,800&#038;subset=latin,cyrillic' type='text/css' media='all' />
	<!--<script src="//api-maps.yandex.ru/2.0/?load=package.standard&lang=ru-RU" type="text/javascript"></script>-->
	<script type="text/javascript">(window.Image ? (new Image()) : document.createElement('img')).src = 'https://vk.com/rtrg?p=VK-RTRG-164292-2FtSh';</script>
	<script type="text/javascript" src="//vk.com/js/api/openapi.js?146"></script>
		

	<script type="text/javascript">
		VK.init({apiId: 6156329, onlyWidgets: true});
	</script>
	<style type="text/css">.visually-hidden{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0;}
	#personsSlide{
        padding-left: 270px;
        display: none;
        position: fixed;
        width: 100%;
        height: 101%;
        z-index: 3;
        top: 0; 
        align-items: center;
        overflow: auto;
        transition: 0.5s;
        background: rgba(32, 28, 46, 0.7);
        justify-content: space-around;
        opacity:0;}
		html { overflow-x: hidden; background-color: #333740}
		body { overflow-x: hidden; }
	</style>
	<script type="text/javascript">
		$(document).ready(function () {
		    $.ajaxSetup({ cache: false });
		  	var trigger = $('.hamburger'),
		    overlay = $('.overlay'),
		  	isClosed = false;

		    trigger.click(function () {
		    	hamburger_cross();      
		    });

		    function hamburger_cross() {
		    	if (isClosed == true) {          
			        overlay.hide();
			        trigger.removeClass('is-open');
			        trigger.addClass('is-closed');
			        isClosed = false;
		    	} else {   
			        overlay.show();
			        trigger.removeClass('is-closed');
			        trigger.addClass('is-open');
			        isClosed = true;
		    	}
		  	}
		  
		  	$('[data-toggle="offcanvas"]').click(function () {
		        $('#sidebar-wrapper').toggleClass('toggled');
		  	});  
		});

		// обработчик модальной формы
		if($('.js-modal-form').length){
		$('.js-modal-form').on('click', function (e) {
			var tomail = $(this).data('tomail'),
				mtitle = $(this).data('mtitle'),
				msuccess = $(this).data('success'),
				btn = $(this).data('btn'),
				frm;

			if($(this).data('frm')){
				frm = $(this).data('frm').split(',');
			}

			$('.frm-input').addClass('hidden');
			$('.frm-input input, .frm-input textarea').attr('disabled',1);
			frm.forEach(function(a,b){
				$('[data-frm="'+a+'"]').removeClass('hidden');
				$('[data-frm="'+a+'"] input, [data-frm="'+a+'"] textarea').removeAttr('disabled');
			});


			$('.js-ap-tomail').val(tomail);
			$('.js-ap-mtitle').html(mtitle);
			$('.m_success').html(msuccess);
			$('.js-ap-btn').html(btn);
			$('.modal-mail').modal();

			e.preventDefault();
		});
	}
	</script>
    
	<!-- Yandex.Metrika counter -->
<script type="text/javascript" >
   (function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
   m[i].l=1*new Date();k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)})
   (window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym");

   ym(52622734, "init", {
        id:52622734,
        clickmap:true,
        trackLinks:true,
        accurateTrackBounce:true,
        webvisor:true
   });
</script>
<noscript><div><img src="https://mc.yandex.ru/watch/52622734" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
<!-- /Yandex.Metrika counter -->
	

<!-- Ниже две группы кодов от google, один для бесплатной аналитики, а второй ниже для гугл ads(рекламы) -->
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-158802061-1"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'UA-158802061-1');
</script>


<!-- Global site tag (gtag.js) - Google Ads: 868536856 -->
<script async src="https://www.googletagmanager.com/gtag/js?id=AW-868536856"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'AW-868536856');
</script>





<!-- Google Tag Manager 2020
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-PTSQXRX');</script>-->
<!-- End Google Tag Manager -->
	
	
	
	
    
    
    
    
	<!--////////    2020  //////////////////////////// -->
	
	
	<!-- Event snippet for Заявка с сайта conversion page -->
    <script>
      gtag('event', 'conversion', {'send_to': 'AW-868536856/MKlOCN6ftrYBEJikk54D'});
    </script>
    <!-- Стили отдельной страницы -->
    <?php 
        /*if(file_exists('./template/page/'.$temp['page'].'/index.php')){
            echo '<link rel="stylesheet" type="text/css" href="/template/css/css/main.css" >';} */
    ?>
    <link rel="stylesheet" type="text/css" href="/template/css/css/main.css" >
    <?php
    $_lireate_page_css = __DIR__ . '/page/' . $temp['page'] . '/style.css';
    if (is_file($_lireate_page_css)) : ?>
    <link rel="stylesheet" type="text/css" href="/template/page/<?php echo $temp['page'];?>/style.css">
    <?php endif; ?>
	<?php /* lireate_design_mktu_lu_head_v1 */ $_lx_mktu_uri = (isset($_SERVER['REQUEST_URI']) && preg_match('#^/mktu(?:[/?]|$)#', $_SERVER['REQUEST_URI'])); if ($_lx_mktu_uri): ?>
	<link rel="preconnect" href="https://fonts.googleapis.com" />
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
	<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" />
	<!--lireate-performance-css-v1:PGxpbmsgcmVsPSJzdHlsZXNoZWV0IiBocmVmPSIvdGVtcGxhdGUvY3NzL2xpcmVhdGUtY21zLXBhZ2UtZGFyay5jc3M/dj0yMDI2MTExMWRlc2lnbjQiIC8+Cgk8bGluayByZWw9InN0eWxlc2hlZXQiIGhyZWY9Ii90ZW1wbGF0ZS9jc3MvbGlyZWF0ZS1jbXMtbHUuY3NzP3Y9MjAyNjExMTFsdTEwIiAvPgoJPGxpbmsgcmVsPSJzdHlsZXNoZWV0IiBocmVmPSIvdGVtcGxhdGUvY3NzL2xpcmVhdGUtbHUtdmlld3BvcnQuY3NzP3Y9MjAyNjExMTF2cDYiIC8+Cgk8bGluayByZWw9InN0eWxlc2hlZXQiIGhyZWY9Ii90ZW1wbGF0ZS9jc3MvbGlyZWF0ZS1ta3R1LWx1LmNzcz92PTIwMjYxMTExbWt0dTQiIC8+--><link rel="stylesheet" href="/template/css/lireate-bundle-9bab63cb98391360.css" data-lireate-css-bundle="1">
	<?php endif; ?>
	<?php /* lireate_design_cms_hero_assets_v1 */ if (in_array($temp['page'], array('patent','patent_prom_obr'), true)): ?>
	<link rel="stylesheet" href="/template/css/lireate-home-hero.css?v=20261120fc3" />
	<script defer src="/template/js/lireate-home-hero.js?v=20261111hero2"></script>
	<?php endif; ?>
	<?php /* lireate-cms-dark-head-v1 */ $_lx_cms_dark = /* lireate_design_cms_dark_pages_v2 */ array('home','patent','patent_prom_obr','mktu','mktu1','reviews','portfolio','spasibo','uslugi','avtor','team','partners','kontaktyi','o_kompanii','contacts'); if (in_array($temp['page'], $_lx_cms_dark, true)): ?>
	<link rel="preconnect" href="https://fonts.googleapis.com" />
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
	<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" />
	<link rel="stylesheet" href="/template/css/lireate-cms-page-dark.css?v=20261111design4" />
	<?php /* lireate_design_cms_lu_assets_v1 */ if (in_array($temp['page'], array('patent','patent_prom_obr','mktu','mktu1'), true)): ?>
	<!--lireate-performance-css-v1:PGxpbmsgcmVsPSJzdHlsZXNoZWV0IiBocmVmPSIvdGVtcGxhdGUvY3NzL2xpcmVhdGUtY21zLWx1LmNzcz92PTIwMjYxMTExbHUxMCIgLz4KCTxsaW5rIHJlbD0ic3R5bGVzaGVldCIgaHJlZj0iL3RlbXBsYXRlL2Nzcy9saXJlYXRlLWx1LXZpZXdwb3J0LmNzcz92PTIwMjYxMTExdnA2IiAvPg==--><link rel="stylesheet" href="/template/css/lireate-bundle-4e464923a3762336.css" data-lireate-css-bundle="1">
	<?php endif; ?>
	<?php if ($temp['page'] === 'home'): ?>
	<link rel="stylesheet" href="/template/css/lireate-home-dark.css?v=20261120fc4" />
	<?php /* lireate_design_home_hero_assets_v1 */ if ($temp['page'] === 'home'): ?>
	<!--lireate-performance-css-v1:PGxpbmsgcmVsPSJzdHlsZXNoZWV0IiBocmVmPSIvdGVtcGxhdGUvcGFnZS91c2x1Z2kvbGlyZWF0ZS11c2x1Z2kuY3NzP3Y9MjAyNjExMTFsdWhvbWUiIC8+Cgk8bGluayByZWw9InN0eWxlc2hlZXQiIGhyZWY9Ii90ZW1wbGF0ZS9wYWdlL2hvbWUvbGlyZWF0ZS1ob21lLWx1LmNzcz92PTIwMjYxMTExbHU5IiAvPgoJPGxpbmsgcmVsPSJzdHlsZXNoZWV0IiBocmVmPSIvdGVtcGxhdGUvY3NzL2xpcmVhdGUtbHUtdmlld3BvcnQuY3NzP3Y9MjAyNjExMTF2cDYiIC8+Cgk8bGluayByZWw9InN0eWxlc2hlZXQiIGhyZWY9Ii90ZW1wbGF0ZS9jc3MvbGlyZWF0ZS1ob21lLW1vZGFsLWx1LmNzcz92PTIwMjYxMTIwZmMyIiAvPg==--><link rel="stylesheet" href="/template/css/lireate-bundle-23b8c2d42f8a78f8.css" data-lireate-css-bundle="1">
	<script defer src="/template/js/lireate-home-modal-lu.js?v=20261111mod2"></script>
	<link rel="stylesheet" href="/template/css/lireate-home-hero.css?v=20261120fc3" />
	<script defer src="/template/js/lireate-home-hero.js?v=20261111hero2"></script>
	<script>document.documentElement.classList.add("lireate-home-lu-html");if(document.body){document.body.classList.add("lireate-home-lu-body");}</script>
	<?php endif; ?>
	<?php endif; ?>
	<?php endif; ?>
	<?php if (!empty($temp['news_id'])): ?>
	<!--lireate-performance-css-v1:PGxpbmsgcmVsPSJzdHlsZXNoZWV0IiBocmVmPSIvdGVtcGxhdGUvY3NzL2xpcmVhdGUtbGVnYWN5LWFydGljbGUtbHUuY3NzP3Y9MjAyNjExMTFhcnQyIiAvPgoJPGxpbmsgcmVsPSJzdHlsZXNoZWV0IiBocmVmPSIvdGVtcGxhdGUvY3NzL2xpcmVhdGUtbGVnYWN5LWNtcy1sdS5jc3M/dj0yMDI2MTExMWxlZzEiIC8+--><link rel="stylesheet" href="/template/css/lireate-bundle-fba08515cc2645cd.css" data-lireate-css-bundle="1">
	<?php endif; ?>
	<?php /* lireate_design_static_lu_v1 head */ if (in_array($temp['page'], array('reviews','portfolio','spasibo','uslugi','avtor','team','partners','kontaktyi','o_kompanii','contacts'), true)): ?>
	<!--lireate-performance-css-v1:PGxpbmsgcmVsPSJzdHlsZXNoZWV0IiBocmVmPSIvdGVtcGxhdGUvY3NzL2xpcmVhdGUtbHUtdmlld3BvcnQuY3NzP3Y9MjAyNjExMTF2cDYiIC8+Cgk8bGluayByZWw9InN0eWxlc2hlZXQiIGhyZWY9Ii90ZW1wbGF0ZS9jc3MvbGlyZWF0ZS1zdGF0aWMtcGFnZXMtbHUuY3NzP3Y9MjAyNjExMTFzdGF0aWMzIiAvPg==--><link rel="stylesheet" href="/template/css/lireate-bundle-f80a3f772af93748.css" data-lireate-css-bundle="1">
	<script defer src="/template/js/lireate-static-hero-lu.js?v=20261111static3"></script>
	<link rel="stylesheet" href="/template/css/lireate-home-modal-lu.css?v=20261120fc2" />
	<script defer src="/template/js/lireate-home-modal-lu.js?v=20261111mod2"></script>
	<?php endif; ?>
	<?php /* audit07 C4 cases wave 1: /portfolio/<slug>/ uses the /portfolio dark styles */ if (strpos((string) $temp['page'], 'portfolio/') === 0): ?>
	<link rel="preconnect" href="https://fonts.googleapis.com" />
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
	<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" />
	<!--lireate-performance-css-v1:PGxpbmsgcmVsPSJzdHlsZXNoZWV0IiBocmVmPSIvdGVtcGxhdGUvY3NzL2xpcmVhdGUtY21zLXBhZ2UtZGFyay5jc3M/dj0yMDI2MTExMWRlc2lnbjQiIC8+Cgk8bGluayByZWw9InN0eWxlc2hlZXQiIGhyZWY9Ii90ZW1wbGF0ZS9jc3MvbGlyZWF0ZS1sdS12aWV3cG9ydC5jc3M/dj0yMDI2MTExMXZwNiIgLz4KCTxsaW5rIHJlbD0ic3R5bGVzaGVldCIgaHJlZj0iL3RlbXBsYXRlL2Nzcy9saXJlYXRlLXN0YXRpYy1wYWdlcy1sdS5jc3M/dj0yMDI2MTExMXN0YXRpYzMiIC8+Cgk8bGluayByZWw9InN0eWxlc2hlZXQiIGhyZWY9Ii90ZW1wbGF0ZS9jc3MvbGlyZWF0ZS1ob21lLW1vZGFsLWx1LmNzcz92PTIwMjYxMTIwZmMyIiAvPg==--><link rel="stylesheet" href="/template/css/lireate-bundle-4df28301533a2bf7.css" data-lireate-css-bundle="1">
	<script defer src="/template/js/lireate-home-modal-lu.js?v=20261111mod2"></script>
	<?php endif; ?>
	<?php /* lireate_design_service_mobile_dock_v2 head */ $_lx_svc_dock_pg = array('patent','patent_prom_obr','mktu','mktu1','uslugi','poisk_tovarnyh_znakov','ekspertiza_tovarnogo_znaka','mezhdunarodnaja_registracija_tovarnogo_znaka','registracija_torgovyh_marok_i_logotipov');
	$_lx_svc_dock = in_array($temp['page'], $_lx_svc_dock_pg, true);
	if (!$_lx_svc_dock) {
	  $_lx_svc_ru = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';
	  $_lx_svc_dock = (bool) preg_match('#^/(?:mktu1?|poisk_tovarnyh_znakov|ekspertiza_tovarnogo_znaka|mezhdunarodnaja_registracija_tovarnogo_znaka|registracija_torgovyh_marok_i_logotipov)(?:[/?]|$)#', $_lx_svc_ru);
	}
	if ($_lx_svc_dock) {
	  $_lx_svc_ru = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';
	  if (preg_match('#^/(?:findtz|registraciya_tovarnogo_znaka(?:_s_oplatoy_za_rezultat|_v_kitae)?|prodlenie_tovarnogo_znaka|vozrazhenie_protiv_registracii_tovarnogo_znaka|zashchita_avtorskih_prav|dogovory|registraciya_programm_evm)(?:[/?]|$)#', $_lx_svc_ru)) {
	    $_lx_svc_dock = false;
	  }
	}
	if ($_lx_svc_dock): ?>
	<link rel="stylesheet" href="/template/css/lireate-service-mobile-bar.css?v=20261111svc3" />
	<script defer src="/template/js/lireate-service-mobile-bar.js?v=20261111svc3"></script>
	<?php endif; ?>
	<?php /* lireate_design_legacy_cms_services_v1 head */ $_lx_leg_svc_ru = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';
	if (preg_match('#^/(?:mktu1?|poisk_tovarnyh_znakov|ekspertiza_tovarnogo_znaka|mezhdunarodnaja_registracija_tovarnogo_znaka|registracija_torgovyh_marok_i_logotipov)(?:[/?]|$)#', $_lx_leg_svc_ru)): ?>
	<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" />
	<!--lireate-performance-css-v1:PGxpbmsgcmVsPSJzdHlsZXNoZWV0IiBocmVmPSIvdGVtcGxhdGUvY3NzL2xpcmVhdGUtY21zLXBhZ2UtZGFyay5jc3M/dj0yMDI2MTExMWRlc2lnbjMiIC8+Cgk8bGluayByZWw9InN0eWxlc2hlZXQiIGhyZWY9Ii90ZW1wbGF0ZS9jc3MvbGlyZWF0ZS1sZWdhY3ktY21zLWx1LmNzcz92PTIwMjYxMTExbGVnMiIgLz4KCTxsaW5rIHJlbD0ic3R5bGVzaGVldCIgaHJlZj0iL3RlbXBsYXRlL2Nzcy9saXJlYXRlLWx1LXZpZXdwb3J0LmNzcz92PTIwMjYxMTExdnA2IiAvPgoJPGxpbmsgcmVsPSJzdHlsZXNoZWV0IiBocmVmPSIvdGVtcGxhdGUvY3NzL2xpcmVhdGUtaG9tZS1tb2RhbC1sdS5jc3M/dj0yMDI2MTEyMGZjMiIgLz4=--><link rel="stylesheet" href="/template/css/lireate-bundle-b530c5cab0151d67.css" data-lireate-css-bundle="1">
	<script defer src="/template/js/lireate-home-modal-lu.js?v=20261111mod2"></script>
	<?php endif; ?>

<!-- seo-perf-20261120-v1 -->
<meta name="lireate-performance-patch" content="seo-perf-20261120-v1">
<link rel="stylesheet" href="/template/css/lireate-performance.css?v=seo-perf-20261120-v1">
<script defer src="/template/js/lireate-performance.js?v=seo-perf-20261120-v1"></script>
<?php /* lireate_home_unslop_20261122u1 */ $_lireate_home_unslop_path = isset($_SERVER['REQUEST_URI']) ? parse_url((string) $_SERVER['REQUEST_URI'], PHP_URL_PATH) : null; if ($_lireate_home_unslop_path === '/' || ($_lireate_home_unslop_path === null && isset($temp['page']) && $temp['page'] === 'home')) { ?>
<link rel="stylesheet" href="/template/css/lireate-home-unslop.css?v=20261122u1">
<?php } ?>
<?php /* lireate_home_v2_20261125 head */ $_lx_home_v2 = (isset($temp['page']) && $temp['page'] === 'home') && (defined('LIREATE_HOME_V2_PREVIEW') || is_file(dirname(__FILE__) . '/page/home/lireate-home-v2.on')); if ($_lx_home_v2) { $_lx_home_v2_base = defined('LIREATE_HOME_V2_ASSET_BASE') ? LIREATE_HOME_V2_ASSET_BASE : '/template/page/home'; ?>
<link rel="stylesheet" href="<?php echo htmlspecialchars($_lx_home_v2_base, ENT_QUOTES, 'UTF-8'); ?>/lireate-home-v2.css?v=20261125v1">
<?php } ?>
</head>




<body class="cms-default<?php if($temp['page']=='docs') echo ' lireate-docs-body';?><?php if($temp['page']=='kontaktyi') echo ' lireate-contacts-body';?><?php if($temp['page']=='contacts') echo ' lireate-contacts-body';?><?php if($temp['page']=='partners') echo ' lireate-partners-body';?><?php if($temp['page']=='o_kompanii') echo ' lireate-about-body';?><?php if($temp['page']=='team') echo ' lireate-team-body'; if($temp['page']=='uslugi') echo ' lireate-uslugi-body'; /* lireate_design_static_lu_v1 */ $_lx_static = array('reviews','portfolio','spasibo','uslugi'); if (in_array($temp['page'], $_lx_static, true)) echo ' lireate-static-lu'; if ($temp['page']==='reviews') echo ' lireate-reviews-body'; if ($temp['page']==='portfolio') echo ' lireate-portfolio-body'; if ($temp['page']==='spasibo') echo ' lireate-spasibo-body'; $_lx_ru = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : ''; if (preg_match('#^/reviews(?:[/?]|$)#', $_lx_ru)) { echo ' lireate-cms-page-dark lireate-static-lu lireate-reviews-body'; } if (preg_match('#^/portfolio(?:[/?]|$)#', $_lx_ru)) { echo ' lireate-cms-page-dark lireate-static-lu lireate-portfolio-body'; } if (preg_match('#^/spasibo(?:[/?]|$)#', $_lx_ru)) { echo ' lireate-cms-page-dark lireate-static-lu lireate-spasibo-body'; } /* lireate_design_service_mobile_dock_v2 body */ $_lx_svc_dock_pg = array('patent','patent_prom_obr','mktu','mktu1','uslugi','poisk_tovarnyh_znakov','ekspertiza_tovarnogo_znaka','mezhdunarodnaja_registracija_tovarnogo_znaka','registracija_torgovyh_marok_i_logotipov');  if (in_array($temp['page'], $_lx_svc_dock_pg, true)) echo ' lireate-svc-body lireate-service-mobile-dock';  else {    $_lx_svc_ru = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';    if (preg_match('#^/(?:mktu1?|poisk_tovarnyh_znakov|ekspertiza_tovarnogo_znaka|mezhdunarodnaja_registracija_tovarnogo_znaka|registracija_torgovyh_marok_i_logotipov)(?:[/?]|$)#', $_lx_svc_ru) && !preg_match('#^/(?:findtz|registraciya_tovarnogo_znaka(?:_s_oplatoy_za_rezultat|_v_kitae)?|prodlenie_tovarnogo_znaka|vozrazhenie_protiv_registracii_tovarnogo_znaka|zashchita_avtorskih_prav|dogovory|registraciya_programm_evm)(?:[/?]|$)#', $_lx_svc_ru)) {      echo ' lireate-svc-body lireate-service-mobile-dock';    }  } /* lireate_design_static_lu_v1 */ $_lx_static = array('reviews','portfolio','spasibo','uslugi'); if (in_array($temp['page'], $_lx_static, true)) echo ' lireate-static-lu'; if ($temp['page']==='reviews') echo ' lireate-reviews-body'; if ($temp['page']==='portfolio') echo ' lireate-portfolio-body'; if ($temp['page']==='spasibo') echo ' lireate-spasibo-body'; /* lireate_design_news_lu_v1 */ if (!empty($temp['news_id'])) echo ' lireate-legacy-article-lu lireate-cms-page-dark'; /* lireate_design_uri_query_safe_v1 */ /* lireate_cms_dark_body_v1 */ /* lireate_design_cms_dark_pages_v2 body */ if (in_array($temp['page'], array('home','patent','patent_prom_obr','mktu','mktu1','reviews','portfolio','spasibo','uslugi','avtor','team','partners','kontaktyi','o_kompanii','contacts'), true)) echo ' lireate-cms-page-dark';  /* lireate_design_mktu_lu_body_v1 */ $_lx_mktu_uri = (isset($_SERVER['REQUEST_URI']) && preg_match('#^/mktu(?:[/?]|$)#', $_SERVER['REQUEST_URI'])); if ($_lx_mktu_uri) echo ' lireate-cms-page-dark lireate-mktu-lu-body'; /* lireate_design_legacy_cms_services_v1 body */ $_lx_leg_svc_ru = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : ''; if (preg_match('#^/(?:mktu1?|poisk_tovarnyh_znakov|ekspertiza_tovarnogo_znaka|mezhdunarodnaja_registracija_tovarnogo_znaka|registracija_torgovyh_marok_i_logotipov)(?:[/?]|$)#', $_lx_leg_svc_ru)) echo ' lireate-cms-page-dark lireate-legacy-cms-lu'; if ($temp['page']==='home') echo empty($_lx_home_v2) ? ' lireate-home-dark' : ' lireate-home-v2'; /* lireate_home_v2_20261125 body */?>">
    <?php 
    
        /* lireate_hide_pagekey_v1 */
        include ('./template/parts/online.php');
    ?>
    <link rel="stylesheet" href="/template/css/lireate-ux-fixes.css?v=20261111design4" />
    <link rel="stylesheet" href="/template/css/lireate-audit-cls.css?v=20261111audit?v=20261110ux">
    <link rel="stylesheet" href="/template/css/lireate-menu-redesign.css?v=20261123m2" /><?php /* lireate-menu-redesign v1 */ ?>
    <link rel="stylesheet" href="/template/css/lireate-modal-contrast.css?v=20261123c1" /><?php /* lireate-modal-contrast v1 */ ?>
    <script src="/template/js/lireate-floating-widget-ux.js?v=20261111design4" defer></script>
    <script src="/template/js/lireate-service-metrika-goals.js?v=20261117a07" defer></script><?php /* audit07 A4: goals on all CMS pages */ ?>
    <?php
    ?>
    
    
<!-- Google Tag Manager (noscript) 2020
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-PTSQXRX"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>-->
<!-- End Google Tag Manager (noscript) -->

    <?php if($temp['page']=='request'):?>
        
<?php endif?>	
    
    
    <!-- ROIStat -->
    <script>
    (function(w, d, s, h, id) {
        w.roistatProjectId = id; w.roistatHost = h;
        var p = d.location.protocol == "https:" ? "https://" : "http://";
        var u = /^.*roistat_visit=[^;]+(.*)?$/.test(d.cookie) ? "/dist/module.js" : "/api/site/1.0/"+id+"/init";
        var js = d.createElement(s); js.charset="UTF-8"; js.async = 1; js.src = p+h+u; var js2 = d.getElementsByTagName(s)[0]; js2.parentNode.insertBefore(js, js2);
    })(window, document, 'script', 'cloud.roistat.com', '3786a2c47e98c308760ee50346cdccbe');
    </script>
	
	<?php
				$server = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'lireate.ru';
		$domain = 'lireate.ru';
		$cities = ['lib'=>'Казань','msk'=>'Москва','rst'=>'Ростов на Дону','nov'=>'Нижний Новгород','kra'=>'Краснодар','vla'=>'Владивосток','sam'=>'Самара'];
		// LIREATE 2026-11-17 audit05: офисов в регионах нет. Город всегда Казань, URL только https://lireate.ru.
		// Редирект по cookie city= на поддомен отключён и не должен включаться.
		$city = 'lib';
		$cook_city = 'lib';
		setcookie('city', 'lib', time() + (86400 * 30), '/', 'lireate.ru');
		$cook = false;
		$bgcity = '';
		$cit_in = 'https://lireate.ru';
	?>
	
	
	
	
    <?php if(!in_array($temp['page'], array('quiz','spasibo_quiz'))){?>
	<div class="modal modal-mail fade popup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" >
	    
	    <div class="" style=" width:100; height:100%; display:flex; align-items:center; justify-content: center;">
		<div class="modal-dialog" role="document" style=" border-radius:0px;" >
			<div class="modal-content modal-height" style="border-radius:0px; padding:15px;">
				<div class="modal-header" style=" border-radius: 0px 0px 0 0; border-bottom:none;">
					<button class="close" type="button" data-dismiss="modal" aria-label="Close" style="top: 5px;">
				    
					    <?php if($temp['page']=='test'){?>
					        
					    <?php } else {?>
					        
					    <?php } ?>
					    <span aria-hidden="true" style="font-size:30px;">&times;</span></button>
					<div>
						<p class="js-ap-mtitle m_title" style="color:black; font-family: 'Proxima_Nova', Arial, sans-serif; font-size: 20px; line-height: 1.1;"></p>
					</div>
				</div>
				<div class="modal-body mbg apmodal" style="padding: 15px;">
					<div class="apmodalform">
						<form class="form-unit pb30 ajax_form" method="POST" action="https://lireate.ru/mail.php">
							<div class="mail_to">
								<input type="hidden" name="mail_token" value="<?=md5(time()*5.15);?>">
								<input type="hidden" name="fuck_spamers" value="<?=md5(date('m.Y'));?>">
								<input class="js-ap-tomail" type="hidden" name="tomail" value="">
								<input type="hidden" name="mail" value="mailorder">
								<input type="hidden" name="form-type" value="order">
								<div class="pb30">
									<div data-frm="name" class="form-group form-unit-group vError frm-input input-group">
									    <img src="{theme_url}/img/reg-tz-main/icon/name.svg" alt="name-icon">
										<input name="name" type="text" class="" placeholder="Введите имя">
									</div>
									
									
					        
									<div data-frm="tel" class="form-group vError frm-input input-group">
									    <img src="https://lireate.ru/template/img/reg-tz-main/icon/phone.svg" alt="phone-icon">
										<input name="tel" type="tel" class="" placeholder="Введите телефон">
									</div>
									
									<div data-frm="brand" class="form-group form-unit-group vError frm-input" style="display: none;">
										<input name="brand" type="text" class="form-control form-unit-control ap-label-js" placeholder="Название товарного знака" hidden>
									</div>
								</div>
								<button type="submit" class=" btn-danger btn-blue-ap btn-popup js-ap-btn btn1"></button><br>
							<p style="font-size: 12px; margin-top: 10px; text-align: justify; line-height: 14px;">
                              <input type="checkbox" checked required style="vertical-align: middle; margin: 0 5px 0 0;">
                              Нажимая на кнопку, вы даете 
                              <a href="/private_data" target="_blank">согласие на обработку персональных данных</a> 
                              и соглашаетесь c 
                              <a href="/politika_konfidencialnosti" target="_blank">политикой конфиденциальности</a>
                            </p>
							</div>
							<div class="s_mail_to modal_success">
								<i class="ap-tick"></i>
								<p class="m_success">Мы перезвоним Вам в ближайшее время</p>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
		</div>
	</div>
	
	<style>
	.input-group{
        width:100%;
        margin-bottom: 18px;
        position: relative; z-index: 1;
        display: table;
        border-collapse: separate;}
    
    
    .input-group img {
        position: absolute;
        z-index: 2;
        left: 15px;
        top: 9px;
        width: 30px;
        height: 30px;
        pointer-events: none;}
        
        
    .input-group input{
        display: inline-block;
        font-family: "Proxima Nova Rg", Arial, sans-serif;
        font-size: 16px;
        line-height: 1.3;
        -webkit-appearance: none;
        padding: 14px 20px 14px 60px;
        overflow: visible;
        background: #f3f4fd;
        /* color: #0115a6; */
        border-radius: 27px;
        -webkit-transition: background 0.3s ease;
        -o-transition: background 0.3s ease;
        transition: background 0.3s ease;
        border: none;
        -webkit-transform: translate3d(0, 0, 0);
        transform: translate3d(0, 0, 0);
        width: 100%;
    }
    .input-group input:focus{
        background: #d9dcf9;
            outline: none;
    }
    
    .input-group input::-webkit-input-placeholder {color:blue; opacity:1; font-size:14px;}/* webkit */
    .input-group input::-moz-placeholder          {color:blue; opacity:1; font-size:14px;}/* Firefox 19+ */
    .input-group input:-moz-placeholder           {color:blue; opacity:1; font-size:14px;}/* Firefox 18- */
    .input-group input:-ms-input-placeholder      {color:blue; opacity:1; font-size:14px;}/* IE */
    .input-group input:focus::-webkit-input-placeholder {opacity:0;}/* webkit */
    .input-group input:focus::-moz-placeholder          {opacity:0;}/* Firefox 19+ */
    .input-group input:focus:-moz-placeholder           {opacity:0;}/* Firefox 18- */
    .input-group input:focus:-ms-input-placeholder      {opacity:0;}/* IE */
	
	.checkbox__text{
    	position: relative;
        display: block;
        color: #929292;
        font-size: 12px;
        line-height: 1.4;
        padding-left: 20px;
	    margin-top: 15px;
        display: block;
        text-align: left;
        position: relative;}
	
        
	.checkbox__text:before {
      background: red;}
	   
	 .checkbox input{  
	    opacity: 0;
        visibility: hidden;
        position: absolute;
        left: 0;
        top: 0;}
	     
	 
	 
	.input-group input:hover{
	    background: #e6e8fb;}
	 
	 
	
        
	.btn1 {
        width:100%;
        font-family: "Proxima_Nova", Arial, sans-serif;
        display: inline-block;
        vertical-align: top;
        position: relative;
        padding: 14px 30px;
        font-size: 18px;
        line-height: 1.3;
        text-decoration: none;
        text-align: center;
        cursor: pointer;
        -webkit-transition: all 0.3s ease;
        -o-transition: all 0.3s ease;
        transition: all 0.3s ease;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
        <?php if($temp['page']!='test'){?>
            background: #d92a3d;
            text-shadow: 0 1px 1px rgb(142, 37, 48);
        <?php } else { ?>
            background: #0122fe;
            text-shadow: 0 1px 1px #03338b;
        <?php } ?>
        -webkit-box-shadow: 0 13px 27px 0 rgba(0, 0, 0, 0.25);
        box-shadow: 0 13px 27px 0 rgba(0, 0, 0, 0.25);
        border: none;
        border-radius: 26px;
        white-space: nowrap;
        color: #FFF;}
    

    .btn1:hover {
        <?php if($temp['page']!='test'){?>
            background: #d92a3d;
        <?php } else { ?>
            background: #344efe;
        <?php } ?>
        -webkit-box-shadow: 0 13px 27px 0 rgba(0, 0, 0, 0.35);
        box-shadow: 0 13px 27px 0 rgba(0, 0, 0, 0.35);}
    


	</style>
	<div class="wrapper-ap">

		<style type="text/css">
			.hamburger {
				position: fixed;
				top: 12px;
				left: 0px;
				z-index: 999;
				display: block;
				width: 38px;
				height: 38px;
				padding: 3px;
				margin-left: 10px;
				/*background: transparent;*/
				border: none;
				background-color: rgba(51, 55, 64, 0.14);
    			border-radius: 3px;
				-webkit-transition: left 0.5s ease;
			    -moz-transition: left 0.5s ease;
			    -o-transition: left 0.5s ease;
			    transition: left 0.5s ease;
			}
			.hamburger.is-open{
				left: 266px;
			}
			.hamburger:hover,
			.hamburger:focus,
			.hamburger:active {
				outline: none;
			}
			.hamburger.is-closed:before {
				content: '';
				display: block;
				width: 100px;
				font-size: 14px;
				color: #fff;
				line-height: 32px;
				text-align: center;
				opacity: 0;
				-webkit-transform: translate3d(0,0,0);
				-webkit-transition: all .35s ease-in-out;
			}
			/*.hamburger.is-closed:hover:before {
				opacity: 1;
				display: block;
				-webkit-transform: translate3d(-100px,0,0);
				-webkit-transition: all .35s ease-in-out;
			}*/

			.hamburger.is-closed .hamb-top,
			.hamburger.is-closed .hamb-middle,
			.hamburger.is-closed .hamb-bottom,
			.hamburger.is-open .hamb-top,
			.hamburger.is-open .hamb-middle,
			.hamburger.is-open .hamb-bottom {
				position: absolute;
				left: 2px;
				height: 4px;
				width: 90%;
			}
			.hamburger.is-closed .hamb-top,
			.hamburger.is-closed .hamb-middle,
			.hamburger.is-closed .hamb-bottom {
				background-color: #fff;
			}
			.hamburger.is-closed .hamb-top { 
				top: 5px; 
				-webkit-transition: all .35s ease-in-out;
			}
			.hamburger.is-closed .hamb-middle {
				top: 50%;
				margin-top: -2px;
			}
			.hamburger.is-closed .hamb-bottom {
				bottom: 5px;  
				-webkit-transition: all .35s ease-in-out;
			}

			/*.hamburger.is-closed:hover .hamb-top {
				top: 0;
				-webkit-transition: all .35s ease-in-out;
			}
			.hamburger.is-closed:hover .hamb-bottom {
				bottom: 0;
				-webkit-transition: all .35s ease-in-out;
			}*/
			.hamburger.is-open .hamb-top,
			.hamburger.is-open .hamb-middle,
			.hamburger.is-open .hamb-bottom {
				background-color: #d92a3d;
			}
			.hamburger.is-open .hamb-top,
			.hamburger.is-open .hamb-bottom {
				top: 50%;
				margin-top: -2px;  
			}
			.hamburger.is-open .hamb-top { 
				-webkit-transform: rotate(45deg);
				-webkit-transition: -webkit-transform .2s cubic-bezier(.73,1,.28,.08);
			}
			.hamburger.is-open .hamb-middle { display: none; }
			.hamburger.is-open .hamb-bottom {
				-webkit-transform: rotate(-45deg);
				-webkit-transition: -webkit-transform .2s cubic-bezier(.73,1,.28,.08);
			}
			.hamburger.is-open:before {
				content: '';
				display: block;
				width: 100px;
				font-size: 14px;
				color: #fff;
				line-height: 32px;
				text-align: center;
				opacity: 0;
				-webkit-transform: translate3d(0,0,0);
				-webkit-transition: all .35s ease-in-out;
			}
			/*.hamburger.is-open:hover:before {
				opacity: 1;
				display: block;
				-webkit-transform: translate3d(-100px,0,0);
				-webkit-transition: all .35s ease-in-out;
			}*/

			#sidebar-wrapper {
			    z-index: 1000;
			    left: 270px;
			    width: 270px;
			    height: 100%;
			    margin-left: -270px;
			    overflow-y: auto;
			    overflow-x: hidden;
			    background: #333740;
			    box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.5);
			    font: 16px/20px "Open Sans",sans-serif;
			    -webkit-transition: all 0.5s ease;
			    -moz-transition: all 0.5s ease;
			    -o-transition: all 0.5s ease;
			    transition: all 0.5s ease;
			}

			#sidebar-wrapper::-webkit-scrollbar {
			  	display: none;
			}

			#wrapper-ap.toggled #sidebar-wrapper {
			    width: 220px;
			}

			#page-content-wrapper {
			    width: 100%;
			    padding-top: 70px;
			}

			#wrapper.toggled #page-content-wrapper {
			    position: absolute;
			    margin-right: -220px;
			}
			
			BODY{
				background: #f5f5f5;
				padding-right: 0px !important;
			}

			.content{
				color: #5c6065;
			}

			.sidebar-nav {
			    position: absolute;
			    top: 0;
			    width: 100%;
			    margin: 0;
			    padding: 0;
			    list-style: none;
			}

			.sidebar-nav li {
			    position: relative; 
			    font: 16px/20px "Open Sans",sans-serif;
			    line-height: 20px;
			    display: inline-block;
			    width: 100%;
			}

			.nav .open > a, .nav > li > a:focus, .nav .open a:focus {
			    background-color: transparent;
			}

			.nav .open a:hover {
			    background-color: #d82a3d; /*#4ea050;*/
			    color: #fff;
			}

			.dropdown-menu > li > a:focus, .dropdown-menu > a:focus{
				background-color: transparent !important;	
			}

			.sidebar-nav li a:hover {
			    background-color: #d82a3d; /*#4ea050;*/
			    color: #fff;
			}

			.sidebar-nav li a {
			    display: block;
			    color: #ddd;
			    text-decoration: none;
			    padding: 10px 15px 10px 20px;
			    font-size: 15px;
			    -webkit-transition: background-color 0.3s ease-out;
			    -moz-transition: background-color 0.3s ease-out;
			    -o-transition: background-color 0.3s ease-out;
			    transition: background-color 0.3s ease-out;
			}

			/*.sidebar-nav li a:hover,
			.sidebar-nav li a:active,
			.sidebar-nav li a:focus,
			.sidebar-nav li.open a:hover,
			.sidebar-nav li.open a:active,
			.sidebar-nav li.open a:focus{
			    color: #fff;
			    text-decoration: none;
			    background-color: transparent;
			}*/

			.sidebar-nav > .sidebar-brand {
			    height: 90px;
			    font-size: 20px;
			    line-height: 44px;
			}

			.sidebar-nav > .sidebar-brand a:hover {
				background-color: transparent !important;
			}
			.sidebar-nav .dropdown-menu {
			    position: relative;
			    width: 100%;
			    padding: 0;
			    margin: 0;
			    border-radius: 0;
			    border: none;
			    background-color: rgb(90, 45, 50);/*#333740;*/
			    box-shadow: none;
			}

			.dropdown-menu li > a {
				text-indent: 0px;
				font-size: 12px;
    			line-height: 9px;
    			font-weight: lighter;
			}

			.sidebar_social {
			    margin: 30px 0px 0;
			    padding: 18px 0;
			    border-top: 1px solid #282b32;
			    border-bottom: 1px solid #282b32;
			    height:60px
			}

			.sidebar_social_item:hover {
				background-color: transparent !important;
			}

			.sidebar_social_item {
			    float: left;
			    width: 24px;
			    height: 24px;
			    margin-left: 8px;
			    background: url('{theme_url}/images/sprite_librend_.png');
			    padding: 0 !important;
			}

			.sidebar_social_item:first-child {
			    margin-left: 0;
			}

			.sidebar_social_item-fb {
			    background-position: 0 0;
			}

			.sidebar_social_item-twi {
			    background-position: -48px 0;
			}

			.sidebar_social_item-vk {
			    background-position: -72px 0;
			}

			.sidebar_social_item-inst {
			    background-position: -96px 0;
			}

			.sidebar_social_item-fb:hover {
			    background-position: 0 -24px;
			}

			.sidebar_social_item-twi:hover {
			    background-position: -48px -24px;
			}

			.sidebar_social_item-vk:hover {
			    background-position: -72px -24px;
			}

			.sidebar_social_item-inst:hover {
			    background-position: -96px -24px;
			}

			.sidebar_callback .btn {
			    padding-top: 12px;
			    padding-bottom: 11px;
			    box-shadow: inset 0 0 1px #a3a5ac;
			    background: 0 0;
			    color: #ddd;
			    width: 100%;
			    border: 1px solid transparent;
			    -webkit-transition: all 0.3s ease-out;
			    -moz-transition: all 0.3s ease-out;
			    -o-transition: all 0.3s ease-out;
			    transition: all 0.3s ease-out;
			}

			.sidebar_callback .btn:hover {
			    box-shadow: none;
			    background: #d92a3d;
			    color: #fff;
			}

			.sidebar_footer {
			    display: table-cell;
			    vertical-align: bottom;
			    /*padding-top: 48px;*/
			}

			.sidebar_callback {
			    padding: 0 20px 20px;
			}

			.sidebar_row {
			    display: table !important;
			}

			.content{
				width: 100%;
			}

			.modal .btn {
			    font-family: 'Open Sans';
			    /*float: right;*/
			    /*padding: 0 20px;*/
			    /*margin-top: 10px;
    			margin-bottom: 10px;*/
			    box-shadow: inset 0 0 5px rgba(51, 55, 64, 0.14), 3px 3px 14px rgba(0, 0, 0, 0.14);
			    color: #fff;
			    background-color: #d92a3d;
			    border: 0px solid transparent;
			    -webkit-transition: all 0.3s ease-out;
			    -moz-transition: all 0.3s ease-out;
			    -o-transition: all 0.3s ease-out;
			    transition: all 0.3s ease-out;
			}

			.modal .btn:hover {
			    background-color: #333740;
			}

			.row{
			    margin-right: 0px;
				margin-left: 0px;
				text-align: left;
			}

			.title{
				font: 700 40px/45px 'Open Sans';
				text-align: center;
				margin-top: 40px;
			}

			.sub-title{
				font: 300 32px/42px "Open Sans",sans-serif;
				text-align: center;
			}

			p {
				font: 18px/26px Georgia,'Century Schoolbook L',Serif;
				text-align: justify;
				/*text-indent: 30px;*/
				margin-top: 20px;
			}

			h2{
				font: 300 26px/36px "Open Sans",sans-serif;
				text-align: justify;
			}

			.m_title{
				font: 300 20px/20px "Open Sans",sans-serif;
				text-align: justify;
				padding: 0px;
			}

			.m_ptext {
    			padding: 0px 21px 0px 0px;
    			margin-bottom: 10px;
    		}

    		.modal-body {
			    padding: 7px 17px;
			}

			.modal p{
				font: 300 19px/23px "Open Sans",sans-serif;
    			text-align: center;
			}

			h2>span{
				color: #d92a3d;
    			/*text-shadow: 0px 0px 6px rgba(0, 0, 0, 0.48);*/
			}

			.content-button.btn {
			    font: 21px/25px 'Open Sans';
			    white-space: pre-wrap;
			    padding: 10px 20px;
			    margin-top: 10px;
    			margin-bottom: 30px;
			    box-shadow: inset 0 0 5px rgba(51, 55, 64, 0.14), 3px 3px 14px rgba(0, 0, 0, 0.14);
			    color: #fff;
			    width: 100%;
			    background-color: rgba(51, 55, 64, 0.65);
			    border: 0px solid transparent;
			    -webkit-transition: all 0.3s ease-out;
			    -moz-transition: all 0.3s ease-out;
			    -o-transition: all 0.3s ease-out;
			    transition: all 0.3s ease-out;
			}

			.content-button.btn.red{
				background-color: #d92a3d;
			}

			.content-button.btn:hover {
			    color: #fff;
			    background-color: #333740;
			}

			.a-bl{
				margin-left: 0px;
			}

			.advant{
				float: left;
    			padding: 0 20px 20px;
    			margin: 10px 0;
    			background-color: rgba(51, 55, 64, 0.85);
    			border-radius: 5px;
    			color: #eaeaeb;
			}

			.advant h3{
				text-align: center;
    			font-size: 24px;
			}

			li{
				font: 18px/26px Georgia,'Century Schoolbook L',Serif;
				text-align: justify;
			}

			.advant li{
				font: 18px/26px Georgia,'Century Schoolbook L',Serif;
				text-align: justify;
			}

			.advant ol{
				padding-left: 0;
				margin-top: 20px;
			}
			.advant ol li{
				font: 300 19px/35px "Open Sans",sans-serif;
			    text-align: justify;
			    text-indent: -21px;
			    padding-left: 21px;
			}

			.advant h3>span{
				color: #d9942a;/*#d92a3d;*/
    			/*text-shadow: 0px 0px 6px rgba(0, 0, 0, 0.48);*/
			}

			.product{
    			padding: 0 20px 20px;
    			margin-top: 20px;
    			margin-bottom: 20px;
    			background-color: rgba(51, 55, 64, 0.81);
    			border-radius: 5px;
    			color: #eaeaeb;
			}

			.product h3{
				text-align: left;
				font: 700 40px/45px 'Open Sans';
				font-size: 21px;
				margin-top: 0px;
			}

			.product p{
				text-align: left;
				margin-top: 0px;
				text-indent: 0px;
			}

			.product span{
				color: #d9942a;
			}

			.product h4{
				margin-top: 0;
			}

			.content{
				padding-left: 270px;
			}

			.hamburger{
				display: none;
			}

			.thumbnail > div{
				padding: 10px 10px;
			}

			.thumbnail .im div {
			    border-radius: 0px;
			    height: 200px;
			}

			.item .thumbnail{
				position: relative;
				cursor: default;
				height: 490px;
				overflow: hidden;
				border: 0px;
				background-color: transparent;
                box-shadow: 10px 10px 5px rgba(51, 55, 64, 0.0);
                -webkit-transition: all 0.15s ease-out;
			    -moz-transition: all 0.15s ease-out;
			    -o-transition: all 0.15s ease-out;
			    transition: all 0.15s ease-out;
			}
			
			.item .thumbnail:hover{
			    background-color: #fff;
                box-shadow: 5px 5px 0px rgba(51, 55, 64, 0.21);
			}

			.news_card{
				height: 420px;
				overflow-y: hidden;
				position: relative;
				/*margin-bottom: 16px;*/
				padding: 0;
			}

			.news_card h4{
				font: 700 16px/20px "Open Sans",sans-serif;
			}

			

			.delimiter{
				/*background-color: rgba(51, 55, 64, 0.4);*/
				background-color: rgba(51, 55, 64, 0.7);
			    box-shadow: inset 2px 2px 15px rgba(0,0,0,0.3);
			    height: 60px;
			    margin-left: 0;
			    margin-top: 20px;
			}

			.footer_logo img{
			    width:100%;
				max-height: 70px;
			}

			.cert{
				height: 500px;
				background: url(/images/Svidetelstvo2.jpg) no-repeat center center;
				background-size: contain;
			}

			.short_intro p, .short_intro span{
				margin-top: 0px;
				font-size: 16px !important;
				line-height: 22px !important;
				color: rgb(68, 68, 68) !important;
				font-family: Roboto, "Helvetica Neue", Arial, sans-serif !important;
			}

			.news_card:after{
				content: "";
			    position: absolute;
			    left: 0;
			    bottom: -2px;
    			height: 30px;
			    width: 100%;
			    background: linear-gradient(to bottom,rgba(255, 255, 255, 0) 0, rgb(245, 245, 245) 90%, rgb(245, 245, 245) 100%);
			    -webkit-transition: all 0.15s ease-out;
			    -moz-transition: all 0.15s ease-out;
			    -o-transition: all 0.15s ease-out;
			    transition: all 0.15s ease-out;
			}
			
			.item .thumbnail:hover .news_card:after {
                background: linear-gradient(to bottom,rgba(255, 255, 255, 0) 0, rgba(255,255,255,1) 90%, rgba(255,255,255,1) 100%);
			}

			.news_card .datetime{
				font: 14px/16px 'Open Sans';
    			margin-top: 10px;
				text-align: justify;
			}

			.news_card a {
				font: 18px/26px Georgia,'Century Schoolbook L',Serif;
				color: #d92a3d;
				text-align: justify;
				text-decoration: underline;
				/*text-indent: 30px;*/
				margin-top: 20px;
			}

			.txt-content{
				text-indent: 0px;
			}

			.news-content img {
			    margin: auto;
			}

			.page_container {
			    padding: 32px 10px 0 10px;
			}

			.form-label{
				text-align: center;
				font: 14px/16px 'Open Sans';
				text-indent: 0px !important;
				margin-bottom: 0px !important;
			}
			/*.main-screen{
				-moz-transform: scaleX(-1);
			    -o-transform: scaleX(-1);
			    -webkit-transform: scaleX(-1);
			    transform: scaleX(-1);
			    filter: FlipH;
			    -ms-filter: "FlipH";
			}*/
			.city a{
				color: #d92a3d !important;
				font-size: 20px;
			}

			.sidebar-nav .city .dropdown-header{
				color: #fff !important;
			    font: 12px/10px "Open Sans",sans-serif;
			}

			.sidebar-nav .city li > a{
			    font: 15px/9px "Open Sans",sans-serif;
			}

			.sidebar-nav .city .dropdown-menu{
			    background-color: rgba(255, 255, 255, 0.06);
			}

			.sidebar-nav .city a:hover {
			    background-color: rgba(255, 255, 255, 0.06);
			}

			.main-button.btn:hover {
			    color: #333740;
			    background-color: #ffffff;
			}

			.footer-ap {
			    background: #333740;
			    color: #fff;
			    margin-top: 0px;
			    padding: 20px 0;
			    padding-left: 270px;
			    padding-bottom: 10px;
    			width: 100%;
			}

			.footer-ap h2{
				text-align: center;
			}

			.footer-ap .row{
				margin: 0 20px;
			}

			.footer-links, .footer-links2{
				text-align: center;
			}

			.footer-links2{
				margin-top: 5px;
			}

			.footer-links a{
				color: #d92a3d;
				font-size: 17px;
				margin: 10px;
				white-space: nowrap;
			}

			.footer-links2 a{
				color: rgba(234, 234, 235, 0.4);
				font-size: 12px;
				margin: 10px;
				white-space: nowrap;
				text-decoration: underline;
			}

			.main-screen{
				box-shadow: 0px 0px 20px rgba(0, 0, 0, 0.26) inset;
			}

			.pagination li a{
				font: 14px/18px "Open Sans",sans-serif;
				color: #d82a3d;	
			}

			.pagination li.active a{
				background-color: #d82a3d;
				border-color: #d82a3d;
			}

			#ermsg{
				font-size: 16px;
				font-family: 'Roboto', sans-serif;
				color: red;
				margin-top: 0px;
			}

		</style>
		<div class="overlay"></div>
		<?php }?> <!-- убираем лишнее выше для quiz-->
		<?php if(!in_array($temp['page'], array('quiz','spasibo_quiz','check','test'))){
		    //if($temp['page']!='check' and $temp['page']!='test' and $temp['page']!='quiz'){
		?> 
		<!-- Navigation -->
		<?php 
		    if(isset($_GET['test'])){  ?>
		        <nav class="navbar navbar-inverse navbar-fixed-top" id="sidebar-wrapper" role="navigation">
            <ul class="nav sidebar-nav">
                <li class="sidebar-brand">
                    <a class="footer_logo" href="/">
						<img src="{theme_url}/images/logo6.png" alt="nav-logo">
					</a>
                </li>
                <li class="dropdown">
                  <a href="#" class="dropdown-toggle" data-toggle="dropdown">Товарный знак</a> <!--<span class="caret"></span>-->
                  <ul class="dropdown-menu" role="menu">
                    <!--<li class="dropdown-header">Dropdown heading</li>-->
                    <li><a href="<?=$cit_in;?>/registraciya_tovarnogo_znaka/">Регистрация товарного знака</a></li>
					<li><a href="<?=$cit_in;?>/findtz">Проверка товарного знака</a></li>
					
                  </ul>
                </li>
                
                
                <li class="dropdown">
                  <a href="#" class="dropdown-toggle" data-toggle="dropdown">Патенты</a> <!--<span class="caret"></span>-->
                  <ul class="dropdown-menu" role="menu">
                    <!--<li class="dropdown-header">Dropdown heading</li>-->
                    <li><a href="<?=$cit_in;?>/patent">Патентование изобретений</a></li>
					<!--<li><a href="#">Патентование полезных моделей</a></li>-->
					<li><a href="<?=$cit_in;?>/patent_prom_obr">Патентование промышленных образцов</a></li>
                  </ul>
                </li>
                
               
                
                
                <li> <a href="http://lireate.ru/mktu">МКТУ</a></li>
                
                <li><a href="http://lireate.ru/portfolio">Портфолио</a></li>
                    
               
                
                
                <li>
                    <a href="https://lireate.ru/news/">Блог</a>
                </li>
                                <li>
                    <a href="http://lireate.ru/team">Команда Лирейт</a>
                </li>
                               
                                
                <li><a href="http://lireate.ru/partners">Партнёры</a></li>
                <li>
                    <a href="http://lireate.ru/o_kompanii">О компании</a>
                </li>
                 <li>
                    <a href="http://lireate.ru/reviews">Видео отзывы</a>
                </li>
                <li>
                    <a href="http://lireate.ru/docs">Документы</a>
                </li>
                <li>
                    <a href="https://yandex.ru/profile/4770028872">Контакты</a>
                </li>
                <li>
                    <style>
                        .youtubeLink{
                            background:url('{theme_url}/images/icon/youtube_grey.svg');
                            background-size: contain;
                            background-repeat: no-repeat;
                            background-position: left;
                        }
                        .youtubeLink:hover{
                            background:url('{theme_url}/images/icon/youtube_red.svg');
                            background-size: contain;
                            background-repeat: no-repeat;
                            background-position: left;
                        }
                    </style>
		            <div class="sidebar_social">
		            	<div style="margin: auto; width: 53%;">
			            	<a href="https://www.facebook.com/lireate/" target="_blank" class="sidebar_social_item sidebar_social_item-fb" title="Facebook"></a> 
			            	<!--<a href="https://twitter.com/devham1" target="_blank" class="sidebar_social_item sidebar_social_item-twi" title="Twitter"></a>-->
			            	<a href="https://www.instagram.com/_lireate_/" target="_blank" class="sidebar_social_item sidebar_social_item-inst"  title="Instagram"></a>
			            	<a href="https://vk.com/lireate" target="_blank" class="sidebar_social_item sidebar_social_item-vk"  title="ВКонтакте"></a>
			            	<a href="https://www.youtube.com/channel/UCpg1LWotCQWwzviPDb_9Rew" target="_blank" class="sidebar_social_item sidebar_social_item-vk youtubeLink"  title="YouTube"></a>
			            </div>
		            </div>
	            </li>
	            <li class="sidebar_row">
	            	<div class="sidebar_footer text-center">
	            	    <a href="tel:+74999384595" data-lx-ym-goal="klik_po_nomeru_telefona"><b>+7(499)938-45-95</a><a href="tel:+79600484957" data-lx-ym-goal="klik_po_nomeru_telefona">   +7(960)048-49-57</a> </b>
						<div class="sidebar_callback">
                            <div class="btn btn-primary btn-fw btn-small js-modal-form" data-toggle="modal" data-frm="name,tel" data-mtitle="Оставьте заявку и мы свяжемся с вами в ближайшее время" data-tomail="Заказать обратный звонок" data-success="Мы перезвоним Вам в ближайшее время." data-btn="Заказать обратный звонок"><i class="glyphicon glyphicon-earphone"></i> Обратный звонок</div>
                        </div>
                	</div>
	            </li>
            </ul>
        </nav>
		  <?php   }   
		    
		    else{
		        include "./template/parts/topBar.php";
		        include "./template/parts/leftSideNav.php";
		?>
		
        <?php   }?>
		
        <script type="text/javascript">
        	// Add slideDown animation to Bootstrap dropdown when expanding.
			$('.dropdown').on('show.bs.dropdown', function() {
				$(this).find('.dropdown-menu').first().stop(true, true).slideDown();
			});

			// Add slideUp animation to Bootstrap dropdown when collapsing.
			$('.dropdown').on('hide.bs.dropdown', function() {
			    $(this).find('.dropdown-menu').first().stop(true, true).slideUp();
			});
			/* lireate-home-dark-skip-main-screen-height */
			function lireateResizeMainScreen() {
			  if ($('body').hasClass('lireate-cms-page-dark')) return;
			  $('.main-screen').height($(window).height());
			}
			$(window).resize(function() { lireateResizeMainScreen(); });

			$(window).trigger('resize');

			$(window).ready(function(){
				$(window).resize();
				$.ajaxSetup({ cache: false });
				//init();
			});
        </script>
        <?php } ?>
        <?php if(!in_array($temp['page'], array('quiz','spasibo_quiz'))){?>
        <div class="container text-center content" style="padding-right: 0px;">


	
	
	            
            
            
          
            
            
            
            
            
            
            
            
            
            
            
            
            
            
            
            
            
            
            
            
            
            
            
            
            
            
            
            <?php if($temp['page']!='check'){?>
        	<button type="button" class="hamburger is-closed" data-toggle="offcanvas">
                <span class="hamb-top"></span>
    			<span class="hamb-middle"></span>
				<span class="hamb-bottom"></span>
            </button>
            <?php } ?>
            <?php } ?> 
		
		
		
		
		











		    
					    

		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		<?php if($temp['page']=='home' && !empty($_lx_home_v2)): /* lireate_home_v2_20261125 content */ include dirname(__FILE__) . '/page/home/lireate-home-v2.inc.php'; elseif($temp['page']=='home'):?>
			<style type="text/css">

			.main-title h1{
				/*font: 700 40px/45px 'Open Sans';*/
				font: 700 32px/45px 'Open Sans';
			}

			.main-title h2{
				font: 30px/50px 'Open Sans'; 
			}

			.main-title h3{
				font: 16px/20px 'Open Sans';
				margin-top: 75px;
				text-align: right;
			}

			
			.main-screen{
				background-image: url('template/images/<?=$bgcity?>main_back_opt.jpg');
				background-size: cover;
			}

			.main-title{
				background-color: rgba(51, 55, 64, 0.89);
			    /*width: fit-content;*/
			    /*width: 55%;*/
			    float: right;
			    padding: 0 20px;
			    margin: 0 35px;
			    border-radius: 5px;
			    color: white;
			    border-right: 10px solid #d92a3d;
			    text-align: right;
			}

			.main-button.btn {
			    font: 22px/50px 'Open Sans';
			    float: right;
			    padding: 0 20px;
			    margin-top: 10px;
    			/*margin-bottom: 30px;*/
			    box-shadow: inset 0 0 5px rgba(51, 55, 64, 0.14), 3px 3px 14px rgba(0, 0, 0, 0.14);
			    color: #fff;
			    width: fit-content;
			    background-color: #d92a3d;
			    border: 0px solid transparent;
			    -webkit-transition: all 0.3s ease-out;
			    -moz-transition: all 0.3s ease-out;
			    -o-transition: all 0.3s ease-out;
			    transition: all 0.3s ease-out;
			}

			
			.content-button.btn {
			    font: 21px/25px 'Open Sans';
			    white-space: pre-wrap;
			    padding: 10px 20px;
			    margin-top: 10px;
    			margin-bottom: 30px;
			    box-shadow: inset 0 0 5px rgba(51, 55, 64, 0.14), 3px 3px 14px rgba(0, 0, 0, 0.14);
			    color: #fff;
			    width: 100%;
			    background-color: rgba(51, 55, 64, 0.65);
			    border: 0px solid transparent;
			    -webkit-transition: all 0.3s ease-out;
			    -moz-transition: all 0.3s ease-out;
			    -o-transition: all 0.3s ease-out;
			    transition: all 0.3s ease-out;
			}

			.content-button.btn.red{
				background-color: #d92a3d;
			}

			.content-button.btn:hover {
			    color: #fff;
			    background-color: #333740;
			}

			.main-modal{
				/*display: none;
				opacity: 0;
				-webkit-transition: all 0.3s ease-out;
			    -moz-transition: all 0.3s ease-out;
			    -o-transition: all 0.3s ease-out;
			    transition: all 0.3s ease-out;*/
			}

			.main-modal.active{
				/*display: block;
				opacity: 1;
				position: fixed;
				top:20%;
				margin: 0px;
				margin-left: 290px;
    			padding: 0px;
    			background-color: #f5f5f5;
    			z-index: 999;*/
			}

			@media (max-width: 970px){
				.main-title{
					bottom: auto !important;
				}
				.main-title h2{
					font: 700 32px/45px 'Open Sans';
				}
				.main-title h3{
					margin-top: 0px;
				}
			}

			@media (max-width: 750px){
				.main-screen{
					background-image: url('{theme_url}/images/<?=$bgcity?>main_back_opt_mini.jpg');
				}
				#modal-list{
					padding-left: 0;
				}
				.main-modal li{
					font-size: 16px;
					text-align: left;
				}
			}
			
			
			
			
			
			
			
			
			

		</style>
        	<div id="lireate-home" class="lu-page lh-page">
        	<div class="lu-glow" aria-hidden="true"></div>
        	<div class="lu-wrap lh-wrap">
        	<div class="row main-screen lireate-hero-v4" id="main_back" style="margin: 0;">
		<?php /* lireate_design_home_hero_v4 */ if (is_file(dirname(__FILE__) . '/page/home/lireate-home-hero.inc.php')) { include dirname(__FILE__) . '/page/home/lireate-home-hero.inc.php'; } ?>
        	</div>
        	
        	<div id="main-modal" class="modal main-modal tz-modal">
        		<div class="modal-dialog">
        			<div class="modal-content">
        				<div class="modal-header">
        					<button class="close" type="button" data-dismiss="modal">&times;</button>
        					<h4 class="modal-title">Выберите интересующую услугу</h4>
						</div>
						<div class="modal-body">
							<ul id='modal-list'>
							</ul>
						</div>
					</div>
				</div>
			</div>
        	<div class="row col-xs-12 lh-modals" id="modals"><!-- lireate_design_home_modals_row_v1 -->
        		<div class="col-xs-12 col-lg-6 advant">
        			<h3><span>Товарный знак</span></h3>
					<p>Зарегистрируем Ваш товарный знак не только на территории России, но и во всём мире</p>
					<div class="btn content-button red" data-toggle="modal" data-target="#main-modal" id="tz_but" style="margin-bottom:0px;">Подробнее</div>
					<!--<div class="btn content-button red js-modal-form" id="tz_but" data-toggle="modal" data-frm="name,tel" data-mtitle="Оставьте заявку и мы свяжемся с вами в ближайшее время" data-tomail="Товарный знак" data-success="Мы перезвоним Вам в ближайшее время." data-btn="Отправить заявку" style="margin-bottom:0px;">Подробнее</div>-->
        		</div>
        		<div class="col-xs-12 col-lg-6 advant">
        			<h3><span>Договоры</span></h3>
					<p>Подготовим индивидуальный договор для охраны Ваших прав на результат интеллектуальной деятельности</p>
					<a class="btn content-button red" href="/dogovory/" id="dog_but" style="margin-bottom:0px;" data-lx-home-more="1"><!-- lireate_design_home_modal_form_btns_v1 --><!-- lireate_design_home_service_urls_v1 -->Подробнее</a>
					<!--<div class="btn content-button red js-modal-form" data-toggle="modal" data-frm="name,tel" data-mtitle="Оставьте заявку и мы свяжемся с вами в ближайшее время" data-tomail="Договоры" data-success="Мы перезвоним Вам в ближайшее время." data-btn="Отправить заявку" style="margin-bottom:0px;">Подробнее</div>-->
        		</div>
        		<div class="col-xs-12 col-lg-6 advant">
        			<h3><span>Патенты</span></h3>
					<p>Поможем Вам получить патент на изобретение, промышленный образец или полезную модель</p>
					<div class="btn content-button red" data-toggle="modal" data-target="#main-modal" id="pat_but" style="margin-bottom:0px;">Подробнее</div>
					<!--<div class="btn content-button red js-modal-form" data-toggle="modal" data-frm="name,tel" data-mtitle="Оставьте заявку и мы свяжемся с вами в ближайшее время" data-tomail="Патенты" data-success="Мы перезвоним Вам в ближайшее время." data-btn="Отправить заявку" style="margin-bottom:0px;">Подробнее</div>-->
        		</div>
        		<div class="col-xs-12 col-lg-6 advant">
        			<h3><span>Программы ЭВМ</span></h3>
					<p>Защитим Ваше приложение: программный код, дизайн, интерфейс</p>
					<a class="btn content-button red" href="/registraciya_programm_evm/" id="pro_but" style="margin-bottom:0px;" data-lx-home-more="1">Подробнее</a>
					<!--<div class="btn content-button red js-modal-form" data-toggle="modal" data-frm="name,tel" data-mtitle="Оставьте заявку и мы свяжемся с вами в ближайшее время" data-tomail="Программы ЭВМ" data-success="Мы перезвоним Вам в ближайшее время." data-btn="Отправить заявку" style="margin-bottom:0px;">Подробнее</div>-->
        		</div>
        		<!--<div class="col-xs-12 col-lg-10 col-lg-offset-1">
        			<h2 class="title title-as-h1" style="text-align: justify;">Мы уверены, что регистрация товарного знака с оплатой за результат — это самая прозрачная форма сотрудничества.</h2>
        			<h2 class="sub-title">Вы оплачиваете только определённый результат.</h2>
        			<ul>
						<li>получение приоритета на товарный знак;</li>
						<li>положительное решение о соответствии классов МКТУ;</li>
						<li>прохождение формальной экспертизы;</li>
						<li>принятие Роспатентом ответа на возражение;</li>
						<li>положительное  решение о возможности регистрации;</li>
						<li>получение Вами свидетельства.</li>
					</ul>
        		</div>-->
        		<div class="row col-xs-12">
	        		<h2 class="sub-title" style="text-align: center;">Регистрация интеллектуальной собственности начинается с Лирейт</h2>
	        	</div>
	        	<div class="col-xs-12 col-lg-10 col-lg-offset-1">
					<p style="font-style: italic;">«Удовлетворить предъявляемые Роспатентом требования — непросто. Подача заявки без помощи специалиста по интеллектуальной собственности может привести к отказу в регистрации»</p>
	    		</div>
        		<div class="row col-xs-12">
	        		<h3 class="sub-title">Нет времени искать ответ на страницах сайта? Оставьте заявку и наши специалисты свяжутся с вами:</h3>
	        	</div>
        		<div class="col-xs-12 col-lg-6 col-lg-offset-3">
        			<div class="btn content-button red js-modal-form" data-toggle="modal" data-frm="name,tel" data-mtitle="Оставьте заявку и мы свяжемся с вами в ближайшее время" data-tomail="Нет времени?" data-success="Мы перезвоним Вам в ближайшее время." data-btn="Отправить заявку">Оставить заявку</div>
        		</div>
        	</div>

        	<script type="text/javascript">
        		function cl(n){
	        		var nm = ["tz_but","dog_but","pat_but","pro_but"];
	        		var lurl = [["/registraciya_tovarnogo_znaka/","/findtz","#","#","#","#"],
					["/dogovory/#licenziya","/dogovory/#franchise","/dogovory/#otchuzhdenie","/dogovory/#avtorskij-zakaz"],
					["/patent_prom_obr","#","/findtz"],
					["/registraciya_programm_evm/","/registraciya_programm_evm/#registraciya-bd","/registraciya_programm_evm/#zaschita-sajta"]]; /* lireate_design_home_service_urls_v1 */
					var lname = [["Регистрация товарного знака","Проверка товарного знака","Регистрация торговой марки","Регистрация логотипа","Защита фирменного наименования","Защита коммерческого обозначения"],
								["Лицензионный договор","Договор коммерческой концессии","Договор отчуждения","Договор авторского заказа"],
								["Патентование изобретений","Патентование полезных моделей","Патентование промышленных образцов"],
								["Регистрация программ ЭВМ","Регистрация баз данных","Регистрация и защита сайтов"]]; // массив названий для ссылок
					var ind = nm.indexOf(n);
					if ($('#modal-list li').length>0){
						$('#modal-list li').remove();
					}
					if (ind > -1){
						for(var i = 0; i < lurl[ind].length; i++){
							if(lurl[ind][i]!='#'){
								$('#modal-list').append('<li><a href="<?=$cit_in;?>'+lurl[ind][i]+'">'+lname[ind][i]+'</a></li>');
							}else{
								$('#modal-list').append('<li><a href="#" class="js-modal-form" data-toggle="modal" data-frm="name,tel" data-mtitle="Оставьте заявку и мы свяжемся с вами в ближайшее время" data-tomail="Товарный знак" data-success="Мы перезвоним Вам в ближайшее время." data-btn="Отправить заявку">'+lname[ind][i]+'</a>');
								$('#modal-list a').click(function(){
									$('.js-modal-form').eq(1).click();
									$('.main-modal .close').click();
								});
							}
						}
					}
				}
				$('#modals .advant .btn').click(function(){cl(this.id);});
        	</script>

        	<div class="row col-xs-12 delimiter">
        		<br/>
        	</div>

        	<div class="row col-xs-12">
        		<h3 class="title">Почему нас выбирают?</h3>
        	</div>
        	<div class="col-xs-12 col-lg-10 col-lg-offset-1 lh-why-grid lu-section"><!-- lireate_design_home_why_grid_v1 -->
        		<div class="col-xs-12 col-lg-6 advant block-margin">
        			<ol>
						<li>У нас есть специалисты, которые занимаются защитой интеллектуальной собственности более 20 лет.</li>
						<li>Мы не обманываем. Обсуждая проект, мы говорим только о реальных возможностях и сроках. Никаких ложных обещаний.</li>
						<li>Оплата только за результат.</li>
					</ol>
        		</div>
        		<div class="col-xs-12 col-lg-6 advant" style="background-color: transparent;">
					<div class="cert"></div>
        		</div>
        	</div>
        	
        	
        	
        	
    		
    		 <div class="col-xs-10 col-xs-offset-1 col-lg-8 col-lg-offset-2 product lh-product lu-section">
	    		<h3 style="margin-bottom: 0px;">Регистрация товарного знака с оплатой за результат</h>
				<h2><span>от 30000 руб.</span></h2>
				<p style="text-align: justify;">Вы решили самостоятельно зарегистрировать свой товарный знак? Это похвально. В среднем вам понадобится около 100 часов на обучение и изучение требований Роспатента. Если у вас есть время на ошибки, мы желаем вам успехов. Регистрация нужна уже сейчас? Мы готовы помочь.</p>
				<div class="col-xs-12 col-lg-6 col-lg-offset-3">
        			<div class="btn content-button red js-modal-form" data-toggle="modal" data-frm="name,tel" data-mtitle="Оставьте заявку и мы свяжемся с вами в ближайшее время" data-tomail="Заказать обратный звонок" data-success="Мы перезвоним Вам в ближайшее время." data-btn="Отправить заявку" style="margin-bottom:0px;">Заказать сейчас</div>
        		</div>
			</div> 
			
			
			            
			
			
			
			
			
			
			
			
			
			
			
			<div class="row col-xs-12 delimiter">
        		<br/>
        	</div>
			<div class="row col-xs-12">
	        	<h2 class="sub-title" style="text-align: center;">Делимся опытом в <a href="https://lireate.ru/news/"><span>блоге:</span></a></h2>
        	</div>
        	<div class="lireate-home-blog">
        		<div class="lireate-home-blog-intro">
				<p style="margin-bottom: 30px;">В блоге мы публикуем полезные материалы, делимся решениями — это ценно. Нам не импонируют агрессивные продажи, безумные триггеры и надоедливые звонки. Мы стараемся доказать нашим клиентам на деле, что мы знаем и можем. Ваша вера в нашу экспертность помогает быстрее добиваться взаимопонимания и значительно упрощает работу. Мы не отвлекаемся на разговоры о нас. Мы ежедневно регистрируем товарные знаки, защищаем авторские права, получаем патенты и консультируем. Стоит ли доверять нам работу? Почитайте наш блог: Вы найдете бесплатные решения, узнаете, как зарегистрировать товарный знак, получить патент, провести проверку торговой марки и много другой полезной информации. Убедившись в нашей экспертности, Вам будет легче сделать выбор в пользу сотрудничества с нами.</p>
        		</div>
		<?php /* lireate_design_home_blog_teaser_v1 */ if (is_file(dirname(__FILE__) . '/page/home/lireate-home-blog-teaser.inc.php')) { include dirname(__FILE__) . '/page/home/lireate-home-blog-teaser.inc.php'; } ?>
        	</div>
    		
		        	</div>
		</div><!-- /* lireate_design_home_lu_wrap_v1 */ -->
		<?php endif?>
		
		
		
		
		
		
		
		
		<!-- ================================================================================================-->
		
		<!-- =====================       РАЗДЕЛ САЙТА - РЕГИСТРАЦИЯ ТОВАРНОГО ЗНАКА          ================-->
		
		<!-- ================================================================================================-->
		
		
		
		
		
		
		
		<?php if($temp['page']=='reg-tz-main'):?>
			<style type="text/css">

				.main-title h1{
					/*font: 700 40px/45px 'Open Sans';*/
					font: 700 32px/45px 'Open Sans';
				}

				.main-title h2{
					font: 30px/50px 'Open Sans'; 
					text-align: right;
				}

				.main-screen{
					background-image: url('{theme_url}/images/reg-tz-main_back_opt.jpg');
					background-size: cover;
				}

				.main-title{
					background-color: rgba(51, 55, 64, 0.69);
				    /*width: fit-content;*/
				    width: 55%;
				    float: right;
				    padding: 0 20px;
				    margin: 0 35px;
				    color: white;
				    border-radius: 5px;
				    border-right: 10px solid #d92a3d;
				    text-align: right;
				}

				.main-button.btn {
				    font: 22px/50px 'Open Sans';
				    float: right;
				    padding: 0 20px;
				    margin-top: 10px;
	    			margin-bottom: 30px;
				    box-shadow: inset 0 0 5px rgba(51, 55, 64, 0.14), 3px 3px 14px rgba(0, 0, 0, 0.14);
				    color: #fff;
				    width: fit-content;
				    background-color: #d92a3d;
				    border: 0px solid transparent;
				    -webkit-transition: all 0.3s ease-out;
				    -moz-transition: all 0.3s ease-out;
				    -o-transition: all 0.3s ease-out;
				    transition: all 0.3s ease-out;
				}

				.content-button.btn {
				    font: 21px/25px 'Open Sans';
				    white-space: pre-wrap;
				    padding: 10px 20px;
				    margin-top: 10px;
	    			margin-bottom: 30px;
				    box-shadow: inset 0 0 5px rgba(51, 55, 64, 0.14), 3px 3px 14px rgba(0, 0, 0, 0.14);
				    color: #fff;
				    width: 100%;
				    background-color: rgba(51, 55, 64, 0.65);
				    border: 0px solid transparent;
				    -webkit-transition: all 0.3s ease-out;
				    -moz-transition: all 0.3s ease-out;
				    -o-transition: all 0.3s ease-out;
				    transition: all 0.3s ease-out;
				}

				.form-reg .order_form-group {
				    padding-top: 5px;
				}

				p {
					margin-top: 10px;
				}

				.form-reg p, .form-reg span{
					color: rgba(255,255,255,0.68);
				}

				.form-reg .has-error p{
					color: #ff0000;
				}

				.form-control{
					margin-top: 5px;
				}

				#im{
					background: url({theme_url}/images/Upload-icon.png) no-repeat center center; 
					height: 100px; 
					background-size:  contain; 
					opacity:  0.5; 
					margin-top: 10px; 
					cursor: pointer;
					-webkit-transition: all 0.3s ease-out;
				    -moz-transition: all 0.3s ease-out;
				    -o-transition: all 0.3s ease-out;
				    transition: all 0.3s ease-out;
				}

				#im:hover{
					opacity: 1;
					background-color: #ffffff1f;
				}

				#im.hasImage{
					background: url({theme_url}/images/ok.png) no-repeat center center;
					background-size:  contain; 
				}

				@media (max-width: 750px){
					.main-screen{
						background-image: url('{theme_url}/images/reg-tz-main_back_opt_mini.jpg');
					}
				}
			</style>
			<!--<div id="find-modal" class="modal find-modal">
        		<div class="modal-dialog">
        			<div class="modal-content" style="background-color: rgba(255, 255, 255, 0.89);">
        				<div class="modal-header" style="background-color: #333740; border-radius: 4px 4px 0 0;">
        					<button class="close" type="button" data-dismiss="modal" style="color: #fff;text-shadow: 0 -2px 0 #000;">&times;</button>
        					<h4 class="modal-title" style="color: white;">Бесплатный поиск по базам Роспатента</h4>
						</div>
						<div class="modal-body">
							<form class="order_form_list ajax_form_find" method="POST" action="https://lireate.ru/mail2.php" multipart="" enctype="multipart/form-data">
								<input type="hidden" name="mail_token" value="<?=md5(microtime(true));?>">
								<input type="hidden" name="fuck_spamers" value="<?=md5(date('m.Y'));?>">
								<input type="hidden" name="tomail" value="<?//=$temp['db_page'][$temp['pagekey']]['name'];?>">
								<input type="hidden" name="mail" value="mailorder">
								<input type="hidden" name="MAX_FILE_SIZE" value="20000000" />
			                    <div class="mail_to">
									<div class="inline-unit clearfix">
										<div data-frm="tel" class="order_form-group telError vError">
											<input name="tel" type="text" class="tel form-control order_form_control" placeholder="Ваш телефон" required>
										</div>
										<div data-frm="email" class="order_form-group emailError vError">
											<input name="email" type="text" class="form-control order_form_control" placeholder="Ваш e-mail" required pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,3}$">
										</div>
			                            <p class="form-label">Вид обозначения</p>
			                            <div data-frm="type_tz" class="order_form-group type_tzError vError">
				                            <select id="type_tz" name="type_tz" class="form-control order_form_control">
				                                <option value="Словесное">Словесное</option>
				                                <option value="Изобразительное">Изобразительное</option>
				                                <option value="Комбинированное">Комбинированное</option>
				                            </select>
				                        </div>
			                            <div data-frm="brand" class="br-field order_form-group brandError vError">
											<input id='brand' name="brand" type="text" class="form-control order_form_control" placeholder="Название товарного знака" required>
										</div>
										<p class="form-label fl-field" style="text-indent: 0px; margin-bottom: 0px;">Файл с изображением товарного знака:</p>
										<div data-frm="userfile" class="fl-field order_form-group emailError vError" style="margin-top: -10px;">
											<input id='file' type="file" name="userfile[]" accept="image/gif,image/jpeg,image/png" class="order_form-group frm-input" multiple>
										</div>
				                        <center style="margin-top: 20px; padding-bottom: 10px;"><button id="submit" type="button" class="btn btn_red btn_orange_bold order_form_btn">Отправить на проверку</button></center>
				                        <div id="prog" class="hidden">
					                        <div class="progress" >
												<div id="progressBar" class="progress-bar progress-bar-warning progress-bar-striped active" style="min-width: 10%;">
													0%
												</div>
											</div>
											<p id="status">
												загрузка файлов
											</p>
										</div>
				                        <span style="font-size: 12px;">Нажимая на кнопку, вы даете <a href="/private_data" target="_blank">согласие на обработку персональных данных</a> и соглашаетесь c <a href="/politika_konfidencialnosti" target="_blank">политикой конфиденциальности</a></span>
			                        </div>
			                    	<div class="s_mail_to">Отправлено!</div>
			                    </div>
			                </form>
			                
			            </div>

			            <script>
			            	window.onload = function() {
								$('#type_tz').change(function(){
								    switch (this.value){
								    	case 'Словесное':
									    	$('#brand').prop('required',true);
									    	$('#file').prop('required',false);
									    	$('.fl-field').addClass('hidden');
									    	$('.br-field').removeClass('hidden');
									    	break;
									    case "Изобразительное":
								    		$('#brand').prop('required',false);
								    		$('#file').prop('required',true);
								    		$('.fl-field').removeClass('hidden');
								    		$('.br-field').addClass('hidden');
									    	break;
								        case "Комбинированное":
									        $('#brand').prop('required',true);
									    	$('#file').prop('required',true);
									    	$('.fl-field').removeClass('hidden');
									    	$('.br-field').removeClass('hidden');
									    	break;
								    }
								});
								$('#type_tz').change();
							}
						</script>
					</div>
				</div>
			</div>-->
			<div class="row main-screen" style="margin: 0;">
	        	<br/>
	        	<br/>
	        	<br/>
	        	<div class="main-title">
	        		<h1>Гарантированная регистрация товарного знака</h1>
		        	<h2>Наши кейсы это не Сoca-Сola и Ford а Вы</h2>
		        	<div class="btn main-button js-modal-form" data-toggle="modal" data-frm="name,tel" data-mtitle="Оставьте заявку и мы свяжемся с вами в ближайшее время" data-tomail="Заказать обратный звонок" data-success="Мы перезвоним Вам в ближайшее время." data-btn="Отправить заявку">Обратиться</div>
	        	</div>
	        	<br/>
	        	<br/>
        	</div>
        	<div class="row col-xs-12">
        		<h3 class="title">3 этапа и Ваш товарный знак зарегистрирован.</h3>
        	</div>
        	<div class="col-xs-12">
	        	<br/>
	        	

	        	<br/>
	        	<div class="col-xs-12">
	        		<h2 class="sub-title">1. Получите <b>бесплатный эспресс-анализ</b> на возможность регистрации Вашего товарного знака в течение 1 часа.</h2>
	        		<p>Проводим поиск схожих обозначений по Вашим классам МКТУ (виды деятельности или/и группам товаров) по открытым источникам Роспатента. При нахождении схожих с уже зарегистрированными товарными знаками, Вы получаете отчёт о возможности регистрации Вашего товарного знака, а также рекомендации об усиления Вашего товарного знака для прохождения регистрации.</p>
        		</div>
	        	<div class="col-xs-12 col-lg-10 col-lg-offset-1" style="margin-top: 20px;">
		        	<div class="modal-content" style="background-color: rgba(51,55,64,0.85); border: 0px;">
        				<div class="modal-header" style="background-color: #333740;border-radius: 4px 4px 0 0;border: 0px;">
        					<!--<button class="close" type="button" data-dismiss="modal" style="color: #fff;text-shadow: 0 -2px 0 #000;">×</button>-->
        					<h4 class="modal-title" style="color: white;">Рекомендуем начать регистрацию с этого этапа. За час проверим знак на возможность регистрации.</h4>
						</div>
						<div class="modal-body">
						    
						    
						    
							<form class="form-reg order_form_list ajax_form_find" method="POST" action="https://lireate.ru/mail2.php" multipart enctype="multipart/form-data">
								<input type="hidden" name="mail_token" value="<?=md5(microtime(true));?>">
								<input type="hidden" name="fuck_spamers" value="<?=md5(date('m.Y'));?>">
								<input type="hidden" name="tomail" value="<?=$temp['db_page'][$temp['pagekey']]['name'];?>">
								<input type="hidden" name="mail" value="mailorder">
								<input type="hidden" name="MAX_FILE_SIZE" value="20000000">
								<input type="hidden" name="form-type" value="files">
								<!--<select id="type_tz" name="type_tz" class="form-control order_form_control" style="display:none;">
	                                <option value="Словесное">Словесное</option>
	                                <option value="Изобразительное">Изобразительное</option>
	                                <option value="Комбинированное">Комбинированное</option>
	                            </select>-->
			                    <div class="mail_to">
									<div class="inline-unit clearfix">
                                        <div class="col-xs-12 col-sm-6">
											<div data-frm="brand" class="br-field order_form-group brandError vError">
												<p class="form-label fl-field" style="text-indent: 0px; margin-bottom: 0px;">Как называется ваш товарный знак?</p>
												<input id="brand" name="brand" type="text" class="form-control order_form_control" placeholder="Название товарного знака" required>
											</div>
											<div data-frm="userfile" class="fl-field order_form-group emailError vError">
												<p class="form-label fl-field" style="text-indent: 0px; margin-bottom: 0px;">Файл с изображением товарного знака:</p>
												<div id="im">
												</div>
												<input id="file" type="file" name="userfile[]" accept="image/gif,image/jpeg,image/png" class="order_form-group frm-input" multiple="" style="display: none;">
											</div>
                                        </div>
                                        <div class="col-xs-12 col-sm-6">
											<!--<div data-frm="gorod" class="order_form-group gorodError vError">
												<p class="form-label fl-field" style="text-indent: 0px; margin-bottom: 0px;">Ваш город</p>
												<input name="gorod" type="text" class="form-control order_form_control" placeholder="Ваш город" required>
											</div>-->
											
											
											<div data-frm="order_block3" class="order_form-group type_tzError vError">
											    <p class="form-label" style="text-indent: 0px; padding-bottom:5px;">Куда отправить результаты?</p>
    				                            <select id="order_block3" name="order_block" style="border-radius:2px;" class="form-control order_form_control">
    				                                <option value="На whatsapp">На whatsapp</option>
    				                                <option value="На viber">На viber</option>
    				                                <option value="На telegram">На telegram</option>
    				                                <option value="По почте">На почту</option>
    				                            </select>
				                            </div>  
											
											
											
											<div data-frm="email" class="email2 order_form-group emailError vError hidden">
												<p class="form-label" style="text-indent: 0px;">Ваш e-mail</p>
												<input name="email" type="text" id="email2" class="form-control order_form_control" style="margin-top: 5px;" placeholder="Ваш e-mail" pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,3}$">
											</div>
											<div data-frm="tel" class="telephon2  order_form-group telError vError">
												<p class="form-label" style="text-indent: 0px;">Ваш телефон</p>
												<input name="tel" type="tel" id="telephone2"  class="tel form-control order_form_control" style="margin-top: 5px;" placeholder="Ваш телефон" required>
											</div>
											
                                        </div>
    									<div class="col-xs-12">
				                        	<center style="padding-top: 20px;padding-bottom: 10px;"><button id="submit" type="button" class="btn btn_red btn_orange_bold order_form_btn">Отправить на проверку</button></center>
				                        	
				                        	
				                        	<div id="prog" class="hidden">
					                    	    <div class="progress">
													<div id="progressBar" class="progress-bar progress-bar-warning progress-bar-striped active" style="min-width: 10%;">0%</div>
												</div>
												<p id="status">загрузка файлов</p>
											</div>
											<p class="text-center hidden" id="ermsg">Пожалуйста заполните все поля!</p>
    									<p style="font-size: 12px; margin-top: 10px; text-align: justify; line-height: 14px;">
                                          <input type="checkbox" checked required style="vertical-align: middle; margin: 0 5px 0 0;">
                                          Нажимая на кнопку, вы даете 
                                          <a href="/private_data" target="_blank">согласие на обработку персональных данных</a> 
                                          и соглашаетесь c 
                                          <a href="/politika_konfidencialnosti" target="_blank">политикой конфиденциальности</a>
                                        </p>
    									</div>
			                        </div>
			                    	<div class="s_mail_to">Отправлено!</div>
			                    </div>
			                </form>
			                
			                
			                <script type="text/javascript">
			                	$('#im').click(function(){$('#file').click()});
			                	$('#file').change(function(){
			                		if($('#file')[0].files.length>0){
			                			$('#im').addClass('hasImage');
			                		}else{
			                			$('#im').removeClass('hasImage');
			                		}
			                	});
			                </script>
			            </div>
					</div>
		        	<!--<div class="btn content-button red" data-toggle="modal" data-target="#find-modal" id="_bbut">Получить отчет о возможности регистрации моего товарного знака</div>-->
		        </div>
	        	<br/>
	        	<br/>
	        	<div class="col-xs-12">
        			<h2 class="sub-title">2. Получение Вами <b>гарантии</b>.</h2>
        			<p>Наступает самый ответственный этап. Здесь мы проводим экспертизу Вашего наименования по данным на регистрацию заявкам и доработка Вашего товарного знака для прохождения 100% регистрации. Если есть хоть 1% сомнения в прохождения регистрации мы его устраняем. После возможной доработки наименования до патентопригодности товарного знака, сохраняя его узнаваемость, формируем пакет документов и отправляем в Роспатент в электронном виде посредством элентронной цифровой подписи.</p>
        		</div>
        		<br/>
	        	<br/>
	        	<div class="col-xs-12">
	        		<h2 class="sub-title">3. Ваш товарный знак <b>защищён</b>.</h2>
        			<p>С даты получения Роспатентом заявки Вы получаете приоритет. Приоритет означает, что с этой даты, поданные  схожие знаки не будут зарегистрированы. Мы ведем делопроизводство под «ключ», не отвлекая Вас от решения задач в Вашем бизнесе. На все запросы Роспатента отвечают наши специалисты. По окончании процедуры регистрации мы вручаем Вам свидетельство на товарный знак.</p>
        		</div>
        		<br/>
	        	<br/>
        	</div>
        	<div class="row col-xs-12 delimiter">
        		<br/>
        	</div>
        	<div class="row col-xs-12">
        		<h3 class="title">ВАШИ ПРЕИМУЩЕСТВА:</h3>
        	</div>
        	<div class="row col-xs-12" style="padding: 0px 30px;">
        		<div class="col-xs-12 col-lg-6 advant">
        			<h3>Процесс регистрации идет <span>быстрее</span>.</h3>
					<p>Заявка и дальнейшая переписка с ФИПС ведется электронно. При подаче заявки она поступает мгновенно и регистрируется. Ориентируясь на наш опыт - Вы раньше получите свидетельство в среднем на 2 месяца.</p>
        		</div>
        		<div class="col-xs-12 col-lg-6 advant">
        			<h3>Вы <span>гарантированно</span> получите свидетельство на товарный знак.</h3>
					<p>Либо мы <span>компенсируем вам 110%</span> потраченных Вами средств.</p>
        		</div>
        		<div class="col-xs-12 col-lg-6 advant">
        			<h3><span>Ни копейки больше</span> как прописано в договоре.</h3>
					<p>Фиксированная стоимость. В договоре мы четко прописываем объём оказываемых услуг и стоимость. Никаких доплат с Вашей стороны.</p>
        		</div>
        		<div class="col-xs-12 col-lg-6 advant">
        			<h3>Вы <span>не ожидаете</span> часами, когда вам перезвонят.</h3>
					<p>Среднее время ответа на Ваш вопрос составляет <span>15 минут</span>. Вы всегда можете оставить запрос любым удобным методом.</p>
        		</div>
        		<div class="col-xs-12 advant">
        			<h3>Вашим товарным знаком будет заниматься патентный поверенный с профессиональным стажем <span>с 1995 года</span>.</h3>
        		</div>
        		<div class="col-xs-12 col-lg-10 col-lg-offset-1">
        			<h3 class="title" style="text-align: justify;">Мы уверены, что регистрация товарного знака с оплатой за результат — это самая прозрачная форма сотрудничества.</h3>
        			<h3 class="sub-title">Вы оплачиваете только определённый результат.</h3>
        			<ul>
						<li>получение приоритета на товарный знак;</li>
						<li>положительное решение о соответствии классов МКТУ;</li>
						<li>прохождение формальной экспертизы;</li>
						<li>принятие Роспатентом ответа на возражение;</li>
						<li>положительное  решение о возможности регистрации;</li>
						<li>получение Вами свидетельства.</li>
					</ul>
        		</div>
        		<div class="col-xs-12 col-lg-6 col-lg-offset-3">
        			<div class="btn content-button red js-modal-form" data-toggle="modal" data-frm="name,tel" data-mtitle="Оставьте заявку и мы свяжемся с вами в ближайшее время" data-tomail="Заказать обратный звонок" data-success="Мы перезвоним Вам в ближайшее время." data-btn="Отправить заявку">Заказать сейчас</div>
        		</div>
        	</div>
        	<div class="row col-xs-12 delimiter">
        		<br/>
        	</div>
        	<div class="row col-xs-12">
        		<h3 class="title">Пожалуй, это одно из лучших предложений на рынке</h3>
        	</div>
        	<div class="col-xs-12 col-lg-10 col-lg-offset-1">
        		<div class="col-xs-12 col-lg-6 advant block-margin">
        			<ol>
						<li>Многие специалисты из нашей команды занимаются регистрацией товарного знака более 20 лет. Мы многое прошли.</li>
						<li>Мы никогда не врём. Обсуждая проект, мы всегда говорим только о реальных возможностях и сроках.</li>
						<li>Никаких ложных обещаний.</li>
						<li>Оплата только за результат.</li>
					</ol>
        		</div>
        		<div class="col-xs-12 col-lg-6 advant" style="background-color: transparent;">
					<div class="cert"></div>
        		</div>
        	</div>
        	<div class="row col-xs-12">
        		<h2 class="sub-title" style="text-align: center;">Регистрация товарного знака начинается с Лирейт</h2>
        	</div>
        	<div class="col-xs-12 col-lg-10 col-lg-offset-1">
				<p style="font-style: italic;">«Удовлетворение сложных требований, предъявляемых Роспатентом, непросто, и подача без помощи специалиста по товарным знакам может привести к отказу в регистрации».</p>
		    </div>
			
			
			
			
			
			<!--===============     БЛОК С ЦЕНАМИ      ==================-->
			
			<!--=========================================================-->
			
			
	        <div class="contein_price col-xs-12">
			    <h2>Регистрация товарного знака под "ключ"</h2>
			    <!--<h3>Ускорение регистрации за 8 месяцев – 0 рублей.</h3> -->
			    
			    <div class="contein_price_flex">
			 
			       <!--  <div class="price_block">
			             <div class="offer_block block-2">
			                <h4 class="H4colorAndPadding">4900 рублей</h4>
			                <div class="offer_block_text "><br>
			                    
			                    <ul>
			                        <li>Подбор классов МКТУ</li>
			                        <li>Составление и подача заявки в Роспатент</li>
			                    </ul>
			                    
			                    
		                        <div class="btn content-button red js-modal-form" data-toggle="modal" data-frm="name,tel" data-mtitle="Оставьте заявку и мы свяжемся с вами в ближайшее время" data-tomail="4900 рублей" data-success="Мы перезвоним Вам в ближайшее время." data-btn="Отправить заявку" style="margin-bottom:0px;">Заказать</div>            			                
		                    </div>
			            </div>
			        </div> -->
			        <div class="price_block">
			             <div class="offer_block block-2">
			                <h4 class="H4colorAndPadding">от 30000 рублей</h4>
			                <div class="offer_block_text "><br>
			                    <!-- ><p class="price_cost price_cost_span">УСЛУГИ:</p><br> -->
			                    <ul>
			                        
			                        <li>Подбор классов МКТУ</li>
			                        <li>Электронное делопроизводство</li>
			                        
			                        <li>Подача заявки и ведение делопроизводства</li>
			                        <li>Направление уведомлений об оплате гос пошлин</li>
			                        <li>Оплата пошлин со скидкой 30%</li>
			                        <li>Ответы на уведомления Роспатента</li>
			                        <li>Получение свидетельства</li>
			                        
			                    </ul>
			                    
			                    <!-- <br><p class="price_cost">Средний срок от обращения до получения свидетельства от нас 10 месяцев.</p>-->
			                    
			                    
		                            <div class="btn content-button red js-modal-form" data-toggle="modal" data-frm="name,tel" data-mtitle="Оставьте заявку и мы свяжемся с вами в ближайшее время" data-tomail="19000 рублей" data-success="Мы перезвоним Вам в ближайшее время." data-btn="Отправить заявку" style="margin-bottom:0px;">Заказать</div>        		
			                </div>
			            </div>
			        </div> 
			             
			        <!-- <div class="price_block">
			             <div class="offer_block block-2">
			                <h4 class="H4colorAndPadding">49000 рублей</h4>
			                <div class="offer_block_text "><br>
			                    
			                    <ul>
			                        <li>Проверка по всем возможным базам и закрытым реестрам ФИПС</li>
			                        <li>Доработка до уникальности</li>
			                        <li>Подбор классов МКТУ</li>
			                        <li>Подача заявки и ведение делопроизводства</li>
			                        <li>Оплата пошлин со скидкой 30%</li>
			                        <li>Ответы на уведомления Роспатента</li>
			                        <li>Защита от отказов</li>
			                        <li>Получение свидетельства</li>
			                    </ul>
			                    
			                    
		                            <div class="btn content-button red js-modal-form" data-toggle="modal" data-frm="name,tel" data-mtitle="Оставьте заявку и мы свяжемся с вами в ближайшее время" data-tomail="49000 рублей" data-success="Мы перезвоним Вам в ближайшее время." data-btn="Отправить заявку" style="margin-bottom:0px;">Заказать</div>
			                </div>
			            </div>
			        </div> -->
			    </div>
		    </div>
		<?php endif?>
		
		

<?php if($temp['page']=='test2'):?>

<script src="{theme_url}/js/main.js" type="text/javascript"></script>
			
			<style type="text/css">

				.main-title h1{
					/*font: 700 40px/45px 'Open Sans';*/
					font: 700 32px/45px 'Open Sans';
				}

				.main-title h2{
					font: 30px/50px 'Open Sans'; 
					text-align: right;
				}

				.main-screen{
					background-image: url('{theme_url}/images/reg-tz-main_back_opt.jpg');
					background-size: cover;
				}

				.main-title{
					background-color: rgba(51, 55, 64, 0.69);
				    /*width: fit-content;*/
				    width: 55%;
				    float: right;
				    padding: 0 20px;
				    margin: 0 35px;
				    color: white;
				    border-radius: 5px;
				    border-right: 10px solid #d92a3d;
				    text-align: right;
				}

				.main-button.btn {
				    font: 22px/50px 'Open Sans';
				    float: right;
				    padding: 0 20px;
				    margin-top: 10px;
	    			margin-bottom: 30px;
				    box-shadow: inset 0 0 5px rgba(51, 55, 64, 0.14), 3px 3px 14px rgba(0, 0, 0, 0.14);
				    color: #fff;
				    width: fit-content;
				    background-color: #d92a3d;
				    border: 0px solid transparent;
				    -webkit-transition: all 0.3s ease-out;
				    -moz-transition: all 0.3s ease-out;
				    -o-transition: all 0.3s ease-out;
				    transition: all 0.3s ease-out;
				}

				.content-button.btn {
				    font: 21px/25px 'Open Sans';
				    white-space: pre-wrap;
				    padding: 10px 20px;
				    margin-top: 10px;
	    			margin-bottom: 30px;
				    box-shadow: inset 0 0 5px rgba(51, 55, 64, 0.14), 3px 3px 14px rgba(0, 0, 0, 0.14);
				    color: #fff;
				    width: 100%;
				    background-color: rgba(51, 55, 64, 0.65);
				    border: 0px solid transparent;
				    -webkit-transition: all 0.3s ease-out;
				    -moz-transition: all 0.3s ease-out;
				    -o-transition: all 0.3s ease-out;
				    transition: all 0.3s ease-out;
				}

				.form-reg .order_form-group {
				    padding-top: 5px;
				}

				p {
					margin-top: 10px;
				}

				.form-reg p, .form-reg span{
					color: rgba(255,255,255,0.68);
				}

				.form-reg .has-error p{
					color: #ff0000;
				}

				.form-control{
					margin-top: 5px;
				}

				#im{
					background: url({theme_url}/images/Upload-icon.png) no-repeat center center; 
					height: 100px; 
					background-size:  contain; 
					opacity:  0.5; 
					margin-top: 10px; 
					cursor: pointer;
					-webkit-transition: all 0.3s ease-out;
				    -moz-transition: all 0.3s ease-out;
				    -o-transition: all 0.3s ease-out;
				    transition: all 0.3s ease-out;
				}

				#im:hover{
					opacity: 1;
					background-color: #ffffff1f;
				}

				#im.hasImage{
					background: url({theme_url}/images/ok.png) no-repeat center center;
					background-size:  contain; 
				}

				@media (max-width: 750px){
					.main-screen{
						background-image: url('{theme_url}/images/reg-tz-main_back_opt_mini.jpg');
					}
				}
			</style>
			
			
			
			
	<link rel="stylesheet" type="text/css" href="{theme_url}/css/reg-tz-main.css">
	<!--=========================== HEAD  ==========================-->
	<!--============================================================-->
			<section class="reg_head">
			    
			    <!-- ЗАДНИЙ ФОН -->
			    <div class="reg_head_bg">
			        <div class="site-bg__bottom-shadow"></div>
			    </div>
			    
			    <!-- ВЕРХНИЙ БЛОК С КОНТАКТАМИ -->
			    <article class="reg_head_contact">
			        <div><a href="#" class="reg_head_contact_consult  js-modal-form"  data-toggle="modal" data-frm="name,tel" data-mtitle="Оставьте заявку и мы свяжемся с вами в ближайшее время" data-tomail="Заказать консультацию" data-success="Мы перезвоним Вам в ближайшее время." data-btn="Заказать консультацию">Консультация специалиста</a></div>
			        <div><a href="tel:+74999384595" class="reg_head_contact_number" data-src="#modal-consult"><img src="{theme_url}/img/reg-tz-main/icon/call.svg" alt="call" class="call-icon">+7 (499) 938-45-95</a></div>
			    </article>
			    
			    <!-- БЛОК ПО СЕРЕДИНЕ С ЗАГОЛОВКОВ И ФОРМОЙ -->
			    <article class="reg_head_title_box">
			        
			       <!-- ЗАГОЛОВОК -->
			       <div class="reg_head_title">
				        <span class="triggers">	    Более 10 лет опыта   |  Страхование ответственности				</span>
				        <h1> 	                    Регистрация товарного знака в&nbsp;России			           	</h1>
				        <span class="guide">        Заполните форму и получите бесплатную консультацию,<strong>+ поиск товарного знака бесплатно</strong>				</span>
			       </div>
			       
			       <!-- ФОРМА -->
			       <div class="reg_head_form">
				        <div class="order__title">  Получить консультацию бесплатно </div>
				        <div class="order__descr">  Оставьте заявку и&nbsp;вы узнаете о&nbsp;возможности регистрации вашего товарного знака </div>
				        
				        <form class="ajax_form vertical-form" method="POST" action="https://lireate.ru/mail.php">
				            <div class="mail_to">    
				                <input type="hidden" name="mail_token" value="<?=md5(time()*5.15);?>">
								<input type="hidden" name="fuck_spamers" value="<?=md5(date('m.Y'));?>">
								<input class="js-ap-tomail" type="hidden" name="tomail" value="">
								<input type="hidden" name="mail" value="mailorder">
								<input type="hidden" name="form-type" value="order">
				            
					        <div class="input-group">
						        <img src="{theme_url}/img/reg-tz-main/icon/name.svg" alt="name-icon">
					        	<input name="name" type="text" placeholder="Введите имя" data-label="Имя пользователя">
					        </div>
				        	<div class="input-group">
						        <img src="{theme_url}/img/reg-tz-main/icon/phone.svg" alt="phone-icon">
						        <input type="tel" name="tel" data-label="Телефон" placeholder="Введите телефон*" data-req="true" class="">
					        </div>
				        	<div class="input-group">
						        <img src="{theme_url}/img/reg-tz-main/icon/email.svg" alt="email-icon">
					        	<input type="email" name="email" placeholder="Введите e-mail*" data-label="Email" data-req="true" class="">
					        </div>
					        
					        <input type="hidden" value="Получить консультацию бесплатно" name="form_subject">
					        <button type="submit" class="btn2">Отправить заявку</button>
				        	<label class="checkbox">
    						    <input type="checkbox" value="yes" name="user_data" data-req="true" data-label="Дал согласие на обработку данных" checked="">
    						    <span class="checkbox__text">Cогласен с <a href="http://lireate.ru/private_data" target="_blank">политикой сбора персональных данных</a></span>
					        </label>
					        </div>
					        <div class="s_mail_to modal_success">
								<i class="ap-tick"></i>
								<p class="m_success">Мы перезвоним Вам в ближайшее время</p>
							</div>
				        </form>
				        
			        </div>
			        
			    </article>
			    
			    <!-- ПАРТНЕРЫ -->
			    <!--<article class="partners">
			        <div class="partners_1"></div>
			        <div class="partners_2"></div>
			    </article>-->
			</section>
			
			
			
			
			
			
        	<div class="row col-xs-12">
        		<h3 class="title">3 этапа и Ваш товарный знак зарегистрирован.</h3>
        	</div>
        	<div class="col-xs-12">
	        	<br/>
	        	

	        	<br/>
	        	<div class="col-xs-12">
	        		<h2 class="sub-title">1. Получите <b>бесплатный эспресс-анализ</b> на возможность регистрации Вашего товарного знака в течение 1 часа.</h2>
	        		<p>Проводим поиск схожих обозначений по Вашим классам МКТУ (виды деятельности или/и группам товаров) по открытым источникам Роспатента. При нахождении схожих с уже зарегистрированными товарными знаками, Вы получаете отчёт о возможности регистрации Вашего товарного знака, а также рекомендации об усиления Вашего товарного знака для прохождения регистрации.</p>
        		</div>
	        	<div class="col-xs-12 col-lg-10 col-lg-offset-1" style="margin-top: 20px;">
		        	<div class="modal-content" style="background-color: rgba(51,55,64,0.85); border: 0px;">
        				<div class="modal-header" style="background-color: #333740;border-radius: 4px 4px 0 0;border: 0px;">
        					<!--<button class="close" type="button" data-dismiss="modal" style="color: #fff;text-shadow: 0 -2px 0 #000;">×</button>-->
        					<h4 class="modal-title" style="color: white;">Рекомендуем начать регистрацию с этого этапа. За час проверим знак на возможность регистрации.</h4>
						</div>
						<div class="modal-body">
						    
						    
						    
							<form class="form-reg order_form_list ajax_form_find" method="POST" action="https://lireate.ru/mail2.php" multipart enctype="multipart/form-data">
								<input type="hidden" name="mail_token" value="<?=md5(microtime(true));?>">
								<input type="hidden" name="fuck_spamers" value="<?=md5(date('m.Y'));?>">
								<input type="hidden" name="tomail" value="<?=$temp['db_page'][$temp['pagekey']]['name'];?>">
								<input type="hidden" name="mail" value="mailorder">
								<input type="hidden" name="MAX_FILE_SIZE" value="20000000">
								<input type="hidden" name="form-type" value="files">
								<!--<select id="type_tz" name="type_tz" class="form-control order_form_control" style="display:none;">
	                                <option value="Словесное">Словесное</option>
	                                <option value="Изобразительное">Изобразительное</option>
	                                <option value="Комбинированное">Комбинированное</option>
	                            </select>-->
			                    <div class="mail_to">
									<div class="inline-unit clearfix">
                                        <div class="col-xs-12 col-sm-6">
											<div data-frm="brand" class="br-field order_form-group brandError vError">
												<p class="form-label fl-field" style="text-indent: 0px; margin-bottom: 0px;">Как называется ваш товарный знак?</p>
												<input id="brand" name="brand" type="text" class="form-control order_form_control" placeholder="Название товарного знака" required>
											</div>
											<div data-frm="userfile" class="fl-field order_form-group emailError vError">
												<p class="form-label fl-field" style="text-indent: 0px; margin-bottom: 0px;">Файл с изображением товарного знака:</p>
												<div id="im">
												</div>
												<input id="file" type="file" name="userfile[]" accept="image/gif,image/jpeg,image/png" class="order_form-group frm-input" multiple="" style="display: none;">
											</div>
                                        </div>
                                        <div class="col-xs-12 col-sm-6">
											<!--<div data-frm="gorod" class="order_form-group gorodError vError">
												<p class="form-label fl-field" style="text-indent: 0px; margin-bottom: 0px;">Ваш город</p>
												<input name="gorod" type="text" class="form-control order_form_control" placeholder="Ваш город" required>
											</div>-->
											
											
											<div data-frm="order_block3" class="order_form-group type_tzError vError">
											    <p class="form-label" style="text-indent: 0px; padding-bottom:5px;">Куда отправить результаты?</p>
    				                            <select id="order_block3" name="order_block" style="border-radius:2px;" class="form-control order_form_control">
    				                                <option value="На whatsapp">На whatsapp</option>
    				                                <option value="На viber">На viber</option>
    				                                <option value="На telegram">На telegram</option>
    				                                <option value="По почте">На почту</option>
    				                            </select>
				                            </div>  
											
											
											
											<div data-frm="email" class="email2 order_form-group emailError vError hidden">
												<p class="form-label" style="text-indent: 0px;">Ваш e-mail</p>
												<input name="email" type="text" id="email2" class="form-control order_form_control" style="margin-top: 5px;" placeholder="Ваш e-mail" pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,3}$">
											</div>
											<div data-frm="tel" class="telephon2  order_form-group telError vError">
												<p class="form-label" style="text-indent: 0px;">Ваш телефон</p>
												<input name="tel" type="tel" id="telephone2"  class="tel form-control order_form_control" style="margin-top: 5px;" placeholder="Ваш телефон" required>
											</div>
											
                                        </div>
    									<div class="col-xs-12">
				                        	<center style="padding-top: 20px;padding-bottom: 10px;"><button id="submit" type="button" class="btn btn_red btn_orange_bold order_form_btn">Отправить на проверку</button></center>
				                        	
				                        	
				                        	<div id="prog" class="hidden">
					                    	    <div class="progress">
													<div id="progressBar" class="progress-bar progress-bar-warning progress-bar-striped active" style="min-width: 10%;">0%</div>
												</div>
												<p id="status">загрузка файлов</p>
											</div>
											<p class="text-center hidden" id="ermsg">Пожалуйста заполните все поля!</p>
    									<p style="font-size: 12px; margin-top: 10px; text-align: justify; line-height: 14px;">
                                          <input type="checkbox" checked required style="vertical-align: middle; margin: 0 5px 0 0;">
                                          Нажимая на кнопку, вы даете 
                                          <a href="/private_data" target="_blank">согласие на обработку персональных данных</a> 
                                          и соглашаетесь c 
                                          <a href="/politika_konfidencialnosti" target="_blank">политикой конфиденциальности</a>
                                        </p>
    									</div>
			                        </div>
			                    	<div class="s_mail_to">Отправлено!</div>
			                    </div>
			                </form>
			                
			                
			                <script type="text/javascript">
			                	$('#im').click(function(){$('#file').click()});
			                	$('#file').change(function(){
			                		if($('#file')[0].files.length>0){
			                			$('#im').addClass('hasImage');
			                		}else{
			                			$('#im').removeClass('hasImage');
			                		}
			                	});
			                </script>
			            </div>
					</div>
		        	<!--<div class="btn content-button red" data-toggle="modal" data-target="#find-modal" id="_bbut">Получить отчет о возможности регистрации моего товарного знака</div>-->
		        </div>
	        	<br/>
	        	<br/>
	        	<div class="col-xs-12">
        			<h2 class="sub-title">2. Получение Вами <b>гарантии</b>.</h2>
        			<p>Наступает самый ответственный этап. Здесь мы проводим экспертизу Вашего наименования по данным на регистрацию заявкам и доработка Вашего товарного знака для прохождения 100% регистрации. Если есть хоть 1% сомнения в прохождения регистрации мы его устраняем. После возможной доработки наименования до патентопригодности товарного знака, сохраняя его узнаваемость, формируем пакет документов и отправляем в Роспатент в электронном виде посредством элентронной цифровой подписи.</p>
        		</div>
        		<br/>
	        	<br/>
	        	<div class="col-xs-12">
	        		<h2 class="sub-title">3. Ваш товарный знак <b>защищён</b>.</h2>
        			<p>С даты получения Роспатентом заявки Вы получаете приоритет. Приоритет означает, что с этой даты, поданные  схожие знаки не будут зарегистрированы. Мы ведем делопроизводство под «ключ», не отвлекая Вас от решения задач в Вашем бизнесе. На все запросы Роспатента отвечают наши специалисты. По окончании процедуры регистрации мы вручаем Вам свидетельство на товарный знак.</p>
        		</div>
        		<br/>
	        	<br/>
        	</div>
        	<div class="row col-xs-12 delimiter">
        		<br/>
        	</div>
        	<div class="row col-xs-12">
        		<h3 class="title">ВАШИ ПРЕИМУЩЕСТВА:</h3>
        	</div>
        	<div class="row col-xs-12" style="padding: 0px 30px;">
        		<div class="col-xs-12 col-lg-6 advant">
        			<h3>Процесс регистрации идет <span>быстрее</span>.</h3>
					<p>Заявка и дальнейшая переписка с ФИПС ведется электронно. При подаче заявки она поступает мгновенно и регистрируется. Ориентируясь на наш опыт - Вы раньше получите свидетельство в среднем на 2 месяца.</p>
        		</div>
        		<div class="col-xs-12 col-lg-6 advant">
        			<h3>Вы <span>гарантированно</span> получите свидетельство на товарный знак.</h3>
					<p>Либо мы <span>компенсируем вам 110%</span> потраченных Вами средств.</p>
        		</div>
        		<div class="col-xs-12 col-lg-6 advant">
        			<h3><span>Ни копейки больше</span> как прописано в договоре.</h3>
					<p>Фиксированная стоимость. В договоре мы четко прописываем объём оказываемых услуг и стоимость. Никаких доплат с Вашей стороны.</p>
        		</div>
        		<div class="col-xs-12 col-lg-6 advant">
        			<h3>Вы <span>не ожидаете</span> часами, когда вам перезвонят.</h3>
					<p>Среднее время ответа на Ваш вопрос составляет <span>15 минут</span>. Вы всегда можете оставить запрос любым удобным методом.</p>
        		</div>
        		<div class="col-xs-12 advant">
        			<h3>Вашим товарным знаком будет заниматься патентный поверенный с профессиональным стажем <span>с 1995 года</span>.</h3>
        		</div>
        		<div class="col-xs-12 col-lg-10 col-lg-offset-1">
        			<h3 class="title" style="text-align: justify;">Мы уверены, что регистрация товарного знака с оплатой за результат — это самая прозрачная форма сотрудничества.</h3>
        			<h3 class="sub-title">Вы оплачиваете только определённый результат.</h3>
        			<ul>
						<li>получение приоритета на товарный знак;</li>
						<li>положительное решение о соответствии классов МКТУ;</li>
						<li>прохождение формальной экспертизы;</li>
						<li>принятие Роспатентом ответа на возражение;</li>
						<li>положительное  решение о возможности регистрации;</li>
						<li>получение Вами свидетельства.</li>
					</ul>
        		</div>
        		<div class="col-xs-12 col-lg-6 col-lg-offset-3">
        			<div class="btn content-button red js-modal-form" data-toggle="modal" data-frm="name,tel" data-mtitle="Оставьте заявку и мы свяжемся с вами в ближайшее время" data-tomail="Заказать обратный звонок" data-success="Мы перезвоним Вам в ближайшее время." data-btn="Отправить заявку">Заказать сейчас</div>
        		</div>
        	</div>
        	<div class="row col-xs-12 delimiter">
        		<br/>
        	</div>
        	<div class="row col-xs-12">
        		<h3 class="title">Пожалуй, это одно из лучших предложений на рынке</h3>
        	</div>
        	<div class="col-xs-12 col-lg-10 col-lg-offset-1">
        		<div class="col-xs-12 col-lg-6 advant block-margin">
        			<ol>
						<li>Многие специалисты из нашей команды занимаются регистрацией товарного знака более 20 лет. Мы многое прошли.</li>
						<li>Мы никогда не врём. Обсуждая проект, мы всегда говорим только о реальных возможностях и сроках.</li>
						<li>Никаких ложных обещаний.</li>
						<li>Оплата только за результат.</li>
					</ol>
        		</div>
        		<div class="col-xs-12 col-lg-6 advant" style="background-color: transparent;">
					<div class="cert"></div>
        		</div>
        	</div>
        	<div class="row col-xs-12">
        		<h2 class="sub-title" style="text-align: center;">Регистрация товарного знака начинается с Лирейт</h2>
        	</div>
        	<div class="col-xs-12 col-lg-10 col-lg-offset-1">
				<p style="font-style: italic;">«Удовлетворение сложных требований, предъявляемых Роспатентом, непросто, и подача без помощи специалиста по товарным знакам может привести к отказу в регистрации».</p>
		    </div>
			
			
			<!--===============     БЛОК С ЦЕНАМИ      ==================-->
			
			<!--=========================================================-->
			
			
	        <div class="contein_price col-xs-12">
			    <h2>Регистрация товарного знака под "ключ"</h2>
			    <!--<h3>Ускорение регистрации за 8 месяцев – 0 рублей.</h3> -->
			    
			    <div class="contein_price_flex">
			 
			       <!--  <div class="price_block">
			             <div class="offer_block block-2">
			                <h4 class="H4colorAndPadding">4900 рублей</h4>
			                <div class="offer_block_text "><br>
			                    
			                    <ul>
			                        <li>Подбор классов МКТУ</li>
			                        <li>Составление и подача заявки в Роспатент</li>
			                    </ul>
			                    
			                    
		                        <div class="btn content-button red js-modal-form" data-toggle="modal" data-frm="name,tel" data-mtitle="Оставьте заявку и мы свяжемся с вами в ближайшее время" data-tomail="4900 рублей" data-success="Мы перезвоним Вам в ближайшее время." data-btn="Отправить заявку" style="margin-bottom:0px;">Заказать</div>            			                
		                    </div>
			            </div>
			        </div> -->
			        <div class="price_block">
			             <div class="offer_block block-2">
			                <h4 class="H4colorAndPadding">30 000 рублей</h4>
			                <div class="offer_block_text "><br>
			                    <!-- ><p class="price_cost price_cost_span">УСЛУГИ:</p><br> -->
			                    <ul>
			                        
			                        <li>Подбор классов МКТУ</li>
			                        <li>Электронное делопроизводство</li>
			                        
			                        <li>Подача заявки и ведение делопроизводства</li>
			                        <li>Направление уведомлений об оплате гос пошлин</li>
			                        <li>Оплата пошлин со скидкой 30%</li>
			                        <li>Ответы на уведомления Роспатента</li>
			                        <li>Получение свидетельства</li>
			                        
			                    </ul>
			                    
			                    <!-- <br><p class="price_cost">Средний срок от обращения до получения свидетельства от нас 10 месяцев.</p>-->
			                    
			                    
		                            <div class="btn content-button red js-modal-form" data-toggle="modal" data-frm="name,tel" data-mtitle="Оставьте заявку и мы свяжемся с вами в ближайшее время" data-tomail="19000 рублей" data-success="Мы перезвоним Вам в ближайшее время." data-btn="Отправить заявку" style="margin-bottom:0px;">Заказать</div>        		
			                </div>
			            </div>
			        </div> 
			             
			        <!-- <div class="price_block">
			             <div class="offer_block block-2">
			                <h4 class="H4colorAndPadding">49000 рублей</h4>
			                <div class="offer_block_text "><br>
			                    
			                    <ul>
			                        <li>Проверка по всем возможным базам и закрытым реестрам ФИПС</li>
			                        <li>Доработка до уникальности</li>
			                        <li>Подбор классов МКТУ</li>
			                        <li>Подача заявки и ведение делопроизводства</li>
			                        <li>Оплата пошлин со скидкой 30%</li>
			                        <li>Ответы на уведомления Роспатента</li>
			                        <li>Защита от отказов</li>
			                        <li>Получение свидетельства</li>
			                    </ul>
			                    
			                    
		                            <div class="btn content-button red js-modal-form" data-toggle="modal" data-frm="name,tel" data-mtitle="Оставьте заявку и мы свяжемся с вами в ближайшее время" data-tomail="49000 рублей" data-success="Мы перезвоним Вам в ближайшее время." data-btn="Отправить заявку" style="margin-bottom:0px;">Заказать</div>
			                </div>
			            </div>
			        </div> -->
			    </div>
		    </div>
		<?php endif?>		
		
<?php if($temp['page']=='test'):?>
			
<link rel="stylesheet" type="text/css" href="{theme_url}/css/test6.css">	


<style>
    .content {
        padding-left: 0px;}

</style>

<!--============================================================-->
<!--=========================== HEAD  ==========================-->
<!--============================================================-->
			
			<section class="reg_head">
			    
			    <!-- ЗАДНИЙ ФОН -->
			    <div class="reg_head_bg">
			        <div class="site-bg__bottom-shadow"></div>
			    </div>
			    
			    <!-- ВЕРХНИЙ БЛОК С КОНТАКТАМИ -->
			    <article class="reg_head_contact">
			        <a href="#" class="reg_head_contact_logo"></a>
			        <div style="height:50px;" ><a href="#" class="reg_head_contact_consult  js-modal-form"  data-toggle="modal" data-frm="name,tel" data-mtitle="Оставьте заявку и мы свяжемся с вами в ближайшее время" data-tomail="Заказать консультацию" data-success="Мы перезвоним Вам в ближайшее время." data-btn="Заказать консультацию">Консультация специалиста</a></div>
			        <div style="height:50px;" ><a href="tel:+74999384595" class="reg_head_contact_number" data-src="#modal-consult"><img src="{theme_url}/img/reg-tz-main/icon/call.svg" alt="call" class="call-icon">+7 (499) 938-45-95</a></div>
			    </article>
			    
			    <!-- БЛОК ПО СЕРЕДИНЕ С ЗАГОЛОВКОВ И ФОРМОЙ -->
			    <article class="reg_head_title_box">
			        
			       <!-- ЗАГОЛОВОК -->
			       <div class="reg_head_title">
				        <span class="triggers">	    Более 10 лет опыта   |  Страхование ответственности				</span>
				        <h1> 	                    Регистрация товарного знака в&nbsp;России			           	</h1>
				        <span class="guide">        Заполните форму и получите бесплатную консультацию,<strong>+ поиск товарного знака бесплатно</strong>				</span>
			       </div>
			       
			       <!-- ФОРМА -->
			       <div class="reg_head_form">
				        <div class="order__title">  Получить консультацию бесплатно </div>
				        <div class="order__descr">  Оставьте заявку и&nbsp;вы узнаете о&nbsp;возможности регистрации вашего товарного знака </div>
				        
				        <form class="ajax_form vertical-form" method="POST" action="https://lireate.ru/mail.php">
				            <div class="mail_to">    
				                <input type="hidden" name="mail_token" value="<?=md5(time()*5.15);?>">
								<input type="hidden" name="fuck_spamers" value="<?=md5(date('m.Y'));?>">
								<input class="js-ap-tomail" type="hidden" name="tomail" value="">
								<input type="hidden" name="mail" value="mailorder">
								<input type="hidden" name="form-type" value="order">
				            
					        <div class="input-group">
						        <img src="{theme_url}/img/reg-tz-main/icon/name.svg" alt="name-icon">
					        	<input name="name" type="text" placeholder="Введите имя" data-label="Имя пользователя">
					        </div>
				        	<div class="input-group">
						        <img src="{theme_url}/img/reg-tz-main/icon/phone.svg" alt="phone-icon">
						        <input type="tel" name="tel" data-label="Телефон" placeholder="Введите телефон*" data-req="true" class="">
					        </div>
				        	<div class="input-group">
						        <img src="{theme_url}/img/reg-tz-main/icon/email.svg" alt="email-icon">
					        	<input type="email" name="email" placeholder="Введите e-mail*" data-label="Email" data-req="true" class="">
					        </div>
					        
					        <input type="hidden" value="Получить консультацию бесплатно" name="form_subject">
					        <button type="submit" class="btn2">Отправить заявку</button>
				        	<label class="checkbox">
    						    <input type="checkbox" value="yes" name="user_data" data-req="true" data-label="Дал согласие на обработку данных" checked="">
    						    <span class="checkbox__text">Cогласен с <a href="http://lireate.ru/private_data" target="_blank">политикой сбора персональных данных</a></span>
					        </label>
					        </div>
					        <div class="s_mail_to modal_success">
								<i class="ap-tick"></i>
								<p class="m_success">Мы перезвоним Вам в ближайшее время</p>
							</div>
				        </form>
				        
			        </div>
			        
			    </article>
			    
			    <!-- ПАРТНЕРЫ -->
			    <!--<article class="partners">
			        <div class="partners_1"></div>
			        <div class="partners_2"></div>
			    </article>-->
			</section>
			
			
			<style>
			
			    /* =======   СТИЛИ СЕКЦИИ    ========= */
			    .first_section{
			        padding:50px 20px;
			        width:100%; 
			        background:black;}
			    
			    /*  =======   ЗАГОЛОВОК БЛОКА    ========= */
			    .first_section_title{
			        max-width:1400px;
			        margin:0 auto; 
			        font-size:50px; 
			        font-family: "Museo Sans Cyrl 900", Arial, sans-serif;
			        color:white; }
			
			    .first_section_flex{
			        align-items:center;
			        width:100%; max-width:1400px;
			        padding:25px 20px;
			        display:flex;
			        justify-content:space-around;}
			    
			    .first_step{
			        width:65%;
			        text-align:left;}
			    
			    .first_step_title{
			        font-size:25px; 
			        font-family: "Museo Sans Cyrl 900", Arial, sans-serif;
			        color:white;}
			       
			    .first_step_description{
			        padding-top:20px;
			        font-size:18px; 
			        font-family: "Proxima_Nova", Arial, sans-serif;
			        color:white;}
			     
			    .first_section_form{
			        width:15%;}
			    
			    
			    .first_section_image_1{
			        background-size:cover; 
			        width:160px; 
			        height:160px; 
			        margin:0 auto;}
			    
			    
			    
			    @media(max-width:950px){
                    .first_section_title{
                        font-size:30px;}}
    
                @media(max-width:480px){
                    .first_section_title{
                        font-size:24px;}
                        
                    .first_step_title{
                        font-size:18px;}
                    
                    .first_step_description{
			            font-size:14px; }}
                
                        
                        
                @media (max-width:850px){
                    
                    .first_section_flex{
                        flex-wrap:wrap;}
                    
                    .first_step{
                        width:100%;} 
                    
                    .first_section_form{
    			        display:none;}
    			     .first_step{
			            width:100%;   
                }}
                    
                       
            @media (max-width: 970px){
                .hamburger {
                    display: none !important;}
                }         
           
            
        
    
           
			</style>
			
<!--=============================================================-->			
<!--=== 1 СЕКЦИЯ - 3 ЭТАПА И ВАШ ТОВАРНЫЙ ЗНАК ЗАРЕГЕСТИРОВАН ===-->
<!--=============================================================-->

			<section class="first_section">
			    
			    <!-- ==== ЗАГОЛОВОК РАЗДЕЛА ==== -->
			    <article class="first_section_title">3 этапа и Ваш товарный знак зарегистрирован<article>
			   

			    <article class="first_section_flex">
			        <div class="first_section_form"><div class="first_section_image_1" style="  background-image:url('{theme_url}/img/test/icon/express.png');"></div></div>
			        <div class="first_step">
			            <div class="first_step_title" style=""> 1. Получите бесплатный эспресс-анализ на возможность регистрации Вашего товарного знака в течение 1 часа</div>
			            <div class="first_step_description"> Проводим поиск схожих обозначений по Вашим классам МКТУ (виды деятельности или/и группам товаров) по открытым источникам Роспатента. При нахождении схожих с уже зарегистрированными товарными знаками, Вы получаете отчёт о возможности регистрации Вашего товарного знака, а также рекомендации об усиления Вашего товарного знака для прохождения регистрации. </div>
			        </div>
			    </article>
			    
			    <article class="first_section_flex">
			        <div class="first_section_form">
			        <div class="first_section_image_1" style="background-image:url('{theme_url}/img/reg-tz-main/icon/guarantee.png');"></div></div>
			        <div class="first_step">
			            <div class="first_step_title"> 2. Получение Вами гарантии</div>
			            <div class="first_step_description"> Наступает самый ответственный этап. Здесь мы проводим экспертизу Вашего наименования по данным на регистрацию заявкам и доработка Вашего товарного знака для прохождения 100% регистрации. Если есть хоть 1% сомнения в прохождения регистрации мы его устраняем. После возможной доработки наименования до патентопригодности товарного знака, сохраняя его узнаваемость, формируем пакет документов и отправляем в Роспатент в электронном виде посредством элентронной цифровой подписи.</div>
			        </div>
			    </article>
			    
			    <article class="first_section_flex">
			        <div class="first_section_form"><div class="first_section_image_1" style="background-image:url('{theme_url}/img/reg-tz-main/icon/security.png');"></div></div>
			        <div class="first_step">
			            <div class="first_step_title" style=""> 3. Ваш товарный знак защищён</div>
			            <div class="first_step_description" style=""> С даты получения Роспатентом заявки Вы получаете приоритет. Приоритет означает, что с этой даты, поданные схожие знаки не будут зарегистрированы. Мы ведем делопроизводство под «ключ», не отвлекая Вас от решения задач в Вашем бизнесе. На все запросы Роспатента отвечают наши специалисты. По окончании процедуры регистрации мы вручаем Вам свидетельство на товарный знак.</div>
			        </div>
			    </article>
			    
			</section>
			
		
		
<!--=================================================================================-->		
<!--===============     БЛОК С ПРЕИМУШЕСТВАМИ      ==================================-->
<!--=================================================================================-->

		<section class="advantage">
		    
		    <!--===============     ЗАГОЛОВОК      ==================-->
		    <article class="advantage_title">ВАШИ ПРЕИМУШЕСТВА</article>
		    
		    <!--===============     FLEX 1      ==================-->
		    <article class="advantage_flex_1">
		        
		        <div class="advantage_flex_1_block">
		            <div class="advantage_flex_1_block_img advantage_flex_1_block_img_1"></div>
		            <div class="advantage_flex_1_block_discription">
		                <div class="advantage_flex_1_block_title">Процесс регистрации идет <span>быстрее</span></div>
		                <div class="advantage_flex_1_block_text">Заявка и дальнейшая переписка с ФИПС ведется электронно. При подаче заявки она поступает мгновенно и регистрируется. Ориентируясь на наш опыт - Вы раньше получите свидетельство в среднем на 2 месяца.</div>
		            </div>
		        </div>
		        
		        <div class="advantage_flex_1_block">
		            <div class="advantage_flex_1_block_img advantage_flex_1_block_img_2"></div>
		            <div class="advantage_flex_1_block_discription">
		                <div class="advantage_flex_1_block_title">Вы <span>гарантированно</span> получите свидетельство на товарный знак</div>
		                <div class="advantage_flex_1_block_text">Либо мы компенсируем вам 110% потраченных Вами средств</div>
		            </div>
		        </div>
		        
		        <div class="advantage_flex_1_block">
		            <div class="advantage_flex_1_block_img advantage_flex_1_block_img_3"></div>
		            <div class="advantage_flex_1_block_discription">
		                <div class="advantage_flex_1_block_title"><span>Ни копейки больше </span>как прописано в договоре</div>
		                <div class="advantage_flex_1_block_text">Фиксированная стоимость. В договоре мы четко прописываем объём оказываемых услуг и стоимость. Никаких доплат с Вашей стороны.</div>
		            </div>
		        </div>
		        
		         
		        
		    </article>
		    <!--===============     FLEX 2      ==================-->
		    <article class="advantage_flex_2">
		        
		        <div class="advantage_flex_1_block">
		            <div class="advantage_flex_1_block_img advantage_flex_1_block_img_4"></div>
		            <div class="advantage_flex_1_block_discription">
		                <div class="advantage_flex_1_block_title">Вы <span>не ожидаете </span> часами, когда вам перезвонят.</div>
		                <div class="advantage_flex_1_block_text">Среднее время ответа на Ваш вопрос составляет 15 минут. Вы всегда можете оставить запрос любым удобным методом.</div>
		            </div>
		        </div>
		        
		         <div class="advantage_flex_1_block">
		            <div class="advantage_flex_1_block_img advantage_flex_1_block_img_5"></div>
		            <div class="advantage_flex_1_block_discription">
		                <div class="advantage_flex_1_block_title">Профессиональный <span>патентный поверенный</span></div>
		                <div class="advantage_flex_1_block_text">Вашим товарным знаком будет заниматься патентный поверенный с профессиональным стажем с 1995 года.</div>
		            </div>
		        </div>

		    </article>
		    
		</section>
		
		
<!--=================================================================================-->		
<!--===============     БЛОК С ПРЕИМУШЕСТВОМ ОПЛАТЫ ЗА РЕЗУЛЬТАТ    =================-->
<!--=================================================================================-->

<section class="advantage2">    
    <div class="advantage2_title">  Мы уверены, что регистрация товарного знака<span> с оплатой за результат</span> — это самая прозрачная форма сотрудничества </div>
</section>		




			
<!--=================================================================================-->		
<!--===============   SECTION_5 -  ЛУЧШЕЕ ПРЕДЛОЖЕНИЕ НА РЫНКЕ    ===================-->
<!--=================================================================================-->

<SECTION class="best_offer BO">
    
    <!-- ==================================== ЗАГОЛОВОК СЕКЦИИ     ============================================ -->
    <article class="BO_title">ПОЖАЛУЙ, ЭТО <SPAN> ОДНО<br> ИЗ ЛУЧШИХ ПРЕДЛОЖЕНИЙ </SPAN> НА РЫНКЕ</article>
    
    <!-- ====================================БЛОК С СОДЕРЖАНИЕМ СЕКЦИИ     ============================================ -->
    <ARTICLE class="BO_content"> 
    
        <DIV class="BO_content_left">
            <div class="BO_content_left_text">
                <div class="BO_content_left_number" style="background-image:url('{theme_url}/img/test/icon/number_1.png');"></div>
                <div class="BO_content_left_description"> Многие специалисты из нашей команды занимаются регистрацией товарного знака более 20 лет. Мы многое прошли </div>
            </div>
            
            <div class="BO_content_left_text">
                <div class="BO_content_left_number" style="background-image:url('{theme_url}/img/test/icon/number_2.png');"></div>
                <div class="BO_content_left_description"> Никаких ложных обещаний </div>
            </div>
            
            <div class="BO_content_left_text">
                <div class="BO_content_left_number" style="background-image:url('{theme_url}/img/test/icon/number_3.png');"></div>
                <div class="BO_content_left_description"> Мы никогда не врём. Обсуждая проект, мы всегда говорим только о реальных возможностях и сроках </div>
            </div>
            
            <div class="BO_content_left_text">
                <div class="BO_content_left_number" style="background-image:url('{theme_url}/img/test/icon/number_4.png');"></div>
                <div class="BO_content_left_description"> Оплата только за результат </div>
            </div>
        </DIV>
        
        <DIV class="BO_content_right">
            <div class="BO_content_right_img"></div>
        </DIV>
    </ARTICLE>
</SECTION>





<!--=================================================================================-->		
<!--=======================   SECTION_6 -  ЦЕНЫ НА УСЛУГИ    ========================-->
<!--=================================================================================-->
<SECTION class="sec_price">
    
    <!-- =========== ЗАГОЛОВОК СЕКЦИИ     ====================== -->
    <article class="sec_price_title"> ЦЕНЫ НА НАШИ УСЛУГИ </article>
    
    <article class="sec_price_content">
        
        <div class="sec_price_content_block js-modal-form"  data-toggle="modal" data-frm="name,tel" data-mtitle="Оставьте заявку и мы свяжемся с вами в ближайшее время" data-tomail="Заказать консультацию" data-success="Мы перезвоним Вам в ближайшее время." data-btn="Заказать консультацию">
            <div class="sec_price_content_block_position">
                <div class="sec_price_content_block_animated"></div>
            </div>
            <div class="sec_price_content_block_content">
                <div class="sec_price_content_icon"><img src="{theme_url}/img/test/icon/3.svg" alt="1"></div>
                <div class="sec_price_content_description">Подача заявки плюс ведение типового делопроизводства с получением свидетельства.</div>
                <div class="sec_price_content_price">70.000 р.</div>
                <div class="sec_price_content_button">Оставить заявку</div>
             </div >
        </div>
        
        <div class="sec_price_content_block js-modal-form"  data-toggle="modal" data-frm="name,tel" data-mtitle="Оставьте заявку и мы свяжемся с вами в ближайшее время" data-tomail="Заказать консультацию" data-success="Мы перезвоним Вам в ближайшее время." data-btn="Заказать консультацию">
            <div class="sec_price_content_block_position">
                <div class="sec_price_content_block_animated"></div>
            </div>
            <div class="sec_price_content_block_content">
                <div class="sec_price_content_icon"><img src="{theme_url}/img/test/icon/2.svg" alt="1"></div>
                <div class="sec_price_content_description">Подготовка и подача документов на регистрацию в Роспатент.</div>
                <div class="sec_price_content_price">150.000 р.</div>
                <div class="sec_price_content_button">Оставить заявку</div>
            </div >
        </div>
        
        <div class="sec_price_content_block js-modal-form"  data-toggle="modal" data-frm="name,tel" data-mtitle="Оставьте заявку и мы свяжемся с вами в ближайшее время" data-tomail="Заказать консультацию" data-success="Мы перезвоним Вам в ближайшее время." data-btn="Заказать консультацию">
            <div class="sec_price_content_block_position">
                <div class="sec_price_content_block_animated"></div>
            </div>
            <div class="sec_price_content_block_content">
                <div class="sec_price_content_icon"><img src="{theme_url}/img/test/icon/1.svg" alt="1"></div>
                <div class="sec_price_content_description">Ускоренная подача заявки и&nbsp;ускоренная регистрация.<br><br>Личное совпровождение заявки Сергеем&nbsp;Мартынюком.</div>
                <div class="sec_price_content_price">300.000 р.</div>
                <div class="sec_price_content_button">Оставить заявку</div>
            </div >
        </div>
        
    </article>    
    
</SECTION>


<?php endif?>	
		
		
		
		
		
		

					    
					    
					 

		
		
		<?php if($temp['page']=='quiz'):?>
		    <header>
              <div class="width-wrap header">
                <a href="#"><img src="{theme_url}/images/logo_a2.png" alt="logo"></a>
                <div class="header-descr">Патентное агентство</div>
                <div class="header-phone">
                   <a href="tel:+74999384595" class="phone" data-lx-ym-goal="klik_po_nomeru_telefona">+7 (499) 938-45-95</a>
                </div>
              </div>
            </header>
            
            <div class="wrapper">
            <section id="main">
              <div class="width-wrap">
                <h1>ГАРАНТИРОВАННАЯ РЕГИСТРАЦИЯ ТОВАРНОГО ЗНАКА</h1>
                <p class="new-label">Пройдите небольшой опрос и получите полный расчёт стоимости регистрации вашего товарного знака, а также узнайте о возможности регистрации</p>
                <p class="new-label">БОНУС: скидка на госпошлину 30%</p>
                <p class="new-label">ПОДАРОК: сократим срок регистрации на 4-6 месяца (бесплатно)</p>
                <div class="check-block">
                  <button onclick="Marquiz.showModal('5b3c722dacbf240042f12b10')">ПРОЙТИ ОПРОС</button>
                  <img src="img/arrow.png" class="arrow2" alt="">
                </div>
              </div>
            </section>
            <footer>
                <div class="width-wrap flex">
                    <span>Головной офис: <br>г.Казань, ул.Салиха Сайдашева д.12, офис 222.<br>Телефон: +7 (499) 938-45-95<br>E-mail: info@lireate.ru</span>
                    <span><a href="/politika_konfidencialnosti" target="_blank">Политика конфиденциальности</a></span>
                </div>
                <span class="copy"> &copy; 2012-2020 lireate.ru Все права защищены. Все материалы сайта lireate.ru являются собственностью компании ООО "Лирейт")</span>
            </footer>
            <!--<form class="form-unit pb30 ajax_form" id="hidden_form" method="POST" style="display:none;">
				<div class="mail_to">
					<input type="hidden" name="mail_token" value="<?=md5(time()*5.15);?>">
					<input type="hidden" name="fuck_spamers" value="<?=md5(date('m.Y'));?>">
					<input class="js-ap-tomail" type="hidden" name="tomail" value="">
					<input type="hidden" name="mail" value="mailorder">
					<input type="hidden" name="form-type" value="quiz">
					<div class="pb30">
						<div data-frm="name" class="form-group form-unit-group vError frm-input input-group">
						    
							<input name="name" type="text" id="hidden_name" class="" placeholder="Введите имя">
						</div>
						
						<div data-frm="tel" class="form-group vError frm-input input-group">
						    
							<input name="tel" type="tel" id="hidden_tel" class="" placeholder="Введите телефон">
						</div>
						
					</div>
					<button type="submit" class=" btn-danger btn-blue-ap btn-popup js-ap-btn btn1"></button><br>
				</div>
			</form>
            <script type="text/javascript">
                var inter;
                function int_fun(){
                    if($('.leed-form__thank') != null){
                        $('#hidden_form').submit();
                        clearInterval(inter);
                    }
                    $('#hidden_name').val($('#name').value);
                    $('#hidden_tel').val($('#phone').value);
                }
                inter = setInterval(int_fun, 500);
            </script>-->
		<?php endif?>
		
		<?php if($temp['page']=='findtz'):?>
			<style type="text/css">

				.main-title h1{
					/*font: 700 40px/45px 'Open Sans';*/
					font: 700 32px/45px 'Open Sans';
				}

				.main-title h2{
					font: 16px/20px 'Open Sans'; 
					text-align: justify;
				}

				.main-screen{
					background-image: url('{theme_url}/images/findtz_back_opt.jpg');
					background-size: cover;
					background-position-y: center;
				}

				.main-title{
					background-color: rgba(51, 55, 64, 0.69);
				    /*width: fit-content;*/
				    width: 55%;
				    float: right;
				    padding: 0 20px;
				    margin: 0 35px;
				    color: white;
				    border-radius: 5px;
				    border-right: 10px solid #d92a3d;
				    text-align: right;
				}

				.main-button.btn {
				    font: 22px/50px 'Open Sans';
				    float: right;
				    padding: 0 20px;
				    margin-top: 10px;
	    			margin-bottom: 30px;
				    box-shadow: inset 0 0 5px rgba(51, 55, 64, 0.14), 3px 3px 14px rgba(0, 0, 0, 0.14);
				    color: #fff;
				    width: fit-content;
				    background-color: #d92a3d;
				    border: 0px solid transparent;
				    -webkit-transition: all 0.3s ease-out;
				    -moz-transition: all 0.3s ease-out;
				    -o-transition: all 0.3s ease-out;
				    transition: all 0.3s ease-out;
				}

				.content-button.btn {
				    font: 21px/25px 'Open Sans';
				    white-space: pre-wrap;
				    padding: 10px 20px;
				    margin-top: 10px;
	    			margin-bottom: 30px;
				    box-shadow: inset 0 0 5px rgba(51, 55, 64, 0.14), 3px 3px 14px rgba(0, 0, 0, 0.14);
				    color: #fff;
				    width: 100%;
				    background-color: rgba(51, 55, 64, 0.65);
				    border: 0px solid transparent;
				    -webkit-transition: all 0.3s ease-out;
				    -moz-transition: all 0.3s ease-out;
				    -o-transition: all 0.3s ease-out;
				    transition: all 0.3s ease-out;
				}

				@media (max-width: 970px){
					.main-title h2{
						font: 16px/20px 'Open Sans' !important;
						text-align: justify !important;
					}
					.main-tit{
						font: 700 30px/36px 'Open Sans';
					}
				}

				@media (max-width: 750px){
					.main-screen{
						background-image: url('{theme_url}/images/findtz_back_opt_mini.jpg');
						background-position-y: bottom;
					}
					.main-tit{
						font: 700 26px/31px 'Open Sans';
					}
				}

				@media (max-width: 520px){
					.main-tit{
						font: 700 22px/27px 'Open Sans';
					}
				}

				@media (min-width: 971px){
					.advant h3{
						font-size: 28px;
					}
					.advant p{
						font-size: 20px;
					}
					.main-tit{
						font: 700 30px/36px 'Open Sans';
					}
				}

				.icons-block h2{
					font: 600 19px/28px "Open Sans",sans-serif;
				}

				.icons-block p{
					font: 400 14px/20px "Open Sans",sans-serif;
    				text-align: center;
    				margin-bottom: 50px;
				}

				.icon{
					height: 120px;
				    background-repeat: no-repeat;
				    background-position: center center;
				}

				.num{
					width: 30px;
				    height: 30px;
				    border-radius: 15px;
				    margin: auto;
				    background-color: #5c6065;
				    color: white;
				    font-weight: bold;
				    font-size: 20px;
				    line-height: 30px;
				}
				.delimiter2{
					background-image: url('{theme_url}/images/bg-dark.jpg');
					padding-bottom: 20px;
    				background-position: center;
    				background-size: cover;
				}
				.delimiter2 .title{
					color: #f5f5f5;
					font-size: 25px;
				}
				.delimiter2 .sub-title{
					color: #f5f5f5;
					font-size: 20px;
				}
				.delimiter2 .btn:hover{
    				background-color: white;
    				color: #333740;
				}
				.form-reg .order_form-group {
				    padding-top: 5px;
				}

				p {
					margin-top: 10px;
				}

				.form-reg p, .form-reg span{
					color: rgba(255,255,255,0.68);
					margin-top: 0px
				}

				.form-reg .has-error p{
					color: #ff0000;
				}

				/*.advant{
					margin-bottom: 10px;
    				margin-top: 10px;
    				margin-left: 16.66666667%;
				}
				.advant p{
					text-align: center;
				}*/

			</style>
			<script>
            	window.onload = function() {
					$('#type_tz').change(function(){
					    switch (this.value){
					    	case 'Словесное':
						    	$('#brand').prop('required',true);
						    	$('#file').prop('required',false);
						    	$('.fl-field').addClass('hidden');
						    	$('.br-field').removeClass('hidden');
						    	break;
						    case "Изобразительное":
					    		$('#brand').prop('required',false);
					    		$('#file').prop('required',true);
					    		$('.fl-field').removeClass('hidden');
					    		$('.br-field').addClass('hidden');
						    	break;
					        case "Комбинированное":
						        $('#brand').prop('required',true);
						    	$('#file').prop('required',true);
						    	$('.fl-field').removeClass('hidden');
						    	$('.br-field').removeClass('hidden');
						    	break;
					    }
					});
					$('#type_tz').change();
					$('#brand2').change(function(){$('input[name=brand]').val($('#brand2').val())});
					
					function select_change(){
					    switch (this.value){
					    	case "На whatsapp":
					    		$('#telephone4').attr('required', true);
					    		$('#email4').attr('required',false);
					    		$('.email1').addClass('hidden');
						    	break;
					        case "На viber":
						        $('#telephone4').attr('required',true);
					    		$('#email4').attr('required',false);
					    		$('.email1').addClass('hidden');
						    	break;
						     case "На telegram":
						        $('#telephone4').attr('required',true);
					    		$('#email4').attr('required',false);
					    		$('.email1').addClass('hidden');
						    	break;
					    	case 'По почте':
					    	    $('.email1').removeClass('hidden');
						    	$('#telephone4').attr('required',true);
						    	$('#email4').attr('required',true);
						    	break;
					    }
					}
					$('#order_block').change(select_change);
					//$('#order_block').change();
					$('#order_block2').change(select_change);
					//$('#order_block2').change();
                    
					//$('#order_block3').change();    
				
					
					/*$('#order_block2').change(function(){
					    switch (this.value){
					    	case "На whatsapp":
					    		$('#telephone').prop('required',true);
					    		$('#email').prop('required',false);
					    		$('.telephon1').removeClass('hidden');
					    		$('.email1').addClass('hidden');
						    	break;
					        case "На viber":
						        $('#telephone').prop('required',true);
					    		$('#email').prop('required',false);
					    		$('.telephon1').removeClass('hidden');
					    		$('.email1').addClass('hidden');
						    	break;
						     case "На telegram":
						        $('#telephone').prop('required',true);
					    		$('#email').prop('required',false);
					    		$('.telephon1').removeClass('hidden');
					    		$('.email1').addClass('hidden');
						    	break;
					    	case 'По почте':
						    	$('#telephone').prop('required',false);
						    	$('#email').prop('required',true);
						    	$('.telephon1').addClass('hidden');
						    	$('.email1').removeClass('hidden');
						    	break;
					    }
					});
					$('#order_block2').change();
					
						
					$('#order_block3').change(function(){
					    switch (this.value){
					    	case "На whatsapp":
					    		$('#telephone').prop('required',true);
					    		$('#email').prop('required',false);
					    		$('.telephon1').removeClass('hidden');
					    		$('.email1').addClass('hidden');
						    	break;
					        case "На viber":
						        $('#telephone').prop('required',true);
					    		$('#email').prop('required',false);
					    		$('.telephon1').removeClass('hidden');
					    		$('.email1').addClass('hidden');
						    	break;
						     case "На telegram":
						        $('#telephone').prop('required',true);
					    		$('#email').prop('required',false);
					    		$('.telephon1').removeClass('hidden');
					    		$('.email1').addClass('hidden');
						    	break;
					    	case 'По почте':
						    	$('#telephone').prop('required',false);
						    	$('#email').prop('required',true);
						    	$('.telephon1').addClass('hidden');
						    	$('.email1').removeClass('hidden');
						    	break;
					    }
					});
					$('#order_block3').change();*/
					
				}
			</script>
			<div id="find-modal" class="modal find-modal">
        		<div class="modal-dialog">
        			<div class="modal-content" style="background-color: rgba(255, 255, 255, 0.89);">
        				<div class="modal-header" style="background-color: #333740; border-radius: 4px 4px 0 0;">
        					<button class="close" type="button" data-dismiss="modal" style="color: #fff;text-shadow: 0 -2px 0 #000;">&times;</button>
        					<h4 class="modal-title" style="color: white;">Бесплатный поиск по базам Роспатента</h4>
						</div>
						<div class="modal-body">
							<form class="order_form_list ajax_form_find" method="POST" action="https://lireate.ru/mail2.php" multipart="" enctype="multipart/form-data">
								<input type="hidden" name="mail_token" value="<?=md5(microtime(true));?>">
								<input type="hidden" name="fuck_spamers" value="<?=md5(date('m.Y'));?>">
								<input type="hidden" name="tomail" value="<?=$temp['db_page'][$temp['pagekey']]['name'];?>">
								<input type="hidden" name="mail" value="mailorder">
								<input type="hidden" name="form-type" value="files">
								<input type="hidden" name="MAX_FILE_SIZE" value="20000000" />
			                    <div class="mail_to">
									<div class="inline-unit clearfix">
										<!--<div data-frm="name" class="order_form-group nameError vError">
											<input name="name" type="text" class="form-control order_form_control" placeholder="Ваше имя" required>
										</div>-->
										<p class="form-label">Куда отправить результаты?</p>
										<div data-frm="order_block" class="order_form-group type_tzError vError">
										    
										    
										    
										    
										    
				                            <select id="order_block" name="order_block" class="form-control order_form_control">
				                                <option value="На whatsapp">На whatsapp</option>
				                                <option value="На viber">На viber</option>
				                                <option value="На telegram">На telegram</option>
				                                <option value="По почте">На почту</option>
				                            </select>
				                            
				                        </div>       
				                            
				                        
										
										<div data-frm="email" class="email1 order_form-group emailError vError hidden">
											<input name="email" id="email4" type="text" class="form-control order_form_control" placeholder="Ваш e-mail" pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,3}$" required>
										</div>
										
										<div data-frm="tel" class="telephon1 order_form-group telError vError">
											<input name="tel" id="telephone4" type="tel" class="tel form-control order_form_control" placeholder="Ваш телефон" required>
										</div>
										
										
			                            <p class="form-label">Вид обозначения</p>
			                            <div data-frm="type_tz" class="order_form-group type_tzError vError">
				                            <select id="type_tz" name="type_tz" class="form-control order_form_control">
				                                <option value="Словесное">Словесное</option>
				                                <option value="Изобразительное">Изобразительное</option>
				                                <option value="Комбинированное">Комбинированное</option>
				                            </select>
				                        </div>
			                            <div data-frm="brand" class="br-field order_form-group brandError vError">
											<input id='brand' name="brand" type="text" class="form-control order_form_control" placeholder="Название товарного знака" required>
										</div>
										<p class="form-label fl-field" style="text-indent: 0px; margin-bottom: 0px;">Файл с изображением товарного знака:</p>
										<div data-frm="userfile" class="fl-field order_form-group emailError vError" style="margin-top: -10px;">
											<input id='file' type="file" name="userfile[]" accept="image/gif,image/jpeg,image/png" class="order_form-group frm-input" multiple> <!--multiple-->
										</div>
				                        <!--<p class="form-label" style="text">Описание видов деятельности на которое должно распространяться действие знака (необязательно)</p>
				                        <div data-frm="tz_comment" class="order_form-group tz_commentError vError">
				                        	<textarea class="form" id="tz_comment" name="tz_comment" style="width: 100%; height: 80px; resize: none;" maxlenth="100"></textarea>
				                        </div>-->
				                        <center style="margin-top: 20px; padding-bottom: 10px;"><button id="submit" type="button" class="btn btn_red btn_orange_bold order_form_btn">Отправить на проверку</button></center>
				                        <div id="prog" class="hidden">
					                        <div class="progress" >
												<div id="progressBar" class="progress-bar progress-bar-warning progress-bar-striped active" style="min-width: 10%;">0%</div>
											</div>
											<p id="status">загрузка файлов</p>
										</div>
										<p class="hidden" id="ermsg">загрузка файлов</p>
			                            <p style="font-size: 12px; margin-top: 10px; text-align: justify; line-height: 14px;">
                                          <input type="checkbox" checked required style="vertical-align: middle; margin: 0 5px 0 0;">
                                          Нажимая на кнопку, вы даете 
                                          <a href="/private_data" target="_blank">согласие на обработку персональных данных</a> 
                                          и соглашаетесь c 
                                          <a href="/politika_konfidencialnosti" target="_blank">политикой конфиденциальности</a>
                                        </p>
			                        </div>
			                    	<div class="s_mail_to">Отправлено!</div>
			                    </div>
			                </form>
			                
			            </div>

			            
						
						
						
						    
						
						
						
						
						
						
					</div>
				</div>
			</div>
			<div class="row main-screen" style="margin: 0;">
	        	<br/>
	        	<br/>
	        	<br/>
	        	<!--<div class="main-title">
	        		<h1>Всесторонний поиск товарных знаков</h1>
		        	<h2>Получите спокойствие и передовую стратегию. Убедитесь, что ваш знак доступен, прежде чем тратить время и деньги. Легко читаемый полный отчет, отправленный вам в электронном виде. Пошлина ФИПС не подлежит возврату, если ваша заявка отклонена, поэтому перед тем, как подать заявку, у вас будет некоторый комфорт. Мы проводим поиск по базам Роспатента, международной базе.</h2>
		        	<div class="btn main-button red" data-toggle="modal" data-target="#find-modal" id="_bbut">Получить отчёт!</div>
	        	</div>-->
	        	<div class="hidden-xs col-sm-1 col-sm-visible pull-right" style="transform: rotate(21deg); right: 51px; padding-left:  0px; padding-right: 0px; top: 29px; background: url('{theme_url}/images/arrow.png') no-repeat; background-size:  contain; height: 258px;">
    			</div>
    			<div class="col-sm-10 col-sm-offset-1 text-center" style="margin-top: -40px;">
                    <h1 class="main-tit" style="color: white;">Бесплатно проверим ваш товарный знак на возможность регистрации за 1 час</h1>
                </div>
	        	<div class="col-sm-7 col-sm-offset-4" style="margin-top: 20px;">
		        	<div class="modal-content pull-right" style="background-color: rgba(51,55,64,0.85);border: 0px; max-width: 450px;">
        				<div class="modal-header" style="background-color: #333740;border-radius: 4px 4px 0 0;border: 0px;">
        					<h4 class="modal-title" style="color: white; text-align: center;">Заполните форму и получите бесплатно консультацию и поиск товарного знака</h4>
						</div>
						<div class="modal-body">
							<form class="form-reg order_form_list ajax_form" method="POST">
								<input type="hidden" name="mail_token" value="<?=md5(microtime(true));?>">
								<input type="hidden" name="fuck_spamers" value="<?=md5(date('m.Y'));?>">
								<input type="hidden" name="tomail" value="<?=$temp['db_page'][$temp['pagekey']]['name'];?>">
								<input type="hidden" name="mail" value="mailorder">
								<input type="hidden" name="form-type" value="consultation">
								<!--<select id="type_tz" name="type_tz" class="form-control order_form_control" style="display:none;">
	                                <option value="Словесное">Словесное</option>
	                                <option value="Изобразительное">Изобразительное</option>
	                                <option value="Комбинированное">Комбинированное</option>
	                            </select>-->
			                    <div class="mail_to">
									<div class="inline-unit clearfix">
                                        <div class="col-xs-12">
											<div data-frm="brand" class="order_form-group brandError vError">
												<p class="form-label" style="text-indent: 0px;">Название</p>
												<input id="brand" name="brand" type="text" class="form-control order_form_control" style="margin-top: 5px;" placeholder="Название товарного знака" required>
											</div>
											
											
											<div data-frm="order_block2" class="order_form-group type_tzError vError">
											    <p class="form-label" style="text-indent: 0px; padding-bottom:5px;">Куда отправить результаты?</p>
    				                            <select id="order_block2" name="order_block" style="border-radius:2px;" class="form-control order_form_control">
    				                                <option value="На whatsapp">На whatsapp</option>
    				                                <option value="На viber">На viber</option>
    				                                <option value="На telegram">На telegram</option>
    				                                <option value="По почте">На почту</option>
    				                            </select>
				                            </div>  
											
											
											
											<div data-frm="email" class="email1  order_form-group emailError vError hidden">
												<p class="form-label" style="text-indent: 0px;">Ваш e-mail</p>
												<input name="email" type="text" id="email4" class="form-control order_form_control" style="margin-top: 5px;" placeholder="Ваш e-mail" pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,3}$">
											</div>
											<div data-frm="tel" class="telephon1  order_form-group telError vError">
												<p class="form-label" style="text-indent: 0px;">Ваш телефон</p>
												<input name="tel" type="tel" id="telephone4"  class="tel form-control order_form_control" style="margin-top: 5px;" placeholder="Ваш телефон" required>
											</div>
                                        </div>
    									<div class="col-xs-12">
				                        	<center style="padding-top: 20px;padding-bottom: 10px;">
				                        		<button id="submit2" type="submit" class="btn btn_red btn_orange_bold order_form_btn">Получить результаты</button>
				                        	</center>
											<p class="text-center hidden" id="ermsg">Пожалуйста заполните все поля!</p>
    								    <p style="font-size: 12px; margin-top: 10px; text-align: justify; line-height: 14px;">
                                          <input type="checkbox" checked required style="vertical-align: middle; margin: 0 5px 0 0;">
                                          Нажимая на кнопку, вы даете 
                                          <a href="/private_data" target="_blank">согласие на обработку персональных данных</a> 
                                          и соглашаетесь c 
                                          <a href="/politika_konfidencialnosti" target="_blank">политикой конфиденциальности</a>
                                        </p>
    									</div>
			                        </div>
			                    </div>
			                    <div class="s_mail_to">Отправлено!</div>
			                </form>
			            </div>
					</div>
		        </div>
		        
		        
		        
						
	        	<br/>
	        	<br/>
        	</div>
        	
        	<?php 
        	$b = $_GET['mode'];
        	if($b == 'test'){
        	 
        	  ?> 
        	  
        	  
<style>
        	        
.trademark_check{
    padding:50px 0;
    border-bottom:10px solid grey;}
 
    .trademark_check_title{
        padding:30px 0px 120px 0px;
        font: 700 40px/45px 'Open Sans';
        background:#333740;
        color:white;
        text-align:center;}

        .trademark_check_title_discription{
            display:flex;
            flex-wrap:wrap;
            padding: 30px 0px 0px 250px;
            justify-content:space-between;}

            .trademark_title_point{
                width:45%;
                text-align:left;
                font: 300 16px/33px 'Open Sans';}

                .trademark_title_point span{
                    background:white;
                    border-radius:50px;
                    color:black;
                    padding: 1px 8px;}
    
.trademark_check_offer_block{
    display:flex;
    justify-content:space-around;
    height:500px;
    width:60%;
    margin:0 auto;}


    .trademark_check_offer{
        margin-top:-80px;
        background:white;
        width:300px;
        border:1px solid rgba(128, 128, 128, 1);;
        box-shadow:0px 0px 11px 0px rgba(0,0,0,0.3);
        background-position:50% 70%;
    }
    
        
        .trademark_check_offer p{
            padding:20px 20px;
            text-align:center;}
            
            .trademark_check_offer p span{
                color:red;
                font-size:14px;}
        
        
    .trademark_check_offer_bc1{
        background-image: url('{theme_url}/images/22.jpg');}
    
    
    .trademark_check_offer_bc2{
        background-image: url('{theme_url}/images/223.jpg');}
    .trademark_check_offer .btn{
        width:70%;
        margin:0 auto;
        margin-top: 360px;
    }
        
</style>
        	  
        	   <section class="trademark_check">
        	       
        	       <!--================ ЗАГОЛОВОК С ОПИСАНИЕМ ==================== -->
        	       <div class="trademark_check_title">Проверка вашего товарного знака 
        	            <div class="trademark_check_title_discription">
        	                <div class="trademark_title_point"><span>1</span>  Среди зарегистрированных товарных знаков РФ</div>
        	                <div class="trademark_title_point"><span>2</span>  Поданных на регистрацию заявки РФ</div>
                            <div class="trademark_title_point"><span>3</span>  Международные регистрации, действующие на территории РФ</div>
                            <div class="trademark_title_point"><span>4</span>  Проверка абсолютных оснований для отказа</div>
                            <div class="trademark_title_point"><span>5</span>  Анализ выявленных противопоставлений</div>
                            <div class="trademark_title_point"><span>6</span>  Оценка успешной регистрации</div>
        	            </div>
        	       </div>
        	       
        	       <!--================ БЛОКС ПРЕДЛОЖЕНИЯМИ ==================== -->
        	       <div class="trademark_check_offer_block">
        	           <div class="trademark_check_offer trademark_check_offer_bc1">
        	               <p>Только слово (словосочетание)<br><span>5000 рублей</span></p>
        	               
        	               <div class="btn content-button red" data-toggle="modal" data-target="#find-modal" id="_bbut">Заказать</div>
        	               
        	           </div>
        	           <div class="trademark_check_offer trademark_check_offer_bc2">
        	               <p>Только изображение <br><span>6000 рублей +500 рублей за каждый класс МКТУ свыше 3-х</span></p>
        	               <div class="btn content-button red" data-toggle="modal" data-target="#find-modal" id="_bbut">Заказать</div>
        	           </div>
        	       </div>
        	       
        	       
        	   </section>
        	   
        	   
        	   
        	   <?php
        	}
        	
        	?>
        	
        	
        	
        	
        	
        	
        	
        	
        	
        	
        	<div class="row col-xs-12">
        		<h3 class="title">Как это работает</h3>
        		<h4 class="sub-title">Всего 3 простых шага – и вы получите подробный отчет о поиске:</h4>
        	</div>
        	<div class="col-xs-12 icons-block">
	        	<div class="col-xs-8 col-xs-offset-2 col-md-4 col-md-offset-0">
	        		<div><div class="num">1</div></div>
	        		<div class="icon" style="background-image: url('{theme_url}/images/find1.png');"></div>
	        		<h3 class="sub-title">Вы отвечаете на вопросы.</h3>
	        		<p>Предоставьте нам базовую информацию о товарном знаке и опишите свои товары и услуги.</p>
        		</div>
	        	<div class="col-xs-8 col-xs-offset-2 col-md-4 col-md-offset-0">
	        		<div><div class="num">2</div></div>
	        		<div class="icon" style="background-image: url('{theme_url}/images/find2.png');"></div>
        			<h3 class="sub-title">Мы проводим всесторонний поиск.</h3>
        			<p>Мы ищем схожие и тождественные товарные знаки по базам данных Роспатента, международной базе, базе доменных имен для выявления возможных рисков.</p>
        		</div>
	        	<div class="col-xs-8 col-xs-offset-2 col-md-4 col-md-offset-0">
	        		<div><div class="num">3</div></div>
	        		<div class="icon" style="background-image: url('{theme_url}/images/find3.png');"></div>
	        		<h3 class="sub-title">Мы отправляем вам подробный отчет.</h3>
        			<p>В подробном отчете указаны все совпадения, включая полную регистрацию товарных знаков для Российских товарных знаков и международных.</p>
        		</div>
        		<br/>
	        	<br/>
        	</div>
        	<div class="row col-xs-12">
        		<h3 class="title">В результате проверки вашего товарного знака вы узнаете:</h3>    
        	</div>
        	<div class="col-xs-12 icons-block">
                <div class="col-xs-8 col-xs-offset-2 col-md-4 col-md-offset-0">
	        		<div><div class="num">1</div></div>
	        		<div class="icon" style="background-image: url('{theme_url}/images/find4.png');"></div>
	        		<p>О возможности регистрации вашего товарного знака</p>
        		</div>
        		<div class="col-xs-8 col-xs-offset-2 col-md-4 col-md-offset-0">
	        		<div><div class="num">2</div></div>
	        		<div class="icon" style="background-image: url('{theme_url}/images/find5.png');"></div>
	        		<p>Варианты изменения дизайна и доработки под требования Роспатента для устранения рисков при регистрации</p>
        		</div>
        		<div class="col-xs-8 col-xs-offset-2 col-md-4 col-md-offset-0">
	        		<div><div class="num">3</div></div>
	        		<div class="icon" style="background-image: url('{theme_url}/images/find6.png');"></div>
	        		<p>О стоимости регистрации товарного знака, индивидуальных сроках для вашего обозначения, а также вариантах ускорения процедуры</p>
        		</div>
    		</div>
    		<div class="row col-xs-12 col-md-4">
        		<h3 class="text-center" style="margin-top: 0px;">товарный знак в безопасности</h3>
        		<h3 class="title" style="margin-top: 0px;">ПРОВЕРКА</h3>
        	</div>
        	<div class="col-xs-12 col-md-8 icons-block">
                <div class="col-xs-8 col-xs-offset-2 col-md-6 col-md-offset-0">
	        		<div class="icon" style="display: inline-block; background-image: url('{theme_url}/images/secure1.png'); height: 50px; width: 70px; /*float: left;*/"></div>
	        		<div style="/*display: inline-block;*/">
	        			<p>повышает шансы на регистрацию</p>
	        		</div>
        		</div>
        		<div class="col-xs-8 col-xs-offset-2 col-md-6 col-md-offset-0">
	        		<div class="icon" style="display: inline-block; background-image: url('{theme_url}/images/secure2.png'); height: 50px; width: 70px; /*float: left;*/"></div>
	        		<div style="/*display: inline-block;*/">
	        			<p>экономит ваше время и деньги</p>
	        		</div>
        		</div>
    		</div>
        	<div class="row col-xs-12 delimiter2">
        		<h3 class="title">Примите меры, чтобы защитить свой товарный знак сегодня.</h3>
        		<h4 class="sub-title">Тысячи людей защитили свой бренд путём регистрации товарного знака</h4>
        		
        				<div class="col-sm-12 col-md-5 col-md-offset-1">
		        			<div data-frm="brand" class="brand-field order_form-group">
								<input id="brand2" name="brand" type="text" class="form-control order_form_control" placeholder="Название товарного знака" required style="height: 42px; font-size: 18px;" maxlength="40">
							</div>
						</div>
						<div class="col-sm-12 col-md-5 col-md-offset-1 hidden">
		        			<div data-frm="tel2" class="tel-field order_form-group">
								<input id="tel2" name="tel" type="tel" class="form-control order_form_control" placeholder="Номер телефона" required style="height: 42px; font-size: 18px;">
							</div>
						</div>
						
						<div class="col-sm-12 col-md-5 col-md-offset-0">
							<!--<div class="btn content-button red js-modal-form" data-toggle="modal" data-frm="name,tel,brand" data-mtitle="Оставьте заявку и мы свяжемся с вами в ближайшее время" data-tomail="Заказать бесплатную проверку" data-success="Мы перезвоним Вам в ближайшее время." data-btn="Заказать бесплатную проверку">Начать проверку</div>-->
	        				<!--<button type="submit" class="btn content-button red" id="_bbut2">Начать проверку</button>-->
	        				<!--<div class="btn content-button red" data-toggle="modal" data-target="#find-modal" id="_bbut2">Начать проверку</div>-->
	        				<div class="btn content-button red" id="_bbut2">Начать проверку</div>
	        			</div>
	        			
	        			<div style="float: right; width: 100%; display: flex; align-items: center; justify-content: space-around;"><p style="font-size: 12px; margin-top: 10px; text-align: justify; line-height: 14px; color:white">
                              <input type="checkbox" checked required style="vertical-align: middle; margin: 0 5px 0 0;">
                              Нажимая на кнопку, вы даете 
                              <a href="/private_data" target="_blank">согласие на обработку персональных данных</a> 
                              и соглашаетесь c 
                              <a href="/politika_konfidencialnosti" target="_blank">политикой конфиденциальности</a>
                            </p></div>
	        			<div class="col-sm-12 col-md-5 col-md-offset-0 hidden">
	        				<form id="double-form" method="POST">
	        					<input type="hidden" name="mail_token" value="<?=md5(microtime(true));?>">
								<input type="hidden" name="fuck_spamers" value="<?=md5(date('m.Y'));?>">
								<input type="hidden" name="tomail" value="<?=$temp['db_page'][$temp['pagekey']]['name'];?>">
	        					<input type="hidden" name="mailorder">
	        					<input type="hidden" name="brand" maxlength="40">
	        					<input type="hidden" name="tel">
	        					<input type="hidden" name="form-type" value="check">
	        					<button type="submit" class="btn content-button red" id="_bbut22">Начать проверку</button>
	        					
	        				</form>
	        			</div>
	        			
	        			<script type="text/javascript">
        					$('#_bbut2').click(function(){
        						var b = $('#brand2').parent();
        						var t = $('#tel2').parent();
        						var bu1 = $('#_bbut2').parent();
        						var bu2 = $('#double-form').parent();
        						b.fadeOut("fast",function(){
        							b.parent().addClass('hidden');
        							t.parent().removeClass('hidden');
        							t.fadeIn();
        							bu1.addClass('hidden');
        							bu2.removeClass('hidden');
        						});
        					});
        					var f = $('#double-form');
        					$('#brand2').change(function(){
        						$('input[name=brand]',f).val($('#brand2').val());
    						});
    						$('#tel2').change(function(){
    							$('input[name=tel]',f).val($('#tel2').val());
    						});
        				</script>
        			<!--<div class="btn content-button red js-modal-form" data-toggle="modal" data-frm="name,tel" data-mtitle="Оставьте заявку и мы свяжемся с вами в ближайшее время" data-tomail="Заказать бесплатную проверку" data-success="Мы перезвоним Вам в ближайшее время." data-btn="Заказать бесплатную проверку">А я начну с бесплатной проверки</div>-->
        		
        	</div>
        	
        	<div class="row col-xs-12">
        		<h3 class="sub-title" style="text-align: center;"><span>Не все</span> товарные знаки могут быть зарегистрированы.</h3>
        	</div>
        	<div class="col-xs-12 col-lg-10 col-lg-offset-1">
				<p>Товарные знаки не могут пройти регистрацию по разным причинам. Закажите предварительную проверку, чтобы с уверенностью в результате подать обозначение на регистрацию, а не потратить деньги на оплату пошлин зря. Наши специалисты проведут проверку вероятности регистрации вашего товарного знака. Мы точно определим, есть ли в базах идентичные или похожие обозначения, которые могут стать препятствием. Наш поверенный проанализирует результаты. Вы узнаете, какова вероятность успешной регистрации вашего товарного знака.</p>
    		</div>
        	<div class="row col-xs-12">
        		<h3 class="title">Проверка проводится:</h3>
        	</div>
        	<div class="row col-xs-12" style="padding: 0px 30px;">
        		<div class="col-xs-12 col-lg-6 advant">
        			<h3>По <span>45 классам</span> МКТУ</h3>
					<p>Один и тот же знак может быть зарегистрирован по разным классам</p>
        		</div>
        		<div class="col-xs-12 col-lg-6 advant">
        			<h3>По <span>зарегистрированным</span> товарным знакам</h3>
					<p>Они могут мешать вашей регистрации. Если срок действия знака истек — вы можете его зарегистрировать!</p>
        		</div>
        		<div class="col-xs-12 col-lg-6 col-lg-offset-3">
        			<div class="btn content-button red" data-toggle="modal" data-target="#find-modal" id="_bbut">Заказать сейчас</div>
        			<!--<div class="btn content-button red js-modal-form" data-toggle="modal" data-frm="name,tel" data-mtitle="Оставьте заявку и мы свяжемся с вами в ближайшее время" data-tomail="Заказать обратный звонок" data-success="Мы перезвоним Вам в ближайшее время." data-btn="Отправить заявку">Заказать сейчас</div>-->
        		</div>
        	</div>
		<?php endif?>
		

		
		
		
		<?php if($temp['page']=='patent'):?>
			<style type="text/css">

				.main-title h1{
					font: 700 27px/32px 'Open Sans';
					text-align: left;
				}

				.main-title h2,.main-title h3{
					font: 25px/25px 'Open Sans'; 
					text-align: left;
				}
				/*.main-title h3{
					font: 25px/25px 'Open Sans'; 
					text-align: left;
				}*/

				.main-screen{
					background-image: url('{theme_url}/images/patent_back_opt.jpg');
					background-size: cover;
				}

				.main-title{
					background-color: rgba(51, 55, 64, 0.69);
				    /*width: fit-content;*/
				    width: 55%;
				    float: right;
				    padding: 0 20px;
				    margin: 0 35px;
				    color: white;
				    border-radius: 5px;
				    border-right: 10px solid #d92a3d;
				    text-align: right;
				}

				.main-button.btn {
				    font: 22px/50px 'Open Sans';
				    float: right;
				    padding: 0 20px;
				    margin-top: 10px;
	    			margin-bottom: 30px;
				    box-shadow: inset 0 0 5px rgba(51, 55, 64, 0.14), 3px 3px 14px rgba(0, 0, 0, 0.14);
				    color: #fff;
				    width: fit-content;
				    background-color: #d92a3d;
				    border: 0px solid transparent;
				    -webkit-transition: all 0.3s ease-out;
				    -moz-transition: all 0.3s ease-out;
				    -o-transition: all 0.3s ease-out;
				    transition: all 0.3s ease-out;
				}

				.content-button.btn {
				    font: 21px/25px 'Open Sans';
				    white-space: pre-wrap;
				    padding: 10px 20px;
				    margin-top: 10px;
	    			margin-bottom: 30px;
				    box-shadow: inset 0 0 5px rgba(51, 55, 64, 0.14), 3px 3px 14px rgba(0, 0, 0, 0.14);
				    color: #fff;
				    width: 100%;
				    background-color: rgba(51, 55, 64, 0.65);
				    border: 0px solid transparent;
				    -webkit-transition: all 0.3s ease-out;
				    -moz-transition: all 0.3s ease-out;
				    -o-transition: all 0.3s ease-out;
				    transition: all 0.3s ease-out;
				}

				.sub-title2{
					font: 300 28px/32px "Open Sans",sans-serif;
    				text-align: justify;
				}

				@media (max-width: 750px){
					.main-screen{
						background-image: url('{theme_url}/images/patent_back_opt_mini.jpg');
					}
				}

				@media (max-width: 970px){
					.main-title h1{
						font: 700 24px/32px 'Open Sans' !important;
					}

					.main-title h2{
						font: 20px/24px 'Open Sans' !important;
					}
				}
			</style>
			<div class="row main-screen main_back lireate-hero-v4 lireate-cms-hero" id="main_back" style="margin: 0;"><!-- lireate_design_patent_hero_v4 -->
		<?php include dirname(__FILE__) . '/page/cms/lireate-cms-hero-patent.inc.php'; ?>
	        </div>
	        	
        	<div class="col-xs-12">
        		<h3 class="title" style="text-align: center;">Страны, в которых вы получите защиту вашего патента</h3>
        	</div>
        	<div class="row col-xs-12" style="padding: 0px 30px;">
        		<div class="col-xs-12 col-lg-6 advant">
        			<h3>РОССИЯ</h3>
					<p>Регистрация осуществляется через Роспатент. Срок патентования составляет 12-15 месяцев.</p>
        		</div>
        		<div class="col-xs-12 col-lg-6 advant">
        			<h3>США</h3>
					<p>Оформления патента в США проводит Ведомство по патентам и товарным знакам США (USPTO) Время регистрация составляет 1,5 года.</p>
        		</div>
        		<div class="col-xs-12 col-lg-6 advant">
        			<h3>КИТАЙ</h3>
					<p>В данной стране защита патента осуществляет Государственное ведомство по интеллектуальной собственности Народной Республики Китай (SIPO). Срок регистрации составляет ориентировочно 13-19 месяцев.</p>
        		</div>
        		<div class="col-xs-12 col-lg-6 advant">
        			<h3>ЕВРОСОЮЗ (38 стран)</h3>
					<p>Орган, осуществляющий регистрацию по странам Евросоюза Ведомство по гармонизации на внутреннем рынке г.Алеканте. Процедура регистрации составляет 7-8 месяцев.</p>
        		</div>
        	</div>
        	<div class="row col-xs-12" style="padding: 0px 30px;">
        		<div class="col-xs-12 col-lg-10 col-lg-offset-1">
        			<h3 class="title" style="text-align: justify;">Шаги, которые приведут к защите вашего изобретения.</h3>
        			<h4 class="sub-title2">Чтобы оценить потенциал изобретения, вы и наш специалист по патентованию будете работать вместе, чтобы ответить на следующие вопросы:</h2>
        			<ul>
						<li>Какую проблему решает ваше изобретение?</li>
						<li>Предлагает ли изобретение более дешевый и / или лучший способ сделать что-то?</li>
						<li>Существуют ли конкурирующие технологии? Насколько лучше новое изобретение, чем у конкурентов?</li>
						<li>Вы собираетесь продолжать работать в этой области?</li>
						<li>Будете ли вы готовы и способны проводить эксперименты для разработки этого изобретения?</li>
						<li>Имеет ли он потенциал для создания нового рынка?</li>
						<li>Сколько инвестиций, как времени, так и денег, потребуется, чтобы вывести изобретение на рынок?</li>
						<li>Есть ли потенциал для начинающей компании создать бизнес, основанный на изобретении, который может способствовать региональному экономическому развитию?</li>
					</ul>
        		</div>
        	</div>
        	<div class="row col-xs-12 delimiter">
        		<br/>
        	</div>
        	<div class="col-xs-12">
        		<h3 class="sub-title" style="text-align: center; font-style: italic;">Чтобы иметь право на патентную защиту, Российские законы требует, чтобы изобретение было:</h3>
	        	<br/>
	        	<br/>
	        	<div class="col-xs-12">
	        		<h3 class="sub-title"><b>1. Новое</b> или имело новизну:</h3>
	        		<p>Изобретение должно быть явно отличным от общедоступных идей, изобретений или продуктов (так называемый «уровень новизны»). Это не означает, что каждый аспект изобретения должен быть новым. Например, новое использование известных процессов, машин, составов материи и материалов патентоспособно. Инкрементальные улучшения в известных процессах также могут быть патентоспособными.</p>
        		</div>
	        	
	        	<br/>
	        	<br/>
	        	<div class="col-xs-12">
        			<h3 class="sub-title"><b>2. Полезно</b>:</h3>
        			<p>Изобретение должно иметь какое-либо применение или полезность или быть улучшенным по сравнению с существующими продуктами и / или технологиями.</p>
        		</div>
        		<br/>
	        	<br/>
	        	<div class="col-xs-12">
	        		<h3 class="sub-title"><b>3. Не очевидное</b>:</h3>
        			<p>Изобретение не может быть очевидным для человека «обычного навыка» в поле; неочевидность обычно демонстрируется, показывая, что практика изобретения дает неожиданные результаты.</p>
        		</div>
        		<br/>
	        	<br/>
        	</div>
        	<div class="col-xs-12" style="padding-top: 20px;">
        		<h3 class="sub-title2">Вы сделали важное открытие или придумали идею? <b>Свяжитесь с нами</b>, чтобы узнать, что должно быть сделано, прежде чем раскрывать его общественности.</h3>
        	</div>
        	<div class="col-xs-12 col-lg-6 col-lg-offset-3">
	        	<div class="btn content-button red js-modal-form" data-toggle="modal" data-frm="name,tel" data-mtitle="Оставьте заявку и мы свяжемся с вами в ближайшее время" data-tomail="Узнать мои шаги" data-success="Мы перезвоним Вам в ближайшее время." data-btn="Узнать мои шаги">Узнать мои шаги</div>
	        </div>

        	<div class="row col-xs-12 delimiter">
        		<br/>
        	</div>
        	
        	<div class="row col-xs-12">
        		<h3 class="title">Пожалуй, это одно из лучших предложений на рынке</h3>
        	</div>
        	<div class="col-xs-12 col-lg-10 col-lg-offset-1">
        		<div class="col-xs-12 col-lg-6 advant block-margin">
        			<ol>
						<li>Многие специалисты из нашей команды занимаются патентованием изобретений <b>более 20 лет</b>. Мы многое прошли.</li>
						<li>Мы никогда не врём. Обсуждая проект, мы всегда говорим только о <b>реальных</b> возможностях и сроках.</li>
						<li>Никаких ложных обещаний.</li>
						<li>Гарантированная регистрация либо <b>возврат 110% ваших денег</b>.</li>
					</ol>
        		</div>
        		<div class="col-xs-12 col-lg-6 advant" style="background-color: transparent;">
					<div class="cert"></div>
        		</div>
        	</div>
		<?php
		$lx_vk_cases_page = '/patent';
		$lireate_vk_cases_modifier = "cms";
		require $_SERVER["DOCUMENT_ROOT"] . "/_shared/lireate-vk-cases-render.inc.php";
		?>
		<?php endif?>
		<?php if($temp['page']=='patent_prom_obr'):?>
			<style type="text/css">

				.main-title h1{
					font: 700 27px/32px 'Open Sans';
					text-align: left;
				}

				.main-title h2{
					font: 25px/25px 'Open Sans'; 
					text-align: left;
				}

				.main-screen{
					background-image: url('{theme_url}/images/patent_prom_obr_back_opt.jpg');
					background-size: cover;
				}

				.main-title{
					background-color: rgba(51, 55, 64, 0.69);
				    /*width: fit-content;*/
				    width: 55%;
				    float: right;
				    padding: 0 20px;
				    margin: 0 35px;
				    color: white;
				    border-radius: 5px;
				    border-right: 10px solid #d92a3d;
				    text-align: right;
				}

				.main-button.btn {
				    font: 22px/50px 'Open Sans';
				    float: right;
				    padding: 0 20px;
				    margin-top: 10px;
	    			margin-bottom: 30px;
				    box-shadow: inset 0 0 5px rgba(51, 55, 64, 0.14), 3px 3px 14px rgba(0, 0, 0, 0.14);
				    color: #fff;
				    width: fit-content;
				    background-color: #d92a3d;
				    border: 0px solid transparent;
				    -webkit-transition: all 0.3s ease-out;
				    -moz-transition: all 0.3s ease-out;
				    -o-transition: all 0.3s ease-out;
				    transition: all 0.3s ease-out;
				}

				.content-button.btn {
				    font: 21px/25px 'Open Sans';
				    white-space: pre-wrap;
				    padding: 10px 20px;
				    margin-top: 10px;
	    			margin-bottom: 30px;
				    box-shadow: inset 0 0 5px rgba(51, 55, 64, 0.14), 3px 3px 14px rgba(0, 0, 0, 0.14);
				    color: #fff;
				    width: 100%;
				    background-color: rgba(51, 55, 64, 0.65);
				    border: 0px solid transparent;
				    -webkit-transition: all 0.3s ease-out;
				    -moz-transition: all 0.3s ease-out;
				    -o-transition: all 0.3s ease-out;
				    transition: all 0.3s ease-out;
				}

				.sub-title2{
					font: 300 28px/32px "Open Sans",sans-serif;
    				text-align: justify;
				}

				.delimiter2{
					background-image: url('{theme_url}/images/bg-dark.jpg');
					padding: 0 40px;
					padding-bottom: 20px;
					margin-bottom: 30px;
    				background-position: center;
    				background-size: cover;
    				box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.5);
				}

				.delimiter2 .sub-title{
					color: #FFF;
					font: 300 20px/32px "Open Sans",sans-serif;
					text-align: justify;
					padding-top: 20px;
					padding-bottom: 20px;
				}

				@media (max-width: 750px){
					.main-screen{
						background-image: url('{theme_url}/images/patent_prom_obr_back_opt_mini.jpg');
					}
				}

				@media (max-width: 970px){
					.main-title h1{
						font: 700 26px/32px 'Open Sans' !important;
					}

					.main-title h2{
						font: 20px/30px 'Open Sans' !important;
					}
					.delimiter2 .sub-title{
						font: 300 16px/22px "Open Sans",sans-serif;
					}
				}
			</style>
			<div class="row main-screen main_back lireate-hero-v4 lireate-cms-hero" id="main_back" style="margin: 0;"><!-- lireate_design_patent_prom_hero_v4 -->
		<?php include dirname(__FILE__) . '/page/cms/lireate-cms-hero-patent-prom.inc.php'; ?>
	        </div>
	        	
        	<div class="row col-xs-12" style="padding: 0px 0px;">
        			<div class="row col-xs-12 delimiter2">
	        			<p class="sub-title" style="font-style: italic;">Воплощая свои идеи не все могут осознавать необходимость их своевременного патентования в виде промышленного образца. Мы своевременно определим возможность целесообразность защиты вашего дизайна и дадим честный прогноз.<br/>В качестве промышленного образца можно запатентовать решение дизайна или вида изделия, промышленного или ремесленного производства.<br/>Промышленный образец может состоять из трехмерных элементов, таких как форма изделия, или двухмерных объектов, таких как узоры, линии или цвет.</p>
	        		</div>
        	</div>
        	
        	<div class="col-xs-12">
        		<h3 class="title" style="text-align: center;">Страны, в которых вы получите защиту вашего патента</h3>
        	</div>
        	<div class="row col-xs-12" style="padding: 0px 30px;">
        		<div class="col-xs-12 col-lg-6 advant">
        			<h3>РОССИЯ</h3>
					<p>Регистрация осуществляется через Роспатент. Срок патентования составляет 12-15 месяцев.</p>
        		</div>
        		<div class="col-xs-12 col-lg-6 advant">
        			<h3>США</h3>
					<p>Оформления патента в США проводит Ведомство по патентам и товарным знакам США (USPTO) Время регистрация составляет 1,5 года.</p>
        		</div>
        		<div class="col-xs-12 col-lg-6 advant">
        			<h3>КИТАЙ</h3>
					<p>В данной стране защита патента осуществляет Государственное ведомство по интеллектуальной собственности Народной Республики Китай (SIPO). Срок регистрации составляет ориентировочно 13-19 месяцев.</p>
        		</div>
        		<div class="col-xs-12 col-lg-6 advant">
        			<h3>ЕВРОСОЮЗ (38 стран)</h3>
					<p>Орган, осуществляющий регистрацию по странам Евросоюза Ведомство по гармонизации на внутреннем рынке г.Алеканте. Процедура регистрации составляет 7-8 месяцев.</p>
        		</div>
        	</div>

        	<div class="col-xs-12">
        		<h3 class="title" style="text-align: center;">Простые шаги по защите вашего дизайна:</h3>
	        	<br/>
	        	<br/>
	        	<div class="col-xs-12">
	        		<h3 class="sub-title2">1. Прежде чем подавать заявку на регистрацию вашего уникального дизайна или вида, нужно определить может ли оно быть запатентовано.</h3>
        		</div>
	        	
	        	<br/>
	        	<br/>
	        	<div class="col-xs-12">
        			<h3 class="sub-title2">2. Проведение патентного поиска. Данный шаг можно условно разделить на две составляющие это непосредственно поиск и проверка. Поиск находит совпадение, проверка оценивает риски связанные с нахождением схожего аналога.</h3>
        		</div>
        		<br/>
	        	<br/>
	        	<div class="col-xs-12">
	        		<h3 class="sub-title2">3. Ну и конечно ответственный этап это формирование заявки, включающее в себя описание, реферат и различные приложения.</h3>
        		</div>
        	</div>
        	<div class="col-xs-12 col-lg-6 col-lg-offset-3">
	        	<div class="btn content-button red js-modal-form" data-toggle="modal" data-frm="name,tel" data-mtitle="Оставьте заявку и мы свяжемся с вами в ближайшее время" data-tomail="Узнать мои шаги" data-success="Мы перезвоним Вам в ближайшее время." data-btn="Узнать мои шаги">Узнать мои шаги</div>
	        </div>

        	<div class="row col-xs-12 delimiter" style="margin-top:50px;">
        		<br/>
        	</div>
        	
        	<div class="row col-xs-12">
        		<h3 class="title">Пожалуй, это одно из лучших предложений на рынке</h3>
        	</div>
        	<div class="col-xs-12 col-lg-10 col-lg-offset-1">
        		<div class="col-xs-12 col-lg-6 advant block-margin">
        			<ol>
						<li>Многие специалисты из нашей команды занимаются патентованием изобретений <b>более 20 лет</b>. Мы многое прошли.</li>
						<li>Мы никогда не врём. Обсуждая проект, мы всегда говорим только о <b>реальных</b> возможностях и сроках.</li>
						<li>Никаких ложных обещаний.</li>
						<li>Гарантированная регистрация либо <b>возврат 110% ваших денег</b>.</li>
					</ol>
        		</div>
        		<div class="col-xs-12 col-lg-6 advant" style="background-color: transparent;">
					<div class="cert"></div>
        		</div>
        	</div>
		<?php
		$lx_vk_cases_page = '/patent_prom_obr';
		$lireate_vk_cases_modifier = "cms";
		require $_SERVER["DOCUMENT_ROOT"] . "/_shared/lireate-vk-cases-render.inc.php";
		?>
		<?php endif?>
		
		
		
		<?php // БЛОГ ////////////////////////////////////////////////////////////////////////
		    if($temp['page']=='news'): 	include "./template/page/blog.php"; endif?> 
		    
		<?php // СТАТЬЯ БЛОГА      //////////////////////////////////////////////////////////
		    if($temp['news_id']!=false){
			if(substr($server,0,3)!='lir'){header("HTTP/1.0 404 Not Found");}
			$temp['page'] = "emp";
        	$news_article = get_news_article($db, 'news', '"'.$temp['news_page'].'"');
    		include "./template/page/newStyle.php";
    		}?>
		
		
		
		
     
    <!-- ======================================================================================================== -->
    <!--================================== СТРАНИЦА С ПРИМЕРАМИ РАБОТАМИ =========================================-->
    <!-- ======================================================================================================== -->
		
    	<?php if(!empty($temp['cat']) && $temp['page']=='mktu1'):?>
    <h1 class="visually-hidden">Класс МКТУ <?php echo htmlspecialchars((string)$temp['cat'], ENT_QUOTES, 'UTF-8'); ?></h1>
    <?php endif; ?>
    <?php if($temp['page']=='mktu1'):?>
    		
        	<style type="text/css">
        		
        		.contein_page{
        		    display:flex;
        		    flex-wrap:wrap; 
        		    justify-content:space-between; 
        		    width:90%; 
        		    margin:0 auto; 
        		    margin-top:50px;}
        		
        		.example_box{
        		    display:block; 
        		    position: relative; 
        		    width:47.5%; 
        		    padding-bottom:20%;  
        		    display:block; 
        		    margin-bottom:70px; 
        		    background-repeat:no-repeat; 
        		    background-size: cover;}
        		
        		.link_box{
        		    display:block;  
        		    position:absolute; 
        		    z-index:10; 
        		    height:100%; 
        		    width:100%;}
        		
        		.title_example_box{
        		    position:absolute;  
        		    bottom:20px; 
        		    right:0; 
        		    max-width: 90%; 
        		    max-height: 90%; 
        		    padding:15px;
        		    background: rgba(51, 55, 64, 0.7);
        		    -webkit-transition: all 0.3s ease-out;
				    -moz-transition: all 0.3s ease-out;
				    -o-transition: all 0.3s ease-out;
				    transition: all 0.3s ease-out;
        		    z-index: 1000;
        		    pointer-events: none;
        		}
        		
        		.title_example_box h4{
        		    line-height: 20px;
        		    color:white; 
        		    margin:0; 
        		    padding:0;
        		    font-size:18px;
        		}
        		    
        		.sub-title2{
        			font: 300 16px/20px "Open Sans",sans-serif;
        			font-family: Roboto, "Helvetica Neue", Arial, sans-serif !important;
        			margin-bottom: 10px;
        			text-align: center;
        			width:80%;
        			padding-left: 20px;
                    padding-right: 20px;
        		    margin:0 auto;
        		}
                        
        		.description_example_box{
        		   /*display:none; 
        		    transition: 1s;*/
        		}
        		
        		.description_example_box p{
        		    font-family: Roboto, "Helvetica Neue", Arial, sans-serif;
        		    margin:0;
        		    padding:0;
        		    color:white;
        		    font-size:16px;
        		    text-align:left;
            		color:#bbb;
            		line-height:1;
            		margin-top:8px;
            		-webkit-transition: all 0.3s ease-out;
				    -moz-transition: all 0.3s ease-out;
				    -o-transition: all 0.3s ease-out;
				    transition: all 0.3s ease-out;
            		line-height: 18px;
        		}
        		
        		
                .example_box:hover .title_example_box{
                    background: rgba(51, 55, 64, 0.85);
                    margin-bottom: 10px;
                    color:white;
                }
                
                @media (max-width: 1200px){
                    .example_box{
                        padding-bottom:44%; 
                        width:97%;}
                        
                    .contein_page{
                       justify-content:space-around;}}
                    
                @media (max-width: 600px){
                    .example_box{
                        margin-bottom:50px;
                        width:99%;}
                        
                    .title_example_box{
                	    padding:10px;}
                    
                    .description_example_box p{
                        font-size:14px;
                        color:#bbb;
                    }
                    
                    
                    .title_example_box h4{
                        font-size:17px;}
                    
                }
                
                .example_box:hover .title_example_box p{
        		    color:white;}
        		    
        		.example_box:hover .title_example_box .description_example_box {
        		    display:block;}
        		
        		.link_box{    
        		    background-size: cover;
        		    background-position: center;
        		    -webkit-transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
				    -moz-transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
				    -o-transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
				    transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
				    filter: blur(0px) brightness(1) contrast(1);
        		}
        		
        		.link_box_back{
    		        width: 100%;
                    height: 100%;
                    background-size: cover;
                    background-position: center;
                    position: absolute;
        		}
        		    
        		.example_box:hover .link_box{
        		    filter: blur(2px) brightness(1.4) contrast(0.8);
        		}
		    
			</style>
			
			<div style="margin-top: 50px;">
			    
				<div class="title_page_wrap">
					<h3 class="" style="text-align: center; padding-bottom: 10px; font-weight: 700;">Примеры работ Лирейт</h3>
				</div>
				
				<div class="row"> <!--id="grid" data-columns>-->
					<div class="" style="margin:0 auto; width: 100%; ">
						<h3 class="sub-title2">Данный раздел позволит вам ознакомиться с конкретными примерами наших работ, вы узнаете о нестандартных ситуациях и об их решении Патентным бюро Лирейт.</h3>
						<div class="contein_page">
						
						<?php 
    						$news_on_page = 12;
    						$num_news = get_sql($db, 'mktu', $wh, 'count')[0][0];
    						$num_pages = ceil($num_news/$news_on_page);
    						$req = $_SERVER['REQUEST_URI'];
    						/**echo var_dump($temp['newspage']);
    						exit;*/
						if(strpos($req,'casepage',0)){
							if(($temp['casepage']>$num_pages)||($temp['casepage']<0)||(strpos($req,'casepage=0'))||(substr($server,0,3)!='lir')){
								header("HTTP/1.0 404 Not Found");
							}
						}
						foreach(get_news($db, 'mktu', $news_on_page, $temp['casepage'], $temp['cat']) as $key => $ct):?>
							<?php $img_pos = strpos($ct['full_story'],'src=');
								if (!empty($temp['cat'])){
									$tmp_cat = "?cat=".$temp['cat'];
								}else $tmp_cat = "";
									if($img_pos){
									    $img = substr($ct['full_story'], $img_pos+5, strpos($ct['full_story'],'"',$img_pos+6) - ($img_pos+5));?>
										<?php } else { ?>
										<?php }; ?>	
										
										
							<!--===================================-->    
                            <!--======= БЛОК С ПРИМЕРОМ РАБОТЫ ====-->
							<div class="example_box">
							    
							    <!--======= ССЫЛКА КОТОРАЯ РАСТЯНУТА НА ВЕСЬ БЛОК И ВЫВЕДЕНА НА ПЕРЕДНИЙ ПЛАН ====-->
							    <a class="link_box" href="/<?=$ct['seo_url'].$tmp_cat;?>" style="background-image: url('<?= $img?>');"></a>
							    <div class="link_box_back" style="background-image: url('<?= $img?>');"></div>
							    
							    <!--======= ПРОЗРАЧНЫЙ БЛОК С ДАТОЙ, ЗАГОЛОВКОМ И ОПИСАНИЕМ ====-->
								<div class="title_example_box">
									
									<!--======= ДАТА =======-->
									<!--<div class="datediv">
										 <i class="glyphicon glyphicon-calendar"></i><p style="color: white; font: 500 14px/12px 'Open Sans',sans-serif; display: inline-block; margin-top: 10px; margin-left: 5px;"> <?=date("H:i  d.m.Y",$ct['datetime']);?></p>
									</div>-->
									
	                                <!--======= ЗАГОЛОВОК =======-->
									<h4 style="">
		    
										<?=trim($ct['seo_title']);?>
									</h4>
                                    
                                    <!--======= ОПИСАНИЕ =======-->
									<div class="description_example_box"> 
										<?=trim(str_replace('&nbsp;', ' ',$ct['short_story']));?>
									</div>

								</div>
							</div>
						    <!--======= КОНЕЦ БЛОКА С ПРИМЕРОМ РАБОТЫ ====-->
						    <!--==========================================--> 
							
						<?php endforeach?>
						</div>
					</div>
				<div class="row">
					        
						
				
					    
						<?php
							$wh=false;
							$tmp_cat="";
							if(!empty($temp['cat']!="")){
								$wh=['category',$temp['cat']];
								$tmp_cat = "?cat=".$temp['cat']."&";
							}
							$num_news = get_sql($db, 'mktu', $wh, 'count')[0][0];
							if ($num_news){
								$num_news = intval($num_news);
								if($num_news>$news_on_page){ ?>
									<nav style="text-align: center;">
										<ul class="pagination pagination-sm">
											<li <?php if($temp['casepage']==0){echo 'class="disabled"';};?>>
												<a href=<?php 
													if($temp['casepage']!=0){
														echo "http://lireate.ru/mktu?".$tmp_cat."casepage=1";
													}
												?>><i class="fa fa-chevron-left "></i><i class="fa fa-chevron-left fa-sm"></i>
												</a>
											</li>
											<li <?if($temp['casepage']==0){echo 'class="disabled"';};?>>
												<a href=<?php if($temp['casepage']>0){echo "http://lireate.ru/mktu?".$tmp_cat."casepage=".($temp['casepage']);}?>><i class="fa fa-chevron-left "></i></a></li>
											<?php
												$num_pages = ceil($num_news/$news_on_page);
												for ($i = 0; $i < $num_pages; $i++){?>
													<li <?if($temp['casepage']==$i){?>class="active" <?};?>><a href="/mktu?casepage=<?=$i+1?>"><?php echo $i+1 ?></a></li>
												<?php }; ?>
											<li <?if(($temp['casepage']+1)==$num_pages){echo 'class="disabled"';}?>>
												<a href=<?php if($temp['casepage']+1<$num_pages){echo ("http://lireate.ru/mktu?".$tmp_cat."casepage=".($temp['casepage']+2));}?>><i class="fa fa-chevron-right "></i></a></li>
											<li <?if(($temp['casepage']+1)==$num_pages){echo 'class="disabled"';}?>>
												<a href=<?php if($temp['casepage']+1<$num_pages){echo ("/news?".$tmp_cat."casepage=".$num_pages);}?>><i class="fa fa-chevron-right "></i><i class="fa fa-chevron-right "></i></a></li>
										</ul>
									</nav>				
						<?php }; };?>
					</div>
				</div>
				
			</div>
		<?php endif?>
		
		
		
		
		
<?php if($temp['page']=='mktu1'){?>
<?php

    $idPage = $_GET['id'];
    if(empty($idPage)){$idPage = 46;}
    $host = 'FIXTURE'; // адрес сервера 
    $database = 'FIXTURE'; // имя базы данных
    $user = 'FIXTURE'; // имя пользователя
    $password = 'FIXTURE'; // пароль

    $db = mysql_connect($host, $user, $password);
    mysql_query("SET NAMES utf8");
    mysql_select_db($database, $db);

    $result = mysql_query("SELECT * FROM goods_and_services WHERE id=$idPage",$db);
    /*Преобразовываем результат в массив*/
    $myrow = mysql_fetch_array($result);
    
?>



<section class="mktu_body">
    <link rel="stylesheet" type="text/css" href="{theme_url}/css/styleg.css?2">  
    <div class="conteinerY">
    	<!-- Заголовок -->
    	<div class="title-ap">
    		     <h1 class="sub-title"><?=$myrow['namePage'];?></h1>
    	</div>

        <div class="conteinerX">
            
            <!--//////////////////////////////// -->
            <!--    БЛОК СПРАВА С РЕКЛАМОЙ       -->
            <div class="left_block">
                <div style="margin-top: -10px; margin-bottom: 20px;">
                    <div id="myCarousel" class="carousel slide" data-interval="3000" data-ride="carousel" style="margin-top: 20px;">
                        <!-- Carousel items -->
                        <div class="carousel-inner">
                            <div class="active item" style="background: url(template/img/slider/1.jpg); text-align: center; ">
                                <div class="block_for_text_slider">
                                    <a href="https://lireate.ru/registraciya_tovarnogo_znaka/"><p>Регистрация товарного знака</p></a>
                                </div>
                            </div>
                            <div class="item" style="background: url(template/img/slider/2.jpg); text-align: center; ">
                                <div class="block_for_text_slider">
                                    <a href="http://lireate.ru/findtz"><p>Проверка товарного знака</p></a>
                                </div>
                            </div>
                        </div>
                        <!-- Carousel nav -->
                        <ol class="carousel-indicators">
                            <li data-target="#myCarousel" data-slide-to="0" class="active"></li>
                            <li data-target="#myCarousel" data-slide-to="1"></li>
                        </ol>
                    </div>
                </div>
                <div class="right_block_flex">
                    <div class="right_block_img"><p class="month">8-10</p><p>МЕСЯЦЕВ</p></div>
                    <div class="right_block_text">Средний срок регистрации товарного знака в Роспатенте</div>
                </div>
                <div class="block_contact text-left">
                    <img src="template/img/qwe/Urii.jpg"  alt="Юрий Горбачев, руководитель патентного бюро LIREATE">
                    <h3>Горбачев Юрий</h3>
                    <p>За 10 минут подробно расскажет о любой необходимой процедуре, связанной с интеллектуальной собственностью. По итогу разговора вы получите точную последовательность действий.</p><br><br><br>
                    <div class="btn content-button red js-modal-form" data-toggle="modal" data-frm="name,tel" data-mtitle="Оставьте заявку и мы свяжемся с вами в ближайшее время" data-tomail="49000 рублей" data-success="Мы перезвоним Вам в ближайшее время." data-btn="Отправить заявку" style="margin-bottom:0px;">Консультация</div>
                </div>
            </div>
            
        	<!--//////////////////////////////// -->
            <!--    БЛОК С ОСНОВНОЙ СТАТЬЕЙ       -->				    
            <div class="txt-content news-content">
                <?php echo $myrow['html']; ?>
            </div>
        </div>
</section>



<?php }?>





<!--/////////////////////////////////////////////////////// -->
<!--       МЕЖДУНАРОДНАЯ КЛАССИФИКАЦИЯ ТОВАРОВ И УСЛУГ      -->
<!--/////////////////////////////////////////////////////// -->

<?php if($temp['case_id']!=false){
	if(substr($server,0,3)!='lir'){header("HTTP/1.0 404 Not Found");}
	?>
	
			<style type="text/css">
				.sub-title{
					text-align: left;
				}
				.collapse-body{
				    background-color: rgba(51, 55, 64, 0);
				    border-radius: 5px;
				    padding: 0px 10px;
				    -webkit-transition: all 0.3s ease-out;
				    -moz-transition: all 0.3s ease-out;
				    -o-transition: all 0.3s ease-out;
				    transition: all 0.3s ease-out;
				}
				.collapse-body.in{
				    background-color: rgba(51, 55, 64, 0.1);
				    border-radius: 5px;
				    padding: 20px 10px;
				}
				.collapse-body h1{
					margin-top: 0px;
				}
				.breadcrumb-ap>li{
				    font-family: Roboto, "Helvetica Neue", Arial, sans-serif !important;
				}


.mktu_head{
    width:100%;
    border-bottom:1px solid #d5d9db;
    background-color: #e6ebed;
    padding:20px;
} 

.mktu_head article{
    width:100%;
    max-width:640px;
    margin:0 auto;
    display:flex;
    justify-content:space-around;
    flex-wrap:wrap;
}
.ssilka{
    width: 27px;
    text-align:left;
    display:block;
    float:left;
}
</style>


<section class="mktu_head">
    <article>
        <div><h4>Товары</h4><script> for(i=1; i<=34; i++){document.write('<a class="ssilka" href="/'+i+'">'+i+'</a>');if(i==6||i==12||i==18||i==24||i==30){document.write('<br>');}}</script></div>
        <div><h4>Услуги</h4><script>for(i=35; i<=45; i++){document.write('<a class="ssilka" href="/'+i+'">'+i+'</a>'); if(i==40){document.write('<br>');}}</script> </div>
    </article>
</section>
			
<?php 
	echo '<link rel="stylesheet" type="text/css" href="{theme_url}/css/styleg.css?2">';
	$temp['page'] = "emp";
	$news_article = get_news_article($db, 'mktu', '"'.$temp['case_page'].'"');?>
	
	<div class="conteinerY">
		<div class="title-ap"><?php if($news_article[0]['title']==1){?><h1 class="sub-title"><?=$news_article[0]['name'];?></h1><?php };?></div>    <!-- заголовок статьи -->
            <div class="conteinerX">
    			
    			<!-- ПРАВЫЙ БЛОК -->
    			<div class="left_block">
                    
                    <!-- Карусель -->
                    <div style="margin-top: -10px; margin-bottom: 20px;">
                        <div id="myCarousel" class="carousel slide" data-interval="3000" data-ride="carousel" style="margin-top: 20px;">
                            <!-- слайды -->
                            <div class="carousel-inner">
                                <div class="active item" style="background: url(template/img/slider/1.jpg); text-align: center; ">
                                    <div class="block_for_text_slider">
                                        <a href="https://lireate.ru/registraciya_tovarnogo_znaka/"><p>Регистрация товарного знака</p></a>
                                    </div>
                                </div>
                                <div class="item" style="background: url(template/img/slider/2.jpg); text-align: center; ">
                                    <div class="block_for_text_slider">
                                        <a href="http://lireate.ru/findtz"><p>Проверка товарного знака</p></a>
                                    </div>
                                </div>
                            </div>
                            <!-- навигация для карусели -->
                            <ol class="carousel-indicators">
                                <li data-target="#myCarousel" data-slide-to="0" class="active"></li>
                                <li data-target="#myCarousel" data-slide-to="1"></li>
                            </ol>
                        </div>
                    </div>
                    
                    <div class="right_block_flex">
                        <div class="right_block_img"><p class="month">8-10</p><p>МЕСЯЦЕВ</p></div>
                        <div class="right_block_text">Средний срок регистрации товарного знака в Роспатенте</div>
                    </div>
                    <div class="block_contact text-left">
                        <img src="template/img/qwe/Urii.jpg"  alt="Юрий Горбачев, руководитель патентного бюро LIREATE">
                        <h3>Горбачев Юрий</h3>
                        <p>За 10 минут подробно расскажет о любой необходимой процедуре, связанной с интеллектуальной собственностью. По итогу разговора вы получите точную последовательность действий.</p><br><br><br>
                        <div class="btn content-button red js-modal-form" data-toggle="modal" data-frm="name,tel" data-mtitle="Оставьте заявку и мы свяжемся с вами в ближайшее время" data-tomail="49000 рублей" data-success="Мы перезвоним Вам в ближайшее время." data-btn="Отправить заявку" style="margin-bottom:0px;">Консультация</div>
                    </div>

                </div>
    					    
        		<div class="txt-content news-content">
                    <!-- Текст статьи -->		
                    <?=(str_replace("<img alt=","<img class='img-responsive' alt=",$news_article[0]['full_story']));?>
        		</div>
        					
        		<script src="//yastatic.net/es5-shims/0.0.2/es5-shims.min.js"></script>
        		<script src="//yastatic.net/share2/share.js"></script>
        		<script type="text/javascript">
        			$(".spoiler-trigger").click(function() {
        			$('.collapse').collapse('toggle');
        			$('.collapse-body').toggleClass('in');
        			});
        		$(document).ready(function () {$('.news-content p').css('background','transparent')});
        	</script>
        </div>
	</div>

<?php }?>
			




			
		
			
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
			
			
		<?php if($temp['page']=='check'){?>
		    <style>
		        .delimiter2{
					background-image: url('http://lireate.ru/template/images/bg-dark.jpg');
					padding-bottom: 20px;
    				background-position: center;
    				background-size: cover;
				}
				.delimiter2 .title{
					color: #f5f5f5;
                    font-size: 22px;
                    line-height: 26px;
                    text-align: left;
                    /*margin-top: 55px;*/
				}
				.delimiter2 .sub-title{
					color: #f5f5f5;
                    font-size: 18px;
                    line-height: 22px;
                    margin-top: 60px;
                    text-align: left;
				}
				.delimiter2 .btn:hover{
    				background-color: white;
    				color: #333740;
				}
				.order_form-group{
				    padding: 0px;
				}
				.footer-ap h2 {
                    text-align: center;
                    font-size: 20px;
                    line-height: 25px;
                }
                html{
                    background-color: #333740 !important;
                }
                .content {
                    padding-left: 0;
                }
		    </style>
		    <div class="row col-xs-12 delimiter2" style="padding: 0;">
		        <div class="col-sm-12 col-md-8 col-md-offset-2 text-center">
    		        <a class="footer_logo" href="" style="margin-top: 55px;">
    					<img src="http://lireate.ru/template/images/logo.png" alt="nav-logo">
    				</a>
    				<br>
    				<a class="title" href="tel:+74999384595" data-lx-ym-goal="klik_po_nomeru_telefona"><b>+7 (499) 938-45-95</b></a>
            		<h3 class="title">Бесплатная экспресс-проверка товарного знака на возможность регистрации</h3>
        		</div>
				<div class="col-sm-12 col-md-8 col-md-offset-2">
				    <h4 class="sub-title">Введите название товарного знака для начала бесплатной проверки</h4>
        			<div data-frm="brand" class="brand-field order_form-group">
						<input id="brand2" name="brand" type="text" class="form-control order_form_control" placeholder="Название товарного знака" required="" style="height: 42px; font-size: 18px;" maxlength="40">
					</div>
				</div>
				<div class="col-sm-12 col-md-8 col-md-offset-2 hidden">
				    <h4 class="sub-title">Введите номер для получения результатов проверки в смс-сообщении</h4>
        			<div data-frm="tel2" class="tel-field order_form-group">
						<input id="tel2" name="tel" type="tel" class="form-control order_form_control" placeholder="Номер телефона" required="" style="height: 42px; font-size: 18px;">
					</div>
				</div>
				
				<div class="col-sm-12 col-md-8 col-md-offset-2">
					<!--<div class="btn content-button red js-modal-form" data-toggle="modal" data-frm="name,tel,brand" data-mtitle="Оставьте заявку и мы свяжемся с вами в ближайшее время" data-tomail="Заказать бесплатную проверку" data-success="Мы перезвоним Вам в ближайшее время." data-btn="Заказать бесплатную проверку">Начать проверку</div>-->
    				<!--<button type="submit" class="btn content-button red" id="_bbut2">Начать проверку</button>-->
    				<!--<div class="btn content-button red" data-toggle="modal" data-target="#find-modal" id="_bbut2">Начать проверку</div>-->
    				<div class="btn content-button red" id="_bbut2">Начать проверку</div>
    			</div>
    			<div class="col-sm-12 col-md-8 col-md-offset-2 hidden">
    				<form id="double-form" method="POST">
    					<input type="hidden" name="mail_token" value="<?=md5(microtime(true));?>">
						<input type="hidden" name="fuck_spamers" value="<?=md5(date('m.Y'));?>">
						<input type="hidden" name="tomail" value="">
    					<input type="hidden" name="mailorder">
    					<input type="hidden" name="brand" maxlength="40" value="123">
    					<input type="hidden" name="tel">
    					<input type="hidden" name="form-type" value="check">
    					<button type="submit" class="btn content-button red" id="_bbut22">Начать проверку</button>
    				</form>
    			</div>
    			<script type="text/javascript">
					$('#_bbut2').click(function(){
						var b = $('#brand2').parent();
						var t = $('#tel2').parent();
						var bu1 = $('#_bbut2').parent();
						var bu2 = $('#double-form').parent();
						b.fadeOut("fast",function(){
							b.parent().addClass('hidden');
							t.parent().removeClass('hidden');
							t.fadeIn();
							bu1.addClass('hidden');
							bu2.removeClass('hidden');
						});
					});
					var f = $('#double-form');
					$('#brand2').change(function(){
						$('input[name=brand]',f).val($('#brand2').val());
					});
					$('#tel2').change(function(){
						$('input[name=tel]',f).val($('#tel2').val());
					});
				</script>
			    <!--<div class="btn content-button red js-modal-form" data-toggle="modal" data-frm="name,tel" data-mtitle="Оставьте заявку и мы свяжемся с вами в ближайшее время" data-tomail="Заказать бесплатную проверку" data-success="Мы перезвоним Вам в ближайшее время." data-btn="Заказать бесплатную проверку">А я начну с бесплатной проверки</div>-->
        	</div>
		<?php }?>
		

		<?php if($temp['page']=='uslugi'){ ?>
			<div class="row">
				<div class="col-xs-12">
					<section id="lireate-uslugi">
<?php require $_SERVER['DOCUMENT_ROOT'] . '/template/page/uslugi/lireate-uslugi-cms.inc.php'; ?>
					</section>
				</div>
			</div>
		<?php }?>

		<?php if($temp['page']=='kontaktyi'){
			$mails = ['lib'=>'info@lireate.ru', 'msk'=>'msk@lireate.ru','rst'=>'rostovnadonu@lireate.ru','nov'=>'n.novgorod@lireate.ru','kra'=>'krasnodar@lireate.ru','vla'=>'Vladivostok@lireate.ru','sam'=>'samara@lireate.ru'];
			$maps = ['lib'=>'%3Ae8f792a388683a820ecc53a823f5f97014b575970a1eeb6a283b818f53230995','msk'=>'%3A7996a63e4bd0b409e7d11e7d83406e22d7028915305bd405364539bbbf96f6c4','rst'=>'%3A5c00f8b32e7ac6e84411d8e418420d82d7d0416685fc330aa719ee378c07cd8a','nov'=>'%3Ac412d5cf254c94571cf2ca7eb41b72978e3acec134985bb1e12af370511dd6f3','kra'=>'%3A74144955f565bd632df7a2f981c8e9bcba198d586b9f317262253a9ced1d0392','vla'=>'%3Ae25ba6fe9068bb497d1e05de175f2f6a055cf953864a181e73a65c9e23b5bcb6','sam'=>'%3Ae5ae22ea0c39533525568a377b54ea71ec67d5dfffe86e35588e9019e62e5412'];
			$adresses = ['lib'=>'РТ, г. Зеленодольск ул. Королёва, 11Б этаж 2','msk'=>'г.Москва, Ленинский проспект д.3','rst'=>'г.Ростов на дону, ул.Береговая, д.8 (офис 5)','nov'=>'г.Нижний Новгород, ул.Чкалова, д.5','kra'=>'г.Краснодар, Кубанская наб., 62 (офис 13)','vla'=>'Приморский край, г.Владивосток, 1-я Морская улица, д.9 (офис 4)','sam'=>'г.Самара, ул.22 Партсъезда, д.45 (офис 8)'];
			?>
			<div class="">
				<div class="row">
					<div class="col-xs-12">
						<section id="lireate-contacts">
<?php /* lireate-contacts-install:v1 */ ?>
<?php require $_SERVER['DOCUMENT_ROOT'] . '/template/page/kontaktyi/lireate-contacts-cms.inc.php'; ?>
</section>

					</div>
				</div>
		<?php }?>
		<!-- Содержание
		====================-->
			<div class="page_container">
				<?php if($temp['page']=='o_kompanii'){ ?>
					<section id="lireate-about">
<?php /* lireate-about-install:v1 */ ?>
<?php require $_SERVER['DOCUMENT_ROOT'] . '/template/page/o_kompanii/lireate-about-cms.inc.php'; ?>
					</section>
				<?php } elseif ($temp['apsw']){?>
					<div class="">
						<div class="row">
							<div class="col-xs-12">
							<?php if($temp['db_page'][0]['title']==1):?>
								<div class="title-ap"><h1><?=$temp['db_page'][0]['name'];?></h1></div>
							<?php endif;?>
								<div class="txt-content">
									<?=$temp['db_page'][0]['text'];?>
								</div>
							<?php require $_SERVER['DOCUMENT_ROOT'] . '/_shared/lireate-vk-cases-cms.inc.php'; ?>
							</div><!-- /obj -->
						</div>
					</div>
				<?php }else{ ?>
					<?php 
					
					    if(file_exists('./template/page/'.$temp['page'].'/index.php')){include('./template/page/'.$temp['page'].'/index.php');}
					    else{include('./template/page/'.$temp['page'].'.php');} ?>
				<?php }; ?>

			</div><!-- /page_container -->
		</div>
		<?php if(!in_array($temp['page'], array('quiz','spasibo_quiz','check','test'))){?>
		<!-- Footer
		====================-->
		<?php
			/* lireate_design_home_footer_v1 */
			/* lireate_design_cms_lu_footer_v1 */ if ($temp['page'] === 'home' || in_array($temp['page'], array('patent','patent_prom_obr'), true) || (isset($_SERVER['REQUEST_URI']) && preg_match('#^/mktu(?:[/?]|$)#', $_SERVER['REQUEST_URI']))) {
				$_lx_ul = dirname(__FILE__) . '/page/uslugi/lireate-uslugi-lib.php';
				if (is_file($_lx_ul)) { require_once $_lx_ul; }
				if (function_exists('lireate_uslugi_render_footer')) {
					lireate_uslugi_render_footer();
				} else {
				    include ('./template/parts/footer.php');
				}
			} else {
			    include ('./template/parts/footer.php');
			}
			?>
			
		<!--footer class="footer-ap">
			<div class="row">
			
				
				<div class="footer-links col-xs-12 col-lg-10 col-lg-offset-1">
					<h2>Мы работаем не только в Казани, но и в других городах России:</h2>
						<?php
							/* LIREATE 2026-11-17 audit05: ссылки на региональные поддомены убраны */?>
                </div>
                <div class="footer-links2 col-xs-12 col-lg-10 col-lg-offset-1">
                	<a href="http://lireate.ru/private_data">Согласие на обработку персональных данных</a>
                	<a href="http://lireate.ru/politika_konfidencialnosti">Политика конфиденциальности</a>
                </div>
			</div>
		</footer-->
		<?php } ?>
	</div>
	<!--<script src="{theme_url}/js/salvattore.min.js" type="text/javascript"></script>-->
	<link rel="stylesheet" href="https://cdn.callbackkiller.com/widget/cbk.css">
	<!--<script type="text/javascript" src="https://cdn.callbackkiller.com/widget/cbk.js?wcb_code=87d2968fc97cb4e99b7ed87e0fb4a5c4" charset="UTF-8" async></script>-->
	<style type="text/css">
	
		@media (max-width: 1020px){
			.main-title {
			    /*margin: 0 10px;*/
			    margin-top: 217px;
			    float: none;
			    display: block;
			    position: absolute;
				bottom: 20px;
				right: 0px;
				margin: 0 35px;
			    padding: 5px 20px;
			    width: auto;
			}
			.main-button.btn {
			    float: none;
			}

			.footer-links ul, .footer-links2 ul {
			    display: block;
			}

			.footer-links li, .footer-links2 li {
			    display: block;
			    text-align: center;
			}

			.content{
				padding-left: 0px;
			}

			#sidebar-wrapper{
				left: 0;
				box-shadow: none;
			}

			#sidebar-wrapper.toggled{
				left: 270px;
				box-shadow: 0px 0px 30px rgba(0, 0, 0, 0.6);
			}

			.title{
				font: 700 26px/38px 'Open Sans';
				margin-top: 25px;
			}

			.sub-title{
				font: 400 19px/28px "Open Sans",sans-serif;
			}

			.main-title h1{
				font: 700 26px/32px 'Open Sans';
				text-align: left;
			}

			.main-title h2{
				font: 25px/30px 'Open Sans';
				text-align: left;
			}
			.main-button.btn{
			    font: 14px/40px 'Open Sans';
			}
			.main-title{
				margin: 0 10px;
			}
			.product h1{
				font: 700 21px/27px 'Open Sans';
			    margin: 10px 0;
			}
			br{
				font-size: 0px;
			}

			p{
				font: 16px/26px Georgia,'Century Schoolbook L',Serif;
			}

			.content-button.btn{
				font: 18px/24px 'Open Sans';
			}

			.hamburger{
				display: block;
			}

			.cert {
				height: 400px !important;
			}

			.footer-ap {
			    padding-left: 0px;
			}
		}

		@media (max-width: 1199px){
			.col-xs-12.advant {
			    width: 100%;
			}
			.col-xs-12.advant:nth-child(2n+1){
				margin-right: 0;
			}
			.block-margin{
				margin-top: 10px;
			}
			.col-xs-12{
				margin-left: 0px;
			}
			.col-xs-6 .item{
			    width: 100%;
			}
			.col-xs-6 .item:nth-child(2n+1){
				margin-right: 0;
			}
		}
		
		@media (max-width: 767px){

			/*.thumbnail .im div {
			    height: 300px;
			}*/

			.item .thumbnail {
			    height: 480px;
			}

			.news_card {
				height: 410px;
			}
		}
		
		@media (max-width: 470px){

			.thumbnail .im div {
			    height: 200px;
			}

		}

		@media (min-width: 1200px){
			.col-lg-6.advant{
				width: 49%;
			}
			.col-lg-6.advant:nth-child(2n+1){
				clear: left;
				margin-right: 2%;
			}
			.block-margin{
				margin-top: 50px;
			}

			.thumbnail .im div {
			    height: 300px;
			}

			.item .thumbnail {
			    height: 560px;
			}

			.news_card {
				height: 490px;
			}

			.col-lg-4 .item{
				width: 49%;
			}
			.col-lg-4 .item:nth-child(2n+1){
				clear: left;
				margin-right: 2%;
			}
		}
	</style>
	<script>
		$('[name="tel"]').mask("+7 (999) 999-99-99");
	</script>
	
	
	<script>
        $(document).ready(function(){
          $(".show-tooltip").click(function(){
            $(".tooltip-method a").tooltip('show');
          });
          $(".hide-tooltip").click(function(){
            $(".tooltip-method a").tooltip('hide');
          });
          $(".toggle-tooltip").click(function(){
            $(".tooltip-method a").tooltip('toggle');
          });
          $(".destroy-tooltip").click(function(){
            $(".tooltip-method a").tooltip('destroy');
          });
        });
        $(document).ready(function(){
          $(".tooltip-examples a").tooltip();
          $(".tooltip-examples button").tooltip();
        });
        
        $(document).ready(function(){
          $(".tooltip-options a").tooltip({
            title : 'Текст всплывающей подсказки при отсутствии атрибута title.',
            placement: 'right',
            delay: { show: 500, hide: 100 }
          });
        });
    </script>
    <script>
        $(document).ready(function(){
          // Инициализирует карусель
          $(".start-slide").click(function(){
            $("#myCarousel2").carousel('cycle');
          });
          // Останавливает процесс автоматической смены слайдов карусели
          $(".pause-slide").click(function(){
            $("#myCarousel2").carousel('pause');
          });
          // Осуществляет переход на предыдущий слайд
          $(".prev-slide").click(function(){
            $("#myCarousel2").carousel('prev');
          });
          // Осуществляет переход на следующий слайд
          $(".next-slide").click(function(){
            $("#myCarousel2").carousel('next');
          });
          // Осуществляет переход на 0 слайд карусели
          $(".slide-one").click(function(){
            $("#myCarousel2").carousel(0);
          });
          // Осуществляет переход на 1 слайд карусели
          $(".slide-two").click(function(){
            $("#myCarousel2").carousel(1);
          });
          // Осуществляет переход на 2 слайд карусели
          $(".slide-three").click(function(){
            $("#myCarousel2").carousel(2);
          });
        });
    </script>
    <script>
    
    
    function select_change_two(){
	    switch (this.value){
	    	case "На whatsapp":
	    		$('#email2').attr('required',false);
	    		$('.email2').addClass('hidden');
		    	break;
	        case "На viber":
	    		$('#email2').attr('required',false);
	    		$('.email2').addClass('hidden');
		    	break;
		     case "На telegram":
	    		$('#email2').attr('required',false);
	    		$('.email2').addClass('hidden');
		    	break;
	    	case 'По почте':
	    	    $('.email2').removeClass('hidden');
		    	$('#email2').attr('required',true);
		    	break;
	    }
	}
	$('#order_block3').change(select_change_two);
    </script>

    <style>
    				
    	/* ==================================================*/  
        /* ====================БЛОК С ЦЕНАМИ ================*/
        /* ==================================================*/  

        .contein_price{
            background-color:#e6ebed;
            width:100%;
            padding-top:15px;
            padding-bottom:15px;
            margin-top: 20px;
        }
    
        .contein_price p{ 
            text-align:left;
            display: block;
            font-family: Roboto, "Helvetica Neue", Arial, sans-serif !important;
            font-weight: 350;
            font-style: normal;
            color: #32404d;
            font-size: 14px;
        }
        
        .contein_price h3{
            text-align:center;
        }
            
            
        .alignCenter{
            padding:0 20px;
            text-align:center !important;
        }
        
    
        .contein_price h2{
            margin-top: 0;
            font-size: 2.5em ;
            font-weight: 700;
            line-height: 1.215;
            text-align: center;
            text-transform: uppercase;
            font-family: Roboto, "Helvetica Neue", Arial, sans-serif;
            color: #32404d;
        }
    
        .contein_price_flex{
            padding: 15px 10px;
            display:flex;
            justify-content:space-around;
            width:100%;
            max-width:1100px;
            margin:0 auto;
        }
        
        .price_block{
            width:50%;
        }
            
        .price_advantage{
            display:flex;
            min-height:290px;
        }
            
        .price_advantage_icon{
            width:40px;
            height:40px;    
            background-size:cover;
        }
            
        .price_advantage_text{
            padding:3px 20px 20px;
            width:90%;
        }
        
        .contein_price h4{ 
            margin-top: 0;
            font-size: 2em ;
            font-weight: 700;
            line-height: 1.215;
            text-align: left;
            text-transform: uppercase;
            font-family: Roboto, "Helvetica Neue", Arial, sans-serif;
            color: #32404d;
        }
    
        .H4colorAndPadding{
            font-size: 1.7em !important;
            color:white !important;
            padding:20px;
            background-color:#1b4769;
            text-align:center !important;
        }
        
        .offer_block{
            background:white;
            
        }
    
    
        .offer_block_text{
            padding:0px 30px 10px;
        }
            
        .offer_block_text ul{
            padding-left: 0;
            margin-top: 0;
            list-style: none;
            display: block;
        }
          
    
        .offer_block_text li {
            display: list-item;
            text-align:left;
            position: relative;
            padding-left: 25px;
            margin-bottom: 7px;
            font-family: Roboto, "Helvetica Neue", Arial, sans-serif !important;
            font-weight: 350 !important;
            font-style: normal !important;
            color: #32404d !important;
            font-size: 14px !important;
        }
    
        .offer_block_text li::before {
            content: "";
            position: absolute;
            top: 0.357142857142857em;
            left:5px;
            width: 15px;
            height: 11px;
            background-image: url(template/img/li.svg);
            background-repeat: no-repeat;
        }
    
        .price_cost{
           margin-top: 0 !important;
            margin-bottom: 27px !important;
            font-size: 1.1em !important;
            font-weight: 700 !important;
            text-align: left !important;
            text-transform: uppercase !important;
            font-family: Roboto, "Helvetica Neue", Arial, sans-serif !important;
            color: #a5b1bc!important;
        }
      
         .price_cost_span{
             color: #32404d !important;
             font-size: 1.7em !important;
         }
        
        @media (max-width: 1380px){
            .price_advantage{
                min-height:390px;
            }
            
            .price_block{
                width:50%;
            }
        
            .H4colorAndPadding{
                font-size: 1.5em !important;
            }
        }         
      
        @media (max-width: 1170px){
            .price_advantage{
                display:none;
            }
            
            .price_block{
                width:55%;
            }
        
            .BcB_width{
                width:80% !important;
                max-width:250px !important;
                margin:0 auto;
            }
        
            .contein_price_flex{
                flex-direction:column;
                align-items:center;
                flex-wrap:wrap;
            }   
        
            .offer_block{
                margin-bottom:25px;
            }
        }   
    
    
    
        @media (max-width: 650px){
            .price_block{
                width:75%;
            }
        }     
        
        @media (max-width: 500px){
            .price_block{
                width:96%;
            }
            
            .conteinerY{
                padding:15px;
            }
        
            .offer_block{
                margin-bottom:15px;
            }
        }      
        
        @media (min-width: 1171px){
            .offer_block ul {
                height:330px;
            }
            .price_block{
                width:50%;
            }
        }
            
        @media (min-width: 1290px){
            .offer_block ul {
                height:300px;
            }
            .price_block{
                width:50%;
            }
        }
             
    
        @media (min-width: 1330px){
            .price_block{
                width:50%;
            }
        }
	</style>
	
	
	
	

	<?php if($temp['page']=='request'):?>
<!----------------------------------------------------->
<!---------РАЗДЕЛ САЙТА: ЗАЯВКА ЧЕРЕЗ AMOCRM----------->
<!----------------------------------------------------->
<script>
        // Меняем viewport в зависимости от разрешения экрана
        var content;
        // Если экран понитора маленький, то отображаем форму на весь экран
        if ( (window.screen.width < 900) || (window.screen.height < 900) ) {
            content = "width=500";
        } else {
            content = "width=device-width, initial-scale=1";
        }
        // Меняем content viewport'а
        document.getElementsByName("viewport")[0].setAttribute("content", content);
    </script>

<style>
#request_block{
    width:100%;

    background-color: #0193d0;
    
}
 #request_block h1{
         font-family: Gilroy-ExtraBold, sans-serif;
    font-size: 41px;
    text-align: left;
    color: #FFFFFF;
    text-shadow: 4px 4px 5px rgba(0,0,0,0.1);
    
    margin-bottom: 16.8px;
    max-width: 500px;
    line-height: 48px;
    margin: auto;
    padding: 80px 20px 20px 20px;
    font-weight: 600;
 }
.page_container{display:none;}
.content { padding-left: 0px; }
.navbar{DISPLAY:NONE;}
.footer-ap{display:none;}
.hamburger{display:none;}
.is-closed{display:none;}

.request_form_position{margin:auto;  width:100%;   max-width: 537px !important; padding:0 10px 0px 0px;}
    

    
    
  html{
       background-color: #0193d0;
  }  
  
</style>

<section id="request_block">
    
    <h1>Получи коммерческое предложение по регистрации своего бренда/товарного знака</h1>
    <article class="request_form_position"><script>var amo_forms_params = {"id":445576,"hash":"116c267deaa2cd436b1ff0eab8b01209","locale":"ru"};</script><script id="amoforms_script" async="async" charset="utf-8" src="https://forms.amocrm.ru/forms/assets/js/amoforms.js"></script></article>
    
</section>

<script>	
	
 window.onload = function() {
    
  	
	function submitHandler(e) {
  	e.preventDefault();
		
    alert('Ура');
}

document.querySelectorAll("form").forEach(form => form.addEventListener("submit", submitHandler));
document.querySelectorAll('form').forEach(form => form.addEventListener('submit', submitHandler));
 };

	
</script>

<?php endif?>  
	
	
	

<section id="personsSlide">
    <div  class="personsSlideClose JS_personsSlideCloseButton"></div>
    
    <div class="personsSlideCenter">

        <div class="prev "><div class="leftButton"></div></div>
        <div class="personSlideCloseButton JS_personsSlideCloseButton ">
        <div class="closeButton"><div></div><div></div></div>
        </div>    
        <div class="next"><div class="rightButton"></div></div>

            <div class="slide">
                <div class="slidePhoto"><img loading="lazy" width="120" height="120" src="/template/images/team/person/11.webp" alt="Сотрудник LIREATE"></div>
                <div class="slideDescription">
                    <h4>Горбачев Юрий Александрович</h4>
                    <p>Должность: Управляющий партнёр</p>
                    
                </div>
            </div>

            <div class="slide">
                <div class="slidePhoto"><img loading="lazy" width="120" height="120" src="/template/images/team/person/10.webp" alt="Сотрудник LIREATE"></div>
                <div class="slideDescription">
                    <h4>Бураков Андрей Геннадьевич</h4>
                    <p>Должность: Управляющий партнёр</p>
                    
                </div>
            </div>
           
            <div class="slide">
                <div class="slidePhoto"><img loading="lazy" width="120" height="120" src="/template/images/team/person/1.webp" alt="Сотрудник LIREATE"></div>
                <div class="slideDescription">
                    <h4>Габдрахманова Эльвира Дамировна</h4>
                    <p>Должность: Руководитель отдела регистрации интеллектуальной собственности</p>
                    
                </div>
            </div>

         
            

            

            <div class="slide">
                <div class="slidePhoto"><img loading="lazy" width="120" height="120" src="/template/images/team/person/5.webp" alt="Сотрудник LIREATE"></div>
                <div class="slideDescription">
                    <h4>Захарова Ирина Николаевна</h4>
                    <p>Должность: Специалист по регистрации интеллектуальной собственности</p>
                    
                </div>
            </div>

            <div class="slide">
                <div class="slidePhoto"><img loading="lazy" width="120" height="120" src="/template/images/team/person/6.webp" alt="Сотрудник LIREATE"></div>
                <div class="slideDescription">
                    <h4>Прокопьева Алия Раифовна</h4>
                    <p>Должность: Специалист по регистрации интеллектуальной собственности</p>
                   
                </div>
            </div>

            <!--div class="slide">
                <div class="slidePhoto"><img loading="lazy" width="120" height="120" src="/template/images/team/person/7.webp" alt="Сотрудник LIREATE"></div>
                <div class="slideDescription">
                    <h4>Сенатова Валерия Станиславовна</h4>
                    <p>Должность: Специалист по регистрации интеллектуальной собственности</p>
                   
                </div>
            </div>

            <div class="slide">
                <div class="slidePhoto"><img loading="lazy" width="120" height="120" src="/template/images/team/person/8.webp" alt="Сотрудник LIREATE"></div>
                <div class="slideDescription">
                    <h4>Томилова Раиса Николаевна</h4>
                    <p>Должность: Делопроизводитель отдела по регистрации интеллектуальной собственности</p>
                </div>
            </div-->

            <div class="slide">
                <div class="slidePhoto"><img loading="lazy" width="120" height="120" src="/template/images/team/person/9.webp" alt="Сотрудник LIREATE"></div>
                <div class="slideDescription">
                    <h4>Ульданова Алёна Анатольева</h4>
                    <p>Должность: Специалист по регистрации интеллектуальной собственности</p>
                </div>
            </div>
            
            <div class="slide">
                <div class="slidePhoto"><img loading="lazy" width="120" height="120" src="/template/images/team/person/12.webp" alt="Сотрудник LIREATE"></div>
                <div class="slideDescription">
                    <h4>Азизова Гузель Рустамовна</h4>
                    <p>Должность: Специалист патентного отдела</p>
                </div>
            </div>
            
            <div class="slide">
                <div class="slidePhoto"><img loading="lazy" width="120" height="120" src="/template/images/team/person/13.webp" alt="Сотрудник LIREATE"></div>
                <div class="slideDescription">
                    <h4>Хайретдинов Рустем Афузалович</h4>
                    <p>Должность: Специалист патентного отдела</p>
                </div>
            </div>
            
            
            <div class="slide">
                <div class="slidePhoto"><img loading="lazy" width="120" height="120" src="/template/images/team/person/16.webp" alt="Сотрудник LIREATE"></div>
                <div class="slideDescription">
                    <h4>Яковлева Вероника Владиславовна</h4>
                    <p>Должность: Специалист патентного отдела</p>
                </div>
            </div>
            <div class="slide">
                <div class="slidePhoto"><img loading="lazy" width="120" height="120" src="/template/images/team/person/17.webp" alt="Сотрудник LIREATE"></div>
                <div class="slideDescription">
                    <h4>Квасова Анна Андреевна</h4>
                    <p>Должность: Специалист по регистрации интеллектуальной собственности</p>
                </div>
            </div>
            <div class="slide">
                <div class="slidePhoto"><img loading="lazy" width="120" height="120" src="/template/images/team/person/20.webp" alt="Сотрудник LIREATE"></div>
                <div class="slideDescription">
                    <h4>Адиятуллина Лилия Ильдаровна</h4>
                    <p>Должность: Специалист по регистрации интеллектуальной собственности</p>
                </div>
            </div>
            <div class="slide">
                <div class="slidePhoto"><img loading="lazy" width="120" height="120" src="/template/images/team/person/21.webp" alt="Сотрудник LIREATE"></div>
                <div class="slideDescription">
                    <h4>Гибадуллина Алия Юнусовна</h4>
                    <p>Должность: Специалист по регистрации интеллектуальной собственности</p>
                </div>
            </div>
            <div class="slide">
                <div class="slidePhoto"><img loading="lazy" width="120" height="120" src="/template/images/team/person/22.webp" alt="Сотрудник LIREATE"></div>
                <div class="slideDescription">
                    <h4>Ибрагимова Алиса Айратовна</h4>
                    <p>Должность: Специалист по регистрации интеллектуальной собственности</p>
                </div>
            </div>
    </div>
   
</section>





<script>
(function () {
    var personsRoot = document.querySelector('.persons');
    if (!personsRoot) {
        return;
    }
    var personsSlide = document.getElementById('personsSlide');
    var persons = personsRoot.querySelectorAll('div');
    var slides = document.querySelectorAll('.slide');
    for (var i = 0; i < persons.length; i++) {
        persons[i].onclick = function (idx) {
            return function () {
                for (var n = 0; n < slides.length; n++) {
                    slides[n].style.display = 'none';
                }
                if (personsSlide) {
                    personsSlide.style.display = 'flex';
                }
                slides[idx].style.display = 'flex';
                if (personsSlide) {
                    setTimeout(function () { personsSlide.style.opacity = '1'; }, 200);
                }
            };
        }(i);
    }
    var personSlideButton = document.querySelectorAll('.JS_personsSlideCloseButton');
    for (var j = 0; j < personSlideButton.length; j++) {
        personSlideButton[j].onclick = function () {
            if (!personsSlide) return;
            personsSlide.style.opacity = '0';
            setTimeout(function () { personsSlide.style.display = 'none'; }, 500);
        };
    }
    var nextButton = document.querySelector('.next');
    if (nextButton && slides.length) {
        nextButton.onclick = function () {
            var slideNumber = 0;
            for (var k = 0; k < slides.length; k++) {
                if (slides[k].style.display === 'flex') {
                    slideNumber = k;
                    break;
                }
            }
            for (var n = 0; n < slides.length; n++) {
                slides[n].style.display = 'none';
            }
            if (slideNumber === slides.length - 1) {
                slideNumber = -1;
            }
            slides[slideNumber + 1].style.display = 'flex';
        };
    }
    var prevButton = document.querySelector('.prev');
    if (prevButton && slides.length) {
        prevButton.onclick = function () {
            var slideNumber = 0;
            for (var k = 0; k < slides.length; k++) {
                if (slides[k].style.display === 'flex') {
                    slideNumber = k;
                    break;
                }
            }
            for (var n = 0; n < slides.length; n++) {
                slides[n].style.display = 'none';
            }
            if (slideNumber === 0) {
                slideNumber = slides.length;
            }
            slides[slideNumber - 1].style.display = 'flex';
        };
    }
})();
</script>
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
		
	<?php /* lireate_design_service_mobile_dock_v2 footer */ $_lx_svc_dock_pg = array('patent','patent_prom_obr','mktu','mktu1','uslugi','poisk_tovarnyh_znakov','ekspertiza_tovarnogo_znaka','mezhdunarodnaja_registracija_tovarnogo_znaka','registracija_torgovyh_marok_i_logotipov');
	$_lx_svc_dock = in_array($temp['page'], $_lx_svc_dock_pg, true);
	if (!$_lx_svc_dock) {
	  $_lx_svc_ru = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';
	  $_lx_svc_dock = (bool) preg_match('#^/(?:mktu1?|poisk_tovarnyh_znakov|ekspertiza_tovarnogo_znaka|mezhdunarodnaja_registracija_tovarnogo_znaka|registracija_torgovyh_marok_i_logotipov)(?:[/?]|$)#', $_lx_svc_ru);
	}
	if ($_lx_svc_dock) {
	  $_lx_svc_ru = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';
	  if (preg_match('#^/(?:findtz|registraciya_tovarnogo_znaka(?:_s_oplatoy_za_rezultat|_v_kitae)?|prodlenie_tovarnogo_znaka|vozrazhenie_protiv_registracii_tovarnogo_znaka|zashchita_avtorskih_prav|dogovory|registraciya_programm_evm)(?:[/?]|$)#', $_lx_svc_ru)) {
	    $_lx_svc_dock = false;
	  }
	}
	if ($_lx_svc_dock) {
	  include __DIR__ . '/lireate/lireate-service-mobile-bar.inc.php';
	}
?>
</body>
</html>

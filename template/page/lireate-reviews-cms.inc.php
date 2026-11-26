<?php
// audit07 D5: brand / city / certificate line + link to /portfolio/<slug>/
if (!empty($_SERVER['DOCUMENT_ROOT']) && is_file($_SERVER['DOCUMENT_ROOT'] . '/data/lireate-cases-lib.php')) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/data/lireate-cases-lib.php';
}
$lx_cms_light_head = dirname(dirname(__DIR__)) . '/pages/_shared/lireate-cms-light-v4-head.inc.php';
if (is_file($lx_cms_light_head)) {
    require_once $lx_cms_light_head;
    if (function_exists('lireate_cms_light_v4_print_css')) {
        lireate_cms_light_v4_print_css(array('/template/css/lireate-reviews-light-v4.css'));
    }
}
echo '<script>if(document.body){document.body.classList.add("lireate-reviews-preview");}</script>' . "\n";
$lx_lh4_subnav = dirname(dirname(__DIR__)) . '/pages/_shared/lireate-lh4-subnav-lib.php';
if (is_file($lx_lh4_subnav)) {
    require_once $lx_lh4_subnav;
    lireate_lh4_include_subnav();
}
?>
<style>
    /* audit07 D5 */
    .lx-rv-case{ margin-top:6px !important; font-size:14px !important; line-height:1.4 !important; color:#cbd5e1; }
    .lx-rv-case a.lx-rv-case-link{ font-weight:600; color:#93c5fd !important; text-decoration:none; white-space:nowrap; }


.page_container {
        padding: 0px; 
        background: white;
    }
    p{margin:0;}
    /**************потом перекинь в файл section.css**************************/
    .sectionPadding{padding:128px 0;}
    
    .contentBox{
        width: calc(100% - 128px); max-width:1200px;
        height: 100%;
        margin:0 auto;}
    
    @media(max-width:1600px){.sectionPadding{padding:96px 0;}}
    @media(max-width:700px){
        .sectionPadding{padding:64px 0;}
        .contentBox{width: calc(100% - 64px);}}
    
    @media(max-width:450px){
        .contentBox{width: calc(100% - 32px);}}
    
    /**************************************************************************/
    
    
    
    /**************потом перекинь в файл distance.css**************************/
    
    .margin_top_64{ margin-top:64px; }
    .margin_top_32{ margin-top:32px; }   
    
    
    @media(max-width:700px){
      .margin_top_64{ margin-top:32px;}
      .margin_top_32{ margin-top:16px; }
    }
    /**************************************************************************/
    
    /**************потом перекинь в файл font.css**************************/
    .h1{
        padding:0; margin:0;
        font-size: calc(1.6vw + 22px);
        line-height: calc(1.6vw + 20px);
        font-weight: 800;
        color: #262c40;
        text-transform: uppercase;
    }
    .h2{
        padding:0; margin:0;
        color: #262c40;
        font-weight: 800;
        font-size: calc(.6vw + 20px);
        text-transform: uppercase;
    }
    
    .h3{
        font-family: "Open Sans",sans-serif!important;
        font-weight: 600;
        color: #262c40;
        margin: 0;
        font-size: calc(.2vw + 18px);
        
    }
    .default_text{
        font-family: "Open Sans",sans-serif!important;
        font-size: 15px;
        color: #262c40;
        line-height: 22px;} 
        
    .titleDesc{
        padding: 0;
        margin: 0;
        font-size: calc(0.3vw + 15px);
        font-weight: 400;
        color: #262c40;
        line-height: 26px;
    }
    .simple_text{
        font-size: 16px;
        color: #262c40;
        line-height: 22px;
    }
    @media(max-width:1000px){
        .titleDesc{line-height: 18px;}
    }
    /**************************************************************************/
#face{
    position:relative;
    overflow:hidden;
}    
#face .h1{
    margin-bottom:32px;}

#face .bg{
    position:absolute;
    top:0; left:0;
    width:100%;
    height:100%;
    background-image:url(/template/img/page/video_rewiev/bg.svg);
    background-size:cover;
    background-position:center 20%;
    opacity:0.6;
}

#face .bg_main{
    z-index: 1;
    top: 50px;
    height: 100%;
    width: calc(100% - 128px);
    max-width: 1200px;
    background-image: url(/template/img/page/video_rewiev/main_bg.svg);
    background-size: 50%;
    background-position: right top;
    background-repeat: no-repeat;
    margin: auto;
    position: absolute;
    left: 0;
    right: 0;
}
#face .titleDesc span{
    font-weight:700;
    color:red;
}
.contentBox{position:relative; z-index:2;}

.face_line {
    position: relative;
    height: 30px;
    background: #E7EBF5;
    box-shadow: 0px -15px 15px -12px rgba(59, 78, 127, 0.1);
}

@media(max-width:1000px){
    #face .bg_main{background-size: 60%;}}

@media(max-width:880px){
    #face { min-height: 450px;}
    #face .bg_main {
        background-position: 170px bottom;
        background-size: 70%;}
    #face .bg {
        background-repeat: no-repeat;}
}

@media(max-width:780px){
    #face {min-height: 550px;}
    #face .bg {
        background-position: top;}
   #face .bg_main {
    background-position: right bottom;
    background-size: 90%;}}
    
@media(max-width:720px){
    #face .bg_main {
        background-size: 100%;}
    .titleDesc { 
        margin-bottom: 8px;
        max-width:80%;
    }
}

@media(max-width:660px){
   #face .bg_main {
    background-size: 110%;}}    

@media(max-width:660px){
   #face .bg_main {
        width:100%;
        background-size: 100%;}
   }
   
@media(max-width:500px){
    #face {
        height: 100vh;
        max-height: 550px;}
    #face .bg_main {
        background-size: 120%;
        background-position: 70% 110%;}
   }   
   
 @media(max-width:460px){
    #face .bg_main {
        background-size: 140%;}}
        
@media(max-width:410px){
    #face .bg_main {
        background-size: 150%;}}  
        
 @media(max-width:370px){
    #face .bg_main {
        background-size: 150%;}}

</style>
    
    
    
<section class="sectionPadding" id="face">
    <div class="bg"></div>
    <div class="bg_main"></div>
    <div class="contentBox">
        <h1 class="h1">Видео отзывы</h1>
        <p class="titleDesc">более <span>1565</span> зарегистрированных товарных знаков</p>
        <p class="titleDesc">более <span>1300</span> благодарных клиентов</p>
    </div>
</section>
<div class="face_line"></div>
<style>
    

        
    
    #list .contentBox{
        display:flex;
        flex-wrap:wrap;
        justify-content: space-evenly;}
        
    #list .contentBox>div{
        cursor:pointer;
        width:calc(33.333333% - 21.666666666px);
        position:relative;
        margin-bottom:16px;
        border: 1px solid #e4e6eb;
        background:white;
        border-radius: 8px;
        box-shadow: 10px 10px 10px -10px rgba(0, 0, 0, 0.20);
        overflow: hidden;
    }
    
    #list .contentBox>div:hover{
        box-shadow: 0px 0px 30px -10px rgba(0, 0, 0, 0.30);
    }
    
    #list .img{
        width:150px; height:70px;
        background-size: contain;
        background-repeat: no-repeat;
        margin-bottom:32px;
        background-position: left center;
        
    }
    
    #list .default_text{
        margin-top: 4px;
        font-weight: 400;}
    
        
    #list .default_text span{
        color: #666f90;
        font-weight: 200;}
    
    #list .text_box{
        padding:24px;
    }
    
    .company_site{
        width: 100%;
        display: inline-flex;
        overflow: hidden;}
    
    .play_button{
        position: absolute;
        width: 20%;
        top: 35%;
        margin: auto;
        left: -50%;
        right: -50%;
        opacity: 0.6;
        transition: 0.4s;
    }
    .rewiev_head{
        position:relative;
    }
    #list .contentBox>div:hover .play_button{
        opacity:1;
        /*box-shadow: 10px 10px 10px -10px rgba(0, 0, 0, 0.20);*/
    }
    @media(max-width:1400px){
         #list .contentBox>div{
            width:calc(50% - 16px); }  
    }
     @media(max-width:1000px){
        .play_button{
            opacity:1;
            /*box-shadow: 10px 10px 10px -10px rgba(0, 0, 0, 0.20);*/}
     }
     
    @media(max-width:600px){
        #list .img{height:50px;}
        #list .contentBox>div{
            height:auto;
            width:100%;}
        #list .text_box{
            position: initial;
            padding:16px;
        }
    }
    .img_box{
        width:100%;
    }
</style>
    
    
    
<section class="sectionPadding" id="list">
    <div class="contentBox">
  
    <?php
        if (defined('LIREATE_REVIEWS_PREVIEW') && LIREATE_REVIEWS_PREVIEW) {
            echo '<p class="lx-rv-preview-note">Превью Light V4: список отзывов заполняется с БД на хостинге после install.</p>';
        } else {
        if (!function_exists('lireate_cms_connect_db')) {
            require_once __DIR__ . '/lireate-cms-db.inc.php';
        }
        $connect = lireate_cms_connect_db();
        if ($connect) {
        $result = $connect->query('Select * from reviews');
        while ($result && ($row = $result->fetch_assoc())) { ?>
        
        <div class="review_box" data-link="<?php echo $row['videoUrl']; ?>">          
            
            <div class="rewiev_head">
                <div class="play_button">
                    <img class="img_box" src="/template/img/page/video_rewiev/youtube.svg" alt="" aria-hidden="true">
                </div>
                <!--div class="img" style="background-image:url(/template/images/reviews/<?php echo $row['logoUrl']; ?>); <?php echo $row['style']; ?> "></div-->
                <?php if($row['img_link']){ ?>
                    <img class="img_box" src="/template/img/page/video_rewiev/images/<?php echo $row['img_link']; ?>" alt="Видеоотзыв клиента<?php echo $row['city'] ? ', ' . htmlspecialchars($row['city'], ENT_QUOTES, 'UTF-8') : ''; ?>" loading="lazy">
               <?php }else{ ?>
                     <img class="img_box" src="/template/img/page/video_rewiev/images/non_image.jpg" alt="Видеоотзыв клиента" loading="lazy">
               <?php } ?>
            </div>
            <div class="text_box">
                <!--p class="default_text"><span>Компания:</span> <?php echo $row['company']; ?> </p-->
                <p class="default_text"><span>Адрес:</span> <?php echo $row['city']; ?> </p>
                <?php /* audit07 D5 */ echo function_exists('lireate_cases_review_line') ? lireate_cases_review_line($row['id']) : ''; ?>
                <!--p class="default_text company_site">
                    <span>Сайт: </span> 
                    <a href="<?php echo $row['site']; ?>" target="_blank">
                        <?php if(stristr($row['site'], 'insta')) {echo '&nbsp;instagram.com';} else{ echo '&nbsp;'.$row['site'];} ?>
                    </a>  
                </p-->
                
                <?php if($row['networkUrl']){ ?>
                <!--p class="default_text company_site">
                    <span>Соц.сети: </span> 
                    <a href="<?php echo $row['networkUrl']; ?>" target="_blank">
                        <?php if(stristr($row['networkUrl'], 'insta')) {echo '&nbsp;instagram.com';} else{ echo '&nbsp;'.$row['networkUrl'];} ?>
                    </a>  
                </p-->
                <?php } ?>
                
            </div>
        </div>
    <?php }
        }
        } ?>    
        

    </div>
</section>

<style>
    #video_box{
        display:none;
        position:fixed;
        width:100%; height:100%;
        top:0; left:0;
        z-index: 500;
        align-items: center;
        justify-content:center;
    }
    #video_box_shadow{
        position:absolute; width:100%; height:100%;
        background-color: rgba(38, 44, 64, 0.8);
    }
    
    #video_box_content_box{
        position: relative;
        z-index: 101;
        width: 75vw;
        max-height: 450px;
        height: 45vw;
        max-width: 750px;
        padding: 4px;
        border-radius: 8px;
    }
    #video_frame{
        width:100%; 
        height:100%; 
        border-radius: 8px;
        border:none;
    }
     @media(max-width:650px){
        #video_box_content_box {
            width:100%;
            padding:0;
            height:60vw;}
            
        #video_frame{
            border-radius: 0px;
         }
     }
    
</style>

<section id="video_box">
    <div id="video_box_shadow"></div>
    <div id="video_box_content_box"></div>
</section>

<script async src="https://www.youtube.com/player_api"></script><!-- audit08 S7: async, YT API is not used synchronously -->
<script>

    

    let review_box = document.querySelectorAll('.review_box');
    
    for(let i = 0; i < review_box.length; i++){
        console.log('добавляем функцию к боксу ' + i)
        review_box[i].addEventListener('click', function(){
            let iframe = document.createElement('iframe');
            iframe.setAttribute('id','video_frame');
            iframe.setAttribute('src',review_box[i].getAttribute('data-link'));
            iframe.setAttribute('allow','accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture');
            iframe.setAttribute('allowfullscreen', '');
            video_box_content_box.append(iframe);
            //video_frame.setAttribute('src', review_box[i].getAttribute('data-link'));
            video_box.style.display = 'flex';
        });
    }
    
    video_box_shadow.addEventListener('click', function(){
        video_box.style.display = 'none';
        video_frame.remove();
    });



    
</script>




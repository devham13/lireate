<?php
// audit07 C4 cases wave 1: links to /portfolio/<slug>/
if (!empty($_SERVER['DOCUMENT_ROOT']) && is_file($_SERVER['DOCUMENT_ROOT'] . '/data/lireate-cases-lib.php')) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/data/lireate-cases-lib.php';
}
// audit07 C2: visible certificate line under each trade mark (RUTM only).
if (!function_exists('lireate_pf_rutm_number')) {
    function lireate_pf_rutm_number($link)
    {
        $link = trim((string) $link);
        if (!preg_match('~[?&]DB=RUTM(?:&|$)~i', $link)) {
            return '';
        }
        if (!preg_match('~[?&]DocNumber=(\d{3,9})~i', $link, $m)) {
            return '';
        }
        return $m[1];
    }
}
if (!function_exists('lireate_pf_fips_url')) {
    function lireate_pf_fips_url($link)
    {
        $n = lireate_pf_rutm_number($link);
        if ($n === '') {
            return (string) $link;
        }
        return 'https://www.fips.ru/registers-doc-view/fips_servlet?DB=RUTM&DocNumber=' . $n . '&TypeFile=html';
    }
}
if (!function_exists('lireate_pf_cert_line')) {
    function lireate_pf_cert_line($tm)
    {
        $n = lireate_pf_rutm_number(isset($tm['fips_link']) ? $tm['fips_link'] : '');
        if ($n === '') {
            return '';
        }
        $h = 'htmlspecialchars';
        $out = '<div class="lx-pf-cert"><a href="' . $h(lireate_pf_fips_url($tm['fips_link']), ENT_QUOTES, 'UTF-8')
            . '" target="_blank" rel="nofollow noopener">Свидетельство № ' . $n . '</a>';
        $d = isset($tm['finish_date']) ? trim((string) $tm['finish_date']) : '';
        $d = trim(preg_replace('~\s*г\.?\s*$~u', '', $d));
        if (preg_match('~^\d{2}\.\d{2}\.\d{4}$~', $d)) {
            $out .= ' · решение от ' . $d;
        }
        $city = isset($tm['adress']) ? trim((string) $tm['adress']) : '';
        if ($city !== '') {
            $out .= ' · ' . $h($city, ENT_QUOTES, 'UTF-8');
        }
        return $out . '</div>';
    }
}
$lx_cms_light_head = dirname(dirname(__DIR__)) . '/pages/_shared/lireate-cms-light-v4-head.inc.php';
if (is_file($lx_cms_light_head)) {
    require_once $lx_cms_light_head;
    if (function_exists('lireate_cms_light_v4_print_css')) {
        lireate_cms_light_v4_print_css(array('/template/css/lireate-portfolio-light-v4.css'));
    }
}
echo '<script>if(document.body){document.body.classList.add("lireate-portfolio-preview");}</script>' . "\n";
?>
<?php
            if (defined('LIREATE_PORTFOLIO_PREVIEW') && LIREATE_PORTFOLIO_PREVIEW) {
                $array = array();
            } else {
            if (!function_exists('lireate_cms_connect_db')) {
                require_once __DIR__ . '/lireate-cms-db.inc.php';
            }
            $connect = lireate_cms_connect_db();
            $array = array();
            if ($connect) {
            $result = $connect->query("SELECT * FROM portfoliocase");

            while ($result && ($row = $result->fetch_assoc())) { 
                
                //Если нет региона в массиве
                if(!array_key_exists($row['region'], $array)){
                    $array[$row['region']] = array();
                    $array[$row['region']][$row['city']] = array();
                    $array[$row['region']][$row['city']][$row['name']] = array(
                        'title' => $row['name'],
                        'time' => $row['time'],
                        'start_date' => $row['start_date'],
                        'finish_date' => $row['finish_date'],
                        'img' => $row['folder'],
                        'img_exp' => $row['img_exp'],
                        'adress' => $row['city'], // audit07: в HTML только город, полный address из БД не выводим
                        'activity' => $row['kind'],
                        'fips_link' => $row['fips_link'],
                        );
                }
                
                //Если регион уже есть в массиве, а города нету
                elseif(!array_key_exists($row['city'], $array[$row['region']])){ 
                    $array[$row['region']][$row['city']] = array();
                    $array[$row['region']][$row['city']][$row['name']] = array(
                        'title' => $row['name'],
                        'time' => $row['time'],
                        'start_date' => $row['start_date'],
                        'finish_date' => $row['finish_date'],
                        'img' => $row['folder'],
                        'img_exp' => $row['img_exp'],
                        'adress' => $row['city'], // audit07: в HTML только город, полный address из БД не выводим
                        'activity' => $row['kind'],
                        'fips_link' => $row['fips_link'],
                        );
                        
                }
                
                //регион и город есть
                else{
                    $array[$row['region']][$row['city']][$row['name']] = array(
                        'title' => $row['name'],
                        'time' => $row['time'],
                        'start_date' => $row['start_date'],
                        'finish_date' => $row['finish_date'],
                        'img' => $row['folder'],
                        'img_exp' => $row['img_exp'],
                        'adress' => $row['city'], // audit07: в HTML только город, полный address из БД не выводим
                        'activity' => $row['kind'],
                        'fips_link' => $row['fips_link'],
                        );
                }
               
               $n++;
            }
            }
            }
?>








<style>

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
    
<style>

    #face{
        background-color: #e7ebf4;
        background-image: url(/template/img/page/portfolio/for_page/face_bg.png);
        background-repeat: no-repeat;
        background-position: 80% center;
        background-size: 45%;
    }

    }
    #face .titleDesc span{
        font-weight:700;
        color:red;}


    }
</style>    
    
<section class="sectionPadding" id="face">
    <div class="contentBox">
        <h1 class="h1">Портфолио: зарегистрированные товарные знаки клиентов</h1>
        <p class="titleDesc"><span>32</span> региона РФ</p>
        <p class="titleDesc"><span>68</span> населенных пунктов</p>
        <p class="titleDesc"><span>1565</span> зарегистрированных товарных знаков</p>
    </div>
</section>
<div class="face_line"></div>
<?= function_exists('lireate_cases_portfolio_strip') ? lireate_cases_portfolio_strip() : '' ?>



<style>

    #portfolio{
        padding:96px 0 64px 0;
    }
    
    #portfolio .contentBox{
        display: flex;
        flex-wrap: wrap;
        flex-direction: column;
        height: 1650px;
    }

    #portfolio .default_text{
        display: table;
        border-bottom: 1px dashed #9eaad3;
        cursor:pointer;
        font-size: 14px;
        color: #525b78;
        margin-top:2px;
    }
    #portfolio .default_text:hover{
        border-bottom: 1px dashed red;
    }    
    .region_title{
        font-family: "Open Sans",sans-serif!important;
        font-size: 16px;
        color: #262c40;
        line-height: 22px;
        font-weight:600;
    } 
    
    .region_list{
        padding-right:24px;
        margin-bottom: 24px;
        width:25%;
    }
    .city_button{
        position:relative;
    }
    .trade_mark_box{
        display:none;
        position:absolute;
        background:white;
        padding:24px;
        box-shadow: 0 0px 20px 0px rgb(38 44 64 / 28%);
        border: 1px solid #e4e6eb;
        border-radius: 8px;
        z-index:10;
        padding-top:32px;
        max-height: 450px;
        overflow: auto;
    }
    .trade_mark_link{
        margin-bottom:8px;
    }
    /* audit07 C2 */
    .lx-pf-cert{
        margin:-4px 0 10px 0;
        font-size:12px;
        line-height:16px;
        color:#6b7490;
    }
    .lx-pf-cert a{
        color:inherit;
        text-decoration:underline;
        text-decoration-style:dotted;
        text-underline-offset:2px;
    }
    .lx-pf-cert a:hover{ color:#d9363e; }
    /* audit07 C4: case links */
    .lx-pf-case{ margin:-6px 0 12px 0; font-size:13px; line-height:16px; }
    .lx-pf-case a{ font-weight:600; text-decoration:none; }
    .lx-pf-cases{ max-width:1120px; margin:24px auto 0; padding:18px 20px; border-radius:14px; background:rgba(30,41,59,0.8); border:1px solid rgba(148,163,184,0.22); }
    .lx-pf-cases-title{ font-size:18px; font-weight:700; color:#f1f5f9; margin:0 0 12px !important; }
    .lx-pf-cases ul{ list-style:none; margin:0; padding:0; display:flex; flex-wrap:wrap; gap:10px; }
    .lx-pf-cases li{ display:flex; flex-direction:column; padding:10px 14px; border-radius:10px; background:rgba(15,23,42,0.6); border:1px solid rgba(148,163,184,0.18); font-size:15px; line-height:1.3; }
    .lx-pf-cases li a{ font-weight:600; text-decoration:none; }
    .lx-pf-cases li span{ font-size:12px; color:#94a3b8; margin-top:2px; }
    @media (max-width:768px){ .lx-pf-cases{ margin:16px 0 0; padding:14px; } .lx-pf-cases li{ flex:1 1 calc(50% - 10px); } }
    /* audit07 C2: dark site theme made names in the white city popup low-contrast */
    body.lireate-portfolio-body .trade_mark_box{
        background:#1e293b;
        border-color:rgba(148,163,184,0.25);
        box-shadow:0 16px 36px rgba(0,0,0,0.45);
    }
    body.lireate-portfolio-body .trade_mark_box .trade_mark_link{ color:#e2e8f0; }
    body.lireate-portfolio-body .lx-pf-cert{ color:#94a3b8; }
    .trade_mark_box_close{
        position:absolute;
        right:0; top:0;
        width:30px; height:30px;
        cursor:pointer;
        background-image:url(/template/img/icon/close.svg);
        background-size: cover;
    }
    @media(max-width:1450px){
        #portfolio .contentBox{ height: 2180px;}
        .region_list{width:33.3333%;}}
        
    @media (max-width: 1270px){
        #face {
            background-position: 110% center;
            background-size: 55%;}
    }
    
    @media (max-width: 880px){
        #face {
            background-position: 190% center;
            background-size: 75%;}
        #portfolio .contentBox{height: 3320px;}
        .region_list{width:50%;}}
        
    @media (max-width: 780px){
        #portfolio {padding:64px 0;}
    

        #face {
            background-position: 190% 120%;
            background-size: 85%;}
        }
        
    @media (max-width: 615px){
        #face {
            background-position: 190% 110%;
            background-size: 90%;}
        }
     @media (max-width: 515px){
        #face {
            background-position: 190% 110%;
            background-size: 100%;}
        }   
    @media (max-width: 605px){
        .region_list {padding-right: 0px;}
        #portfolio .contentBox{height: auto;}
        .region_list{width:100%;}}
        
    @media (max-width: 450px){
        #face {
            background-position: -90% 90%;
            background-size: 120%;}
        }
</style>
    
<section class="sectionPadding" id="portfolio">
    <div class="contentBox">
    
    <?php 
        
        //Получаем список регионов в виде массива из ключей
        $keys = array_keys($array);
        $i = 0;
        //Общее количество товарных знаков
        $trade_mark_count_itog = 0;  
        
        
        
        
        
        
        
        
        
        
        //Создаём цикл которые перебирает каждый массив, количество которых равно количеству регионов
        foreach($array as $value){ 
            //Выводим регион ?>
            <div class="region_list">
            <?php 
                //Расчёт количества товарных знаков в регионе
                $city_keys = array_keys($array[$keys[$i]]);
                $y = 0;
                
                $trade_mark_count = 0;
                foreach($array[$keys[$i]] as $value){ 
                    $trade_mark_count = $trade_mark_count + count($array[$keys[$i]][$city_keys[$y]]);
                    $y++;
                } 
                $trade_mark_count_itog = $trade_mark_count_itog + $trade_mark_count;
                
            ?>
                <p class="region_title"><?= $keys[$i].' ('.$trade_mark_count.')' ?></p>
                
            <?php 
                $city_keys = array_keys($array[$keys[$i]]);
                $y = 0;
                foreach($array[$keys[$i]] as $value){ 
                    if($city_keys[$y] != 'Другие населенные пункты'){
                        $city_name = $city_keys[$y];
                    }else{$city_name = 'Небольшие населенные пункты';}
            ?>
                        
                <p class="default_text city_button js-city_button" style=""><?= $city_name.' ('.count($array[$keys[$i]][$city_keys[$y]]).')' ?>
                    <div class="trade_mark_box js-city_tm_box">
                        <div class="trade_mark_box_close js-tm_box_close_button"></div>
                
            <?php
                //Перебираем товарные знаки
                $trade_mark_keys = array_keys($array[$keys[$i]][$city_keys[$y]]);
                $q = 0;
                foreach($array[$keys[$i]][$city_keys[$y]] as $value){?>
                    <p 
                        class="default_text trade_mark_link" 
                        data-title="<?= $array[$keys[$i]][$city_keys[$y]][$trade_mark_keys[$q]]['title']?>"
                        data-time="<?= $array[$keys[$i]][$city_keys[$y]][$trade_mark_keys[$q]]['time']?>"
                        data-start="<?= $array[$keys[$i]][$city_keys[$y]][$trade_mark_keys[$q]]['start_date']?>"
                        data-finish="<?= $array[$keys[$i]][$city_keys[$y]][$trade_mark_keys[$q]]['finish_date']?>"
                        data-img="<?= $array[$keys[$i]][$city_keys[$y]][$trade_mark_keys[$q]]['img']?>"
                        data-adress="<?= $array[$keys[$i]][$city_keys[$y]][$trade_mark_keys[$q]]['adress']?>"
                        data-activity="<?= $array[$keys[$i]][$city_keys[$y]][$trade_mark_keys[$q]]['activity']?>"
                        data-fips="<?= lireate_pf_fips_url($array[$keys[$i]][$city_keys[$y]][$trade_mark_keys[$q]]['fips_link'])?>"
                        data-img_exp="<?php if($array[$keys[$i]][$city_keys[$y]][$trade_mark_keys[$q]]['img_exp'] == 'png'){echo 'png';}else{echo 'jpg';}?>"
                        >
                        <?= str_replace('Регистрация товарного знака', '', $trade_mark_keys[$q])?>
                    </p>
                    <?= lireate_pf_cert_line($array[$keys[$i]][$city_keys[$y]][$trade_mark_keys[$q]]) ?>
                    <?= function_exists('lireate_cases_portfolio_link') ? lireate_cases_portfolio_link($array[$keys[$i]][$city_keys[$y]][$trade_mark_keys[$q]]['title']) : '' ?>
                  
                  <?php  $q++; 
                }
                echo'</div></p>';
                
                $y++;
                } ?> <?= '</div>' ?><?php
                    
                    $i++;

            }
            
           
           //echo 'Итого: '.$trade_mark_count_itog.' товарных знаков';
        ?>
        
    </div>
</section>

<script>

    let city_buttons = document.querySelectorAll('.js-city_button');
    let city_trade_marks_boxes = document.querySelectorAll('.js-city_tm_box');
    let tm_box_close_buttons = document.querySelectorAll('.js-tm_box_close_button');
    
    portfolio.addEventListener('blur', function(){
            for(let y = 0; y < city_trade_marks_boxes.length; y++){
                city_trade_marks_boxes[y].style.display = 'none';
            }
        })
        
    for(let i=0; i < city_buttons.length; i++){
        tm_box_close_buttons[i].addEventListener('click', function(){
            city_trade_marks_boxes[i].style.display = 'none';
        })
        
        city_buttons[i].addEventListener('focusout', function(){
            for(let y = 0; y < city_trade_marks_boxes.length; y++){
                city_trade_marks_boxes[y].style.display = 'none';
            }
        })
        
        city_buttons[i].addEventListener('click', function(){
            for(let y = 0; y < city_trade_marks_boxes.length; y++){
                city_trade_marks_boxes[y].style.display = 'none';
            }
            city_trade_marks_boxes[i].style.display = 'block';
        })
    }

</script>

<style>
    #tm_info_box {
        display:none;
        position: fixed;
        z-index: 500;
        height: 100%;
        width: 100%;
        top: 0;
        right: 0;
    }
    
    #tm_info_box_shadow{
        position: fixed;
        z-index: 1;
        background: #262c4066;
        height: 100%;
        width: 100%;
        top: 0;
        right: 0;
        opacity:0;
        transition:0.4s;
    }
    #tm_info{
        position: fixed;
        z-index: 2;
        background: white;
        height: 100%;
        width: calc(100% - 60px);
        max-width:600px;
        top: 0;
        right: -100%;
        border-radius: 30px 0 0 30px;
        box-shadow: 0 0px 20px 0px rgb(38 44 64 / 18%);
        transition:0.4s;
        transition-timing-function:ease;
        padding:64px;
        overflow: auto;
    }    
    #tm_info_title{
        font-size: calc(0.4vw + 16px);
        line-height: calc(0.7vw + 20px);
        font-weight: 500;
        color: #262c40;} 
        
    #tm_info_title span{
        display:block;
        font-size: calc(0.6vw + 22px);
        font-weight: 800;
        color: #262c40;
    }  
    #tm_info_img{
        margin-top: 32px;
        width: 20%;
        min-width: 130px;
        border: 1px solid #cfd2dd;
        border-radius: 8px;
    }
        
    #tm_info_desc{
        width: 400px;
        margin-top: 32px;
    }
    #tm_info_desc .default_text{
        color: #7d86aa;
        margin-bottom: 12px;
        font-size:13px;
        line-height: 20px;
    }
        
    #tm_info_desc .default_text span{
        font-size:16px;
        display:block;
        color: #262b3f;}
        
    #tm_certificate{
        margin-bottom: 32px;
        position:relative;
        width:75px; height:100px;
        margin-top:4px;
        border: 1px solid #cfd2dd;
        border-radius: 8px;
        background-size: cover;
        display: flex;
        align-items: center;
        justify-content: center;
        background-position:center;
        background-image:url(/template/img/page/portfolio/for_page/certificate_icon.jpg);
        cursor:pointer;
    }
    #zoom_icon{
        width: 40%;
        height: 45%;
        cursor: pointer;
        background-position: center;
        background-repeat: no-repeat;
        background-image: url(/template/img/icon/zoom_in.svg);
        background-size: contain;
        opacity: 0.9;
    }
    #tm_fips_link{
        font-size:16px;
        display: table;
        color: #262b3f;
        border-bottom: 1px dashed #9eaad3;
    }
    @media(max-width:530px){
        #tm_info {border-radius: 30px 0 0 0px;}
        #tm_info{padding:32px;}
        #tm_info_desc {
            width: 100%;}}
        
    @media(max-width:430px){
        #tm_info_title {font-size: calc(0.4vw + 12px);}
        #tm_info_img {
            margin-top: 16px;
            min-width: 100px;}
        #tm_info_desc {
            margin-top: 16px;}
        #tm_certificate {
            margin-bottom: 16px;
            width: 50px;
            height: 75px;}  
        #tm_info_desc .default_text {
            margin-bottom: 8px;
            font-size: 12px;
            line-height: 17px;}
        #tm_info_desc .default_text span {font-size: 14px;}
    }
    

    
</style>

<section id="tm_info_box">
    <div id="tm_info_box_shadow"></div>
    <div id="tm_info">
        <div id="tm_info_title">Регистрация товарного знака <span id="tm_name">Cuba Print</span></div>
        <img id="tm_info_img" src="" alt="Товарный знак">
        <div id="tm_info_desc">
            <p class="default_text">Свидетельство:<div id="tm_certificate" data-img=""><div id="zoom_icon"></div></div></p>
            <p class="default_text">Срок регистрации:<span id="tm_time"> 7 месяцев</span></p>
            <p class="default_text">Дата поступления заявки:<span id="tm_start"> 29.11.2018</span></p>
            <p class="default_text">Решение о регистрации:<span id="tm_finish"> 23.05.2019</span></p>
            <p class="default_text">Адрес:<span id="tm_adress"> Омск</span></p>
            <p class="default_text" id="tm_activity_box">Деятельность:<span id="tm_activity"> Производство мебели</span></p>
            <p class="default_text">Ознакомиться со стадией регистрацией Вы можете на официальном сайте ФИПС перейдя по ссылке<a id="tm_fips_link" href="" target="_blank" rel="nofollow noopener">fips.ru</a></p>
        </div>
    </div>
</section>


<script>

    //Функция для закрытия информации и товарном знаке
    function tm_display_none(){
        tm_info_box.style.display = 'none';
    }
    tm_info_box_shadow.addEventListener('click', function(){
        tm_info_box_shadow.style.opacity = '0';
        tm_info.style.right = '-100%';
        setTimeout(tm_display_none, 400);
    })
    
    
    //Функции для открытия блока с информацией о товарном знаке
    let trade_mark_links = document.querySelectorAll('.trade_mark_link');
    function tm_opacity(){
        tm_info_box_shadow.style.opacity = '1';
        tm_info.style.right = '0';
    }
    for(let i = 0; i < trade_mark_links.length; i++){
        console.log('Цикл работает')
        trade_mark_links[i].addEventListener('click', function(){
            
            //Обновление данных
            tm_name.innerHTML = trade_mark_links[i].getAttribute('data-title');
            tm_time.innerHTML = trade_mark_links[i].getAttribute('data-time') + ' месяцев';
            tm_start.innerHTML = trade_mark_links[i].getAttribute('data-start');
            tm_finish.innerHTML = trade_mark_links[i].getAttribute('data-finish');
            tm_adress.innerHTML = trade_mark_links[i].getAttribute('data-adress');
            tm_certificate.setAttribute('data-img', trade_mark_links[i].getAttribute('data-img'));
            tm_certificate.setAttribute('data-img_exp', trade_mark_links[i].getAttribute('data-img_exp'));
            if(trade_mark_links[i].getAttribute('data-activity')){
                console.log('Данные есть');
                tm_activity_box.style.display = "block"
                tm_activity.innerHTML = trade_mark_links[i].getAttribute('data-activity');
            }else{
                console.log('Данных нет');
                tm_activity_box.style.display = "none";
            }
            
            tm_fips_link.setAttribute('href', trade_mark_links[i].getAttribute('data-fips'))
            //Изображение
            tm_info_img.setAttribute('src', '/template/images/portfolio/' + trade_mark_links[i].getAttribute('data-img') + '/logo.jpg')
            
            tm_info_box.style.display = 'block';
            setTimeout(tm_opacity, 100);
        })
    }
    
</script>


<style>

#certificate_box{
    display: none; align-items: center; justify-content: center;
    position:fixed; z-index: 999;
    width:100%; height:100%; 
    top:0; left:0;}

#certificate_box_shadow{
    position: fixed; z-index: 1;
    top: 0; right: 0;
    height: 100%;width: 100%;
    background: #262c4066;
    /*opacity:0;*/
    transition:0.4s;
}
#certificate{
    position:absolute; z-index:2;
    width: calc(100% - 32px);
    max-width: 550px;
    background-color:white;
    border-radius:4px; 
    box-shadow: 0 0px 20px 0px rgb(38 44 64 / 18%);
}
#certificate::before{
    content:'';
    position:absolute;
    width:30px; height:30px;
    background:red;
}
</style>

<section id="certificate_box">
    <div id="certificate_box_shadow"></div>
    <img id="certificate" src="/template/images/portfolio/Santini/1.jpg" alt="Свидетельство на товарный знак">
        <div></div>
    </img>
</section>

<script>
    
certificate_box_shadow.addEventListener('click', function(){
    certificate_box.style.display = 'none';});
    
tm_certificate.addEventListener('click', function(){
    certificate.setAttribute('src', '/template/images/portfolio/' + tm_certificate.getAttribute('data-img') + '/1.' +  tm_certificate.getAttribute('data-img_exp'));
    certificate_box.style.display = 'flex';
});    
</script>





























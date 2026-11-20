<style>
    
    #appearBox{
        padding:20px;
        display:none; align-items: center; justify-content: center;
        position:fixed; z-index:6;
        width:100%; height:100%;
        top:0; left:0;}
        
        /* Фон для всплывающей формы */
        #appearBox_shadow{
            cursor:pointer;
            position:absolute;
            width:100%; height:100%;
            top:0; left:0;
            background-color:#00000073;}
        
        /* Блок с формой */    
        #appearBox-container{
            position:relative;
            width: 100%;
            padding:40px 30px;
            border-radius:8px;
            position:relative;
            max-width:350px; min-height:350px;
            background-color:white;} 
            #appear_close_button{
                position:absolute; top:10px; right:10px;
                width:25px; height:25px;
                border-radius:4px;
                background-color: #ecf3ff;
                background-image:url(inc/img/icon/close.svg);
                background-repeat:no-repeat;
                background-position:center;
                background-size: 40%;
                cursor:pointer;
            }
            #appearBox-container .face-form-box{
                margin-top:0;
                height:auto;}
            #appearBox-container .formStyleClassic{}
            #appearBox-container .input-box{width:100%;}
            #appearBox-container .submitBox{margin-top:20px; }
            #appearBox-container .p2 {
                margin-bottom: 30px;
                display:block;
                margin-top:0;
                text-align: center;
                font-size: 17px;
                font-weight: 500;
                color: #484952;}
            #appearBox-container .input-box input {font-size: 15px;}
            #appearBox-container .input-box label {font-size: 15px;}
            #appearBox-container input[type='submit']{
                width:100%;}
            #appearBox-container .face-form-box {display: block;}
            #appearBox-container .input-box .label_not_active {font-size: 11px;}
            @media (max-width: 850px){
                #appearBox-container .face-form-box {display: block;}
                #appearBox-container .face-form-box {margin-top: 0px!important}}
            @media (max-width: 450px){
                #appearBox-container {padding: 30px 20px;}}
                
            
            .appear_online_box {
                margin-top:30px;
            }
            .appear_online_box p{
                text-align:center;
            }
               
            
</style>

<div id="appearBox">
    <div id='appearBox_shadow'></div>
    <div id='appearBox-container'>
        <div id="appear_close_button"></div>
        <div class="face-form-box">
            <form id='sendMail' class="formStyleClassic js-form" data-consent-form="popup" novalidate>
               <p class="p2" id='formTitle'>Получите подробную бесплатную консультацию и проверку вашего товарного знака</p>
                <!--div class="input-box"> 
                    <label class="label">Имя*</label> 
                    <input type="text" prompt='name' placeholder="Александр" name="name" pattern="^[А-Яа-яЁё\sA-z]{2,}$" required>  
                    <p class="prompt"> </p> 
                </div-->
                <input type="text" name="service" id='services_input' value='Бесплатная консультация' hidden>
                <div class="input-box"> 
                    <input type="tel" id="phone" name="phone" placeholder="+7 (XXX) XXX-XX-XX" class="mask_true" pattern="\+\d \(\d+\)\s\d+-\d+-\d{2}" required>
                    <label class="label">Телефон*</label>
                    <p class="prompt"></p> 
                </div>
                <!--div class="input-box">
                    <input type="email" name="email" placeholder="email@yandex.ru" pattern="([A-z0-9_.-]{1,})@([A-z0-9_.-]{1,}).([A-z]{2,8})">
                    <label class="label">email</label> 
                    <p class="prompt"> </p>  
                </div-->
                 <div class="form-agreement">
  <input type="checkbox" id="popup-consent-request" name="consent_request" value="1" required>
  <label for="popup-consent-request">Я даю <a href="/processing-consent.html" target="_blank" rel="noopener" class="agreement-link">согласие на обработку данных для ответа на обращение</a>.</label>
  <p><a href="/privacy_policy" target="_blank" rel="noopener" class="agreement-link">Политика обработки персональных данных</a></p>
</div>
<div class="form-agreement">
  <input type="checkbox" id="popup-consent-marketing-ads" value="1">
  <label for="popup-consent-marketing-ads">Я согласен(-на) на <a href="/marketing-data-consent.html" target="_blank" rel="noopener" class="agreement-link">обработку контактных данных для рекламы</a> и <a href="/advertising-consent.html" target="_blank" rel="noopener" class="agreement-link">получение рекламы ЛИРЕЙТ</a> по телефону и, если указан, email.</label>
  <input type="hidden" name="consent_marketing_data" id="popup-hidden-marketing" value="" disabled="disabled">
  <input type="hidden" name="consent_ads" id="popup-hidden-ads" value="" disabled="disabled">
</div>
                <div class="submitBox">
                    <input id='sendFormButton' type="submit" name="button" value="Бесплатная консультация">
                </div> 
                 <div class="appear_online_box">
                    <p class="p">Проконсультируем по регистрации товарного знака в мессенджере, без заполнения форм и ожидания операторов</p>
                <div class="write-us">
                    <a class="topBar-icon whatsapp-icon" href="" target="_blank"></a>
                    <a class="topBar-icon teleg-icon" href="" target="_blank"></a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>

    let open_appearForm = document.querySelectorAll('.js-open-appearForm');
    for(i = 0; i < open_appearForm.length; i++){
        sendFormButton.value;
        open_appearForm[i].addEventListener('click', function(e){
            console.log(e.target.getAttribute('data-service'));
            services_input.setAttribute('value', e.target.getAttribute('data-service'))
            formTitle.innerHTML = e.target.getAttribute('data-service');
            sendFormButton.value = e.target.innerHTML
            appearBox.style.display = 'flex'
        })
    }
    
    appearBox_shadow.addEventListener('click', function(){
        appearBox.style.display = 'none'
    })
    appear_close_button.addEventListener('click', function(){
        appearBox.style.display = 'none'
    })

    var popupMarketingAds = document.getElementById('popup-consent-marketing-ads');
    var popupHiddenMarketing = document.getElementById('popup-hidden-marketing');
    var popupHiddenAds = document.getElementById('popup-hidden-ads');
    function syncPopupMarketingCombined() {
        if (!popupMarketingAds || !popupHiddenMarketing || !popupHiddenAds) return;
        if (popupMarketingAds.checked) {
            popupHiddenMarketing.disabled = false;
            popupHiddenAds.disabled = false;
            popupHiddenMarketing.value = '1';
            popupHiddenAds.value = '1';
        } else {
            popupHiddenMarketing.disabled = true;
            popupHiddenAds.disabled = true;
            popupHiddenMarketing.value = '';
            popupHiddenAds.value = '';
        }
    }
    if (popupMarketingAds) {
        popupMarketingAds.addEventListener('change', syncPopupMarketingCombined);
    }
</script>


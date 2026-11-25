$(function(){

    // Separate, unchecked consents for every legacy lead form.
    var consentForms = $('.ajax_form, .ajax_form_main, .simple_form, .ajax_form_find, form[id="double-form"]');
    $('input[type="checkbox"][checked]').each(function() {
        var old = $(this).closest('label, p');
        if (/соглас|private_data|персональн/i.test(old.text())) old.remove();
    });
    consentForms.each(function(index) {
        var form = $(this);
        form.find('input[type="checkbox"][checked]').each(function() {
            var old = $(this).closest('label, p');
            if (/соглас|private_data|персональн/i.test(old.text())) old.remove();
        });
        form.find('span').filter(function() {
            return /Нажимая на кнопку, вы даете/.test($(this).text());
        }).remove();
        var target = form.find('button[type="submit"], button[id="submit"]').first();
        if (!target.length) return;
        var container = $('<div class="lireate-consents" style="margin:12px 0;font-size:14px;line-height:1.5;text-align:left;color:#e8ecf5"></div>');
        container.append('<input type="hidden" name="consent_form" value="legacy-' + index + '">');
        container.append('<label style="display:block;margin:8px 0;font-weight:normal"><input type="checkbox" name="consent_request" value="1" required style="margin-right:6px">Я даю <a href="/processing-consent.html" target="_blank" rel="noopener">согласие на обработку персональных данных для ответа на обращение</a>. <a href="/politika_konfidencialnosti" target="_blank" rel="noopener">Политика конфиденциальности</a>.</label>');
        container.append('<label style="display:block;margin:8px 0;font-weight:normal"><input type="checkbox" class="lireate-consent-marketing-combined" value="1" style="margin-right:6px">Я согласен(-на) на <a href="/marketing-data-consent.html" target="_blank" rel="noopener">обработку контактных данных для рекламы</a> и <a href="/advertising-consent.html" target="_blank" rel="noopener">получение рекламы ЛИРЕЙТ</a> по телефону и, если указан, email.</label>');
        container.append('<div class="lireate-consent-error" role="alert" style="display:none;color:#b00020">Подтвердите согласие для ответа на обращение.</div>');
        if (form.attr('id') === 'double-form' && form.closest('.hidden').length) {
            var visibleButton = form.closest('.hidden').siblings().find('[id="_bbut2"]').first();
            if (visibleButton.length) {
                container.insertAfter(visibleButton);
                bindMarketingCombined(container, form);
                container.find('input[type="checkbox"][name]').on('change', function() {
                    var name = this.name;
                    form.find('input[name="' + name + '"]').remove();
                    if (this.checked) form.append($('<input type="hidden">').attr('name', name).val('1'));
                });
                return;
            }
        }
        bindMarketingCombined(container, form);
        container.insertBefore(target);
    });

    function syncMarketingCombined(form, checked) {
        form.find('input[name="consent_marketing_data"], input[name="consent_ads"]').remove();
        if (checked) {
            form.append($('<input type="hidden">').attr('name', 'consent_marketing_data').val('1'));
            form.append($('<input type="hidden">').attr('name', 'consent_ads').val('1'));
        }
    }
    function bindMarketingCombined(container, form) {
        container.find('.lireate-consent-marketing-combined').on('change', function() {
            syncMarketingCombined(form, this.checked);
        });
    }

    function consentReady(form) {
        var box = $(form).find('input[name="consent_request"]');
        if (!box.length || !(box.prop('checked') || (box.attr('type') === 'hidden' && box.val() === '1'))) {
            var error = $(form).find('.lireate-consent-error');
            if (!error.length) error = $(form).closest('.hidden').siblings().find('.lireate-consent-error').first();
            error.show();
            if (box.length) box[0].focus();
            return false;
        }
        return true;
    }
    document.addEventListener('submit', function(event) {
        if ($(event.target).is('.ajax_form, .ajax_form_main, .simple_form, .ajax_form_find, form[id="double-form"]') && !consentReady(event.target)) {
            event.preventDefault();
            event.stopImmediatePropagation();
        }
    }, true);
    $(document).on('click', '.ajax_form_find button[id="submit"]', function(event) {
        if (!consentReady($(this).closest('form')[0])) {
            event.preventDefault();
            event.stopImmediatePropagation();
        }
    });

// Текст при наведении 
	if($('[data-toggle="tooltip"]').length)
		$('[data-toggle="tooltip"]').tooltip();

// Product slider
	//$('.product_slider').liMarquee();
	
	// Открыть меню(закрыть меню)
	$(".mobile_menu_btn").click(function() {
	$('.nav_menu').slideToggle();
	});
// Для изображения в отзывах
	// if($('#main-search').length){
	// 	$('#main-search').popover({
	// 		html:true,
	// 		content: $('.main-search').html()
	// 	});
	// }
	// $(" .slider_page_picture, .img_scale").imageScale();

	if($('.menutoogle').length){
		$('.menutoogle').click(function(){
			if(document.body.clientWidth<=992)
				if ($(this).next('ul').length > 0)
					$(this).next('ul').slideToggle('fast');
				else{
					$(this).next().next('ul').slideToggle('fast');
				}
		});
		$('.menutoogle, .submenu').hover(function(){
			if(document.body.clientWidth>992){
				if($(this).hasClass('submenu'))
					$(this).show();
				$(this).next('ul').show();
			}
		},function(){
			if(document.body.clientWidth>992){
				if($(this).hasClass('submenu'))
					$(this).hide();

				$(this).next('ul').hide();
			}
		});
	}

	if($('.ajax_form').length){
		$('.ajax_form').submit(function(e){
		var form_this = this;
		e.preventDefault();
		var m_data=$(this).serialize();
        console.log (form_this.mail_token);
        
		$('[type="submit"]', form_this).attr('disabled',true);
			$.ajax({
				type: 'POST',
				url: '/mail.php',
				data: m_data,
				dataType: 'json',
				success: function(data)
				{
					$('[type="submit"]', form_this).removeAttr('disabled');
					if(data[0]=='success'){
						/* audit07 A6: goal for CMS lead forms; redirect below (after 2 s) is unchanged */
						if (!($(form_this).hasClass('lireate-contacts-form') || $(form_this).hasClass('lireate-lead-stay')) && typeof window.ym === 'function') {
							try { window.ym(52622734, 'reachGoal', 'zayavka'); } catch (e) {}
						}
						$(form_this).find('.mail_to').fadeOut("fast", function(){
							$(form_this).find('.s_mail_to').fadeIn("fast");
							$(form_this)[0].reset();
							$('.ap-label-js').prev('label').removeClass('active');
						});

						setTimeout(function(){
							var keep = $(form_this).hasClass('lireate-contacts-form') || $(form_this).hasClass('lireate-lead-stay');
							if (!keep) {
								$(form_this).find('.s_mail_to').fadeOut("fast", function(){
									$(form_this).find('.mail_to').fadeIn("fast");
								});
								$('.modal-mail').modal('hide');
								if(form_this.new_lending){
								 window.location="/new_lending?thanks";
								}else{window.location="/spasibo";}
							}
							if (keep && typeof window.ym === 'function') {
								try { window.ym(52622734, 'reachGoal', 'zayavka'); } catch (e) {}
							}
						}, 2000);
					}else{
						$.each(data[1], function(a,b){
							$(form_this).find('.vError[data-frm="'+a+'"]').addClass('error_form');
							$(form_this).find('.vError input').keyup(function(){
								$(this).parent().removeClass('error_form');
							});
							$(form_this).find('.vError textarea').keyup(function(){
								$(this).parent().removeClass('error_form');
							});
							$(form_this).find('.vError>div').mouseenter(function(){
								$(this).parent().removeClass('error_form');
							});
						});
					}
				}
			});
		});
	}

	if($('.ajax_form_main').length){
		$('.ajax_form_main').submit(function(e){
		var form_this = this;
		e.preventDefault();
		var m_data=$(this).serialize();

		$('[type="submit"]', form_this).attr('disabled',true);
			$.ajax({
				type: 'POST',
				url: '/mail-gen.php',
				data: m_data,
				dataType: 'json',
				success: function(data)
				{
					$('[type="submit"]', form_this).removeAttr('disabled');
					if(data[0]=='success'){
						$(form_this).find('.mail_to').fadeOut("fast", function(){
							$(form_this).find('.s_mail_to').fadeIn("fast");
							$(form_this)[0].reset();
							$('.ap-label-js').prev('label').removeClass('active');
						});

						setTimeout(function(){
						$(form_this).find('.s_mail_to').fadeOut("fast", function(){
							$(form_this).find('.mail_to').fadeIn("fast");
						});
						//$('.modal-mail').modal('hide');
						window.location="/spasibo";
						}, 2000);
					}else{
						$.each(data[1], function(a,b){
							$(form_this).find('.vError[data-frm="'+a+'"]').addClass('error_form');
							$(form_this).find('.vError input').keyup(function(){
								$(this).parent().removeClass('error_form');
							});
						});
					}
				}
			});
		});
	}

	if($('.simple_form').length){
		$('.simple_form').submit(function(e){
		var form_this = this;
		e.preventDefault();
		var m_data=$(this).serialize();

		$('[type="submit"]', form_this).attr('disabled',true);
			$.ajax({
				type: 'POST',
				url: '/mail-simple.php',
				data: m_data,
				dataType: 'json',
				success: function(data)
				{
					$('[type="submit"]', form_this).removeAttr('disabled');
					if(data[0]=='success'){
						$(form_this).find('.s_mail_to').fadeIn("fast");
						$(form_this)[0].reset();
						$('.ap-label-js').prev('label').removeClass('active');

						setTimeout(function(){
						$(form_this).find('.mail_to').fadeIn("fast");
						window.location="/spasibo";
						}, 2000);
					}else{
						$.each(data[1], function(a,b){
							$(form_this).find('.vError[data-frm="'+a+'"]').addClass('error_form');
							$(form_this).find('.vError input').keyup(function(){
								$(this).parent().removeClass('error_form');
							});
						});
					}
				}
			});
		});
	}

	if($('#double-form').length){
		$('#double-form').submit(function(e){
		var form_this = this;
		e.preventDefault();
		var m_data=$(this).serialize();

		$('[type="submit"]', form_this).attr('disabled',true);
			$.ajax({
				type: 'POST',
				url: '/mail-double.php',
				data: m_data,
				dataType: 'json',
				success: function(data)
				{
					$('[type="submit"]', form_this).removeAttr('disabled');
					if(data[0]=='success'){
						setTimeout(function(){window.location="/spasibo";}, 1000);
					}
				}
			});
		});
	}

	
	function _(el){
		return document.getElementById(el);
	}
	
	function progressHandler(event){
		//_("loaded_n_total").innerHTML = "Uploaded "+event.loaded+" bytes of "+event.total;
		var percent = (event.loaded / event.total) * 100;
		_("progressBar").innerHTML = Math.round(percent)+'%';
		$('#progressBar').css('width',Math.round(percent)+"%");
		_("status").innerHTML = Math.round(percent)+"% загружено...";
	}
	function completeHandler(event){
		//_("status").innerHTML = event.target.responseText;
		_("progressBar").innerHTML = '0%';
		$('#progressBar').css('width','10%')
		
		$('#submit').attr('disabled',false);
		//ajax.responseText
		
	}
	function errorHandler(event){
		_("status").innerHTML = "Ошибка загрузки!";
		$('#submit').attr('disabled',false);
	}
	function abortHandler(event){
		_("status").innerHTML = "Загрузка отменена";
		$('#progressBar').css('width','10%');
		$("#prog").addClass('hidden');
		$('#submit').attr('disabled',false);
	}
	
	function uploadFile(form){
		var files = _("file").files;
		if(files.length == 0){
           $('#file').attr('disabled', true);
        }
		// alert(file.name+" | "+file.size+" | "+file.type);
		var formdata = new FormData(form[0]);
		//var formdata = new FormData();
		//var formdata = form.serialize();
		//alert(formdata);
		//formdata.append("userfile", files);
		//formdata.append("userfile", files);
		var ajax = new XMLHttpRequest();
		ajax.upload.addEventListener("progress", progressHandler, false);
		ajax.addEventListener("load", completeHandler, false);
		ajax.addEventListener("error", errorHandler, false);
		ajax.addEventListener("abort", abortHandler, false);
		ajax.open("POST", "/mail2.php?_=" + new Date().getTime(), true);
		$("#ermsg").addClass('hidden')
		$(".has-error").removeClass('has-error');
		ajax.onreadystatechange = function(){
			if(ajax.readyState == 4){
				if(ajax.status == 200){
					var data = ajax.responseText;
					if (data == 'true') {
						_("status").innerHTML = 'Заявка отправлена';
						$("#prog").addClass('hidden');
						window.location="/spasibo";
					}else{
						var jdata = JSON.parse(data);
						var keys = [];
						for(var key in jdata) {
						  if(jdata.hasOwnProperty(key)) { //to be safe
						    keys.push(key);
						  }
						}
						var i = 0;
						while(i<keys.length){
							$('[name='+keys[i]+']',form[0]).parent().addClass('has-error');
							i++;
						}
						//alert(data);
						_("ermsg").innerHTML = 'Пожалуйста заполните все поля!';
						$("#ermsg").removeClass('hidden');
						$("#prog").addClass('hidden');
					}
				}else{
					$('#submit').attr('disabled', false);
					_('status').innerHTML = 'Не удалось отправить заявку. Проверьте поля и повторите попытку.';
				}
			}
		}
		ajax.send(formdata);
		$("#prog").removeClass('hidden');
	}
	


	if($('.ajax_form_find').length){
		$('.ajax_form_find button[id="submit"]').click(function(e){
			var form_this = $(this).closest('form');
			e.preventDefault();
			if (!consentReady(form_this[0])) return;
			//var file_data = $('#file').prop('files')[0];
			//var m_data=$(this).serialize();
			//var m_data = new FormData(form_this);
			//var m_data = new FormData();                  
	    	//m_data.append('file', file_data);
	    	//alert(m_data);
			//console.log(m_data);
			/*$.ajaxSetup ({
			    // Disable caching of AJAX responses
			    cache: false
			});*/
			
			$('#submit', form_this).attr('disabled',true);
			uploadFile(form_this);
			/*$.ajax({
				url: '/mail2.php',
				dataType: 'json',
				cache: false,
				contentType: false,
				processData: false,
				data: m_data,
				type: 'post',
				success: function(data)
				{
					$('[type="submit"]', form_this).removeAttr('disabled');
					//alert(data[0]);
					if(data[0]=='success'){
						$(form_this).find('.mail_to').fadeOut("fast", function(){
							$(form_this).find('.s_mail_to').fadeIn("fast");
							$(form_this)[0].reset();
							$('.ap-label-js').prev('label').removeClass('active');
						});

						setTimeout(function(){
						$(form_this).find('.s_mail_to').fadeOut("fast", function(){
							$(form_this).find('.mail_to').fadeIn("fast");
						});
						//$('.modal-mail').modal('hide');
						window.location="/spasibo";
						}, 2000);
					}else{
						alert(data[1]);
						$.each(data[1], function(a,b){
							$(form_this).find('.vError[data-frm="'+a+'"]').addClass('error_form');
							$(form_this).find('.vError input').keyup(function(){
								$(this).parent().removeClass('error_form');
							});
						});
					}
				}
			});*/
			//return true;
		});
	}

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

})
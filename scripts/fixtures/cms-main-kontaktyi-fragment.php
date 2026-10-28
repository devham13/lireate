<?php
/**
 * Fixture: fragment of template/main.php around kontaktyi (structure + PHP balance).
 */
?>
<!DOCTYPE html>
<html lang="ru">
<head><meta charset="utf-8"><title>CMS shell test</title></head>
<body class="cms-default">
<div class="wrapper">
<div class="container text-center content">
<?php if($temp['page']=='kontaktyi'){ ?>
					<div class="">
				<div class="row">
					<div class="col-xs-12">
						<div class="title-ap"><h1>Контакты Лирейт</h1></div>
						<script src='https://www.google.com/recaptcha/api.js?hl=ru'></script>
						<div class="row" style="margin-bottom:20px;">
							<div class="col-xs-12 col-md-5" style="margin-bottom: 10px;">
								<div class="order_form_content">
									<form class="form-unit pb30 ajax_form" method="POST" action="mail.php">
										<input type="hidden" name="mail" value="mailorder">
									</form>
								</div>
							</div>
							<div class="col-xs-12 col-md-7" style="text-indent: 0px;">
								<b>Адрес:</b> РТ, г. Зеленодольск<br />
							</div>
						</div>
					</div>
				</div>
				<!-- Содержание
		====================-->
			<div class="page_container">
			</div><!-- /page_container -->
		</div>
<?php } ?>
<?php if($temp['page']=='news'){ ?>
<section id="blog"><p>News block ok</p></section>
<?php } ?>
</div>
</div>
</body>
</html>

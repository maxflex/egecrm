<?php if (User::fromSession()->show_phone_calls) :?>
	<div class="phone-app">
		<? include 'js/bower/phoneapi/dist/template/_phone_api.php'; ?> 
		<phone user_id="<?= User::fromSession()->id ?>" type="egecrm" key="<?= Socket::EGECRM_APP_KEY ?>"></phone>
	</div>
<?php endif ?>


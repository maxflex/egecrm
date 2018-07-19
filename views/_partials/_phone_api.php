<?php if (User::fromSession()->allowed(Shared\Rights::PHONE_NOTIFICATIONS)) :?>
	<div class="phone-app">
		<? include 'js/bower/phoneapi/dist/template/_phone_api.php'; ?>
		<phone user_id="<?= User::id() ?>" type="egecrm" key="<?= Socket::EGECRM_APP_KEY ?>"></phone>
	</div>
<?php endif ?>

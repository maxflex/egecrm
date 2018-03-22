<center ng-app="Login" ng-controller="GetPwdCtrl" ng-init="mode = <?= $mode ?>">
	<div class="form-signin" autocomplete="off">
<!-- 		<h2 class="form-signin-heading">Вход в систему</h2> -->
		<div ng-show="!success && !error">
			<input style='border-radius: 4px' type="text" class="form-control"
			placeholder="Введите ваш email" autofocus name="login" ng-model="email" autocomplete="off" ng-keyup="enter($event)">
			<button id="login-submit" data-style="zoom-in" class="btn btn-lg btn-primary btn-block ladda-button"
				ng-disabled="in_process" type="submit" ng-click="go()">
				<span>далее</span>
			</button>
		</div>
		<div ng-show="success">
			ссылка на <?= $mode == 1 ? 'установку' : 'восстановление' ?> пароля отправлена на <br /><b>{{ email }}</b>
		</div>
		<div ng-show="error">
			<span class="text-danger">Невозможно получить пароль<br />Cвяжитесь с администрацией по телефону<br /><b>+7 495 646-85-92</b></span>
		</div>
	</div>
</center>
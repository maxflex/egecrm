<center ng-app="Login" ng-controller="ResetPwdCtrl" ng-init="code = '<?= $_GET['code'] ?>'">
	<div class="form-signin" autocomplete="off">
<!-- 		<h2 class="form-signin-heading">Вход в систему</h2> -->
		<div ng-show="!success">
			<input  type="password" class="form-control" placeholder="Введите новый пароль" autofocus
				name="login" ng-model="pwd_1" autocomplete="off" ng-keyup="enter($event)">

			<input type="password" class="form-control"
				placeholder="Повторите пароль" autofocus name="login" ng-model="pwd_2" autocomplete="off" ng-keyup="enter($event)">

			<button id="login-submit" data-style="zoom-in" class="btn btn-lg btn-primary btn-block ladda-button"
				ng-disabled="in_process" type="submit" ng-click="go()">
				<span>далее</span>
			</button>
		</div>
		<div ng-show="success">
			<span class="text-success">Пароль успешно изменен!</span>
			<div style='margin-top: 30px; text-align: center'>
				<a href="/login" class="btn btn-lg btn-primary btn-block ladda-button">Войти</a>
			</div>
		</div>
	</div>
</center>

<center ng-app="Login" ng-controller="LoginCtrl">
    <div class="g-recaptcha" data-sitekey="<?= RECAPTCHA_SITE ?>" data-size="invisible" data-callback="captchaChecked"></div>
	<div class="form-signin" autocomplete="off">
<!-- 		<h2 class="form-signin-heading">Вход в систему</h2> -->
		<input ng-disabled="sms_verification" type="text" id="inputLogin" class="form-control" placeholder="Логин" autofocus name="login" ng-model="login" autocomplete="off" ng-keyup="enter($event)">
		<input ng-disabled="sms_verification" type="password" id="inputPassword" class="form-control" placeholder="Пароль" name="password" ng-model="password" autocomplete="off" ng-keyup="enter($event)">
        <input type="text" id="sms-code" class="form-control" placeholder="код из смс" ng-model="$parent.code" autocomplete="off" ng-keyup="enter($event)" ng-if="sms_verification">


		<button id="login-submit" data-style="zoom-in" ng-disabled="in_process" class="btn btn-lg btn-primary btn-block ladda-button" type="submit" ng-click="checkFields()">
			<span class="glyphicon glyphicon-lock"></span><span ng-show="!in_process">Войти</span><span ng-show="in_process">Вход</span>
		</button>

<!--
		<div style="overflow: hidden">
			<div class="error-message alert alert-dismissible alert-danger" ng-show="form_errors !== undefined" style="margin-top: 20px">
			  <button type="button" class="close" data-dismiss="alert">×</button>
			  {{form_errors}}
			</div>
		</div>
-->

	</div>
</center>
<script src='https://www.google.com/recaptcha/api.js?hl=ru'></script>
<style>
    .grecaptcha-badge {
        visibility: hidden;
    }
</style>
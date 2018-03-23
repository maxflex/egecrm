<center ng-app="Login" ng-controller="LoginCtrl">
    <div class="g-recaptcha" data-sitekey="<?= RECAPTCHA_SITE ?>" data-size="invisible" data-callback="captchaChecked"></div>
	<div class="form-signin" autocomplete="off">
<!-- 		<h2 class="form-signin-heading">Вход в систему</h2> -->

    <div ng-show='!error'>
      <div style='top: -25px; position: relative; text-align: left'>
        <b>Уважаемые учителя и ученики!</b>
        <p style='margin-top: 5px'>
          С 22 марта 2018 года для входа в систему необходимо в качестве логина использовать Ваш email,
          указанный при регистрации или заключении договора.
        </p>
      </div>
      <input ng-disabled="sms_verification" type="text" id="inputLogin" class="form-control" placeholder="Email" autofocus name="login" ng-model="login" autocomplete="off" ng-keyup="enter($event)">
      <input ng-disabled="sms_verification" type="password" id="inputPassword" class="form-control" placeholder="Пароль" name="password" ng-model="password" autocomplete="off" ng-keyup="enter($event)">
      <input type="text" id="sms-code" class="form-control" placeholder="код из смс" ng-model="$parent.code" autocomplete="off" ng-keyup="enter($event)" ng-if="sms_verification">


      <button id="login-submit" data-style="zoom-in" ng-disabled="in_process" class="btn btn-lg btn-primary btn-block ladda-button" type="submit" ng-click="checkFields()">
        <span ng-show="!in_process">Войти</span><span ng-show="in_process">Вход</span>
      </button>
    </div>

    <div ng-show="error">
			<span class="text-danger">
				{{ error }}
				<br />
				<b>+7 495 646-85-92</b>
			</span>
		</div>


		<!-- <div ng-hide='true' style="overflow: hidden">
			<div class="error-message alert alert-dismissible alert-danger" ng-show="form_errors !== undefined" style="margin-top: 20px">
			  <button type="button" class="close" data-dismiss="alert">×</button>
			  {{form_errors}}
			</div>
		</div> -->

  <div class="password-controls">
    <a href='/login/forgot-password' class="forgot-password">забыли пароль?</a>
  </div>
	</div>
</center>
<script src='https://www.google.com/recaptcha/api.js?hl=ru'></script>
<style>
    .grecaptcha-badge {
        visibility: hidden;
    }
</style>

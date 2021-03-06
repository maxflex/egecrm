<div ng-show="image_loaded">
	<center autocomplete="off" id='center' class="animated fadeIn login-form" style='animation-duration: 1.5s'>
		<div class="g-recaptcha" data-sitekey="<?= RECAPTCHA_SITE ?>" data-size="invisible" data-callback="captchaChecked"></div>
		<div class="login-logo group">
			<img ng-if="!logged_user || !logged_user.photo" src="../img/svg/logo.svg" />
			<div ng-if="logged_user && logged_user.photo" class="login-user-ava" style="background-image: url({{ logged_user.photo }})"></div>
		</div>
		<div class="input-groups">
	        <div class="group" ng-show="logged_user">
	            <input disabled type="text" ng-model="logged_user.name">
	        </div>
	        <div class="group" ng-show="!logged_user">
	            <input ng-disabled="sms_verification" type="text" id="inputLogin" autofocus ng-model="login" placeholder="email"
				 	autocomplete="off" ng-keyup="enter($event)" ng-model-options="{ allowInvalid: true }" required>
	        </div>
	        <div class="group">
	            <input ng-disabled="sms_verification" type="password" id="inputPassword" ng-model="password" placeholder="пароль"
					autocomplete="new-password" ng-keyup="enter($event)" ng-model-options="{ allowInvalid: true }" required>
	        </div>
	        <div class="group" ng-show="sms_verification">
	            <input type="text" id="sms-code" placeholder="sms code" ng-model="code" autocomplete="off" ng-keyup="enter($event)">
	        </div>
			<div class="group">
				<button class="btn btn-submit ladda-button" type="submit" id="login-submit" data-style="zoom-in" ng-disabled="in_process" ng-click="checkFields()">войти</button>
			</div>
		</div>
		<div class="password-controls">
			<div>
				<a ng-click="clearLogged()" class="pointer forgot-password" ng-show="logged_user">другой пользователь</a>
			</div>
			<div>
				<a href='/login/forgot-password' class="forgot-password">забыли пароль</a>
			</div>
			<div>
				<a href='/login/forgot-password' class="get-password">у меня нет пароля</a>
			</div>
		  </div>
		<div ng-show="error" class="login-errors">
			в доступе отказано
  		</div>
    </center>

  <?php if(@$wallpaper->user_id) :?>
  <span class="wallpaper-by animated fadeInRight">
	  <?php if($wallpaper->title) :?>
		  <?= $wallpaper->title ?> –
	  <?php endif ?>
	  by <?= $wallpaper->user->login ?>
  </span>
<?php endif ?>
</div>
<div ng-show="!image_loaded">
  <img src="img/svg/tail-spin.svg" />
</div>

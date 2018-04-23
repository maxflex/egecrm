<div ng-show="image_loaded">
	<center autocomplete="off" id='center' class="animated fadeIn" style='animation-duration: 1.5s'>
		<div class="g-recaptcha" data-sitekey="<?= RECAPTCHA_SITE ?>" data-size="invisible" data-callback="captchaChecked"></div>
		<div ng-show="!error">
            <div class="group">
                <input ng-disabled="sms_verification" type="text" id="inputLogin" autofocus ng-model="login"
				 	autocomplete="off" ng-keyup="enter($event)" ng-model-options="{ allowInvalid: true }" required>
				<span class="bar"></span>
				<label>логин</label>
            </div>
            <div class="group">
                <input ng-disabled="sms_verification" type="password" id="inputPassword" ng-model="password"
					autocomplete="new-password" ng-keyup="enter($event)" ng-model-options="{ allowInvalid: true }" required>
				<span class="bar"></span>
				<label>пароль</label>
            </div>
            <div class="group" ng-show="sms_verification" style='position: absolute'>
                <input type="text" id="sms-code" placeholder="sms code" ng-model="code" autocomplete="off" ng-keyup="enter($event)">
				<span class="bar"></span>
				<label>sms code</label>
            </div>
			<div class="btn-box">
				<button class="btn btn-submit ladda-button" type="submit" id="login-submit" data-style="zoom-in" ng-disabled="in_process" ng-click="checkFields()">войти</button>
			</div>
			<div class="password-controls">
				<div>
					<a href='/login/forgot-password' class="forgot-password">забыли пароль?</a>
				</div>
				<div>
					<a href='/login/forgot-password' class="get-password">я тут впервые</a>
				</div>
			  </div>
		  </div>
		<div ng-show="error">
  			<span class="text-danger">
  				{{ error }}
  				<br />
  				<b>+7 495 646-85-92</b>
  			</span>
  		</div>
    </center>

  <?php if(@$wallpaper->user_id) :?>
  <span class="wallpaper-by animated fadeInRight">by <?= $wallpaper->user->login ?></span>
<?php endif ?>
</div>
<div ng-show="!image_loaded">
  <img src="img/svg/tail-spin.svg" />
</div>

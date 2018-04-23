<div ng-show="image_loaded">
	<center ng-app="Login" ng-controller="GetPwdCtrl" id='center' class="animated fadeIn">
		<div class="form-signin" autocomplete="off">
	<!-- 		<h2 class="form-signin-heading">Вход в систему</h2> -->
			<div ng-show="!success && !error">
				<div class="group">
					<input type="text" autofocus name="login" ng-model="email"
						autocomplete="off" ng-keyup="enter($event)" ng-model-options="{ allowInvalid: true }" required>
					<span class="bar"></span>
					<label>введите ваш email</label>
				</div>
				<button id="login-submit" data-style="zoom-in" class="btn btn-submit ladda-button"
					ng-disabled="in_process" type="submit" ng-click="go()">
					<span>далее</span>
				</button>
			</div>
			<div ng-show="success">
				ссылка на установку пароля отправлена на <br /><b>{{ email }}</b>
			</div>
			<div ng-show="error">
				<span class="text-danger">
					{{ error }}
					<br />
					<b>+7 495 646-85-92</b>
				</span>
			</div>
			<div class="password-controls">
		    <a href='/login' class="forgot-password">назад</a>
		  </div>
		</div>
	</center>
	<?php if(@$wallpaper->user_id) :?>
    <span class="wallpaper-by animated fadeInRight">by <?= $wallpaper->user->login ?></span>
  <?php endif ?>
</div>
<div ng-show="!image_loaded">
  <img src="img/svg/tail-spin.svg" />
</div>

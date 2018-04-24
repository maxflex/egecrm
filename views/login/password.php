<div ng-show="image_loaded">
	<center ng-app="Login" ng-controller="GetPwdCtrl" id='center' class="animated fadeIn">
		<div class="login-logo group">
			<img src="../img/svg/logo.svg" />
		</div>
		<div ng-show="!success">
			<div class="input-groups">
				<div class="group">
					<input type="text" autofocus name="login" ng-model="email" placeholder="ваш email"
						autocomplete="off" ng-keyup="enter($event)">
				</div>
				<button id="login-submit" data-style="zoom-in" class="btn btn-submit ladda-button"
					ng-disabled="in_process" type="submit" ng-click="go()">
					<span>далее</span>
				</button>
			</div>
		</div>
		<div ng-show="success">
			ссылка на установку пароля отправлена на <br /><b>{{ email }}</b>
		</div>
		<div class="password-controls">
		    <a href='/login' class="forgot-password">назад</a>
		  </div>
	  <div ng-show="error" class="login-errors">
		  {{ error }}
		  <br />
		  <b>+7 495 646-85-92</b>
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

<div ng-show="image_loaded">
	<center ng-app="Login" ng-controller="GetPwdCtrl" id='center' class="animated fadeIn login-form">
		<div class="login-logo group">
			<img src="../img/svg/logo.svg" />
		</div>
		<div ng-show="!success">
			<div class="input-groups">
				<div>
					<div class="group">
						<input type="text" autofocus name="login" ng-model="email" placeholder="ваш email"
							ng-disabled="step > 1" autocomplete="off" ng-keyup="enter($event)">
					</div>
					<div class="group">
						<input type="text" ng-model="code" placeholder="код подтверждения" ng-show="step >= 2"
							ng-disabled="step > 2" autocomplete="off" ng-keyup="enter($event)">
					</div>
				</div>
				<div ng-show="step >= 3">
					<div class="group">
						<input type="password" autofocus name="login" ng-model="pwd_1" placeholder="введите новый пароль"
							autocomplete="off" ng-keyup="enter($event)">
		            </div>

					<div class="group">
						<input type="password" autofocus name="login" ng-model="pwd_2" autocomplete="off"
							ng-keyup="enter($event)" placeholder="повторите пароль">
		            </div>
				</div>
				<button id="login-submit" data-style="zoom-in" class="btn btn-submit ladda-button"
					ng-disabled="in_process" type="submit" ng-click="go()">
					<span>далее</span>
				</button>
			</div>
		</div>
		<div ng-show="success">
			<b>пароль успешно установлен</b>
		</div>
		<div class="password-controls">
		    <a href='/login' class="forgot-password">назад</a>
		  </div>
	  <div ng-show="message.body" class="login-errors" ng-class="{'success': message.type == 'success'}">
		  {{ message.body }}
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

<div ng-show="image_loaded">
	<center id='center' class="animated fadeIn" ng-app="Login" ng-controller="ResetPwdCtrl" ng-init="code = '<?= $_GET['code'] ?>'">
		<div class="login-logo group">
			<img src="../img/svg/logo.svg" />
		</div>
	<!-- 		<h2 class="form-signin-heading">Вход в систему</h2> -->
			<div ng-show="!success">
				<div class="input-groups">
					<div class="group">
						<input type="password" autofocus name="login" ng-model="pwd_1" placeholder="введите новый пароль"
							autocomplete="off" ng-keyup="enter($event)">
		            </div>

					<div class="group">
						<input type="password" autofocus name="login" ng-model="pwd_2" autocomplete="off"
							ng-keyup="enter($event)" placeholder="повторите пароль">
		            </div>

					<button id="login-submit" data-style="zoom-in" class="btn btn-submit ladda-button"
						ng-disabled="in_process" type="submit" ng-click="go()">
						<span>далее</span>
					</button>
				</div>
			</div>
			<div ng-show="success">
				<span class="text-success">Пароль успешно изменен!</span>
				<div style='margin-top: 30px; text-align: center'>
					<a href="/login" class="btn btn-submit ladda-button">Войти</a>
				</div>
			</div>
			<div ng-show="error" class="login-errors">
	  		  {{ error }}
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

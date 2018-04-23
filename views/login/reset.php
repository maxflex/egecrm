<div ng-show="image_loaded">
	<center ng-app="Login" ng-controller="ResetPwdCtrl" ng-init="code = '<?= $_GET['code'] ?>'">
		<div id='center' class="animated fadeIn" autocomplete="off">
	<!-- 		<h2 class="form-signin-heading">Вход в систему</h2> -->
			<div ng-show="!success">
				<div class="group">
					<input type="password" autofocus name="login" ng-model="pwd_1"
						autocomplete="off" ng-keyup="enter($event)" ng-model-options="{ allowInvalid: true }" required>
					<span class="bar"></span>
					<label>введите новый пароль</label>
	            </div>

				<div class="group">
					<input type="password" autofocus name="login" ng-model="pwd_2" autocomplete="off" ng-keyup="enter($event)" ng-model-options="{ allowInvalid: true }" required>
					<span class="bar"></span>
					<label>повторите пароль</label>
	            </div>

				<button id="login-submit" data-style="zoom-in" class="btn btn-submit ladda-button"
					ng-disabled="in_process" type="submit" ng-click="go()">
					<span>далее</span>
				</button>
			</div>
			<div ng-show="success">
				<span class="text-success">Пароль успешно изменен!</span>
				<div style='margin-top: 30px; text-align: center'>
					<a href="/login" class="btn btn-submit ladda-button">Войти</a>
				</div>
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

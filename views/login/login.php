<center ng-app="Login" ng-controller="LoginCtrl">
	<form class="form-signin" ng-submit="checkFields()">
<!-- 		<h2 class="form-signin-heading">Вход в систему</h2> -->
		<input type="text" id="inputLogin" class="form-control" placeholder="Логин" autofocus name="login" ng-model="login">
		<input type="password" id="inputPassword" class="form-control" placeholder="Пароль" name="password" ng-model="password" style="margin-bottom: 0">
		<div class="checkbox pull-left">
          <label>
            <input type="checkbox" value="remember-me" checked> Запомнить
          </label>
        </div>
		<button class="btn btn-lg btn-primary btn-block" type="submit"><span class="glyphicon glyphicon-lock"></span>Войти</button>
		
		<div style="overflow: hidden">
			<div class="error-message alert alert-dismissible alert-danger animate-show-fadeIn" ng-show="form_errors" style="margin-top: 20px">
			  <button type="button" class="close" data-dismiss="alert">×</button>
			  {{form_errors}}
			</div>
		</div>
		
	</form>
</center>

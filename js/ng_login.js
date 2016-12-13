	angular.module("Login", ["ngAnimate"])
		/*
		.controller("RegisterCtrl", function($scope) {
			// Отправка формы
			$scope.checkFields = function() {
				if (!$scope.login) {
					angular.element('#inputLogin').focus()
					$scope.form_errors = "Укажите логин"
					return false
				}
				
				if (!$scope.password || !$scope.password_repeat) {
					angular.element('#inputPassword').focus()
					$scope.form_errors = "Введите пароли"
					return false
				}
				
				if ($scope.password != $scope.password_repeat) {
					angular.element('#inputPassword').focus()
					$scope.form_errors = "Пароли не совпадают"
					return false
				}
				
				$scope.form_errors = ""
				
				$.post("index.php?controller=start&action=AjaxAddUser", {
					'login'		: $scope.login,
					'password'	: $scope.password
				}, function(response) {
					window.location = "profile"
				})
			}
		})*/
		.directive('enter', function() {
			 return {
				 restrict: 'A',
				 link: function(scope, element, attrs) {
					 return element.bind("keydown keypress", function(event) {
						 if (event.which === 13) {
							scope.$apply(function() {
								return scope.$eval(attrs.enter);
						 	});
						 }

					 	 return event.preventDefault();
					 });
				 }
			 };
		 })

		.controller("LoginCtrl", function($scope) {
			angular.element(document).ready(function() {
				set_scope("Login")
				l = Ladda.create(document.querySelector('#login-submit'));
			});
			
			// Отправка формы
			$scope.checkFields = function() {
				if (!$scope.login) {
					angular.element('#inputLogin').focus()
					$scope.form_errors = "Укажите логин"
					return false
				}
				if (!$scope.password) {
					angular.element('#inputPassword').focus()
					$scope.form_errors = "Укажите пароль"
					return false
				}
				
				
			 	l.start();
				
				ajaxStart()
				$scope.in_process = true;
				$.post("index.php?controller=login&action=AjaxLogin", {
					'login'		: $scope.login,
					'password'	: $scope.password
				}, function(response) {
					console.log(response)
					if (response === true) {
						// window.location = "requests";
						location.reload()
					} else {
						ajaxEnd()
						$scope.in_process = false;
						l.stop()
						if (response == "banned") {
							notifyError("Пользователь заблокирован")
						} else {
							notifyError("Неправильная пара логин-пароль")
						}
						$scope.$apply()
						return false
					}
				}, "json")
			}
		});
	angular.module("Login", ["ngAnimate"])
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
		})
		.controller("LoginCtrl", function($scope) {
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
								
				$.post("index.php?controller=login&action=AjaxLogin", {
					'login'		: $scope.login,
					'password'	: $scope.password
				}, function(response) {
					console.log(response)
					if (response == "true") {
						window.location = "requests";
					} else {
						$scope.form_errors = "Неправильная пара логин-пароль"
						return false
					}
				})
 
			}
		});
	app = angular.module("Login", ["ngAnimate"])
		.controller("LoginCtrl", function($scope) {
			angular.element(document).ready(function() {
				set_scope("Login")
				l = Ladda.create(document.querySelector('#login-submit'));
			});

			//обработка события по enter в форме логина
			$scope.enter = function($event){
				if($event.keyCode == 13){
					$scope.checkFields()
				}
			}

            $scope.goLogin = function() {
                ajaxStart()
                $.post("index.php?controller=login&action=AjaxLogin", {
					'login'		: $scope.login,
					'password'	: $scope.password,
                    'code'      : $scope.code,
                    'captcha'   : grecaptcha.getResponse()
				}, function(response) {
					console.log(response)
                    grecaptcha.reset()
					if (response === true) {
						// window.location = "requests";
						location.reload()
					} else if (response === 'sms') {
                        ajaxEnd()
						$scope.in_process = false;
						l.stop()
                        $scope.sms_verification = true
                        $scope.$apply()
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

			 	l.start()
				$scope.in_process = true

                if (grecaptcha.getResponse() == '') {
                    grecaptcha.execute()
                } else {
                    $scope.goLogin()
                }
			}
		});


function captchaChecked() {
    ang_scope.goLogin()
}
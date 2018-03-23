	error_messages = {
		'-1': 'Пользователь с таким email не найден. Пожалуйста, проверьте правильность написания email или позвоните в администрацию ЕГЭ-Центра',
		'-2': 'Невозможно войти. Пожалуйста, свяжитесь с администрацией по телефону'
	}

	app = angular.module("Login", ["ngAnimate"])
		.controller("ResetPwdCtrl", function($scope) {
			$scope.success = false

			angular.element(document).ready(function() {
				set_scope("Login")
				l = Ladda.create(document.querySelector('#login-submit'));
			})

			//обработка события по enter в форме логина
			$scope.enter = function($event){
				if($event.keyCode == 13){
					$scope.go()
				}
			}

			$scope.go = function() {
				if ($scope.pwd_1 != $scope.pwd_2) {
					notifyError('Пароли не совпадают')
					return
				}
				if ($scope.pwd_1.length < 8) {
					notifyError('Пароль должен быть<br> длиннее 8 символов')
					return
				}
				ajaxStart()
				l.start()
				$scope.in_process = true
				$.post("/login/AjaxResetPwd", {
					'password': $scope.pwd_1,
					'code': $scope.code
				}, function(response) {
					ajaxEnd()
					l.stop()
					$scope.in_process = false
					if (response == -1) {
						notifyError("Произошла ошибка")
					} else {
						$scope.success = true
						$scope.$apply()
					}
				})
			}
		})
		.controller("GetPwdCtrl", function($scope) {
			$scope.success = false
			$scope.error = false

			angular.element(document).ready(function() {
				set_scope("Login")
				l = Ladda.create(document.querySelector('#login-submit'));
			})

			//обработка события по enter в форме логина
			$scope.enter = function($event){
				if($event.keyCode == 13){
					$scope.go()
				}
			}

			$scope.go = function() {
				ajaxStart()
				l.start()
				$scope.in_process = true
				$.post("/login/AjaxGetPwd", {
					'email': $scope.email
				}, function(response) {
					ajaxEnd()
					l.stop()
					$scope.in_process = false
					if (response < 0) {
						$scope.error = error_messages[response]
					} else {
						$scope.success = true
					}
					$scope.$apply()
				})
			}
		})
		.controller("LoginCtrl", function($scope) {
			$scope.error = false
			angular.element(document).ready(function() {
				set_scope("Login")
				l = Ladda.create(document.querySelector('#login-submit'));
                login_data = $.cookie("login_data")
                if (login_data !== undefined) {
                    login_data = JSON.parse(login_data)
                    $scope.login = login_data.login
                    $scope.password = login_data.password
                    $scope.sms_verification = true
                    $scope.$apply()
                }
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
						$.removeCookie('login_data')
						location.reload()
					} else if (response === 'sms') {
                        ajaxEnd()
						$scope.in_process = false;
						l.stop()
                        $scope.sms_verification = true
                        $scope.$apply()
                        $.cookie("login_data", JSON.stringify({login: $scope.login, password: $scope.password}), { expires: 1 / (24 * 60) * 2, path: '/' })
                    } else {
						ajaxEnd()
						$scope.in_process = false;
						l.stop()
						if (response < 0) {
							$scope.error = error_messages[response]
						} else if (response == "banned") {
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

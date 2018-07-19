	error_messages = {
		'-1': 'Пользователь с таким email не найден. Пожалуйста, проверьте правильность написания email или позвоните в администрацию ЕГЭ-Центра',
		'-2': 'Невозможно войти. Пожалуйста, свяжитесь с администрацией по телефону'
	}

	app = angular.module("Login", ["ngAnimate"])
		.controller("ResetPwdCtrl", function($scope) {
			$scope.success = false

			angular.element(document).ready(function() {
				loadImage()
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
				$scope.error = false
				if ($scope.pwd_1 != $scope.pwd_2) {
					$scope.error = 'Пароли не совпадают'
					return
				}
				if ($scope.pwd_1.length < 8) {
					$scope.error = 'Пароль должен быть длиннее 8 символов'
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
						$scope.error = "Произошла ошибка"
					} else {
						$scope.success = true
					}
					$scope.$apply()
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
				$scope.error = false
				l.start()
				$scope.in_process = true
				$.post("/login/AjaxGetPwd", {
					'email': $scope.email
				}, function(response) {
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

			loadImage = function() {
				console.log('loading image')
			  $scope.image_loaded = false;
			  img = new Image;
			  img.addEventListener("load", function() {
				  console.log('image loaded')
			    $('body').css({
			      'background-image': "url(" + $scope.wallpaper.image_url + ")"
			    });
			    $scope.image_loaded = true;
			    $scope.$apply();
			    setTimeout(function() {
			      $('#center').removeClass('animated').removeClass('fadeIn').removeAttr('style');
			  }, 2000);
			  });
			  img.src = $scope.wallpaper.image_url;
			};

			$scope.error = false
			angular.element(document).ready(function() {
				loadImage()
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
				$scope.login = 'maksim@kolyadin.com'
				$scope.password = '184005'
			});

			//обработка события по enter в форме логина
			$scope.enter = function($event){
				if($event.keyCode == 13){
					$scope.checkFields()
				}
			}

            $scope.goLogin = function() {
                $.post("index.php?controller=login&action=AjaxLogin", {
					'login'		: $scope.login,
					'password'	: $scope.password,
                    'code'      : $scope.code,
                    'captcha'   : grecaptcha.getResponse()
				}, function(response) {
                    grecaptcha.reset()
					if (response === true) {
						$.removeCookie('login_data')
						location.reload()
					} else if (response === 'sms') {
						$scope.in_process = false;
						l.stop()
                        $scope.sms_verification = true
                        $.cookie("login_data", JSON.stringify({login: $scope.login, password: $scope.password}), { expires: 1 / (24 * 60) * 2, path: '/' })
                    } else {
						$scope.in_process = false;
						l.stop()
						if (response == "banned") {
							$scope.error = "Пользователь заблокирован"
						} else {
							$scope.error = "Неправильная пара логин-пароль"
						}
					}
					$scope.$apply()
				}, "json")
            }

			// Отправка формы
			$scope.checkFields = function() {
				$scope.error = false
				if (!$scope.login) {
					angular.element('#inputLogin').focus()
					$scope.error = "Укажите логин"
					return false
				}
				if (!$scope.password) {
					angular.element('#inputPassword').focus()
					$scope.error = "Укажите пароль"
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
	console.log('go login')
    ang_scope.goLogin()
}

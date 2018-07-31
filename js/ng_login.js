	error_messages = {
		'-1': 'Пользователь с таким email не найден. Пожалуйста, проверьте правильность написания email или позвоните в администрацию ЕГЭ-Центра',
		'-2': 'Невозможно войти. Пожалуйста, свяжитесь с администрацией по телефону'
	}

	app = angular.module("Login", ["ngAnimate"])
		.controller("GetPwdCtrl", function($scope) {
			$scope.success = false
			$scope.message = {body: ''}

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

			$scope.step = 1
			$scope.go = function() {
				l.start()
				$scope.in_process = true
				switch($scope.step) {
					case 1:
						sendCodeToEmail()
						break
					case 2:
						checkCode()
						break
					case 3:
						setNewPassword()
				}
			}

			sendCodeToEmail = function() {
				$.post("/login/AjaxGetPwd", {
					'email': $scope.email
				}, function(response) {
					l.stop()
					$scope.in_process = false
					if (response < 0) {
						$scope.message = {type: 'error', body: error_messages[response]}
					} else {
						$scope.message = {type: 'success', body: 'код отправлен на ' + $scope.email}
						$scope.step = 2
						$scope.user_id = response
					}
					$scope.$apply()
				}, 'json')
			}

			checkCode = function() {
				$.post("/login/AjaxCheckCode", {
					'code': $scope.code,
					'user_id': $scope.user_id
				}, function(response) {
					l.stop()
					$scope.in_process = false
					if (response === true) {
						$scope.message = {type: 'success', body: 'установите новый пароль'}
						$scope.step = 3
					} else {
						$scope.message = {type: 'error', body: 'неверный код подверждения'}
					}
					$scope.$apply()
				}, 'json')
			}

			setNewPassword = function() {
				if ($scope.pwd_1 != $scope.pwd_2) {
					$scope.message = {type: 'error', body: 'Пароли не совпадают'}
					l.stop()
					$scope.in_process = false
					return
				}
				if ($scope.pwd_1.length < 8) {
					$scope.message = {type: 'error', body: 'Пароль должен быть длиннее 8 символов'}
					l.stop()
					$scope.in_process = false
					return
				}
				$.post("/login/AjaxResetPwd", {
					'password': $scope.pwd_1,
					'user_id': $scope.user_id
				}, function(response) {
					l.stop()
					$scope.in_process = false
					$scope.success = true
					$scope.message = {body: ''}
					$scope.$apply()
				})
			}
		})
		.controller("LoginCtrl", function($scope, $timeout) {

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
				if ($scope.logged_user) {
					$scope.login = $scope.logged_user.email
				}

                login_data = $.cookie("login_data")
                if (login_data !== undefined) {
                    login_data = JSON.parse(login_data)
                    $scope.login = login_data.login
                    $scope.password = login_data.password
                    $scope.sms_verification = true
                    $scope.$apply()
                }
			});

			$scope.clearLogged = function() {
				$scope.logged_user = null
				$scope.login = ''
				$.removeCookie('logged_user')
			}

			//обработка события по enter в форме логина
			$scope.enter = function($event){
				if($event.keyCode == 13){
					$scope.checkFields()
				}
			}

            $scope.goLogin = function() {
				$scope.error = null
                $.post("index.php?controller=login&action=AjaxLogin", {
					'login'		: $scope.login,
					'password'	: $scope.password,
                    'code'      : $scope.code,
					'birthday'	: $scope.birthday,
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
					} else if (response === 'verify_birthday') {
						$scope.verify_birthday = true
						$scope.in_process = false
						l.stop()
						$timeout(function() {
							$('.verify-birthday').mask("99.99.9999", {clearIfNotMatch: true})
							$('.verify-birthday').datepicker({
								language	: 'ru',
								orientation	: 'top left',
								format: 'dd.mm.yyyy',
								autoclose	: true
							})
						})
                    } else {
						$scope.in_process = false;
						l.stop()
						if (response == "banned") {
							$scope.error = "Пользователь заблокирован"
						} else if (response == "wrong_birthday") {
							$scope.error = "Неверная дата рождения"
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

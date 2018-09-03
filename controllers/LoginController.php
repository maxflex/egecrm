<?php

	// Контроллер
	class LoginController extends Controller
	{
		public $defaultAction = "login";

		/**
		 * Перед выполнением всех экшнов.
		 *
		 */
		public function beforeAction()
		{
			// Если пользователь уже вошел, либо была галка "запомнить",
			// то редиректим на ту страницу, куда пользователь шел изначально
			if (!isset($_GET['access_denied']) && User::loggedIn()) {
				// Если пользователь залогинен и пытается перейти на страницу логина,
				// то редиректим его на страницу заявок
				if ($_GET["controller"] == "login") {
					switch (User::fromSession()->type) {
						case Admin::USER_TYPE: {
							if (isset($_GET['url'])) {
								$this->redirect(SsoHandler::handle($_GET['url']));
							} else {
								$this->redirect("requests");
							}
							break;
						}
						case Teacher::USER_TYPE:
						case Student::USER_TYPE:
						{
							$this->redirect( strtolower(User::fromSession()->type) . "s/groups");
							break;
						}
					}
				} else {
					// Иначе обновляем страницу (уже залогинены. местоположение сохраняется)
					$this->refresh();
				}
			}
			$this->addCss("signin");
			$this->addJs("ng_login");
		}

		// Папка вьюх
		protected $_viewsFolder	= "login";

		// Страница входа
		public function actionLogin()
		{
			$this->render("login", self::pageData(), "login");
		}

		public function actionPassword()
		{
			$this->render('password', self::pageData(), 'login');
		}

		private static function pageData()
		{
			$wallpaper = Background::get();
			return [
				'wallpaper' => $wallpaper,
				'ang_init_data' => angInit([
					'wallpaper'	=> $wallpaper,
					'logged_user' => isset($_COOKIE['logged_user']) ? json_decode($_COOKIE['logged_user']) : 0,
					'error' => isset($_GET['access_denied']) ? 1 : 0,
				])
			];
		}

		/**
		 * Выход пользователя.
		 *
		 */
		public function logout()
		{
			User::logout();
			return redirect('/login');
		}

		public function actionLogout()
		{
            self::log(User::id(), 'logout');

			if (isset($_SESSION["user"]) && $_SESSION["user"]) {
	            SessionService::destroy();
	            unset($_SESSION['user']);
				header("Refresh:0");
	        }
		}

		##################################################
		###################### AJAX ######################
		##################################################

		/**
		 * Вход пользователя.
		 *
		 */
		public function actionAjaxLogin()
		{
			extract($_POST);
            $recaptcha = new \ReCaptcha\ReCaptcha(RECAPTCHA_SECRET);
            $resp = $recaptcha->verify($captcha, $_SERVER['REMOTE_ADDR']);
            if (! $resp->isSuccess()) {
                self::log(null, 'failed_login', 'попытка обхода captcha');
                returnJSON($resp->getErrorCodes());
            }

            $query = ["email='$login'"];

            # проверка логина
            if (self::userExists($query)) {
                $user_id = dbConnection()->query('select id from users where '. $query[0])->fetch_object()->id;
            } else {
                self::log(null, 'failed_login', 'неверный логин', compact('login'));
                returnJSON(false);
            }

            # проверка пароля
            $query[] = "password='" . User::password($password) . "'";
            if (! self::userExists($query)) {
                self::log($user_id, 'failed_login', 'неверный пароль');
                returnJSON(false);
            }

			// Пытаемся найти пользователя
			$User = User::find(['condition'	=> implode(' AND ', $query)]);

			// Если входит представитель, подменяем на ученика
			if ($User->type == Representative::USER_TYPE) {
				$Representative = $User;
				$student_id = Student::find(['condition' => "id_representative={$User->id_entity}"])->id;
				$User = User::find(['condition' => "id_entity={$student_id} AND type='" . Student::USER_TYPE . "'"]);
			}

            // Пользователь заблокирован?
            if ($User->type == Admin::USER_TYPE && $User->allowed(Shared\Rights::EC_BANNED)) {
                self::log($user_id, 'failed_login', 'пользователь заблокирован');
                returnJSON('banned');
            }

            // Учитель заблокирован?
            if ($User->type == Teacher::USER_TYPE && Teacher::getLight($User->id_entity, ['in_egecentr'])->in_egecentr != 2) {
				self::log($user_id, 'failed_login', 'пользователь заблокирован');
                returnJSON('banned');
            }

            // Ученик заблокирован?
            if ($User->type == Student::USER_TYPE && Student::isBanned($User->id_entity)) {
				self::log($user_id, 'failed_login', 'пользователь заблокирован');
                returnJSON('banned');
            }

			$allowed_to_login = $User->allowedToLogin();

			if ($allowed_to_login) {
                // Нужна ли дополнительная смс-проверка для этого IP
                if ($allowed_to_login->confirm_by_sms) {
                    $client = new Predis\Client();
                    $sent_code = $client->get("egecrm:codes:{$User->id}");
                    // если уже был отправлен – проверяем
                    if (! empty($sent_code)) {
                        if ($code != $sent_code) {
                            self::log($user_id, 'failed_login', 'неверный смс-код');
                            returnJson(false);
                        } else {
                            $client->del("egecrm:codes:{$User->id}");
                        }
                    } else {
                        // иначе отправляем код
                        self::log($user_id, 'sms_code_sent');
                        Sms::verify($User);
                        returnJson('sms');
                    }
                }

                self::log($user_id, 'success_login');

                $User->toSession(true); 	// Входим в сессию
				SessionService::action(true);
				SessionService::clearCache();

				$Entity = $User->getEntity();
				if (User::isStudent() || User::isTeacher() || User::isRepresentative()) {
					$Entity->login_count++;
					$Entity->save("login_count");
				}

				$email = $User->email;
				$type = $User->type;
				switch($User->type) {
					case Teacher::USER_TYPE:
						$name = implode(' ', [$Entity->first_name, $Entity->middle_name]);
						$photo = $Entity->photo_extension ? (EGEREP_URL . "img/tutors/{$Entity->id}.{$Entity->photo_extension}") : null;
						break;
					case Admin::USER_TYPE:
					case Student::USER_TYPE:
						$name = implode(' ', [$Entity->first_name, $Entity->last_name]);
						$photo = $Entity->has_photo_cropped ? $Entity->photo_url : null;
						break;
					case Representative::USER_TYPE:
						$name = implode(' ', [$Representative->first_name, $Representative->last_name]);
						$photo = $Entity->has_photo_cropped ? $Entity->photo_url : null;
						$email = $Representative->email;
						$type = Representative::USER_TYPE;
						break;
				}

				setcookie("logged_user", json_encode(compact('email', 'type', 'name', 'photo')), time() + (3600 * 24 * 365), "/");
				returnJson(true);					// Ответ АЯКСУ, мол, вошли нормально
			} else {
                self::log($user_id, 'failed_login', 'нет прав доступа для данного IP');
				returnJSON(false);
			}
		}


		public function actionAjaxGetPwd()
		{
			extract($_POST);

			$user = User::getByEmail($email);
			if ($user) {
				// если с таким email > 1
				if (User::getByEmail($email, 'count') > 1) {
					returnJsonAng(-2);
				}
				$user->resetPassword();
				returnJson($user->id);
			} else {
				returnJsonAng(-1);
			}
		}

		public function actionAjaxCheckCode()
		{
			extract($_POST);
			$client = new Predis\Client();
			$key = "egecrm:reset-password:{$user_id}";
			if ($client->exists($key)) {
				$sent_code = $client->get($key);
				if ($sent_code == $code) {
					$client->del($key);
					returnJson(true);
				}
			}
			returnJson(false);
		}

		public function actionAjaxResetPwd()
		{
			extract($_POST);
			$user = User::findById($user_id);
			$user->password = User::password($password);
			$user->save('password');
		}


        /**
         * Существует ли пользователь?
         */
        private static function userExists($query)
        {
            return User::count([
                'condition' => implode(' AND ', $query)
            ]);
        }

        public static function log($user_id, $type, $message = '', $data = [])
        {
            $data = array_merge($data, [
                $type => $message,
                'user_agent' => @$_SERVER['HTTP_USER_AGENT']
            ]);
            Log::custom('authorization', $user_id, $data);
        }
	}

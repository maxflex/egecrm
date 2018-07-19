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
			if (User::loggedIn()) {
				// Если пользователь залогинен и пытается перейти на страницу логина,
				// то редиректим его на страницу заявок
				if ($_GET["controller"] == "login") {
					switch (User::fromSession()->type) {
						case Admin::USER_TYPE: {
							$this->redirect("requests");
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
			$this->render("login", ['wallpaper' => Background::get()], "login");
		}

		public function actionPassword()
		{
			$this->render('password', ['wallpaper' => Background::get()], 'login');
		}


		/**
		 * Выход пользователя.
		 *
		 */
		public function actionLogout()
		{
            self::log(User::id(), 'logout');

			// Удаляем сессию
			session_destroy();
			session_unset();

			// Очищаем куку залогиненного пользователя
			removeCookie("egecrm_token");

			// Очищаем куку сессии PHP
			removeCookie("PHPSESSID", "/");
			//setcookie("PHPSESSID","",time()-3600,"/"); // delete session cookie

			// Редирект на страницу LOGOUT с хэшем, чтобы в ангуляре убралось тоже
			$this->redirect("login");
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

				if (User::isStudent() || User::isTeacher() || User::isRepresentative()) {
					$Entity = User::getEntity();
					$Entity->login_count++;
					$Entity->save("login_count");
				}

				returnJson(true);					// Ответ АЯКСУ, мол, вошли нормально
			} else {
                self::log($user_id, 'failed_login', 'нет прав доступа для данного IP');
				returnJSON(false);
			}
		}

		public function actionReset()
		{
			$client = new Predis\Client();
			$code = $_GET['code'];
			$user_id = User::getIdFromCode($code);
			$key = "egecrm:reset-password:{$user_id}";
			if ($client->exists($key) && $client->get($key) == $code) {
				$this->render('reset', ['wallpaper' => Background::get()], 'login');
			} else {
				$this->render('link_timeout', ['wallpaper' => Background::get()], 'login');
			}
			// $code = $_GET['code'];
			// $user_id = base64_decode(substr($code, 32, 999));
			//
			// return;
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
			} else {
				returnJsonAng(-1);
			}
		}

		public function actionAjaxResetPwd()
		{
			extract($_POST);
			$client = new Predis\Client();
			$user_id = User::getIdFromCode($code);
			$key = "egecrm:reset-password:{$user_id}";
			if ($client->exists($key) && $client->get($key) == $code) {
				$user = User::findById($user_id);
				$user->password = User::password($password);
				$user->save('password');
				$client->del($key);
			} else {
				returnJsonAng(-1);
			}
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

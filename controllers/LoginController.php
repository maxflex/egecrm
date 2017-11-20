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
			if (User::loggedIn() || User::rememberMeLogin()) {
				// Если пользователь залогинен и пытается перейти на страницу логина,
				// то редиректим его на страницу заявок
				if ($_GET["controller"] == "login") {
					switch (User::fromSession()->type) {
						case User::USER_TYPE: {
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
		}

		// Папка вьюх
		protected $_viewsFolder	= "login";

		// Страница входа
		public function actionLogin()
		{
			$this->addCss("signin");
			$this->addJs("ng_login");
			$this->render("login", array(), "login");
		}


		/**
		 * Выход пользователя.
		 *
		 */
		public function actionLogout()
		{
            self::log(User::fromSession()->id, 'logout');

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

            $query = ["login='$login'"];

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

            // Пользователь заблокирован?
            if ($User->type == User::USER_TYPE && $User->allowed(Shared\Rights::EC_BANNED)) {
                self::log($user_id, 'failed_login', 'пользователь заблокирован');
                returnJSON('banned');
            }

            // Учитель заблокирован?
            if ($User->type == Teacher::USER_TYPE) {
				$status = Teacher::getLight($User->id_entity, ['in_egecentr'])->in_egecentr;
				if (! in_array($status, [1, 2])) {
					self::log($user_id, 'failed_login', 'пользователь заблокирован');
					returnJSON('banned');
				}
            }

            // Ученик заблокирован?
            if ($User->type == Student::USER_TYPE && Student::isBanned($User->id_entity)) {
				self::log($user_id, 'failed_login', 'пользователь заблокирован');
                returnJSON('banned');
            }

            $worldwide_access = $User->type == User::USER_TYPE ? (User::fromOffice() || $User->allowed(Shared\Rights::WORLDWIDE_ACCESS)) : true;

			if ($worldwide_access) {
                // Дополнительная СМС-проверка, если пользователь логинится если не из офиса
                if (! User::fromOffice() && $User->type == User::USER_TYPE) {
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

                $User->toSession(true, true); 	// Входим в сессию

				if ($User->isStudent() || $User->isTeacher()) {
					$User->login_count++;
					$User->save("login_count");
				}

				returnJson(true);					// Ответ АЯКСУ, мол, вошли нормально
			} else {
                self::log($user_id, 'failed_login', 'нет прав доступа для данного IP');
				returnJSON(false);
			}
		}


        /**
         * Существует ли пользователь?
         */
        private static function userExists($query)
        {
            // echo implode(' AND ', $query) . "\n";
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
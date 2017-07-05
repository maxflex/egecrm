<?php	// Контроллер	class LoginController extends Controller	{		public $defaultAction = "login";		/**		 * Перед выполнением всех экшнов.		 *		 */		public function beforeAction()		{			// Если пользователь уже вошел, либо была галка "запомнить",			// то редиректим на ту страницу, куда пользователь шел изначально			if (User::loggedIn() || User::rememberMeLogin()) {				// Если пользователь залогинен и пытается перейти на страницу логина,				// то редиректим его на страницу заявок				if ($_GET["controller"] == "login") {					switch (User::fromSession()->type) {						case User::USER_TYPE: {							$this->redirect("requests");							break;						}						case Teacher::USER_TYPE:						case Student::USER_TYPE:						{							$this->redirect( strtolower(User::fromSession()->type) . "s/groups");							break;						}					}				} else {					// Иначе обновляем страницу (уже залогинены. местоположение сохраняется)					$this->refresh();				}			}		}		// Папка вьюх		protected $_viewsFolder	= "login";		// Страница входа		public function actionLogin()		{			$this->addCss("signin");			$this->addJs("ng_login");			$this->render("login", array(), "login");		}		/**		 * Выход пользователя.		 *		 */		public function actionLogout()		{			// Удаляем сессию			session_destroy();			session_unset();			// Очищаем куку залогиненного пользователя			removeCookie("egecrm_token");			// Очищаем куку сессии PHP			removeCookie("PHPSESSID", "/");			//setcookie("PHPSESSID","",time()-3600,"/"); // delete session cookie			// Редирект на страницу LOGOUT с хэшем, чтобы в ангуляре убралось тоже			$this->redirect("login");		}		##################################################		###################### AJAX ######################		##################################################		/**		 * Вход пользователя.		 *		 */		public function actionAjaxLogin()		{			extract($_POST);            $recaptcha = new \ReCaptcha\ReCaptcha(RECAPTCHA_SECRET);            $resp = $recaptcha->verify($captcha, $_SERVER['REMOTE_ADDR']);            if (! $resp->isSuccess()) {                returnJSON($resp->getErrorCodes());            }			// Пытаемся найти пользователя			$User = User::find(array(				"condition"	=> "login='$login' AND password='" . User::password($password) . "'"			));            if (! $User) {                returnJSON(false);            }            $worldwide_access = $User->type == User::USER_TYPE ? ($User->allowed(Shared\Rights::WORLDWIDE_ACCESS) || User::fromOffice()) : true;			if ($worldwide_access) {				if ($User->allowed(Shared\Rights::EC_BANNED)) {					returnJSON('banned');				} else {					$User->toSession(true, true); 	// Входим в сессию					if ($User->isStudent() || $User->isTeacher()) {						$User->login_count++;						$User->save("login_count");					}					returnJson(true);					// Ответ АЯКСУ, мол, вошли нормально				}			} else {				returnJSON(false);			}		}	}
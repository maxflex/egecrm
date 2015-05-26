<?php	// Контроллер	class LoginController extends Controller	{		public $defaultAction = "login";				/**		 * Перед выполнением всех экшнов.		 * 		 */		public function beforeAction()		{			// Если пользователь уже вошел, либо была галка "запомнить",			// то редиректим на ту страницу, куда пользователь шел изначально			if (User::loggedIn() || User::rememberMeLogin()) {				// Если пользователь залогинен и пытается перейти на страницу логина,				// то редиректим его на страницу заявок				if ($_GET["controller"] == "login") {					$this->redirect("requests");				} else {					// Иначе обновляем страницу (уже залогинены. местоположение сохраняется)					$this->refresh();									}			} else {				$this->addJs("ng_start"); // Если не войти из сессии, показываем страницу входа			}		}				// Папка вьюх		protected $_viewsFolder	= "login";				// Страница входа			public function actionLogin()		{				$this->addCss("signin");			$this->addJs("ng_login");			$this->render("login", array(), "login");		}						/**		 * Выход пользователя.		 * 		 */		public function actionLogout()		{			// Удаляем сессию			session_destroy();			session_unset();						// Очищаем куку залогиненного пользователя			removeCookie("egecrm_token");						// Очищаем куку сессии PHP			removeCookie("PHPSESSID", "/"); 			//setcookie("PHPSESSID","",time()-3600,"/"); // delete session cookie						if (isset($_COOKIE["PHPSESSID"])) {				exit("HIA");			}						// Редирект на страницу LOGOUT с хэшем, чтобы в ангуляре убралось тоже			$this->redirect("login");		}				##################################################		###################### AJAX ######################		##################################################				/**		 * Вход пользователя.		 * 		 */		public function actionAjaxLogin()		{			extract($_POST);						// Пытаемся найти пользователя			$User = User::find(array(				"condition"	=> "login='$login' AND password='".User::password($password)."'"			));						// Если пользователь найден			if ($User) {				$User->toSession(true, true); 	// Входим в сессию				toJson(true);					// Ответ АЯКСУ, мол, вошли нормально			}		}	}
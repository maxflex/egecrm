<?php
	// Если не установлен контроллер, то главная страница
	/*if (empty($_GET["controller"])) {
		include_once("main.php");
		exit();
	}*/

	// Время выполнения скрипта
	$time_start = microtime(true);

	// Подключаем файл конфигураций
	include_once("config.php");

	// Если сессия уже когда-то была начата (если пользователь залогинен), то возобновляем ее
	if(isset($_COOKIE["PHPSESSID"])) {
	  session_start();
	}
	session_start();

	// Получаем названия контроллеров и экшена
	$_controller	 = $_GET["controller"];	// Получаем название контроллера
	$_action		 = $_GET["action"];		// Получаем название экшена

	// Проверка на аякс-запрос
	if (strtolower(mb_strimwidth($_action, 0, 4)) == "ajax") {

		$_ajax_request = true;

		// Это аякс-запрос, к скрипту можно обращаться только через AJAX
/*
		if (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
			die("SECURITY RESTRICTION: THIS PAGE ACCEPTS AJAX REQUESTS ONLY (poshel nahuj)");	// Выводим мега-сообщение
		}
*/
	} else {
		$_ajax_request = false;
	}

	/* Основные действия */
	$_controllerName = ucfirst(strtolower($_controller))."Controller";	// Преобразуем название контроллера в NameController
	$_actionName	 = "action".ucfirst(strtolower($_action));			// Преобразуем название экшена в actionName


	// Проверяем зайден ли пользователь. Если не зайден, форсируем контроллер логина с экшеном Login
	// не на логин контроллер, не на апи контроллер (эти страницы доступны незалогиненным)
	// (можно сделать и редирект, для этого раскомментить первую строчку)

	$bypass_login = ["LoginController", "ApiController", "CronController", "MangoController"]; // эти страницы не требуют логина для просмотра

	$external_requests = ["ApiController", "CronController", "MangoController"];

	if (! LOCAL_DEVELOPMENT) {
		if ($_SERVER['HTTP_HOST'] != 'lk.ege-centr.ru' && !in_array($_controllerName, $external_requests)) {
			header("Location: https://lk.ege-centr.ru" . $_SERVER['REQUEST_URI']);
			exit();
		}
	}

	if ((!User::loggedIn()) && !in_array($_controllerName, $bypass_login)) {
	//	$this->redirect(BASE_ADDON . "login"); // Можно сделать так же редирект на страницу входа
		$_controllerName	= "LoginController";
		$_actionName		= "actionLogin";
	} else {
		if (User::loggedIn()) {
            try {
				SessionService::action();
            }
            catch (Exception $e) {
                header("Location: logout");
            }
		}

		// если у учителя в URL нет teachers/ или у ученика нет students/
		if (!$_ajax_request && $_controllerName != "AsController" && $_controllerName != "LoginController") {
            // логируем проход по URL
            if (User::loggedIn() && $_SERVER['REQUEST_METHOD'] === 'GET' && !($_controller == 'users' && $_action == 'get')) {
                // error_log($_controller . " | " . $_action . " | " . @$_SERVER['REQUEST_URI']);
                Log::custom('url', User::fromSession()->id, ['url' => @$_SERVER['REQUEST_URI']]);
            }
			if (User::fromSession()->type == Teacher::USER_TYPE || User::fromSession()->type == Student::USER_TYPE) {
				// sms может отправлять учитель
				if ($_controller != 'sms') {
					if (strpos($_SERVER['REQUEST_URI'], BASE_ADDON . strtolower(User::fromSession()->type)) === false) {
						$IndexController = new $_controllerName;	// Создаем объект контроллера
						$IndexController->renderRestricted();
					}
				}
			}
		}
	}

	$IndexController = new $_controllerName($_actionName);	// Создаем объект контроллера

// 	preType([$_GET, $_controller, $_action, $_controllerName, $_actionName], true);


	// проверка прав доступа к контроллеру
	if (!in_array(User::fromSession()->type, $IndexController::$allowed_users) && !in_array($_controllerName, $bypass_login)) {
		$IndexController->renderRestricted();
	}

	// Запускаем BeforeAction, если существует
	if (method_exists($IndexController, "beforeAction")) {
		$IndexController->beforeAction();
	}

	// Если указанный _actionName существует – запускаем его
	if (method_exists($IndexController, $_actionName))
	{
		$IndexController->$_actionName();			// Запускаем нужное действие
	} // иначе запускаем метод по умолчанию
	else
	{
		$IndexController->{"action".$IndexController->defaultAction}();
	}


	// Когда понадобится AfterAction – раскомментировать
	/* // Запускаем afterAction, если существует
	if (method_exists($IndexController, "afterAction")) {
		$IndexController->afterAction();
	} */

	// Конец выполнения скрипта
	$time_end = microtime(true);

	if (!$_ajax_request && ((User::loggedIn() && allowed(Shared\Rights::IS_DEVELOPER)) || isset($_GET['execution_time'])) || strpos($_SERVER['HTTP_REFERER'], '3000')) {
		$time = $time_end - $time_start;
	    echo "<span class='pull-right small text-gray' style='margin-right: 15px'>время выполнения: " . round($time, 2) . " сек</span>";
	}

	/*********************/
?>

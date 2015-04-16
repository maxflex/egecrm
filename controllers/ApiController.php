<?php

	// Контроллер
	class ApiController extends Controller
	{  
		// Ключ API, проверяется при каждом запросе к API
		const API_KEY = "qfJTcVaMsUm278qHUfEm";
		
		// Перед выполнением любого действия, устанавливаем заголовок для JSON данных API
		public function beforeAction()
		{
			// Тип данных - JSON
			header('Content-Type: application/json');
			
			// Первым делом проверяем API_KEY
			if (trim($_POST["API_KEY"]) != self::API_KEY) {
				self::errorMessage("Invalid API_KEY");
			}
		}
		
		/*
		 * Получить пользователя по ID ВКонтакте
		 * $_POST["id_vk"] - передается по посту ID_VK, по нему находится пользователь
		 */
		public static function actionGetUserByIdVk()
		{
			// Находим пользователя по ID_VK
			$User = User::find(array(
				"condition"	=> "id_vk=".$_POST["id_vk"],
			));
			
			// Если пользователь найден
			if ($User) {
				// Возвращаем  JSON объекта пользователя
				self::returnJSON($User);
			} else {
				// Ошибка
				self::errorMessage("User not found");
			}
		}
		
		/*
		 * Получить пользователя по ID на Ratie
		 * $_POST["id_user"] - передается по посту ID_USER, по нему находится пользователь
		 */
		public static function actionGetUserById()
		{
			// Отключаем инициализацию подключения к БД
			//User::$initialize_on_new = (bool)$_POST["with_adjectives"];
			
			// Находим пользователя по ID на Ratie
			$User = self::getUser();
			
			// Если пользователь найден
			if ($User) {
				// Возвращаем  JSON объекта пользователя
				self::returnJSON($User);
			} else {
				// Ошибка
				self::errorMessage("User not found");
			}
		}
		
		/*
		 * Получить подписки и подписчиков пользователя
		 * в функцию должен передаваться id_user в $_POST["id_user"]
		 */
		public static function actionGetSubs()
		{	
			// Получаем пользователя
			$User = self::getUser();
			
			// Получаем его подписчиков и подписки
			$UserSubscribers	= $User->Subscribers();
			$UserSubscriptions	= $User->Subscriptions();
			
			// Отключаем инициализацию подключения к БД
			User::$initialize_on_new = false;
			
			// Создаем массив объектов пользователей подписчиков
			foreach ($UserSubscribers as $Subscriber) {
				$Subscribers[] = User::findById($Subscriber->id_user);
			}
			
			// Создаем массив объектов пользователей подписок
			foreach ($UserSubscriptions as $Subscription) {
				$Subscriptions[] = User::findById($Subscription->id_user);
			}
			
			self::returnJSON(array(
				"subscribers" 	=> $Subscribers,
				"subscriptions"	=> $Subscriptions,
			));
		}
		
		/*
		 * Получить новостную ленту пользователя
		 * в функцию должен передаваться id_user в $_POST["id_user"]
		 */
		public static function actionGetNews()
		{
			$User = self::getUser();
			
			self::returnJSON(array(
				"news" 		=> $User->getNews(),
				"old_news"	=> $User->getOldNews(),
			));
			
			$User->updateNewsCount();
		}
		
		/*
		 * Добавить прилагательное
		 */
		public static function actionAddThought()
		{
			$User = self::getUser();
			
			// Пытаемся добавить прилагательное
			$result = $User->addAdjective($_POST["adjective"]);
			
			// Если в результат записалась строка с ошибкой
			if (is_string($result)) {
				self::errorMessage($result);
			}
		}
		
		/*
		 * Добавить голос
		 */
		public static function actionAddVote()
		{
			// Подгоняем названия переменных под существующую функцию
			$_POST["id_viewing"]	= $_POST["id_user"];
			$_POST["id"]			= $_POST["id_adjective"];
			
			// Добавляем голос
			UserController::actionAjaxVote();
			/*
			// Инициализируем сессию для того, чтобы получить ID текущего пользователя, чья страница просматривается
			$User = User::findById($_POST["id_viewing"]);
			
			// Ищем прилагательное
			$Adjective = Adjective::findById($_POST["id"]);
			
			// Добавляем голос
			$Adjective->addVote(true, $_POST["type"]);*/
		}
		
		
		/*
		 * Возвратить комментарии к текущему прилагательному
		 * $count - возвратить только количество комментариев
		 */
		public static function actionGetComments()
		{
			// Подключаемся к БД пользователя, чтобы получить его прилагательные
			$User = self::getUser();
			
			// Получаем прилагательное
			$Adjective = Adjective::findById($_POST["id_adjective"]);
			
			// Получаем комментарии прилагательного
			self::returnJSON(
				$Adjective->getComments((bool)$_POST["count"])
			);
			
		}
		
		/*
		 * JSON-сообщение с ошибкой
		 * $error_message – сообщение ошибки
		 */
		public static function errorMessage($error_message)
		{
			exit(json_encode(array(
					"error_message"	=> $error_message,
					"post_data"		=> $_POST,
				)));
		}
		
		/*
		 * Возвратить JSON
		 */
		public static function returnJSON($Object)
		{
			echo json_encode($Object); 
		}
		
		/*
		 * Получить пользователя
		 */
		public static function getUser()
		{
			return User::findById($_POST["id_user"]);
		}
		
	}
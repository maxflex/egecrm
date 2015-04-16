<?php
	class User extends Model
	{
		const SALT 					= "32dg9823dldfg2o001-2134>?erj&*(&(*^";	// Для генерации кук
		
		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/

		public static $mysql_table	= "users";
		
		/*====================================== СИСТЕМНЫЕ ФУНКЦИИ ======================================*/
		
		
		/*====================================== СТАТИЧЕСКИЕ ФУНКЦИИ ======================================*/
		
		/*
		 * Автовход по Remember-me
		 * $redirect – нужно ли редиректить на главную страницу пользователя в случае автовхода?
		 */
		public static function rememberMeLogin($redirect = true)
		{
			// Кука токена хранится в виде: 
			// 1) Первые 16 символов MD5-хэш
			// 2) Остальные символы – id_user (код пользователя)
			// $cookie_hash = mb_strimwidth($_COOKIE["ratie_token"], 0, 32); // Нам не надо получать хэш из кук -- мы создаем новый здесь для сравнения
			$cookie_user = substr($_COOKIE["login_token"], 32);
			
			// Получаем пользователя по ID (чтобы из его параметров генерировать хэш)
			$User = User::findById($cookie_user);
			
			// Если пользователь найден
			if ($User) {
				// Генерируем хэш для сравнения с хешем в БД
				$hash = md5(self::SALT . $User->id . $User->password . self::SALT);
				
				// Пытаемся найти пользователя
				$RememberMeUser = self::find(array(
					"condition"	=> "id=".$cookie_user." AND token='{$hash}'",
				));
				
				// Если пользователь найден
				if ($RememberMeUser) {
				
					// Логинимся (не обновляем токен, создаем сессию)
					$RememberMeUser->toSession(false, true);
					
					if ($redirect) {
						header("Location: ".$RememberMeUser->login);
					}
				}
			}
		}
		
		/*
		 * Проверяем, залогинен ли пользователь
		 */
		public static function loggedIn()
		{
			return isset($_SESSION["user"]);
		}
		
		/*
		 * Пользователь из сессии
		 * @boolean $init – инициализировать ли соединение с БД пользователя
		 * @boolean $update – обновлять данные из БД
		 */
		public static function fromSession($upadte = false)
		{
			// Если обновить данные из БД, то загружаем пользователя
			if ($upadte) {
				$User = User::findById($_SESSION["user"]->id);
				$User->toSession();
			} else {
				// Получаем пользователя из СЕССИИ
				$User = $_SESSION["user"];
			}
						
			// Возвращаем пользователя
			return $User;
		}	

		/*====================================== ФУНКЦИИ КЛАССА ======================================*/
		
		/*
		 * Вход/запись пользователя в сессию
		 * $update_token – обновлять ли токен в БД (делается при авторизации)
		 * $start_session – стартовать ли сессию?
		 */
		public function toSession($update_token = false, $start_session = false)
		{
			// Если стартовать сессию
			if ($start_session) {
				session_set_cookie_params(3600 * 24,"/"); // PHP сессия на сутки
				session_start();
			}
			
			// Если обновлять токен
			if ($update_token) {
				self::updateToken();
				self::save();
			}
			
			$_SESSION["user"] = $this;
		}
		
		 /*
		  * Создаем/Обновляем token для автологина
		  */
		public function updateToken()
		{
			$this->token = md5(self::SALT . $this->id . $this->password . self::SALT);
			
			// Remember me token в КУКУ
			$cookie_time = time() + 3600 * 24 * 30 * 3; // час - сутки - месяц * 3 = КУКА на 3 месяца
			setcookie("login_token", $this->token . $this->id, $cookie_time);	// КУКА ТОКЕНА (первые 16 символов - токен, последние - id_user)
		}
		

	}
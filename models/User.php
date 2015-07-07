<?php
	class User extends Model
	{
		const SALT 					= "32dg9823dldfg2o001-2134>?erj&*(&(*^";	// Для генерации кук
		
		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/

		public static $mysql_table	= "users";
		
		/*====================================== СИСТЕМНЫЕ ФУНКЦИИ ======================================*/
	
		
		/*====================================== СТАТИЧЕСКИЕ ФУНКЦИИ ======================================*/
		
		
		/**
		 * Вернуть пароль, как в репетиторах
		 * 
		 */
		public static function password($password)
		{
			$password = md5($password."_rM");
            $password = md5($password."Mr");  
			
			return $password;
		}
		
		/*
		 * Функция определяет соединение БД
		 */
		public static function dbConnection()
		{
			// Открываем соединение с основной БД		
			$db_repetitors = new mysqli(DB_HOST, DB_LOGIN, DB_PASSWORD, DB_PREFIX."repetitors");
			
			// Установлено ли соединение
			if (mysqli_connect_errno($db_repetitors))
			{
				die("Failed to connect to USER {$id_user} MySQL: " . mysqli_connect_error());
			}
			
			// Устанавливаем кодировку
			$db_repetitors->set_charset("utf8");
			
			return $db_repetitors;	
		}
		
		
		/**
		 * Список пользователей.
		 * $selected – ID пользователя (!НЕ ПОРЯДКОВЫЙ НОМЕР В МАССИВЕ), выбранный по умолчанию
		 * $all -- получить всех пользователей (или только работающих)?
		 */
		public static function buildSelector($selected = false, $name = "id_user", $all = false)
		{
			$Users = $all ? self::findAll() : self::findAll(["condition" => "worktime=1"]);
			
			// Находим выбранного пользователя
			if ($selected) {
				$SelectedUser = array_pop(array_filter($Users, function($e) use ($selected) {
					return $e->id == $selected;
				}));
			}
			
			echo "<script src='js/user-color-control.js' type='text/javascript'></script>";
			echo "<select class='form-control' id='user-list' name='$name' ".($selected ? "style='background-color: {$SelectedUser->color}'" : "").">";
				echo "<option selected value=''>пользователь</option>";
				echo "<option disabled value=''>──────────────</option>";
			foreach ($Users as $User) {
				echo "<option ".($User->id == $selected ? "selected" : "")." style='background-color: {$User->color}' value='{$User->id}'>{$User->login}</option>";
			}
			echo "</select>";
		}
		
		/*
		 * Автовход по Remember-me
		 */
		public static function rememberMeLogin()
		{
			// Кука токена хранится в виде: 
			// 1) Первые 16 символов MD5-хэш
			// 2) Остальные символы – id_user (код пользователя)
			// $cookie_hash = mb_strimwidth($_COOKIE["ratie_token"], 0, 32); // Нам не надо получать хэш из кук -- мы создаем новый здесь для сравнения
			$cookie_user = substr($_COOKIE["egecrm_token"], 32);
			
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
					
					return true;
				} else {
					return false;
				}
			} else {
				return false;
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
			$cookie_time = time() + 3600 * 24 * 30 * 3; 						// час - сутки - месяц * 3 = КУКА на 3 месяца
			setcookie("egecrm_token", $this->token . $this->id, $cookie_time);	// КУКА ТОКЕНА (первые 16 символов - токен, последние - id_user)
		}
		

	}
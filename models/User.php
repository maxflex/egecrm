<?php

	class User extends Model
	{
		const SALT 					= "32dg9823dldfg2o001-2134>?erj&*(&(*^";	// Для генерации кук

		const USER_TYPE = "USER";
		const SEO_TYPE = "SEO";

		const LAST_REAL_USER_ID = 112;
		const ONLINE_TIME_MINUTES = 15;

        const UPLOAD_DIR = 'img/users/';
        const NO_PHOTO   = 'no-profile-img.gif';

		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/

		public static $mysql_table	= "users";

		public static $online_list;

        public $log_except = [
            'last_action_time',
            'last_action_link',
            'token',
            'login_count'

        ];
		/*====================================== СИСТЕМНЫЕ ФУНКЦИИ ======================================*/
		
		public function __construct($array = [], $flag = null)
        {
            parent::__construct($array);
            $this->salary = $this->salary ? $this->salary : '';
			if ($flag === null) {
				$this->has_photo_original = $this->hasPhotoOriginal();
	            $this->photo_original_size = $this->photoOriginalSize();
	            $this->photo_cropped_size = $this->photoCroppedSize();
	            $this->photo_url = $this->photoUrl();
	            
	            // цвет черный, если пользователя забанили
	            if ($this->banned) {
		        	$this->color = 'black';
	            }	
			}
        }
        
        public function photoPath($addon = '')
        {
            return static::UPLOAD_DIR . $this->id . $addon . '.' . $this->photo_extension;
        }

        public function photoUrl()
        {
            if ($this->hasPhotoCropped()) {
                $photo = $this->id . '.' . $this->photo_extension;
            } else {
                $photo = static::NO_PHOTO;
            }
            return static::UPLOAD_DIR . $photo;
        }

        public function hasPhotoOriginal()
        {
            return file_exists($this->photoPath('_original'));
        }

        public function hasPhotoCropped()
        {
            return file_exists($this->photoPath());
        }

        public function photoCroppedSize()
        {
            if ($this->hasPhotoCropped()) {
                return filesize($this->photoPath());
            } else {
                return 0;
            }
        }

        public function photoOriginalSize()
        {
            if ($this->hasPhotoOriginal()) {
                return filesize($this->photoPath('_original'));
            } else {
                return 0;
            }
        }

		/*====================================== СТАТИЧЕСКИЕ ФУНКЦИИ ======================================*/

		public static function getLogin($id_user)
		{
			$result = dbConnection()->query("SELECT login FROM users WHERE id={$id_user}");

			if ($result->num_rows) {
				return $result->fetch_object()->login;
			} else {
				return 'system';
			}
		 }

		public static function getOnlineList()
		{
			if (!static::$online_list) {
				$online = User::findAll([
					"condition" => "id <= " . self::LAST_REAL_USER_ID . " AND banned=0
						AND (UNIX_TIMESTAMP(NOW()) - last_action_time) / 60 < " . self::ONLINE_TIME_MINUTES . " AND id != " . User::fromSession()->id,
					"order" => "last_action_time DESC"
				]);

				$offline = User::findAll([
					"condition" => "id <= " . self::LAST_REAL_USER_ID . " AND banned=0
						AND (UNIX_TIMESTAMP(NOW()) - last_action_time) / 60 >= " . self::ONLINE_TIME_MINUTES . " AND id != " . User::fromSession()->id,
					"order" => "last_action_time DESC"
				]);

				static::$online_list = (object)[
					'online'	=> $online,
					'offline'	=> $offline,
				];
			}
			return static::$online_list;
		}

		/**
		 * Обновить время последнего действия.
		 *
		 */
		public function updateLastActionTime()
		{
			// не засчитываем AjaxCheckLogout за действие. не обновляем
			if ($_GET['action'] != 'AjaxCheckLogout') {
				$this->last_action_time = time();
				// если не ajax-действие, записываем ссылку последнего действия
				if (strpos(strtolower($_GET['action']), "ajax") !== 0) {
					$this->last_action_link = $_SERVER['REQUEST_URI'];
					$this->save('last_action_link');
				}
				$this->save('last_action_time');
			}
		}

		public static function isUser($return_string = false)
		{
			$return = User::fromSession()->type == User::USER_TYPE;

			if ($return_string) {
				return $return ? 'true' : 'false';
			} else {
				return $return;
			}
		}

		public static function isTeacher($return_string = false)
		{
			$return = User::fromSession()->type == Teacher::USER_TYPE;

			if ($return_string) {
				return $return ? 'true' : 'false';
			} else {
				return $return;
			}
		}

		public static function isStudent($return_string = false)
		{
			$return = User::fromSession()->type == Student::USER_TYPE;

			if ($return_string) {
				return $return ? 'true' : 'false';
			} else {
				return $return;
			}
		}

		public static function getLoginCount($id_entity, $type)
		{
			$Entity = self::find([
				"condition" => "id_entity = $id_entity AND type='$type'"
			]);

			if ($Entity) {
				return $Entity->login_count;
			} else {
				return 0;
			}
		}

		// Получить пользователей из кеша
		public static function getCached($with_system = false)
		{
			if (LOCAL_DEVELOPMENT) {
                $return = [];

			    $Users = self::findAll([
				    'condition' => "type = 'USER' AND `banned` = 0"
                ]);

				foreach ($Users as $User) {
					$return[$User->id] = $User->dbData();
				}

				return $return;
			} else {
				$Users = memcached()->get("Users");

				if (!$Users) {
					self::updateCache();
				}
				
				if ($with_system) {
					array_unshift($Users, [
						'id' 	=> 0,
						'login' => 'system',
					]);
				}

				return $Users;
			}
		}

		public static function updateCache()
		{
			$Users = self::findAll([
				"condition"=>"type = '".User::USER_TYPE."' and banned = 0"
			]);

			foreach ($Users as $User) {
				$return[$User->id] = $User->dbData();
			}

			$Users = $return;
			memcached()->set("Users", $Users, 2 * 24 * 3600); // кеш на 2 дня
		}

		public static function findAllReal()
		{
			return self::findAll([
				"condition" => "id <=" . self::LAST_REAL_USER_ID,
			]);
		}

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

			echo "<select class='form-control user-list' name='$name' ".($selected ? "style='background-color: {$SelectedUser->color}'" : "").">";
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
			if (User::loggedIn()) {
				return true;
			}

			// ОТКЛЮЧАЕМ ВХОД ПО REMEMBER-ME. УДАЛИТЬ СТРОЧКУ НИЖЕ, ЕСЛИ ОПЯТЬ ПОНАДОБИТСЯ
			return false;

			if (!isset($_COOKIE["egecrm_token"])) {
				return false;
			}

			// Кука токена хранится в виде:
			// 1) Первые 16 символов MD5-хэш
			// 2) Остальные символы – id_user (код пользователя)
			// $cookie_hash = mb_strimwidth($_COOKIE["ratie_token"], 0, 32); // Нам не надо получать хэш из кук -- мы создаем новый здесь для сравнения
			$cookie_user = substr($_COOKIE["egecrm_token"], 32);

			// Получаем пользователя по ID (чтобы из его параметров генерировать хэш)
			$User = User::findById($cookie_user);

			// Если пользователь найден и он не заблокирован
			if ($User && !$User->banned) {
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

					if ($RememberMeUser->isStudent() || $RememberMeUser->isTeacher()) {
						$RememberMeUser->login_count++;
						$RememberMeUser->save("login_count");
					}

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

		public static function findTeacher($id_teacher)
		{
			return self::find([
				"condition" => "id_entity=$id_teacher AND type='" . Teacher::USER_TYPE . "'"
			]);
		}

		public static function isDev() {
            return User::fromSession()->is_dev == 1;
		}

		public static function isRoot() {
            return User::fromSession()->id == 1;
		}

		public static function allowedToSeeTasks() {
			return User::fromSession()->show_tasks;
		}

		/*====================================== ФУНКЦИИ КЛАССА ======================================*/

		public function promoVisit()
		{
			if ($this->type == Student::USER_TYPE && $this->id > 112) {
				$Student = Student::findById($this->id_entity);
				$Student->promo_visit_count++;
				$Student->save("promo_visit_count");
			}
		}

		public function beforeSave()
		{
			if ($this->isNewRecord) {
				$this->password = self::password($this->password);
			}
            $this->is_dev = $this->is_dev; // % 2;
            $this->phone = cleanNumber($this->phone);
		}

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
		
		/*
		 * Режим просмотра
		 */
		public function enterViewMode($id, $type)
		{
			$_SESSION["view_mode_user_id"] 	= User::fromSession()->id;
			$_SESSION["view_mode_url"] 		= $_SERVER['HTTP_REFERER'];
			$_SESSION["user"] = User::find([
				"condition" => "type='$type' AND id_entity=$id"
			]);
		}
		
		/*
		 * Выйти из режима просмотра
		 */
		public function quitViewMode()
		{
			$_SESSION["user"] = User::findById($_SESSION["view_mode_user_id"]);
			unset($_SESSION["view_mode_user_id"]);
		}
		
		/*
		 * Находимся в режиме просмотра?
		 */
		public static function inViewMode()
		{
			return isset($_SESSION["view_mode_user_id"]);
		}
		
		/**
		 * Пользователь начал разговаривать (занят)
		 */
		public static function setCallBusy($id_user)
		{
			memcached()->set("users:{$id_user}:busy", true, minutes(100));
		}

		/**
		 * Пользователь закончил разговаривать
		 */		
		public static function setCallFree($id_user)
		{
			memcached()->delete("users:{$id_user}:busy");
		}
		
		/*
		 * Пользователь сейчас разговаривает
		 */
		public static function isCallBusy($id_user)
		{
			return (memcached()->get("users:{$id_user}:busy") ? true : false);
		}

		public static function getIds()
        {
            $user_ids = [];
            foreach (static::getCached() as $user) $user_ids[] = $user['id'];

            return $user_ids;
        }
    }

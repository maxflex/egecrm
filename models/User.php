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

        protected $_inline_data = ['rights'];

        public $log_except = [
            'last_action_time',
            'last_action_link',
            'token',
            'login_count',
            'updated_at'
        ];
		/*====================================== СИСТЕМНЫЕ ФУНКЦИИ ======================================*/

		public function __construct($array = [], $flag = null)
        {
            parent::__construct($array);

			if ($flag === null) {
                $this->salary = $this->salary ? $this->salary : '';
				$this->has_photo_original = $this->hasPhotoOriginal();
	            $this->photo_original_size = $this->photoOriginalSize();
	            $this->photo_cropped_size = $this->photoCroppedSize();
	            $this->photo_url = $this->photoUrl();

	            // цвет черный, если пользователя забанили
	            if ($this->allowed(Shared\Rights::EC_BANNED)) {
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

				// создать отложенную задачу на логаут
				Job::dispatch(
					LogoutJob::class,
					['session_id' => session_id()],
					User::fromSession()->type == User::USER_TYPE ? 40 : 15
				);
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
				    'condition' => "type = 'USER' "
                ]);

				foreach ($Users as $User) {
					$return[] = $User->dbData();
				}

				return $return;
			} else {
				$Users = memcached()->get("Users");

				if (! $Users) {
					$Users = self::updateCache();
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
				"condition"=>"type = '".User::USER_TYPE."' "
			]);

			foreach ($Users as $User) {
				$return[] = $User->dbData();
			}

			$Users = $return;
			memcached()->set("Users", $Users, 2 * 24 * 3600); // кеш на 2 дня
            return $Users;
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
		}

		/*
		 * Проверяем, залогинен ли пользователь
		 */
		public static function loggedIn()
		{
			return isset($_SESSION["user"]) // пользователь залогинен
                && ! User::isBlocked()      // и не заблокирован
                && User::worldwideAccess() // и можно входить
                && User::notChanged();      // и данные по пользователю не изменились
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

		/*====================================== ФУНКЦИИ КЛАССА ======================================*/

		public function beforeSave()
		{
			if ($this->isNewRecord) {
				$this->password = self::password($this->password);
			}
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

        public static function getJson()
        {
            return toJson(User::fromSession()->dbData());
        }

        public static function isAdmin()
        {
            return User::fromSession()->type == self::USER_TYPE;
        }

        public static function isBlocked()
        {
            return dbConnection()->query('
                SELECT 1 FROM users
                WHERE id=' . User::fromSession()->id . ' AND FIND_IN_SET(' . Shared\Rights::EC_BANNED . ', rights)
            ')->num_rows;
        }

        /**
         * Данные по пользователю не изменились
         * если поменяли в настройках хоть что-то, сразу выкидывает, чтобы перезайти
         */
        public static function notChanged()
        {
            return User::fromSession()->updated_at == dbConnection()->query('SELECT updated_at FROM users WHERE id=' . User::fromSession()->id)->fetch_object()->updated_at;
        }

        /**
         * Логин из офиса
         */
        public static function fromOffice()
        {
            if (LOCAL_DEVELOPMENT) {
                return true;
            }
            $current_ip = @$_SERVER['HTTP_X_REAL_IP'];
            foreach(['213.184.130.', '77.37.220.250'] as $ip) {
                if (strpos($current_ip, $ip) === 0) {
                    return true;
                }
            }
            return false;
        }

        /**
         * Вход из офиса или включена настройка «доступ отовсюду»
         */
        public static function worldwideAccess()
        {
            if (in_array(User::fromSession()->type, [Teacher::USER_TYPE, Student::USER_TYPE]) || User::fromOffice()) {
                return true;
            }

            // WORLDWIDE_ACCESS check
            return dbConnection()->query('
                SELECT 1 FROM users
                WHERE id=' . User::fromSession()->id . ' AND FIND_IN_SET(' . Shared\Rights::WORLDWIDE_ACCESS . ', rights)
            ')->num_rows > 0;
        }

        public function allowed($right)
        {
            return in_array($right, $this->rights);
        }
    }

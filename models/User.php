<?php

	class User extends Model
	{
		const SALT 					= "32dg9823dldfg2o001-2134>?erj&*(&(*^";	// Для генерации кук

		const USER_TYPE = "USER";

		const LAST_REAL_USER_ID = 112;
		const ONLINE_TIME_MINUTES = 15;

        const UPLOAD_DIR = 'img/users/';
        const NO_PHOTO   = 'no-profile-img.gif';

		const ADMIN_SESSION_DURATION = 40;
		const OTHER_SESSION_DURATION = 15;

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
			$this->last_action_time = time();

			// если не ajax-действие, записываем ссылку последнего действия
			if (strpos(strtolower($_GET['action']), "ajax") !== 0) {
				$this->last_action_link = $_SERVER['REQUEST_URI'];
				$this->save('last_action_link');
			}
			$this->save('last_action_time');

			// не логаутить меня
			if ($this->id != 69) {
				Job::dispatch(
					LogoutNotifyJob::class,
					['user_id' => $this->id],
					$this->type == User::USER_TYPE ? (self::ADMIN_SESSION_DURATION - 1) : (self::OTHER_SESSION_DURATION - 1)
				);

				// создать отложенную задачу на логаут
				Job::dispatch(
					LogoutJob::class,
					['session_id' => session_id()],
					$this->type == User::USER_TYPE ? self::ADMIN_SESSION_DURATION : self::OTHER_SESSION_DURATION
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
			return isset($_SESSION["user"]) && $_SESSION["user"] // пользователь залогинен
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

		public static function byType($id_entity, $type, $func = 'find')
		{
			return self::{$func}([
				'condition' => "id_entity={$id_entity} AND type='{$type}'"
			]);
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
			$_SESSION["user"] = User::byType($id, $type);
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
            foreach(['213.184.130.', '85.30.249.251', '178.140.53.201'] as $ip) {
                if (strpos($current_ip, $ip) === 0) {
                    return true;
                }
            }
            return false;
        }

		/**
	     * Из Мальдив (временно)
	     */
	    public static function fromMaldives()
	    {
	        $ips = '27.114.128.0	27.114.191.255
	            43.226.220.0	43.226.223.255
	            43.231.28.0	43.231.31.255
	            45.42.136.0	45.42.136.255
	            46.244.29.144	46.244.29.159
	            57.92.192.0	57.92.207.255
	            69.94.80.0	69.94.95.255
	            103.31.84.0	103.31.87.255
	            103.50.104.0	103.50.107.255
	            103.67.26.0	103.67.26.255
	            103.71.57.0	103.71.57.255
	            103.76.2.0	103.76.2.255
	            103.84.132.0	103.84.132.255
	            103.84.134.0	103.84.134.255
	            103.87.188.0	103.87.188.255
	            103.103.66.0	103.103.66.255
	            103.110.40.0	103.110.40.255
	            103.110.109.0	103.110.111.255
	            103.197.164.0	103.197.167.255
	            115.84.128.0	115.84.159.255
	            123.176.0.0	123.176.31.255
	            124.195.192.0	124.195.223.255
	            202.1.192.0	202.1.207.255
	            202.21.176.0	202.21.191.255
	            202.153.80.0	202.153.87.255
	            202.174.131.88	202.174.131.95
	            202.174.131.128	202.174.131.135
	            202.174.131.144	202.174.131.151
	            202.174.131.176	202.174.131.215
	            202.174.131.224	202.174.131.231
	            202.174.132.208	202.174.132.223
	            202.174.132.240	202.174.132.247
	            202.174.133.240	202.174.133.255
	            203.82.2.0	203.82.3.255
	            203.104.24.0	203.104.31.255
	            216.183.208.0	216.183.223.255
	            220.158.220.0	220.158.223.255';

	        $current_ip = ip2long($_SERVER['HTTP_X_REAL_IP']);

	        foreach(explode("\n", $ips) as $line) {
	            list($ip_start, $ip_end) = explode("\t", $line);
	            $ip_start = ip2long(trim($ip_start));
	            $ip_end = ip2long(trim($ip_end));
	            if ($current_ip >= $ip_start && $current_ip <= $ip_end) {
	                return true;
	            }
	        }
	        return false;
	    }

		public static function id()
		{
			return User::fromSession()->type == User::USER_TYPE ? User::fromSession()->id : User::fromSession()->id_entity;
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

				public static function getByEmail($email, $func = 'find')
				{
					return User::{$func}([
						'condition' => "email='$email'"
					]);
				}

				public function resetPassword()
				{
					$code = md5(mt_rand(1, 99999)) . base64_encode($this->id);
					$client = new Predis\Client();
	        		$client->set("egecrm:reset-password:{$this->id}", $code, 'EX', 5 * 60);
					$link = "https://lk.ege-centr.ru/login/reset?code={$code}";
					$set_or_reset = $this->password ? ['Восстановление', 'восстановления'] : ['Установка', 'установки'];
					Email::send($this->email, $set_or_reset[0] . ' пароля', "
						Ссылка для {$set_or_reset[1]} пароля:
						<a href='{$link}'>{$link}</a>
					");
				}

				/**
				 * Получить ID пользователя по коду восстановления
				 */
				public static function getIdFromCode($code)
				{
					return base64_decode(substr($code, 32, 999));
				}
    }

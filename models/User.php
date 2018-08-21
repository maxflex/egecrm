<?php

	class User extends Model
	{
		const SALT 					= "32dg9823dldfg2o001-2134>?erj&*(&(*^";	// Для генерации кук
		const ADMIN_SESSION_DURATION = 40;
		const OTHER_SESSION_DURATION = 15;

		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/

		public static $mysql_table	= "users";

		/*====================================== СИСТЕМНЫЕ ФУНКЦИИ ======================================*/

		public function __construct($array = [], $flag = null)
        {
            parent::__construct($array);

			if (! $this->isNewRecord) {
				// Записываем поля из Entity
				$Entity = $this->getEntity();

				foreach($Entity->mysql_vars as $field) {
					if (! isset($this->{$field})) {
						$this->{$field} = $Entity->{$field};
					}
				}
			}
        }

		public function getEntity()
		{
			switch ($this->type) {
				case Admin::USER_TYPE:
					return Admin::findById($this->id_entity);
				case Representative::USER_TYPE:
					return Representative::findById($this->id_entity);
				case Teacher::USER_TYPE:
					return Teacher::findById($this->id_entity);
				case Student::USER_TYPE:
					return Student::findById($this->id_entity);
			}
		}


		/*====================================== СТАТИЧЕСКИЕ ФУНКЦИИ ======================================*/

		/**
		 * Выкидывать автоматически
		 */
		public function trackLogout()
		{
			// не логаутить меня
			if ($this->id != 69) {
				Job::dispatch(
					LogoutNotifyJob::class,
					['user_id' => $this->id],
					$this->type == Admin::USER_TYPE ? (self::ADMIN_SESSION_DURATION - 1) : (self::OTHER_SESSION_DURATION - 1)
				);

				// создать отложенную задачу на логаут
				Job::dispatch(
					LogoutJob::class,
					['session_id' => session_id()],
					$this->type == Admin::USER_TYPE ? self::ADMIN_SESSION_DURATION : self::OTHER_SESSION_DURATION
				);
			}
		}

		public static function isAdmin($return_string = false)
		{
			$return = User::fromSession()->type == Admin::USER_TYPE;

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

		public static function isRepresentative($return_string = false)
		{
			$return = User::fromSession()->type == Representative::USER_TYPE;

			if ($return_string) {
				return $return ? 'true' : 'false';
			} else {
				return $return;
			}
		}

		// Получить пользователей из кеша
		public static function getCached($with_system = false)
		{
			if (LOCAL_DEVELOPMENT) {
                $return = [];

			    $Users = self::findAll([
				    'condition' => "type = 'ADMIN' "
                ]);

				foreach ($Users as $User) {
					$return[] = $User;
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
				"condition"=>"type = '".Admin::USER_TYPE."' "
			]);

			foreach ($Users as $User) {
				$return[] = $User;
			}

			$Users = $return;
			memcached()->set("Users", $Users, 2 * 24 * 3600); // кеш на 2 дня
            return $Users;
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
		 * Проверяем, залогинен ли пользователь
		 */
		public static function loggedIn()
		{
			return isset($_SESSION["user"]) && $_SESSION["user"] 	// пользователь залогинен
                && (User::isAdmin() ? !User::fromSession()->isBanned() : true)  // и не заблокирован (разрешаем заблокированным пользователям для режима просмотра)
                && User::fromSession()->allowedToLogin() 			// и можно входить
				&& User::notChanged()      							// и данные по пользователю не изменились
				&& SessionService::exists();						// сессия существует и не истекла
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

		/*====================================== ФУНКЦИИ КЛАССА ======================================*/

		/*
		 * Вход/запись пользователя в сессию
		 * $start_session – стартовать ли сессию?
		 */
		public function toSession($start_session = false)
		{
			// Если стартовать сессию
			if ($start_session) {
				session_set_cookie_params(3600 * 24,"/"); // PHP сессия на сутки
				session_start();
			}

			$_SESSION["user"] = $this;
		}

		/*
		 * Режим просмотра
		 */
		public function enterViewMode($id, $type)
		{
			$_SESSION["view_mode_user_id"] 	= User::id();
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
            return toJson(User::fromSession());
        }

		public static function is($who = [])
		{
			return in_array(User::fromSession()->type, $who);
		}

        public function isBanned()
        {
			switch ($this->type) {
				case Admin::USER_TYPE:
					return $this->allowed(Shared\Rights::EC_BANNED);
				case Teacher::USER_TYPE:
					return Teacher::getLight($this->id_entity, ['in_egecentr'])->in_egecentr != 2;
				default:
					return Student::isBanned($this->id_entity);
			}
        }

        /**
         * Данные по пользователю не изменились
         * если поменяли в настройках хоть что-то, сразу выкидывает, чтобы перезайти
         */
        public static function notChanged()
        {
			switch (User::fromSession()->type) {
				case Admin::USER_TYPE:
					return User::fromSession()->updated_at == dbConnection()->query('SELECT updated_at FROM admins WHERE id=' . User::id())->fetch_object()->updated_at;
				default:
					return true;
			}
        }

		/**
		 * Можно ли логиниться с этого IP?
		 */
		public function allowedToLogin()
		{
			if (LOCAL_DEVELOPMENT || in_array($this->type, [
				Student::USER_TYPE,
				Teacher::USER_TYPE,
				Representative::USER_TYPE
			])) {
                return new AdminIp([
					'confirm_by_sms' => false
				]);
            }
			return Admin::allowedToLogin($this->id_entity);
		}

		public static function id()
		{
			return User::fromSession()->id_entity;
		}

		public static function getByEmail($email, $func = 'find')
		{
			return User::{$func}([
				'condition' => "email='$email'"
			]);
		}

		public function resetPassword()
		{
			$code = mt_rand(100000, 999999);
			$client = new Predis\Client();
    		$client->set("egecrm:reset-password:{$this->id}", $code, 'EX', 5 * 60);
			$set_or_reset = $this->password ? ['Восстановление', 'восстановления'] : ['Установка', 'установки'];
			Email::send($this->email, $set_or_reset[0] . ' пароля', "
				Код для {$set_or_reset[1]} пароля: <b>{$code}</b> (активен 5 минут)
			");
		}

		public function allowed($right)
        {
            return in_array($right, $this->rights);
        }
    }

<?php
	class Admin extends Model
	{
		use HasPhoto;

		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/

		public static $mysql_table	= "admins";
		protected $_inline_data = ['rights'];

		const USER_TYPE = "ADMIN";
		const UPLOAD_DIR = 'img/users/';
		const NO_PHOTO   = 'no-profile-img.gif';

		/*====================================== СИСТЕМНЫЕ ФУНКЦИИ ======================================*/

		public function __construct($array)
		{
			parent::__construct($array);

			$this->salary = $this->salary ? $this->salary : '';

			$this->bindPhoto();

			// цвет черный, если пользователя забанили
			if ($this->allowed(Shared\Rights::EC_BANNED)) {
				$this->color = 'black';
			}
		}

		/**
		 * Можно ли логиниться с этого IP?
		 */
		public static function allowedToLogin($id_admin)
		{
			$current_ip = ip2long($_SERVER['HTTP_X_REAL_IP']);

			$admin_ips = AdminIp::getAll($id_admin);

			foreach($admin_ips as $admin_ip) {
	            $ip_from = ip2long(trim($admin_ip->ip_from));
	            $ip_to = ip2long(trim($admin_ip->ip_to ?: $admin_ip->ip_from));
	            if ($current_ip >= $ip_from && $current_ip <= $ip_to) {
	                return $admin_ip;
	            }
			}

			return false;
		}

		public static function getLogin($id_user)
		{
			$result = dbConnection()->query("SELECT login FROM admins WHERE id={$id_user}");

			if ($result->num_rows) {
				return $result->fetch_object()->login;
			} else {
				return 'system';
			}
		 }

		 public function beforeSave()
		 {
			 $this->phone = cleanNumber($this->phone);
		 }

		 public function allowed($right)
         {
             return in_array($right, $this->rights);
         }

		 public static function edit($User)
		 {
			 unset($User['photo_extension']);
			 unset($User['has_photo_cropped']);
             $User['updated_at'] = now();

			 // если убрали все права
			 if (! isset($User['rights'])) {
				 $User['rights'] = [];
			 }

			 if (isset($User['ips'])) {
				 AdminIp::saveData($User['id_entity'], $User['ips']);
			 }
			 Admin::updateById($User['id_entity'], $User);
			 User::updateById($User['id'], $User);
		 }
	}

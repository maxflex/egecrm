<?php
	class Background extends Model
	{
		public static $mysql_table = "backgrounds";

		public function __construct($array)
		{
			parent::__construct($array);

			if ($this->user_id) {
				$this->user = (object)[
					'login' => Admin::getLogin($this->user_id)
				];
			}

			$this->image_url = EGEREP_URL . 'img/wallpaper/' . $this->image;
		}

		public static function get()
		{
			if (true) {
				$wallpaper = Background::find([
					"condition" => "status=1 AND date=CURDATE()"
				]);
	            if (! $wallpaper) {
					$wallpaper = Background::find([
						"condition" => "status=1 AND date<CURDATE()",
						"order" => "date desc"
					]);
	            }
			}

			// отображаем фон только в случае, если последний залогиненный
			// пользователь был ADMIN
			return ($_COOKIE['login_user_type'] == Admin::USER_TYPE && $wallpaper) ? $wallpaper : (object)[
				'image_url' => 'img/background/blue.png'
			];
		}

		public static function dbConnection()
		{
			return dbEgerep();
		}
    }

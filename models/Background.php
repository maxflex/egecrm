<?php
	class Background extends Model
	{
		public static $mysql_table = "backgrounds";

		public function __construct($array)
		{
			parent::__construct($array);

			if ($this->user_id) {
				$this->user = (object)[
					'login' => User::getLogin($this->user_id)
				];
			}

			$this->image_url = EGEREP_URL . 'img/wallpaper/' . $this->image;
		}

		public static function get()
		{
			// эта страница логин-пароль в системе ECCRM должна работать только в случае
			// если это наш IP офиса и разрешение как на iMac или MacBook Pro 15 inch.
			// Для остальных синий фон
			if (User::fromOffice()) {
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

			return $wallpaper ? $wallpaper : (object)[
				'image_url' => 'img/background/blue.png'
			];
		}

		public static function dbConnection()
		{
			return dbEgerep();
		}
    }

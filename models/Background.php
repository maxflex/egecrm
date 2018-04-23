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
			$wallpaper = Background::find([
				"condition" => "status=1 AND date=CURDATE()"
			]);
            if (! $wallpaper) {
				$wallpaper = Background::find([
					"condition" => "status=1 AND date<CURDATE()",
					"order" => "id asc"
				]);
                // если не найден, делаем dummy-объект с зеленым фоном
                if (! $wallpaper) {
                    $wallpaper = (object)[
                        'image_url' => 'img/background/green.png'
                    ];
                }
            }

			return $wallpaper;
		}

		public static function dbConnection()
		{
			return dbEgerep();
		}
    }

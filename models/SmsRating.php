<?php
	class SmsRating extends Model
	{
		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/

		public static $mysql_table = "sms_rating";

		public function __construct($array)
		{
			parent::__construct($array);

			if (! $this->isNewRecord) {
				$this->caller = Call::determineEgecrm($this->number);
				$this->user_login = User::getLogin($this->user_id);
			}
		}

		public static function dbConnection()
		{
			return dbEgerep();
		}
    }

<?php
	class Vacation extends Model
	{
		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/

		public static $mysql_table	= "vacations";


		/*====================================== СИСТЕМНЫЕ ФУНКЦИИ ======================================*/

		public static function getDates($year)
		{
			$data = self::getByYear($year);

			$dates = [];

			foreach($data as $d) {
				$dates[] = $d->date;
			}

			return $dates;
		}

		public static function getByYear($year)
		{
			return self::findAll([
				'condition' => "year={$year}"
			]);
		}
	}

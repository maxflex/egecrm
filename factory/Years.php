<?php
	/**
	 * Годы
	 */
	class Years extends Factory {
		# Все
		static $all  = [2015, 2016];

		public static function json()
		{
			return json_encode(static::$all);
		}

		public static function getCurrent()
		{
			return $_GET['year'] ? $_GET['year'] : ($_COOKIE['current_year'] ? $_COOKIE['current_year'] : static::$all[0]);
		}

		public static function getAcademic()
		{
			$year = date("Y", time());
			$day_month = date("m-d", time());

			if ($day_month >= '01-01' && $day_month <= '07-15') {
				$year--;
			}
			return $year;
		}
	}

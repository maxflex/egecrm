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
	}
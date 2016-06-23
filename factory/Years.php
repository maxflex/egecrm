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
	}
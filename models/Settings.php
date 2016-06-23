<?php
	class Settings extends Model
	{
		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/

// 		public static $mysql_table	= "teachers";
		public static $mysql_table = "settings";
		
		private function _findByKey($key)
		{
			return static::find([
				'condition' => "name = '{$key}'"
			]);
		}
		
		public static function get($key)
		{
			return static::_findByKey($key)->value;
		}
		
		public static function set($key, $value)
		{
			$Settings = static::_findByKey($key);
			$Settings->value = $value;
			$Settings->save('value');
			return $value;
		}
	}

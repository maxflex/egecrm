<?php
	/**
	 * Классы
	 */
	class GroupLevels extends Factory {
		
		# Список
		const LOW 		= 1;
		const MEDIUM 	= 2;
		const HIGH 		= 3;
		const SPECIAL 	= 4;
		const EXTERNAL 	= 5;

		# Все
		static $all  = [
			self::LOW 		=> "низкий",
			self::MEDIUM 	=> "средний",
			self::HIGH 		=> "высокий",
			self::SPECIAL 	=> "спецкурс",
			self::EXTERNAL 	=> "экстернат",
		];
		
		# 
		static $short  = [
			self::LOW 		=> "Н",
			self::MEDIUM 	=> "С",
			self::HIGH 		=> "В",
			self::SPECIAL 	=> "Сп",
			self::EXTERNAL 	=> "Э",
		];
		
		# Заголовок
		static $title = "уровень";	
		
		public static function json()
		{
			return json_encode(static::$short);
		}
	}
<?php
	/**
	 * Классы
	 */
	class GroupLevels extends Factory {
		
		# Список
		const LOW 		= 1;
		const MEDIUM 	= 2;
		const HIGH 		= 3;
		
		# Все
		static $all  = [
			self::LOW 		=> "низкий",
			self::MEDIUM 	=> "средний",
			self::HIGH 		=> "высокий",
		];
		
		# Заголовок
		static $title = "уровень";	
		
	}
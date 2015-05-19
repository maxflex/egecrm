<?php
	/**
	 * Типы уведомлений
	 */
	class NotificationTypes extends Factory {
		
		# Список 
		const TYPE1		= 1;
		const TYPE2		= 2;
		
		# Все 
		static $all = [
			self::TYPE1	=> "тип 1",
			self::TYPE2	=> "тип 2",
		];
		
	}
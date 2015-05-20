<?php
	/**
	 * Типы уведомлений
	 */
	class NotificationTypes extends Factory {
		
		# Список 
		const CALL		= 1;
		const MEETING	= 2;
		
		# Все 
		static $all = [
			self::CALL		=> "звонок",
			self::MEETING	=> "встреча",
		];
		
		# Заголовок
		static $title = "тип напоминания";
		
	}
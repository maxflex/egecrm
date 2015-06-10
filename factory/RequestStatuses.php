<?php
	/**
	 * Статусы заявки
	 */
	class RequestStatuses extends Factory {
		
		# Список 
		const NEWR		= 0;
		const UNFINISHED= 1;
		const FINISHED	= 2;
		const AWAITING	= 3;
		const DENY		= 4;
		const SPAM		= 5;
		
		# Все 
		static $all = [
			self::NEWR		=> "новые заявки",
			self::UNFINISHED=> "невыполненные",
			self::FINISHED	=> "выполненные",
			self::AWAITING	=> "в ожидании",
			self::DENY		=> "отказы",
			self::SPAM		=> "спам",
		];
		
		# Заголовок
		static $title = "статус заявки";
		
		# Удаляем
		static $deleted = [
			self::DENY
		];
	}
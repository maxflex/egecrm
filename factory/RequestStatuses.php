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
		const SPAM		= 4;
		const DENY		= 5;

		
		# Все 
		static $all = [
			self::NEWR		=> "новые заявки",
			self::UNFINISHED=> "невыполненные",
			self::FINISHED	=> "выполненные",
			self::AWAITING	=> "в ожидании",
			self::SPAM		=> "спам",
			self::DENY		=> "отказы",
		];
		
		# Заголовок
		static $title = "статус заявки";
		
		# Удаляем
		static $deleted = [
			self::DENY
		];
	}
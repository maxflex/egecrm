<?php
	/**
	 * Классы
	 */
	class Grades extends Factory {
		
		# Список
		const FIRST 	= 1;
		const SECOND 	= 2;	
		const THIRD 	= 3;
		const FOURTH	= 4;
		const FIFTH		= 5;
		const SIXTH		= 6;
		const SEVENTH	= 7;
		const EIGHTH	= 8;
		const NINETH	= 9;
		const TENTH		= 10;
		const ELEVENTH	= 11;
		
		# Класс (для формирования надписи)
		const GRADE 	= "класс";
		
		# Все
		static $all  = [
			self::FIRST 	=> self::FIRST	." ".self::GRADE,
			self::SECOND 	=> self::SECOND	." ".self::GRADE,
			self::THIRD 	=> self::THIRD	." ".self::GRADE,
			self::FOURTH	=> self::FOURTH	." ".self::GRADE,
			self::FIFTH		=> self::FIFTH	." ".self::GRADE,
			self::SIXTH		=> self::SIXTH	." ".self::GRADE,
			self::SEVENTH	=> self::SEVENTH." ".self::GRADE,
			self::EIGHTH	=> self::EIGHTH	." ".self::GRADE,
			self::NINETH	=> self::NINETH	." ".self::GRADE,
			self::TENTH		=> self::TENTH	." ".self::GRADE,
			self::ELEVENTH	=> self::ELEVENTH." ".self::GRADE,
		];
		
		# Заголовок
		static $title = "класс";	
	}
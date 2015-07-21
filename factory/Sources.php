<?php
	/**
	 * Данные по предметам.
	 */
	class Sources extends Factory {
		
		# Список предметов
		const INTERNET 		= 1;
		const CALL			= 2;
		const RECOMMENDATION= 3;
		const NO_CALL		= 4;
		const OTHER			= 5;
		
		# Все предметы
		static $all = [
			self::INTERNET 		=> "интернет",
			self::CALL			=> "звонок",
			self::RECOMMENDATION=> "по рекоммендации",
			self::NO_CALL		=> "без звонка",
			self::OTHER			=> "другое",
		];
		
		static $deleted = [
			self::INTERNET,		// интернет нельзя выбирать вручную
		];
		
		# Заголовок
		static $title = "источник";
		
		
		
		public static function buildSelector($selected = false, $name = false, $attrs = false) {
			// если выбран ИНТЕРНЕТ, то нельзя редактировать
			if ($selected == self::INTERNET) {
				$attrs["disabled"] = "disabled";
			}
			
			parent::buildSelector($selected, $name, $attrs);
		}
			
	}
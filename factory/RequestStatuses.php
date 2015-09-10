<?php
	/**
	 * Статусы заявки
	 */
	class RequestStatuses extends Factory {

		# Список
		const NEWR			= 0;
		const NBT 			= 9;
		const FINISHED		= 2;
		const AWAITING		= 3;
		const DENY			= 5;
		const SPAM			= 4;
		const DUPLICATE		= 7;
		const ALL 			= 8;


		# Все
		static $all = [
			self::NEWR			=> "невыполненные",
			self::NBT 			=> "нбт",
			self::FINISHED		=> "выполненные",
			self::AWAITING		=> "ожидаются",
			self::DENY			=> "отказ",
			self::SPAM			=> "спам",
			self::DUPLICATE		=> "дубль",
			self::ALL			=> "все",
		];

		# Заголовок
		static $title = "статус заявки";

		# Удаляем
		static $deleted = [
			self::ALL,
		];

		# Не отображать в списке
		static $hidden = [
		//	self::SPAM,
		//	self::DUPLICATE,
		];

		/**
		 * Получить с названиями констат фактории, c учетом удалений
		 *
		 */
		public static function get()
		{
			$A = new ReflectionClass(get_called_class());

			// получаем названия констант
			$constants = $A->getConstants();
			
			foreach ($constants as $name => $value) {
				// не показывать удаленные
				if (!in_array($value, static::$hidden)) {
					$return[] = [
						"id" 		=> $value,
						"constant"	=> $name,
						"name"		=> static::$all[$value],
					];
				}
			}

			return $return;
		}
	}

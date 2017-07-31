<?php
	/**
	 * Классы
	 */
	class PaymentTypes extends Factory {

		# Список
		const PAYMENT = 1;
		const RETURNN = 2;

		# Все
		static $all  = [
			self::PAYMENT 	=> "платеж",
			self::RETURNN 	=> "возврат",
		];

		# Заголовок
		static $title = "тип";


        public static function categories()
        {
            return json_encode([
                '1' => 'обучение',
                '2' => 'профориентация',
                '3' => 'пробный ЕГЭ',
            ]);
        }
	}
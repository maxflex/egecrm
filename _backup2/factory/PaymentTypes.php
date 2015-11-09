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
	}
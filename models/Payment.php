<?php
	class Payment extends Model
	{
		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/
		
		public static $mysql_table	= "payments";
		
		# Список статусов
		const PAID 		= 1;
		const NOT_PAID 	= 2;
		const RETURNS	= 3;
		
		
		# Все
		static $all  = [
			self::PAID		=> "оплачен",
			self::NOT_PAID	=> "не оплачен",
			self::RETURNS	=> "возврат",
		];
		
		# Заголовок
		static $title = "статус";
		
		/*====================================== СИСТЕМНЫЕ ФУНКЦИИ ======================================*/
		
		public function __construct($array) {
			parent::__construct($array);
			
			// Добавляем данные
			$this->user_login = User::findById($this->id_user)->login;
		}
		
		
		/*====================================== СТАТИЧЕСКИЕ ФУНКЦИИ ======================================*/
		
		/**
		 * Построить селектор из всех записей.
		 * $selcted - что выбрать по умолчанию
		 * $name 	– имя селектора, по умолчанию имя класса
		 * $attrs	– остальные атрибуты
		 * 
		 */
		public static function buildSelector($selcted = false, $name = false, $attrs = false)
		{
			$class_name = strtolower(get_called_class());
			echo "<select class='form-control' id='".$class_name."-select' name='".($name ? $name : $class_name)."' ".Html::generateAttrs($attrs).">";
			if (static::$title) {
				echo "<option selected value=0>". static::$title ."</option>";
				echo "<option disabled>──────────────</option>";
			}
			foreach (static::$all as $id => $value) {
				echo "<option value='$id' ".($id == $selcted ? "selected" : "").">$value</option>";
			}
			echo "</select>";
		}
				
		/*====================================== ФУНКЦИИ КЛАССА ======================================*/

		/**
		 * Добавить платежи
		 * 
		 */
		public static function addData($payments_array, $id_student) 
		{	
			// Сохраняем данные
			foreach ($payments_array as $id => $one_payment) {
				// если у платежа есть ID, то обновляем его
				if ($one_payment["id"]) {
					$Payment = Payment::findById($one_payment["id"]);
					$Payment->update($one_payment);	
				} else {
					// иначе добавляем новый платеж
					$Payment = new self($one_payment);
					$Payment->id_student	= $id_student;
					$Payment->id_user		= User::fromSession()->id;
					$Payment->first_save_date = now();
					$Payment->save();
				}
			}
		}
			
	}
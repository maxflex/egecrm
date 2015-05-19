<?php
	class Contract  extends Model
	{
	
		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/

		public static $mysql_table	= "contracts";
		
		
		/*====================================== СИСТЕМНЫЕ ФУНКЦИИ ======================================*/
		
		public function __construct($array) 
		{
			parent::__construct($array);
		}
		
		
		/*====================================== СТАТИЧЕСКИЕ ФУНКЦИИ ======================================*/
		
				
		/*====================================== ФУНКЦИИ КЛАССА ======================================*/
		
		
		public function beforeSave()
		{
			// Если расторгаем договор
			// (если расторгнут)
			if ($this->cancelled) {
				// если изменили статус (а был НЕ расторгнут)
				if (!self::findById($this->id)->cancelled) {
					// сохраняем данные пользователя, который сделал расторжение договора
					$this->cancelled_by 	= User::fromSession()->id;
					$this->cancelled_date	= now();
				}
			}
		}
	}
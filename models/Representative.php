<?php
	class Representative extends Model
	{
	
		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/

		public static $mysql_table	= "representatives";
		
		/*====================================== СИСТЕМНЫЕ ФУНКЦИИ ======================================*/
		
		public function __construct($array) 
		{
			parent::__construct($array);
			
			// Включаем связи
			$this->Passport	= Passport::findById($this->id_passport);
		}
		
		/*====================================== СТАТИЧЕСКИЕ ФУНКЦИИ ======================================*/


				
		/*====================================== ФУНКЦИИ КЛАССА ======================================*/

		/**
		 * Добавить паспорт.
		 * 
		 * $save - сохранить новое поле?
		 */
		public function addPassport($Passport, $save = false)
		{
			$this->Passport 		= $Passport;
			$this->id_passport		= $Passport->id;
			
			if ($save) {
				$this->save("id_passport");
			}
		}
		
	}
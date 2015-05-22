<?php
	class Freetime  extends Model
	{
	
		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/

		public static $mysql_table	= "freetime";
		
		/*====================================== СИСТЕМНЫЕ ФУНКЦИИ ======================================*/
		
		
		
		/*====================================== СТАТИЧЕСКИЕ ФУНКЦИИ ======================================*/
		

		/**
		 * Добавить свободное время из angular-json-данных.
		 * 
		 */
		public static function addData($json_array, $id_student) 
		{
			// Сначала декодируем данные
			$freetime_data = json_decode($json_array, true);
			
			// Если никаких данных нет
			if (!count($freetime_data)) {
				return false;
			}
			
			// Удаляем предыдущие данные 
			// @todo: проверять есть ли данные. если их не существует – добавлять по отдельности
/*
			self::deleteAll([
				"condition"	=> "id_student=$id_student",
			]);
*/
			
			
			// Создаем интервалы свободного времени
			foreach ($freetime_data as $one_day_freetime_data) {
				$FreetimeData = new self($one_day_freetime_data);
				$FreetimeData->id_student = $id_student;
				$FreetimeData->save();
			}
		}
				
		/*====================================== ФУНКЦИИ КЛАССА ======================================*/

		
	}
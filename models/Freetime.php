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
			
			// Создаем интервалы свободного времени
			foreach ($freetime_data as $one_day_freetime_data) {
				// Если промежуток уже существует и его надо удалить
				if ($one_day_freetime_data["id"] && $one_day_freetime_data["deleted"]) {
					// создаем массив из ID промежутков, которые нужно удалить
					$ids_to_delete[] = $one_day_freetime_data["id"];	
				} else {
					// иначе если промежуток новый (и он не удален)
					if (!$one_day_freetime_data["deleted"]) {
						// добавляем промежуток свободного времени
						$FreetimeData = new self($one_day_freetime_data);
						$FreetimeData->id_student = $id_student;
						$FreetimeData->save();	
					}
				}
			}
			
			// Если есть что удалять
			if ($ids_to_delete) {
				// Удаляем
				self::deleteAll([
					"condition"	=> "id IN (". implode(",", $ids_to_delete) .")",
				]);
			}
		}
				
		/*====================================== ФУНКЦИИ КЛАССА ======================================*/

		
	}
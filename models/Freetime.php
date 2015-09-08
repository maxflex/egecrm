<?php
	class Freetime extends Model
	{
	
		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/

		public static $mysql_table	= "freetime";
		
		/*====================================== СИСТЕМНЫЕ ФУНКЦИИ ======================================*/
		
		
		
		/*====================================== СТАТИЧЕСКИЕ ФУНКЦИИ ======================================*/
		

		/**
		 * Добавить свободное время
		 * 
		 */
		public static function addData($data, $id_student) 
		{
			self::deleteAll([
				"condition" => "id_student=$id_student"
			]);
			
			foreach ($data as $id_branch => $branch_data) {
				foreach ($branch_data as $day => $day_data) {
					foreach ($day_data as $time) {
						if (empty(trim($time))) {
							continue;
						}
						$Freetime = new self([
							"id_student"	=> $id_student,
							"id_branch"		=> $id_branch,
							"day"			=> $day,
							"time"			=> $time,
						]);
						
						$Freetime->save();
					}
				}
			}
		}
		
		public static function getIndexByTime($time) {
			switch ($time) {
/*
				case "11:00": {
					return 0;
				}
*/
				case "13:30": {
					return 1;
				}
				case "16:00":
				case "16:15": {
					return 2;
				}
				case "18:30":
				case "18:40": {
					return 3;
				}
				default: {
					return 0;
				}
			}
		}
				
		/*====================================== ФУНКЦИИ КЛАССА ======================================*/

		
	}
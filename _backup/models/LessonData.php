<?php
	class LessonData extends Model
	{
		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/

		public static $mysql_table	= "lesson_data";
		
		public static $statuses = ["не указано", "был", "не был"];
		
		public static function addData($id_group, $date, $data)
		{
			self::deleteAll([
				"condition" => "date='$date' AND id_group=$id_group"	
			]);
			
			foreach ($data as $id_student => $student_data) {
				self::add([
					"presence"		=> $student_data['presence'],
					"late"			=> $student_data['late'],
					"comment" 		=> $student_data['comment'],
					"id_student"	=> $id_student,
					"id_group"		=> $id_group,
					"date"			=> $date
				]);
			}
		}
	}
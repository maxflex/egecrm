<?php
	class TeacherReview extends Model
	{
		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/

		public static $mysql_table	= "teacher_reviews";
		
		
		/*====================================== СИСТЕМНЫЕ ФУНКЦИИ ======================================*/
	
		
		/*====================================== СТАТИЧЕСКИЕ ФУНКЦИИ ======================================*/
		
		public static function addData($data)
		{
			self::deleteAll([
				"condition" => "id_student=" . User::fromSession()->id_entity,
			]);
			
			foreach ($data as $id_teacher => $id_subject_data) {
				foreach ($id_subject_data as $id_subject => $rating) {
					self::add([
						"id_teacher"	=> $id_teacher,
						"id_subject"	=> $id_subject,
						"rating"		=> $rating['rating'],
						"comment" 		=> $rating['comment'],
						"id_student"	=> User::fromSession()->id_entity,
						"date"			=> now(),
					]);
				}
			}
		}
		
		public static function getInfo()
		{
			$RatingInfo = self::findAll([
				"condition" => "id_student=" . User::fromSession()->id_entity,
			]);
			
			foreach ($RatingInfo as $Rating) {
				$return[$Rating->id_teacher][$Rating->id_subject] = $Rating; 
			}
			
			return $return;
		}

	}
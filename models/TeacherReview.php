<?php
	class TeacherReview extends Model
	{
		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/

		public static $mysql_table	= "teacher_reviews";


		/*====================================== СИСТЕМНЫЕ ФУНКЦИИ ======================================*/


		/*====================================== СТАТИЧЕСКИЕ ФУНКЦИИ ======================================*/

		public static function addData($data, $id_student)
		{
			self::deleteAll([
				"condition" => "id_student=" . $id_student,
			]);

			foreach ($data as $id_teacher => $id_subject_data) {
				foreach ($id_subject_data as $id_subject => $rating) {
					self::add([
						"id_teacher"	=> $id_teacher,
						"id_subject"	=> $id_subject,
						"rating"		=> $rating['rating'],
						"admin_rating"	=> $rating['admin_rating'],
						"comment" 		=> $rating['comment'],
						"admin_comment" => $rating['admin_comment'],
						"published" 	=> $rating['published'],
						"id_student"	=> $id_student,
						"date"			=> now(),
					]);
				}
			}
		}

		public static function getInfo($id_student)
		{
			$RatingInfo = self::findAll([
				"condition" => "id_student=" . $id_student,
			]);

			foreach ($RatingInfo as $Rating) {
				$return[$Rating->id_teacher][$Rating->id_subject] = $Rating;
			}

			return $return;
		}


		/**
		 * Получить оценку учителя
		 */
		public static function getStatus($id_student, $id_teacher, $id_subject)
		{
			$StudentTeacherLike = static::find([
				"condition" => "id_teacher={$id_teacher} AND id_student={$id_student} AND id_subject={$id_subject}"
			]);

			if ($StudentTeacherLike) {
				return $StudentTeacherLike->admin_rating;
			} else {
				return 0;
			}
		}
	}

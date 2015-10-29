<?php
	class StudentTeacherLike extends Model
	{
		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/

		public static $mysql_table	= "student_teacher_likes";
		
		
		public static function updateData($data)
		{
			$StudentTeacherLike = self::find([
				"condition" => "id_teacher={$data['id_teacher']} AND id_student={$data['id_student']}"
			]);
			
			# если мнение о преподе уже существует
			if ($StudentTeacherLike) {
				# если мнение изменилось
				if ($StudentTeacherLike->id_status != $data['id_status']) {
					$StudentTeacherLike->id_status = $data['id_status'];
					$StudentTeacherLike->save("id_status");
				}	
			} else {
				self::add($data);
			}
		}
		
		public static function getStatus($id_student, $id_teacher)
		{
			$StudentTeacherLike = self::find([
				"condition" => "id_teacher={$id_teacher} AND id_student={$id_student}"
			]);
			
			if ($StudentTeacherLike) {
				return $StudentTeacherLike;
			} else {
				return 0;
			}
		}
	}
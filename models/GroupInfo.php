<?php
	class GroupTeacherLike extends Model
	{
		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/

		public static $mysql_table	= "group_teacher_likes";


		public static function addData($data)
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
				return $StudentTeacherLike->id_status;
			} else {
				return 0;
			}
		}
	}

	class GroupSms extends Model
	{
		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/

		public static $mysql_table	= "group_sms";


		public static function notify($data)
		{
			self::add($data);
		}

		public static function getStatus($id_student, $id_branch, $id_subject, $first_schedule, $cabinet)
		{
			// preType([$id_student, $id_branch, $id_subject, $first_schedule, $cabinet]);
			return self::count([
				"condition" => "id_student={$id_student}
								 AND id_branch={$id_branch}
								 AND id_subject={$id_subject}
								 AND first_schedule='{$first_schedule}' 
								 AND cabinet={$cabinet}"
			]);
		}
	}

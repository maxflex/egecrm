<?php
	class VisitJournal extends Model
	{
		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/

		public static $mysql_table	= "visit_journal";
		
		public static function addData($id_group, $date, $data)
		{
			$Schedule = GroupSchedule::find([
				"condition" => "id_group=$id_group AND date='$date'"
			]);
			
			$Group = Group::findById($id_group);
			
			foreach ($Group->students as $id_student)
			{
				$Student = Student::findById($id_student);
				
				self::add([
					"id_entity" 			=> $id_student,
					"type_entity"			=> Student::USER_TYPE,
					"id_group"				=> $id_group,
					"id_subject"			=> $Group->id_subject,
					"id_branch"				=> $Group->id_branch,
					"cabinet"				=> $Group->cabinet,
					"is_special"			=> $Group->is_special,
					"lesson_date"			=> $date,
					"lesson_time"			=> $Schedule->time,
					"date"					=> now(),
					"presence"				=> $data[$id_student]['presence'],
					"late"					=> $data[$id_student]['late'],
					"comment"				=> $data[$id_student]['comment'],
					"id_user_saved"			=> User::fromSession()->id,
				]);
			}
			
			self::add([
				"id_entity" 			=> $Group->id_teacher,
				"type_entity"			=> Teacher::USER_TYPE,
				"id_group"				=> $id_group,
				"id_subject"			=> $Group->id_subject,
				"id_branch"				=> $Group->id_branch,
				"cabinet"				=> $Group->cabinet,
				"is_special"			=> $Group->is_special,
				"lesson_date"			=> $date,
				"lesson_time"			=> $Schedule->time,
				"date"					=> now(),
				"teacher_price"			=> $Group->teacher_price,
				"id_user_saved"			=> User::fromSession()->id,
			]);
		}
		
		public static function lessonPresent($id_group)
		{
			return self::find([
				"condition" => "id_group=$id_group"
			]);
		}
	}
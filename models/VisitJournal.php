<?php
	class VisitJournal extends Model
	{
		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/

		public static $mysql_table	= "visit_journal";
		
		public static $statuses = ["не указано", "был", "не был"];
		
		public static function addData($id_group, $date, $data)
		{
			$Schedule = GroupSchedule::find([
				"condition" => "id_group=$id_group AND date='$date'"
			]);
			
			$Group = Group::findById($id_group);
			
			foreach ($Group->students as $id_student)
			{
				$Student = Student::findById($id_student);
				
				// если админ, не отправлять смски
				if (!isAdmin()) {
					// если отсутствовал на занятии
					if ($data[$id_student]['presence'] == 2) {
						$message = Template::get(7, [
							"date" 			=> today_text(),
							"student_name"	=> $Student->last_name . " " . $Student->first_name,
							"abscent_word"	=> ($Student->getGender() == 1 ? "отсутствовал" : "отсутствовала"),
							"subject"		=> Subjects::$dative[$Group->id_subject],
						]);
						foreach (Student::$_phone_fields as $phone_field) {
							$representative_number = $Student->Representative->{$phone_field};
							if (!empty($representative_number)) {
								SMS::send($representative_number, $message, ["additional" => 3]);
							}
						}
					} else 
					// если отсутствовал на занятии
					if ($data[$id_student]['late'] >= 15) {
						$message = Template::get(6, [
							"date" 			=> today_text($Schedule->date),
							"student_name"	=> $Student->last_name . " " . $Student->first_name,
							"late_word"		=> ($Student->getGender() == 1 ? "опоздал" : "опоздала"),
							"subject"		=> Subjects::$dative[$Group->id_subject],
							"late_minutes"	=> $data[$id_student]['late'] . " " . pluralize('минуту', 'минуты', 'минут', $data[$id_student]['late']),
						]);
						foreach (Student::$_phone_fields as $phone_field) {
							$representative_number = $Student->Representative->{$phone_field};
							if (!empty($representative_number)) {
								SMS::send($representative_number, $message, ["additional" => 3]);
							}
						}
					}
				}
				
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
					"id_teacher"			=> $Group->id_teacher,
					"grade"					=> $Group->grade,
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
				"grade"					=> $Group->grade,
			]);
		}
		
		public static function getLessonCount($id_group)
		{
			return dbConnection()->query("SELECT id as c FROM visit_journal WHERE true AND id_group=$id_group GROUP BY lesson_date")->num_rows;
		}
		
		public static function lessonPresent($id_group)
		{
			return self::find([
				"condition" => "id_group=$id_group"
			]);
		}
		
				
		/**
		 * Коливество дней/недель/месяцев/лет с момента первого занятия
		 * 
		 * @param string $mode (default: 'days')
		 * $mode = days | weeks | months | years
		 */
		public static function fromFirstLesson($mode = 'days')
		{
			$today = time(); // or your date as well
		    $first_lesson_date = self::find(["order" => "lesson_date ASC"])->lesson_date;
			
		    $first_lesson_date = strtotime($first_lesson_date);
		    
		    $datediff = $today - $first_lesson_date;
			
		    switch ($mode) {
			    case 'days': {
				    return ceil($datediff / (60 * 60 * 24));
			    }
			    case 'weeks': {
				    return floor($datediff / (60 * 60 * 24 * 7));
			    }
			    case 'months': {
				    return ceil($datediff / (60 * 60 * 24 * 30));
			    }
			    case 'years': {
				    return ceil($datediff / (60 * 60 * 24 * 365));
			    }
		    }
		}
		
		
		/**
		 * Получить ID преподавателей, которые сейчас ведут группы.
		 * 
		 */
		public function getTeacherIds()
		{
			$result = dbConnection()->query("
				SELECT id_teacher FROM visit_journal
				WHERE id_teacher > 0
				GROUP BY id_teacher
			");
			
			$teacher_ids = [];
			
			while ($row = $result->fetch_object()) {
				$teacher_ids[] = $row->id_teacher;
			}
			
			return $teacher_ids;
		}
	}
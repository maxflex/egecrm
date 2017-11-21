<?php
	class VisitJournal extends Model
	{
		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/

		public static $mysql_table	= "visit_journal";

		public static $statuses = ["не указано", "был", "не был"];

		public function __construct($array)
		{
			parent::__construct($array);

            if (! $this->isNewRecord) {
                if ($this->grade == Grades::EXTERNAL) {
                    $this->grade_label = 'экстернат';
                    $this->grade_short = 'Э';
                } else {
                    $this->grade_label = $this->grade . ' класс';
                    $this->grade_short = $this->grade;
                }
            }

			if ($this->lesson_time) {
				$this->lesson_time = mb_strimwidth($this->lesson_time, 0, 5);
				if ($this->lesson_time == "00:00") {
					$this->lesson_time = null; // чтобы отображало "не установлено"
				}
			}
		}

        /**
         * @schedule-refactored
         */
		public static function addData($id_schedule, $data)
		{
			$Schedule = GroupSchedule::findById($id_schedule);

			$Group = Group::findById($Schedule->id_group);

			foreach ($Group->students as $id_student)
			{
				$Student = Student::findById($id_student);

				// если админ, не отправлять смски
				if (! User::isAdmin()) {
					// если отсутствовал на занятии
					if ($data[$id_student]['presence'] == 2) {
						// @sms-checked
						$message = Template::get(7, [
							"date" 			=> today_text(),
							"student_name"	=> $Student->last_name . " " . $Student->first_name,
							"abscent_word"	=> ($Student->getGender() == 1 ? "отсутствовал" : "отсутствовала"),
							"subject"		=> Subjects::$dative[$Group->id_subject],
						]);
						foreach (Student::$_phone_fields as $phone_field) {
							$representative_number = $Student->Representative->{$phone_field};
							if (!empty($representative_number)) {
								SMS::send($representative_number, $message);
							}
						}
					} else {
						// если отсутствовал на занятии
						if ($data[$id_student]['late'] >= 5) {
							// @sms-checked
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
									SMS::send($representative_number, $message);
								}
							}
						}
					}
				}
				// @time-refactored @time-checked
				self::add([
					"id_entity" 			=> $id_student,
					"type_entity"			=> Student::USER_TYPE,
					"id_group"				=> $Schedule->id_group,
					"id_subject"			=> $Group->id_subject,
					"cabinet"				=> $Schedule->cabinet,
					"lesson_date"			=> $Schedule->date,
					"lesson_time"			=> $Schedule->time,
					"date"					=> now(),
					"presence"				=> $data[$id_student]['presence'],
					"late"					=> $data[$id_student]['late'],
					"comment"				=> $data[$id_student]['comment'],
					"id_user_saved"			=> User::fromSession()->id,
					"id_teacher"			=> $Group->id_teacher,
					"grade"					=> $Group->grade,
					"duration"				=> $Group->duration,
					"year"					=> static::_academicYear($Schedule->date),
				]);
			}
			// @time-refactored @time-checked
			self::add([
				"id_entity" 			=> $Group->id_teacher,
				"type_entity"			=> Teacher::USER_TYPE,
				"id_group"				=> $Schedule->id_group,
				"id_subject"			=> $Group->id_subject,
				"cabinet"				=> $Schedule->cabinet,
				"lesson_date"			=> $Schedule->date,
				"lesson_time"			=> $Schedule->time,
				"date"					=> now(),
				"teacher_price"			=> $Group->teacher_price,
				"teacher_price_official"=> $Group->teacher_price_official,
				"insurance"             => round($Group->teacher_price_official * 0.302, 2),
				"id_user_saved"			=> User::fromSession()->id,
				"grade"					=> $Group->grade,
				"duration"				=> $Group->duration,
				"year"					=> static::_academicYear($Schedule->date),
			]);
		}

		/**
		 * Изменение истории журнала. Доступен только для админов.
		 * @schedule-refactored
		 */
		public static function updateData($id_schedule, $data)
		{
			$Schedule = GroupSchedule::findById($id_schedule);

			$Group = Group::findById($Schedule->id_group);
			$updatedElemCnt = 0;
			foreach ($Group->students as $id_student)
			{
				$VisitJournal = VisitJournal::find([
										'condition' => 	"id_entity = ".$id_student." AND ".
														"type_entity = '".Student::USER_TYPE."' AND ".
														"id_group = ".$Schedule->id_group." AND ".
														"lesson_date = '".$Schedule->date."' AND ".
														"lesson_time = '".$Schedule->time."' "
								]);

				if ($VisitJournal) {
					$res = $VisitJournal->update([
									"presence" => $data[$id_student]['presence'],
									"late"     => $data[$id_student]['late'],
									"comment"  => $data[$id_student]['comment']
							]);
					$updatedElemCnt += $res ? 1 : 0;
				}
			}
			echo $updatedElemCnt;
		}

		public static function getLessonCount($id_group)
		{
			return dbConnection()->query("SELECT id as c FROM visit_journal WHERE true AND id_group=$id_group and type_entity = 'TEACHER'")->num_rows;
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

        public static function getGroupIdsBySubject($id_student, $id_subject, $grade) {
            return self::getGroupIds($id_student, $id_subject, $grade);
        }

        public static function getGroupIds($id_student, $id_subject = false, $grade = false) {
            $Visits = self::findAll([
                "condition" =>
                    "id_entity = ".$id_student." AND ".
                    "type_entity = 'STUDENT' " .
                    ($id_subject ? "AND id_subject = {$id_subject} " : "") .
                    ($grade ? "AND grade = {$id_subject} " : ""),
                "order" => "lesson_date",
                "group" => "id_group"
            ]);

            $group_ids = [];
            foreach ($Visits as $v) {
                $group_ids[] = $v->id_group;
            }
            return $group_ids;
        }


		private static function _academicYear($date)
		{
			$year = date("Y", strtotime($date));
			$day_month = date("m-d", strtotime($date));

			if ($day_month >= '01-01' && $day_month <= '07-15') {
				$year--;
			}
			return $year;
		}

		public static function getTeacherLessons($id_teacher, $with = [])
        {
            $Lessons = static::findAll([
                "condition" => "id_entity=$id_teacher AND type_entity='TEACHER'",
                "order"		=> "lesson_date, lesson_time",
            ]);
            if (! $Lessons) {
				return [];
			}
            for ($i = 0; $i < count($Lessons); $i++) {
                $Lesson = $Lessons[$i];
                $NextLesson = isset($Lessons[$i + 1]) ? $Lessons[$i + 1] : false;

                $Lesson->cabinet = Cabinet::getBlock($Lesson->cabinet, $Lesson->id_branch);
                $Lesson->group_level = dbConnection()->query("SELECT level FROM groups WHERE id= {$Lesson->id_group}")->fetch_object()->level;

                if (in_array('payments', $with)) {
                    $Lesson->payments = Payment::findAll([
                        "condition" => "entity_id={$id_teacher} and entity_type='" . Teacher::USER_TYPE . "' " .
                            "and str_to_date(date, '%d.%m.%Y') >= '{$Lesson->lesson_date}' " . ($NextLesson ? " and str_to_date(date, '%d.%m.%Y') < '" . $NextLesson->lesson_date . "' " : "")
                    ]);
                }

                if (in_array('login', $with)) {
                    $Lesson->login_user_saved = dbConnection()->query("select login from users where id = {$Lesson->id_user_saved}")->fetch_object()->login;
                }
            }

            return $Lessons;
        }
	}

<?php
	class VisitJournal extends Model
	{
		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/

		public static $mysql_table	= "visit_journal";

		public static $statuses = ["не указано", "был", "не был"];

		const PLANNED_CONDITION = "((type_entity='' or type_entity IS NULL) and cancelled=0)";

		public function __construct($array)
		{
			parent::__construct($array);

			$this->is_planned   = $this->type_entity ? false : true;
			$this->is_conducted = $this->type_entity ? true  : false;

			// занятие не зарегистрировано
			$this->not_registered = $this->is_planned && ! $this->cancelled && $this->lesson_date < now(true);

			// подтягиваем недостающие данные из группы, если занятие планируется
			if ($this->is_planned) {
				$Group = Group::getLight($this->id_group);
				foreach(['id_teacher', 'id_subject', 'grade', 'year'] as $field) {
					$this->{$field} = $Group->{$field};
				}
			}

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

			$this->date_time = $this->lesson_date . ' ' . $this->lesson_time;
		}



		public static function addData($id_lesson, $data)
		{
			$Lesson = self::findById($id_lesson);

			$Group = Group::findById($Lesson->id_group);

			$prices = Prices::get();

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
								"date" 			=> today_text($Lesson->lesson_date),
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

				$last_student_contract = $Student->getLastContract($Group->year);
				$price = $prices[$last_student_contract->info->grade];
				if ($last_student_contract->discount) {
					$price = round($price - ($price * ($last_student_contract->discount / 100)));
				}

				self::add([
					"id_entity" 			=> $id_student,
					"type_entity"			=> Student::USER_TYPE,
					"id_group"				=> $Lesson->id_group,
					"id_subject"			=> $Group->id_subject,
					"cabinet"				=> $Lesson->cabinet,
					"lesson_date"			=> $Lesson->lesson_date,
					"lesson_time"			=> $Lesson->lesson_time,
					"date"					=> now(),
					"presence"				=> $data[$id_student]['presence'],
					"late"					=> $data[$id_student]['late'],
					"comment"				=> $data[$id_student]['comment'],
					"id_user_saved"			=> User::id(),
					"id_teacher"			=> $Group->id_teacher,
					"grade"					=> $Group->grade,
					"duration"				=> $Group->duration,
					"year"					=> static::_academicYear($Lesson->lesson_date),
					"price"					=> $price,
					"entry_id"				=> $id_lesson,
				]);
			}
			// @time-refactored @time-checked
			self::updateById($id_lesson, [
				"id_entity" 			=> $Group->id_teacher,
				"type_entity"			=> Teacher::USER_TYPE,
				"id_subject"			=> $Group->id_subject,
				"date"					=> now(),
				"price"					=> $Group->teacher_price,
				"id_user_saved"			=> User::id(),
				"grade"					=> $Group->grade,
				"duration"				=> $Group->duration,
				"year"					=> static::_academicYear($Lesson->lesson_date),
				"entry_id"				=> $id_lesson,
				"id_teacher"			=> $Group->id_teacher,
			]);
		}

		public function afterFirstSave()
		{
			if (! $this->entity_type && ! $this->entry_id) {
				$this->entry_id = $this->id;
				$this->save('entry_id');
			}
		}

		/**
		 * Изменение истории журнала. Доступен только для админов.
		 */
		public static function updateData($id_lesson, $data)
		{
			$Lesson = self::findById($id_lesson);

			$Group = Group::findById($Lesson->id_group);
			$updatedElemCnt = 0;
			foreach ($Group->students as $id_student)
			{
				$VisitJournal = VisitJournal::find([
					'condition' =>
						"id_entity = " . $id_student . " AND " .
						"type_entity = '" . Student::USER_TYPE . "' AND " .
						"entry_id = " . $Lesson->entry_id
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

		/**
         * незапланированное
         */
		public function isUnplanned()
		{
            $Time = Time::getLight();

			$GroupTimeData = GroupTime::findAll([
				"condition" => "id_group=" . $this->id_group,
			]);

			$day_of_the_week = date("w", strtotime($this->lesson_date));
			if ($day_of_the_week == 0) {
				$day_of_the_week = 7;
			}

			$is_planned = false;
			foreach ($GroupTimeData as $GroupTime) {
				if ($day_of_the_week == Time::getDay($GroupTime->id_time) && $this->lesson_time == $Time[$GroupTime->id_time]) {
					$is_planned = true;
					break;
				}
			}

			return !$is_planned;
		}

		/**
		 * Занятие в процессе
		 */
		public function inProgress()
		{
			if ($this->cancelled) {
				return false;
			}
			$start_time = (new DateTime($this->date_time))->getTimestamp();
			$end_time = (new DateTime($this->date_time))->modify('+135 minutes')->getTimestamp();
			return ((time() < $end_time) && (time() > $start_time));
		}

		/**
		 * Получить номер урока
		 */
		public function getLessonNumber()
		{
			return self::count([
				"condition" => "id_group={$this->id_group}
					AND CONCAT(`lesson_date`,' ',`lesson_time`) <= '{$this->lesson_date} {$this->lesson_time}:00'
					AND (type_entity='TEACHER' or " . self::PLANNED_CONDITION . ")
					AND cancelled = 0"
			]);
		}

		/**
		 * Get group past & planned lessons
		 */
		public static function getGroupLessons($id_group, $func = 'findAll', $order = 'ASC')
		{
			return self::{$func}([
				'condition' => "id_group={$id_group} and (type_entity='TEACHER' or " . self::PLANNED_CONDITION . ")",
				'order' => "CONCAT(lesson_date, ' ', lesson_time) {$order}"
			]);
		}

		/**
		 * Проверить урок на наслоение кабинетов
		 */
		public function isLayered()
		{
			return self::find([
				'condition' => "
					lesson_date = '{$this->lesson_date}' AND
					lesson_time = '{$this->lesson_time}:00' AND
					cabinet = {$this->cabinet} AND
					id <> {$this->id} AND
					" . self::PLANNED_CONDITION
			]);
		}

		/**
		 * Get student group past & planned lessons
		 */
		public static function getStudentGroupLessons($id_group, $id_student)
		{
			return self::findAll([
				'condition' => "id_group={$id_group} and ((type_entity='STUDENT' and id_entity={$id_student}) or " . self::PLANNED_CONDITION . ")",
				'order' => 'lesson_date asc, lesson_time asc'
			]);
		}

	}

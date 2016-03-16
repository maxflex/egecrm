<?php
	class Group extends Model
	{
		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/

		public static $mysql_table	= "groups";

		protected $_inline_data = ["students"];

		const DURATION = [135];
 

		/*====================================== СИСТЕМНЫЕ ФУНКЦИИ ======================================*/

		public function __construct($array)
		{
			parent::__construct($array);

			if (empty($this->students[0])) {
				$this->students = [];
			}

			$this->first_schedule 		= $this->getFirstSchedule();

			if ($this->id_teacher) {
				$this->Teacher	= Teacher::findById($this->id_teacher);
			}

			if (!$this->isNewRecord) {
				$this->past_lesson_count = VisitJournal::getLessonCount($this->id);
				$this->agreed_students_count 	= $this->getAgreedStudentsCount();
				$this->notified_students_count 	= $this->getNotifiedStudentsCount();
				$this->schedule_count = $this->getScheduleCountCached();

				if ($this->grade && $this->id_subject) {
					$this->days_before_exam = $this->daysBeforeExam();
				}
			}

			if (!$this->student_statuses) {
				$this->student_statuses = [];
			}

			if ($this->cabinet) {
				$this->CabinetInfo = Cabinet::findById($this->cabinet);
			}

			if ($this->id_branch) {
				$this->branch = Branches::getShortColoredById($this->id_branch,
					($this->cabinet ? "-".$this->CabinetInfo->number : "")
				);
			}

			$this->is_special 			= $this->isSpecial();
			$this->day_and_time 		= $this->getDayAndTime();

			$this->Comments	= Comment::findAll([
				"condition" => "place='". Comment::PLACE_GROUP ."' AND id_place=" . $this->id,
			]);
		}

		/*====================================== СТАТИЧЕСКИЕ ФУНКЦИИ ======================================*/


		public function getAgreedStudentsCount()
		{
			if (!count($this->students)) {
				return 0;
			}

// 			preType("id_group = {$this->id} AND type_entity='STUDENT' AND id_entity IN (" . implode(",", $this->students) . ") AND id_status=3");

			$result = dbConnection()->query("SELECT COUNT(*) FROM group_agreement WHERE id_group = {$this->id} AND type_entity='STUDENT' AND id_entity IN (" . implode(",", $this->students) . ") AND id_status=3 GROUP BY id_group, id_entity");

			return $result->num_rows;
/*
			return GroupAgreement::count([
				"condition" => "id_group = {$this->id} AND type_entity='STUDENT' AND id_entity IN (" . implode(",", $this->students) . ") AND id_status=3"
			]);
*/
		}

		public function getNotifiedStudentsCount()
		{
			if (!count($this->students) || !$this->id_branch || !$this->id_subject || !$this->first_schedule || !$this->cabinet) {
				return 0;
			}
			return GroupSms::count([
				"condition" => "id_branch = {$this->id_branch} AND id_student IN (" . implode(",", $this->students) . ") AND notified=1
								 AND id_subject = {$this->id_subject} AND first_schedule = '{$this->first_schedule}' AND cabinet={$this->cabinet}"
			]);
		}

		/**
		 * Получить даты проведенных занятий.
		 *
		 */
		public function getPastLessonDates()
		{

			$Lessons = VisitJournal::findAll([
				"condition" => "id_group={$this->id}",
				"group"		=> "lesson_date",
			]);

			foreach ($Lessons as $Lesson) {
				$dates[] = $Lesson->lesson_date;
			}

			return $dates;
		}

		/**
		 * Получить даты отмененных занятий.
		 *
		 * @return string[]			Даты отмененных занятий в формате гггг-мм-дд
		 */
		public function getCancelledLessonDates()
		{
			$dates = [];
			$Schedules = GroupSchedule::findAll([
				"condition" => "id_group={$this->id} AND cancelled = 1"
			]);

			/* @var $Schedules GroupSchedule[] */
			foreach ($Schedules as $Schedule) {
				$dates[] = $Schedule->date;
			}

			return $dates;
		}

		/**
		 * Получить ID преподавателей, которые сейчас ведут группы.
		 *
		 */
		public function getTeacherIds()
		{
			$result = dbConnection()->query("
				SELECT id_teacher FROM groups
				GROUP BY id_teacher
			");

			$teacher_ids = [];

			while ($row = $result->fetch_object()) {
				$teacher_ids[] = $row->id_teacher;
			}

			return $teacher_ids;
		}


		/**
		 * Если хотя бы 1 день в расписании группы не соответствует дням недели этой группы то в списке групп нужно
		   ставить пиктограммку в конце например типа восклицательный значок.
		 *
		 */
		public function lessonDaysMatch()
		{
			if ($this->day_and_time) {
				$days = array_keys($this->day_and_time);

				// sunday in mysql is 0
				foreach ($days as &$day) {
					if ($day == 7) {
						$day = 0;
					}
				}

				// дни совпали
				$days_match = GroupSchedule::count([
					"condition" => "id_group={$this->id} AND DATE_FORMAT(date, '%w') NOT IN (" . implode(',', $days) . ") AND cancelled = 0"
				]) > 0 ? false : true;

				// если дни совпали, проверяем время
				if ($days_match) {

					// проверяем время
					$sql = [];

					foreach($this->day_and_time as $day => $day_data) {
						$sql_tmp = "DATE_FORMAT(date, '%w') = " . ($day == 0 ? 7 : $day);
						$sql_time = [];
						foreach ($day_data as $time) {
							$sql_time[] = "'". $time ."'";
						}
						if (count($sql_time)) {
							$sql_tmp .= " AND SUBSTR(time, 1, 5) NOT IN (" . implode(",", $sql_time) . ")";
						}
						$sql[] = "(" . $sql_tmp . ")";
					}

					// время совпало?
					return GroupSchedule::count([
						"condition" => "id_group={$this->id} AND (" . implode(" OR ", $sql) . ") AND cancelled = 0"
					]) > 0 ? false : true;
				} else {
					return false;
				}

			} else {
				return true;
			}
		}


		/**
		 * Получить количество занятий из календаря.
		 *  УЖЕ ГДЕ-ТО ЕСТЬ ЭТОТ ФУНКЦИОНАЛ! Group.Schedule.length
		 */
/*
		public function getTotalLessonCount()
		{
			return GroupSchedule::count([
				"condition"	=> "id_group={$this->id}",
			]);
		}

*/
        /**
         * @param $id_group                 ID группы
         * @param $date                     Дата
         * @param bool $withoutCancelled    Надо ли учитовать отмененные пары
         * @return GroupSchedule[]|bool
         */
		public function inSchedule($id_group, $date, $withoutCancelled = false)
		{
			return GroupSchedule::find([
				"condition" => "id_group=$id_group AND date='$date'".($withoutCancelled ? " AND cancelled = 0 " : "")
			]);
		}
		
		/**
		 * Получить отсутствующие занятие за последние 7 дней
		 */
		public static function getLastWeekMissing($total_count = false)
		{
			$date = date('Y-m-d');
			
			$minutes = LESSON_LENGTH + 30; // 30 минут после окончания урока
			
			foreach(range(1, 7) as $i) {
				$GroupSchedule = GroupSchedule::findAll([
					"condition" => "date='$date'  AND ((UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(CONCAT_WS(' ', date, time))) / 60) >  {$minutes}
						AND UNIX_TIMESTAMP(CONCAT_WS(' ', date, time)) < UNIX_TIMESTAMP(NOW())"
				]);
				
				
				foreach ($GroupSchedule as $Schedule) {
					// Проверяем было ли это занятие
					$was_lesson = VisitJournal::find([
						"condition" => "lesson_date = '" . $Schedule->date . "' AND id_group=" . $Schedule->id_group
					]);
					
					// если занятия не было, добавляем в ошибки
					if (!$was_lesson) {
						$return[$date]++;
						$total_missing_count++;
					}	
				}
				
				$date = date('Y-m-d', strtotime($date . "-$i day"));
			}
			
			return $total_count ? $total_missing_count : $return;
		}

		/**
		 *  Всего человеко групп - это количество человек, записанных в любые группы. Если один человек записан в 3 группы, то это 3 человеко-группы
			Согласных с расписание - это количество учеников, согласных в конкретной группе с отметкой согласен
			Запланировано групп - это просто
			Преподов, согласных с расписанием - это количество синих "согласен" у преподов
			Человеко-групп не в группах - это количество человеко-групп с действующими договорами, не прикрепленных к группам.
		 *
		 */
		public static function getStats()
		{
			if (LOCAL_DEVELOPMENT) {
				return self::_getStats();
			} else {
				$return = memcached()->get("GroupStats");

				if (memcached()->getResultCode() != Memcached::RES_SUCCESS) {
					$return = self::_getStats();
					memcached()->set("GroupStats", $return, 3600 * 24);
				}

				return $return;
			}
		}

		public static function _getStats()
		{
			$Groups = Group::findAll();

			foreach ($Groups as $Group) {

				if ($Group->students) {
					$total_group_students += count($Group->students);
				}
			}

			return [
				"total_group_students" 	=> $total_group_students,
				"total_students_notified" => $total_students_notified,
				"total_groups"			=> count($Groups),
			];
		}

		/*====================================== ФУНКЦИИ КЛАССА ======================================*/

		public function daysBeforeExam()
		{
			if ($this->grade == 10) {
				return false;
			}

			// Получаем дату последнего запланированного занятия
			$GroupSchedule = GroupSchedule::find([
				"condition" => "id_group={$this->id}",
				"order"		=> "date DESC",
			]);

			// Дату экзамена
			$ExamDay = ExamDay::find([
				"condition" => "id_subject={$this->id_subject} AND grade={$this->grade}"
			]);

/*
			$datetime1 = new DateTime(date("Y-m-d", strtotime($ExamDay->date)));
			$datetime2 = new DateTime(date("Y-m-d", strtotime($GroupSchedule->date)));
			$difference = $datetime1->diff($datetime2);
			return ($difference->d - 1);
*/
			$diff = strtotime($ExamDay->date) - strtotime($GroupSchedule->date);
			return floor($diff/(60*60*24)) - 1;
		}

		/**
		 * @param bool $withoutCancelled	whether cancelled lessons should be ignored.
		 * @return GroupSchedule[]|bool		Schedule elems if found, false otherwise.
		 */
		public function getSchedule($withoutCancelled=false)
		{
			return GroupSchedule::findAll([
				"condition" => "id_group=".$this->id.($withoutCancelled ? ' AND cancelled = 0 ' : ''),
				"order"		=> "date ASC, time ASC",
			]);

		}

		/**
		 * Gets all schedules of group, where schedule time is not defined and lesson isn't cancelled.
		 *
		 * @return GroupSchedule[]|bool		Group schedules, where time is not defined if found,
		 * 									false otherwise.
		 */
		public function getScheduleWithoutTime()
		{
			return GroupSchedule::findAll([
				"condition" => "id_group=".$this->id." AND ".
							   "(time IS NULL OR time = '00:00:00' OR cabinet IS NULL OR id_branch IS NULL OR ".
							   "0 in (cabinet, id_branch)) AND cancelled = 0",
				"order"		=> "date ASC, time ASC",
			]);
		}

		public function getFutureSchedule()
		{
			return GroupSchedule::findAll([
				"condition" => "id_group=".$this->id." AND UNIX_TIMESTAMP(CONCAT_WS(' ', date, time)) > UNIX_TIMESTAMP(NOW())",
				"order"		=> "date ASC, time ASC",
			]);
		}

		public function countFutureSchedule()
		{
			return GroupSchedule::count([
				"condition" => "id_group=".$this->id." AND UNIX_TIMESTAMP(CONCAT_WS(' ', date, time)) > UNIX_TIMESTAMP(NOW())",
			]);
		}

		public function getPastSchedule()
		{
			return GroupSchedule::findAll([
				"condition" => "id_group=".$this->id." AND UNIX_TIMESTAMP(CONCAT_WS(' ', date, time)) < UNIX_TIMESTAMP(NOW())",
				"order"		=> "date ASC, time ASC",
			]);
		}

		// LESSON_LENGTH = 105 минут - 1:45 - 30 минут до конца занятия
		//
		// ВНИМАНИЕ, ТЕПЕРЬ LESSON_LENGTH = 135 + 30 (165). Раньше было с минусом $minutes = LESSON_LENGTH + $minutes_to_end,
		// т.е. цифры обновлялись за полчаса
		public function getPastScheduleBeforeEnd($minutes_to_end = 30)
		{
			$minutes = LESSON_LENGTH + $minutes_to_end;

			return GroupSchedule::findAll([
				// "condition" => "id_group=".$this->id." AND  ((ABS(UNIX_TIMESTAMP(CONCAT_WS(' ', date, time)) - UNIX_TIMESTAMP(NOW())) / 60) > {$minutes})
				"condition" => "id_group=".$this->id." AND ((UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(CONCAT_WS(' ', date, time))) / 60) >  {$minutes}
					AND UNIX_TIMESTAMP(CONCAT_WS(' ', date, time)) < UNIX_TIMESTAMP(NOW())",
				"order"		=> "date ASC, time ASC",
			]);
		}

		// получить прошлое расписание для уведомления учителя об отсутсвии записи в журнале
		public function getPastScheduleTeacherReport()
		{
			return GroupSchedule::findAll([
				"condition" => "id_group=".$this->id."
					AND ((UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(CONCAT_WS(' ', date, time))) / 60) < ". (LESSON_LENGTH + 35) ."
					AND ((UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(CONCAT_WS(' ', date, time))) / 60) > ". (LESSON_LENGTH + 25) ."
					AND date='". date('Y-m-d') ."'",
				"order"		=> "date ASC, time ASC",
			]);
		}


		public function countSchedule()
		{
			$paid = GroupSchedule::count([
				"condition" => "is_free=0 AND id_group=".$this->id,
			]);

			$free = GroupSchedule::count([
				"condition" => "is_free=1 AND id_group=".$this->id,
			]);

			return [
				'free' => $free,
				'paid' => $paid,
			];
		}

/*

		public function getScheduleCached()
		{
			$return = memcached()->get("GroupSchedule[{$this->id}]");

			if (memcached()->getResultCode() != Memcached::RES_SUCCESS) {
				$return = $this->getSchedule();
				memcached()->set("GroupSchedule[{$this->id}]", $return, 5 * 24 * 3600);
			}
			return $return;
		}
*/

		public function getScheduleCountCached()
		{
			if (LOCAL_DEVELOPMENT) {
				return $this->countSchedule();
			}


			$return = memcached()->get("GroupScheduleCount[{$this->id}]");

			if (memcached()->getResultCode() != Memcached::RES_SUCCESS) {
				$return = $this->countSchedule();
				memcached()->set("GroupScheduleCount[{$this->id}]", $return, 5 * 24 * 3600);
			}
			return $return;
		}


		/**
		 * Получить дату первого занятия из расписания.
		 */
		public function getFirstSchedule($unix = true)
		{
			$GroupFirstSchedule =  GroupSchedule::find([
				"condition" => "id_group={$this->id} AND cancelled = 0 ",
				"order"		=> "date ASC"
			]);

			if ($unix) {
				return $GroupFirstSchedule ? strtotime($GroupFirstSchedule->date . " " . $GroupFirstSchedule->time) . "000" : false;
			} else {
				return $GroupFirstSchedule;
			}
		}

		/**
		 * Получить дату первого занятия из расписания.
		 *
		 */
/*
		public function getFirstScheduleCached()
		{
			$return = memcached()->get("GroupFirstSchedule[{$this->id}]");

			if (memcached()->getResultCode() != Memcached::RES_SUCCESS) {
				$GroupFirstSchedule = $this->getFirstSchedule();
				$return = $GroupFirstSchedule ? strtotime($GroupFirstSchedule->date) . "000" : false;
				memcached()->set("GroupFirstSchedule[{$this->id}]", $return, 180 * 24 * 3600);
			}

			return $return;
		}
*/

		/**
		 * Получить первое занятие
		 * $from_today – первое относительно сегодняшнего дня (ближайшее следующее)
		 */
		public function getFirstLesson($from_today = false)
		{
			return GroupSchedule::find([
				"condition" => "id_group={$this->id}" . ($from_today ? " AND date >= '" . date("Y-m-d") . "'" : ""),
				"order"		=> "date ASC"
			]);
		}

		/**
		 * Если в группе состоит хотя бы 1 ученик с занятиями больше 40, то в списке групп предмет выглядит вместо "русский" пишем "русский (спецгруппа)"
		 *
		 */
		public function isSpecial()
		{
			if (!$this->id_subject) {
				return false;
			}

			return dbConnection()->query("
				SELECT g.id FROM groups g
					LEFT JOIN students s ON s.id IN (" . implode(",", $this->students) . ")
					LEFT JOIN contracts c ON c.id_student = s.id
					LEFT JOIN contract_subjects cs ON cs.id_contract = c.id
				WHERE g.id = {$this->id} AND (c.id_contract=0 OR c.id_contract IS NULL) AND cs.count>40 AND cs.id_subject={$this->id_subject}
				LIMIT 1
			")->num_rows;
		}

		public function registeredInJournal($date)
		{
			return VisitJournal::count([
				"condition" => "id_group=" . $this->id . " AND lesson_date='$date'",
			]) > 0 ? true : false;
		}

		public function getStudents()
		{
			if (!$this->students) {
				return false;
			}
			return Student::findAll([
				"condition" => "id IN (" . implode(",", $this->students) . ")"
			]);
		}

		/**
		 * Получить свободное время ученика.
		 *
		 */
		public function getDayAndTime()
		{
			$GroupTime = GroupTime::findAll([
				"condition"	=> "id_group=" . $this->id
			]);

			if (!$GroupTime) {
				return [];
			}

			foreach ($GroupTime as $GroupTimeData) {
				$index = Freetime::getIndexByTime($GroupTimeData->time);
				$return[$GroupTimeData->day][$index] = $GroupTimeData->time;
			}

			return $return;
		}
	}

	class GroupSchedule extends Model
	{
		public static $mysql_table	= "group_schedule";

		const PER_PAGE = 100; // Сколько отображать на странице списка


		public function __construct($array)
		{
			parent::__construct($array);

			if ($this->time) {
				$this->time = mb_strimwidth($this->time, 0, 5);
			}

			$this->was_lesson = VisitJournal::find(["condition" => "id_group={$this->id_group} AND lesson_date='{$this->date}'"]) ? true : false;
			$this->is_first_lesson = $this->date == $this->getFirstLessonDate();
		}


		public function getFirstLessonDate()
		{
/*
			return self::find([
				"condition" => "id_group=" . $this->id_group,
				"order"		=> "date ASC"
			])->date;
*/
			$result = dbConnection()->query("SELECT date FROM group_schedule WHERE id_group={$this->id_group} ORDER BY date ASC LIMIT 1");
			return $result->fetch_object()->date;
		}

		/**
		 * Получить заявки по номеру страницы и ID списка из RequestStatuses Factory.
		 *
		 */
		public static function getByPage($page)
		{
			if (!$page) {
				$page = 1;
			}

			// С какой записи начинать отображение, по формуле
			$start_from = ($page - 1) * self::PER_PAGE;

			$Schedule = GroupSchedule::findAll([
				"order" => "date ASC, time ASC",
				"limit" 	=> $start_from. ", " .self::PER_PAGE
			]);

			foreach ($Schedule as &$S) {
				$Group = Group::findById($S->id_group);
				if ($Group) {
					$S->Group = $Group;
					$ExistingSchedule[] = $S;
				}
			}

			return $ExistingSchedule;
		}

		public static function countAll()
		{
			if (LOCAL_DEVELOPMENT) {
				$result = dbConnection()->query("
					SELECT COUNT(gs.id) as cnt FROM group_schedule gs
					LEFT JOIN groups g ON g.id = gs.id_group
					WHERE g.id IS NOT NULL
					ORDER BY date ASC, time ASC
				");

				return $result->fetch_object()->cnt;
			} else {
				$result = memcached()->get("GroupScheduleCount");

				if (!$result) {
					$result = dbConnection()->query("
						SELECT COUNT(gs.id) as cnt FROM group_schedule gs
						LEFT JOIN groups g ON g.id = gs.id_group
						WHERE g.id IS NOT NULL
						ORDER BY date ASC, time ASC
					");

					$result = $result->fetch_object()->cnt;
					memcached()->set($result, 24 * 3600);
				}

				return $result;
			}
		}

		public function isUnplanned()
		{
			$GroupTimeData = GroupTime::findAll([
				"condition" => "id_group=" . $this->id_group,
			]);

			$is_planned = false;
			foreach ($GroupTimeData as $GroupTime) {
				$day_of_the_week = date("w", strtotime($this->date));
				if ($day_of_the_week == 0) {
					$day_of_the_week = 7;
				}

				// error_log("{$this->id_group} | {$day_of_the_week} / {$GroupTime->day} | " . $GroupTime->time . " / {$this->time}");
				if ($day_of_the_week == $GroupTime->day && $this->time == $GroupTime->time) {
					$is_planned = true;
					break;
				}
			}

			return !$is_planned;
		}

		/**
		 * @param  bool $withoutCancelled		whether get cancelled lessons too.
		 * @return GroupSchedule[]|bool		Found elems.
		 */
		public static function getVocationDates($withoutCancelled = false)
		{
			$Vocations = self::findAll([
				"condition" => "id_group=0".($withoutCancelled ? ' AND cancelled = 0 ' : '')
			]);

			$vocation_dates = [];

			foreach ($Vocations as $Vocation) {
				$vocation_dates[] = $Vocation->date;
			}

			return $vocation_dates;
		}

		/**
		 * Return array of ids of branches, where group lessons are held.
		 *
		 * @param int $id_group     	Id of group
		 * @return string[] 		    Array of branch ids
		 */
		public static function getBranchIds($id_group,$asArray = true)
		{
			$result = dbConnection()->query(
				"SELECT GROUP_CONCAT(DISTINCT gs.id_branch ORDER BY gs.id_branch ASC) as ids ".
				"FROM  `group_schedule` gs ".
				"WHERE gs.id_branch <> 0 AND gs.id_group = ".intval($id_group)
			);
			if ($result) {
				return explode(',',$result->fetch_object()->ids);
			} else {
				return [];
			}
		}

        /**
         * Проверка на наслоенность
         *
         * @return bool|int[]       False если нет наслоения,
         *                          иначе ID студента, в расписании которого возникло наслоение
         */
        public function isLayered()
        {
            /* find groups with same schedule */
            $GroupSchedules = GroupSchedule::findAll([
                "condition" => "date='{$this->date}' AND time='{$this->time}' AND cancelled=0"
            ]);

            $group_ids = [];
            /* @var $GroupSchedules     $GroupSchedule[] */
            foreach ($GroupSchedules as $GroupSchedule) {
                $group_ids[] = $GroupSchedule->id_group;
            }

            if (!empty($group_ids)) {
                $Groups = Group::findAll([
                    "condition" => "id IN (".implode(',', $group_ids).") AND ended=0"
                ]);

                $currentGroup = false;
                foreach ($Groups as $Group) {
                    if ($Group->id == $this->id_group) {
                        $currentGroup = $Group;
                        break;
                     }
                }

                /* @var $Groups     Group[] */
                foreach ($Groups as $Group) {
                    if ($Group->id != $currentGroup->id) {
                        if ($layerdata = array_intersect($currentGroup->students, $Group->students)) {
                            return $layerdata;
                        }
                    }
                }
            }

            return false;
        }
    }


	class GroupTime extends Model
	{
		public static $mysql_table	= "group_time";

		public function __construct($array)
		{
			parent::__construct($array);

			if (!$this->isNewRecord) {
				$this->time = Freetime::TIME[$this->time];
			}
		}
		/**
		 * Добавить свободное время
		 *
		 */
		public static function addData($data, $id_group)
		{
			self::deleteAll([
				"condition" => "id_group=$id_group"
			]);

			foreach ($data as $day => $day_data) {
				foreach ($day_data as $time) {
					if (empty(trim($time))) {
						continue;
					}
					$GroupTime = new self([
						"id_group"	=> $id_group,
						"day"		=> $day,
						"time"		=> Freetime::getId($time),
					]);

					$GroupTime->save();
				}
			}
		}
	}

	class GroupNote extends Model
	{
		public static $mysql_table	= "group_missing_notes";
	}

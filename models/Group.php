<?php
	class Group extends Model
	{
		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/

		public static $mysql_table	= "groups";

		protected $_inline_data = ["students"];

		const DURATION = [135];
		const PER_PAGE = 1000;


		/*====================================== СИСТЕМНЫЕ ФУНКЦИИ ======================================*/

		public function __construct($array)
		{
			parent::__construct($array);

			if (empty($this->students[0])) {
				$this->students = [];
			}

			$this->first_schedule 		= $this->getFirstSchedule();

			if ($this->id_teacher) {
				$this->Teacher	= Teacher::getLight($this->id_teacher, ['comment']);
			}

			if (!$this->isNewRecord) {
				$this->past_lesson_count 		= $this->getPastScheduleCountCached();;
				$this->schedule_count = $this->getScheduleCountCached();

				if ($this->grade && $this->id_subject && !$this->ended && $this->schedule_count['paid']) {
					$this->days_before_exam = $this->daysBeforeExam();
				}

				// @time-refactored @time-checked
				static::_addCabinets($this);
			}

			if (! $this->student_statuses) {
				$this->student_statuses = [];
			}

			$this->is_special 			= $this->isSpecial();
			$this->day_and_time 		= $this->getDayAndTime($this->id);

			$this->Comments	= Comment::findAll([
				"condition" => "place='". Comment::PLACE_GROUP ."' AND id_place=" . $this->id,
			]);
		}

		/*====================================== СТАТИЧЕСКИЕ ФУНКЦИИ ======================================*/

		// @time-refactored @time-checked
		public static function getNotifiedStudentsCount($Group)
		{
			$FirstLesson = Group::getFirstLesson($Group->id, true);
			if (!count($Group->students) || !$Group->id_subject || !$Group->first_schedule || !$FirstLesson->cabinet) {
				return 0;
			}
			return GroupSms::count([
				"condition" => "id_student IN (" . implode(",", $Group->students) . ") AND id_subject = {$Group->id_subject}
									AND first_schedule = '{$Group->first_schedule}' AND cabinet={$FirstLesson->cabinet}"
			]);
		}

		public static function getCabinetIds($id_group)
		{
			$cabinet_ids = [];
			$result = dbConnection()->query("SELECT id_cabinet FROM group_time WHERE id_group={$id_group} GROUP BY id_cabinet");
			while($row = $result->fetch_object()) {
				$cabinet_ids[] = $row->id_cabinet;
			}
			return $cabinet_ids;
		}

		/**
		 * Получить даты проведенных занятий.
		 * @time-refactored @time-checked
		 */
		public function getPastLessons()
		{
			return VisitJournal::findAll([
				"condition" => "id_group={$this->id} AND year={$this->year}",
				"group"		=> "lesson_date",
			]);
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
			// @refactored
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
		 * ставить пиктограммку в конце например типа восклицательный значок.
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
				// @refactored – используется в тесте
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
         * @refactored
         */
		public function inSchedule($id_group, $date, $withoutCancelled = false)
		{
			// @refactored
			return GroupSchedule::find([
				"condition" => "id_group=$id_group AND date='$date'".($withoutCancelled ? " AND cancelled = 0 " : "")
			]);
		}

		/**
		 * Получить отсутствующие занятие за последние 7 дней
		 */
		public static function getLastWeekMissing($total_count = false)
		{
			$date = date('Y-m-d', strtotime('yesterday'));

			foreach(range(1, 10) as $i) {
				$GroupSchedule = GroupSchedule::findAll([
					"condition" => "date='$date' AND id_group > 0 AND cancelled=0"
				]);

				foreach ($GroupSchedule as $Schedule) {
					// Проверяем было ли это занятие
					$was_lesson = VisitJournal::find([
						"condition" => "lesson_date = '" . $Schedule->date . "' AND id_group=" . $Schedule->id_group
					]);

					// если занятия не было, добавляем в ошибки
					if (! $was_lesson) {
						$return[$date]++;
						$total_missing_count++;
					}
				}
				$date = date('Y-m-d', strtotime($date . "-$i day"));
			}

			return $total_count ? $total_missing_count : $return;
		}

		/*====================================== ФУНКЦИИ КЛАССА ======================================*/


		// @test – используется только в TestController
		// @refactored
		public function daysBeforeExam()
		{
			if ($this->grade == 10) {
				return false;
			}

			// Получаем дату последнего запланированного занятия
			// @refactored
			$GroupSchedule = GroupSchedule::find([
				"condition" => "id_group={$this->id} AND cancelled=0",
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
				"condition" => "date >= '{$this->year}-09-01' AND date <= '" . ($this->year + 1) . "-08-31'
									AND id_group=".$this->id.($withoutCancelled ? ' AND cancelled = 0 ' : ''),
				"order"		=> "date ASC, time ASC",
			]);

		}

		// @depricated – нигде не используется, если использовать, то не забыть про cancelled
		public function getFutureSchedule()
		{
			return GroupSchedule::findAll([
				"condition" => "id_group=".$this->id." AND UNIX_TIMESTAMP(CONCAT_WS(' ', date, time)) > UNIX_TIMESTAMP(NOW())",
				"order"		=> "date ASC, time ASC",
			]);
		}

		// @refactored
		public function countFutureSchedule()
		{
			// @refactored
			return GroupSchedule::count([
				"condition" => "id_group=".$this->id." AND UNIX_TIMESTAMP(CONCAT_WS(' ', date, time)) > UNIX_TIMESTAMP(NOW()) AND cancelled=0",
			]);
		}

		public function countFutureScheduleStatic($id)
		{
			// @refactored
			return GroupSchedule::count([
				"condition" => "id_group=".$id." AND UNIX_TIMESTAMP(CONCAT_WS(' ', date, time)) > UNIX_TIMESTAMP(NOW()) AND cancelled=0",
			]);
		}

		// @depricated – нигде не используется, если использовать, то не забыть про cancelled
		public function getPastSchedule()
		{
			return GroupSchedule::findAll([
				"condition" => "id_group=".$this->id." AND UNIX_TIMESTAMP(CONCAT_WS(' ', date, time)) < UNIX_TIMESTAMP(NOW())",
				"order"		=> "date ASC, time ASC",
			]);
		}

		// @depricated – нигде не используется, если использовать, то не забыть про cancelled

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

		// @delete – удалить после обновления крона
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
			// @REFACTORED
			$paid = GroupSchedule::count([
				"condition" => "is_free=0 AND cancelled=0 AND id_group=".$this->id,
			]);

			// @REFACTORED
			$free = GroupSchedule::count([
				"condition" => "is_free=1 AND cancelled=0 AND id_group=".$this->id,
			]);

			return [
				'free' => $free,
				'paid' => $paid,
			];
		}

		public function countScheduleStatic($id)
		{
			// @REFACTORED
			$paid = GroupSchedule::count([
				"condition" => "is_free=0 AND cancelled=0 AND id_group=".$id,
			]);

			// @REFACTORED
			$free = GroupSchedule::count([
				"condition" => "is_free=1 AND cancelled=0 AND id_group=".$id,
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

		public function getPastScheduleCountCached()
		{
			if (LOCAL_DEVELOPMENT) {
				return VisitJournal::getLessonCount($this->id);
			}

			$return = memcached()->get("GroupPastScheduleCount[{$this->id}]");

			if (memcached()->getResultCode() != Memcached::RES_SUCCESS) {
				memcached()->set("GroupPastScheduleCount[{$this->id}]", VisitJournal::getLessonCount($this->id), 5 * 24 * 3600);
			}
			return $return;
		}

		public static function getScheduleCountCachedStatic($id_group)
		{
			if (LOCAL_DEVELOPMENT) {
				return 0;
			}


			$return = memcached()->get("GroupScheduleCount[{$id_group}]");

			if (memcached()->getResultCode() != Memcached::RES_SUCCESS) {
				$return = Group::countScheduleStatic($id_group);
				memcached()->set("GroupScheduleCount[{$id_group}]", $return, 5 * 24 * 3600);
			}
			return $return;
		}

		public static function getPastScheduleCountCachedStatic($id_group)
		{
			if (LOCAL_DEVELOPMENT) {
				return VisitJournal::getLessonCount($id_group);
			}

			$return = memcached()->get("GroupPastScheduleCount[{$id_group}]");

			if (memcached()->getResultCode() != Memcached::RES_SUCCESS) {
				memcached()->set("GroupPastScheduleCount[{$id_group}]", VisitJournal::getLessonCount($id_group), 5 * 24 * 3600);
			}
			return $return;
		}



		/**
		 * Получить дату первого занятия из расписания.
		 */
		public function getFirstSchedule($unix = true)
		{
			// @refactored
			$GroupFirstSchedule =  GroupSchedule::find([
				"condition" => "id_group={$this->id} AND cancelled = 0",
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
		 */
		public function getFirstScheduleStatic($id_group)
		{
			// @refactored
			$GroupFirstSchedule =  GroupSchedule::find([
				"condition" => "id_group={$id_group} AND cancelled = 0",
				"order"		=> "date ASC"
			]);

			return $GroupFirstSchedule ? strtotime($GroupFirstSchedule->date . " " . $GroupFirstSchedule->time) . "000" : false;
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
		public static function getFirstLesson($id_group, $from_today = false)
		{
			// @refactored
			return GroupSchedule::find([
				"condition" => "id_group={$id_group} AND cancelled=0 " . ($from_today ? " AND date >= '" . date("Y-m-d") . "'" : ""),
				"order"		=> "date ASC"
			]);
		}

		/**
		 * Добавить информацию по кабинетам в группу
		 */
		private static function _addCabinets(&$Group)
		{
			$Group->cabinet_ids = Group::getCabinetIds($Group->id);
			foreach($Group->cabinet_ids as $id_cabinet) {
                if (empty($id_cabinet)) {
                    continue;
                }
				$Group->cabinets[] = Cabinet::getBlock($id_cabinet);
			}
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

			// @refactored
			return dbConnection()->query("
				SELECT g.id FROM groups g
					LEFT JOIN students s ON s.id IN (" . implode(",", $this->students) . ")
					LEFT JOIN contracts c ON c.id_student = s.id
					LEFT JOIN contract_subjects cs ON cs.id_contract = c.id
				WHERE g.id = {$this->id} AND (c.id_contract=0 OR c.id_contract IS NULL) AND cs.count>40 AND cs.id_subject={$this->id_subject}
				LIMIT 1
			")->num_rows;
		}

		/*
		 * Получить данные для основного модуля
		 * $id_student – если просматриваем отзывы отдельного ученика
		 * @time-refactored @time-checked
		 */
		public static function getData($page)
		{
			if (!$page) {
				$page = 1;
			}

			// С какой записи начинать отображение, по формуле
			$start_from = ($page - 1) * Group::PER_PAGE;

			$search = isset($_COOKIE['groups']) ? json_decode($_COOKIE['groups']) : (object)[];

			// получаем данные
			$query = static::_generateQuery($search, "g.id, g.id_subject, g.grade, g.level, g.students, g.id_teacher, g.ended, g.ready_to_start", " GROUP BY g.id");
			$result = dbConnection()->query($query . " LIMIT {$start_from}, " . Group::PER_PAGE);

			while ($row = $result->fetch_object()) {
				$Group = $row;

				if ($Group->id_teacher) {
					$Group->Teacher = Teacher::getLight($Group->id_teacher, ['comment']);
				}

				static::_addCabinets($Group);

				$Group->students = empty($Group->students) ? [] : explode(',', $Group->students);

				$Group->first_schedule 		= Group::getFirstScheduleStatic($Group->id);
				$Group->past_lesson_count 	= Group::getPastScheduleCountCachedStatic($Group->id);;
				$Group->schedule_count 		= Group::getScheduleCountCachedStatic($Group->id);
				$Group->day_and_time 		= Group::getDayAndTime($Group->id);

				if ($Group->ready_to_start) {
					$Group->notified_students_count = static::getNotifiedStudentsCount($Group);
				}

				$data[] = $Group;
			}

			// counts

			$query = dbConnection()->query(static::_generateQuery($search, "g.id_teacher", " GROUP BY g.id_teacher"));
			$teacher_ids = [];
			while ($row = $query->fetch_object()) {
				$teacher_ids[] = $row->id_teacher;
			}

			return [
				'teacher_ids'	=> $teacher_ids,
				'data' 			=> $data,
				'counts' 		=> $counts,
			];
		}

		// @time-refactored @time-checked
		private static function _generateQuery($search, $select, $ending = '')
		{
			// " . ((! empty($search->time_ids) || !isBlank($search->id_branch) || $search->cabinet) ? " JOIN group_time gt ON (g.id = gt.id_group " . . ")" : "") . "
			// 		AND (" . implode(' OR ', array_map(function($id_time) { return "gt.id_time=$id_time"; }, $search->time_ids)) . "))"
			$main_query = "
				FROM groups g
				" . ((! empty($search->time_ids) || !isBlank($search->id_branch) || $search->cabinet) ? 'JOIN group_time gt ON (g.id = gt.id_group '
					. (! empty($search->time_ids) ? " AND (" . implode(' OR ', array_map(function($id_time) { return "gt.id_time=$id_time"; }, $search->time_ids)) . ")" : '')
					. ($search->cabinet ? " AND (gt.id_cabinet = {$search->cabinet})" : "")
				. ')' : '' )
				. ((! isBlank($search->id_branch)) ? " JOIN cabinets c ON (c.id = gt.id_cabinet AND c.id_branch={$search->id_branch})" : '')
				. " WHERE true "
				. (! isBlank($search->year) ? " AND g.year={$search->year}" : "")
				. ((! isBlank($search->id_teacher) && empty($ending)) ? " AND g.id_teacher={$search->id_teacher}" : "")
				. (! isBlank($search->subjects) ? " AND g.id_subject IN (". (is_array($search->subjects) ? implode(",", $search->subjects) : $search->subjects) .") " : "")
				. (! isBlank($search->grade) ? " AND g.grade={$search->grade}" : "")
				. (! isBlank($search->level) ? $search->level == GroupLevels::EXTERNAL ? " AND g.level=".GroupLevels::EXTERNAL : " AND g.level <> ".GroupLevels::EXTERNAL : "");
			return "SELECT " . $select . $main_query . $ending;

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
		 * Получить время занятий группы.
		 *
		 * $sort_by_days – вернуть по дням недели
		 *
		 * @refactored
		 */
		public static function getDayAndTime($id_group, $sort_by_days = true)
		{
			$GroupTime = GroupTime::findAll([
				"condition"	=> "id_group=" . $id_group,
			]);

			if ($sort_by_days) {
				$return = [];
				foreach ($GroupTime as $GroupTimeData) {
					$return[$GroupTimeData->time->day][] = $GroupTimeData;
				}
				return $return;
			} else {
				return $GroupTime;
			}
		}
	}

	class GroupSchedule extends Model
	{
		public static $mysql_table	= "group_schedule";

		const PER_PAGE = 100; // Сколько отображать на странице списка


		public function __construct($array)
		{
			parent::__construct($array);
			// @time-refactored @time-checked

			if ($this->time) {
				$this->time = mb_strimwidth($this->time, 0, 5);
				if ($this->time == "00:00") {
					$this->time = null; // чтобы отображало "не установлено"
				}
			}

			$this->isUnplanned = $this->isUnplanned();

			$this->was_lesson = VisitJournal::find(["condition" => "id_group={$this->id_group} AND lesson_date='{$this->date}'"]) ? true : false;
		}

        /**
         * незапланированное @have-to-refactor
         */
		public function isUnplanned()
		{
            $Time = Time::getLight();

			$GroupTimeData = GroupTime::findAll([
				"condition" => "id_group=" . $this->id_group,
			]);

			$day_of_the_week = date("w", strtotime($this->date));
			if ($day_of_the_week == 0) {
				$day_of_the_week = 7;
			}

			$is_planned = false;
			foreach ($GroupTimeData as $GroupTime) {
				preType([$day_of_the_week, Time::getDay($GroupTime->id_time), $this->time, $Time[$GroupTime->id_time]]);
				if ($day_of_the_week == Time::getDay($GroupTime->id_time) && $this->time == $Time[$GroupTime->id_time]) {
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
    }


	class GroupTime extends Model
	{
		public static $mysql_table	= "group_time";

		public function __construct($array)
		{
			parent::__construct($array);
			if ($this->id_time) {
				$this->time = Time::findById($this->id_time);
			}
		}
		/**
		 * Добавить время группы
		 * @refactored
		 */
		public static function addData($data, $id_group)
		{
			self::deleteAll([
				"condition" => "id_group=$id_group"
			]);

			foreach ($data as $day => $day_data) {
				foreach ($day_data as $d) {
					$GroupTime = new self([
						"id_group"	=> $id_group,
						"id_time"	=> $d['id_time'],
						"id_cabinet"=> $d['id_cabinet'],
					]);
					$GroupTime->save();
				}
			}
		}
	}

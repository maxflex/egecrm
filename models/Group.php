<?php
	class Group extends Model
	{
		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/

		public static $mysql_table	= "groups";

		protected $_inline_data = ["students"];

		const DURATION = [135];
		const PER_PAGE = 1000;


		/*====================================== СИСТЕМНЫЕ ФУНКЦИИ ======================================*/

		public function __construct($array, $light = false)
		{
			parent::__construct($array);

			if (empty($this->students[0])) {
				$this->students = [];
			}

            $this->first_schedule 		= $this->getFirstSchedule();

            // @notice  порядок first_schedule - notified_students важен.
            if (!$light) {
                $this->notified_students_count = static::getNotifiedStudentsCount($this);
            }


            if ($this->id_teacher) {
				$this->Teacher = Teacher::getLight($this->id_teacher, ['comment']);
			}

			if (!$this->isNewRecord) {
				static::assignGrade($this);
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

			$this->day_and_time 		= $this->getDayAndTime($this->id);
		}

		/*====================================== СТАТИЧЕСКИЕ ФУНКЦИИ ======================================*/

		public static function assignGrade($group)
		{
			if ($group->grade == Grades::EXTERNAL) {
				$group->grade_label = 'экстернат';
				$group->grade_short = 'Э';
			} else {
				$group->grade_label = $group->grade . ' класс';
				$group->grade_short = $group->grade;
			}
		}

		// @time-refactored @time-checked
		public static function getNotifiedStudentsCount($Group)
		{
			$FirstLesson = Group::getFirstLesson($Group->id, true);
			if (!count($Group->students) || !$Group->id_subject || !$Group->first_schedule || !$FirstLesson->cabinet) {
				return 0;
			}
			return dbConnection()->query("
				SELECT COUNT(DISTINCT id_student) AS c FROM group_sms
				WHERE id_student IN (" . implode(",", $Group->students) . ") AND id_subject = {$Group->id_subject}
					AND first_schedule = '{$Group->first_schedule}' AND cabinet={$FirstLesson->cabinet}
			")->fetch_object()->c;
		}

		public static function getCabinetIds($id_group = false)
		{
			$cabinet_ids = [];
			$result = dbConnection()->query("SELECT id_cabinet FROM group_time WHERE id_group={$id_group} GROUP BY id_cabinet");
            if ($result->num_rows) {
                while($row = $result->fetch_object()) {
                    $cabinet_ids[] = $row->id_cabinet;
                }
            }
			return $cabinet_ids;
		}

		/**
		 * Получить даты проведенных занятий.
		 * @time-refactored @time-checked @schedule-refactored
		 */
		public function getPastLessons()
		{
			return VisitJournal::findAll([
				"condition" => "id_group={$this->id} AND type_entity='TEACHER'",
			]);
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
				WHERE id_teacher > 0
				GROUP BY id_teacher
			");

			$teacher_ids = [];

			while ($row = $result->fetch_object()) {
				$teacher_ids[] = $row->id_teacher;
			}

			return $teacher_ids;
		}

		/**
		 * Получить отсутствующие занятие за последние N дней
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
					// если занятия не было, добавляем в ошибки
					// @schedule-refactored
					if (! $Schedule->was_lesson) {
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
			return static::_getDaysBeforeExam($this);
		}

		public static function _getDaysBeforeExam($Group)
        {
            if ($Group->grade == 10) {
                return false;
            }

            // Получаем дату последнего запланированного занятия
            // @refactored @schedule-refactored
            $GroupSchedule = GroupSchedule::find([
                "condition" => "id_group={$Group->id} AND cancelled=0",
                "order"		=> "date DESC",
            ]);

            $exam_year = $Group->year + 1;
            // Дату экзамена
            $ExamDay = ExamDay::find([
                "condition" => "id_subject={$Group->id_subject} AND grade={$Group->grade} AND date like '%{$exam_year}'"
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
		 * @time-refactored
		 */
		public function getSchedule($withoutCancelled=false)
		{
			return GroupSchedule::findAll([
				"condition" => "date >= '{$this->year}-09-01' AND date <= '" . ($this->year + 1) . "-08-31'
									AND id_group=".$this->id.($withoutCancelled ? ' AND cancelled = 0 ' : ''),
				"order"		=> "date ASC, time ASC",
			]);

		}

		// Получить будущее расписание
		// @time-refactored
		public function getFutureSchedule($with_cabinets = false)
		{
			$GroupSchedule = GroupSchedule::findAll([
				"condition" => "id_group=" . $this->id . " AND UNIX_TIMESTAMP(CONCAT_WS(' ', date, time)) > UNIX_TIMESTAMP(NOW())",
				"order"		=> "date ASC, time ASC",
			]);
            if ($with_cabinets) {
                foreach($GroupSchedule as $GS) {
                    $GS->cabinet_number = Cabinet::getField($GS->cabinet, 'number');
                }
            }
            return $GroupSchedule;
		}

		// @refactored
		// @schedule-refactored
		public function countFutureSchedule()
		{
			// @refactored
			return GroupSchedule::count([
				"condition" => "id_group=".$this->id." AND UNIX_TIMESTAMP(CONCAT_WS(' ', date, time)) > UNIX_TIMESTAMP(NOW()) AND cancelled=0",
			]);
		}

		public function countFutureScheduleStatic($id)
		{
			// @refactored @schedule-refactored
			return GroupSchedule::count([
				"condition" => "id_group=".$id." AND UNIX_TIMESTAMP(CONCAT_WS(' ', date, time)) > UNIX_TIMESTAMP(NOW()) AND cancelled=0",
			]);
		}

		// @depricated – нигде не используется, если использовать, то не забыть про cancelled
		// @schedule-refactored
		public function getPastSchedule()
		{
			return GroupSchedule::findAll([
				"condition" => "id_group=".$this->id." AND UNIX_TIMESTAMP(CONCAT_WS(' ', date, time)) < UNIX_TIMESTAMP(NOW())",
				"order"		=> "date ASC, time ASC",
			]);
		}


        /**
         * @schedule-refactored
         */
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

        /**
         * @schedule-refactored
         */
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
				return ['paid' => 32, 'free' => 1];
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
				"order"		=> "date ASC, time ASC"
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
				"order"		=> "date ASC, time ASC"
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
		 * @schedule-refactored
		 */
		public static function getFirstLesson($id_group, $from_today = false)
		{
			// @refactored
			return GroupSchedule::find([
				"condition" => "id_group={$id_group} AND cancelled=0 " . ($from_today ? " AND date >= '" . date("Y-m-d") . "'" : ""),
				"order"		=> "date ASC, time ASC"
			]);
		}

		/**
		 * Добавить информацию по кабинетам в группу
		 */
		private static function _addCabinets(&$Group)
		{
			$Group->cabinet_ids = Group::getCabinetIds($Group->id);
			// для "болото" не отображаем кабинет
			if ($Group->is_dump) {
				$cabinet = Cabinet::getBlock($Group->cabinet_ids[0]);
				$cabinet['label'] = $cabinet['short'];
				$Group->cabinets[] = $cabinet;
			} else {
				foreach($Group->cabinet_ids as $id_cabinet) {
	                if (empty($id_cabinet)) {
	                    continue;
	                }
					$Group->cabinets[] = Cabinet::getBlock($id_cabinet);
				}
			}
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
			$query = static::_generateQuery($search, "g.id, g.id_subject, g.grade, g.level, g.students, g.id_teacher, g.ended, g.year, g.is_dump, g.ready_to_start", " group by g.id");
			$result = dbConnection()->query($query);

			while ($row = $result->fetch_object()) {
				$Group = $row;

				if ($Group->id_teacher) {
					$Group->Teacher = Teacher::getLight($Group->id_teacher, ['comment']);
				}

				static::_addCabinets($Group);
                $Group->days_before_exam = static::_getDaysBeforeExam($Group);

				$Group->students = empty($Group->students) ? [] : explode(',', $Group->students);

				$Group->first_schedule 		= Group::getFirstScheduleStatic($Group->id);
				$Group->past_lesson_count 	= Group::getPastScheduleCountCachedStatic($Group->id);;
				$Group->schedule_count 		= Group::getScheduleCountCachedStatic($Group->id);
				$Group->day_and_time 		= Group::getDayAndTime($Group->id);
				static::assignGrade($Group);

//				if ($Group->ready_to_start) {
					$Group->notified_students_count = static::getNotifiedStudentsCount($Group);
//				}

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
				. (! isBlank($search->contract_signed) ? " AND g.contract_signed={$search->contract_signed}" : "")
				. (! isBlank($search->year) ? " AND g.year={$search->year}" : "")
				. ((! isBlank($search->id_teacher) && empty($ending)) ? " AND g.id_teacher={$search->id_teacher}" : "")
				. (! isBlank($search->subjects) ? " AND g.id_subject IN (". (is_array($search->subjects) ? implode(",", $search->subjects) : $search->subjects) .") " : "")
				. (! isBlank($search->grades) ? " AND g.grade IN (". (is_array($search->grades) ? implode(",", $search->grades) : $search->grades) .") " : "");

            // echo("SELECT " . $select . $main_query . $ending);
			return "SELECT " . $select . $main_query . $ending;

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

		public function inGroup($id_student) {
		    return in_array($id_student, $this->students);
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
            $this->was_lesson = VisitJournal::count(["condition" => "id_group={$this->id_group} AND lesson_date='{$this->date}' AND lesson_time='{$this->time}' AND type_entity='TEACHER'"]) ? true : false;
			if ($this->time) {
				$this->time = mb_strimwidth($this->time, 0, 5);
				if ($this->time == "00:00") {
					$this->time = null; // чтобы отображало "не установлено"
				}
			}
            $this->date_time = $this->date . ' ' . $this->time;
			$this->isUnplanned = $this->isUnplanned();
		}

        /**
         * Добавить группу занятия в экземпляр
         */
        public function getGroup()
        {
            $this->Group = Group::find([
				"condition" => "id={$this->id_group} AND ended=0"
			]);
        }

        /**
         * Добавить занятие из журнала в экземпляр
         */
        public function getLesson()
        {
            $this->Lesson = VisitJournal::find(["condition" => "id_group={$this->id_group} AND lesson_date='{$this->date}' AND lesson_time='{$this->time}:00' AND type_entity='TEACHER'"]);
        }

        /**
         * незапланированное @have-to-refactor
         * @schedule-refactored
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

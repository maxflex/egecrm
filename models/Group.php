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

            $this->first_lesson_date 		= self::getFirstLessonDate($this->id);

            // @notice  порядок first_lesson_date - notified_students важен.
            if (!$light) {
                $this->notified_students_count = static::getNotifiedStudentsCount($this);
            }


            if ($this->id_teacher) {
				$this->Teacher = Teacher::getLight($this->id_teacher, ['comment']);
			}

			if (! $this->isNewRecord) {
				static::assignGrade($this);
				$this->lesson_count = $this->getLessonCount($this->id);

				if ($this->grade && $this->id_subject && !$this->ended && $this->lesson_count->all) {
					$this->days_before_exam = $this->daysBeforeExam();
				}

				static::_addCabinets($this);
			}

			if (! $this->student_statuses) {
				$this->student_statuses = [];
			}

			$this->day_and_time 		= $this->getDayAndTime($this->id);
		}

		/*====================================== СТАТИЧЕСКИЕ ФУНКЦИИ ======================================*/

		public static function getLight($id_group)
		{
			$group = dbConnection()->query("select * from groups where id={$id_group}")->fetch_object();
			$group->students = explode(',', $group->students);
			return $group;
		}

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

		public static function getNotifiedStudentsCount($Group)
		{
			$FirstLesson = Group::getFirstLesson($Group->id, true);
			if (!count($Group->students) || !$Group->id_subject || !$Group->first_lesson_date || !$FirstLesson->cabinet) {
				return 0;
			}

			return dbConnection()->query("
				SELECT COUNT(DISTINCT id_student) AS c FROM group_sms
				WHERE id_student IN (" . implode(",", $Group->students) . ") AND id_subject = {$Group->id_subject}
					AND first_lesson_date = '{$Group->first_lesson_date}' AND cabinet={$FirstLesson->cabinet}
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

			$total_missing_count = 0;
			$return = [];

			foreach(range(1, 10) as $i) {
				$missing_count = VisitJournal::count([
					"condition" => "lesson_date='$date' AND cancelled=0 AND " . VisitJournal::PLANNED_CONDITION
				]);

				if ($missing_count) {
					$return[$date] = $missing_count;
					$total_missing_count += $missing_count;
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
            $LastPlanned = VisitJournal::getGroupLessons($Group->id, 'find', 'DESC');

            $exam_year = $Group->year + 1;
            // Дату экзамена
            $ExamDay = ExamDay::find([
                "condition" => "id_subject={$Group->id_subject} AND grade={$Group->grade} AND YEAR(`date`)='{$exam_year}'"
            ]);

            $diff = strtotime($ExamDay->date) - strtotime($LastPlanned->lesson_date);
            return floor($diff/(60*60*24)) - 1;
        }

		// Получить запланированные уроки
		public function getPlannedLessons()
		{
			$Lessons = VisitJournal::findAll([
				'condition' => "id_group=" . $this->id . " AND " . VisitJournal::PLANNED_CONDITION,
				'order'		=> "lesson_date ASC, lesson_time ASC",
			]);

            foreach($Lessons as &$Lesson) {
                $Lesson->cabinet_number = Cabinet::getField($Lesson->cabinet, 'number');
            }

            return $Lessons;
		}

        /**
         * @schedule-refactored
         */
		public function getLessonCount($id)
		{
			$conducted = VisitJournal::count([
				"condition" => "cancelled=0 AND type_entity='TEACHER' AND id_group=".$id,
			]);

			$planned = VisitJournal::count([
				"condition" => "cancelled=0 AND " . VisitJournal::PLANNED_CONDITION . " AND id_group=".$id,
			]);

			$all = $conducted + $planned;

			return (object)compact('conducted', 'planned', 'all');
		}

		/**
		 * Получить дату первого занятия из расписания.
		 */
		public static function getFirstLessonDate($id_group)
		{
			// @refactored
			$FirstLesson = self::getFirstLesson($id_group);

			return $FirstLesson ? strtotime($FirstLesson->lesson_date . " " . $FirstLesson->lesson_time) . "000" : false;
		}

		/**
		 * Получить первое занятие
		 * $from_today – первое относительно сегодняшнего дня (ближайшее следующее)
		 */
		public static function getFirstLesson($id_group, $from_today = false)
		{
			return VisitJournal::find([
				"condition" => "id_group={$id_group} AND cancelled=0 " . ($from_today ? " AND lesson_date >= '" . now(true) . "'" : ""),
				"order"		=> "lesson_date ASC, lesson_time ASC"
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
		 */
		public static function getData($search = null)
		{
			if (! $search) {
				$search = isset($_COOKIE['groups']) ? json_decode($_COOKIE['groups']) : (object)[];
			} else {
				$search = (object)$search;
			}

			// если никаких фильтров не установлено, не загружать
			if (count(array_filter((array)$search)) == 0) {
				return returnJsonAng(['data' => -1]);
			}

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

				$Group->first_lesson_date 		= Group::getFirstLessonDate($Group->id);
				$Group->lesson_count 		= Group::getLessonCount($Group->id);
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
				. " WHERE is_unplanned=0 "
				. (! isBlank($search->contract_signed) ? " AND g.contract_signed={$search->contract_signed}" : "")
				. (! isBlank($search->year) ? " AND g.year={$search->year}" : "")
				. (! isBlank($search->id_teacher) ? " AND g.id_teacher={$search->id_teacher}" : "")
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



	/**
	 * GROUP TIME CLASS
	 */
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

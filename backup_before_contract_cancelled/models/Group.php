<?php
	class Group extends Model
	{
		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/

		public static $mysql_table	= "groups";
		
		protected $_inline_data = ["students"];
		
		
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
				
				if ($this->Teacher) {
					$this->teacher_agreed = $this->Teacher->agreedToBeInGroup($this->id);
				}
				
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
					"condition" => "id_group={$this->id} AND DATE_FORMAT(date, '%w') NOT IN (" . implode(',', $days) . ")"
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
						"condition" => "id_group={$this->id} AND (" . implode(" OR ", $sql) . ")"
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
		public function inSchedule($id_group, $date)
		{
			return GroupSchedule::find([
				"condition" => "id_group=$id_group AND date='$date'"
			]);
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
				
				if ($Group->id_teacher) {
					if (Teacher::agreedToBeInGroupStatic($Group->id_teacher, $Group->id)) {
						$total_teachers_agreed++;			
					}
				}
				if ($Group->students) {
					$total_group_students += count($Group->students);
					foreach ($Group->students as $id_student) {
						if (Student::agreedToBeInGroupStatic($id_student, $Group->id)) {
							$total_students_agreed++;		
						}
						if (Student::notifiedInGroupStatic($id_student, $Group->id)) {
							$total_students_notified++;		
						}
					}
				}
			}
			
			return [
				"total_group_students" 	=> $total_group_students,
				"total_students_agreed"	=> $total_students_agreed,
				"total_teachers_agreed"	=> $total_teachers_agreed,
				"total_students_notified" => $total_students_notified,
				"total_groups"			=> count($Groups),
			//	"total_witn_no_group"	=> Student::countSubjectsWithoutGroup(),
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
		
		public function getSchedule()
		{
			return GroupSchedule::findAll([
				"condition" => "id_group=".$this->id,
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
		public function getPastScheduleBeforeEnd($minutes_to_end = 30)
		{
			$minutes = LESSON_LENGTH - $minutes_to_end;
			
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
			return GroupSchedule::count([
				"condition" => "id_group=".$this->id,
			]);
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
		 * 
		 */
		public function getFirstSchedule($unix = true)
		{
			$GroupFirstSchedule =  GroupSchedule::find([
				"condition" => "id_group={$this->id}",
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
		
		public static function getVocationDates()
		{
			$Vocations = self::findAll([
				"condition" => "id_group=0"
			]);
			
			$vocation_dates = [];
			
			foreach ($Vocations as $Vocation) {
				$vocation_dates[] = $Vocation->date;
			}
			
			return $vocation_dates;
		}
	}
	
	
	class GroupTeacherStatuses extends Model
	{
		# Список предметов
		const NBT 		= 1;
		const AWAITING	= 2;
		const AGREED	= 3;
		
		# Все предметы
		static $all = [
			self::NBT 		=> "нбт",
			self::AWAITING	=> "ожидает",
			self::AGREED	=> "согласен",
		];

		
		/**
		 * Если ученик присутствует в группе и вместе с этим у него стоит метка "согласен", 
		   если у этого ученика есть другие группы, то в них расписании у него соответствующий кирпичик должен быть красным.
		 * 
		 */
		public static function inRedFreetime($id_group, $day, $time, $id_teacher) 
		{
			return dbConnection()->query("
				SELECT g.id FROM group_agreement ga
					LEFT JOIN groups g ON g.id = ga.id_group
					LEFT JOIN group_time gt ON g.id = gt.id_group
				WHERE g.id != $id_group AND gt.time = '$time' AND gt.day = '$day' AND ga.id_status = ". self::AGREED ." AND ga.id_entity = $id_teacher
					AND ga.type_entity='TEACHER' AND (ga.id_entity = g.id_teacher AND g.id = ga.id_group)
				LIMIT 1
			")->num_rows;	
		}
	}
	
	class GroupStudentStatuses
	{
		# Список предметов
		const NBT 		= 1;
		const AWAITING	= 2;
		const AGREED	= 3;
		
		# Все предметы
		static $all = [
			self::NBT 		=> "нбт",
			self::AWAITING	=> "ожидает",
			self::AGREED	=> "согласен",
		];
		
		/**
		 * Если ученик присутствует в группе и вместе с этим у него стоит метка "согласен", 
		   если у этого ученика есть другие группы, то в них расписании у него соответствующий кирпичик должен быть красным.
		 * 
		 */
		public static function inRedFreetime($id_group, $day, $time, $id_student) 
		{
			return dbConnection()->query("
				SELECT g.id FROM group_agreement ga
					LEFT JOIN groups g ON g.id = ga.id_group
					LEFT JOIN group_time gt ON g.id = gt.id_group
				WHERE g.id != $id_group AND gt.time = '$time' AND gt.day = '$day' AND ga.id_status = ". self::AGREED ." AND ga.id_entity = $id_student
					AND ga.type_entity='STUDENT' AND (CONCAT(',', CONCAT(g.students, ',')) LIKE CONCAT('%,', ga.id_entity ,',%') AND g.id = ga.id_group)
			")->num_rows;	
		}
		
		public static function inRedFreetimeHalf($id_group, $day, $time, $id_student) 
		{
			return dbConnection()->query("
				SELECT g.id FROM groups g
					LEFT JOIN group_time gt ON g.id = gt.id_group
				WHERE g.id != $id_group AND gt.time = '$time' AND gt.day = '$day'
					 AND CONCAT(',', CONCAT(students, ',')) LIKE '%,{$id_student},%'
			")->num_rows;	
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
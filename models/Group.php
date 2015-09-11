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
			
			$this->Teacher	= Teacher::findById($this->id_teacher);
			
			if (!$this->isNewRecord) {
				$this->student_statuses = GroupStudentStatuses::getByGroupId($this->id);
			}
			
			if (!$this->student_statuses) {
				$this->student_statuses = [];
			}
			
			if ($this->id_branch) {
				$this->branch = Branches::getShortColoredById($this->id_branch);
			}
			
			if ($this->id_teacher) {
				$this->teacher_status = GroupTeacherStatuses::getStatus($this->id, $this->id_teacher);
				if ($this->teacher_status) {
					$this->teacher_status = $this->teacher_status->id_status;
				}
			}
			
			if (!$this->teacher_status) {
				$this->teacher_status = "";
			}
			
			
			
			$this->is_special = $this->isSpecial();
			$this->first_schedule = $this->getFirstSchedule();
			$this->day_and_time = $this->getDayAndTime();
			
			$this->Comments	= Comment::findAll([
				"condition" => "place='". Comment::PLACE_GROUP ."' AND id_place=" . $this->id,
			]);
		}
		
		/*====================================== СТАТИЧЕСКИЕ ФУНКЦИИ ======================================*/
		
		/*====================================== ФУНКЦИИ КЛАССА ======================================*/

		public function getSchedule()
		{
			return GroupSchedule::findAll([
				"condition" => "id_group=".$this->id
			]);
		}
		
		
		/**
		 * Получить дату первого занятия из расписания.
		 * 
		 */
		public function getFirstSchedule()
		{
			$GroupFirstSchedule =  GroupSchedule::find([
				"condition" => "id_group={$this->id}",
				"order"		=> "date ASC"	
			]);
			
			return $GroupFirstSchedule ? strtotime($GroupFirstSchedule->date) . "000" : false;
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
		
		public function __construct($array)
		{
			parent::__construct($array);
			
			if ($this->time) {
				$this->time = mb_strimwidth($this->time, 0, 5);
			}
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
		public static $mysql_table	= "group_teacher_statuses";
		
		# Список предметов
		const NBT 		= 1;
		const AWAITING	= 2;
		const AGREED	= 3;
		
		# Все предметы
		static $all = [
			self::NBT 		=> "нбт",
			self::AWAITING	=> "ожидает расписания",
			self::AGREED	=> "полностью согласен",
		];
		
		# Заголовок
		static $title = "статус";
		
		
		public function getStatus($id_group, $id_teacher) {
			return GroupTeacherStatuses::find([
				"condition" => "id_group=$id_group AND id_teacher=$id_teacher"
			]);
		}
		
		public function saveData($id_group, $id_status, $id_teacher)
		{
			if ($id_teacher) {
				GroupTeacherStatuses::deleteAll([
					"condition" => "id_group=$id_group"
				]);
				if ($id_status) {
					GroupTeacherStatuses::add([
						"id_group" 	=> $id_group,
						"id_teacher"=> $id_teacher,
						"id_status" => $id_status,
					]);
				}
			}
		}

	
		/**
		 * Если ученик присутствует в группе и вместе с этим у него стоит метка "полностью согласен", 
		   если у этого ученика есть другие группы, то в них расписании у него соответствующий кирпичик должен быть красным.
		 * 
		 */
		public static function inRedFreetime($id_group, $day, $time, $id_teacher) 
		{
			return dbConnection()->query("
				SELECT g.id FROM group_teacher_statuses gts
					LEFT JOIN groups g ON g.id = gts.id_group
					LEFT JOIN group_time gt ON g.id = gt.id_group
				WHERE g.id != $id_group AND gt.time = '$time' AND gt.day = '$day' AND gts.id_status = ". self::AGREED ." AND gts.id_teacher = $id_teacher
					AND (gts.id_teacher = g.id_teacher AND g.id = gts.id_group)
				LIMIT 1
			")->num_rows;	
		}
	}
	
	class GroupStudentStatuses extends Model
	{
		public static $mysql_table	= "group_student_statuses";
		
		# Список предметов
		const NBT 		= 1;
		const AWAITING	= 2;
		const AGREED	= 3;
		
		# Все предметы
		static $all = [
			self::NBT 		=> "нбт",
			self::AWAITING	=> "ожидает расписания",
			self::AGREED	=> "полностью согласен",
		];
		
		# Заголовок
		static $title = "статус";
		
		
		public function saveData($id_group, $student_statuses)
		{
			if (count($student_statuses)) {
				GroupStudentStatuses::deleteAll([
					"condition" => "id_group=$id_group"
				]);
				
				foreach ($student_statuses as $id_student => $id_status) {
					if (!$id_status) {
						continue;
					}
					
					GroupStudentStatuses::add([
						"id_group" 	=> $id_group,
						"id_student"=> $id_student,
						"id_status" => $id_status,
					]);
				}
			}
		}

	
		/**
		 * Если ученик присутствует в группе и вместе с этим у него стоит метка "полностью согласен", 
		   если у этого ученика есть другие группы, то в них расписании у него соответствующий кирпичик должен быть красным.
		 * 
		 */
		public static function inRedFreetime($id_group, $day, $time, $id_student) 
		{
			return dbConnection()->query("
				SELECT g.id FROM group_student_statuses gss
					LEFT JOIN groups g ON g.id = gss.id_group
					LEFT JOIN group_time gt ON g.id = gt.id_group
				WHERE g.id != $id_group AND gt.time = '$time' AND gt.day = '$day' AND gss.id_status = ". self::AGREED ." AND gss.id_student = $id_student
					AND (CONCAT(',', CONCAT(g.students, ',')) LIKE CONCAT('%,', gss.id_student ,',%') AND g.id = gss.id_group)
				LIMIT 1
			")->num_rows;	
		}
		
		public static function inRedFreetimeHalf($id_group, $day, $time, $id_student) 
		{
			return dbConnection()->query("
				SELECT g.id FROM groups g
					LEFT JOIN group_time gt ON g.id = gt.id_group
				WHERE g.id != $id_group AND gt.time = '$time' AND gt.day = '$day'
					 AND CONCAT(',', CONCAT(students, ',')) LIKE '%,{$id_student},%'
				LIMIT 1
			")->num_rows;	
		}
		
		public function getByGroupId($id_group)
		{
			$data = GroupStudentStatuses::findAll([
				"condition" => "id_group=$id_group"
			]);
			
			foreach ($data as $data_line) {
				$return[$data_line->id_student] = $data_line->id_status;
			}
			
			return $return;			
		}
	}
	
	class GroupTime extends Model
	{
		public static $mysql_table	= "group_time";	
		
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
						"time"		=> $time,
					]);
					
					$GroupTime->save();
				}
			}
		}

	}
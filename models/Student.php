<?php
	class Student extends Model
	{
	
		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/

		public static $mysql_table	= "students";
		
		protected $_inline_data = ["branches"]; // Предметы (в БД хранятся строкой "1, 2, 3" – а тут в массиве
		
		// Номера телефонов
		public static $_phone_fields = ["phone", "phone2", "phone3"];
		
		// тип маркера
		const MARKER_OWNER = "STUDENT";
		
		/*====================================== СИСТЕМНЫЕ ФУНКЦИИ ======================================*/
		
		public function __construct($array)
		{
			parent::__construct($array);
			
			// Добавляем связи
			$this->Representative	= Representative::findById($this->id_representative);
			$this->Passport			= Passport::findById($this->id_passport);
		}
		
		/*====================================== СТАТИЧЕСКИЕ ФУНКЦИИ ======================================*/
		
		public static function getName($last_name, $first_name, $middle_name, $order = 'fio')
		{
			if (empty(trim($last_name)) && empty(trim($first_name)) && empty(trim($middle_name))) {
				return "Неизвестно";
			}
			
			if ($last_name) {
				$name[0] = $last_name;
			}
			
			if ($first_name) {
				$name[1] = $first_name;
			}
			
			if ($middle_name) {
				$name[2] = $middle_name;
			}
			
			$order_values = [
				'f' => 0,
				'i' => 1,
				'o' => 2,
			];
			
			$name_ordered[] = $name[$order_values[$order[0]]];
			$name_ordered[] = $name[$order_values[$order[1]]];
			$name_ordered[] = $name[$order_values[$order[2]]];
			
			return implode(" ", $name_ordered);
		}
		
		/**
		 * Получить студентов с договорами.
		 * 
		 */
		public static function countWithoutContract()
		{
			$query = dbConnection()->query("
				SELECT s.id FROM students s
					LEFT JOIN requests r 	ON r.id_student = s.id
					LEFT JOIN contracts c 	ON c.id_student = s.id
				WHERE r.adding = 0 	AND c.id_student IS NULL
				GROUP BY s.id
			");
			
			return $query->num_rows;
		}
		
		
		public static function getWithContractByBranch($id_branch)
		{
			$query = dbConnection()->query("
				SELECT s.id FROM contracts c
				LEFT JOIN students s ON s.id = c.id_student
				WHERE CONCAT(',', CONCAT(s.branches, ',')) LIKE '%,{$id_branch},%' 
					AND (c.id_contract=0 OR c.id_contract IS NULL) GROUP BY s.id");
			
			while ($row = $query->fetch_array()) {
				if ($row["id"]) {
					$ids[] = $row["id"];
				}
			}
			
			return self::findAll([
				"condition"	=> "id IN (". implode(",", $ids) .")"
			]);
		}
		
		/**
		 * Получить студентов с договорами.
		 * 
		 * $only_active - только активные договоры
		 */
		public static function getWithContract($only_active = false)
		{
			$query = dbConnection()->query("SELECT id_student FROM contracts WHERE true "
				. ($only_active ? " AND cancelled=0 " : "") . Contract::ZERO_OR_NULL_CONDITION . " GROUP BY id_student");
			
			while ($row = $query->fetch_array()) {
				if ($row["id_student"]) {
					$ids[] = $row["id_student"];
				}
			}
			
			
			return self::findAll([
				"condition"	=> "id IN (". implode(",", $ids) .")"
			]);
		}
		
		/**
		 * Последний id договора активный
		 */
		public static function getWithActiveContract()
		{
			$query = dbConnection()->query("SELECT id_student FROM contracts 
				WHERE cancelled=0 " . Contract::ZERO_OR_NULL_CONDITION . " GROUP BY id_student LIMIT 1");
			
			while ($row = $query->fetch_array()) {
				if ($row["id_student"]) {
					$ids[] = $row["id_student"];
				}
			}
			
			
			return self::findAll([
				"condition"	=> "id IN (". implode(",", $ids) .")"
			]);
		}
		
		// Удаляет ученика и всё, что с ним связано
		public static function fullDelete($id_student)
		{
			$Student = Student::findById($id_student);
			
			# Договоры
			$contract_ids = Contract::getIds([
				"condition" => "id_student=$id_student"
			]);
			
			Contract::deleteAll([
				"condition" => "id IN (". implode(",", $contract_ids) .")"
			]);
			
			ContractSubject::deleteAll([
				"condition" => "id_contract IN (". implode(",", $contract_ids) .")"
			]);
			
			# Свободное время
			Freetime::deleteAll([
				"condition" => "id_student=$id_student"
			]);
			
			# Метки
			Marker::deleteAll([
				"condition" => "id_owner=$id_student AND owner='STUDENT'"
			]);
			
			# Платежи
			Payment::deleteAll([
				"condition" => "id_student=$id_student"
			]);
			
			if ($Student->id_passport) {
				Payment::deleteAll([
					"condition" => "id={$Student->id_passport}"
				]);
			}
			
			if ($Student->id_representative) {
				Representative::deleteAll([
					"condition" => "id={$Student->id_representative}"
				]);
			}
			
			$Student->delete();
		}
		
		public static function createEmptyRequest($id_student)
		{
			return Request::add([
				"id_student" => $id_student,
			]);
		}
		
		/*====================================== ФУНКЦИИ КЛАССА ======================================*/
		
		public function beforeSave()
		{
			// Очищаем номера телефонов
			foreach (static::$_phone_fields as $phone_field) {
				$this->{$phone_field} = cleanNumber($this->{$phone_field});
			}
			
			if ($this->isNewRecord) {
				if (!LOCAL_DEVELOPMENT) {
					// кеш количества учеников без договоров обновляется только при создании нового ученика
					// т.е. если существующему ученику добавить договор, количество не отнимется до создания нового ученика
					memcached()->set("TotalStudentsWithNoContract", Student::countWithoutContract(), 3600 * 24 * 30);
				}
			}
		}
		
		/**
		 * Добавить паспорт.
		 * 
		 * $save - сохранить новое поле?
		 */
		public function addPassport($Passport, $save = false)
		{
			$this->Passport 		= $Passport;
			$this->id_passport		= $Passport->id;
			
			if ($save) {
				$this->save("id_passport");
			}
		}
		
		public function agreedToBeInGroup($id_group)
		{
			return GroupStudentStatuses::count([
				"condition" => "id_student=" . $this->id . " AND id_group=" . $id_group . " AND id_status=" . GroupStudentStatuses::AGREED
			]) > 0 ? true : false;
		}
		
		/**
		 * Сколько номеров установлено.
		 * 
		 */
		public function phoneLevel()
		{
			if (!empty($this->phone3)) {
				return 3;
			} else
			if (!empty($this->phone2)) {
				return 2;
			} else {
				return 1;
			}
		}
		
		public function getComments()
		{
			return Comment::findAll([
				"condition"	=> "place='". Comment::PLACE_STUDENT ."' AND id_place=". $this->id
			]);
		}
		
		/**
		 * Получить договоры студента.
		 * 
		 */
		public function getContracts()
		{
			return Contract::findAll([
				"condition"	=> "deleted=0 AND id_student=" . $this->id
			]);	
		}
		
		
		public function getGroups()
		{
			return Group::findAll([
				"condition" => "CONCAT(',', CONCAT(students, ',')) LIKE '%,{$this->id},%'"
			]);
		}
		
		public function countGroups()
		{
			return Group::count([
				"condition" => "CONCAT(',', CONCAT(students, ',')) LIKE '%,{$this->id},%'"
			]);
		}
		
		/**
		 * Получить договоры студента без версий.
		 * 
		 */
		public function getActiveContracts()
		{
			return Contract::findAll([
				"condition"	=> "deleted=0  AND id_student=" . $this->id . Contract::ZERO_OR_NULL_CONDITION
			]);	
		}
		
		/**
		 * Получить постудний договор студента.
		 * 
		 */
		public function getLastContract($only_active = false)
		{
			return Contract::find([
				"condition"	=> "deleted=0 AND id_student=" . $this->id . ($only_active ? " AND cancelled=0 " : "") . Contract::ZERO_OR_NULL_CONDITION,
				"order"		=> "id DESC",
				"limit"		=> "1",
			]);	
		}
		
		
		/**
		 * Получить одну из заявок студента.
		 * 
		 */
		public function getRequest()
		{
			return Request::find([
				"condition" => "id_student={$this->id}"
			]);
		}
		
		public function isNotFull()
		{
			$Requsts = Request::findAll([
				"condition" => "id_student={$this->id}"
			]);
			
			/*
				Хотя бы в 1 заявке отсутствует дата создания
				Хотя бы в 1 заявке не указан источник
			*/
			foreach ($Requsts as $Requst) {
				if (emptyDate($Requst->date) || !$Requst->id_source) {
					return true;
				}
			}
			
			/*
				Если у ученика не заполнено хотя бы 1 из полей (класс, фио, хотя бы 1 телефон, хотя бы 1 из полей паспортных данных, дата рождения)
				Представитель: статус, фио, хотя бы 1 телефон, хотя бы 1 из полей в группе «паспорт»
				Не стоит ни одной метки (школа, факт)
				Ни одного филиала в удобных филиалах
			*/
			
//			preType($Requst);
//			echo $Requst->id_source;			
//			var_dump(!$Requst->id_source);
			
			if (
				   !$this->grade || !$this->first_name || !$this->last_name || !$this->middle_name || !$this->Representative->address
				|| !($this->phone || $this->phone2 || $this->phone3) || !$this->Passport->series || !$this->Passport->number  || !$this->Passport->date_birthday
				|| !$this->Representative->status || !$this->Representative->first_name || !$this->Representative->last_name || !$this->Representative->middle_name
				|| !($this->Representative->phone || $this->Representative->phone2 || $this->Representative->phone3) || !$this->Representative->Passport->series
				|| !$this->Representative->Passport->number || !$this->Representative->Passport->date_birthday || !$this->Representative->Passport->issued_by
				|| !$this->Representative->Passport->date_issued || !$this->Representative->Passport->address || ($this->getMarkers() < 2) || !$this->branches
			) {
				return true;
			} else {
				return false;
			}
		}
		
		/**
		 * ФИО.
		 * 
		 */
		public function fio()
		{
			return $this->last_name." ".$this->first_name." ".$this->middle_name;
		}
		
		/**
		 * Найти все платежи студента (клиента).
		 * 
		 */
		public function getPayments()
		{
			return Payment::findAll([
				"condition" => "deleted=0 AND id_student=" . $this->id
			]);
		}
		
		
		/**
		 * Получить свободное время ученика.
		 * 
		 */
		public function getFreetime()
		{
			$Freetime = Freetime::findAll([
				"condition"	=> "id_student=" . $this->id
			]);
			
			if (!$Freetime) {
				return [];
			}
			
			foreach ($Freetime as $FreetimeData) {
				$index = Freetime::getIndexByTime($FreetimeData->time);
				$return[$FreetimeData->id_branch][$FreetimeData->day][$index] = $FreetimeData->time;
			}
			
			return $return;
		}
		
				
		/**
		 * Получить свободное время ученика.
		 * $id_group -- для нахождения красных кирпичиков. если есть в группах кроме group_id
		 * 
		 */
		public function getGroupFreetime($id_group, $id_branch = false)
		{
			$Freetime = Freetime::findAll([
				"condition"	=> "id_student=" . $this->id
			]);
			
			if (!$Freetime) {
				return [];
			}
			
			foreach ($Freetime as $FreetimeData) {
				$return[$FreetimeData->id_branch][$FreetimeData->day][] = $FreetimeData->time;
				
				if (!in_array($FreetimeData->time, $return[0][$FreetimeData->day])) {
					$return[0][$FreetimeData->day][] = $FreetimeData->time;
				}
			}
			
			# красные кирпичи
			foreach (Freetime::$weekdays as $day => $schedule) {
				foreach ($schedule as $time) {
					if (GroupStudentStatuses::inRedFreetime($id_group, $day, $time, $this->id)) {
						if (!in_array($time, $return_red[$day])) {
							$return_red[$day][] = $time;
						}
					}
				}
			}
			
			# красные половинки
			foreach (Freetime::$weekdays as $day => $schedule) {
				foreach ($schedule as $time) {
					if (GroupStudentStatuses::inRedFreetimeHalf($id_group, $day, $time, $this->id)) {
						if (!in_array($time, $return_red_half[$day])) {
							$return_red_half[$day][] = $time;
						}
					}
				}
			}
			
			$return = [
				"freetime" 			=> $return,
				"freetime_red"		=> $return_red,
				"freetime_red_half" => $return_red_half,
			];
			
			if ($id_branch) {
				$orange = self::getOrange($id_group, $id_branch, $this->id, $return_red_half, $return_red);
				$return['freetime_orange'] 		= $orange['half'];
				$return['freetime_orange_full'] = $orange['full'];
			}
			
			return $return;
		}
		
		public static function getOrange($id_group, $id_branch, $id_student, $freetime_red, $freetime_red_full)
		{
			foreach (Freetime::$weekdays as $day => $schedule) {
				foreach ($schedule as $time) {
					// текущий кирпич не должен быть занят в другой группе у этого преподавателя
					if (!$time || in_array($time, $freetime_red[$day])) {
						continue;		
					}
					
					// текущий кирпич обязательно должен соседствовать с красным кирпичом в рамках одного дня
					$red_neighbour = false;
					
					$current_index = array_search($time, Freetime::$weekdays[$day]);
					
					# проверяем следующий день
					$red_neighbour_right 		= false;
					$red_neighbour_right_data 	= false;
					if ($current_index < 3) {
						$red_neighbour_right = in_array(Freetime::$weekdays[$day][$current_index + 1], $freetime_red[$day]);
						if ($red_neighbour_right) {
							// сохраняем данные найденного справа красного кирпича
							$red_neighbour_right_data = [
								"day" 	=> $day,
								"time"	=> Freetime::$weekdays[$day][$current_index + 1],
							];
						}
					}
					
					# проверяем предыдущий день
					$red_neighbour_left 	= false;
					$red_neighbour_left_data= false;
					if ($current_index > 0) {
						$red_neighbour_left = in_array(Freetime::$weekdays[$day][$current_index - 1], $freetime_red[$day]);
						if ($red_neighbour_left) {
							// сохраняем данные найденного слева красного кирпича
							$red_neighbour_left_data = [
								"day" 	=> $day,
								"time"	=> Freetime::$weekdays[$day][$current_index - 1],
							];
						}
					}
					// если нашелся красный сосед, идем дальше
					if ($red_neighbour_left || $red_neighbour_right) {
						// филиал текущей группы должен отличаться от филиала группы соседствующего красного кирпича
						if ($red_neighbour_left) {
							$is_orange = self::_branchDifferent($id_group, $id_branch, $id_student, $red_neighbour_left_data['day'], $red_neighbour_left_data['time']);
							
							if ($is_orange) {
								if (in_array(Freetime::$weekdays[$day][$current_index - 1], $freetime_red_full[$day])) {
									$return_full[$day][] = $time; // добавляем оранжевое время	
								} else {
									$return_half[$day][] = $time; // добавляем оранжевое время	
								}
								continue;
							}
						}
						
						// филиал текущей группы должен отличаться от филиала группы соседствующего красного кирпича
						if ($red_neighbour_right) {
							$is_orange = self::_branchDifferent($id_group, $id_branch, $id_student, $red_neighbour_right_data['day'], $red_neighbour_right_data['time']);
							if ($is_orange) {
								if (in_array(Freetime::$weekdays[$day][$current_index + 1], $freetime_red_full[$day])) {
									$return_full[$day][] = $time; // добавляем оранжевое время	
								} else {
									$return_half[$day][] = $time; // добавляем оранжевое время	
								}
							}
						}
					}
				}
			}
			
			return [
				"half" 	=> $return_half,
				"full"	=> $return_full,
			];
		}
		
		// подфункция проверки, что другой филиал
		private static function _branchDifferent($id_group, $id_branch, $id_student, $day, $time)
		{
			return dbConnection()->query("
				SELECT g.id FROM groups g
					LEFT JOIN group_time gt ON gt.id_group = g.id
				WHERE g.id != $id_group AND g.id_branch != $id_branch AND gt.day = '$day' AND gt.time = '$time' 
					AND CONCAT(',', CONCAT(g.students, ',')) LIKE '%,{$id_student},%'
			")->num_rows;
		}

		
		/**
		 * Получить метки студента.
		 * 
		 */
		public function getMarkers()
		{
			// Получаем все маркеры
			return Marker::findAll([
				"condition" => "owner='". self::MARKER_OWNER ."' AND id_owner=".$this->id
			]);
		}
		
		// Добавить маркеры студентов
		// $marker_data - array( array[lat, lng, type], array[lat, lng, type], ... )
		public function addMarkers($marker_data) {
			// декодируем данные
			$marker_data = json_decode($marker_data, true);
			
			// если данные не установлены
			if (!count($marker_data)) {
				return;
			}
			
			// удаляем все старые маркеры
			Marker::deleteAll([
				"condition"	=> "owner='". self::MARKER_OWNER ."' AND id_owner=".$this->id
			]);
			
			// Добавляем новые
			foreach ($marker_data as $marker) {
				Marker::add($marker + ["id_owner" => $this->id, "owner" => self::MARKER_OWNER]);
			}
		}
		
		
		/**
		 * Получить группу, в которых есть ученик.
		 * 
		 * @access public
		 * @return void
		 */
		public function findGroupBySubject($id_subject)
		{
			return Group::find([
				"condition" => "CONCAT(',', CONCAT(students, ',')) LIKE '%,{$this->id},%' AND id_subject=$id_subject"
			]);
		}
		
				
		/**
		 * Если ученик состоит в группах кроме $id_group
		 * 
		 * @access public
		 * @return void
		 */
		public function inOtherGroup($id_group, $id_subject)
		{
			$id_group = empty($id_group) ? 0 : $id_group;
			
			return Group::find([
				"condition" => "CONCAT(',', CONCAT(students, ',')) LIKE '%,{$this->id},%' AND id_subject=$id_subject AND id!=$id_group"
			]);
		}
		
		/**
		 * Если ученик состоит в группах кроме $id_group
		 * 
		 * @access public
		 * @return void
		 */
		public function inOtherBranchGroup($id_branch, $id_subject)
		{
			return Group::find([
				"condition" => "CONCAT(',', CONCAT(students, ',')) LIKE '%,{$this->id},%' AND id_subject=$id_subject AND id_branch=$id_branch"
			]);
		}
		
		/**
		 * Если ученик состоит в группах кроме $id_group
		 * 
		 * @access public
		 * @return void
		 */
		public function inOtherGradeSubjectGroup($grade, $id_subject)
		{
			return Group::find([
				"condition" => "CONCAT(',', CONCAT(students, ',')) LIKE '%,{$this->id},%' AND id_subject=$id_subject AND grade=$grade"
			]);
		}

		
		/**
		 * Если ученик состоит в группах кроме $id_group
		 * 
		 * @access public
		 * @return void
		 */
		public function inOtherSubjectGroup($id_subject)
		{
			return Group::find([
				"condition" => "CONCAT(',', CONCAT(students, ',')) LIKE '%,{$this->id},%' AND id_subject=$id_subject"
			]);
		}
		
		/**
		 * Если ученик состоит в группах кроме $id_group
		 * 
		 * @access public
		 * @return void
		 */
		public static function inOtherGroupStatic($id_student, $id_group, $id_subject)
		{
			$id_group = empty($id_group) ? 0 : $id_group;
			
			return Group::find([
				"condition" => "CONCAT(',', CONCAT(students, ',')) LIKE '%,{$id_student},%' AND id_subject=$id_subject AND id!=$id_group"
			]);
		}
		
		/**
		 * Если ученик состоит в группах кроме $id_group
		 * 
		 */
		public function inAnyOtherGroup()
		{
			return Group::find([
				"condition" => "CONCAT(',', CONCAT(students, ',')) LIKE '%,{$this->id},%'"
			]);
		}
		
		/**
		 * Если ученик состоит в группах кроме $id_group
		 * 
		 */
		public static function inAnyOtherGroupById($id_student)
		{
			return Group::find([
				"condition" => "CONCAT(',', CONCAT(students, ',')) LIKE '%,{$id_student},%'"
			]);
		}
		
		// Добавить маркеры студентов
		// $marker_data - array( array[lat, lng, type], array[lat, lng, type], ... )
		public static function addMarkersStatic($marker_data, $id_student) {
			// если данные не установлены
			if (!count($marker_data)) {
				return;
			}
			
			// удаляем все старые маркеры
			Marker::deleteAll([
				"condition"	=> "owner='". self::MARKER_OWNER ."' AND id_owner=".$id_student
			]);
			
			// Добавляем новые
			foreach ($marker_data as $marker) {
				Marker::add($marker + ["id_owner" => $id_student, "owner" => self::MARKER_OWNER]);
			}
		}
					
	}
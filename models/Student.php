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
				|| !$this->Representative->Passport->date_issued || !$this->Representative->Passport->address || !$this->getMarkers() || !$this->branches
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
			return Freetime::findAll([
				"condition"	=> "id_student=" . $this->id
			]);
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
					
	}
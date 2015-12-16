<?php
	class Teacher extends Model
	{
		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/

		public static $mysql_table	= "teachers";
		
		protected $_inline_data = ["branches", "subjects"];

		const USER_TYPE = "TEACHER";
		
		/*====================================== СИСТЕМНЫЕ ФУНКЦИИ ======================================*/
		
		public function __construct($array)
		{
			parent::__construct($array);
			
			if (!$this->id_a_pers) {
				$this->id_a_pers = null;
			}
			
			// Было ли занятие?
			if (!$this->isNewRecord) {
				$this->had_lesson = $this->hadLesson();
			}
						
			foreach ($this->branches as $id_branch) {
				if (!$id_branch) {
					continue;
				}
				$this->branch_short[$id_branch] = Branches::getShortColoredById($id_branch);
			}
		}
		
		public function getBar($id_group, $id_branch)
		{
			return Freetime::getTeacherBar($id_group, $id_branch, $this->id);
		}
		
		/*====================================== СТАТИЧЕСКИЕ ФУНКЦИИ ======================================*/
		
		public static function getActiveGroups()
		{
			$result = dbConnection()->query("
				SELECT id_teacher FROM groups
				WHERE (id_teacher!=0 AND id_teacher IS NOT NULL)
				GROUP BY id_teacher
			");
			
			while ($row = $result->fetch_object()) {
				$ids[] = $row->id_teacher;
			}
			
			return self::findAll([
				"condition" => "id IN (" . implode(",", $ids) . ")",
				"order"		=> "last_name ASC, first_name ASC, middle_name ASC",
			]);
		}
		
		public static function getGroups($id_teacher = false)
		{
			$id_teacher = !$id_teacher ? User::fromSession()->id_entity : $id_teacher;
			return Group::findAll([
				"condition" => "id_teacher=$id_teacher"
			]);
		}
		
		public static function countGroups($id_teacher = false)
		{
			$id_teacher = !$id_teacher ? User::fromSession()->id_entity : $id_teacher;
			return Group::count([
				"condition" => "id_teacher=$id_teacher"
			]);
		}
		
		public static function getReviews($id_teacher)
		{
			$Reviews = TeacherReview::findAll([
				"condition" => "id_teacher = $id_teacher",
				"order"		=> "date DESC"
			]);
			
			foreach ($Reviews as &$Review) {
				$Review->Student = Student::findById($Review->id_student);
			}
			
			return $Reviews;
		}
		
		/*====================================== ФУНКЦИИ КЛАССА ======================================*/
		
		public function beforeSave()
		{
			// Очищаем номера телефонов
			foreach (Student::$_phone_fields as $phone_field) {
				$this->{$phone_field} = cleanNumber($this->{$phone_field});
			}

			if ($this->isNewRecord) {
				$this->login 	= $this->_generateLogin();
				$this->password	= $this->_generatePassword();
				
				$this->User = User::add([
					"login" 		=> empty($this->login) 		? $this->_generateLogin() 	: $this->login,
					"password"		=> empty($this->password) 	? $this->_generatePassword(): $this->password,
					"first_name"	=> $this->first_name,
					"last_name"		=> $this->last_name,
					"middle_name"	=> $this->middle_name,
					"type"			=> self::USER_TYPE,
				]);
			} else {
				$User = User::findTeacher($this->id);
				if ($User) {
					$User->login 	= $this->login;
					$User->password = User::password($this->password);
					$User->save();
				}
			}
		}
		
		public function afterFirstSave()
		{
			$this->User->id_entity = $this->id;
			$this->User->save("id_entity");
		}
		
		public function _generateLogin()
		{
			$last_name 	= mb_strtolower($this->last_name, "UTF-8");
			$last_name = translit($last_name);
			
			$first_name = mb_strtolower($this->first_name, "UTF-8");
			$first_name = translit($first_name);
			
			$middle_name= mb_strtolower($this->middle_name, "UTF-8");
			$middle_name= translit($middle_name);
			
			return mb_strimwidth($last_name, 0, 3) . "_" . $first_name[0] . $middle_name[0];
		}
		
		public function _generatePassword()
		{
			return mt_rand(10000000, 99999999);
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
		
		public function getInitials()
		{
			return $this->last_name . " " . mb_substr($this->first_name, 0, 1, 'utf-8') . ". " . mb_substr($this->middle_name, 0, 1, 'utf-8') . ".";
		}
		
		public function getReports()
		{
			$Reports = Report::findAll([
				"condition" => "id_teacher=" . $this->id
			]);
			
			foreach ($Reports as &$Report) {
				$Report->Student = Student::findById($Report->id_student);	
			}
			
			return $Reports;
		}
		
		public function agreedToBeInGroup($id_group)
		{
			return GroupAgreement::count([
				"condition" => "id_entity=" . $this->id . " AND id_group=" . $id_group . " AND type_entity='TEACHER' AND id_status=" . GroupTeacherStatuses::AGREED
			]) > 0 ? true : false;
		}
		
		public function agreedToBeInGroupStatic($id_teacher, $id_group)
		{
			return GroupAgreement::count([
				"condition" => "id_entity=" . $id_teacher . " AND id_group=" . $id_group . " AND type_entity='TEACHER' AND id_status=" . GroupTeacherStatuses::AGREED
			]) > 0 ? true : false;
		}
		
		public function hadLesson()
		{
			return VisitJournal::count([
				"condition" => "type_entity='TEACHER' AND id_entity={$this->id}"
			]);
		}
	}
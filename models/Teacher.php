<?php
	class Teacher extends Model
	{
		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/

		public static $mysql_table	= "teachers";

		protected $_inline_data = ["branches", "subjects", "public_grades"];
		protected $_additional_vars = ["banned"];

		const USER_TYPE = "TEACHER";
		const UPLOAD_DIR = "img/teachers/";

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
				$this->has_photo = file_exists(self::UPLOAD_DIR . $this->id . ".jpg");

				$this->banned = User::findTeacher($this->id)->banned;
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

		// 	количество красных меток "требуется создание отчета"
		public function redReportCount()
		{
			return self::redReportCountStatic($this->id);
		}

		/*====================================== СТАТИЧЕСКИЕ ФУНКЦИИ ======================================*/

		/**
		 * Получить преподавателей, которые выгружаются на сайт ЕГЭ-Центра
		 */
		public static function getPublished()
		{
			return static::findAll([
				'condition' => 'published = 1'
			]);
		}

		// 	количество красных меток "требуется создание отчета"
		public static function redReportCountStatic($id_teacher)
		{
			$result = dbConnection()->query("SELECT id_entity, id_subject FROM visit_journal WHERE id_teacher={$id_teacher} GROUP BY id_entity, id_subject");

			while ($row = $result->fetch_object()) {
				$student_subject[] = $row;
			}

			$red_count = 0;
			foreach ($student_subject as $Object) {
				// проверяем статус расторжения
				$status = dbConnection()->query("
					SELECT cs.status FROM contract_subjects cs
					LEFT JOIN contracts c on c.id = cs.id_contract
					WHERE c.id_student = {$Object->id_entity} " . Contract::ZERO_OR_NULL_CONDITION_JOIN . "
						AND cs.id_subject = {$Object->id_subject}
				")->fetch_object()->status;

				if ($status <= 1) {
					continue;
				}

				// получаем кол-во занятий с последнего отчета по предмету
				$LatestReport = Report::find([
					"condition" => "id_student=" . $Object->id_entity . " AND id_subject=" . $Object->id_subject ." AND id_teacher=" . $id_teacher,
					"order" => " DATE(date) asc"
				]);

				if ($LatestReport) {
					$latest_report_date = date("Y-m-d", strtotime($LatestReport->date));
				} else {
					$latest_report_date = "0000-00-00";
				}

				$lessons_count = VisitJournal::count([
					"condition" => "id_subject={$Object->id_subject} AND id_entity={$Object->id_entity} AND id_teacher=" . $id_teacher . "
						AND lesson_date > '$latest_report_date'"
				]);

				if ($lessons_count >= 8) {
					$red_count++;
				}
			}

			return $red_count;
		}

		public static function redReportCountAll()
		{
			if (LOCAL_DEVELOPMENT) {
				return;
			}
			
			// Try to get from memcached first
			$red_count = memcached()->get("redReportCountAll");
			if (memcached()->getResultCode() != Memcached::RES_SUCCESS) {
				$red_count = 0;
				
				foreach (self::getIds(['condition' => 'in_egecentr = 1']) as $id_teacher) {
					$red_count += Teacher::redReportCountStatic($id_teacher);
				}
				
				memcached()->set('redReportCountAll', $red_count, 3600 * 24 * 30);
			}
			return $red_count;
		}

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
				"condition" => "id_teacher=$id_teacher AND ended = 0"
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
				"condition" => "rating > 0 AND id_teacher = $id_teacher",
				"order"		=> "date DESC"
			]);

			foreach ($Reviews as &$Review) {
				$Review->Student = Student::findById($Review->id_student);
			}

			return $Reviews;
		}

		/*====================================== ФУНКЦИИ КЛАССА ======================================*/
		
		// Перезаписываем функцию findAll
		public static function findAll($params = [])
		{
			if (! isset($params['condition'])) {
				$params['condition'] = 'in_egecentr = 1';
			} else {
				$params['condition'] .= ' AND in_egecentr = 1';
			}
			
			return parent::findAll($params);
		}
		
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
					$User->banned = $this->banned === "true" ? 1 : 0;
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

		public function getFullName()
		{
			return $this->last_name . " " . $this->first_name . " " . $this->middle_name;
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

		public function hadLesson()
		{
			return VisitJournal::count([
				"condition" => "type_entity='TEACHER' AND id_entity={$this->id}"
			]);
		}

		public static function getGreenRed() {
			$teacher_ids = VisitJournal::getTeacherIds();

			foreach ($teacher_ids as $id_teacher) {

				$result = dbConnection()->query("
					SELECT id_teacher, id_entity, id_subject, id_group FROM visit_journal
					WHERE type_entity = 'STUDENT' AND id_teacher=$id_teacher
					GROUP BY id_entity, id_subject, id_group, id_teacher
				");

				$res = [];
				while ($row = $result->fetch_object()) {
					$res[] = $row;
				}

				$return[$id_teacher]['green_count'] = $result->num_rows;
				$return[$id_teacher]['red_count'] 	= 0;

				foreach ($res as $data) {
					#
					# ИТЕРАЦИЯ 1
					#

					// Ищем последнюю запись в журнале в конфигурации с учеником
					$last_lesson_date_with_student = dbConnection()->query("
						SELECT lesson_date FROM visit_journal
						WHERE id_teacher=$id_teacher AND type_entity='STUDENT' AND id_group={$data->id_group}
							AND id_entity={$data->id_entity} AND id_subject={$data->id_subject}
						ORDER BY lesson_date DESC
						LIMIT 1
					")->fetch_object()->lesson_date;

					// Проверяем был ли урок без этого ученика в будущем
					$was_lesson_without_student = dbConnection()->query("
						SELECT lesson_date FROM visit_journal
						WHERE id_teacher=$id_teacher AND type_entity='STUDENT' AND id_group={$data->id_group}
							AND id_subject={$data->id_subject} AND lesson_date > '{$last_lesson_date_with_student}'
						LIMIT 1
					")->num_rows;

					$iteration_1_minus = false;
					if ($was_lesson_without_student) {
						$return[$id_teacher]['red_count']++;
// 						$return[$id_teacher]['red_count_1']++;
						$iteration_1_minus = true;
					}

					#
					# ИТЕРАЦИЯ 2
					#
					// есть ли ученик в группе?
					$student_present = dbConnection()->query("
						SELECT id FROM groups
						WHERE id = {$data->id_group} AND id_subject={$data->id_subject}
							AND CONCAT(',', CONCAT(students, ',')) LIKE '%,{$data->id_entity},%'
					")->num_rows;

					if ($student_present) {
						// Проверяем статус предмета ученика по предмету
						$subject_status = dbConnection()->query("
							SELECT cs.status FROM contracts c
							LEFT JOIN contract_subjects cs ON cs.id_contract = c.id
							WHERE c.id_student = {$data->id_entity} " . Contract::ZERO_OR_NULL_CONDITION_JOIN . "
								AND cs.id_subject = {$data->id_subject}
						")->fetch_object()->status;
						// если статус предмета зеленый или желтый
						if ($subject_status < 3) {
							$return[$id_teacher]['red_count']++;
// 							$return[$id_teacher]['red_count_2']++;
						}
					#
					# ИТЕРАЦИЯ 3
					#
					} else if (!$iteration_1_minus) {
						$return[$id_teacher]['red_count']++;
// 						$return[$id_teacher]['red_count_3']++;
					}
				}
			}

			return $return;
		}

	}

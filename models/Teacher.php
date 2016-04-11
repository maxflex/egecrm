<?php
	class Teacher extends Model
	{
		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/

// 		public static $mysql_table	= "teachers";
		public static $mysql_table = "tutors";

		protected $_inline_data = ["branches", "subjects", "grades", "public_grades"];
		protected $_additional_vars = ["banned"];

        public static $api_fields = [
            'id', 'photo_extension',
            'first_name', 'last_name', 'middle_name',
            'description',  'has_photo',
            'subjects', 'public_seniority', 'public_ege_start', 'public_grades'
        ];

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
				
				
				$this->has_photo = $this->photoExists();

				$this->banned = User::findTeacher($this->id)->banned;
			}

			foreach ($this->branches as $id_branch) {
				if (!$id_branch) {
					continue;
				}
				$this->branch_short[$id_branch] = Branches::getShortColoredById($id_branch);
			}
		}
		
		public static function dbConnection()
		{
			return dbEgerep();
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
				'condition' => "description != ''"
			]);
		}
		
		/*
		 * Проверить, есть ли фото у преподавателя
		 */
		public function photoExists()
		{
			$ch = curl_init('http://lk.a-perspektiva.ru:8085/img/tutors/' . $this->id . '@2x.' . $this->photo_extension);
			curl_setopt($ch, CURLOPT_NOBODY, true);
			curl_exec($ch);
			$retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			// $retcode >= 400 -> not found, $retcode = 200, found.
			curl_close($ch);
			return $retcode == 200;
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
//					"order" => " DATE(date) asc"
                    "order" => " STR_TO_DATE(date,'%d.%m.%Y') desc "
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
			// @refactored
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

		public static function getGroups($id_teacher = false, $only_ended = true)
		{
			// @refactored
			$id_teacher = !$id_teacher ? User::fromSession()->id_entity : $id_teacher;
			
			return Group::findAll([
				"condition" => "id_teacher=$id_teacher" . ($only_ended ? " AND ended=0" : ""),
			]);
		}

		public static function countGroups($id_teacher = false)
		{
			$id_teacher = !$id_teacher ? User::fromSession()->id_entity : $id_teacher;
			
			// @refactored
			return Group::count([
				"condition" => "id_teacher=$id_teacher AND ended = 0"
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
	}

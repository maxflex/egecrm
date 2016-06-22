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
            'description',  'has_photo', 'comment_extended',
            'subjects', 'public_grades', 'start_career_year',
            'video_link',
        ];

		const USER_TYPE = "TEACHER";
		const UPLOAD_DIR = "img/teachers/";
		const EXTERNAL_PHOTO_PATH = 'http://static.a-perspektiva.ru/img/tutors/';

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
			$ch = curl_init(static::EXTERNAL_PHOTO_PATH . $this->id . '.' . $this->photo_extension);
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
				
				memcached()->set('redReportCountAll', $red_count, 3600 * 24); // на 24 часа
			}
			return $red_count;
		}
		
		/*
		 * Получить преподавателей для отчета
		 */
		public static function getReportTeachers()
		{
			$result = dbConnection()->query("
				SELECT id_entity 
				FROM visit_journal 
				WHERE type_entity = 'TEACHER'
				GROUP BY id_entity
			");
			
			while ($row = $result->fetch_object()) {
				$return[] = static::getLight($row->id_entity);
			}
			
			return $return;
		}
		
		/*
		 * Получить легкую версию (имя + id)
		 */
		public static function getLight($id)
		{
			return dbEgerep()->query("
				SELECT id, first_name, last_name, middle_name 
				FROM " . static::$mysql_table . " 
				WHERE id = " . $id . " 
				ORDER BY last_name, first_name, middle_name ASC")
			->fetch_object(); 
		}
		
		/*
		 * Получить данные для отчета
		 */
		public static function getReportData($page)
		{
			if (!$page) {
				$page = 1;
			}
			// С какой записи начинать отображение, по формуле
			$start_from = ($page - 1) * Report::PER_PAGE;
			
			$search = json_decode($_COOKIE['reports']);
			
			// получаем все человеко-предметы
			$query = "
				SELECT vj.id_entity, vj.id_subject, vj.id_teacher, vj.year, r.id, rh.lesson_count
				FROM visit_journal vj
				LEFT JOIN reports" . static::_connectTables('r') . "
				JOIN reports_helper" . static::_connectTables('rh', 'AND isnull(rh.id_report) = isnull(r.id)') . "
				WHERE vj.type_entity='STUDENT' "
				. (($search->mode == 1) ? " AND r.id IS NOT NULL" : "")
				. ($search->available_for_parents ? " AND r.available_for_parents={$search->available_for_parents}" : "")
				. ($search->email_sent ? " AND r.email_sent={$search->email_sent}" : "")
				. ($search->year ? " AND vj.year={$search->year}" : "")
				. ($search->id_teacher ? " AND vj.id_teacher={$search->id_teacher}" : "")
				. ((isset($search->subjects) && count($search->subjects)) ? " AND vj.id_subject IN (" . implode(',', $search->subjects) . ")" : "")
				. (($search->mode > 1) ? " AND (r.id IS NULL AND rh.lesson_count" . ($search->mode == 2 ? ">=8" : "<8") . ")" : "") . "
				GROUP BY vj.id_entity, vj.id_subject, vj.id_teacher, vj.year, r.id
				ORDER BY vj.lesson_date DESC";
			
			$result = dbConnection()->query($query . " LIMIT {$start_from}, " . Report::PER_PAGE);
			
			while ($row = $result->fetch_object()) {
				$student_subject[] = $row;
			}
			
			foreach ($student_subject as &$ss) {
				$ss->Student = Student::getLight($ss->id_entity);
				$ss->Teacher = Teacher::getLight($ss->id_teacher);
				$ss->force_noreport = ReportForce::check($ss->id_entity, $ss->id_teacher, $ss->id_subject, $ss->year);
			}
			
			return [
				'data' 	=> $student_subject,
				'count' => dbConnection()->query($query)->num_rows,
			];
		}
		
		private static function _connectTables($t, $addon) {
			return " {$t} ON ({$t}.id_student = vj.id_entity AND {$t}.id_teacher = vj.id_teacher AND {$t}.id_subject = vj.id_subject AND {$t}.year = vj.year {$addon})";
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
				$params['condition'] = 'in_egecentr >= 1';
			} else {
				$params['condition'] .= ' AND in_egecentr >= 1';
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
		
		/*
		 * Вернуть учителей для API
		 */
		public static function forApi($Teachers)
		{
			foreach ($Teachers as $Teacher) { 
				$object = [];
				foreach (Teacher::$api_fields as $field) {
					$object[$field] = $Teacher->{$field};
				}
				$object['photo_url'] = $Teacher->has_photo ? static::EXTERNAL_PHOTO_PATH . $Teacher->id . '.' . $Teacher->photo_extension : static::EXTERNAL_PHOTO_PATH . 'no-profile-img.gif'; 
				$object['full_name'] = $Teacher->getFullName();
				$object['grades_interval'] = $object['public_grades'][0] . (count($object['public_grades']) > 1 ? '-' . end($object['public_grades']) : '');
				
				$subject_string = [];
				foreach ($Teacher->subjects as $index => $id_subject) {
					$subject_string[] = Subjects::$dative[$id_subject];
				} 
				$object['subjects_dative'] = implode(', ', $subject_string);
				
				$return[] = $object;
			}
			return $return;
		}
	}

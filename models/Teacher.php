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
            'subjects', 'public_grades', 'start_career_year',
            'video_link',
        ];

		const USER_TYPE = "TEACHER";
		const UPLOAD_DIR = "img/teachers/";
		const EXTERNAL_PHOTO_PATH = 'http://static.a-perspektiva.ru/img/tutors/';
		const PLACE = 'teacher';

		/*====================================== СИСТЕМНЫЕ ФУНКЦИИ ======================================*/

		public function __construct($array)
		{
			parent::__construct($array);

			if (!$this->id_a_pers) {
				$this->id_a_pers = null;
			}

			// Было ли занятие?
			if (! $this->isNewRecord) {
				$this->had_lesson = $this->hadLesson();


				$this->has_photo = $this->photoExists();

                // @rights-need-to-refactor
                $User = User::findTeacher($this->id);
				$this->banned = $User ? $User->allowed(Shared\Rights::EC_BANNED) : false;
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

		public function getBar()
		{
			return Freetime::getTeacherBar($this->id);
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
        public static function redReportCountStatic($id_teacher, $year = false)
        {
            return dbConnection()->query(Report::generateQuery(compact('id_teacher', 'year')))->fetch_object()->cnt;
        }

		public static function redReportCountAll()
		{
            $red_count = 0;
			$search = json_decode($_COOKIE['reports']);

            foreach (static::getJournalTeachers(true) as $id_teacher) {
                //$red_count += Teacher::redReportCountStatic($id_teacher, $search->year);
                $search->mode = 2;                  // требующие создания
                $search->id_teacher = $id_teacher;
                $red_count += static::_count($search);
			}
			return $red_count ? $red_count : null;
		}

		/*
		 * Получить преподавателей для отчета
		 */
		public static function getJournalTeachers($only_ids = false)
		{
			$result = dbConnection()->query("
				SELECT id_entity
				FROM visit_journal
				WHERE type_entity = 'TEACHER'
				GROUP BY id_entity
			");


			while ($row = $result->fetch_object()) {
				$tutor_ids[] = $row->id_entity;
			}

			if ($only_ids) {
			    return $tutor_ids;
            } else {
			    return static::getLightArray($tutor_ids);
            }
		}

		/*
		 * Получить легкую версию (имя + id)
		 */
		public static function getLight($id, $additional = [])
		{
			return dbEgerep()->query("
				SELECT id, first_name, last_name, middle_name " . (count($additional) ? ', ' . implode(',', $additional) : '') .
                " FROM " . static::$mysql_table . "
				WHERE id = " . $id . "
				ORDER BY last_name, first_name, middle_name ASC")
			->fetch_object();
		}

				/*
		 * Получить легкую версию (имя + id)
		 */
		public static function getLightArray($teacher_ids)
		{
			$result = dbEgerep()->query("
				SELECT id, first_name, last_name, middle_name
				FROM " . static::$mysql_table . "
				WHERE id IN (" . implode(',', $teacher_ids) . ")
				ORDER BY last_name, first_name, middle_name ASC");

			$Teachers = [];
			while($row = $result->fetch_object()) {
				$Teachers[] = $row;
			}
			return $Teachers;
		}

		/*
		 * Получить данные для отчета
		 * $Teachers – нужен для counts, чтобы не получать заново
		 */
		public static function getReportData($page, $Teachers, $id_student = false)
		{
			if (!$page && $page != -1) {
				$page = 1;
			}
			// С какой записи начинать отображение, по формуле
			$start_from = ($page - 1) * Report::PER_PAGE;

			if (! $id_student) {
				$search = json_decode($_COOKIE['reports']);
				if (!$search) $search = (object)[];
			} else {
				$search = (object)compact('id_student');
			}

			// получаем данные
			$query = static::_generateQuery($search, "vj.id_entity, vj.id_subject, vj.id_teacher, vj.year, rh.id_report as id, r.date, r.available_for_parents, rh.lesson_count, r.homework_grade, r.activity_grade, r.behavior_grade, r.material_grade, r.tests_grade, vj.grade");
			$result = dbConnection()->query($query . ($page == -1 ? '' : " LIMIT {$start_from}, " . Report::PER_PAGE));

			while ($row = $result->fetch_object()) {
				$student_subject[] = $row;
			}

			foreach ($student_subject as &$ss) {
				$ss->date = toDotDate($ss->date);
				$ss->Student = Student::getLight($ss->id_entity);
				$ss->Teacher = Teacher::getLight($ss->id_teacher);
				$ss->force_noreport = ReportForce::check($ss->id_entity, $ss->id_teacher, $ss->id_subject, $ss->year);
				if ($ss->grade) {
					$ss->grade_label = Grades::$short[$ss->grade];
				}
			}

			if ($id_student) {
				return $student_subject;
			}

			// counts
			$counts['all'] = static::_count($search);

			foreach(array_merge([""], Years::$all) as $year) {
				$new_search = clone $search;
				$new_search->year = $year;
				$counts['year'][$year] = static::_count($new_search);
			}

			foreach(["", 1, 2, 3, 4] as $mode) {
				$new_search = clone $search;
				$new_search->mode = $mode;
				$counts['mode'][$mode] = static::_count($new_search);
			}

			foreach(["", 0, 1] as $available_for_parents) {
				$new_search = clone $search;
				$new_search->available_for_parents = $available_for_parents;
				$counts['available_for_parents'][$available_for_parents] = static::_count($new_search);
			}
			foreach(["", 9, 10, 11, 12, 13, 14] as $grade) {
				$new_search = clone $search;
				$new_search->grade = $grade;
				$counts['grade'][$grade] = static::_count($new_search);
			}

			foreach(([''=>''] + Subjects::$all) as $id_subject => $name) {
				$new_search = clone $search;
				$new_search->id_subject = $id_subject;
				$counts['subject'][$id_subject] = static::_count($new_search);
			}

			foreach(array_merge(['id' => ''], $Teachers) as $Teacher) {
				$new_search = clone $search;
				$new_search->id_teacher = $Teacher['id'];
				$counts['teacher'][$Teacher['id']] = static::_count($new_search);
			}


			return [
				'data' 	=> $student_subject,
				'counts' => $counts,
			];
		}

		private static function _count($search) {
			return dbConnection()
					->query(static::_generateQuery($search, "COUNT(*) AS cnt FROM (SELECT vj.id", false, ") AS X"))
					->fetch_object()
					->cnt;
		}

		private static function _connectTables($t, $addon = '') {
			return " {$t} ON ({$t}.id_student = vj.id_entity AND {$t}.id_teacher = vj.id_teacher AND {$t}.id_subject = vj.id_subject AND {$t}.year = vj.year {$addon})";
		}

		private static function _generateQuery($search, $select, $order = true, $ending = '')
		{
			$main_query = "
				FROM visit_journal vj
				JOIN reports_helper" . static::_connectTables('rh') . "
				LEFT JOIN reports" . static::_connectTables('r', 'and rh.id_report = r.id') . "
				LEFT JOIN reports_force " . static::_connectTables('rf') . "
				WHERE vj.type_entity='STUDENT' "
				. ($search->mode == 1 ? " AND rh.id_report IS NOT NULL" : "")
				. (!isBlank($search->available_for_parents) ? " and if(r.available_for_parents = 1 and r.id > 0, 1, 0) = {$search->available_for_parents} " : "")
				. ($search->year ? " AND vj.year={$search->year}" : "")
				. ($search->grade ? " AND vj.grade={$search->grade}" : "")
				. ($search->id_teacher ? " AND vj.id_teacher={$search->id_teacher}" : "")
				. ($search->id_student ? " AND vj.id_entity={$search->id_student}" : "")
				. (($search->id_subject) ? " AND vj.id_subject={$search->id_subject}" : "")
				. (($search->mode > 1 && $search->mode < 4) ? " AND (rh.id_report IS NULL AND rf.id IS NULL AND rh.lesson_count" . ($search->mode == 2 ? " >= " . Report::LESSON_COUNT : " < " . Report::LESSON_COUNT) . ")" : "")
				. (($search->mode == 4) ? " AND rf.id IS NOT NULL AND rh.id_report IS NULL" : "")
				. " GROUP BY vj.id_entity, vj.id_subject, vj.id_teacher, vj.year, rh.id_report "
				. ($order ? " ORDER BY vj.lesson_date DESC" : "");
			return "SELECT " . $select . $main_query . $ending;
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
				$Teachers[] = Teacher::getLight($row->id_teacher);
			}

			return $Teachers;
		}

		public static function getGroups($id_teacher = false, $only_ended = true)
		{
			// @refactored
			$id_teacher = !$id_teacher ? User::fromSession()->id_entity : $id_teacher;

			return Group::findAll([
				"condition" => "id_teacher=$id_teacher AND is_unplanned=0" . ($only_ended ? " AND ended=0" : ""),
			], true);
		}

		public static function countGroups($id_teacher = false)
		{
			$id_teacher = !$id_teacher ? User::fromSession()->id_entity : $id_teacher;

			// @refactored
			return Group::count([
				"condition" => "id_teacher=$id_teacher AND ended = 0 AND is_unplanned=0"
			]);
		}

		public static function getReviews($id_teacher)
		{
			$Reviews = TeacherReview::findAll([
				"condition" => "id_teacher = $id_teacher",
				"order"		=> "id ASC"
			]);

			foreach ($Reviews as &$Review) {
				$Review->Student = Student::getLight($Review->id_student);
			}

			return $Reviews;
		}

		public function getPublishedReviews()
		{
			return TeacherReview::findAll([
				'condition' => "id_teacher={$this->id} AND published=1"
			]);
		}

		/*====================================== ФУНКЦИИ КЛАССА ======================================*/

		// Перезаписываем функцию findAll
		public static function findAll($params = [], $flag = false)
		{
			if (! isset($params['condition'])) {
				$params['condition'] = 'in_egecentr >= 1';
			} else {
				$params['condition'] .= ' AND in_egecentr >= 1';
			}

			return parent::findAll($params, $flag);
		}

		public static function getByStatus($in_egecentr)
		{
			return Teacher::findAll([
				"condition" => "in_egecentr = {$in_egecentr}",
				"order"		=> "last_name, first_name, middle_name ASC"
			]);
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
					"phone"			=> $this->phone,
					"email"			=> $this->email,
					"type"			=> self::USER_TYPE,
				]);
			} else {
				$User = User::findTeacher($this->id);
				if ($User) {
					$User->login 	= $this->login;
					$User->password = User::password($this->password);
					$User->banned = $this->allowed(Shared\Rights::EC_BANNED) === "true" ? 1 : 0;
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
			return static::getReportsStatic($this->id);
		}

		public function getReportsStatic($id_teacher)
		{
			$Reports = Report::findAll([
				"condition" => "id_teacher=" . $id_teacher
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
			if (! is_array($Teachers)) {
				$Teachers = [$Teachers];
				$single = true;
			} else {
				$single = false;
			}
			foreach ($Teachers as $Teacher) {
				$object = [];
				foreach (Teacher::$api_fields as $field) {
					$object[$field] = $Teacher->{$field};
				}
				$object['photo_url'] = $Teacher->has_photo ? static::EXTERNAL_PHOTO_PATH . $Teacher->id . '.' . $Teacher->photo_extension : static::EXTERNAL_PHOTO_PATH . 'no-profile-img.gif';
				$object['full_name'] = $Teacher->getFullName();
				$object['grades_interval'] = $object['public_grades'][0] . (count($object['public_grades']) > 1 ? '-' . end($object['public_grades']) : '');
				$object['public_seniority'] = date('Y') - $Teacher->start_career_year;
				$subject_string = [];
				foreach ($Teacher->subjects as $index => $id_subject) {
					$subject_string[] = Subjects::$dative[$id_subject];
				}
				$object['subjects_dative'] = implode(', ', $subject_string);

				if ($single) {
					$object['reviews'] = $Teacher->getPublishedReviews();
				}

				$return[] = $object;
			}

			if ($single) {
				return $return[0];
			} else {
				return $return;
			}
		}

		public function lessonCount()
		{
			return VisitJournal::count([
				'condition' => ''
			]);
		}

		/**
		 * Получить статистику преподавателя
		 */
		public static function stats($tutor_id)
		{
			$ec_lesson_count = VisitJournal::count([
                "condition" => "id_entity = {$tutor_id} and type_entity = '".Teacher::USER_TYPE."'"
            ]);
			$ec_lesson_count_by_grade[9] = VisitJournal::count([
                "condition" => "id_entity = {$tutor_id} and type_entity = '".Teacher::USER_TYPE."' AND grade=9"
            ]);
			$ec_lesson_count_by_grade[10] = VisitJournal::count([
                "condition" => "id_entity = {$tutor_id} and type_entity = '".Teacher::USER_TYPE."' AND grade=10"
            ]);
			$ec_lesson_count_by_grade[11] = VisitJournal::count([
                "condition" => "id_entity = {$tutor_id} and type_entity = '".Teacher::USER_TYPE."' AND grade=11"
            ]);
			$ec_review_count = TeacherReview::count([
                "condition" => "id_teacher = {$tutor_id} AND admin_rating_final <= 5 AND admin_rating_final > 0"
            ]);
			$result = dbConnection()->query("select avg(admin_rating_final) as cnt from teacher_reviews where id_teacher = {$tutor_id} AND admin_rating_final <= 5 AND admin_rating_final > 0");
			$ec_review_avg = $result->fetch_assoc();
			$ec_review_avg = floatval($ec_review_avg['cnt']);

			return [
				'ec_lesson_count' 		=> $ec_lesson_count,
				'ec_review_count' 		=> $ec_review_count,
				'ec_review_avg' 		=> $ec_review_avg,
			];

		}

        /**
         * Сколько процентов приносит компании
         */
        public static function getEfficency($id_teacher)
        {
			$student_ids = [];
            $query = dbConnection()->query("SELECT id_entity FROM visit_journal WHERE type_entity = 'STUDENT' AND id_teacher={$id_teacher} GROUP BY id_entity");

			while($row = $query->fetch_object()) {
				$student_ids[] = $row->id_entity;
			}

			// сколько всего учитель принес компании (сколько всего ученики заплатили учителю )
			$total_students_paid = 0;

			// сколько всего компания заплатила учителю
			$total_paid_to_teacher = dbConnection()->query("select sum(price) as s from visit_journal where type_entity='TEACHER' and id_entity={$id_teacher}")->fetch_object()->s;

			foreach($student_ids as $id_student) {
				$payments = Payment::getByStudentId($id_student);
				$payment_sum = [];
				$sums = [];
				$limits = [];
				foreach($payments as $payment) {
					if (! isset($payment_sum[$payment->year])) {
						$payment_sum[$payment->year] = 0;
					}
					if ($payment->id_type == 2) {
						$payment_sum[$payment->year] -= $payment->sum;
					} else {
						$payment_sum[$payment->year] += $payment->sum;
					}
				}

				// Находим все цепи договоров
				$contracts = ContractInfo::findAll([
					'condition' => "id_student={$id_student}"
				]);

				// Для каждой последней версии из цепи получаем кол-во предметов
				foreach($contracts as $contract) {
					$last_contract_id = Contract::getIds([
						'condition' => "id_contract={$contract->id_contract} AND current_version=1"
					])[0];

					$contract_subjects = ContractSubject::findAll([
						'condition' => "id_contract={$last_contract_id}"
					]);

					foreach($contract_subjects as $cs) {
						$limits[$contract->year][$cs->id_subject] += $cs->count;
						$sums[$contract->year] += $cs->count;
					}
				}

				foreach($payment_sum as $year => $sum) {
					// цена за 1 занятие
					$price_for_one_lesson = $payment_sum[$year] / $sums[$year];

					// сколько занятий было по предмету у учителя
					foreach($limits[$year] as $id_subject => $limit) {
						$lesson_count = dbConnection()->query("select count(*) as cnt from (select id_teacher from visit_journal where id_subject={$id_subject} AND type_entity='STUDENT' AND id_entity={$id_student} AND year={$year} limit {$limit}) as x where id_teacher={$id_teacher}")->fetch_object()->cnt;
						$total_students_paid += ($lesson_count * $price_for_one_lesson);
					}
				}
			}

			if ($total_students_paid > 0) {
				return round($total_paid_to_teacher / $total_students_paid * 100);
			} else {
				return 0;
			}
        }

		// получить платежи преподавателя
		public static function getPayments($id_teacher)
		{
			$items = [];

			// кешируем группы
			$groups = [];

			/* начисления за проведенные занятия */
			$lessons = VisitJournal::findAll([
                "condition" => "id_entity=$id_teacher AND type_entity='TEACHER'"
            ]);

			foreach($lessons as $lesson) {
				if (! isset($groups[$lesson->id_group])) {
					$group = dbConnection()->query("select * from groups where id={$lesson->id_group}")->fetch_object();
					$group->cabinet_ids = Group::getCabinetIds($group->id);
					$group->cabinet = Cabinet::getBlock($group->cabinet_ids[0]);
					$groups[$lesson->id_group] = $group;
				}
				$group = $groups[$lesson->id_group];
				if ($group->is_unplanned) {
					$comment = "дополнительное занятие " . date("d.m.y", strtotime($lesson->lesson_date)) . " в {$lesson->lesson_time} (" . Subjects::$three_letters[$lesson->id_subject] . "-" . Grades::$short[$lesson->grade] . "), кабинет " . Cabinet::getBlock($lesson->cabinet)['label'];
				} else {
					$comment = "занятие " . date("d.m.y", strtotime($lesson->lesson_date)) . " в {$lesson->lesson_time}, группа {$lesson->id_group} (" . Subjects::$three_letters[$group->id_subject] . "-" . Grades::$short[$group->grade] . "), кабинет " . $group->cabinet['label'];
				}
				$items[$lesson->year][$lesson->lesson_date][] = [
					'sum' 		  => $lesson->price,
					'comment'	  => $comment,
					'credentials' => User::findById($lesson->id_user_saved)->login . ' ' . dateFormat($lesson->date),
					'date'		  => $lesson->date,
				];
			}

			/* платежи */
			$payments = Payment::findAll([
				"condition" => "entity_id={$id_teacher} and entity_type='" . Teacher::USER_TYPE . "' "
			]);

			foreach($payments as $payment) {
				$items[$payment->year][fromDotDate($payment->date)][] = [
					'sum' 		  => intval($payment->sum) * -1,
					'comment' 	  => Payment::$all[$payment->id_status],
					'credentials' => $payment->user_login . ' ' . dateFormat($payment->first_save_date),
					'date' 		  => $payment->first_save_date,
				];
			}

			/* доп услуги */
			$additional_payments = TeacherAdditionalPayment::get($id_teacher);

			foreach($additional_payments as $payment) {
				$items[$payment->year][fromDotDate($payment->date)][] = [
					'sum' 		  => $payment->sum,
					'comment' 	  => $payment->purpose,
					'credentials' => $payment->user_login . ' ' . dateFormat($payment->created_at),
					'date' 		  => $payment->created_at,
				];
			}

			ksort($items);
            $items = array_reverse($items, true);

			foreach($items as $year => $data) {
				ksort($items[$year]);
			}

			return $items;
		}
    }

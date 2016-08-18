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
				
				$this->comment_extended = nl2br($this->comment_extended);
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
		public static function redReportCountStatic($id_teacher, $year = false)
		{
			return dbConnection()->query("
				SELECT COUNT(*) AS cnt FROM reports_helper rh
				LEFT JOIN reports_force rf ON (rf.id_subject = rh.id_subject AND rf.id_teacher = rh.id_teacher AND rf.id_student = rh.id_student AND rf.year = rh.year)
				WHERE rh.lesson_count >= 8 AND rf.id IS NULL AND rh.id_teacher = {$id_teacher} AND rh.id_report IS NULL " . ($year ? "AND rh.year={$year}" : "")
			)->fetch_object()->cnt;
		}

		public static function redReportCountAll()
		{
			$search = json_decode($_COOKIE['reports']);
			
			foreach (self::getIds(['condition' => 'in_egecentr >= 1']) as $id_teacher) {
				$red_count += Teacher::redReportCountStatic($id_teacher, $search->year);
			}
			return $red_count ? $red_count : null;
		}
		
		/*
		 * Получить преподавателей для отчета
		 */
		public static function getJournalTeachers()
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
			
			return static::getLightArray($tutor_ids);
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
		public static function getReportData($page, $Teachers)
		{
			if (!$page) {
				$page = 1;
			}
			// С какой записи начинать отображение, по формуле
			$start_from = ($page - 1) * Report::PER_PAGE;
			
			$search = json_decode($_COOKIE['reports']);
			
			// получаем данные
			$query = static::_generateQuery($search, "vj.id_entity, vj.id_subject, vj.id_teacher, vj.year, r.id, rh.lesson_count");
			
			$result = dbConnection()->query($query . " LIMIT {$start_from}, " . Report::PER_PAGE);
			
			while ($row = $result->fetch_object()) {
				$student_subject[] = $row;
			}
			
			foreach ($student_subject as &$ss) {
				$ss->Student = Student::getLight($ss->id_entity);
				$ss->Teacher = Teacher::getLight($ss->id_teacher);
				$ss->force_noreport = ReportForce::check($ss->id_entity, $ss->id_teacher, $ss->id_subject, $ss->year);
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
			
			foreach(["", 0, 1] as $email_sent) {
				$new_search = clone $search;
				$new_search->email_sent = $email_sent;
				$counts['email_sent'][$email_sent] = static::_count($new_search);
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
		
		private static function _connectTables($t, $addon) {
			return " {$t} ON ({$t}.id_student = vj.id_entity AND {$t}.id_teacher = vj.id_teacher AND {$t}.id_subject = vj.id_subject AND {$t}.year = vj.year {$addon})";
		} 
		
		private static function _generateQuery($search, $select, $order = true, $ending)
		{
			$main_query = "
				FROM visit_journal vj
				LEFT JOIN reports" . static::_connectTables('r') . "
				LEFT JOIN reports_force " . static::_connectTables('rf') . "
				JOIN reports_helper" . static::_connectTables('rh', 'AND isnull(rh.id_report) = isnull(r.id)') . "
				WHERE vj.type_entity='STUDENT' "
				. (($search->mode == 1 || !isBlank($search->available_for_parents) || !isBlank($search->email_sent)) ? " AND r.id IS NOT NULL" : "")
				. (!isBlank($search->available_for_parents) ? " AND r.available_for_parents={$search->available_for_parents}" : "")
				. (!isBlank($search->email_sent) ? " AND r.email_sent={$search->email_sent}" : "")
				. ($search->year ? " AND vj.year={$search->year}" : "") 
				. ($search->id_teacher ? " AND vj.id_teacher={$search->id_teacher}" : "")
				. (($search->id_subject) ? " AND vj.id_subject={$search->id_subject}" : "")
				. (($search->mode > 1 && $search->mode < 4) ? " AND (r.id IS NULL AND rh.lesson_count" . ($search->mode == 2 ? ">=8 AND rf.id IS NULL" : "<8") . ")" : "")
				. (($search->mode == 4) ? " AND rf.id IS NOT NULL AND r.id IS NULL" : "")
				. " GROUP BY vj.id_entity, vj.id_subject, vj.id_teacher, vj.year, r.id "
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
		public static function findAll($params = [])
		{
			if (! isset($params['condition'])) {
				$params['condition'] = 'in_egecentr >= 1';
			} else {
				$params['condition'] .= ' AND in_egecentr >= 1';
			}

			return parent::findAll($params);
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
			
			$t = Teacher::findById($tutor_id);
			$t->calcHoldCoeff();
			
			// общий
			$Teachers = Teacher::findAll([
				"condition" => "in_egecentr > 0",
				"order" => "last_name ASC",
			]);

            foreach ($Teachers as $Teacher) {
                $Teacher->calcHoldCoeff(['fact_lesson_cnt' => true]);
            }
            
            $coeff_total = [];
            foreach ([0, 9, 10, 11] as $grade) {
	            $numerator = 0;
				$denominator = 0;
				foreach($Teachers as $Teacher) {
					if ($grade) {
						if ($Teacher->loss_by_grade[$grade]) {
							$numerator += $Teacher->total_lessons_by_grade[$grade] - $Teacher->loss_by_grade[$grade];
							$denominator += $Teacher->total_lessons_by_grade[$grade];
						}
					} else {
						$numerator += $Teacher->total_lessons - $Teacher->loss;
						$denominator += $Teacher->total_lessons;
					}
				}
				if (! $denominator) {
					$coeff_total[$grade] = 0;
				} else {
					$coeff_total[$grade] = round(100 * $numerator / $denominator);
				}
            }
            
			return [
				'ec_lesson_count' 		=> $ec_lesson_count,
				'ec_review_count' 		=> $ec_review_count,
				'ec_review_avg' 		=> $ec_review_avg,
				'hold_coeff'			=> $t->hold_coeff,
				'hold_coeff_by_grade' 	=> $t->hold_coeff_by_grade,
				'coeff_total'			=> $coeff_total,
				'total_lessons_by_grade' => $ec_lesson_count_by_grade,
			];

		}
		
		/**
		 * Коэффициент удержания препода.
		 * 
		 * @param bool $group_id		Если передан group_id, то коэффициент считается только для указанной группы.
		 */
		public function calcHoldCoeff($params = [])
		{
			$this->loss = 0; // изначально потеря = 0;
			$this->loss_by_grade = []; // потеря по классам;
			$this->loss_data = []; // будем хранить данные о потерях, для проверки результатов и т. д.;

            $this->total_lessons = 0;        // количество занятий за которые препод ответственнен
            $this->total_lessons_data = [];        // количество занятий за которые препод ответственнен
            $this->total_lessons_by_grade = []; // количество занятий по классам за которые препод ответственнен

            $this->fact_lesson_cnt = []; // данные фактического посещения студентов + препода по классам -> группам.

            // опции подсчета: по группам, по классам
            $_group_id = isset($params['group_id']) ? $params['group_id']: false;

            //получаем все группы препода.
			$condition[] = "id_entity = {$this->id}";
			$condition[] = "type_entity = '".Teacher::USER_TYPE."'";
			$condition[] = $_group_id ? "id_group = {$_group_id}" : '1';

            $query = "select distinct id_group, grade from visit_journal where ".implode(" and ", $condition);
            $result = dbConnection()->query($query);
            $groups = [];
            if ($result) {
                while ($group = $result->fetch_array(MYSQLI_NUM)) {
                    $groups[] = $group;
                }
            }

            foreach ($groups as list($group_id, $grade)) {
                if (!isset($this->loss_by_grade[$grade]))
                    $this->loss_by_grade[$grade] = 0;

                // считать фактические посещения: все посещения где препод = this.id или id = this.id
                if ($params['fact_lesson_cnt']) {
                    $query = "select count(*) as lesson_count ".
                             "from visit_journal ".
                             "where id_group = {$group_id} and id_teacher = {$this->id} ";

                    $this->fact_lesson_cnt[$grade][$group_id] = dbConnection()->query($query)->fetch_object()->lesson_count;;
                }

                //получаем последнее посещение всех студентов группы.
                $query = "select id_entity as id, lesson_date as last_lesson, id_teacher as last_teacher ".
                         "from (select * from visit_journal where id_group = {$group_id} and type_entity = '".Student::USER_TYPE."' order by lesson_date desc) v ".
                         "group by id_entity";
                $result = dbConnection()->query($query);


                while ($result && $student = $result->fetch_object()) {
                    if (!isset($this->total_lessons_data[$grade][$group_id]))
                        $this->total_lessons_data[$grade][$group_id] = 0;

					// первое занятие ученика и препода
					$query = "select lesson_date as first_common_lesson ".
							 "from visit_journal ".
							 "where id_group = {$group_id} and type_entity = '".Student::USER_TYPE."' and id_entity = {$student->id} and id_teacher = {$this->id} ".
							 "order by lesson_date asc limit 1";
					$first_common_lesson = dbConnection()->query($query)->fetch_object()->first_common_lesson;

					// если последный препод студента был этот препод, то считаем потери.
					if ($student->last_teacher == $this->id) {
						$loss = GroupSchedule::count([
									"condition" => "id_group = {$group_id} and date > '{$student->last_lesson}' and date < now() and cancelled = 0"
								]);

						// начиная первого занятия студент-препода до конца таблицы
						$total_lessons = GroupSchedule::count([
											"condition" => "id_group = {$group_id} and date >= '{$first_common_lesson}' and date <= now() and cancelled = 0"
										 ]);
						$this->total_lessons += $total_lessons;
						$this->total_lessons_data[$grade][$group_id] += $total_lessons;

						if ($loss) {
							$this->loss += $loss;
							$this->loss_by_grade[$grade] += $loss;
							$this->loss_data[$grade][$group_id][$student->id] = ['loss' => $loss, 'first_common_lesson' => $first_common_lesson, 'total_lessons' => $total_lessons];
						}
					} else {
						// если студент отвалился до урока препода, то не считаем период.
						// иначе берем период от начала первого занятия до конца таблицы.
						if (new DateTime($first_common_lesson) < new DateTime($student->last_lesson)) {
                            $total_lessons = GroupSchedule::count([
                                "condition" => "id_group = {$group_id} and date >= '{$first_common_lesson}' and date < now() and cancelled = 0"
                            ]);
                            $this->total_lessons += $total_lessons;
                            $this->total_lessons_data[$grade][$group_id] += $total_lessons;
                        }
					}
				}
            }

            $this->hold_coeff = $this->total_lessons ? round(100*($this->total_lessons - $this->loss)/$this->total_lessons) : 0;

            foreach ([9, 10, 11] as $grade) {
                $this->fact_lesson_cnt_by_grade[$grade] = array_sum($this->fact_lesson_cnt[$grade]);

                $total_lessons = $this->total_lessons_by_grade[$grade] = isset($this->total_lessons_data[$grade]) ? array_sum($this->total_lessons_data[$grade]) : 0;
                $this->hold_coeff_by_grade[$grade] = $total_lessons ? round(100*($total_lessons - $this->loss_by_grade[$grade])/$total_lessons) : 0;
            }
            $this->fact_lesson_total_cnt = array_sum($this->fact_lesson_cnt_by_grade);
        }

    }

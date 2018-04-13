<?php
	class Student extends Model
	{

		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/

		public static $mysql_table	= "students";

		protected $_inline_data = ["branches"]; // Предметы (в БД хранятся строкой "1, 2, 3" – а тут в массиве

		// Номера телефонов
		public static $_phone_fields = ["phone", "phone2", "phone3"];

		// тип маркера
		const MARKER_OWNER 	= "STUDENT";
		const USER_TYPE		= "STUDENT";
		const PER_PAGE		= 99999;

        const UPLOAD_DIR = 'img/students/';
        const NO_PHOTO   = 'no-profile-img.gif';

        /*====================================== СИСТЕМНЫЕ ФУНКЦИИ ======================================*/

		public function __construct($array, $light = false)
		{
			parent::__construct($array);

			if (! $light) {
                // Добавляем связи
                $this->Representative = Representative::findById($this->id_representative);
                $this->Passport = Passport::findById($this->id_passport);
                if ($this->id) {
                    $user_data = self::dbConnection()->query('select photo_extension, has_photo_cropped from users where id_entity = ' . $this->id)->fetch_object();
                    $this->photo_extension = $user_data->photo_extension;
                    $this->has_photo_cropped = $user_data->has_photo_cropped;
                    $this->has_photo_original = $this->hasPhotoOriginal();
                    $this->photo_original_size = $this->photoOriginalSize();
                    $this->photo_cropped_size = $this->photoCroppedSize();
                    $this->photo_url = $this->photoUrl();
                }
            }

            if ($this->grade == Grades::EXTERNAL) {
                $this->grade_label = 'экстернат';
                $this->grade_short = 'Э';
            } else {
                $this->grade_label = $this->grade . ' класс';
                $this->grade_short = $this->grade;
            }

            $this->profile_link = "student/{$this->id}";
        }

        public function photoPath($addon = '')
        {
            return static::UPLOAD_DIR . $this->id . $addon . '.' . $this->photo_extension;
        }

        public function photoUrl()
        {
            if ($this->hasPhotoCropped()) {
                $photo = $this->id . '.' . $this->photo_extension;
            } else {
                $photo = static::NO_PHOTO;
            }
            return static::UPLOAD_DIR . $photo;
        }

        public function hasPhotoOriginal()
        {
            return file_exists($this->photoPath('_original'));
        }

        public function hasPhotoCropped()
        {
            return file_exists($this->photoPath());
        }

        public function photoCroppedSize()
        {
            if ($this->hasPhotoCropped()) {
                return filesize($this->photoPath());
            } else {
                return 0;
            }
        }

        public function photoOriginalSize()
        {
            if ($this->hasPhotoOriginal()) {
                return filesize($this->photoPath('_original'));
            } else {
                return 0;
            }
        }

				public function afterSave()
				{
					// синхронизация email
					$user = User::byType($this->id, self::USER_TYPE);
					if ($user) {
						$user->email = $this->email;
						$user->phone = $this->phone;
						$user->first_name = $this->first_name;
						$user->last_name = $this->last_name;
						$user->middle_name = $this->middle_name;
						$user->save();
					}
				}

		/*====================================== СТАТИЧЕСКИЕ ФУНКЦИИ ======================================*/
		public static function getReportCount($id_student, $year = null)
		{
			return Report::count([
				"condition" => "id_student=$id_student AND available_for_parents=1" . ($year ? " AND year={$year}" : '')
			]);
		}

		public function name($order = 'fio')
		{
			return getName($this->last_name, $this->first_name, $this->middle_name, $order);
		}

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

		public function getBar()
		{
			return Freetime::getStudentBar($this->id);
		}

		public static function reviewsNeeded()
		{

			$VisitJournal = self::getExistedTeachers(User::fromSession()->id_entity);

			$count = 0;
			if ($VisitJournal) {
				foreach ($VisitJournal as $VJ) {
					$has_review = TeacherReview::count([
						"condition" => "id_teacher={$VJ->id_teacher} AND id_student={$VJ->id_entity} AND id_subject={$VJ->id_subject} AND year={$VJ->year}
							AND rating > 0 AND comment!=''"
					]);

					if (!$has_review) {
						$count++;
					}
				}
			}

			return $count;
		}

		/**
		 * Получает всех преподавателей, с которым у ученика когда-либо были занятия.
		 *
		 */
		public static function getExistedTeachers($id_student)
		{
			return VisitJournal::findAll([
				"condition" => "id_entity=$id_student AND type_entity='" . self::USER_TYPE . "' AND presence=1",
				"group"		=> "id_entity, id_subject, id_teacher"
			]);
		}

		/**
		 * разрешен вход только тем, у кого последняя версия договора в этом году имеет зеленый или желтый предмет
		 * @param  [type]  $id_student [description]
		 * @return boolean             [description]
		 */
		public static function isBanned($id_student)
		{
			$query = dbConnection()->query("
                SELECT id FROM contracts c
                JOIN contract_info ci ON ci.id_contract = c.id_contract
                WHERE ci.id_student={$id_student} AND c.current_version=1 AND ci.year=" . academicYear() . "
                ORDER BY id DESC
                LIMIT 1
            ");
			if ($query->num_rows) {
				$last_contract_id = $query->fetch_object()->id;
				return ! ContractSubject::count([
					'condition' => "id_contract={$last_contract_id} and status>1"
				]);
			}
			return true;
		}

		/**
		 * Получить человеко-предметы без групп.
		 *
		 * @access public
		 * @static
		 * @return void
		 */
		public static function getWithoutGroup()
		{
            // @contract-refactored
			$result = dbConnection()->query("
				SELECT 	UUID() as unique_id, s.id, s.branches, s.first_name, s.last_name, s.middle_name,
						cs.id_subject, cs.status, cs.count,
						ci.*, EXISTS(SELECT 1 FROM groups g WHERE g.id_subject = cs.id_subject AND FIND_IN_SET(s.id, g.students) AND ci.year = g.year) as in_group
				FROM students s
				JOIN contract_info ci on (ci.id_student = s.id and ci.id_contract = (
					select max(id_contract)
					from contract_info ci2
					where ci2.year = ci.year and ci.id_student = ci2.id_student
				))
				JOIN contracts c on c.id_contract = ci.id_contract
				LEFT JOIN contract_subjects cs on cs.id_contract = c.id
				WHERE c.current_version=1 AND cs.id_subject > 0 AND cs.status > 1 and ci.year=2017
			");

			while ($row = $result->fetch_assoc()) {
				$student_branches = explode(",", $row['branches']);
				$row['branches'] = $student_branches;
				foreach ($student_branches as $id_branch) {
					$row['branch_short'][$id_branch] = Branches::getShortColoredById($id_branch);
				}

				$Students[] = $row;
			}

			return $Students;
		}

		/**
		 * Уже было хотя бы одно занятие
		 */
		public function alreadyHadLesson($id_group)
		{
			return VisitJournal::count([
				"condition" => "id_entity={$this->id} AND type_entity='STUDENT' AND presence=1 AND id_group=$id_group"
			]);
		}

		public static function alreadyHadLessonStatic($id_student, $id_group)
		{
			return VisitJournal::count([
				"condition" => "id_entity={$id_student} AND type_entity='STUDENT' AND presence=1 AND id_group=$id_group"
			]);
		}


		// Удаляет ученика и всё, что с ним связано
		public static function fullDelete($id_student)
		{
			$Student = Student::findById($id_student);


            dbConnection()->query("
                DELETE c, ci, cs FROM contract_info ci
                LEFT JOIN contracts c on c.id_contract = ci.id_contract
                LEFT JOIN contract_subjects cs on cs.id_contract = c.id
                WHERE ci.id_student={$id_student}
            ");

			# Метки
			Marker::deleteAll([
				"condition" => "id_owner=$id_student AND owner='STUDENT'"
			]);

			# Платежи
			Payment::deleteAll([
				"condition" => "entity_id=$id_student and entity_type='".Student::USER_TYPE."'"
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

		public function getAwaitingSmsStatuses($id_group)
		{
			$Group = Group::findById($id_group);
			$subject = Subjects::$dative[$Group->id_subject];

			$student_phones = [];
			foreach (static::$_phone_fields as $phone_field) {
				if (!empty($this->{$phone_field})) {
					$student_phones[] = "'" . $this->{$phone_field} . "'";
				}
			}

			$condition = "message LIKE '%ожидается на первое занятие по $subject%' AND number IN (". implode(",", $student_phones) .") AND id_status=";

			if (SMS::count(["condition" => $condition."103"])) {
				$student_awaiting_status = 1;
			} else
			if (SMS::count(["condition" => $condition."102"])) {
				$student_awaiting_status = 2;
			} else {
				$student_awaiting_status = 3;
			}

			if ($this->Representative) {
				$representative_phones = [];
				foreach (static::$_phone_fields as $phone_field) {
					if (!empty($this->Representative->{$phone_field})) {
						$representative_phones[] = "'" . $this->Representative->{$phone_field} . "'";
					}
				}

				$condition = "message LIKE '%ожидается на первое занятие по $subject%' AND number IN (". implode(",", $representative_phones) .") AND id_status=";

				if (SMS::count(["condition" => $condition."103"])) {
					$representative_awaiting_status = 1;
				} else
				if (SMS::count(["condition" => $condition."102"])) {
					$representative_awaiting_status = 2;
				} else {
					$representative_awaiting_status = 3;
				}
			}

			return [
				'student_awaiting_status' 			=> $student_awaiting_status,
				'representative_awaiting_status' 	=> $representative_awaiting_status,
			];
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

		public function getReports()
		{
			$Reports = Report::findAll([
				"condition" => "id_student=" . $this->id
			]);

			foreach ($Reports as &$Report) {
				$Report->Teacher = Teacher::findById($Report->id_teacher);
			}

			return $Reports;
		}

		public function getReportsStatic($id_student)
		{
			return Teacher::getReportData(1, [], $id_student);
		}

		/**
		 * Получить договоры студента.
		 *
		 */
		public function getContracts()
		{
			return Contract::findAll([
				"condition"	=> "id_contract IN (" . Contract::getIdsByStudent($this->id) . ")",
                "order"     => "str_to_date(date, '%d.%m.%Y'), date_changed"
			]);
		}

		/**
		 * Получить договоры тестирования студента.
		 *
		 */
		public function getContractsTest()
		{
			return ContractTest::findAll([
				"condition"	=> "id_contract IN (" . ContractTest::getIdsByStudent($this->id) . ")",
                "order"     => "str_to_date(date, '%d.%m.%Y'), date_changed"
			]);
		}

		public static function groups($id_student, $func = 'findAll')
		{
			return Group::{$func}([
				'condition' => "FIND_IN_SET({$id_student}, students) AND is_unplanned=0",
			]);
		}

		// Подсчитывает кол-во групп (кружочек в ЛК ученика)
		public static function countGroups($id_student)
		{
			// @refactored
			return Group::count([
				"condition" => "CONCAT(',', CONCAT(students, ',')) LIKE '%,{$id_student},%' AND ended = 0 AND is_dump=0 AND is_unplanned=0"
			]);
		}

		/**
		 * Получить постудний договор студента.
		 *
		 */
		public function getLastContract($year = false, $light = false)
		{
            $query = dbConnection()->query("
                SELECT id FROM contracts c
                JOIN contract_info ci ON ci.id_contract = c.id_contract
                WHERE ci.id_student={$this->id} AND c.current_version=1" . ($year ? " AND ci.year={$year}" : '') . "
                ORDER BY id DESC
                LIMIT 1
            ");
            if ($query->num_rows) {
                return Contract::findById($query->fetch_object()->id, $light);
            } else {
                return false;
            }
		}

		/**
		 * Получить пол.
		 *
		 * 1 - мужской, 2 - женский
		 */
		public function getGender()
		{
			$nc = new NCLNameCaseRu();

			return $nc->genderDetect($this->last_name . " " . $this->first_name . " " . $this->middle_name);
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

		public function getRequests()
		{
			return Request::findAll([
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
			*/
			foreach ($Requsts as $Requst) {
				if (emptyDate($Requst->date)) {
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
		public function getPayments($year = null)
		{
			return Payment::findAll([
				"condition" => "entity_id=" . $this->id." and entity_type = '".Student::USER_TYPE."'" . ($year ? " AND year={$year}" : '')
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

		public function getTeacherLikes()
		{
			$TeacherLikes = TeacherReview::findAll([
				"condition" => "id_student={$this->id}"
			]);

			foreach ($TeacherLikes as &$Like) {
				$Like->Teacher = Teacher::findById($Like->id_teacher);
			}

			return $TeacherLikes;
		}

		public static function getTeacherLikesStatic($id_student)
		{
			$TeacherLikes = TeacherReview::findAll([
				"condition" => "id_student=$id_student"
			]);

			foreach ($TeacherLikes as &$Like) {
				$Like->Teacher = Teacher::findById($Like->id_teacher);
			}

			return $TeacherLikes;
		}

		public static function getPhoneErrors()
		{
			$Requests = Request::findAll([
				"condition" => "adding=0"
			]);

			$students = [];
			$student_ids = [];
			foreach ($Requests as $Request) {
				foreach (Student::$_phone_fields as $phone_field) {
					$request_phone = $Request->{$phone_field};
					if (!empty($request_phone)) {
						if (isDuplicate($request_phone, $Request->id)) {
							if (!in_array($Request->Student->id, $student_ids)) {
								$students[] = $Request->Student;
								$student_ids[] = $Request->Student->id;
							}
							break;
						}
					}

					$student_phone = $Request->Student->{$phone_field};
					if (!empty($student_phone)) {
						if (isDuplicate($student_phone, $Request->id)) {
							if (!in_array($Request->Student->id, $student_ids)) {
								$students[] = $Request->Student;
								$student_ids[] = $Request->Student->id;
							}
							break;
						}
					}

					if ($Request->Student->Representative) {
						$representative_phone = $Request->Student->Representative->{$phone_field};
						if (!empty($representative_phone)) {
							if (isDuplicate($representative_phone, $Request->id)) {
								if (!in_array($Request->Student->id, $student_ids)) {
									$students[] = $Request->Student;
									$student_ids[] = $Request->Student->id;
								}
								break;
							}
						}
					}
				}
			}

			return $students;
		}


		public function getVisits($params = [])
		{
		    $condition = "id_entity={$this->id} AND type_entity='STUDENT'";

            if (count($params)) {
                $condition .= ' AND ' . implode(' AND ', array_map(function($key, $value) {
                    return "$key='$value'";
                }, array_keys($params), $params));
            }

            $visits = VisitJournal::findAll([
				"condition" => $condition,
                "order" => "lesson_date ASC, lesson_time ASC"
			]);

			return $visits;
		}

		public static function getVisitsStatic($id_student)
		{
			return VisitJournal::findAll([
				"condition" => "id_entity={$id_student} AND type_entity='STUDENT'"
			]);
		}

		/**
		 * Получить только список ID => ФИО. C договорами
		 *
		 */
		public static function getAllList()
		{
            $query = dbConnection()->query("
				SELECT s.id, CONCAT_WS(' ', s.last_name, s.first_name, s.middle_name) as name
				FROM students s
				WHERE EXISTS(SELECT 1 FROM contract_info WHERE id_student=s.id)
				ORDER BY name ASC
			");

			while ($row = $query->fetch_object()) {
				$students[] = $row;
			}

			return $students;
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

		private static function _getPhoneNumbers($Object)
		{
			$text = "";
			foreach (Student::$_phone_fields as $phone_field) {
				$phone = $Object->{$phone_field};
				if (!empty($phone)) {
					$text .= $phone;
				}
			}
			return $text;
		}


		/*
		 * Получить легкую версию (имя + id)
		 */
		public static function getLight($id)
		{
			return dbConnection()->query("
				SELECT s.id, s.first_name, s.last_name, s.middle_name, s.id_user_review, s.grade, u.login as user_login, u.color
				FROM " . static::$mysql_table . " s
				LEFT JOIN users u ON u.id = s.id_user_review
				WHERE s.id = " . $id . "
				ORDER BY s.last_name, s.first_name, s.middle_name ASC")
			->fetch_object();
		}



		/*
		 * Получить данные для основного модуля
		 * $page==-1 – получить без лимита
		 */
		public static function getData($page)
		{
			if (!$page) {
				$page = 1;
			}
			// С какой записи начинать отображение, по формуле
			$start_from = ($page - 1) * Student::PER_PAGE;

			$search = isset($_COOKIE['clients']) ? json_decode($_COOKIE['clients']) : (object)[];


			// получаем данные
			$query = static::_generateQuery($search, ($page == -1 ? "DISTINCT(s.id)" : "DISTINCT(s.id),
				concat(c.payments_split, '-', c.payments_queue) as contract_split,
				ci.year as contract_year,
				s.payment_status,
				(select MAX(STR_TO_DATE(date, '%d.%m.%Y')) " .
                "from payments p " .
                "where p.entity_type = '" . Student::USER_TYPE . "' and p.entity_id = s.id " . (! isBlank($search->year) ? "and p.year={$search->year}" : '') . ") as latest_payment_date,
				(select count(*) from contract_subjects where id_contract=c.id and status=3) as green_cnt,
				(select count(*) from contract_subjects where id_contract=c.id and status=2) as yellow_cnt,
				(select count(*) from contract_subjects where id_contract=c.id and status=1) as red_cnt,
				s.first_name, s.last_name, s.middle_name " . (! isBlank($search->year) ? ", ss.sum" : '') ));
			$result = dbConnection()->query($query . ($page == -1 ? "" : " LIMIT {$start_from}, " . Student::PER_PAGE));

            $data = [];
			$totals = ['sum' => 0];
            if ($result->num_rows) {
                while ($row = $result->fetch_object()) {
                    if ($page == -1) {
                        $data[] = $row->id;
                    } else {
						$totals['sum']  += intval($row->sum);

						// статус клиента
						$total_subject_cnt = $row->green_cnt + $row->yellow_cnt + $row->red_cnt;
						if ($row->red_cnt == $total_subject_cnt) {
							$row->status = 'red';
						} else if ($row->yellow_cnt > 0 && $row->green_cnt == 0) {
							$row->status = 'yellow';
						} else {
							$row->status = 'none';
						}
                        $data[] = $row;
                    }
                }
            }

			return [
				'data' 	=> $data,
				'totals' => $totals,
			];
		}

		private static function _count($search) {
			return dbConnection()
					->query(static::_generateQuery($search, "COUNT(DISTINCT s.id) AS cnt"))
					->fetch_object()
					->cnt;
		}


		private static function _generateQuery($search, $select)
		{
			$status_conditions = [];
			if (in_array('red', $search->statuses)) {
				$status_conditions[] = '(red_cnt = (red_cnt + yellow_cnt + green_cnt))';
			}
			if (in_array('yellow', $search->statuses)) {
				$status_conditions[] = '(yellow_cnt > 0 and green_cnt = 0)';
			}
			if (in_array('green', $search->statuses)) {
				$status_conditions[] = '(green_cnt > 0)';
			}
			// все рублевые стобцы, кроме депозита убрать
			// добавить "дата последнего платежа"

            // @contract-refactored
            // @have-to-refactor c.id_student – depricated @refactored
			$main_query = "
				FROM students s " .
                "  JOIN (select max(id_contract) as id_contract, id_student, year, grade from contract_info group by id_student, grade, year) ci ON (ci.id_student = s.id" . (! isBlank($search->year) ? " AND ci.year = {$search->year}" : '') . ")" .
				( ! isBlank($search->error) && $search->error == 0 ? " JOIN users u ON u.id_entity = s.id AND type = 'STUDENT' AND u.photo_extension = '' " : "") .
				( ! isBlank($search->error) && $search->error == 1 ? " JOIN users u ON u.id_entity = s.id AND type = 'STUDENT' AND u.photo_extension <> '' AND u.has_photo_cropped = 0 " : "") . "
				JOIN contracts c ON (c.id_contract = ci.id_contract AND c.current_version = 1)
				" . (! isBlank($search->year) ? " LEFT JOIN student_sums ss ON (ss.id_student = s.id AND ss.year = {$search->year}) " : '') . "
				WHERE true "
				. (!isBlank($search->error) && $search->error == 2 ? " AND NOT EXISTS (SELECT 1 FROM freetime f WHERE f.id_entity = s.id AND f.type_entity = '".Student::USER_TYPE."')" : "")
				. (!isBlank($search->error) && $search->error == 3 ? " AND ci.grade = " . Grades::EXTERNAL : "")
				. (count($search->payment_statuses) ? ' and s.payment_status in (' . implode(',', $search->payment_statuses) . ')' : '')
				. (count($status_conditions) ? 'having ' . implode(' OR ', $status_conditions) : '')
				. " ORDER BY " . ((isset($search->order) && !isBlank($search->year)) ? " ss.sum {$search->order}, " : "") . " s.last_name, s.first_name, s.middle_name
			";
			// exit("SELECT " . $select . $main_query);
			return "SELECT " . $select . $main_query;
		}

        public static function getDebt($id_student = false)
        {
            if (LOCAL_DEVELOPMENT) {
                return 0;
            }
            $query =
                "select " .
                "(select ifnull(sum(c.sum), 0) " .
                "from contract_info ci " .
                "join contracts c on c.id_contract = ci.id_contract " .
                "where ci.year = " . Years::getAcademic() .  " and c.current_version = 1 " . ($id_student ? " and ci.id_student = {$id_student} " : "") . ")" .
                " + " .
                "(select ifnull(sum(c.sum), 0) " .
                "from contract_info_test ci " .
                "join contracts_test c on c.id_contract = ci.id_contract " .
                "where ci.year = " . Years::getAcademic() .  " and c.current_version = 1 " . ($id_student ? " and ci.id_student = {$id_student} " : "") . ")" .
                " - " .
                "(select ifnull(sum(case when p.id_type = " . PaymentTypes::PAYMENT . " then p.sum else -p.sum end), 0)" .
                "from payments p " .
                "where p.entity_type = '" . Student::USER_TYPE . "' " . ($id_student ? " and p.entity_id = {$id_student} " : "") . " and p.year = " . Years::getAcademic() . ") " .
                " as debt";

            return static::dbConnection()->query($query)->fetch_object()->debt;
        }

        public static function getTotalDebt()
        {
            return static::getDebt();
        }

		public static function getBalance($id_student)
		{
			$items = [];

			// кешируем группы
			$groups = [];

			/* вычеты за проведенные занятия */
			$lessons = VisitJournal::findAll([
                "condition" => "id_entity=$id_student AND type_entity='" . self::USER_TYPE . "'"
            ]);

			foreach($lessons as $lesson) {
				if (! isset($groups[$lesson->id_group])) {
					$group = dbConnection()->query("select * from groups where id={$lesson->id_group}")->fetch_object();
					$group->cabinet_ids = Group::getCabinetIds($group->id);
					$group->cabinet = Cabinet::getBlock($group->cabinet_ids[0]);
					$groups[$lesson->id_group] = $group;
				}
				$group = $groups[$lesson->id_group];
				$items[$lesson->year][$lesson->lesson_date][] = [
					'sum' 		  => intval($lesson->price) * -1,
					'comment'	  => "занятие " . date("d.m.y", strtotime($lesson->lesson_date)) . " в {$lesson->lesson_time}, группа {$lesson->id_group} (" . Subjects::$three_letters[$group->id_subject] . "-" . Grades::$short[$group->grade] . "), кабинет " . $group->cabinet['label'],
					'credentials' => User::findById($lesson->id_user_saved)->login . ' ' . dateFormat($lesson->date),
					'date'		  => $lesson->date,
				];
			}

			/* платежи */
			$payments = Payment::findAll([
				"condition" => "entity_id={$id_student} and entity_type='" . self::USER_TYPE . "' "
			]);

			foreach($payments as $payment) {
				$sum = intval($payment->sum);
				$comment = Payment::$all[$payment->id_status];
				if ($payment->id_type == PaymentTypes::RETURNN) {
					$sum = $sum * -1;
					$comment = PaymentTypes::$all[$payment->id_type];
				}
				if ($payment->category > 1) {
					$comment .= ' (' . PaymentTypes::$categories[$payment->category] . ')';
				}

				$items[$payment->year][fromDotDate($payment->date)][] = [
					'sum' 		  => $sum,
					'comment' 	  => $comment,
					'credentials' => $payment->user_login . ' ' . dateFormat($payment->first_save_date),
					'date' 		  => $payment->first_save_date,
				];
			}

			/* доп услуги */
			$additional_payments = StudentAdditionalPayment::get($id_student);

			foreach($additional_payments as $payment) {
				$items[$payment->year][fromDotDate($payment->date)][] = [
					'sum' 		  => intval($payment->sum) * -1,
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

		/**
		 * Получить ID групп, которые ученик когда-либо посещал
		 */
		public static function getGroupIdsEverVisited($id_student)
		{
			$query = dbConnection()->query("select id_group from visit_journal where type_entity='STUDENT' and id_entity={$id_student} group by id_group");
			$group_ids = [];
			while ($row = $query->fetch_object()) {
				$group_ids[] = $row->id_group;
			}
			return $group_ids;
		}

		/**
		 * Получить даты первого и последнего неотмененного занятия
		 */
		public static function getFirstAndLastLessonDates($Lessons)
		{
			$max_date = '0000-00-00';
			$min_date = '9999-99-99';

			foreach($Lessons as $Lesson) {
				if (! $Lesson->cancelled && $Lesson->is_conducted) {
					if ($Lesson->lesson_date > $max_date) {
						$max_date = $Lesson->lesson_date;
					}
					if ($Lesson->lesson_date < $min_date) {
						$min_date = $Lesson->lesson_date;
					}
				}
			}

			return [$min_date, $max_date];
		}

		/**
		 * Ученик состоит в группе в данный момент
		 */
		public static function isInGroup($id_student, $id_group)
		{
			return dbConnection()->query("select count(*) as cnt from groups where FIND_IN_SET({$id_student}, students) AND id={$id_group}")
				->fetch_object()->cnt ? true : false;
		}

		/**
		 * Получить все уроки ученика
		 */
		public static function getFullSchedule($id_student, $sort_by_month = false)
		{
			$group_ids = self::getGroupIdsEverVisited($id_student);

			$Lessons = [];
			foreach($group_ids as $group_id) {
				$StudentGroupLessons = VisitJournal::getStudentGroupLessons($group_id, $id_student);
				list($first_lesson_date, $last_lesson_date) = Student::getFirstAndLastLessonDates($StudentGroupLessons);
				$student_is_in_group = Student::isInGroup($id_student, $group_id);

				// нужно не отображать:
				// 1) отмененные занятия до 1го занятия ученика в группе
				// 2) будущие планируемые и отмененные занятия, если ученик больше не присутствует в группе
				// $Lessons[$group_id] = $StudentGroupLessons;
				$Lessons[$group_id] = array_filter($StudentGroupLessons, function($Lesson) use ($student_is_in_group, $first_lesson_date, $last_lesson_date) {
					if ($Lesson->cancelled && ($Lesson->lesson_date < $first_lesson_date)) {
						return false;
					}
					if ($Lesson->is_planned && ! $student_is_in_group && ($Lesson->lesson_date > $last_lesson_date)) {
						return false;
					}
					return true;
				});
			}

			$AdditionalLessons = AdditionalLesson::getByEntity(Student::USER_TYPE, $id_student);

			foreach($AdditionalLessons as $Lesson) {
				$ConductedLesson = VisitJournal::find(['condition' => "type_entity='STUDENT' AND entry_id=" . $Lesson['entry_id']]);
				if ($ConductedLesson) {
					$L = $ConductedLesson;
				} else {
					$L = (object)$Lesson;
				}
				$L->id_group = -1;
				$Lessons[-1][] = $L;
			}

			$years = [];
			foreach($Lessons as $group_id => $GroupLessons) {
				foreach($GroupLessons as $Lesson) {
					$Lesson->Teacher = Teacher::getLight($Lesson->id_teacher);
					if (! in_array($Lesson->year, $years)) {
						$years[] = $Lesson->year;
					}
				}
			}

			$LessonsSorted = [];
			foreach($Lessons as $group_id => $GroupLessons) {
				foreach($GroupLessons as $Lesson) {
					if ($sort_by_month) {
						$LessonsSorted[$Lesson->year][date('n', strtotime($Lesson->lesson_date))][] = $Lesson;
					} else {
						$LessonsSorted[$Lesson->year][$group_id][] = $Lesson;
					}
				}
			}

			sort($years);

			return (object)[
				'Lessons' => $LessonsSorted,
				'years' => $years,
			];
		}
    }

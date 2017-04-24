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
		const PER_PAGE		= 30;

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

		}

		/*====================================== СТАТИЧЕСКИЕ ФУНКЦИИ ======================================*/
		public static function getReportCount($id_student)
		{
			return Report::count([
				"condition" => "id_student=$id_student AND available_for_parents=1"
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
				SELECT 	s.id, s.branches, s.first_name, s.last_name, s.middle_name,
						cs.id_subject, cs.status, cs.count,
						contract_info.*
				FROM students s
                    JOIN contract_info on contract_info.id_student = s.id
					JOIN contracts c on c.id_contract = contract_info.id_contract
					LEFT JOIN contract_subjects cs on cs.id_contract = c.id
					WHERE c.current_version=1 AND cs.id_subject > 0 AND cs.status > 1
                        AND NOT EXISTS(SELECT 1 FROM groups g WHERE g.id_subject = cs.id_subject AND FIND_IN_SET(s.id, g.students) AND contract_info.year = g.year)
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
			$Reports = Report::findAll([
				"condition" => "id_student=" . $id_student
			]);

			foreach ($Reports as &$Report) {
				$Report->Teacher = Teacher::findById($Report->id_teacher);
			}

			return $Reports;
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

		public static function getGroupsStatic($id_student, $with_schedule = false)
		{
			// @refactored
			$Groups = Group::findAll([
				"condition" => "CONCAT(',', CONCAT(students, ',')) LIKE '%,{$id_student},%'"
			]);

			if ($with_schedule) {
				foreach ($Groups as &$Group) {
					$Group->Schedule = $Group->getSchedule(true);
				}
			}

			return $Groups;
		}

		public function getGroups($with_schedule = false)
		{
			// @refactored
			$Groups = Group::findAll([
				"condition" => "CONCAT(',', CONCAT(students, ',')) LIKE '%,{$this->id},%'"
			]);

			if ($with_schedule) {
				foreach ($Groups as &$Group) {
					$Group->Schedule = $Group->getSchedule(true);
				}
			}

			return $Groups;
		}

		// Подсчитывает кол-во групп (кружочек в ЛК ученика)
		public function countGroupsStatic($id_student)
		{
			// @refactored
			return Group::count([
				"condition" => "CONCAT(',', CONCAT(students, ',')) LIKE '%,{$id_student},%' AND ended = 0"
			]);
		}


		public function countGroups()
		{
			return Group::count([
				"condition" => "CONCAT(',', CONCAT(students, ',')) LIKE '%,{$this->id},%'"
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
				"condition" => "entity_id=" . $this->id." and entity_type = '".Student::USER_TYPE."'"
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

		public function getVisitsAndSchedule()
		{
			$visits = VisitJournal::findAll([
				"condition" => "id_entity={$this->id} AND type_entity='STUDENT'"
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

		/*
		 * Планируются ли еще занятия у ученика?
		 * (серые точки в профиле)
		 *
		 */
		public function hasFutureLessons()
		{
			// получаем группы, в которых присутствует ученик
			$group_ids = Group::getIds([
				'condition' => "FIND_IN_SET({$this->id}, students)",
			]);

			foreach ($group_ids as $group_id) {
				if (Group::countFutureScheduleStatic($group_id)) {
					return true;
				}
			}

			return false;
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
			$query = static::_generateQuery($search, ($page == -1 ? "DISTINCT(s.id)" : "DISTINCT(s.id), s.first_name, s.last_name, s.middle_name "));
			$result = dbConnection()->query($query . ($page == -1 ? "" : " LIMIT {$start_from}, " . Student::PER_PAGE));

            $data = [];
            if ($result->num_rows) {
                while ($row = $result->fetch_object()) {
                    if ($page == -1) {
                        $data[] = $row->id;
                    } else {
                        $row->debt = Student::getDebt($row->id);
                        $data[] = $row;
                    }
                }
            }

			if ($page > 0) {
				// counts
				$counts['all'] = static::_count($search);

				foreach(array_merge([""], Years::$all) as $year) {
					$new_search = clone $search;
					$new_search->year = $year;
					$counts['year'][$year] = static::_count($new_search);
				}

				foreach(array_merge([''], range(0,3)) as $error) {
					$new_search = clone $search;
					$new_search->error = $error;
					$counts['error'][$error] = static::_count($new_search);
				}
			}

			return [
				'data' 	=> $data,
				'counts' => $counts,
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
            // @contract-refactored
            // @have-to-refactor c.id_student – depricated @refactored
			$main_query = "
				FROM students s " .
                "  JOIN contract_info ci ON (ci.id_student = s.id" . (! isBlank($search->year) ? " AND ci.year = {$search->year}" : '') . ")" .
				( ! isBlank($search->error) && $search->error == 0 ? " JOIN users u ON u.id_entity = s.id AND type = 'STUDENT' AND u.photo_extension = '' " : "") .
				( ! isBlank($search->error) && $search->error == 1 ? " JOIN users u ON u.id_entity = s.id AND type = 'STUDENT' AND u.photo_extension <> '' AND u.has_photo_cropped = 0 " : "") . "
				JOIN contracts c ON (c.id_contract = ci.id_contract AND c.current_version = 1) WHERE true "
				. (!isBlank($search->error) && $search->error == 2 ? " AND NOT EXISTS (SELECT 1 FROM freetime f WHERE f.id_entity = s.id AND f.type_entity = '".Student::USER_TYPE."')" : "")
				. (!isBlank($search->error) && $search->error == 3 ? " AND ci.grade = " . Grades::EXTERNAL : "")
				. " ORDER BY s.last_name, s.first_name, s.middle_name
			";
			return "SELECT " . $select . $main_query;
		}

        public static function getDebt($id_student = false)
        {
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
    }

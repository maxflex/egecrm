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

		/*====================================== СИСТЕМНЫЕ ФУНКЦИИ ======================================*/

		public function __construct($array)
		{
			parent::__construct($array);

			// Добавляем связи
			$this->Representative	= Representative::findById($this->id_representative);
			$this->Passport			= Passport::findById($this->id_passport);
		}

		public function afterSave()
		{
			$this->updateSearchData();
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

		public function getBar($id_group, $id_branch)
		{
			return Freetime::getStudentBar($id_group, $id_branch, $this->id);
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

		/**
		 * Посчитать студентов с не расторгнутыми договорами.
		 *
		 */
		public static function countWithActiveContract()
		{
			$query = dbConnection()->query("SELECT c.id_student FROM contracts c
				LEFT JOIN contract_subjects cs ON cs.id_contract = c.id WHERE cs.status > 1"
				. Contract::ZERO_OR_NULL_CONDITION_JOIN . " GROUP BY c.id_student");

			return $query->num_rows;
		}

		public static function reviewsNeeded()
		{

			$VisitJournal = self::getExistedTeachers(User::fromSession()->id_entity);

			$count = 0;
			if ($VisitJournal) {
				foreach ($VisitJournal as $VJ) {
					$has_review = TeacherReview::count([
						"condition" => "id_teacher={$VJ->id_teacher} AND id_student={$VJ->id_entity} AND id_subject={$VJ->id_subject}
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

/*
			$group_ids = [];
			foreach ($VisitJournal as $VJ) {
				$group_ids[] = $VJ->id_group;
			}

			if (!$group_ids) {
				return false;
			}

			$VisitJournal = VisitJournal::findAll([
				"condition" => "id_group IN (" . implode(",", $group_ids) . ") AND type_entity='". Teacher::USER_TYPE ."'",
				"group"		=> "id_entity",
			]);

			if ($VisitJournal) {
				foreach ($VisitJournal as $VJ) {
					$teacher_ids[] = $VJ->id_entity;
				}

				return $teacher_ids;
			}

			return false;
*/
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
			$result = dbConnection()->query("
				SELECT 	s.id, s.branches, s.first_name, s.last_name, s.middle_name,
						cs.id_subject, cs.score, cs.status, cs.count,
						c.id as id_contract, c.grade, c.date
				FROM students s
					LEFT JOIN contracts c on c.id_student = s.id
					LEFT JOIN contract_subjects cs on cs.id_contract = c.id
					LEFT JOIN groups g ON (g.id_subject = cs.id_subject AND FIND_IN_SET(s.id, g.students) AND c.year = g.year)
					WHERE c.id IS NOT NULL AND (c.id_contract=0 OR c.id_contract IS NULL) AND g.id IS NULL AND cs.id_subject > 0
						AND cs.status != 1
			");

			while ($row = $result->fetch_assoc()) {
				$student_branches = explode(",", $row['branches']);
				unset($row['branches']);
				foreach ($student_branches as $id_branch) {
					$row['branch_short'][$id_branch] = Branches::getShortColoredById($id_branch);
				}

				$Students[] = $row;
			}

			return $Students;
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


		/**
		 * Получить студентов с договорами.
		 *
		 * $only_active - только активные договоры
		 */
		public static function getWithContract()
		{
			$query = dbConnection()->query("SELECT id_student FROM contracts WHERE true "
				. Contract::ZERO_OR_NULL_CONDITION . " GROUP BY id_student");


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
		 * Посчитать студентов с договорами.
		 *
		 */
		public static function countWithContract()
		{
			$query = dbConnection()->query("SELECT id_student FROM contracts WHERE true "
				. Contract::ZERO_OR_NULL_CONDITION . " GROUP BY id_student");

			return $query->num_rows;
		}

		/**
		 * Получить студентов с договорами.
		 *
		 */
		public static function getWithContractPreCancelled()
		{
			$query = dbConnection()->query("SELECT id_student FROM contracts WHERE true "
				. Contract::ZERO_OR_NULL_CONDITION . " GROUP BY id_student");

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

		public function notifiedInGroupStatic($id_student, $id_group)
		{
			return GroupStudentStatuses::count([
				"condition" => "id_student=" . $id_student . " AND id_group=" . $id_group . " AND notified=1"
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

		public static function getGroupsStatic($id_student)
		{
			// @refactored
			return Group::findAll([
				"condition" => "CONCAT(',', CONCAT(students, ',')) LIKE '%,{$id_student},%' AND ended = 0 "
			]);
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
		 * Получить договоры студента без версий.
		 *
		 */
		public function getActiveContracts()
		{
			return Contract::findAll([
				"condition"	=> "id_student=" . $this->id . Contract::ZERO_OR_NULL_CONDITION
			]);
		}

		/**
		 * Получить постудний договор студента.
		 *
		 */
		public function getLastContract()
		{
			return Contract::find([
				"condition"	=> "id_student=" . $this->id .  Contract::ZERO_OR_NULL_CONDITION,
				"order"		=> "id DESC",
				"limit"		=> "1",
			]);
		}
		
		
		/**
		 * Получить постудний договор студента текущего учебного года.
		 *
		 */
		public function getCurrentYearLastContract()
		{
			return Contract::find([
				"condition"	=> "year = " . (date("Y") - 1) . " AND id_student=" . $this->id .  Contract::ZERO_OR_NULL_CONDITION,
				"order"		=> "id DESC",
				"limit"		=> "1",
			]);
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
				"condition" => "deleted=0 AND id_student=" . $this->id
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

			// $TeacherLikes = GroupTeacherLike::findAll([
			// 	"condition" => "id_student={$this->id} AND id_status > 0"
			// ]);
			//
			// foreach ($TeacherLikes as &$Like) {
			// 	$Like->Teacher = Teacher::findById($Like->id_teacher);
			// }
			//
			// return $TeacherLikes;
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


		public function getVisits()
		{
			$visits = VisitJournal::findAll([
				"condition" => "id_entity={$this->id} AND type_entity='STUDENT'"
			]);

			return $visits;
		}

		public function getVisitsAndSchedule()
		{
			$visits = VisitJournal::findAll([
				"condition" => "id_entity={$this->id} AND type_entity='STUDENT'"
			]);
		}

		public static function getSameSubjectErrors()
		{
			$Students = Student::getWithContract();


			foreach ($Students as $Student) {
				$Groups = $Student->getGroups();

				foreach ($Groups as $Group) {
					$count = Group::count([
						"condition" => "CONCAT(',', CONCAT(students, ',')) LIKE '%,{$Student->id},%' AND id_subject={$Group->id_subject}"
					]);

					if ($count > 1) {
						$return[] = $Student;
					}
				}
			}

			return $return;
		}


		/**
		 * Получить только список ID => ФИО. C договорами
		 *
		 */
		public static function getAllList()
		{
//			$query = dbConnection()->query("
//				SELECT s.id, CONCAT_WS(' ', s.last_name, s.first_name, s.middle_name) as name FROM students s
//					LEFT JOIN contracts c 	ON c.id_student = s.id
//					LEFT JOIN contract_subjects cs on cs.id_contract = c.id
//				WHERE c.id_student IS NOT NULL AND cs.status > 1
//				GROUP BY s.id
//				ORDER BY name ASC
//			");

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
		
		/**
		 * Обновить данные по поиску.
		 *
		 */
		public function updateSearchData()
		{
			$text = "";
			$Requests = $this->getRequests();
			foreach ($Requests as $Request) {
				$text .= $Request->name;
				$text .= self::_getPhoneNumbers($Request);
			}
			// Имя, телефоны ученика и представителя
			$text .= $this->name();
			$text .= self::_getPhoneNumbers($this);
			$text .= $this->email;

			if ($this->Passport) {
				$text .= $this->Passport->series;
				$text .= $this->Passport->number;
			}

			if ($this->Representative) {
				$text .= $this->Representative->name();
				$text .= self::_getPhoneNumbers($this->Representative);
				$text .= $this->Representative->email;
				$text .= $this->Representative->address;

				if ($this->Representative->Passport) {
					$text .= $this->Representative->Passport->series;
					$text .= $this->Representative->Passport->number;
					$text .= $this->Representative->Passport->issued_by;
					$text .= $this->Representative->Passport->address;
				}
			}

			// Последние 4 цифры номер карты
			$Payments = Payment::findAll([
				"condition" => "id_status=" . Payment::PAID_CARD . " AND id_student=" . $this->id . " AND card_number!=''"
			]);
			foreach ($Payments as $Payment) {
				$text .= $Payment->card_number;
			}

			$exists = dbConnection()->query("
				SELECT id_student FROM search_students
				WHERE id_student = {$this->id}
			")->num_rows;

			if ($exists) {
				dbConnection()->query("UPDATE search_students SET search_text = '{$text}' WHERE id_student = {$this->id}");
			} else {
				dbConnection()->query("INSERT INTO search_students (search_text, id_student) VALUES ('{$text}', {$this->id})");
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

	}

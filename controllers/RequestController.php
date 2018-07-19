<?php

	// Контроллер
	class RequestController extends Controller
	{
		public $defaultAction = "List";

		// Папка вьюх
		protected $_viewsFolder	= "request";

		public static $allowed_users = [Admin::USER_TYPE, Teacher::USER_TYPE, Student::USER_TYPE];

		/**
		 * BEFORE ACTION.
		 *
		 */
		public function beforeAction()
		{
			// Скрипт контроллера
			$this->addJs("ng-request-app, dnd-new");
		}

		/**
		 * Информация по заявке.
		 *
		 */
		public function actionEdit($id_student = false)
		{
			$this->setRights([Admin::USER_TYPE, Teacher::USER_TYPE]);

			// Находим заявку по ID
			$Request = Request::findById($_GET["id"]);

			// Если заявка или ученик не установлены
			if (!$Request || !$Request->Student) {
				static::redirect("requests", true);
			}

			// не надо панель рисовать
			$this->_custom_panel = true;

			$mode = ($id_student ? 'student' : 'request');

			$this->addJs("//maps.google.ru/maps/api/js?libraries=places", true);
			$this->addJs("maps.controller");

			if (User::isTeacher()) {
				$this->addCss('teacher');
			}

			# Генерируем данные для ангуляра
			$ang_init_data = angInit([
				'mode' 			=> $mode,
				"id_request"	=> $Request->id,					    // ID текущей заявки
				"id_student"	=> $Request->Student->id,
				'three_letters'	=> Subjects::$three_letters,
                "user"			=> User::fromSession(),
                'academic_year' => academicYear(),
                "Request"		=> $Request,
                "request_duplicates"=> $Request->getDuplicates(true),	// получить дубликаты, включая свой ID
                "Grades"        => Grades::$all,
				"is_teacher"	=> User::isTeacher() ? 1 : 0,
				"headed_students" => User::isTeacher() ? Student::getIds(['condition' => "id_head_teacher=" . User::id()]) : [],
				"headed_teachers" => User::isTeacher() ? Teacher::getIds(['condition' => "id_head_teacher=" . User::id()]) : []
			]);

			# Передача во view
			$this->render("edit", [
				'mode' 			=> $mode,
				"Request"		=> $Request,
				"User"			=> User::fromSession(),
				"ang_init_data"	=> $ang_init_data,
			]);
		}


		/**
		 * Список заявок.
		 *
		 */
		public function actionList()
		{
			$this->setRights([Admin::USER_TYPE]);
			$this->setTabTitle("Заявки ");
            $this->setRightTabTitle('<a href="requests/add" class="link-reverse link-white">создать заявку</a>');

			// Получаем выбранный список заявок
			$id_status	= Request::getIdStatus();
			$page		= isset($_GET['page']) ? $_GET['page'] : 1;

			// Получаем новые заявки
			$Requests = Request::getByPage($page, $id_status);
            $search = (object)[
                'id_status' => $id_status,
                'id_user'   => $_COOKIE['id_user_list'],
            ];

			// Данные для ангуляра
			$ang_init_data = angInit([
				"requests" 				=> $Requests,
                "subjects"				=> Subjects::$all,
				"branches"				=> Branches::getAll(),
				"request_statuses"		=> RequestStatuses::get(),
				"chosen_list"			=> $id_status,
                "currentPage"			=> $page,
                "counts"                => [
                    'users' => Request::getUserCounts($search),
                    'requests' => Request::getAllStatusesCount($search)
                ],
                "user"					=> User::fromSession()
            ]);

			$this->render("list", [
				"ang_init_data"	=> $ang_init_data,
			]);
		}


		/**
		 * Список заявок.
		 *
		 */
		public function actionRelevant()
		{
			$this->setRights([Admin::USER_TYPE]);

			extract($_GET);

			$this->addCss("bootstrap-select");
			$this->addJs("bootstrap-select");

			$Users = User::getCached();

			// Генерируем HTML для выбора пользователей
				$html = '<select class="user-list top" onchange="setRequestListUser(this); ang_scope.pageChangedRelevant()"
					style="background-color: '. $Users[$_COOKIE["id_user_list"]]['color'] .'">
					<option selected="" value="">все</option>
					<option disabled="" value="">──────────────</option>';

				foreach ($Users as $User) {
					$html .= '
						<option style="background-color: '. $User['color'] .'"
							'. ($User['id'] == $_COOKIE["id_user_list"] ? "selected" : "") .'
							value="'. $User['id'] .'">
							'. $User['login'] .'
						</option>';
				}
				$html .= '</select>';
			// \конец

			$this->setTabTitle("Релевантные заявки " . $html);
			$this->setRightTabTitle('<a href="requests/add" class="link-reverse link-white">создать заявку</a>');

			// Получаем новые заявки
			$Requests = Request::getByPageRelevant(1, $grade, $branch, $subject);

			// Данные для ангуляра
			$ang_init_data = angInit([
				"requests" 				=> $Requests,
				"users"					=> $Users,
				"subjects"				=> Subjects::$all,
				"branches"				=> Branches::getAll(),
				"request_statuses"		=> RequestStatuses::get(),
				"requests_count"		=> Request::countByPageRelevant($grade, $branch, $subject),
				"search"				=> [
					"grade" 		=> $grade,
					"id_subject"	=> $subject,
					"id_branch"		=> $branch,
				],
			]);

			$this->render("list_relevant", [
				"ang_init_data"	=> $ang_init_data,
			]);
		}


		/**
		 * Добавление заявки.
		 *
		 */
		public function actionAdd()
		{
			$this->setRights([Admin::USER_TYPE]);

			$default_params = [
				"id_user_created"	=> User::id(),
			//	"id_status"			=> RequestStatuses::CUSTOM,
				"adding" 			=> 1,
			];

			// Добавляем заявку
			$Request = new Request($_GET + $default_params);

			if (!$Request->id_student) {
				// Создаем нового ученика по заявке, либо привязываем к уже существующему
				$this->setTabTitle("Добавление профиля ученика");
				$Request->createStudent();
			} else {
				$this->setTabTitle("Добавление заявки к профилю ученика №" . $Request->id_student);
			}

			// Сохраняем заявку
			$Request->save();

			// Редиректим на редактирование заявки
			// $this->redirect("requests/edit/" . $Request->id, true);
			$_GET["id"] = $Request->id;
			$this->actionEdit();
		}



		##################################################
		###################### AJAX ######################
		##################################################

		/**
		 * Редактирование заявки – основное сохранение.
		 *
		 */
		public function actionAjaxSave()
		{
			$this->setRights([Admin::USER_TYPE]);

			# ЗАЯВКА
			// Получаем ID заявки
			$id_request = $_POST["id_request"];

			$save_request = intval($_POST["save_request"]);
			$save_student = intval($_POST["save_student"]);

			// Находим заявку
			$Request = Request::findById($id_request);

			// форсируем ID студента
/*
			if ($_POST["id_student_force"]) {
				$Request->id_student = $_POST["id_student_force"];
				$Request->save("id_student");
				$Request = Request::findById($id_request);
			}
*/

			// Обновляем данные (без сохранения, сохраняем в конце)


			if (!isset($_POST["Request"]["branches"])) {
				$_POST["Request"]["branches"] = "";
			}

			if ($save_request) {
                $Request->update($_POST["Request"]);
			}

			if ($save_student) {
				# СТУДЕНТ
				// Если студент найден
				if ($Request->Student) {
					$Request->Student->update($_POST["Student"]); // обновить и сохранить данные
				} else {
					// инача создаем нового студента
					if ($Student = Student::add($_POST["Student"])) {
						$Request->addRelation("Student", $Student);
					}
				}

				// Если добавили студента (ИНАЧЕ ВООБЩЕ НИКАКИЕ ДАННЫЕ НЕ СОХРАНЯТСЯ)
				if ($Request->Student) {
					# МЕТКИ СТУДЕНТА
					// $Request->Student->addMarkers($_POST["marker_data"]);

					# Если удобные для ученика филиалы не установлены
					if (!$_POST["Student"]["branches"]) {
						$Request->Student->branches = null;
					}

					# ПАСПОРТ УЧЕНИКА
					// Если паспорт найден
					if ($Request->Student->Passport) {
						$Request->Student->Passport->update($_POST["StudentPassport"]);
					} else {
						// Если данные паспорта были введены
						if (hasValues($_POST["StudentPassport"])) {
							$StudentPassport = Passport::add($_POST["StudentPassport"] + ["type" => Passport::TYPE_STUDENT]); // добавляем тип по умолчанию
							$Request->Student->addRelation("Passport", $StudentPassport, true); // добавляем взаимосвязь и сохраняем id_passport
						}
					}

					# ПРЕДСТАВИТЕЛЬ
					// Если есть представитель у ученика
					if ($Request->Student->Representative) {
						$Request->Student->Representative->update($_POST["Representative"]); // обновить и сохранить данные
					} else {
						// Иначе создаем нового представителя
						if ($Representative = Representative::add($_POST["Representative"])) {
							$Request->Student->addRelation("Representative", $Representative); // добавляем вместе с видимостью $Request->Representative
						}
					}

					# ПАСПОРТ ПРЕДСТАВИТЕЛЯ
					// Если паспорт найден
					if ($Request->Student->Representative->Passport) {
						$Request->Student->Representative->Passport->update($_POST["Passport"]);
					} else {
						// Если данные паспорта были введены
						if (hasValues($_POST["Passport"])) {
							$Passport = Passport::add($_POST["Passport"] + ["type" => Passport::TYPE_REPRESENTATIVE]); // добавляем тип по умолчанию
							$Request->Student->Representative->addRelation("Passport", $Passport, true); // добавляем взаимосвязь и сохраняем id_passport
						}
					}

					// Сохраняем все изменения по студенту
					$Request->Student->save();
				}
			}

			// Сохраняем все изменения заявки
			$Request->save();
		}


		/**
		 * Получить по странице и списку.
		 *
		 */
		public function actionAjaxGetByPage()
		{
			extract($_GET);

            $search = (object)[
                'id_status' => $id_status,
                'id_user'   => $_COOKIE['id_user_list'],
            ];

			returnJSON([
			    'requests' => Request::getByPage($page, $id_status),
			    'counts' => [
			        'requests' => Request::getAllStatusesCount($search),
			        'users' => Request::getUserCounts($search)
                ]
            ]);
		}

		/**
		 * Получить по странице и списку.
		 *
		 */
		public function actionAjaxGetByPageRelevant()
		{
			extract($_GET);

			returnJSON([
				"requests" => Request::getByPageRelevant($page, $grade, $id_branch, $id_subject),
				"requests_count" => Request::countByPageRelevant($grade, $id_branch, $id_subject)
			]);
		}



		/**
		 * Найти студента по ID.
		 *
		 */
		public function actionAjaxGetStudent()
		{
			extract($_GET);

			returnJSON(Student::findById($id));
		}


		/**
		 * Присвоить заявку ученику (склейка клиентов).
		 *
		 */
		public function actionAjaxGlueRequest()
		{
			extract($_POST);

			$Request = Request::findById($id_request);

			returnJSON($Request->bindToStudent($id_student, $delete_student));
		}

		public function actionAjaxChangeStatus()
		{
			extract($_POST);

			Request::updateById($id_request, [
				"id_status" => $id_request_status
			]);
		}

		public function actionAjaxSaveMarkers()
		{
			extract($_POST);

			preType($_POST);

			Student::addMarkersStatic($markers, $id_student);
		}

		public function actionAjaxLoadRequest()
		{
			extract($_POST);

			$Request = Request::findById($id_request);

			returnJsonAng([
				# Основные данные
				"responsible_user"	=> $Request->id_user ? User::findById($Request->id_user) : [],	// ответственный
				"user"				=> User::fromSession(),
				"users"				=> User::getCached(true), // с system
				"request_phone_level"	=> $Request->phoneLevel(),
			]);
		}

		public function actionAjaxLoadStudent()
		{
			extract($_POST);

			$Student = Student::findById($id_student);
			$Student->is_banned = Student::isBanned($id_student);

			$search = isset($_COOKIE['groups']) ? json_decode($_COOKIE['groups']) : (object)[];

			returnJsonAng([
				# Данные ученика
				"Subjects"			=> Subjects::$three_letters,
				"SubjectsFull"		=> Subjects::$all,
				"SubjectsFull2"		=> Subjects::$full,
				"FreetimeBar"		=> Freetime::getFreetimeBar($id_student, EntityFreetime::STUDENT),
				"GroupsBar"			=> Freetime::getStudentBar($id_student),
				"server_markers"=> $Student->getMarkers(),		// Метки ученика
				"contracts"		=> $Student->getContracts(),	// Договоры ученика
				"student"		=> $Student,  // Данные по ученику для печати
				"Groups"		=> Student::groups($Student->id),
				"student_phone_level"	=> $Student->phoneLevel(),
				"branches_brick"		=> Branches::getShortColored(),
				"academic_year"			=> $search->year,
                "Prices"                => Prices::getRecommended(),
				"Teachers"				=> Teacher::getLight(false),
				# Данные представителя
				"representative_phone_level"	=> ($Student->Representative ? $Student->Representative->phoneLevel() : 1), // уровень телефона 1, если нет представителя
				"representative"				=> ($Student->Representative ? $Student->Representative : new Representative()),  // для печати
			]);
		}

		public function actionAjaxLoadPayments()
		{
			extract($_POST);

			$payments = Payment::getByStudentId($id_student);

			$PaymentsByYear = [];

			foreach($payments as $payment) {
				$PaymentsByYear[$payment->year][] = $payment;
			}

			returnJsonAng([
				"user"				=> User::fromSession(),
				"payment_statuses"	=> Payment::$all,
				"payment_types"		=> PaymentTypes::$all,
				"PaymentsByYear"	=> $PaymentsByYear,	// Платежи ученика
                "academic_year"     => academicYear(),
                "tobe_paid"         => Student::getDebt($id_student),
			]);
		}

		public function actionAjaxLoadAdditionalPayments()
		{
			extract($_POST);
			returnJsonAng(StudentAdditionalPayment::get($id_student));
		}

		public function actionAjaxLoadLessons()
		{
			extract($_POST);

			$Schedule = Student::getFullSchedule($id_student);

			returnJsonAng([
				"Subjects"	=> Subjects::$three_letters,
				"Lessons"	=> $Schedule->Lessons,
				"lesson_statuses" => VisitJournal::$statuses,
				"all_cabinets" =>  Branches::allCabinets(),
				"lesson_years" => $Schedule->years,
				"months" => Months::get(),
				"selected_lesson_year" => end($Schedule->years)
			]);
		}

		public function actionAjaxLoadReviews()
		{
			extract($_POST);

			returnJsonAng([
				'Reviews' 		 => TeacherReview::getData(null, null, $id_student)['data'],
				"user"				=> User::fromSession(),
				"users"				=> User::getCached(true), // с system
				"grades_short"  => Grades::$short,
				'id_user_review' => dbConnection()->query("SELECT id_user_review FROM students WHERE id = {$id_student}")->fetch_object()->id_user_review,
			]);
		}

		public function actionAjaxLoadReports()
		{
			extract($_POST);

			$Reports = Student::getReports($id_student);

			$return = [];
			foreach($Reports as $Report) {
				 $return[$Report->year][] = $Report;
			}

			returnJsonAng($return);
		}

		public function actionAjaxLoadBalance()
		{
			extract($_POST);

			$balance = Student::getBalance($id_student);
			$years = array_reverse(array_keys($balance));

			returnJsonAng([
				'Balance' => $balance,
				'years' => $years,
				'selected_year' => end($years)
			]);
		}

		public function actionAjaxLoadStudentTests()
		{
			extract($_POST);

			returnJsonAng([
				'Tests'			=> Test::getLightAll(),
				"correct_answers" => TestProblem::getCorrectAnswers(),
				'StudentTests'	=> TestStudent::getByStudentId($id_student),
			]);
		}
	}

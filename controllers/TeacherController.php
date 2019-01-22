<?php

	// Контроллер
	class TeacherController extends Controller
	{
		public $defaultAction = "list";

		public static $allowed_users = [Admin::USER_TYPE, Teacher::USER_TYPE];

		// Папка вьюх
		protected $_viewsFolder	= "teacher";

		public function beforeAction()
		{
			$this->addJs("ng-teacher-app, dnd-new");
		}

		public function actionFaq()
		{
            # @rights-refactored
            $this->checkRights(Shared\Rights::SHOW_FAQ);
			$this->setTabTitle("Редактирование FAQ преподавателя");

			$ang_init_data = angInit([
				"html" => Settings::get('teachers_faq'),
			]);

			$this->render("faq", [
				"ang_init_data" => $ang_init_data,
			]);
		}

		public function actionSalary()
		{
            # @rights-refactored
            $this->checkRights(Shared\Rights::SHOW_TEACHER_PAYMENTS);

            $year = ! empty($_GET['year']) ? intval($_GET['year']) : academicYear();


            $query = dbConnection()->query(
	                "select id_teacher
	                from visit_journal
	                where (type_entity='TEACHER' OR " . VisitJournal::PLANNED_CONDITION . ") and year={$year}
	                group by id_teacher
			");

	        $teacher_ids = [];

	        while($row = $query->fetch_object()) {
		        $teacher_ids[] = $row->id_teacher;
	        }

	        $query = dbConnection()->query(
	                "select id_teacher
	                from teacher_additional_payments
	                where year={$year}
	                group by id_teacher
			");

			while($row = $query->fetch_object()) {
				if (! in_array($row->id_teacher, $teacher_ids)) {
					$teacher_ids[] = $row->id_teacher;
				}
	        }

			$real_total_sum = 0;
			$total_sum = 0;
			$total_payment_sum = 0;
			$lesson_count = 0;

			$planned_lessons_sum = 0;
			$planned_debt_sum = 0;

			$total_service_sum = 0;
			$total_service_count = 0;

			foreach ($teacher_ids as $id_teacher) {
				$Teacher = Teacher::getLight($id_teacher);

				$Payments = Payment::findAll([
					"condition" => "entity_id=$id_teacher and entity_type = '".Teacher::USER_TYPE."' and year={$year}"
				], true);

				$payment_sum = 0;
				foreach ($Payments as $Payment) {
					$payment_sum += $Payment->sum;
					$total_payment_sum += $Payment->sum;
				}

				$Data = VisitJournal::findAll([
					"condition" => "id_entity=$id_teacher AND type_entity='TEACHER' and year={$year}"
				]);

				$sum = 0;
				$real_sum = 0;
				foreach ($Data as $OneData) {
					$sum += $OneData->price;
					$total_sum += $OneData->price;
					$real_sum += $OneData->price;
					$real_total_sum += $OneData->price;
				}

				$service_sum = dbConnection()->query("select sum(`sum`) as s from teacher_additional_payments where id_teacher={$id_teacher} and year={$year}")->fetch_object()->s;
				$total_service_sum += $service_sum;
				$real_sum += $service_sum;
				$real_total_sum += $service_sum;

				$service_count = dbConnection()->query("select count(*) as cnt from teacher_additional_payments where id_teacher={$id_teacher} and year={$year}")->fetch_object()->cnt;
				$total_service_count += $service_count;

				// получаем планируемые занятия преподавателя
				$teacher_group_ids = implode(',', Teacher::getGroupIds($id_teacher, $year));

				$planned_lessons = VisitJournal::findAll([
					'condition' => VisitJournal::PLANNED_CONDITION . " AND id_group IN ({$teacher_group_ids})"
				]);

				$planned_debt = 0;
				foreach($planned_lessons as $planned_lesson) {
					$planned_debt += dbConnection()->query("select teacher_price from groups where id={$planned_lesson->id_group}")->fetch_object()->teacher_price;
				}

				if ($planned_lessons) {
					$planned_lessons_sum += count($planned_lessons);
				}
				$planned_debt_sum += $planned_debt;

				if ($Data) {
					$lesson_count += count($Data);
				}

				$return[] = [
					"Teacher" 	=> $Teacher,
					"sum"		=> $sum,
					"real_sum"  => $real_sum,
					"payment_sum" => $payment_sum,
					'planned_lessons' => ($planned_lessons ? count($planned_lessons) : 0),
					'planned_debt' => $planned_debt,
					"count"		=> ($Data ? count($Data) : 0),
					'service_count' => $service_count,
					'service_sum' => $service_sum,
				];

			}

			// Сортировка по ФИО
			usort($return, function($a, $b) {
				if ($a["Teacher"]->last_name > $b["Teacher"]->last_name) {
					return 1;
				} else {
					if ($a["Teacher"]->last_name == $b["Teacher"]->last_name) {
						if ($a["Teacher"]->first_name > $b["Teacher"]->first_name) {
							return 1;
						} else {
							if ($a["Teacher"]->first_name == $b["Teacher"]->first_name) {
								if ($a["Teacher"]->middle_name > $b["Teacher"]->middle_name) {
									return 1;
								} else {
									return -1;
								}
							}
						}
					} else {
						return -1;
					}
				}
			});

			$ang_init_data = angInit([
				"Data"                => $return,
				"total_sum"           => $total_sum,
				"real_total_sum"      => $real_total_sum,
				"total_payment_sum"   => $total_payment_sum,
				"lesson_count"        => $lesson_count,
				"planned_lessons_sum" => $planned_lessons_sum,
				"planned_debt_sum"    => $planned_debt_sum,
				"subjects"            => Subjects::$short,
				"active_year"         => $year,
				'total_service_sum'   => $total_service_sum,
				'total_service_count' => $total_service_count,
			]);

			$this->setTabTitle('Дебет преподавателей');

			$this->render("salary", [
				"ang_init_data" => $ang_init_data,
			]);
		}

		public function actionJournal()
		{
			$id_teacher = $_GET['id'];
			$Teacher = Teacher::getLight($id_teacher);
						
			$ang_init_data = angInit([
				'year' => academicYear(),
				'id_teacher' => $id_teacher,
				'Grades' => Grades::$all,
			]);

			
			$this->setTabTitle('Посещаемость | ' . getShortName($Teacher));
			$this->render("journal", [
				"ang_init_data" => $ang_init_data,
			]);
		}
		
		public function actionAjaxJournal()
		{
			extract($_POST);
			$date_start = implode('-', [$year, '09', '01']);
			$date_end = date('Y-m-d', strtotime('first Sunday of September ' . $year));
			
			$dates = [];
			$result = [];
			$students = [];
			$name_colors = [];
			while ($date_end <= ($year + 1) . '-07-01') {
				$lessons = VisitJournal::findAll([
					'condition' => "lesson_date > '{$date_start}' AND lesson_date <= '{$date_end}' AND type_entity='STUDENT' AND id_teacher={$id_teacher}" 
						. (isset($grades) ? " AND grade IN (" . implode(',', $grades) . ")" : ''),
					'order' => '',
					'group' => 'id_entity',
				]);
				
				foreach($lessons as $lesson) {
					if (! isset($result[$lesson->id_entity])) {
						$result[$lesson->id_entity] = [];
						$students[] = Student::getLightName($lesson->id_entity);
						$name_colors[$lesson->id_entity] = $lesson->id_subject;
					}
// 					if (! isset($result[$lesson->id_entity][$date])) {
						$result[$lesson->id_entity][$date_end] = $lesson->presence == 2 ? 'red' : ($lesson->late > 0 ? 'orange' : 'green');
						
						// если предметы разные на протяжении всего времени, то цвет серый
						if ($name_colors[$lesson->id_entity] !== 'grey') {
							if ($name_colors[$lesson->id_entity] != $lesson->id_subject) {
								$name_colors[$lesson->id_entity] = 'grey';
							}
						}
// 					}
				}
				
				
				$dates[] = $date_end;
				$date_start = $date_end;
				$date_end = (new DateTime($date_end))->modify('+1 week')->format('Y-m-d');
			}
			
			foreach($name_colors as $id_student => $id_subject) {
				if ($id_subject !== 'grey') {
					// получаем догавар по этому предмету в этом году
					$last_contract_id = Student::getLastContractId($id_student, $year, true);
					// echo $last_contract_id . " | " . $name_colors[$student->id] . "\n";
					$contract_subject = ContractSubject::find([
						'condition' => "id_contract={$last_contract_id} AND id_subject={$id_subject}"
					]);
// 					echo $id_student . " | " . $contract_subject->status . "\n";
					$name_colors[$id_student] = $contract_subject->status;
				}
			}
			
// 			returnJsonAng(compact('name_colors'));
			returnJsonAng(compact('dates', 'result', 'students', 'name_colors'));
		}
		
		public function actionList()
		{
			$this->_custom_panel = true;

            $ang_init_data = angInit([
				"three_letters"	=> Subjects::$three_letters,
				"Branches"		=> Branches::getAll('*'),
				"subjects" 		=> Subjects::$short,
                "user"          => User::fromSession()
			]);

			$this->render("list", [
				"ang_init_data" => $ang_init_data
			]);
		}

		// @time-refactored @time-checked
		public function actionEdit()
		{
			$id_teacher = $_GET['id'];
			$Teacher = Teacher::findById($id_teacher);
			$Teacher->reports_needed = Teacher::redReportCountStatic($id_teacher);

			if (User::isTeacher()) {
				if ($Teacher->id_head_teacher != User::id()) {
					$this->renderRestricted();
				}
				$this->addCss('teacher');
				$this->setTabTitle("Просмотр преподавателя " . $Teacher->getFullName());
			} else {
				$this->setTabTitle("Редактирование преподавателя " . $Teacher->getFullName());
				$this->setRightTabTitle("
					<a class='link-white' style='margin-right: 10px' href='teachers/journal/{$id_teacher}'>посещаемость</a>
					<a class='link-white' style='margin-right: 10px' href='https://lk.ege-repetitor.ru/tutors/{$id_teacher}/edit'>профиль в системе ЕГЭ-Репетитор</a>
					<a class='link-white' href='as/teacher/{$id_teacher}'>режим просмотра</a>
				");
			}

			$ang_init_data = angInit([
				"Teacher" 				=> $Teacher,
				"teacher_phone_level"	=> $Teacher->phoneLevel(),
				"branches_brick"		=> Branches::getShortColored(),
				"GroupLevels"			=> GroupLevels::$all,
				"Subjects"	            => Subjects::$three_letters,
				"three_letters"         => Subjects::$three_letters,
				"SubjectsFull"          => Subjects::$all,
				"payment_statuses"	    => Payment::$all,
				"payment_types"	        => PaymentTypes::$all,
				"user"				    => User::fromSession(),
				"Grades"			    => Grades::$all,
				"grades_short"		    => Grades::$short,
				"academic_year"			=> Years::getAcademic(),
				"Teachers"				=> Teacher::getLight(false),
				"is_teacher"			=> User::isTeacher() ? 1 : 0,
				"headed_students"		=> User::isTeacher() ? Student::getIds(['condition' => "id_head_teacher=" . User::id()]) : []
			]);

			$this->render("edit", [
				"Teacher"		=> $Teacher,
				"ang_init_data" => $ang_init_data
			]);
		}



		/******* AJAX ********/

		public function actionAjaxMenu()
		{
			extract($_POST);
			switch ($menu) {
				case 0: {
					returnJsonAng(Teacher::getGroups($id_teacher, false));
				}
				case 1: {
					returnJsonAng(Teacher::getReviews($id_teacher));
				}
				case 2: {
					$payments = Teacher::getPayments($id_teacher);
					$years = array_reverse(array_keys($payments));
					returnJsonAng([
						'Lessons' => $payments,
						'years' => $years,
						'selected_year' => end($years)
					]);
				}
				case 3: {
					returnJsonAng([
                        'payments'      => Payment::findAll(['condition' => "entity_id = $id_teacher and entity_type = '" . Teacher::USER_TYPE . "'", 'order' =>'first_save_date asc']),
                        'tobe_paid' => Payment::tobePaid($id_teacher, Teacher::USER_TYPE),
                        'academic_year' => academicYear(),
                        'user_rights' => User::fromSession()->rights,
                    ]);
				}
				case 4: {
					if (User::isTeacher()) {
						returnJsonAng(Report::getForTeacherLk($id_teacher, User::id()));
					} else {
						returnJsonAng(Teacher::getReportsStatic($id_teacher));
					}
				}
				case 5: {
					$Teacher = Teacher::findById($id_teacher);
					$Stats = Teacher::stats($id_teacher);

                    $Stats['clients_count'] = dbEgerep()->query("SELECT COUNT(*) AS cnt FROM attachments WHERE tutor_id=" . $id_teacher)->fetch_object()->cnt;
                    if ($Stats['clients_count']) {
                        $Stats['er_first_attachment_date'] = dbEgerep()->query("SELECT date FROM attachments WHERE tutor_id=" . $id_teacher. " ORDER BY date LIMIT 1")->fetch_object()->date;
                    }

                    $Stats['er_review_count'] = dbEgerep()->query("
						SELECT COUNT(*) AS cnt FROM reviews r
						JOIN attachments a ON a.id = r.attachment_id
						WHERE a.tutor_id={$id_teacher} AND r.score < 11 AND r.score > 0
					")->fetch_object()->cnt;

					$review_score_sum = dbEgerep()->query("
						SELECT SUM(r.score) AS sm FROM reviews r
						JOIN attachments a ON a.id = r.attachment_id
						WHERE a.tutor_id={$id_teacher} AND r.score < 11 AND r.score > 0
					")->fetch_object()->sm;

			        switch($Teacher->js) {
			            case 10: {
			                $js = 8;
			                break;
			            }
			            case 8: {
			                $js = 10;
			                break;
			            }
			            default: {
			                $js = $Teacher->js;
			            }
			        }

			        $Stats['er_review_avg'] = (4* (($Teacher->lk + $Teacher->tb + $js) / 3) + $review_score_sum)/(4 + $Stats['er_review_count']);

					returnJsonAng($Stats);
				}
				case 6: {
					$Bars = [
						'Group' 	=> Freetime::getTeacherBar($id_teacher),
						'Freetime'	=> Freetime::getFreetimeBar($id_teacher, EntityFreetime::TEACHER)
					];
					returnJsonAng($Bars);
				}
				case 7: {
					returnJsonAng([
						'TeacherAdditionalPayments' => TeacherAdditionalPayment::get($id_teacher),
						"all_cabinets" => Branches::allCabinets(),
						"Students" => Student::getAllList(),
						"AdditionalLessons" => AdditionalLesson::getByEntity(Teacher::USER_TYPE, $id_teacher)
					]);
				}
			}
		}

		public function actionAjaxStats($value='')
		{
			extract($_POST);
			returnJsonAng(Teacher::stats($id_teacher, $years, $grades));
		}

		public function actionAjaxLoadAll()
		{
			// $Teachers = Teacher::findAll([
			// 	"condition" => "in_egecentr > 0",
			// 	"order" => "last_name ASC",
			// ]);

			$query = dbEgerep()->query("
				select id, first_name, last_name, middle_name, branches, subjects_ec, in_egecentr,
					IF(LENGTH(photo_desc) > 0, 1, 0) as photo_desc_exists,
					IF(LENGTH(description) > 0, 1, 0) as description_exists
				from tutors
				where in_egecentr > 0
				order by last_name ASC
			");

			$Teachers = [];
			while ($row = $query->fetch_object()) {
				$row->subjects_ec = explode(',', $row->subjects_ec);
				$row->branches = explode(',', $row->branches);
				$row->bar = Freetime::getTeacherBar($row->id, true);
				if ($row->in_egecentr == Teacher::ACTIVE_NOW) {
					$row->alerts = [];

					// есть ли подпись под фото?
					if (! $row->photo_desc_exists) {
						$row->alerts[] = '• поле "подпись под фото на сайте ЕГЭ-Центра" пусто';
					}

					// есть ли опубликованное описание?
					if (! $row->description_exists) {
						$row->alerts[] = '• поле "опубликованное описание на сайте ЕГЭ-Центра" пусто';
					}

					// есть ли фото?
					// (если нет опубликованного описания, то запись в tutor_data не будет создана
					// соответственно ошибка всегда будет возникать)
					if ($row->description_exists && ! Teacher::hasPhoto($row->id)) {
						$row->alerts[] = '• отсутствует обрезанное фото';
					}
				}
				$Teachers[] = $row;
			}

			// foreach($Teachers as &$Teacher) {
			// 	$Teacher->bar = Freetime::getTeacherBar($Teacher->id, true);
			// }

			returnJsonAng($Teachers);
		}

		public function actionAjaxSaveHeadTeacher()
		{
			extract($_POST);
			$Teacher = Teacher::findById($id_teacher);
			$Teacher->id_head_teacher = $id_head_teacher;
			$Teacher->save("id_head_teacher");
		}
	}

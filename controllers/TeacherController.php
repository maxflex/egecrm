<?php

	// Контроллер
	class TeacherController extends Controller
	{
		public $defaultAction = "list";

		public static $allowed_users = [User::USER_TYPE, Teacher::USER_TYPE];

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


            $teacher_ids = explode(',', dbConnection()->query(
	                "select group_concat(distinct id_entity) as teacher_ids " .
	                "from visit_journal " .
	                "where type_entity='" . Teacher::USER_TYPE . "'" .
					" and year={$year}"
	            )->fetch_object()->teacher_ids
			);

            $teacher_ids = array_filter($teacher_ids);

			$real_total_sum = 0;
			$total_sum = 0;
			$total_payment_sum = 0;
			$lesson_count = 0;
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

				$additional_payments_sum = dbConnection()->query("select sum(`sum`) as s from teacher_additional_payments where id_teacher={$id_teacher} and year={$year}")->fetch_object()->s;
				$sum += $additional_payments_sum;
				$total_sum += $additional_payments_sum;
				$real_sum += $additional_payments_sum;
				$real_total_sum += $additional_payments_sum;

				$lesson_count += count($Data);

				$return[] = [
					"Teacher" 	=> $Teacher,
					"sum"		=> $sum,
					"real_sum"  => $real_sum,
					"payment_sum" => $payment_sum,
					"count"		=> ($Data ? count($Data) : 0),
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

            $tobe_paid = dbConnection()->query(
                "select format(sum(g.teacher_price), 0) as tobe_paid from visit_journal vj " .
                "join groups g on g.id = vj.id_group " .
                "where lesson_date > now() and vj.cancelled = 0"
            )->fetch_object()->tobe_paid;

			$ang_init_data = angInit([
				"Data" 		                     => $return,
				"total_sum"			             => $total_sum,
				"real_total_sum"			     => $real_total_sum,
				"total_payment_sum"	             => $total_payment_sum,
				"lesson_count"		             => $lesson_count,
				"subjects"	                     => Subjects::$short,
				"active_year"                    => $year,
			]);

			$this->setTabTitle('Дебет преподавателей');
            $this->setRightTabTitle('Планируемый дебет: ' . str_replace(',', ' ', $tobe_paid) . ' руб.');

			$this->render("salary", [
				"ang_init_data" => $ang_init_data,
			]);
		}


		public function actionList()
		{
			$this->_custom_panel = true;

            $ang_init_data = angInit([
				"three_letters"	=> Subjects::$three_letters,
				"Branches"		=> Branches::getAll('*'),
				"subjects" 		=> Subjects::$short,
                "user"          => User::fromSession()->dbData()
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

			        // Доля пропусков
					$total_student_visits = VisitJournal::count([
						"condition" => "type_entity='STUDENT' AND id_teacher=" . $Teacher->id
					]);
					if ($total_student_visits) {
						$abscent_count = VisitJournal::count([
							"condition" => "id_teacher={$Teacher->id} AND type_entity='STUDENT' AND presence=2"
						]);
						$Stats['abscent_percent'] = round($abscent_count / $total_student_visits * 100);
					}

                    $Stats['ec_efficency'] = Teacher::getEfficency($Teacher->id);

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

		public function actionAjaxLoadAll()
		{
			// $Teachers = Teacher::findAll([
			// 	"condition" => "in_egecentr > 0",
			// 	"order" => "last_name ASC",
			// ]);

			$query = dbEgerep()->query("
				select id, first_name, last_name, middle_name, branches, subjects, in_egecentr
				from tutors
				where in_egecentr > 0
				order by last_name ASC
			");

			$Teachers = [];
			while ($row = $query->fetch_object()) {
				$row->subjects = explode(',', $row->subjects);
				$row->branches = explode(',', $row->branches);
				$row->bar = Freetime::getTeacherBar($row->id, true);
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

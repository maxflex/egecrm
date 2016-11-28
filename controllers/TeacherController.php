<?php

	// Контроллер
	class TeacherController extends Controller
	{
		public $defaultAction = "list";

		// Папка вьюх
		protected $_viewsFolder	= "teacher";

		public function beforeAction()
		{
			$this->addJs("ng-teacher-app, dnd-new");
		}

		public function actionFaq()
		{
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
			$Data = VisitJournal::findAll([
				"condition" => "type_entity='TEACHER'"
			]);

			$teacher_ids = [];
			foreach ($Data as $OneData) {
				if (!$OneData->id_entity) {
					continue;
				}
				if (!in_array($OneData->id_entity, $teacher_ids)) {
					$teacher_ids[] = $OneData->id_entity;
				}
			}

			$total_sum = 0;
			$total_payment_sum = 0;
			$lesson_count = 0;
			foreach ($teacher_ids as $id_teacher) {
				$Teacher = Teacher::findById($id_teacher);

				$Payments = Payment::findAll([
					"condition" => "entity_id=$id_teacher and entity_type = '".Teacher::USER_TYPE."'"
				]);

				$payment_sum = 0;
				foreach ($Payments as $Payment) {
					$payment_sum += $Payment->sum;
					$total_payment_sum += $Payment->sum;
				}

				$Data = VisitJournal::findAll([
					"condition" => "id_entity=$id_teacher AND type_entity='TEACHER'"
				]);

				$sum = 0;
				foreach ($Data as $OneData) {
					$sum += $OneData->teacher_price;
					$total_sum += $OneData->teacher_price;
				}

				$lesson_count += count($Data);

				$return[] = [
					"Teacher" 	=> $Teacher,
					"sum"		=> $sum,
					"payment_sum" => $payment_sum,
					"count"		=> count($Data),
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
				"Data" 		=> $return,
				"total_sum"			=> $total_sum,
				"total_payment_sum"	=> $total_payment_sum,
				"lesson_count"		=> $lesson_count,
				"subjects"	=> Subjects::$short,
			]);

			$this->setTabTitle("Дебет преподавателей");

			$this->render("salary", [
				"ang_init_data" => $ang_init_data,
			]);
		}


		public function actionList()
		{
			$this->_custom_panel = true;

			$this->addJs("bootstrap-select");
			$this->addCss("bootstrap-select");

			$Teachers = Teacher::findAll([
				"condition" => "in_egecentr > 0",
				"order" => "last_name ASC",
			]);

            $ang_init_data = angInit([
				"Teachers" 		=> $Teachers,
				"three_letters"	=> Subjects::$three_letters,
				"subjects" 		=> Subjects::$short,
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

			$this->setTabTitle("Редактирование преподавателя " . $Teacher->getFullName());
			$this->setRightTabTitle("
				<a class='link-white' style='margin-right: 10px' href='https://lk.ege-repetitor.ru/tutors/{$id_teacher}/edit'>профиль в системе ЕГЭ-Репетитор</a>
				<a class='link-white' href='as/teacher/{$id_teacher}'>режим просмотра</a>
			");
			$Teacher = Teacher::findById($id_teacher);


			$this->addJs("bootstrap-select");
			$this->addCss("bootstrap-select");

			$ang_init_data = angInit([
				"Teacher" => $Teacher,
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
				"academic_year"			=> Years::getAcademic(),
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
                    $Lessons = VisitJournal::getTeacherLessons($id_teacher, ['login' => true, 'payments' => true]);
                    returnJsonAng($Lessons);
				}
				case 3: {
					returnJsonAng(Payment::findAll(["condition" => "entity_id=$id_teacher and entity_type='".Teacher::USER_TYPE."'", 'order'=>'first_save_date desc']));
				}
				case 4: {
					returnJsonAng(Teacher::getReportsStatic($id_teacher));
				}
				case 5: {
					$Teacher = Teacher::findById($id_teacher);
					$Stats = Teacher::stats($id_teacher, false);

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
			        
					returnJsonAng($Stats);
				}
				case 6: {
					$Comments = Comment::getByPlace(Comment::PLACE_TEACHER, $id_teacher);
					$Bars = [
						'Group' 	=> Freetime::getTeacherBar($id_teacher),
						'Freetime'	=> Freetime::getFreetimeBar($id_teacher, EntityFreetime::TEACHER),
						'Comments'	=> $Comments ? $Comments : [],
					];
					returnJsonAng($Bars);
				}
			}
		}
	}

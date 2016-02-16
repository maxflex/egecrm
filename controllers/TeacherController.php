<?php

	// Контроллер
	class TeacherController extends Controller
	{
		public $defaultAction = "list";

		// Папка вьюх
		protected $_viewsFolder	= "teacher";

		public function beforeAction()
		{
			$this->addJs("ng-teacher-app");
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

				$Payments = TeacherPayment::findAll([
					"condition" => "id_teacher=$id_teacher"
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

			$Teachers = Teacher::findAll([
				"order" => "last_name ASC"
			]);

			foreach ($Teachers as &$Teacher) {
				// $Teacher->login_count = User::getLoginCount($Teacher->id, Teacher::USER_TYPE);
				$Groups = Teacher::getGroups($Teacher->id);
				foreach ($Groups as $Group) {
					foreach ($Group->students as $id_student) {
						$admin_rating = TeacherReview::getStatus($id_student, $Teacher->id, $Group->id_subject);
						if ($admin_rating) {
							$Teacher->statuses[$admin_rating]++;
						} else {
							// если в группе не было ни одного занятия
							if (Student::alreadyHadLessonStatic($id_student, $Group->id)) {
								$Teacher->statuses[0]++;
							}
						}
					}
				}


				# ОТЧЕТЫ
				if ($Teacher->had_lesson) {
// 					$result = dbConnection()->query("SELECT id FROM visit_journal WHERE id_teacher={$Teacher->id} GROUP BY id_entity, id_subject");
// 					$Teacher->student_subject_count = $result->num_rows;
					$Teacher->student_subject_counts = $Teacher->getReportCounts();

					// $Teacher->reports_count = Report::count([
					// 	"condition" => "id_teacher=" . $Teacher->id,
					// ]);
					//
					// $Teacher->reports_sent_count = Report::count([
					// 	"condition" => "email_sent=1 AND id_teacher=" . $Teacher->id,
					// ]);
				}
			}

			$ang_init_data = angInit([
				"Teachers" => $Teachers,
				"subjects" => Subjects::$short,
			]);

			$this->render("list", [
				"ang_init_data" => $ang_init_data
			]);
		}

		public function actionAdd()
		{
			$Teacher = new Teacher();

			$this->setTabTitle("Добавление преподавателя");
			$this->actionEdit($Teacher);
		}

		# если передан $Teacher, то идет добавление
		public function actionEdit($Teacher = false)
		{
			if (!$Teacher) {
				$id_teacher = $_GET['id'];
				$this->setTabTitle("Редактирование преподавателя №{$id_teacher}");
				$this->setRightTabTitle("
					<a class='link-white' style='margin-right: 10px' href='http://crm.a-perspektiva.ru:8080/egerep/public//tutors/{$id_teacher}/edit'>егэ-репетитор</a>
					<a class='link-white' style='margin-right: 10px' href='as/teacher/{$id_teacher}'>режим просмотра</a>
					<span class='link-reverse pointer' onclick='deleteTeacher($id_teacher)'>удалить преподавателя</span>
				");
				$Teacher = Teacher::findById($id_teacher);
				$Teacher->Reviews = Teacher::getReviews($Teacher->id);

				# Данные по занятиям/выплатам
				$Data = VisitJournal::findAll([
					"condition" => "id_entity=$id_teacher AND type_entity='TEACHER'",
					"order"		=> "lesson_date DESC, lesson_time DESC",
				]);

				$Groups = Teacher::getGroups($id_teacher);
			}

			$this->addJs("bootstrap-select");
			$this->addCss("bootstrap-select");

			$ang_init_data = angInit([
				"Teacher" => $Teacher,
				"Data"				=> $Data,
				"teacher_phone_level"	=> $Teacher->phoneLevel(),
				"branches_brick"		=> Branches::getShortColored(),
				"Groups"				=> $Groups,
				"Reports"				=> $Teacher->getReports(),
				"GroupLevels"			=> GroupLevels::$all,
				"Subjects"	=> Subjects::$three_letters,
				"SubjectsFull" => Subjects::$all,
				"payment_statuses"	=> Payment::$all,
				"payments"			=> TeacherPayment::findAll(["condition" => "id_teacher=$id_teacher"]),
				"user"				=> User::fromSession(),
				"time" 					=> Freetime::TIME,
			]);

			$this->render("edit", [
				"Teacher"		=> $Teacher,
				"ang_init_data" => $ang_init_data
			]);
		}

		public function actionAjaxSave()
		{
			$Teacher = $_POST;

			if ($Teacher['id']) {
				if (!isset($Teacher['subjects'])) {
					$Teacher['subjects'] = '';
				}
				if (!isset($Teacher['branches'])) {
					$Teacher['branches'] = '';
				}
				Teacher::updateById($Teacher['id'], $Teacher);
			} else {
				$NewTeacher = new Teacher($Teacher);
				$saved = $NewTeacher->save();
				returnJSON($saved);
			}
		}

		public function actionAjaxDelete()
		{
			Teacher::deleteById($_POST["id_teacher"]);
		}

	}

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

		public function actionEdit()
		{
			$id_teacher = $_GET['id'];
			$this->setTabTitle("Редактирование преподавателя №{$id_teacher}");
			$this->setRightTabTitle("
				<a class='link-white' style='margin-right: 10px' href='https://lk.ege-repetitor.ru/tutors/{$id_teacher}/edit'>профиль в системе ЕГЭ-Репетитор</a>
				<a class='link-white' href='as/teacher/{$id_teacher}'>режим просмотра</a>
			");
			$Teacher = Teacher::findById($id_teacher);
			$Teacher->Reviews = Teacher::getReviews($Teacher->id);

			# Данные по занятиям/выплатам
			$Data = VisitJournal::findAll([
				"condition" => "id_entity=$id_teacher AND type_entity='TEACHER'",
				"order"		=> "lesson_date DESC, lesson_time DESC",
			]);

			$Groups = Teacher::getGroups($id_teacher, false);

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
				"payments"			=> TeacherPayment::findAll(["condition" => "id_teacher=$id_teacher", 'order'=>'first_save_date desc']),
				"user"				=> User::fromSession(),
				"time" 				=> Freetime::TIME,
				"Grades"			=> Grades::$all,
			]);

			$this->render("edit", [
				"Teacher"		=> $Teacher,
				"ang_init_data" => $ang_init_data
			]);
		}
	}

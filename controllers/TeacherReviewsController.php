<?php

	// Контроллер
	class TeacherReviewsController extends Controller
	{
		public $defaultAction = "main";

		// Папка вьюх
		protected $_viewsFolder	= "teacher_reviews";

		public static $allowed_users = [User::USER_TYPE, Student::USER_TYPE];

		public function beforeAction()
		{
			$this->addJs("ng-teacher-review-app");
		}

		public function actionMain()
		{
			$this->setTabTitle("Оценка преподавателей");

			$this->render("main", [
				"ang_init_data" => static::_generateAngInit(User::fromSession()->id_entity),
			]);
		}

		/**
		 * Для админов
		 */
		public function actionAdmins()
		{
			$id_student = $_GET['id_student'];
			$Student = Student::findById($id_student);

			$this->setTabTitle($Student->name('fi') . " | Оценка преподавателей");

			$this->render("admin_main", [
				"ang_init_data" => static::_generateAngInit($id_student),
			]);
		}

		/**
		 * Сгенерировать ang_init_data
		 */
		 private function _generateAngInit($id_student)
		 {
			$VisitJournal = Student::getExistedTeachers($id_student);

 			foreach ($VisitJournal as $VJ) {
 				$Teacher = Teacher::findById($VJ->id_teacher);
 				$Teacher->lessons_count = VisitJournal::count([
 					"condition" => "id_teacher={$VJ->id_teacher} AND id_entity=" . $id_student . "
 						AND type_entity='STUDENT' AND id_subject={$VJ->id_subject}"
 				]);
 				$Teacher->id_subject = $VJ->id_subject;

 				$Teachers[] = $Teacher;
 			}

 			$RatingInfo = TeacherReview::getInfo($id_student);

 			return angInit([
 				"Teachers" 	=> $Teachers,
 				"RatingInfo"=> $RatingInfo,
 				"Subjects" 	=> Subjects::$dative,
 				"id_student" => $id_student,
 			]);
		 }

		public function actionAjaxSave()
		{
			extract($_POST);

			$RatingInfo = array_filter($RatingInfo);

			preType($RatingInfo);

			TeacherReview::addData($RatingInfo, $id_student);
		}
	}

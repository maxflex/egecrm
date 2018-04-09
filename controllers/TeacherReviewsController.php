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
			$this->addJs("ng-teacher-review-app, dnd-new");
		}

		private function _studentId()
		{
			return User::isStudent() ? User::fromSession()->id_entity : $_GET['id_student'];
		}

		/**
		 * Список для ученика
		 */
		public function actionList()
		{
			$id_student = self::_studentId();

			$this->_custom_panel = true;

			$ang_init_data = angInit([
				'Subjects' 		=> Subjects::$all,
				'three_letters' => Subjects::$three_letters,
				'id_student'	=> $id_student,
				'user'			=> User::fromSession()->dbData(),
				'users'			=> User::getCached(true),
				'Teachers'		=> Teacher::getJournalTeachers(),
				'Student'		=> Student::getLight($id_student),
				'currentPage'	=> $_GET['page'] ? $_GET['page'] : 1,
			]);

			$this->render("student_reviews", [
				"ang_init_data" => $ang_init_data,
			]);
		}

        /**
		 * Для админов
		 */
		public function actionView()
		{
    		$Review = TeacherReview::findById($_GET['id']);

            $id_student = $Review->id_student;
			$id_teacher = $Review->id_teacher;
			$id_subject = $Review->id_subject;
			$year 		= $Review->year;

			$this->_custom_panel = true;

			$this->setTabTitle("Оценка преподавателей");

			$this->render("edit", [
				"ang_init_data" => static::_generateAngInit($id_student, $id_teacher, $id_subject, $year),
			]);
		}

		/**
		 * Для админов
		 */
		public function actionAdmins()
		{
			$id_student = self::_studentId();
			$id_teacher = $_GET['id_teacher'];
			$id_subject = $_GET['id_subject'];
			$year 		= $_GET['year'];

			$this->_custom_panel = true;

			$this->setTabTitle("Оценка преподавателей");

			$this->render("edit", [
				"ang_init_data" => static::_generateAngInit($id_student, $id_teacher, $id_subject, $year),
			]);
		}

		/**
		 * Основной список
		 */
		public function actionReviews()
		{
			$this->_custom_panel = true;

			$ang_init_data = angInit([
				'Subjects' 		=> Subjects::$all,
				'three_letters' => Subjects::$three_letters,
				'id_student'	=> false,
                'Grades'		=> Grades::$all,
                'grades_short'	=> Grades::$short,
                'user'			=> User::fromSession()->dbData(),
				'Teachers'		=> Teacher::getJournalTeachers(),
				'currentPage'	=> $_GET['page'] ? $_GET['page'] : 1,
			]);

			$this->render("reviews", [
				"ang_init_data" => $ang_init_data,
			]);
		}

		/**
		 * Сгенерировать ang_init_data
		 */
		 private function _generateAngInit($id_student, $id_teacher, $id_subject, $year)
		 {
		 	$Student = Student::getLight($id_student);
		 	$Teacher = Teacher::getLight($id_teacher);
		 	$lesson_count = VisitJournal::count([
								"condition" => "id_teacher={$id_teacher} AND id_entity=" . $id_student . "
								AND type_entity='STUDENT' AND id_subject={$id_subject} AND year={$year}"
 							]);

            if (! $lesson_count) {
                $this->renderRestricted();
            }

 			$RatingInfo = TeacherReview::getInfo($id_student, $id_teacher, $id_subject, $year);

 			return angInit([
	 			"Student"		=> $Student,
 				"Teacher" 		=> $Teacher,
 				"RatingInfo"	=> $RatingInfo ? $RatingInfo : (object)['published' => 0],
 				"subject_name" 	=> Subjects::$dative[$id_subject],
 				"id_subject"	=> $id_subject,
 				"user"			=> User::fromSession()->dbData(),
				"users"			=> User::getCached(true), // с system
 				'id_user_review' => dbConnection()->query("SELECT id_user_review FROM students WHERE id = {$id_student}")->fetch_object()->id_user_review,
 				"year"			=> $year,
 				"lesson_count"	=> $lesson_count,
 			]);
		 }

		public function actionAjaxSave()
		{
			extract($_POST);

			$RatingInfo = array_filter($RatingInfo);

			echo TeacherReview::addData($RatingInfo, $id_student, $id_teacher, $id_subject, $year);
		}
	}

<?php

	// Контроллер
	class TeacherReviewsController extends Controller
	{
		public $defaultAction = "main";

		// Папка вьюх
		protected $_viewsFolder	= "teacher_reviews";
		
		public static $allowed_users = [Student::USER_TYPE];
		
		public function beforeAction()
		{
			$this->addJs("ng-teacher-review-app");
		}
		
		public function actionMain()
		{
			$this->setTabTitle("Оценка преподавателей");
			
			$teacher_ids = Student::getExistedTeachers(User::fromSession()->id_entity);

			if ($teacher_ids) {
				$Teachers = Teacher::findAll([
					"condition" => "id IN (" . implode(",", $teacher_ids) . ")"
				]);
			}
			
			$RatingInfo = TeacherReview::getInfo();
			
			$ang_init_data = angInit([
				"Teachers" 	=> $Teachers,
				"RatingInfo"=> $RatingInfo,
			]);
			
			$this->render("main", [
				"ang_init_data" => $ang_init_data,
			]);
		}
		
		public function actionAjaxSave()
		{
			extract($_POST);
			
			$RatingInfo = array_filter($RatingInfo);
			
			preType($RatingInfo);
			
			TeacherReview::addData($RatingInfo);
		}
	}
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
			
			$VisitJournal = Student::getExistedTeachers(User::fromSession()->id_entity);
			
			foreach ($VisitJournal as $VJ) {
				$Teacher = Teacher::findById($VJ->id_teacher);
				$Teacher->lessons_count = VisitJournal::count([
					"condition" => "id_teacher={$VJ->id_teacher} AND id_entity=" . User::fromSession()->id_entity . " 
						AND type_entity='STUDENT' AND id_subject={$VJ->id_subject}"
				]);
				$Teacher->id_subject = $VJ->id_subject;
				
				$Teachers[] = $Teacher;
			}

			$RatingInfo = TeacherReview::getInfo();
			
			$ang_init_data = angInit([
				"Teachers" 	=> $Teachers,
				"RatingInfo"=> $RatingInfo,
				"Subjects" 	=> Subjects::$dative,
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
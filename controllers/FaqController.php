<?php	// Контроллер	class FaqController extends Controller	{		public $defaultAction = "faq";		public static $allowed_users = [Admin::USER_TYPE, Teacher::USER_TYPE, Student::USER_TYPE];		// Папка вьюх		protected $_viewsFolder	= "faq";		public function actionFaq()		{			$this->setTabTitle("Необходимая информация");            // type-rights-refactored			switch (User::fromSession()->type) {				case Student::USER_TYPE:				{					$this->render("student_faq");					break;				}				case Teacher::USER_TYPE:				{					$this->render("teacher_faq");					break;				}			}		}	}
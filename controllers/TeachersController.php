<?php

	// Контроллер
	class TeachersController extends Controller
	{
		public $defaultAction = "list";

		// Папка вьюх
		protected $_viewsFolder	= "teacher";
		
		public function beforeAction()
		{
			$this->addJs("ng-teacher-app");
		}
		
		public function actionList()
		{
			$this->setTabTitle("Преподователи");
		}
		
		public function actionEdit()
		{
			extract($_GET);
			
			$this->setTabTitle("Редактирование преподавателя");
			
			$Teacher = Teacher::findById($id_teacher);
			
			$ang_init_data = angInit([
				"Teacher" => $Teacher,
				"teacher_phone_level"	=> $Teacher->phoneLevel(),
			]);
			
			$this->render("edit", [
				"ang_init_data" => $ang_init_data
			]);
		}
		
	}
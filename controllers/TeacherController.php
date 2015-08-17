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
		
		public function actionList()
		{
			$this->setTabTitle("Преподователи");
			$this->setRightTabTitle("<a href='teachers/add'>добавить преподавателя</a>");
			
			$Teachers = Teacher::findAll();
			
			$ang_init_data = angInit([
				"Teachers" => $Teachers,
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
				$this->setTabTitle("Редактирование преподавателя №{$_GET['id']}");
				$Teacher = Teacher::findById($_GET['id']);
			}
						
			$ang_init_data = angInit([
				"Teacher" => $Teacher,
				"teacher_phone_level"	=> $Teacher->phoneLevel(),
			]);
			
			$this->render("edit", [
				"ang_init_data" => $ang_init_data
			]);
		}
		
		public function actionAjaxSave()
		{
			$Teacher = $_POST;
			
			if ($Teacher['id']) {
				Teacher::updateById($Teacher['id'], $Teacher);
			} else {
				$NewTeacher = new Teacher($Teacher);
				returnJSON($NewTeacher->save());
			}
		}
		
	}
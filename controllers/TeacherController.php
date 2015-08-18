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
				$id_teacher = $_GET['id'];
				$this->setTabTitle("Редактирование преподавателя №{$id_teacher}");
				$this->setRightTabTitle("<span class='link-reverse pointer' onclick='deleteTeacher($id_teacher)'>удалить преподавателя</span>");
				$Teacher = Teacher::findById($id_teacher);
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
		
		public function actionAjaxDelete()
		{
			Teacher::deleteById($_POST["id_teacher"]);
		}
		
	}
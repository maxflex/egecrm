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
			
			foreach ($Teachers as $index => &$Teacher) {
				foreach ($Teacher->branches as $id_branch) {
					if (!$id_branch) {
						continue;
					}
					$Teacher->branch_short[$id_branch] = Branches::getShortColoredById($id_branch);
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
				$this->setRightTabTitle("<span class='link-reverse pointer' onclick='deleteTeacher($id_teacher)'>удалить преподавателя</span>");
				$Teacher = Teacher::findById($id_teacher);
			}
			
			$this->addJs("bootstrap-select");
			$this->addCss("bootstrap-select");
			
			$ang_init_data = angInit([
				"Teacher" => $Teacher,
				"teacher_phone_level"	=> $Teacher->phoneLevel(),
				"Subjects"	=> Subjects::$all,
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
				returnJSON($NewTeacher->save());
			}
		}
		
		public function actionAjaxDelete()
		{
			Teacher::deleteById($_POST["id_teacher"]);
		}
		
	}
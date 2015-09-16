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
			
			foreach ($Teachers as &$Teacher) {
				$Teacher->login_count = User::getLoginCount($Teacher->id, Teacher::USER_TYPE);
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
				$Teacher->Reviews = Teacher::getReviews($Teacher->id);
				
				$Groups = Teacher::getGroups($id_teacher);
			}
			
			$this->addJs("bootstrap-select");
			$this->addCss("bootstrap-select");
			
			$ang_init_data = angInit([
				"Teacher" => $Teacher,
				"freetime"		=> $Teacher->getFreetime(),
				"teacher_phone_level"	=> $Teacher->phoneLevel(),
				"branches_brick"		=> Branches::getShortColored(),
				"Groups"				=> $Groups,
				"GroupLevels"			=> GroupLevels::$all,
				"Subjects"	=> Subjects::$three_letters,
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
				# СВОБОДНОЕ ВРЕМЯ
				TeacherFreetime::addData($Teacher['freetime'], $Teacher['id'], $Teacher['branches']);
				Teacher::updateById($Teacher['id'], $Teacher);
			} else {
				$NewTeacher = new Teacher($Teacher);
				$saved = $NewTeacher->save();
				TeacherFreetime::addData($Teacher['freetime'], $NewTeacher->id);
				returnJSON($saved);
			}
		}
		
		public function actionAjaxDelete()
		{
			Teacher::deleteById($_POST["id_teacher"]);
		}
		
	}
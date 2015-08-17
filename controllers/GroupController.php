<?php

	// Контроллер
	class GroupController extends Controller
	{
		public $defaultAction = "list";

		// Папка вьюх
		protected $_viewsFolder	= "group";
		
		public function beforeAction()
		{
			$this->addJs("ng-group-app");
		}
		
		public function actionList()
		{
			$this->setTabTitle("Группы");
			$this->setRightTabTitle("<a href='groups/add'>добавить группу</a>");
			
			$Groups = Group::findAll();
			
			$ang_init_data = angInit([
				"Groups" => $Groups,
			]);
			
			$this->render("list", [
				"Groups" => $Groups,
				"ang_init_data" => $ang_init_data
			]);
		}
		
		public function actionAdd()
		{
			$Group = new Group();
			$this->actionEdit($Group);
		}	
			
		public function actionEdit($Group = false)
		{
			$this->addCss("bootstrap-select");
			$this->addJs("bootstrap-select");
			// не надо панель рисовать
			$this->_custom_panel = true;
			
			if (!$Group) {
				$Group = Group::findById($_GET['id']);
			}
			
			$Teachers = Teacher::findAll();
			$Students = Student::getWithContract(true);
			
			foreach ($Students as &$Student) {
				$Student->Contract 	= $Student->getLastContract();
				$Student->is_not_full = $Student->isNotFull();
			}
			
			// сортировка по номеру договора
			usort($Students, function($a, $b) {
				return ($a->Contract->id < $b->Contract->id ? -1 : 1);
			});

			
			$ang_init_data = angInit([
				"Group" => $Group,
				"Teachers" => $Teachers,
				"Students"	=> $Students,
			]);
			
			$this->render("edit", [
				"Group"			=> $Group,
				"ang_init_data" => $ang_init_data
			]);
		}
		
		public function actionAjaxSave()
		{
			$Group = $_POST;
			
			if ($Group['id']) {
				Group::updateById($Group['id'], $Group);
			} else {
				$NewGroup = new Group($Group);
				returnJSON($NewGroup->save());
			}
		}
		
	}
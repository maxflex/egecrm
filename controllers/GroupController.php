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
		
		public function actionSchedule()
		{
			// не надо панель рисовать
			$this->_custom_panel = true;
			
			$id_group = $_GET['id'];		
			$Group = Group::findById($id_group);
			
			$Group->Schedule = $Group->getSchedule();
			
			$ang_init_data = angInit([
				"Group" 		=> $Group,
				"vocation_dates"=> GroupSchedule::getVocationDates(),
			]);
			
			$this->render("schedule", [
				"Group"			=> $Group,
				"ang_init_data" => $ang_init_data,	
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
				foreach ($Student->branches as $id_branch) {
					if (!$id_branch) {
						continue;
					}
					$Student->branch_svg[] = Branches::metroSvg($id_branch);
				}
			}
			
			// сортировка по номеру договора
			usort($Students, function($a, $b) {
				return ($a->Contract->id < $b->Contract->id ? -1 : 1);
			});

			
			$ang_init_data = angInit([
				"Group" 	=> $Group,
				"Teachers"	=> $Teachers,
				"Students"	=> $Students,
				"Subjects"	=> Subjects::$all,
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
		
		public function actionAjaxDelete()
		{
			Group::deleteById($_POST["id_group"]);
		}
		
		
		public function actionAjaxDeleteScheduleDate()
		{
			extract($_POST);
			
			GroupSchedule::deleteAll([
				"condition" => "date='$date' AND id_group=$id_group"
			]);
		}
		
		public function actionAjaxAddScheduleTime()
		{
			extract($_POST);
			
			$GroupSchedule = GroupSchedule::find([
				"condition" => "date='$date' AND id_group='$id_group'"
			]);
			
			$GroupSchedule->time = $time;
			
			$GroupSchedule->save("time");
		}
		
		public function actionAjaxAddScheduleDate()
		{
			extract($_POST);
			
			GroupSchedule::add([
				"date" => $date,
				"id_group" => $id_group,
			]);
		}
		
		public function actionAjaxTimeFromGroup()
		{
			extract($_POST);
			
			dbConnection()->query("UPDATE ".GroupSchedule::$mysql_table." SET time='$time' WHERE time IS NULL AND id_group=$id_group");
		}
	}
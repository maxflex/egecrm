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
			
			$this->addCss("bootstrap-select");
			$this->addJs("bootstrap-select, dnd");
			
			$Groups = Group::findAll();
			
			$ang_init_data = angInit([
				"Groups" 	=> $Groups,
				"Subjects" 	=> Subjects::$all,
				"Grades"	=> Grades::$all,
				"mode" 		=> ($_GET["mode"] == "students" ? 1 : 2),
				"change_mode" => ($_GET["mode"] == "students" ? 1 : 2),
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
			$this->addJs("bootstrap-select, jquery.simulate");
			// не надо панель рисовать
			$this->_custom_panel = true;
			
			if (!$Group) {
				$Group = Group::findById($_GET['id']);
			}
			
			$Teachers = Teacher::findAll();
			
			$Students = [];
			foreach ($Group->students as $id_student) {
				$Student = Student::findById($id_student);
				$Student->fio = $Student->fio();
				$Student->Contract 	= $Student->getLastContract();
				
				foreach ($Student->branches as $id_branch) {
					if (!$id_branch) {
						continue;
					}
					$Student->branch_short[$id_branch] = Branches::getShortColoredById($id_branch);
				}
				
				if (array_key_exists($Student->id, $Group->student_statuses)) {
					$Student->id_status = $Group->student_statuses[$Student->id];
				}
				
				$Students[] = $Student;
			}
			
			// сортировка по номеру договора
			usort($Students, function($a, $b) {
				return ($a->Contract->id < $b->Contract->id ? -1 : 1);
			});
			
			$ang_init_data = angInit([
				"Group" 	=> $Group,
				"Teachers"	=> $Teachers,
				"TmpStudents" => $Students,
//				"Students"	=> $Students,
				"Subjects"	=> Subjects::$all,
				"Cabinets"	=> Cabinet::getByBranch($Group->id_branch),
				"GroupStudentStatuses" => GroupStudentStatuses::$all,
			]);
			
			$this->render("edit", [
				"Group"			=> $Group,
				"ang_init_data" => $ang_init_data
			]);
		}
		
		public function actionAjaxGetStudents()
		{
			$Students = Student::getWithContract();
			$Group    = Group::findById($_POST['id_group']);
			
			foreach ($Students as $index => &$Student) {
				$Student->Contract 	= $Student->getLastContract();
				
				$Student->in_other_group = $Student->inOtherGroup($_POST['id_group'], $_POST['id_subject']) ? true : false;
				
				if ($Group && array_key_exists($Student->id, $Group->student_statuses)) {
					$Student->id_status = $Group->student_statuses[$Student->id];
				}
								
				foreach ($Student->branches as $id_branch) {
					if (!$id_branch) {
						continue;
					}
					$Student->branch_short[$id_branch] = Branches::getShortColoredById($id_branch);
				}
			}
			
			// сортировка по номеру договора
			usort($Students, function($a, $b) {
				return ($a->Contract->id < $b->Contract->id ? -1 : 1);
			});
			
			echo json_encode($Students, JSON_NUMERIC_CHECK);
		}	
			
		public function actionAjaxSave()
		{
			$Group = $_POST;
			
			if ($Group['id']) {
				Group::updateById($Group['id'], $Group);
				GroupStudentStatuses::saveData($Group['id'], $Group['student_statuses']);
			} else {
				$NewGroup = new Group($Group);
				$NewGroup->save();
				GroupStudentStatuses::saveData($NewGroup->id, $Group['student_statuses']);
				returnJson($NewGroup->id);
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
		
		public function actionAjaxInGroup(){
			extract($_POST);
			
			$in_other_group = Student::inOtherGroupStatic($id_student, $id_group, $id_subject) ? true : false;
			
			returnJsonAng($in_other_group);
		}
		
		public function actionAjaxGetCabinet() {
			extract($_POST);
			
			returnJsonAng(
				Cabinet::getByBranch($id_branch)
			);
		}		
		
		public function actionAjaxAddStudentDnd() {
			extract($_POST);
			
			$Group = Group::findById($id_group);
			
			$Group->students[] = $id_student;
			
			$Group->save("students");
		}
	}
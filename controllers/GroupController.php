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
			
// 			$mode = ($_GET["mode"] == "students" ? 1 : ($_GET["mode"] == "nogroup") ? 2 : 0);
			$mode = ($_GET["mode"] == "students" ? 1 : 2);
// 			$mode = 2;
			
			$ang_init_data = angInit([
				"Groups" 	=> $Groups,
				"Subjects" 	=> Subjects::$all,
				"Grades"	=> Grades::$all,
				"mode" 			=> $mode,
				"change_mode" 	=> $mode,
			]);
			
			$this->render("list", [
				"Groups" 		=> $Groups,
				"mode"			=> $mode,
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
				$this->addJs("dnd");
				$Group = Group::findById($_GET['id']);
			}
			
			$Teachers = Teacher::findAll();
			
			$Students = [];
			foreach ($Group->students as $id_student) {
				$Student = Student::findById($id_student);
				$Student->Contract 	= $Student->getLastContract();
								
				foreach ($Student->branches as $id_branch) {
					if (!$id_branch) {
						continue;
					}
					$Student->branch_short[$id_branch] = Branches::getShortColoredById($id_branch);
				}
				
				$freetime = $Student->getGroupFreetime($Group->id);
				$Student->freetime 			= $freetime["freetime"];
				$Student->freetime_red 		= $freetime["freetime_red"];
				$Student->freetime_red_half = $freetime["freetime_red_half"];
				
				if (array_key_exists($Student->id, $Group->student_statuses)) {
					$Student->id_status = $Group->student_statuses[$Student->id];
				}
				$Students[] = $Student;
			}
			
			// сортировка по номеру договора
			usort($Students, function($a, $b) {
				return ($a->Contract->id < $b->Contract->id ? -1 : 1);
			});
			
			
			// Свободное время препода
			$teacher_freetime 			= TeacherFreetime::get($Group->id_teacher);
			$teacher_freetime_red 		= TeacherFreetime::getRed($Group->id, $Group->id_teacher);
			$teacher_freetime_red_full	= TeacherFreetime::getRedFull($Group->id, $Group->id_teacher);
			
			$teacher_freetime_orange	= TeacherFreetime::getOrange($Group->id, $Group->id_branch, $Group->id_teacher, $teacher_freetime_red, $teacher_freetime_red_full);
			$teacher_freetime_orange_half = $teacher_freetime_orange['half'];
			$teacher_freetime_orange_full = $teacher_freetime_orange['full'];
			
			$ang_init_data = angInit([
				"Group" 	=> $Group,
				"Teachers"	=> $Teachers,
				"TmpStudents" => $Students,
//				"Students"	=> $Students,
				"Subjects"	=> Subjects::$all,
				"subjects_short" => Subjects::$short,
				"Cabinets"	=> Cabinet::getByBranch($Group->id_branch),
				"GroupStudentStatuses"	=> GroupStudentStatuses::$all,
				"GroupTeacherStatuses"	=> GroupTeacherStatuses::$all,
				"branches_brick"		=> Branches::getShortColored(),
				"cabinet_freetime"		=> Cabinet::getFreetime($Group->id, $Group->cabinet),
				"teacher_freetime"		=> $teacher_freetime_red, // red half
				"teacher_freetime_green"=> $teacher_freetime,
				"teacher_freetime_red"	=> $teacher_freetime_red_full,
				"teacher_freetime_orange_half" 	=> $teacher_freetime_orange_half,
				"teacher_freetime_orange_full"	=> $teacher_freetime_orange_full,
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
				
				$freetime = $Student->getGroupFreetime($_POST['id_group']);
				$Student->freetime 			= $freetime['freetime'];
				$Student->freetime_red 		= $freetime['freetime_red'];
				$Student->freetime_red_half = $freetime['freetime_red_half'];
				
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
				if (!isset($Group['students'])) {
					$Group['students'] = [];
				}
				Group::updateById($Group['id'], $Group);
				GroupStudentStatuses::saveData($Group['id'], $Group['student_statuses']);
				GroupTeacherStatuses::saveData($Group['id'], $Group['teacher_status'], $Group['Teacher']['id']);
				GroupTime::addData($Group['day_and_time'], $Group['id']);
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
		
		public function actionAjaxGetCabinetFreetime() {
			extract($_POST);
			
			returnJsonAng(
				Cabinet::getFreetime($id_group, $cabinet)	
			);
		}
		
		public function actionAjaxGetTeacherFreetime() {
			extract($_POST);
			
			
			// Свободное время препода
			$teacher_freetime 			= TeacherFreetime::get($id_teacher);
			$teacher_freetime_red 		= TeacherFreetime::getRed($id_group, $id_teacher);
			$teacher_freetime_red_full	= TeacherFreetime::getRedFull($id_group, $id_teacher);
			
			$teacher_freetime_orange	= TeacherFreetime::getOrange($id_group, $id_branch, $id_teacher, $teacher_freetime_red, $teacher_freetime_red_full);
			$teacher_freetime_orange_half = $teacher_freetime_orange['half'];
			$teacher_freetime_orange_full = $teacher_freetime_orange['full'];
			
			// статус согласия нового препода
			$teacher_status = GroupTeacherStatuses::getStatus($id_group, $id_teacher);
			if ($teacher_status) {
				$teacher_status = $teacher_status->id_status;
			}
			if (!$teacher_status) {
				$teacher_status = "";
			}

			
			returnJsonAng([
				"red" 		=> $teacher_freetime_red,
				"red_full" 	=> $teacher_freetime_red_full,
				"green"		=> $teacher_freetime,
				"orange"	=> $teacher_freetime_orange_half,
				"orange_full" 	=> $teacher_freetime_orange_full,
				"teacher_status"=> $teacher_status,
			]);
		}
	}
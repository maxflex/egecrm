<?php

	// Контроллер
	class GroupController extends Controller
	{
		public $defaultAction = "list";

		// Папка вьюх
		protected $_viewsFolder	= "group";
		
		public static $allowed_users = [User::USER_TYPE, Teacher::USER_TYPE, Student::USER_TYPE];
		
		public function beforeAction()
		{
			$this->addJs("ng-group-app");
			
/*
			ini_set("display_errors", 1);
			error_reporting(E_ALL);
*/
			
		}
		
		public function actionLesson()
		{
			$this->setRights([User::USER_TYPE, Teacher::USER_TYPE]);
			$date 		= $_GET['date'];
			$id_group 	= $_GET['id_group'];
			
			if (!Group::inSchedule($id_group, $date)) {
				$this->setTabTitle("Ошибка");
				$this->render("no_lesson");
			} else {
				$this->_custom_panel = true;
				$Group = Group::findById($id_group);
				$registered_in_journal = $Group->registeredInJournal($date);
				
				// если занятие уже зарегистрировано, берем данные из журнала
				if ($registered_in_journal) {
					$LessonData = VisitJournal::findAll([
						"condition" => "lesson_date='$date' AND id_group=$id_group AND type_entity='". Student::USER_TYPE ."'"
					]);
					
					$student_ids = [];
					foreach ($LessonData as $OneData) {
						$student_ids[] = $OneData->id_entity;
						$OrderedLessonData[$OneData->id_entity] = $OneData;
					}
					
					$Group->Students = Student::findAll(["condition" => "id IN (". implode(",", $student_ids) .")"]);
				} else {
					$Group->Students = $Group->getStudents();
				}
				
				$ang_init_data = angInit([
					"Group" 	=> $Group,
					"LessonData"=> $OrderedLessonData,
					"lesson_statuses" => LessonData::$statuses,
					"id_group"		=> $id_group,
					"date"			=> $date,
					"registered_in_journal" => $Group->registeredInJournal($date),
				]);
				
				
				$this->render("lesson", [
					"ang_init_data" => $ang_init_data,
				]);
			}
		}
		
		public function actionList()
		{
			if (User::fromSession()->type == Teacher::USER_TYPE) {
				$this->setTabTitle("Мои группы");
				$Groups = Teacher::getGroups(User::fromSession()->id_entity);
				
				foreach ($Groups as &$Group) {
					$Group->Schedule = $Group->getSchedule();
				}
				
				$ang_init_data = angInit([
					"Groups" 		=> $Groups,
					"Subjects" 		=> Subjects::$all,
					"Grades"		=> Grades::$all,
					"GroupLevels"	=> GroupLevels::$all,
					"Branches"		=> Branches::$all,				
				]);
				
				$this->render("list_for_teachers", [
					"Groups" 		=> $Groups,
					"ang_init_data" => $ang_init_data
				]);
				
			} else 
			if (User::fromSession()->type == Student::USER_TYPE) {
				$this->setTabTitle("Мои группы");
				$Groups = Student::getGroupsStatic(User::fromSession()->id_entity);
				
				foreach ($Groups as &$Group) {
					$Group->Schedule = $Group->getSchedule();
				}
				
				$ang_init_data = angInit([
					"Groups" 		=> $Groups,
					"Subjects" 		=> Subjects::$all,
					"Grades"		=> Grades::$all,
					"GroupLevels"	=> GroupLevels::$all,
					"Branches"		=> Branches::$all,
				]);
				
				$this->render("list_for_students", [
					"Groups" 		=> $Groups,
					"ang_init_data" => $ang_init_data
				]);
			} else {
				// не надо панель рисовать
				$this->_custom_panel = true;
				
				$this->addCss("bootstrap-select");
				$this->addJs("bootstrap-select, dnd");
				
				$Groups = Group::findAll();
/*
				
				foreach ($Groups as &$Group) {
				//	$Group->Schedule = $Group->getScheduleCached();
					$Group->schedule_count = $Group->getScheduleCountCached();
				}
*/
				
				$mode = ($_GET["mode"] == "students" ? 1 : 2);
				
				$Teachers = Teacher::getActiveGroups();
				
				$Stats = Group::getStats();
				
				$ang_init_data = angInit([
					"Groups" 	=> $Groups,
					"Teachers"	=> $Teachers,
					"Subjects" 	=> Subjects::$three_letters,
					"Grades"	=> Grades::$all,
					"mode" 			=> $mode,
					"change_mode" 	=> $mode,
					"GroupLevels"	=> GroupLevels::$all,
					"Stats"		=> $Stats,
				]);
				
				$this->render("list", [
					"Groups" 		=> $Groups,
					"mode"			=> $mode,
					"ang_init_data" => $ang_init_data
				]);
			}
		}

		public function actionSchedule()
		{
			if (User::fromSession()->type == Student::USER_TYPE) {
				// не надо панель рисовать
				$this->_custom_panel = true;
				
				$id_group = $_GET['id'];		
				$Group = Group::findById($id_group);
				
				$Group->Schedule = $Group->getSchedule();
				
				$Teacher = Teacher::findById($Group->id_teacher);
				
				if (!$Teacher) {
					$Teacher = 0;
				}
								
				$ang_init_data = angInit([
					"Group" 				=> $Group,
					"Teacher"				=> $Teacher,
					"vocation_dates"		=> GroupSchedule::getVocationDates(),
					"past_lesson_dates" 	=> $Group->getPastLessonDates(),
				]);
				
				$this->render("student_schedule", [
					"Group"			=> $Group,
					"ang_init_data" => $ang_init_data,
				]);	
			} else 
			if (User::fromSession()->type == Teacher::USER_TYPE) {
				// не надо панель рисовать
				$this->_custom_panel = true;
				
				$id_group = $_GET['id'];		
				$Group = Group::findById($id_group);
				
				$Group->Schedule = $Group->getSchedule();
				
				$Teacher = Teacher::findById($Group->id_teacher);
				
				if (!$Teacher) {
					$Teacher = 0;
				}
								
				$ang_init_data = angInit([
					"Group" 				=> $Group,
					"Teacher"				=> $Teacher,
					"vocation_dates"		=> GroupSchedule::getVocationDates(),
					"past_lesson_dates" 	=> $Group->getPastLessonDates(),
				]);
				
				$this->render("teacher_schedule", [
					"Group"			=> $Group,
					"ang_init_data" => $ang_init_data,
				]);
			} else {
				// не надо панель рисовать
				$this->_custom_panel = true;
				
				$id_group = $_GET['id'];		
				$Group = Group::findById($id_group);
				
				$Group->Schedule = $Group->getSchedule();
				
				foreach ($Group->day_and_time as $day_data) {
					if ($Group->default_time) {
						break;
					}
					foreach ($day_data as $index => $time) {
						$Group->default_time = $time;
						break;
					}
				}
				
				$ang_init_data = angInit([
					"Group" 			=> $Group,
					"past_lesson_dates" => $Group->getPastLessonDates(),
					"vocation_dates"	=> GroupSchedule::getVocationDates(),
				]);
				
				$this->render("schedule", [
					"Group"			=> $Group,
					"ang_init_data" => $ang_init_data,	
				]);	
			}
		}
		
		public function actionAdd()
		{
			$Group = new Group();
			
			$this->actionEdit($Group);
		}	
			
		public function actionEdit($Group = false)
		{
			$this->setRights();
			$this->addCss("bootstrap-select");
			$this->addJs("bootstrap-select, jquery.simulate");
			// не надо панель рисовать
			$this->_custom_panel = true;
			
			if (!$Group) {
				$this->addJs("dnd");
				$Group = Group::findById($_GET['id']);
				$Group->day_and_time_2 = $Group->day_and_time;
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
				
				$freetime = $Student->getGroupFreetime($Group->id, $Group->id_branch);
				$Student->freetime 			= $freetime["freetime"];
				$Student->freetime_red 		= $freetime["freetime_red"];
				$Student->freetime_red_half = $freetime["freetime_red_half"];
				$Student->red_doubleblink 	= $freetime["red_doubleblink"];
				
				$Student->freetime_orange	 	= $freetime["freetime_orange"];
				$Student->freetime_orange_full 	= $freetime["freetime_orange_full"];
				
				# Статус доставки СМС
				$Student->delivery_data			= $Student->getAwaitingSmsStatuses($Group->id);
				
				if (array_key_exists($Student->id, $Group->student_statuses)) {
					$Student->id_status	= $Group->student_statuses[$Student->id]['id_status'];
					$Student->notified	= $Group->student_statuses[$Student->id]['notified'];
				}
				$Students[] = $Student;
			}
			
			// сортировка по номеру договора
			usort($Students, function($a, $b) {
				return ($a->Contract->id < $b->Contract->id ? -1 : 1);
			});
			
			
			// Свободное время препода
			$teacher_freetime 			= TeacherFreetime::get($Group->id_teacher);
			
			$teacher_freetime_all 		= TeacherFreetime::getRedAll($Group->id, $Group->id_teacher);
			$teacher_freetime_red 		= $teacher_freetime_all['red_half'];
			$teacher_freetime_red_full	= $teacher_freetime_all['red_full'];
			
			$teacher_freetime_doubleblink = $teacher_freetime_all['red_doubleblink'];
			
			$teacher_freetime_orange	= TeacherFreetime::getOrange($Group->id, $Group->id_branch, $Group->id_teacher, $teacher_freetime_red, $teacher_freetime_red_full);
			$teacher_freetime_orange_half = $teacher_freetime_orange['half'];
			$teacher_freetime_orange_full = $teacher_freetime_orange['full'];
			
			
			
			$ang_init_data = angInit([
				"Group" 	=> $Group,
				"Teachers"	=> $Teachers,
				"TmpStudents" => $Students,
//				"Students"	=> $Students,
				"Subjects"	=> Subjects::$three_letters,
				"GroupLevels" => GroupLevels::$all,
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
				"teacher_freetime_doubleblink"	=> $teacher_freetime_doubleblink,
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
				
				$freetime = $Student->getGroupFreetime($_POST['id_group'], $_POST['id_branch']);
				$Student->freetime 				= $freetime['freetime'];
				$Student->freetime_red 			= $freetime['freetime_red'];
				$Student->freetime_red_half 	= $freetime['freetime_red_half'];
				$Student->freetime_orange	 	= $freetime["freetime_orange"];
				$Student->freetime_orange_full 	= $freetime["freetime_orange_full"];
				$Student->red_doubleblink 		= $freetime["red_doubleblink"];
				
				# Статус доставки СМС
// 				$Student->delivery_data			= $Student->getAwaitingSmsStatuses($_POST['id_group']);

				
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
			GroupTime::deleteAll([
				"condition" => "id_group=".$_POST["id_group"]
			]);
			GroupStudentStatuses::deleteAll([
				"condition" => "id_group=".$_POST["id_group"]
			]);
			GroupTeacherStatuses::deleteAll([
				"condition" => "id_group=".$_POST["id_group"]
			]);
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
			$Group = Group::findById($id_group);
			
			$Group->Schedule = $Group->getSchedule();
			
			foreach($Group->Schedule as $Schedule) {
				$d = date("w", strtotime($Schedule->date));
				if ($d == 0) {
					$d = 7;
				}
				$Schedule->time = end($Group->day_and_time[$d]);
				$Schedule->save("time");
			}
//			dbConnection()->query("UPDATE ".GroupSchedule::$mysql_table." SET time='$time' WHERE time IS NULL AND id_group=$id_group");
		}
		
		public function actionAjaxInGroup()
		{
			extract($_POST);
			
			$in_other_group = Student::inOtherGroupStatic($id_student, $id_group, $id_subject) ? true : false;
			
			returnJsonAng($in_other_group);
		}
		
		public function actionAjaxGetCabinet() 
		{
			extract($_POST);
			
			returnJsonAng(
				Cabinet::getByBranch($id_branch)
			);
		}		
		
		public function actionAjaxAddStudentDnd() 
		{
			extract($_POST);
			
			$Group = Group::findById($id_group);
			
			$Group->students[] = $id_student;
			
			$Group->save("students");
		}
		
		public function actionAjaxGetCabinetFreetime() 
		{
			extract($_POST);
			
			returnJsonAng(
				Cabinet::getFreetime($id_group, $cabinet)	
			);
		}
		
		public function actionAjaxGetTeacherFreetime() 
		{
			extract($_POST);
			
			
			// Свободное время препода
			$teacher_freetime 			= TeacherFreetime::get($id_teacher);
			
			$teacher_freetime_all 		= TeacherFreetime::getRedAll($id_group, $id_teacher);
			$teacher_freetime_red 		= $teacher_freetime_all['red_half'];
			$teacher_freetime_red_full	= $teacher_freetime_all['red_full'];
			
			$teacher_freetime_doubleblink = $teacher_freetime_all['red_doubleblink'];
			
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
				"orange_full"		=> $teacher_freetime_orange_full,
				"red_doubleblink" 	=> $teacher_freetime_doubleblink,
				"teacher_status"=> $teacher_status,
			]);
		}
		
		public function actionAjaxGetGroups()
		{
			$Groups = Group::findAll();
			
			returnJsonAng($Groups);
		}

		public function actionAjaxRegisterInJournal()
		{
			extract($_POST);
			$data = array_filter($data);
			
			LessonData::addData($id_group, $date, $data);
			VisitJournal::addData($id_group, $date, $data);
		}
		
		
		/**
		 * Обновить кеш групп.
		 * 
		 */
		public function actionAjaxUpdateCache()
		{
			extract($_POST);
			
			$Group = Group::findById($id_group);
			
			$return = $Group->countSchedule();
			memcached()->set("GroupScheduleCount[{$Group->id}]", $return, 5 * 24 * 3600);
		}
		
		public function actionAjaxUpdateStatsCache()
		{
			memcached()->set("GroupStats", Group::_getStats(), 3600 * 24);
		}
		
		public function actionAjaxSmsNotify()
		{
			extract($_POST);
			
			$Student = Student::findById($id_student);
			$Group 	 = Group::findById($id_group);
			
			$FirstLesson = $Group->getFirstLesson(true);
			
			//=========
			$date = date("n", strtotime($FirstLesson->date));
			$date = russian_month($date);
			
			$date_day = date("j", strtotime($FirstLesson->date)) . " " . $date;
			
			$date_formatted = $date_day;
			
			if ($FirstLesson->time) {
				$time = mb_strimwidth($FirstLesson->time, 0, 5);
				$date_formatted .= " в " . $time;
			}
			//=========
			
			
			
			$Template = Template::getFull(8, [
				"student_name"	=> $Student->last_name . " " . $Student->first_name,
				"subject"		=> Subjects::$dative[$Group->id_subject],
				"branch"		=> Branches::$all[$Group->id_branch],
				"date"			=> $date_formatted,
				"cabinet"		=> Cabinet::findById($Group->cabinet)->number,
			]);
			
			$message = $Template->text;
			
			if ($Template->toStudents()) {
				foreach (Student::$_phone_fields as $phone_field) {
					$student_number = $Student->{$phone_field};
					if (!empty($student_number)) {
						SMS::send($student_number, $message);
					}
				}
			}
			
			if ($Template->toRepresentatives()) {
				if ($Student->Representative) {
					foreach (Student::$_phone_fields as $phone_field) {
						$representative_number = $Student->Representative->{$phone_field};
						if (!empty($representative_number)) {
							SMS::send($representative_number, $message);
						}
					}
				}
			}
		}
	}
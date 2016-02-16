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

		public function actionJournal()
		{
			$id_group 	= $_GET['id_group'];
			$id_group = $_GET['id'];

			$this->setRights([User::USER_TYPE, Teacher::USER_TYPE]);

			$this->setTabTitle("Посещаемость группы №" . $id_group);

			$Group = Group::findById($id_group);

			// restrict other teachers to access journal
			if ((User::fromSession()->type == Teacher::USER_TYPE) && ($Group->id_teacher != User::fromSession()->id_entity)) {
				$this->renderRestricted();
			}


			$Group->Schedule = $Group->getSchedule();


			// get student ids
			$result = dbConnection()->query("
				SELECT id_entity FROM visit_journal
				WHERE id_group=$id_group AND type_entity='STUDENT'
				GROUP BY id_entity
			");

			$student_ids = [];
			while ($row = $result->fetch_object()) {
				$student_ids[] = $row->id_entity;
			}


			if (count($student_ids)) {
				// get students from journal
				$result = dbConnection()->query("
					SELECT id, first_name, last_name FROM students
					WHERE id IN (". implode(",", $student_ids) .")
				");


				$students = [];
				while ($row = $result->fetch_object()) {
					$students[] = $row;
				}
			} else {
				// если пустой журнал
				$this->render("journal_empty");
				return;
			}
			$Group->Students = $students;

			$LessonData = VisitJournal::findAll([
				"condition" => "id_group=$id_group AND type_entity='". Student::USER_TYPE ."'"
			]);


			$ang_init_data = angInit([
				"Group" 		=> $Group,
				"LessonData"	=> $LessonData,
			]);

			$this->render("journal", [
				"ang_init_data" => $ang_init_data,
			]);
		}

		public function actionLesson()
		{
			$this->setRights([User::USER_TYPE, Teacher::USER_TYPE]);

			$date 		= $_GET['date'];
			$id_group 	= $_GET['id_group'];

			if (!Group::inSchedule($id_group, $date)) {
				$this->setTabTitle("Ошибка");
				$this->render("no_lesson", [
					"message" => "Занятие отсутствует"
				]);
			} else {
				// если занятие еще не началось, нельзя переходить в функционал добавления в журнал
				$Schedule = GroupSchedule::find([
					"condition" => "date='$date' AND id_group=$id_group"
				]);
				$schedule_date = $Schedule->date . " " . $Schedule->time;

				if ($schedule_date > now()) {
					$this->setTabTitle("Ошибка");
					$this->render("no_lesson", [
						"message" => "Занятие еще не началось"
					]);
				} else {
					// если дошло досюда, всё хорошо, ошибок нет
					$this->_custom_panel = true;
					$Group = Group::findById($id_group);

					// restrict other teachers to access journal
					if ((User::fromSession()->type == Teacher::USER_TYPE) && ($Group->id_teacher != User::fromSession()->id_entity)) {
						$this->renderRestricted();
					}

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
						"Schedule"	=> $Schedule,
						"lesson_statuses" => VisitJournal::$statuses,
						"id_group"		=> $id_group,
						"date"			=> $date,
						"registered_in_journal" => $Group->registeredInJournal($date),
					]);


					$this->render("lesson", [
						"ang_init_data" => $ang_init_data,
					]);
				}

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
					"time" 			=> Freetime::TIME,
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
					"time" 			=> Freetime::TIME,
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

				// исключить некоторые данные для более быстрой front-end загрузки
				foreach ($Groups as  $index => &$Group) {
					unset($Group->Comments);
					if ($Group->schedule_is_finished) {
//						echo 1;
						unset($Groups[$index]);
					}
				}

				$Teachers = Teacher::getActiveGroups();
				$ang_init_data = angInit([
					"Groups" 		=> $Groups,
					"Branches"		=> Branches::$all,
					"Teachers"		=> $Teachers,
					"Subjects" 		=> Subjects::$three_letters,
					"SubjectsShort" => Subjects::$short,
					"SubjectsFull"	=> Subjects::$all,
					"Grades"		=> Grades::$all,
					"mode" 			=> $mode,
					"change_mode" 	=> $mode,
					"GroupLevels"	=> GroupLevels::$all,
					"time" 			=> Freetime::TIME,
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
					"exam_dates"			=> ExamDay::getExamDates($Group),
					"SubjectsDative"		=> Subjects::$dative,
					"past_lesson_dates" 	=> $Group->getPastLessonDates(),
					"time" 					=> Freetime::TIME,
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

				// restrict other teachers to access journal
				if ($Group->id_teacher != User::fromSession()->id_entity) {
					$this->renderRestricted();
				}

				$Group->Schedule = $Group->getSchedule();
				foreach ($Group->Schedule as &$Schedule) {
					if ($Schedule->cabinet) {
						$Schedule->Cabinet = Cabinet::findById($Schedule->cabinet);
					}
				}

				$Group->Students = Student::findAll([
					"condition" => "id IN (" . implode(',', $Group->students) . ")"
				]);

				$Teacher = Teacher::findById($Group->id_teacher);

				if (!$Teacher) {
					$Teacher = 0;
				}

				$ang_init_data = angInit([
					"Group" 				=> $Group,
					"Teacher"				=> $Teacher,
					"vocation_dates"		=> GroupSchedule::getVocationDates(),
					"SubjectsDative"		=> Subjects::$dative,
					"exam_dates"			=> ExamDay::getExamDates($Group),
					"past_lesson_dates" 	=> $Group->getPastLessonDates(),
					"time" 					=> Freetime::TIME,
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
					"exam_dates"		=> ExamDay::getExamDates($Group),
					"time" 				=> Freetime::TIME,
					"Cabinets"			=> Cabinet::getByBranch($Group->id_branch),
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

			if ($Group->id_teacher) {
				foreach ($Teachers as &$Teacher) {
					if ($Teacher->id == $Group->id_teacher) {
						$Teacher->bar 		= $Teacher->getBar($Group->id, $Group->id_branch);
					}
				}
			}

			$Students = [];
			foreach ($Group->students as $id_student) {
				$Student = Student::findById($id_student);
				$Student->Contract 	= $Student->getLastContract();

				$Student->teacher_like_status 	= TeacherReview::getStatus($Student->id, $Group->id_teacher, $Group->id_subject);
				$Student->sms_notified			= GroupSms::getStatus($id_student, $Group->id_branch, $Group->id_subject, $Group->first_schedule, $Group->cabinet);

				foreach ($Student->branches as $id_branch) {
					if (!$id_branch) {
						continue;
					}
					$Student->branch_short[$id_branch] = Branches::getShortColoredById($id_branch);
				}

				$Student->already_had_lesson	= $Student->alreadyHadLesson($Group->id);
				$Student->bar					= $Student->getBar($Group->id, $Group->id_branch);

				# Статус доставки СМС
				// $Student->delivery_data			= $Student->getAwaitingSmsStatuses($Group->id);

				if (array_key_exists($Student->id, $Group->student_statuses)) {
					$Student->id_status		= $Group->student_statuses[$Student->id]['id_status'];
					$Student->notified		= $Group->student_statuses[$Student->id]['notified'];
					$Student->review_status	= $Group->student_statuses[$Student->id]['review_status'];
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
				"Subjects"	=> Subjects::$three_letters,
				"GroupLevels" => GroupLevels::$all,
				"subjects_short" => Subjects::$short,
				"duration"		=> Group::DURATION,
				"Cabinets"	=> Cabinet::getByBranch($Group->id_branch, $Group->id),
				"branches_brick"		=> Branches::getShortColored(),
				"cabinet_bar"			=> Freetime::getCabinetBar($Group->id, $Group->cabinet),
				"time" => Freetime::TIME,
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
				if (!isset($Group['students'])) {
					$Group['students'] = [];
				}
				Group::updateById($Group['id'], $Group);
				GroupTime::addData($Group['day_and_time'], $Group['id']);
			} else {
				$NewGroup = new Group($Group);
				$NewGroup->save();
				returnJson($NewGroup->id);
			}
		}

		public function actionAjaxDelete()
		{
			Group::deleteById($_POST["id_group"]);

			$condition = [
				"condition" => "id_group=".$_POST["id_group"]
			];

			# Удаляем всё, что связано с группой
			GroupTime::deleteAll($condition);
			GroupSchedule::deleteAll($condition);
			GroupNote::deleteAll($condition);
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

				// если дня нет в расписании группы
				if (!in_array($d, array_keys($Group->day_and_time))) {
					continue;
				}

				$Schedule->time = end($Group->day_and_time[$d]);
				$Schedule->save("time");

				if ($Group->cabinet) {
					$Schedule->cabinet = $Group->cabinet;
					$Schedule->save("cabinet");
				}
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
				Cabinet::getByBranch($id_branch, $id_group)
			);
		}

		public function actionAjaxAddStudentDnd()
		{
			extract($_POST);

			$Group = Group::findById($id_group);

			$Group->students[] = $id_student;

			$Group->save("students");
		}

		public function actionAjaxGetCabinetBar()
		{
			extract($_POST);

			returnJsonAng(
				Freetime::getCabinetBar($id_group, $cabinet)
			);
		}

		public function actionAjaxGetTeacherBar()
		{
			extract($_POST);

			$id_group = $id_group ? $id_group : 0;

			returnJsonAng(
				Freetime::getTeacherBar($id_group, $id_branch, $id_teacher)
			);
		}

		public function actionAjaxGetStudentBars()
		{
			extract($_POST);

			$id_group = $id_group ? $id_group : 0;

			foreach ($student_ids as $id_student) {
				$return[$id_student] = Freetime::getStudentBar($id_group, $id_branch, $id_student);
			}

			returnJsonAng($return);
		}

		public function actionAjaxGetGroups()
		{
			$Groups = Group::findAll();

			returnJsonAng($Groups);
		}

		public function actionAjaxRegisterInJournal()
		{
			extract($_POST);

			// Дополнительный вход
			User::rememberMeLogin();
			$data = array_filter($data);

			VisitJournal::addData($id_group, $date, $data);

			// Обновляем красные счетчики
			if (!LOCAL_DEVELOPMENT) {
				$errors = memcached()->get("JournalErrors");

				if (($key = array_search($id_group, $errors[$date])) !== false) {
					unset($errors[$date][$key]);
					$errors[$date] = array_values($errors[$date]);
					// if no errors now
					if (!count($errors[$date])) {
						unset($errors[$date]);
					}
				    memcached()->set("JournalErrors", $errors, 3600 * 24);
				}
			}
			// CronController::actionUpdateJournalMiss();
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

		public function actionAjaxUpdateCacheAll()
		{
			$Groups = Group::findAll();

			foreach ($Groups as $Group) {
				memcached()->set("GroupScheduleCount[{$Group->id}]", $Group->countSchedule(), 5 * 24 * 3600);
			}
		}

		public function actionAjaxUpdateStatsCache()
		{
			memcached()->set("GroupStats", Group::_getStats(), 3600 * 24);
		}

		public function actionAjaxSmsNotify()
		{
			extract($_POST);

			GroupSms::notify($_POST);

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


			$GroupSchedule = GroupSchedule::find([
				"condition" => "id_group={$Group->id} AND date='" . $FirstLesson->date ."'"
			]);
			$Template = Template::getFull(8, [
				"student_name"	=> $Student->last_name . " " . $Student->first_name,
				"subject"		=> Subjects::$dative[$Group->id_subject],
				"address"		=> Branches::$address[$Group->id_branch],
				"branch"		=> Branches::$all[$Group->id_branch],
				"date"			=> $date_formatted,
				"cabinet"		=> trim(Cabinet::findById($GroupSchedule->cabinet)->number),
			]);

			$message = $Template->text;

			if ($Template->toStudents()) {
				foreach (Student::$_phone_fields as $phone_field) {
					$student_number = $Student->{$phone_field};
					if (!empty($student_number)) {
						if (LOCAL_DEVELOPMENT) {
							Email::send("makcyxa-k@yandex.ru", "Уведомление ученику", $message);
						} else {
							SMS::send($student_number, $message);
						}
					}
				}
			}

			if ($Template->toRepresentatives()) {
				if ($Student->Representative) {
					foreach (Student::$_phone_fields as $phone_field) {
						$representative_number = $Student->Representative->{$phone_field};
						if (!empty($representative_number)) {
							if (LOCAL_DEVELOPMENT) {
								Email::send("makcyxa-k@yandex.ru", "Уведомление представителю", $message);
							} else {
								SMS::send($representative_number, $message);
							}
						}
					}
				}
			}
		}

		// DOWNLOAD SCHEDULE
		public function actionDownloadSchedule()
		{
			header('Content-Type: application/vnd.ms-excel');
			header('Content-Disposition: attachment;filename="расписание.xls"');
			header('Cache-Control: max-age=0');

			$objPHPExcel = new PHPExcel();

			$objPHPExcel->setActiveSheetIndex(0);

			$objPHPExcel->getActiveSheet()->SetCellValue('B1', 'РАСПИСАНИЕ');

			$objPHPExcel->getActiveSheet()->getStyle('B1')
				->getAlignment()
				->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()
				->setBold(true)
				->setName('Apple SD Gothic Neo')
				->setSize(46);

			$objPHPExcel->getActiveSheet()->mergeCells('B1:S1');


			$objPHPExcel->getActiveSheet()->SetCellValue('B3', 'ПОНЕДЕЛЬНИК');
			$objPHPExcel->getActiveSheet()->mergeCells('B3:C3');

			$objPHPExcel->getActiveSheet()->SetCellValue('B4', Freetime::TIME[1]);
			$objPHPExcel->getActiveSheet()->SetCellValue('C4', Freetime::TIME[2]);

			$objPHPExcel->getActiveSheet()->SetCellValue('D3', 'ВТОРНИК');
			$objPHPExcel->getActiveSheet()->mergeCells('D3:E3');

			$objPHPExcel->getActiveSheet()->SetCellValue('D4', Freetime::TIME[1]);
			$objPHPExcel->getActiveSheet()->SetCellValue('E4', Freetime::TIME[2]);

			$objPHPExcel->getActiveSheet()->SetCellValue('F3', 'СРЕДА');
			$objPHPExcel->getActiveSheet()->mergeCells('F3:G3');

			$objPHPExcel->getActiveSheet()->SetCellValue('F4', Freetime::TIME[1]);
			$objPHPExcel->getActiveSheet()->SetCellValue('G4', Freetime::TIME[2]);

			$objPHPExcel->getActiveSheet()->SetCellValue('H3', 'ЧЕТВЕРГ');
			$objPHPExcel->getActiveSheet()->mergeCells('H3:I3');

			$objPHPExcel->getActiveSheet()->SetCellValue('H4', Freetime::TIME[1]);
			$objPHPExcel->getActiveSheet()->SetCellValue('I4', Freetime::TIME[2]);

			$objPHPExcel->getActiveSheet()->SetCellValue('J3', 'ПЯТНИЦА');
			$objPHPExcel->getActiveSheet()->mergeCells('J3:K3');

			$objPHPExcel->getActiveSheet()->SetCellValue('J4', Freetime::TIME[1]);
			$objPHPExcel->getActiveSheet()->SetCellValue('K4', Freetime::TIME[2]);


			$objPHPExcel->getActiveSheet()->SetCellValue('L3', 'СУББОТА');
			$objPHPExcel->getActiveSheet()->mergeCells('L3:O3');

			$objPHPExcel->getActiveSheet()->SetCellValue('L4', Freetime::TIME[3]);
			$objPHPExcel->getActiveSheet()->SetCellValue('M4', Freetime::TIME[4]);
			$objPHPExcel->getActiveSheet()->SetCellValue('N4', Freetime::TIME[5]);
			$objPHPExcel->getActiveSheet()->SetCellValue('O4', Freetime::TIME[6]);


			$objPHPExcel->getActiveSheet()->SetCellValue('P3', 'ВОСКРЕСЕНЬЕ');
			$objPHPExcel->getActiveSheet()->mergeCells('P3:S3');

			$objPHPExcel->getActiveSheet()->SetCellValue('P4', Freetime::TIME[3]);
			$objPHPExcel->getActiveSheet()->SetCellValue('Q4', Freetime::TIME[4]);
			$objPHPExcel->getActiveSheet()->SetCellValue('R4', Freetime::TIME[5]);
			$objPHPExcel->getActiveSheet()->SetCellValue('S4', Freetime::TIME[6]);

			$objPHPExcel->getActiveSheet()->getStyle('B3:S4')
				->getAlignment()
				->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)
				->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

			$objPHPExcel->getActiveSheet()->getStyle('B3:S4')->getFont()
				->setName('Apple SD Gothic Neo')
				->setSize(18);

			// resize default height
		    $objPHPExcel->getActiveSheet()->getRowDimension(3)->setRowHeight(35);
		    $objPHPExcel->getActiveSheet()->getRowDimension(4)->setRowHeight(35);

			$Cabinets = Cabinet::findAll([
				"condition" => "id_branch=" . Branches::TRG,
			]);


			$row = 4;
			$col = 'A';

			foreach ($Cabinets as $Cabinet) {
				$row++;

				$objPHPExcel->getActiveSheet()->SetCellValue($col.$row, 'Кабинет ' . $Cabinet->number);
				$objPHPExcel->getActiveSheet()->getStyle($col.$row)
								->getAlignment()
								->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)
								->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
								->setWrapText(true);
				$objPHPExcel->getActiveSheet()->getStyle($col.$row)->getFont()
								->setName('Apple SD Gothic Neo')
								->setSize(18);

				// Cabinet groups
				$Groups = Group::findAll([
					"condition" => "cabinet=" . $Cabinet->id
				]);

				foreach ($Groups as $Group) {
					$Teacher = Teacher::findById($Group->id_teacher);

					foreach ($Group->day_and_time as $day => $time_data) {
						foreach ($time_data as $time) {
							$time_index = Freetime::getIndexByTime($time);
							if ($day < 6) {
								$time_index -= 2;
							}
// 							h1($day . " | " . $time_index . " | " . $row . " | " . self::getCol($day, $time_index, $row));
							$text = [];
							$text[] = Subjects::$full[$Group->id_subject];
							$text[] = Grades::$all[$Group->grade];
							$text[] = $Teacher->last_name . " " . $Teacher->first_name;
							$text[] = $Teacher->middle_name;
							$text = implode("\r", $text);

							$col_row = self::getColRow($day, $time_index, $row);

							$objPHPExcel->getActiveSheet()->SetCellValue($col_row, $text);
							$objPHPExcel->getActiveSheet()->getStyle($col_row)
								->getAlignment()
								->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)
								->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
								->setWrapText(true);
							$objPHPExcel->getActiveSheet()->getStyle($col_row)->getFont()
								->setName('Apple SD Gothic Neo')
								->setSize(9);
						}
					}
				}
			}

			// Кабинет
			$style_default_border = [
				'borders' => array(
			        'allborders' => array(
			            'style' => PHPExcel_Style_Border::BORDER_THIN,
			            'color' => array('rgb' => 'AAAAAA')
			        )
			    )
			];

			$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(25);
			foreach(range('B', 'S') as $columnID) {
			    $objPHPExcel->getActiveSheet()->getColumnDimension($columnID)->setWidth(20);
			}

			foreach(range(5, $row) as $rowID) {
			    $objPHPExcel->getActiveSheet()->getRowDimension($rowID)->setRowHeight(80);
			    if ($rowID % 2 == 0) {
				    $objPHPExcel->getActiveSheet()->getStyle("A$rowID:S$rowID")
				    	->getFill()
				        ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
				        ->getStartColor()
				        ->setRGB('F2F2F2');
			    }
			    $objPHPExcel->getActiveSheet()->getStyle("A$rowID:S$rowID")->applyFromArray($style_default_border);
			}

			// default border for weekdays
			$objPHPExcel->getActiveSheet()->getStyle("B3:S4")->applyFromArray($style_default_border);

			$style_thick_border = [
				'borders' => array(
			        'outline' => array(
			            'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
			            'color' => array('rgb' => '000000')
			        )
			    )
			];
			$objPHPExcel->getActiveSheet()->getStyle("A5:S$rowID")->applyFromArray($style_thick_border);

/*
			foreach (range(5, $row) as $rowID) {
				$objPHPExcel->getActiveSheet()->getStyle("A{$rowID}")->applyFromArray($style_thick_border);
			}
*/

			foreach (range(1, 7) as $day) {
				switch ($day) {
					case 7: {
						$col_start = 'P';
						break;
					}
					case 6: {
						$col_start = 'L';
						break;
					}
					default: {
						$col_start 	= chr(64 + (2 * $day));
					}
				}
				$col_end	= ($day < 6) ? chr(ord($col_start) + 1) : chr(ord($col_start) + 3);

				$objPHPExcel->getActiveSheet()->getStyle("{$col_start}3:{$col_end}{$row}")->applyFromArray($style_thick_border);
			}

// 			exit();

			$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
			$objWriter->save('php://output');
		}

		private function getColRow($day, $time_index, $row)
		{
			if ($day < 7) {
				$col = chr(64 + (2 * $day) + $time_index);
			} else {
				// воскресенье
				$col = chr(ord('P') + $time_index);
			}
			return $col.$row;
		}


		public function actionAjaxChangeTeacher()
		{
			extract($_POST);

			foreach ($students as $id_student) {
				$return['teacher_like_statuses'][$id_student] = TeacherReview::getStatus($id_student, $id_teacher, $id_subject);
			}

			$return['bar'] = Freetime::getTeacherBar($id_group, $id_branch, $id_teacher);

			Group::updateById($id_group, [
				"id_teacher" => $id_teacher,
			]);

			returnJsonAng($return);
		}

		public function actionAjaxReloadSmsNotificationStatuses()
		{
			extract($_POST);

			foreach ($students as $id_student) {
				$return['sms_notification_statuses'][$id_student] = GroupSms::getStatus($id_student, $id_branch, $id_subject, $first_schedule, $cabinet);
			}

			returnJsonAng($return);
		}

		public function actionAjaxUpdateGroup()
		{
			extract($_POST);

			Group::updateById($id_group, $data);
		}

		public function actionAjaxChangeScheduleCabinet()
		{
			extract($_POST);

			GroupSchedule::updateById($id, [
				"cabinet" => $cabinet,
			]);
		}

		public function actionAjaxChangeScheduleFree()
		{
			extract($_POST);

			GroupSchedule::updateById($id, [
				"is_free" => $is_free,
			]);
		}
	}

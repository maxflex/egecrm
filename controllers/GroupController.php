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
			$this->addJs("https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.2.2/Chart.js", true);
			$this->addJs("https://cdn.jsdelivr.net/angular.chartjs/1.0.2/angular-chart.js", true);
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

			// get teacher ids
			$result = dbConnection()->query("
				SELECT DISTINCT id_entity FROM visit_journal
				WHERE id_group=$id_group AND type_entity='TEACHER'
			");

			$teacher_ids = [];
			while ($row = $result->fetch_object()) {
				$teacher_ids[] = $row->id_entity;
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

			$Teachers = [];
			if (count($teacher_ids)) {
				$Teachers = Teacher::findAll(['condition' => 'id in ('.implode(',', array_unique($teacher_ids)).')']);
				foreach ($Teachers as $Teacher) {
					$Teacher->calcHoldCoeff(['group_id' => $Group->id]);
				}
			}

			$LessonData = VisitJournal::findAll([
				"condition" => "id_group=$id_group" //и преподы и студенты
			]);


			$ang_init_data = angInit([
				"Group" 		=> $Group,
				"LessonData"	=> $LessonData,
				"Teachers"		=> $Teachers,
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

            /* третий парам чтобы не учитовать отменные занятия */
            // @refactored
			if (!Group::inSchedule($id_group, $date, true)) {
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

						$Group->Students = Student::findAll(["condition" => "id IN (". implode(",", $student_ids) .")"], true);
					} else {
						$Group->Students = $Group->getStudents();
					}

					$isAdmin = User::fromSession()->type == User::USER_TYPE;

					$ang_init_data = angInit([
						"Group" 	=> $Group,
						"LessonData"=> (object)$OrderedLessonData,
						"Schedule"	=> $Schedule,
						"lesson_statuses" => VisitJournal::$statuses,
						"id_group"		=> $id_group,
						"date"			=> $date,
						"isAdmin"		=> $isAdmin,
						"registered_in_journal" => $Group->registeredInJournal($date),
					]);

					//изменение исторических данных: доступен только админам
					if ($isAdmin) {
						$this->render("lesson_admin", [
							"ang_init_data" => $ang_init_data,
						]);
					} else {	// т.е. User::fromSession()->type == Teacher::USER_TYPE
						$this->render("lesson", [
							"ang_init_data" => $ang_init_data,
						]);
					}

				}

			}
		}

		public function actionList()
		{

			if (User::fromSession()->type == Teacher::USER_TYPE) {
				$this->setTabTitle("Мои группы");
				$Groups = Teacher::getGroups(User::fromSession()->id_entity);

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

				$Teachers = Teacher::getLightArray(Group::getTeacherIds());

				$ang_init_data = angInit([
					"Cabinets"		=> Cabinet::getByBranch(1),
					"Branches"		=> Branches::$all,
					"Teachers"		=> $Teachers,
					"Subjects" 		=> Subjects::$three_letters,
					"SubjectsShort" => Subjects::$short,
					"SubjectsFull"	=> Subjects::$all,
					"Grades"		=> Grades::$all,
					"GroupLevels"	=> GroupLevels::$all,
					'currentPage'	=> $_GET['page'] ? $_GET['page'] : 1,
				]);

				$this->render("list", [
					"search"		=> (isset($_COOKIE['groups']) ? json_decode($_COOKIE['groups']) : (object)[]),
					"ang_init_data" => $ang_init_data
				]);
			}
		}


		public function actionAdd()
		{
			$Group = new Group();

			$this->actionEdit($Group);
		}

		/**
		 * getSchedule и getVocationDates c параметром true возвращает только активные(не отмененные) занятия.
		 */
		public function actionSchedule()
		{
			if (User::fromSession()->type == Student::USER_TYPE) {
				// не надо панель рисовать
				$this->_custom_panel = true;

				$id_group = $_GET['id'];
				$Group = Group::findById($id_group);

				// @refactored
				$Group->Schedule = $Group->getSchedule();

				foreach ($Group->Schedule as &$Schedule) {
					if ($Schedule->cabinet) {
						$Schedule->Cabinet = Cabinet::findById($Schedule->cabinet);
					}
				}

				$Teacher = Teacher::findById($Group->id_teacher);

				if (!$Teacher) {
					$Teacher = 0;
				}

				$ang_init_data = angInit([
					"Group" 				=> $Group,
					"Teacher"				=> $Teacher,
					"vocation_dates"		=> GroupSchedule::getVocationDates(true),
					"exam_dates"			=> ExamDay::getExamDates($Group),
					"SubjectsDative"		=> Subjects::$dative,
					"past_lessons" 	        => $Group->getPastLessons(), // @time-refactored @time-checked
					"cancelled_lesson_dates" => $Group->getCancelledLessonDates(),
                    "all_cabinets"			=> Branches::allCabinets(), // @to show past lesson cabinet number
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

					// @refactored
					$Group->Schedule = $Group->getSchedule();
					foreach ($Group->Schedule as &$Schedule) {
						if ($Schedule->cabinet) {
							$Schedule->Cabinet = Cabinet::findById($Schedule->cabinet);
						}
					}

                    $Group->Students = [];
                    foreach ($Group->students as $id_student) {
                        $Student = Student::getLight($id_student);
                        if ($Group->grade && $Group->id_subject) {
                            $Student->Test = TestStudent::getForGroup($id_student, $Group->id_subject, $Group->grade);
                        }
                        $Group->Students[] = $Student;
                    }

					$Teacher = Teacher::findById($Group->id_teacher);

					if (!$Teacher) {
						$Teacher = 0;
					}

					$ang_init_data = angInit([
						"Group" 				=> $Group,
						"Teacher"				=> $Teacher,
						"vocation_dates"		=> GroupSchedule::getVocationDates(true),
						"SubjectsDative"		=> Subjects::$dative,
						"exam_dates"			=> ExamDay::getExamDates($Group),
						"past_lessons" 			=> $Group->getPastLessons(), // @time-refactored @time-checked
						"cancelled_lesson_dates" => $Group->getCancelledLessonDates(),
                        "all_cabinets"			=> Branches::allCabinets(), // @to show past lesson cabinet number
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

					$ang_init_data = angInit([
						"Group" 			=> $Group,
						"past_lessons" => $Group->getPastLessons(), 		// @time-refactored @time-checked
						"vocation_dates"	=> GroupSchedule::getVocationDates(),
						"exam_dates"		=> ExamDay::getExamDates($Group),
						"cancelled_lesson_dates" => $Group->getCancelledLessonDates(),
						"all_cabinets"			=> Branches::allCabinets(), // @time-refactored @time-checked
						"Time"				=> Time::getLight(),
					]);

					$this->render("schedule", [
						"Group"			=> $Group,
						"ang_init_data" => $ang_init_data,
					]);
				}
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
			}

			if (! LOCAL_DEVELOPMENT) {
				$Teachers = Teacher::findAll();

				if ($Group->id_teacher) {
					foreach ($Teachers as &$Teacher) {
						if ($Teacher->id == $Group->id_teacher) {
							$Teacher->bar 		= $Teacher->getBar();
						}
					}
				}
			}

			$Students = [];
			foreach ($Group->students as $id_student) {
				$Student = Student::findById($id_student);
				$Student->Contract 	= $Student->getLastContract($Group->year);

				$Student->teacher_like_status 	= TeacherReview::getStatus($Student->id, $Group->id_teacher, $Group->id_subject, $Group->year);
				$Student->sms_notified			= GroupSms::getStatus($id_student, $Group);

				if ($Group->grade && $Group->id_subject) {
					// тест ученика
					$Student->Test = TestStudent::getForGroup($id_student, $Group->id_subject, $Group->grade);
				}

				foreach ($Student->branches as $id_branch) {
					if (!$id_branch) {
						continue;
					}
					$Student->branch_short[$id_branch] = Branches::getShortColoredById($id_branch);
				}

				$Student->already_had_lesson	= $Student->alreadyHadLesson($Group->id);
				$Student->bar					= Freetime::getStudentBar($Student->id, true, $Group->id); // @refactored

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
				"all_cabinets"			=> Branches::allCabinets(),
				"branches_brick"		=> Branches::getShortColored(),
				"cabinet_bars"			=> Freetime::getCabinetBar($Group),
				"time"			=> Time::get(),
				"weekdays"		=> Time::WEEKDAYS,
				"free_cabinets" => Freetime::checkFreeCabinets($Group->id, $Group->year, $Group->day_and_time)
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

		/**
		 * Отмена урока в группе
		 */
		public function actionAjaxCancelScheduleDate()
		{
			extract($_POST);
			$gs = GroupSchedule::find([
					"condition" => "date='$date' AND id_group='$id_group'"
			]);
			if ($gs) {
				$gs->cancelled = 1;
				$gs->save("cancelled");
			}
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

		/**
		 * Adds schedule record.
		 * @return int       Id of new record
		 */
		public function actionAjaxAddScheduleDate()
		{
			extract($_POST);

			$obj = GroupSchedule::add([
						"date" => $date,
						"id_group" => $id_group,
				   ]);
			returnJson($obj->id);
		}

		public function actionAjaxTimeFromGroup()
		{
			extract($_POST);
			GroupSchedule::updateById($id, compact('time', 'cabinet'));
		}

		public function actionAjaxInGroup()
		{
			extract($_POST);

			$in_other_group = Student::inOtherGroupStatic($id_student, $id_group, $id_subject) ? true : false;

			returnJsonAng($in_other_group);
		}

		public function actionAjaxAddStudentDnd()
		{
			extract($_POST);

			$Group = Group::findById($id_group);

			$Group->students[] = $id_student;

			$Group->save("students");

            if ($old_id_group) {
                $OldGroup = Group::findById($old_id_group);
                $OldGroup->students = array_diff($OldGroup->students, array($id_student));;
                $OldGroup->save("students");
            }
		}

		public function actionAjaxGetCabinetBar()
		{
			extract($_POST);

			returnJsonAng(
				Freetime::getCabinetBar(Group::findById($id_group))
			);
		}

		public function actionAjaxGetTeacherBar()
		{
			extract($_POST);

            returnJsonAng(
                $id_teacher ? Freetime::getTeacherBar($id_teacher, true, $id_group) : []
            );
		}

		public function actionAjaxGetStudentBars()
		{
			extract($_POST);

			foreach ($student_ids as $id_student) {
				$return[$id_student] = Freetime::getStudentBar($id_student, true, $id_group);
			}

			returnJsonAng($return);
		}

		// Похожие гуппы (вверху из редактирования группы)
		public function actionAjaxGetGroups()
		{
			// @reafactored
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
		 * Изменение данных журнала без отправки СМС. Доступен только админам.
		 *
		 */
		public function actionAjaxRegisterInJournalWithoutSMS()
		{
			extract($_POST);

			// Дополнительный вход
			User::rememberMeLogin();
			if (User::fromSession()->type == User::USER_TYPE) {
				$data = array_filter($data);
				VisitJournal::updateData($id_group, $date, $data);

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
			// @refactored
			memcached()->set("GroupScheduleCount[{$Group->id}]", $return, 5 * 24 * 3600);
		}

		public function actionAjaxUpdateCacheAll()
		{
			$Groups = Group::findAll();

			// @refactored
			foreach ($Groups as $Group) {
				memcached()->set("GroupScheduleCount[{$Group->id}]", $Group->countSchedule(), 5 * 24 * 3600);
				memcached()->set("GroupPastScheduleCount[{$Group->id}]", VisitJournal::getLessonCount($Group->id), 5 * 24 * 3600);
			}
		}

		public function actionAjaxSmsNotify()
		{
			extract($_POST);

			GroupSms::notify($_POST);

			$Student = Student::findById($id_student);
			$Group 	 = Group::findById($id_group);

			$FirstLesson = Group::getFirstLesson($Group->id, true);

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
			// @time-refactored @time-checked
			// @sms-checked
			$Template = Template::getFull(8, [
				"student_name"	=> $Student->last_name . " " . $Student->first_name,
				"subject"		=> Subjects::$dative[$Group->id_subject],
				"address"		=> Branches::$address[Cabinet::getField($GroupSchedule->cabinet)],
				"branch"		=> Branches::$all[Cabinet::getField($GroupSchedule->cabinet)],
				"date"			=> $date_formatted,
				"cabinet"		=> trim(Cabinet::getField($GroupSchedule->cabinet, 'number')),
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

			// Кабинет
			$style_default_border = [
				'borders' => array(
			        'allborders' => array(
			            'style' => PHPExcel_Style_Border::BORDER_THIN,
			            'color' => array('rgb' => 'AAAAAA')
			        )
			    )
			];

			$style_thick_border = [
				'borders' => array(
			        'outline' => array(
			            'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
			            'color' => array('rgb' => '000000')
			        )
			    )
			];

			$objPHPExcel = new PHPExcel();

			$objPHPExcel->setActiveSheetIndex(0);

			//
			// CABINETS
			//
			$Cabinets = Cabinet::findAll([
				"condition" => "id_branch=" . Branches::TRG,
			]);


			$row = 4;
			$col = 'A';

			foreach ($Cabinets as $Cabinet) {
				$cursor = 'A';
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

				foreach (Time::MAP as $day => $time_ids) {
					foreach($time_ids as $id_time) {
						$cursor = static::_ordToChr(static::_chrToOrd($cursor) + 1);

						$query = dbConnection()->query("
							SELECT g.id_subject, g.id_teacher, g.grade FROM groups g
							JOIN group_time gt ON gt.id_group = g.id
							WHERE gt.id_time = {$id_time} AND gt.id_cabinet = {$Cabinet->id} AND g.year=" . Years::getAcademic());
						if ($query->num_rows) {
							$Group = $query->fetch_object();
							$Teacher = Teacher::getLight($Group->id_teacher);
							$text = [];
							$text[] = Subjects::$full[$Group->id_subject];
							$text[] = Grades::$all[$Group->grade];
							$text[] = $Teacher->last_name . " " . $Teacher->first_name;
							$text[] = $Teacher->middle_name;
							$text = implode("\r", $text);

							$col_row = "{$cursor}{$row}";
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

			//
			// DAYS
			//

			$cursor = 'B';
			$Time = Time::getLight();
			foreach(Time::MAP as $day => $time_ids) {
				$cursor_start = $cursor;
				// preType([$cursor . '3', strtoupper(Time::WEEKDAYS_FULL[$day])]);
				// preType([$cursor . '3:' . static::_ordToChr(ord($cursor) + count($time_ids) - 1) .'3']);
				$objPHPExcel->getActiveSheet()->SetCellValue($cursor . '3', strtoupper(Time::WEEKDAYS_FULL[$day]));
				$objPHPExcel->getActiveSheet()->mergeCells($cursor . '3:' . static::_ordToChr(static::_chrToOrd($cursor) + count($time_ids) - 1) .'3');

				foreach($time_ids as $index => $id_time) {
					$objPHPExcel->getActiveSheet()->SetCellValue(static::_ordToChr(static::_chrToOrd($cursor) + $index) . '4', $Time[$id_time]);
					// preType([static::_ordToChr(ord($cursor) + $index) . '4', $Time[$id_time]]);
				}
				$cursor = static::_ordToChr(static::_chrToOrd($cursor) + count($time_ids));
				$objPHPExcel->getActiveSheet()->getStyle("{$cursor_start}3:" . static::_ordToChr(static::_chrToOrd($cursor) - 1) . "{$row}")->applyFromArray($style_thick_border);
			}
			$cursor = $cursor[0] . chr(ord($cursor[1]) - 1);
			$objPHPExcel->getActiveSheet()->getStyle("B3:{$cursor}4")
				->getAlignment()
				->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)
				->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

			$objPHPExcel->getActiveSheet()->getStyle("B3:{$cursor}4")->getFont()
				->setName('Apple SD Gothic Neo')
				->setSize(18);

			// resize default height weekdays
		    $objPHPExcel->getActiveSheet()->getRowDimension(3)->setRowHeight(35);
		    $objPHPExcel->getActiveSheet()->getRowDimension(4)->setRowHeight(35);

			$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(25);

			// ШИРИНА ВСЕХ ПОЛЕЙ, КРОМЕ ЗАГОЛОВКОВ
			// + 25 потому что А = 65, Z = 90, Z - A = 90 - 65 = 25
			// $objPHPExcel->getActiveSheet()->getHighestDataColumn()[1] – второй символ
			foreach(range(ord('B'), ord($objPHPExcel->getActiveSheet()->getHighestDataColumn()[1]) + 26) as $columnID) {
			    $objPHPExcel->getActiveSheet()->getColumnDimension(static::_ordToChr($columnID))->setWidth(20);
			}

			foreach(range(5, $row) as $rowID) {
			    $objPHPExcel->getActiveSheet()->getRowDimension($rowID)->setRowHeight(80);
			}

			$objPHPExcel->getActiveSheet()->getStyle("A5:{$cursor}{$rowID}")->applyFromArray($style_thick_border);


            // Надпись "РАСПИСАНИЕ" вверху
            $objPHPExcel->getActiveSheet()->SetCellValue('B1', 'РАСПИСАНИЕ');

			$objPHPExcel->getActiveSheet()->getStyle('B1')
				->getAlignment()
				->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()
				->setBold(true)
				->setName('Apple SD Gothic Neo')
				->setSize(46);

			$objPHPExcel->getActiveSheet()->mergeCells("B1:{$cursor}1");

			$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
			$objWriter->save('php://output');
		}

        // ord to chr
		private static function _ordToChr($ord)
		{
			$prefix = '';
			if ($ord > 90) {
				$prefix = 'A';
				$ord -= 26;
			}
			return $prefix . chr($ord);
		}

		// @return convert AA to 91
		private static function _chrToOrd($chr)
		{
			if (strlen($chr) > 1) {
				return ord($chr[1]) + 26;
			} else {
				return ord($chr);
			}
		}


		public function actionAjaxChangeTeacher()
		{
			extract($_POST);
            if (!$id_teacher)
                $id_teacher = 0;

			foreach ($students as $id_student) {
				$return['teacher_like_statuses'][$id_student] = TeacherReview::getStatus($id_student, $id_teacher, $id_subject, $year);
			}

			$return['bar'] = Freetime::getTeacherBar($id_teacher, true, $id_group);

			Group::updateById($id_group, [
				"id_teacher" => $id_teacher,
			]);

			returnJsonAng($return);
		}

		public function actionAjaxReloadSmsNotificationStatuses()
		{
			extract($_POST);
			$Group = Group::findById($id);
			foreach ($students as $id_student) {
				$return['sms_notification_statuses'][$id_student] = GroupSms::getStatus($id_student, $Group);
			}

			returnJsonAng($return);
		}

		public function actionAjaxReloadTests()
		{
			extract($_POST);

			foreach ($students as $id_student) {
				$return[$id_student] = TestStudent::getForGroup($id_student, $id_subject, $grade);
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
			GroupSchedule::updateById($id, compact('cabinet'));
		}

		public function actionAjaxChangeScheduleFree()
		{
			extract($_POST);

			GroupSchedule::updateById($id, [
				"is_free" => $is_free,
			]);
		}

		public function actionAjaxGet()
		{
			extract($_POST);

			returnJsonAng(
				Group::getData($page, $teachers)
			);
		}

		public function actionAjaxCheckFreeCabinets()
		{
			extract($_POST);

			returnJsonAng(
				Freetime::checkFreeCabinets($id_group, $year, $day_and_time)
			);
		}

		public function actionAjaxToggleReadyToStart()
		{
			extract($_POST);
			Group::updateById($id, compact('ready_to_start'));
		}
	}

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
		}

		public function actionJournal()
		{
			$id_group = $_GET['id'];

			$this->setRights([User::USER_TYPE, Teacher::USER_TYPE]);

            // has-access-refactored
            if (User::isTeacher()) {
                $this->hasAccess('groups', $id_group);
            }

			$this->setTabTitle("Посещаемость группы №" . $id_group);

			$Group = Group::findById($id_group, true);

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
				foreach (array_unique($teacher_ids) as $id) {
                    $Teachers[] = Teacher::getLight($id);
                }
			}

			$LessonData = VisitJournal::findAll([
				"condition" => "id_group=$id_group" //и преподы и студенты
			]);

            if (User::fromSession()->type == Teacher::USER_TYPE) {
                $ang_init_data = angInit([
                    "Group" 		=> $Group,
                    "LessonData"	=> $LessonData,
                    "Teachers"		=> $Teachers,
                ]);

                $this->_viewsFolder = 'journal';
                $this->render("teacher_journal", [
                    "ang_init_data" => $ang_init_data
                ]);
            } else {
                $ang_init_data = angInit([
                    "Group" 		=> $Group,
                    "LessonData"	=> $LessonData,
                    "Teachers"		=> $Teachers,
                ]);

                $this->render("journal", [
                    "ang_init_data" => $ang_init_data,
                ]);
            }
		}

        /**
         * @schedule-refactored
         */
		public function actionLesson()
		{
			$this->setRights([User::USER_TYPE, Teacher::USER_TYPE]);

			$id_schedule = $_GET['id_schedule'];;

            $Schedule = GroupSchedule::findById($id_schedule);

            /* третий парам чтобы не учитовать отменные занятия */
            // @refactored
			if (! $Schedule) {
				$this->setTabTitle("Ошибка");
				$this->render("no_lesson", [
					"message" => "Занятие отсутствует"
				]);
			} else {
				// если занятие еще не началось, нельзя переходить в функционал добавления в журнал
				$schedule_date = $Schedule->date . " " . $Schedule->time;

				if ($schedule_date > now()) {
					$this->setTabTitle("Ошибка");
					$this->render("no_lesson", [
						"message" => "Занятие еще не началось"
					]);
				} else {
					// если дошло досюда, всё хорошо, ошибок нет
					$this->_custom_panel = true;

                    $Schedule->getGroup();

					// has-access-refactored
                    if (User::isTeacher()) {
                        $this->hasAccess('groups', $Schedule->Group->id);
                    }

					// если занятие уже зарегистрировано, берем данные из журнала
					if ($Schedule->was_lesson) {
                        // @schedule-refactored
						$LessonData = VisitJournal::findAll([
							"condition" => "lesson_date='{$Schedule->date}' AND lesson_time='{$Schedule->time}:00' AND id_group={$Schedule->Group->id} AND type_entity='". Student::USER_TYPE ."'"
						]);

						$student_ids = [];
						foreach ($LessonData as $OneData) {
							$student_ids[] = $OneData->id_entity;
							$OrderedLessonData[$OneData->id_entity] = $OneData;
						}

						$Schedule->Group->Students = Student::findAll(["condition" => "id IN (". implode(",", $student_ids) .")"], true);
					} else {
						$Schedule->Group->Students = $Schedule->Group->getStudents();
					}

					$ang_init_data = angInit([
                        "Schedule"        => $Schedule,
						"LessonData"      => (object)$OrderedLessonData,
						"lesson_statuses" => VisitJournal::$statuses,
						"isAdmin"		  => User::isAdmin(),
					]);

					//изменение исторических данных: доступен только админам
					if (User::isAdmin()) {
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
				$Groups = Teacher::getGroups(User::fromSession()->id_entity, false);
                foreach ($Groups as &$Group) {
                    $counts = Group::getScheduleCountCachedStatic($Group->id);
                    $Group->schedule_count      = $counts['free'] + $counts['paid'];
                    $Group->first_schedule 		= Group::getFirstScheduleStatic($Group->id);
                    $Group->past_lesson_count 	= Group::getPastScheduleCountCachedStatic($Group->id);;

                }

				$ang_init_data = angInit([
					"Groups" 		=> $Groups,
					"Subjects" 		=> Subjects::$three_letters,
					"Grades"		=> Grades::$all,
					"GroupLevels"	=> GroupLevels::$short,
					"Branches"		=> Branches::getAll(),
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
                    $counts = Group::getScheduleCountCachedStatic($Group->id);
                    $Group->schedule_count      = $counts['free'] + $counts['paid'];
                    $Group->first_schedule 		= Group::getFirstScheduleStatic($Group->id);
                    $Group->past_lesson_count 	= Group::getPastScheduleCountCachedStatic($Group->id);;
                }

				$ang_init_data = angInit([
					"Groups" 		=> $Groups,
					"Subjects" 		=> Subjects::$three_letters,
					"Grades"		=> Grades::$all,
					"GroupLevels"	=> GroupLevels::$short,
					"Branches"		=> Branches::getAll(),
				]);

				$this->render("list_for_students", [
					"Groups" 		=> $Groups,
					"ang_init_data" => $ang_init_data
				]);
			} else {
				// не надо панель рисовать
				$this->_custom_panel = true;
				$this->addJs("dnd");

				$Teachers = Teacher::getLightArray(Group::getTeacherIds());

				$ang_init_data = angInit([
					"Cabinets"		=> Cabinet::getByBranch(1),
					"Branches"		=> Branches::getAll(),
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
            // @cancelled_lesson_dates – удалить везде
            // @past_lessons – рассмотреть удаление у teachers & students
            $this->addJs("vendor/angular-bootstrap-calendar-tpls, ng-schedule-app");
			if (User::fromSession()->type == Student::USER_TYPE) {
                $id_group = $_GET['id'];

                // has-access-refactored
                $this->hasAccess('groups', $id_group, 'students', true);

				// не надо панель рисовать
				$this->_custom_panel = true;
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
                $exams = ExamDay::getExamDates($Group);
				$ang_init_data = angInit([
					"Group" 				=> $Group,
					"Teacher"				=> $Teacher,
					"SubjectsDative"		=> Subjects::$dative,
					"past_lessons" 	        => $Group->getPastLessons(), // @time-refactored @time-checked
                    "all_cabinets"			=> Branches::allCabinets(), // @to show past lesson cabinet number
                    "special_dates"	=> [
                        'vacations' => GroupSchedule::getVocationDates(),
                        'exams' => $exams['this_subject'],
                        'other_exams' => $exams['other_subject'],
                    ],
				]);

				$this->render("student_schedule", [
					"Group"			=> $Group,
					"ang_init_data" => $ang_init_data,
				]);
			} else
				if (User::fromSession()->type == Teacher::USER_TYPE) {
					$id_group = $_GET['id'];
					$Group = Group::findById($id_group);


					// has-access-refactored
					$this->hasAccess('groups', $id_group);

                    // не надо панель рисовать
					$this->_custom_panel = true;

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

                    $exams = ExamDay::getExamDates($Group);
					$ang_init_data = angInit([
						"Group" 				=> $Group,
						"Teacher"				=> $Teacher,
						"SubjectsDative"		=> Subjects::$dative,
						"past_lessons" 			=> $Group->getPastLessons(), // @time-refactored @time-checked
                        "all_cabinets"			=> Branches::allCabinets(), // @to show past lesson cabinet number
                        "special_dates"	=> [
                            'vacations' => GroupSchedule::getVocationDates(),
                            'exams' => $exams['this_subject'],
                            'other_exams' => $exams['other_subject'],
                        ],
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
                    $exams = ExamDay::getExamDates($Group);

					$ang_init_data = angInit([
						"Group" 			=> $Group,
						"past_lessons" => $Group->getPastLessons(), 		// @time-refactored @time-checked
						"special_dates"	=> [
                            'vacations' => GroupSchedule::getVocationDates(),
                            'exams' => $exams['this_subject'],
                            'other_exams' => $exams['other_subject'],
                        ],
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
			// не надо панель рисовать
			$this->_custom_panel = true;

			$this->addJs("//maps.google.ru/maps/api/js?key=AIzaSyAXXZZwXMG5yNxFHN7yR4GYJgSe9cKKl7o&libraries=places&language=ru", true);
            $this->addJs('maps.controller');

			if (!$Group) {
				$this->addJs("dnd");
				$Group = Group::findById($_GET['id']);
			}

			if (! LOCAL_DEVELOPMENT) {
                $Teachers = Teacher::findAll(["select" => ['id', 'last_name', 'first_name', 'subjects', 'middle_name']], true);

				if ($Group->id_teacher) {
					foreach ($Teachers as &$Teacher) {
						if ($Teacher->id == $Group->id_teacher) {
							$Teacher->bar = $Teacher->getBar();
						}
					}
				}
			}

			$Students = [];
			foreach ($Group->students as $id_student) {
				$Student = Student::findById($id_student, true);
				$Student->Contract 	= $Student->getLastContract($Group->year, true);

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
				$Student->markers 				= $Student->getMarkers();

				if (array_key_exists($Student->id, $Group->student_statuses)) {
					$Student->id_status		= $Group->student_statuses[$Student->id]['id_status'];
					$Student->notified		= $Group->student_statuses[$Student->id]['notified'];
					$Student->review_status	= $Group->student_statuses[$Student->id]['review_status'];
				}
				$Students[] = $Student;
			}

			$Group->bar = Freetime::getGroupBar($Group->id);

			$ang_init_data = angInit([
				"Group" 	     => $Group,
				"Branches" 	     => Branches::getAll('*'),
				"Teachers"	     => $Teachers,
				"TmpStudents"    => $Students,
				"Subjects"	     => Subjects::$three_letters,
				"GroupLevels"    => GroupLevels::$all,
				"subjects_short" => Subjects::$short,
				"duration"		 => Group::DURATION,
				"all_cabinets"	 => Branches::allCabinets(),
				"branches_brick" => Branches::getShortColored(),
				"cabinet_bars"	 => Freetime::getCabinetBar($Group),
				"time"			 => Time::get(),
				"time_imcomp"	 => Time::INCOMPABILITY_MAP,
				"weekdays"		 => Time::WEEKDAYS,
				"free_cabinets"  => Freetime::checkFreeCabinets($Group->id, $Group->year, $Group->day_and_time),
                "FirstLesson"    => Group::getFirstLesson($Group->id),
                "user"			 => User::fromSession()->dbData()
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

            // @schedule-refactored
			# Удаляем всё, что связано с группой
			GroupTime::deleteAll($condition);
			GroupSchedule::deleteAll($condition);
			GroupNote::deleteAll($condition);
		}


		public function actionAjaxSaveSchedule()
		{
			extract($_POST);

            if (isset($id)) {
                GroupSchedule::updateById($id, $_POST);
            } else {
                returnJsonAng(
                    GroupSchedule::add($_POST)
                );
            }
		}

        // @schedule-refactored
		public function actionAjaxDeleteSchedule()
		{
			extract($_POST);

			GroupSchedule::deleteAll([
				"condition" => "id={$id}"
			]);
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

		public function actionAjaxGetGroupBar()
		{
			extract($_POST);

			returnJsonAng(
				Freetime::getGroupBar($id_group)
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

			VisitJournal::addData($id_schedule, $data);

			// Обновляем красные счетчики
			if (! LOCAL_DEVELOPMENT) {
                $Schedule = VisitJournal::findById($id_schedule);
				$errors = memcached()->get("JournalErrors");

				if (($key = array_search($Schedule->id_group, $errors[$Schedule->date])) !== false) {
					unset($errors[$Schedule->date][$key]);
					$errors[$Schedule->date] = array_values($errors[$Schedule->date]);
					// if no errors now
					if (!count($errors[$Schedule->date])) {
						unset($errors[$Schedule->date]);
					}
				    memcached()->set("JournalErrors", $errors, 3600 * 24);
				}
			}
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
				VisitJournal::updateData($id_schedule, $data);

				// Обновляем красные счетчики
				if (! LOCAL_DEVELOPMENT) {
                    $Schedule = VisitJournal::findById($id_schedule);
					$errors = memcached()->get("JournalErrors");

					if (($key = array_search($Schedule->id_group, $errors[$Schedule->date])) !== false) {
						unset($errors[$Schedule->date][$key]);
						$errors[$Schedule->date] = array_values($errors[$Schedule->date]);
						// if no errors now
						if (!count($errors[$Schedule->date])) {
							unset($errors[$Schedule->date]);
						}
						memcached()->set("JournalErrors", $errors, 3600 * 24);
					}
				}
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

			// @refactored @schedule-refactored
			memcached()->set("GroupScheduleCount[{$Group->id}]", $return, 5 * 24 * 3600);
		}

		public function actionAjaxUpdateCacheAll()
		{
			$Groups = Group::findAll();

			// @refactored @schedule-refactored
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

			// @time-refactored @time-checked
			// @sms-checked
			$Template = Template::getFull(8, [
				"student_name"	=> $Student->last_name . " " . $Student->first_name,
				"subject"		=> Subjects::$dative[$Group->id_subject],
				"address"		=> Branches::getField(Cabinet::getField($FirstLesson->cabinet), 'address'),
				"branch"		=> Branches::getField(Cabinet::getField($FirstLesson->cabinet), 'full'),
				"date"			=> $date_formatted,
				"cabinet"		=> trim(Cabinet::getField($FirstLesson->cabinet, 'number')),
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
            $this->setRights([User::USER_TYPE]);

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
				"condition" => "id_branch=1",
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
							WHERE g.ended=0 AND gt.id_time = {$id_time} AND gt.id_cabinet = {$Cabinet->id} AND g.year=" . Years::getAcademic());
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

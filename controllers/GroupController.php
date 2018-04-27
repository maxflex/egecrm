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
			$this->_viewsFolder = 'journal';
			$id_group = $_GET['id'];

			$this->setRights([User::USER_TYPE, Teacher::USER_TYPE]);

            // has-access-refactored
            if (User::isTeacher()) {
                $this->hasAccess('groups', $id_group);
            }

			$this->setTabTitle("Посещаемость группы №" . $id_group);

			$student_count = dbConnection()->query("
				SELECT COUNT(*) AS cnt FROM visit_journal
				WHERE id_group=$id_group AND type_entity='STUDENT'
			")->fetch_object()->cnt;

			// если пустой журнал
			if (! $student_count) {
				$this->render("empty");
				return;
			}

			$ang_init_data = angInit(compact('id_group'));

            if (User::fromSession()->type == Teacher::USER_TYPE) {
                $this->render("teacher", compact('ang_init_data'));
            } else {
                $this->render("user", compact('ang_init_data'));
            }
		}

		public function actionYearSchedule()
		{
			$this->setRights([Student::USER_TYPE]);

			$this->setTabTitle("Расписание и отчеты");

			$Schedule = Student::getFullSchedule(User::fromSession()->id_entity, true);

			$ang_init_data = angInit([
				"Subjects"	=> Subjects::$three_letters,
				"Lessons"	=> $Schedule->Lessons,
				"lesson_statuses" => VisitJournal::$statuses,
				"all_cabinets" =>  Branches::allCabinets(),
				"months" => Months::get(),
				"lesson_years" => $Schedule->years,
				"selected_lesson_year" => end($Schedule->years)
			]);


			$this->render("year_schedule", [
				"ang_init_data" => $ang_init_data,
			]);
		}

		public function actionLesson()
		{
			$this->setRights([User::USER_TYPE, Teacher::USER_TYPE]);

            $Lesson = VisitJournal::findById($_GET['id']);

            /* третий парам чтобы не учитовать отменные занятия */
            // @refactored
			if (! $Lesson) {
				$this->setTabTitle("Ошибка");
				$this->render("no_lesson", [
					"message" => "Занятие отсутствует"
				]);
			} else {
				// если занятие еще не началось, нельзя переходить в функционал добавления в журнал
				if ($Lesson->date_time > now()) {
					$this->setTabTitle("Ошибка");
					$this->render("no_lesson", [
						"message" => "Занятие еще не началось"
					]);
				} else {
					// если дошло досюда, всё хорошо, ошибок нет
					$this->_custom_panel = true;

					// has-access-refactored
                    if (User::isTeacher()) {
                        $this->hasAccess('groups', $Lesson->id_group);
                    }

					// если занятие уже зарегистрировано, берем данные из журнала
					if ($Lesson->is_conducted) {
                        // @schedule-refactored
						$LessonData = VisitJournal::findAll([
							"condition" => "entry_id='{$Lesson->entry_id}' AND type_entity='". Student::USER_TYPE ."'"
						]);

						$student_ids = [];
						foreach ($LessonData as $OneData) {
							$student_ids[] = $OneData->id_entity;
							$OrderedLessonData[$OneData->id_entity] = $OneData;
						}
					} else {
						// если занятие еще не началось, берем учеников из настроек группы
						$Group = Group::findById($Lesson->id_group);
						$student_ids = $Group->students;
					}

					$Students = Student::findAll(["condition" => "id IN (". implode(",", $student_ids) .")"], true);

					// получаем учеников, которые присутствовали в группе, но сейчас их по какой-то причине нет
					// (перешли в другую группу или прекратили обучение)
					$left_students_vj = VisitJournal::findAll([
						'condition' => "id_group={$Lesson->id_group} AND type_entity='". Student::USER_TYPE ."' AND id_entity NOT IN (" . implode(',', $student_ids) . ")",
						'group' => 'id_entity'
					]);

					$left_students = [];
					if ($left_students_vj) {
						if ($Lesson->is_conducted) {
							$year = $Lesson->year;
							$id_subject = $Lesson->id_subject;
						} else {
							$year = $Group->year;
							$id_subject = $Group->id_subject;
						}
						foreach($left_students_vj as $s) {
							$student = Student::getLight($s->id_entity);
							$student->id = $s->id_entity;
							$student->status = VisitJournal::count([
								'condition' => "id_group!={$Lesson->id_group} AND type_entity='". Student::USER_TYPE ."' AND id_entity={$s->id_entity} AND year={$year}  AND id_subject={$id_subject}"
							]);
							$left_students[] = $student;
						}
					}

					$ang_init_data = angInit([
						"Students"		  => $Students,
                        "Lesson"          => $Lesson,
						"LessonData"      => (object)$OrderedLessonData,
						"lesson_statuses" => VisitJournal::$statuses,
						"isAdmin"		  => User::isAdmin() ? 1 : 0,
						"left_students"   => $left_students,
						'Teacher'		  => Teacher::getLight(User::fromSession()->id_entity)
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

				$ang_init_data = angInit([
					"Groups" 		=> $Groups,
					"Subjects" 		=> Subjects::$three_letters,
					"Grades"		=> Grades::$all,
					"GroupLevels"	=> GroupLevels::$short,
					"Branches"		=> Branches::getAll(),
					"all_cabinets"	=> Branches::allCabinets(), // @to show past lesson cabinet number
					"AdditionalLessons" => AdditionalLesson::getByEntity(Teacher::USER_TYPE, User::fromSession()->id_entity),
					"TeacherAdditionalPayments" => TeacherAdditionalPayment::get(User::fromSession()->id_entity),
				]);

				$this->render("list_for_teachers", [
					"Groups" 		=> $Groups,
					"ang_init_data" => $ang_init_data
				]);

			} else
			if (User::fromSession()->type == Student::USER_TYPE) {
				$this->setTabTitle("Мои группы");
				$Groups = Student::groups(User::fromSession()->id_entity);

				$ang_init_data = angInit([
					"Groups" 		=> $Groups,
					"Subjects" 		=> Subjects::$three_letters,
					"Grades"		=> Grades::$all,
					"GroupLevels"	=> GroupLevels::$short,
					"Branches"		=> Branches::getAll(),
					"all_cabinets"	=> Branches::allCabinets(), // @to show past lesson cabinet number
					"AdditionalLessons" => AdditionalLesson::getByEntity(Student::USER_TYPE, User::fromSession()->id_entity)
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

		public function actionSchedule()
		{
            // @cancelled_lesson_dates – удалить везде
            // @past_lessons – рассмотреть удаление у teachers & students
            $this->addJs("vendor/angular-bootstrap-calendar-tpls, ng-schedule-app");
			$id_group = $_GET['id'];
			$Group = Group::findById($id_group);

			if ($Group->is_unplanned) {
				$this->renderRestricted('Группа не найдена');
			}

			if (User::fromSession()->type == Student::USER_TYPE) {
                // has-access-refactored
                $this->hasAccess('groups', $id_group, 'students', true);

				// не надо панель рисовать
				$this->_custom_panel = true;

				$Teacher = Teacher::findById($Group->id_teacher);

				if (! $Teacher) {
					$Teacher = 0;
				}

                $exams = ExamDay::getExamDates($Group);
				$ang_init_data = angInit([
					"Lessons"				=> VisitJournal::getGroupLessons($id_group),
					"Group" 				=> $Group,
					"Teacher"				=> $Teacher,
					"SubjectsDative"		=> Subjects::$dative,
                    "all_cabinets"			=> Branches::allCabinets(), // @to show past lesson cabinet number
                    "special_dates"	=> [
                        'vacations' => Vacation::getDates($Group->year),
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
					// has-access-refactored
					$this->hasAccess('groups', $id_group);

                    // не надо панель рисовать
					$this->_custom_panel = true;

                    $Group->Students = [];
                    foreach ($Group->students as $id_student) {
                        $Student = Student::getLight($id_student);
                        if ($Group->grade && $Group->id_subject) {
                            $Student->Test = TestStudent::getForGroup($id_student, $Group->id_subject, $Group->grade);
                        }
                        $Group->Students[] = $Student;
                    }

					$Teacher = Teacher::findById($Group->id_teacher);

					if (! $Teacher) {
						$Teacher = 0;
					}

                    $exams = ExamDay::getExamDates($Group);
					$ang_init_data = angInit([
						"Lessons"				=> VisitJournal::getGroupLessons($id_group),
						"Group" 				=> $Group,
						"Teacher"				=> $Teacher,
						"SubjectsDative"		=> Subjects::$dative,
                        "all_cabinets"			=> Branches::allCabinets(), // @to show past lesson cabinet number
                        "special_dates"	=> [
                            'vacations' => Vacation::getDates($Group->year),
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

                    $exams = ExamDay::getExamDates($Group);

					$ang_init_data = angInit([
						"Group" 			=> $Group,
						"Lessons" => VisitJournal::getGroupLessons($id_group),
						"special_dates"		=> [
                            'vacations' 	=> Vacation::getDates($Group->year),
                            'exams' 		=> $exams['this_subject'],
                            'other_exams' 	=> $exams['other_subject'],
                        ],
						"all_cabinets"		=> Branches::allCabinets(), // @time-refactored @time-checked
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

			if ($Group->is_unplanned) {
				$this->renderRestricted('Группа не найдена');
			}

			$Group->bar = Freetime::getGroupBar($Group->id);

			$ang_init_data = angInit([
				"Group" 	     => $Group,
				"Grades"		=> Grades::$all,
				"time"			 => Time::get(),
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
		}


		public function actionAjaxSaveLesson()
		{
			extract($_POST);

			if (isset($id)) {
                $response = VisitJournal::updateById($id, $_POST);
            } else {
                $response = VisitJournal::add($_POST);
            }
			returnJsonAng($response);
		}

		public function actionAjaxDeleteLesson()
		{
			extract($_POST);

			VisitJournal::deleteById($id);
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

		public function actionAjaxRegisterInJournal()
		{
			extract($_POST);

			// Дополнительный вход
			User::rememberMeLogin();
			$data = array_filter($data);

			VisitJournal::addData($id_lesson, $data);
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
				if ($Cabinet->isDeleted()) {
					continue;
				}
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
				Group::getData($search)
			);
		}

		public function actionAjaxCheckFreeCabinets()
		{
			extract($_POST);

			returnJsonAng(
				Freetime::checkFreeCabinets($id_group, $year, $day_and_time)
			);
		}

		public function actionAjaxToggleBoolean()
		{
			extract($_POST);
			Group::updateById($id, [
				$field => $value
			]);
		}

		public function actionAjaxSaveEditedStudent()
		{
			VisitJournal::updateById($_POST['id'], $_POST);
		}

		public function actionAjaxGetEditData()
		{
			extract($_POST);

			$Group = Group::findById($id);

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

			returnJsonAng([
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
				"time_imcomp"	 => Time::INCOMPABILITY_MAP,
				"weekdays"		 => Time::WEEKDAYS,
				"free_cabinets"  => Freetime::checkFreeCabinets($Group->id, $Group->year, $Group->day_and_time),
                "FirstLesson"    => Group::getFirstLesson($Group->id),
                "user"			 => User::fromSession()->dbData()
			]);
		}
	}

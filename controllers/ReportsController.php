<?php
	// Контроллер отчетов
	class ReportsController extends Controller
	{
		public $defaultAction = "add";

		public static $allowed_users = [User::USER_TYPE, Teacher::USER_TYPE, Student::USER_TYPE];

		// Папка вьюх
		protected $_viewsFolder	= "report";

		public function beforeAction()
		{
			$this->addJs("ng-reports-app");
		}

		public function actionView()
		{
            $id_report = $_GET['id'];

            // has-access-refactored
            if (User::isTeacher() || User::isStudent()) {
                $this->hasAccess('reports', $id_report);
            }

			$Report				= Report::findById($id_report);
			$Report->Student	= Student::findById($Report->id_student);
			$Report->Teacher	= Teacher::findById($Report->id_teacher);

            $this->_custom_panel = true;

			$ang_init_data = angInit([
				"Report" 		=> $Report,
				"Subjects"		=> Subjects::$dative,
			]);

			$this->render("view", [
				'ang_init_data' => $ang_init_data,
			]);
		}

		public function actionList()
		{
			if (User::fromSession()->type == Teacher::USER_TYPE) {
				$this->_teacherList();
			}
			if (User::fromSession()->type == User::USER_TYPE) {
				$this->_userList();
			}
		}

		private function _teacherList()
		{
			$this->_custom_panel = true;

			$ang_init_data = angInit([
				'data'	=> $data,
				'year' => academicYear(),
				'Subjects' 	=> Subjects::$three_letters,
			]);

			$this->render("teacher_list", [
				'ang_init_data' => $ang_init_data,
                'year'          => $year,
			]);
		}

		private function _userList()
		{
			$this->_custom_panel = true;

			$ang_init_data = angInit([
				'Subjects' 		=> Subjects::$all,
				'Teachers'		=> Teacher::getJournalTeachers(),
				'Grades'		=> Grades::$all,
				"grades_short"  => Grades::$short,
				'three_letters' => Subjects::$three_letters,
				'reports_updated' => Settings::get('reports_updated'),
				'currentPage'	=> $_GET['page'] ? $_GET['page'] : 1,
			]);

			$this->render("user_list", [
				"ang_init_data" => $ang_init_data,
			]);
		}

		public function actionStudent()
		{
            // has-access-refactored
            $this->setRights([Teacher::USER_TYPE]);

            $id_student = $_GET['id_student'];
            $id_subject = $_GET['id_subject'];
            $id_teacher = User::fromSession()->id_entity;

            if (! static::lessonExists($id_teacher, $id_student, $id_subject)) {
                $this->renderRestricted();
            }

			$Student = Student::findById($id_student);
			$Visits = $Student->getVisits(compact('id_teacher', 'id_subject'));
            $Reports = Report::get($id_student, $id_teacher, $id_subject);

            $Group = Group::find([
                "condition" => "FIND_IN_SET($id_student, students) AND id_subject={$id_subject}
                    AND ended=0 AND id_teacher={$id_teacher}"
            ]);

			$years = [];
			foreach ($Visits as $Visit) {
                $Visit->cabinet_number = Cabinet::getField($Visit->cabinet, 'number');
				if (! in_array($Visit->year, $years)) {
					$years[] = $Visit->year;
				}
            }

            // ВНИМАНИЕ: ДОБАВЛЯЕМ ОТЧЕТЫ В МАССИВ visits!
            foreach ($Reports as $Report) {
                // внимание!
				$Report->is_report = true;
                $Report->lesson_date = $Report->date_original;
				$Report->label = sprintf("отчет по %s", Subjects::$dative[$Report->id_subject]);
				$Report->date_time = sprintf("%s 00:00:00", $row->date);
                $Visits[] = $Report;
				if (! in_array($Report->year, $years)) {
					$years[] = $Report->year;
				}
            }

            // Sort visits by SO CALLED lesson_date
            usort($Visits, function($a, $b) {
                return $a->lesson_date > $b->lesson_date;
            });

			$LessonsByMonth = [];
			foreach($Visits as $Visit) {
				$LessonsByMonth[$Visit->year][date('n', strtotime($Visit->lesson_date))][] = $Visit;
			}

			$PlannedLessons = $Group ? $Group->getPlannedLessons() : false;
			if ($PlannedLessons) {
				$PlannedLessonsByMonth = [];
				foreach($PlannedLessons as $PlannedLesson) {
					$PlannedLessonsByMonth[$PlannedLesson->year][date('n', strtotime($PlannedLesson->lesson_date))][] = $PlannedLesson;
				}
			}

			$ang_init_data = angInit([
				'Student'         => $Student,
                'Lessons'          => $LessonsByMonth,
                'PlannedLessons'   => $PlannedLessons ? $PlannedLessonsByMonth : false,
                'id_group'        => $Group ? $Group->id : 0,
				"all_cabinets" =>  Branches::allCabinets(),
				"years"	=> $years,
				"months" => Months::get(),
				"Subjects"	=> Subjects::$three_letters,
                'Subject'         => [
                    'id'            => $id_subject,
                    'three_letters' => Subjects::$three_letters[$id_subject],
                    'dative'        => Subjects::$dative[$id_subject]
                ]
			]);

			$this->setTabTitle('Добавление отчета');

			$this->render('add_student', [
				'ang_init_data'   => $ang_init_data,
                'report_required' => Report::required($id_student, $id_teacher, $id_subject, academicYear()),
			]);
		}

		public function actionEdit()
		{
			$this->setRights([User::USER_TYPE, Teacher::USER_TYPE]);

            $id_report = $_GET['id'];

            // has-access-refactored
            if (User::isTeacher()) {
                $this->hasAccess('reports', $id_report);
            }

			$Report = Report::findById($id_report);

			$this->actionAdd($Report);
		}

		public function actionAdd($Report = false)
		{
            $this->setRights([User::USER_TYPE, Teacher::USER_TYPE]);

			$this->_custom_panel = true;

			if ($Report) {
				$Report->Student = Student::findById($Report->id_student);
				$Report->Teacher = Teacher::findById($Report->id_teacher);
			} else {
				$id_student = $_GET["id_student"];
				$id_subject = $_GET["id_subject"];

                if (! static::lessonExists(User::fromSession()->id_entity, $id_student, $id_subject)) {
                    $this->renderRestricted();
                }

				$Report = new Report([
					"id_student" => $id_student,
					"id_subject" => $id_subject,
					"id_teacher" => User::fromSession()->id_entity,
					"date"       => now(true),
				]);

				$Report->Student = Student::findById($id_student);
				$Report->Teacher = Teacher::findById(User::fromSession()->id_entity);
			}

			$ang_init_data = angInit([
				"Report" 	=> $Report,
				"Subjects"	=> Subjects::$dative,
				"SubjectsFull" => Subjects::$full,
			]);

			$this->render("add", [
				'ang_init_data' => $ang_init_data,
			]);
		}

		public function actionAjaxAdd()
		{
			extract($_POST);

			$NewReport = Report::add($Report);
            returnJsonAng([]);
		}

		public function actionAjaxEdit()
		{
			extract($_POST);

			Report::updateById($Report['id'], $Report);

			preType($Report);
		}

		public function actionAjaxDelete()
		{
			extract($_POST);

			Report::deleteById($id_report);
		}


		public function actionAjaxGetReports()
		{
			extract($_POST);

			$data = Teacher::getReportData($page, $teachers);

			returnJsonAng($data);
		}

		public function actionAjaxForceNoreport()
		{
			extract($_POST);
			ReportForce::toggle($id_student, $id_teacher, $id_subject, $year);
		}

		public function actionAjaxRecalcHelper()
		{
			returnJsonAng([
				'date' 		=> ReportHelper::recalc(),
				'red_count'	=> Teacher::redReportCountAll()
			]);
		}

		public function actionAjaxLoadByYear()
		{
			extract($_POST);
			$id_teacher = User::fromSession()->id_entity;

            $data = ReportHelper::findAll([
                'condition' => "year={$year} AND id_teacher={$id_teacher}",
                'group' => 'id_student, id_subject, id_teacher, year'
            ]);

            foreach($data as $d) {
                $d->Student = Student::getLight($d->id_student);
                $d->lessons_count   = Report::getLessonsCount($d->id_student, $id_teacher, $d->id_subject, $year);
                $d->reports_count   = Report::getCount($d->id_student, $id_teacher, $d->id_subject, $year);
                $d->report_required = Report::required($d->id_student, $id_teacher, $d->id_subject, $year);

                $result = dbConnection()->query(
					"select id, grade from groups where FIND_IN_SET({$d->id_student}, students) AND id_subject={$d->id_subject}
                        AND ended=0 AND id_teacher={$id_teacher}"
				);
				if ($result->num_rows) {
					$d->group = $result->fetch_object();
					Group::assignGrade($d->group);
				}
            }

			returnJsonAng($data);
		}

        private static function lessonExists($id_teacher, $id_student, $id_subject)
        {
            return VisitJournal::count([
                'condition' => "id_entity={$id_student} AND type_entity='STUDENT' AND id_teacher={$id_teacher} AND id_subject={$id_subject}",
            ]);
        }
	}

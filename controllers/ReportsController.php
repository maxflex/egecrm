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
				$this->_teacherList();			}
			if (User::fromSession()->type == Student::USER_TYPE) {
				$this->_studentList();
			}
			if (User::fromSession()->type == User::USER_TYPE) {
				$this->_userList();
			}
		}

		private function _teacherList()
		{
            $year = isset($_GET['year']) ? $_GET['year'] : academicYear();
            $id_teacher = User::fromSession()->id_entity;
			$this->_custom_panel = true;

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
            // dd($data);

			$ang_init_data = angInit([
				'data'	=> $data,
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
				'three_letters' => Subjects::$three_letters,
				'reports_updated' => Settings::get('reports_updated'),
				'currentPage'	=> $_GET['page'] ? $_GET['page'] : 1,
			]);

			$this->render("user_list", [
				"ang_init_data" => $ang_init_data,
			]);
		}

		private function _studentList()
		{
            $this->_custom_panel = true;
            $year = isset($_GET['year']) ? $_GET['year'] : academicYear();
            $id_student = User::fromSession()->id_entity;

            $data = ReportHelper::findAll([
                'condition' => "year={$year} AND id_student={$id_student}",
                'group' => 'id_student, id_subject, id_teacher, year'
            ]);

			foreach($data as $d) {
				$d->Teacher = Teacher::getLight($d->id_teacher);
                $d->lessons_count   = Report::getLessonsCount($id_student, $d->id_teacher, $d->id_subject, $year);
                $d->reports_count   = Report::getCount($id_student, $d->id_teacher, $d->id_subject, $year, true);
                $d->Group = Group::find([
                    "condition" => "FIND_IN_SET({$id_student}, students) AND id_subject={$d->id_subject}
                        AND ended=0 AND id_teacher={$d->id_teacher}"
                ]);
			}

			$ang_init_data = angInit([
				'data'	=> $data,
				'Subjects' 	=> Subjects::$three_letters,
			]);

			$this->render("student_list", [
				'ang_init_data' => $ang_init_data,
                'year'          => $year,
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

			$Student = Student::findById($id_student, true);
			$Visits = $Student->getVisits(compact('id_teacher', 'id_subject'));
            $Reports = Report::get($id_student, $id_teacher, $id_subject);

            $Group = Group::find([
                "condition" => "FIND_IN_SET($id_student, students) AND id_subject={$id_subject}
                    AND ended=0 AND id_teacher={$id_teacher}"
            ]);

			foreach ($Visits as $Visit) {
                $Visit->cabinet_number = Cabinet::getField($Visit->cabinet, 'number');
            }

            // ВНИМАНИЕ: ДОБАВЛЯЕМ ОТЧЕТЫ В МАССИВ visits!
            foreach ($Reports as $Report) {
                // внимание!
                $Report->lesson_date = date("Y-m-d", strtotime($Report->date));
                $Visits[] = $Report;
            }

            // Sort visits by SO CALLED lesson_date
            usort($Visits, function($a, $b) {
                return $a->lesson_date > $b->lesson_date;
            });

			$ang_init_data = angInit([
				'Student'         => $Student,
                'Visits'          => $Visits,
                'PlannedLessons'   => $Group ? $Group->getPlannedLessons() : false,
                'id_group'        => $Group ? $Group->id : 0,
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

		public function actionTeacher()
		{
            // has-access-refactored
            $this->setRights([Student::USER_TYPE]);

			$id_student = User::fromSession()->id_entity;
            $id_subject = $_GET['id_subject'];
            $id_teacher = $_GET['id_teacher'];

            if (! static::lessonExists($id_teacher, $id_student, $id_subject)) {
                $this->renderRestricted();
            }

			$Student = Student::findById($id_student, true);
			$Teacher = Teacher::findById($id_teacher, true);
			$Visits = $Student->getVisits(compact('id_teacher', 'id_subject'));
            $Reports = Report::get($id_student, $id_teacher, $id_subject, [
	            'available_for_parents' => 1
            ]);

            $Group = Group::find([
                "condition" => "FIND_IN_SET($id_student, students) AND id_subject={$id_subject}
                    AND ended=0 AND id_teacher={$id_teacher}"
            ]);

			foreach ($Visits as $Visit) {
                $Visit->cabinet_number = Cabinet::getField($Visit->cabinet, 'number');
            }

            // ВНИМАНИЕ: ДОБАВЛЯЕМ ОТЧЕТЫ В МАССИВ visits!
            foreach ($Reports as $Report) {
                // внимание!
                $Report->lesson_date = date("Y-m-d", strtotime($Report->date));
                $Visits[] = $Report;
            }

            // Sort visits by SO CALLED lesson_date
            usort($Visits, function($a, $b) {
                return $a->lesson_date > $b->lesson_date;
            });

			$ang_init_data = angInit([
				'Student'         => $Student,
				'Teacher'         => $Teacher,
                'Visits'          => $Visits,
                'PlannedLessons'   => $Group ? $Group->getPlannedLessons() : false,
                'id_group'        => $Group ? $Group->id : 0,
                'AllSubjects'     => Subjects::$dative,
                'Subject'         => [
                    'id'            => $id_subject,
                    'three_letters' => Subjects::$three_letters[$id_subject],
                    'dative'        => Subjects::$dative[$id_subject]
                ]
			]);

			$this->setTabTitle('Отчетность');

			$this->render('teacher', [
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
					"date"       => date('d.m.Y'),
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

        private static function lessonExists($id_teacher, $id_student, $id_subject)
        {
            return VisitJournal::count([
                'condition' => "id_entity={$id_student} AND type_entity='STUDENT' AND id_teacher={$id_teacher} AND id_subject={$id_subject}",
            ]);
        }
	}

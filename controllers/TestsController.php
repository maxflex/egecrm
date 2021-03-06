<?php

	// Контроллер
	class TestsController extends Controller
	{
		public $defaultAction = "list";

		public static $allowed_users = [Admin::USER_TYPE, Teacher::USER_TYPE, Student::USER_TYPE];

		// Папка вьюх
		protected $_viewsFolder	= "tests";

		public function beforeAction()
		{
			$this->addJs("ng-tests-app");
			// $this->addCss('ng-autocomplete, bootstrap-select');
		}


		public function getCounters()
		{
			return [
				'tests' => Test::count(),
				'students' => [
					'all' 		  => TestStudent::count(),
					'not_started' => TestStudent::countNotStarted(),
					'finished' 	  => TestStudent::countFinished(),
					'in_process'  => TestStudent::countInProcess(),
				]
			];
		}
		public function actionList()
		{
            // не надо панель рисовать
			$this->_custom_panel = true;

			$ang_init_data = angInit([
				"Tests" 	=> Test::getLightAll(),
			]);

			$this->render("list", [
				'ang_init_data' => $ang_init_data,
			]);
		}

		public function actionStudentTests()
		{
			$this->_custom_panel = true;
			$ang_init_data = angInit([
				'current_page'	=> $_GET['page'] ? $_GET['page'] : 1,
                "Grades"    => Grades::$all,
                "Subjects"  => Subjects::$all,
                "TestStates"  => TestStates::$all,
				"correct_answers" => TestProblem::getCorrectAnswers()
			]);

			$this->render('student_tests', [
				'ang_init_data' => $ang_init_data,
			]);
		}

		public function actionStudentList()
		{
			// не надо панель рисовать
			$this->_custom_panel = true;

			$Tests = TestStudent::findAll([
				"condition" => "id_student=" . User::id(),
			]);

			foreach($Tests as &$Test) {
				$Test->Test = Test::findById($Test->id_test);
			}

			$ang_init_data = angInit([
				"Tests"     => $Tests,
                "Grades"    => Grades::$all,
                "Subjects"  => Subjects::$all,
                "TestStates"  => TestStates::$all,
			]);

			$this->render("student_list", [
				'ang_init_data' => $ang_init_data,
			]);
		}

		public function actionStart()
		{
			$id = $_GET['id'];

			// не надо панель рисовать
			$this->_custom_panel = true;
			$this->addCss('tests');

			$Test = Test::findById($id);
			$Test->Problems = TestProblem::findByTest($Test->id);

			$StudentTest = TestStudent::find([
				"condition" => "id_student=" . User::id() . " AND id_test={$id}",
			]);

			$data = [
				"answers" => (object)$StudentTest->answers,
				"server_answers" => (object)$StudentTest->answers,
				"Test" => $Test,
			];

			if ($StudentTest->finished()) {
				$data['final_score'] = $StudentTest->finalScoreString();
			}
			else if (! $StudentTest->inProgress()) {
				$StudentTest->start();
			}

			$data['time'] = (strtotime($StudentTest->date_start) + ($Test->minutes * 60))  - time();

			$ang_init_data = angInit($data);

			$this->render("test", [
				'ang_init_data' => $ang_init_data,
			]);
		}

		public function actionIntro()
		{
			$id = $_GET['id'];

			// не надо панель рисовать
			$this->_custom_panel = true;
			$this->addCss('tests');

			$Test = Test::findById($id);

			$ang_init_data = angInit(compact("Test"));

			$this->render("intro", [
				'ang_init_data' => $ang_init_data,
			]);
		}


		public function actionEdit()
		{
			$Test = Test::findById($_GET['id']);
			$Test->Problems = TestProblem::findByTest($Test->id);

			$this->actionCreate($Test);
		}

		public function actionCreate($Test = false)
		{
			$this->setRights();

			// не надо панель рисовать
			$this->_custom_panel = true;

			if ($Test === false) {
				$Test = new Test();
			}

			$ang_init_data = angInit([
				"Test" 			=> $Test,
				"NewProblem"	=> TestProblem::getEmpty(),
			]);

			$this->render("add", [
				"ang_init_data" => $ang_init_data,
				"Test" 			=> $Test,
			]);
		}

		public function actionAjaxGetStudentTests()
		{
            extract($_POST);

            returnJsonAng(
				TestStudent::getData($page)
			);
        }

		public function actionAjaxAdd()
		{
			extract($_POST);

			$NewTest = Test::add($Test);

			foreach($Test['Problems'] as $Problem) {
				$Problem['id_test'] = $NewTest->id;
				TestProblem::add($Problem);
			}

			echo $NewTest->id;
		}

		public function actionAjaxEdit()
		{
			$new_problem_ids = [];
			extract($_POST);
			Test::updateById($Test['id'], $Test);
			foreach($Test['Problems'] as $Problem) {
				if ($Problem['id']) {
					$id = $Problem['id'];
					unset($Problem['id']);
					TestProblem::updateById($id, $Problem);
				} else {
					$Problem['id_test'] = $Test['id'];
					$Problem = TestProblem::add($Problem);
					$new_problem_ids[] = $Problem->id;
				}
			}
			echo json_encode($new_problem_ids);
		}

		public function actionAjaxDeleteProblem()
		{
			extract($_POST);

			TestProblem::deleteById($id_problem);
		}

		public function actionAjaxDeleteTest()
		{
			extract($_POST);

			Test::deleteById($id_test);
		}

		public function actionAjaxSignUp()
		{
			extract($_POST);

			if ($Test['checked'] == 'true') {
				TestStudent::add([
					'id_test'		=> $Test['id'],
					'id_student' 	=> $id_student,
				]);
			} else {
				TestStudent::deleteAll([
					'condition' => "id_test = {$Test['id']} AND id_student={$id_student}"
				]);
			}
		}

		public function actionAjaxFinishTest()
		{
			extract($_POST);

			$Test = TestStudent::get(User::id(), $id);
			$Test->finish();

			echo $Test->finalScoreString();
		}

		public function actionAjaxToggleStatus()
		{
			extract($_POST);

			$Test = TestStudent::get($id_student, $id_test);
			if (! $Test->isFinished) {
				$Test->intermediate = !$Test->intermediate;
				$Test->save('intermediate');
			}

			echo intval($Test->intermediate);
		}

		public function actionAjaxSaveAnswers()
		{
			extract($_POST);

			$Test = TestStudent::get(User::id(), $id);

			$Test->answers = $answers;
            $Test->calcScore();
            $Test->save();

		}

		public function actionAjaxDeleteStudentTest()
		{
			extract($_POST);
			TestStudent::deleteById($id);
		}
	}
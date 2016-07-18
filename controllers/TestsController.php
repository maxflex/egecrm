<?php	// Контроллер	class TestsController extends Controller	{		public $defaultAction = "list";				public static $allowed_users = [User::USER_TYPE, Teacher::USER_TYPE, Student::USER_TYPE];				// Папка вьюх		protected $_viewsFolder	= "tests";						public function beforeAction()		{			$this->addJs("ng-tests-app");			// $this->addJs("ng-tests-app, ng-autocomplete, ng-testing-app, bootstrap-select");			// $this->addCss('ng-autocomplete, bootstrap-select');		}				public function actionList()		{				// не надо панель рисовать			$this->_custom_panel = true;						$ang_init_data = angInit([				"Tests" 	=> Test::findAll(),			]);						$this->render("list", [				'ang_init_data' => $ang_init_data,			]);		}						public function actionStudentList()		{				// не надо панель рисовать			$this->_custom_panel = true;						$Tests = TestStudent::findAll([				"condition" => "id_student=" . User::fromSession()->id_entity,			]);						$ang_init_data = angInit([				"Tests" => $Tests,			]);						$this->render("student_list", [				'ang_init_data' => $ang_init_data,			]);		}				public function actionStart()		{				$id = $_GET['id'];						// не надо панель рисовать			$this->_custom_panel = true;			$this->addCss('tests');						$Test = Test::findById($id);						$StudentTest = TestStudent::find([				"condition" => "id_student=" . User::fromSession()->id_entity . " AND id_test={$id}",			]);						$data = [				"answers" => TestStudent::getAnswers($id),				"Test" => $Test,			];						if ($StudentTest->finished()) {				$data['final_score'] = $StudentTest->finalScoreString();			}			else if (! $StudentTest->inProgress()) {				$StudentTest->start();				}						$data['time'] = time() - strtotime($StudentTest->date_start);				$ang_init_data = angInit($data);						$this->render("test", [				'ang_init_data' => $ang_init_data,			]);		}						public function actionEdit()		{			$Test = Test::findById($_GET['id']);						$this->actionCreate($Test);		}				public function actionCreate($Test = false)		{			$this->setRights();						// не надо панель рисовать			$this->_custom_panel = true;						if ($Test === false) {				$Test = new Test();				}						$ang_init_data = angInit([				"Test" 			=> $Test,				"NewProblem"	=> TestProblem::getEmpty(),			]);			$this->render("add", [				"ang_init_data" => $ang_init_data,				"Test" 			=> $Test,			]);		}				public function actionAjaxAdd()		{			extract($_POST);						$NewTest = Test::add($Test);						foreach($Test['Problems'] as $Problem) {				$Problem['id_test'] = $NewTest->id;				TestProblem::add($Problem);			}						echo $NewTest->id;		}				public function actionAjaxEdit()		{			extract($_POST);			Test::updateById($Test['id'], $Test);			foreach($Test['Problems'] as $Problem) {				if ($Problem['id']) {					$id = $Problem['id'];					unset($Problem['id']);					TestProblem::updateById($id, $Problem);				} else {					$Problem['id_test'] = $Test['id'];					TestProblem::add($Problem);				}			}		}						public function actionAjaxDeleteProblem()		{			extract($_POST);						TestProblem::deleteById($id_problem);		}				public function actionAjaxDeleteTest()		{			extract($_POST);						Test::deleteById($id_test);		}				public function actionAjaxSignUp()		{			extract($_POST);						if ($Test['checked'] == 'true') {				TestStudent::add([					'id_test'		=> $Test['id'],					'id_student' 	=> $id_student,				]);			} else {				TestStudent::deleteAll([					'condition' => "id_test = {$Test['id']} AND id_student={$id_student}"				]);			}		}				public function actionAjaxFinishTest()		{			extract($_POST);						$Test = TestStudent::get(User::fromSession()->id_entity, $id);			$Test->finish();						echo $Test->finalScoreString();		}								public function actionAjaxToggleStatus()		{			extract($_POST);						$Test = TestStudent::get($id_student, $id_test);						if (! $Test->isFinished) {				$Test->intermediate = !$Test->intermediate;				$Test->save('intermediate');			}						echo intval($Test->intermediate);		}	}
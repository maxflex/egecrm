<?php	// Контроллер	class TestingController extends Controller	{		public $defaultAction = "list";				public static $allowed_users = [User::USER_TYPE, Teacher::USER_TYPE, Student::USER_TYPE];				// Папка вьюх		protected $_viewsFolder	= "testing";				public function beforeAction()		{			$this->addJs("ng-testing-app");		}				public function actionList()		{				$this->setTabTitle("Пробные тестирования");						$ang_init_data = angInit([				"Testings" 	=> Testing::findAll(),				'Subjects'	=> Subjects::$three_letters,			]);						$this->render("list", [				'ang_init_data' => $ang_init_data,			]);		}				public function actionEdit()		{			$Testing = Testing::findById($_GET['id']);						$this->actionAdd($Testing);		}				public function actionAdd($Testing = false)		{			if ($Testing) {				$this->setTabTitle('Редактирование тестирования');			} else {				$this->setTabTitle("Добавление тестирования");			}						$Cabinets = Cabinet::getByBranch(Branches::TRG);						$ang_array = [				'future_dates' 	=> self::_generateFutureDates(),				'Cabinets'		=> $Cabinets,				'Subjects'		=> Subjects::$three_letters,				'minutes_9'		=> Subjects::$minutes_9,				'minutes_11'	=> Subjects::$minutes_11,			];						if ($Testing) {				$ang_array['Testing'] = $Testing;			}						$ang_init_data = angInit($ang_array);						$this->render("add", [				'ang_init_data' => $ang_init_data,				'Testing'		=> $Testing,			]);		}				public function actionAjaxAdd()		{			extract($_POST);						$Testing['subjects_9']	= array_keys($Testing['subjects_9']);			$Testing['subjects_11'] = array_keys($Testing['subjects_11']);			// 			preType($Testing);						returnJsonAng(				Testing::add($Testing)->id			);		}				public function actionAjaxSave()		{			extract($_POST);						$Testing['subjects_9']	= array_keys($Testing['subjects_9']);			$Testing['subjects_11'] = array_keys($Testing['subjects_11']);						Testing::updateById($Testing['id'], $Testing);						preType($Testing);		}				public function actionAjaxChangeDate()		{			extract($_POST);						$Cabinets = Cabinet::getByBranch(Branches::TRG);						foreach ($Cabinets as $Cabinet) {				$cabinet_ids[] = $Cabinet->id;			}			$cabinet_ids = implode(',', $cabinet_ids);						// lesson time			$time_data_schedule = GroupSchedule::findAll([				"condition" => "date='$date' AND cabinet IN ($cabinet_ids)",				"order"		=> "time ASC"			]);						foreach ($time_data_schedule as $data) {				$return[$data->cabinet][] = [					'start_time' => $data->time,					'end_time'	 => self::_plusHours($data->time),				];			}						// testing time			$time_data_testing = Testing::findAll([				"condition" => "date='$date' AND cabinet IN ($cabinet_ids)". ($id > 0 ? " AND id!=$id" : ""),				"order"	=> "start_time ASC"			]);						foreach ($time_data_testing as $data) {				$return[$data->cabinet][] = [					'start_time' => $data->start_time,					'end_time'	 => $data->end_time,				];			}						returnJsonAng($return);		}				private static function _plusHours($time, $hours = 2, $minutes = 15)		{			$timestamp = strtotime($time) + 60*60*$hours + (60 * $minutes);			return date('H:i', $timestamp);		}				private static function _generateFutureDates($days = 14) 		{			foreach(range(0, $days) as $day) {				$dates[] = date("Y-m-d", strtotime("+$day days"));			}						return $dates;		}			}
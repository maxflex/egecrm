<?php

	// Контроллер
	class SettingsController extends Controller
	{
		public static $allowed_users = [User::USER_TYPE];

		public $defaultAction = "vocations";

		// Папка вьюх
		protected $_viewsFolder	= "settings";

		public function beforeAction()
		{
			$this->addJs("ng-settings-app, vendor/angular-bootstrap-calendar-tpls");
		}

		public function actionPrices()
		{
			$this->setTabTitle("Рекомендуемые цены");

			$prices = json_decode(Settings::get("recommended_prices"));

			$ang_init_data = angInit([
				"years" => Years::get(),
				"prices" => $prices,
			]);

			$this->render("recommended_price", [
				"ang_init_data" => $ang_init_data,
			]);
		}

		public function actionVacations()
		{
            # @rights-refactored
            $this->checkRights(Shared\Rights::SHOW_CALENDAR);

			// не надо панель рисовать
			$this->_custom_panel = true;

			$year = Years::getCurrent();

			$Vacations = Vacation::getByYear($year);

			$ang_init_data = angInit([
				"Vacations"		=> $Vacations,
				"Subjects"		=> Subjects::$three_letters,
				"current_year"	=> $year,
                "exam_days" 	=> ExamDay::getData($year),
                "special_dates"	=> [
                    'vacations' => Vacation::getDates($year),
                ],
			]);

			$this->render("vacations", [
				"ang_init_data" => $ang_init_data,
			]);
		}

		// @time-refactored @time-checked
		public function actionCabinet()
        {
            $this->setTabTitle("Загрузка кабинетов");
            $Cabinets = [];
            $branches = Branches::getAll('*');
            foreach ($branches as &$branch) {
				$branch->svg = Branches::metroSvg($branch->color);
                foreach (Cabinet::getByBranch($branch->id) as $Cabinet) {
					if ($Cabinet->isDeleted()) {
						continue;
					}
                    $Cabinet->bar = Freetime::getCabinetBar(null, $Cabinet->id);
                    $Cabinets[$branch->id][] = $Cabinet;
                }
            }

            $ang_init_data = angInit([
                "Branches" => $branches,
                "Cabinets" => $Cabinets
            ]);

            $this->render('cabinet', [
                "ang_init_data" => $ang_init_data,
            ]);
        }

		public function actionAjaxAddCabinet()
		{
			Cabinet::add($_POST);
		}

		public function actionAjaxRemoveCabinet()
		{
			extract($_POST);

			$Cabinet = Cabinet::findAll([
				"condition" => "id_branch=$id_branch",
				"limit"		=> "$index, 1"
			])[0];

			$Cabinet->delete();
		}
	}

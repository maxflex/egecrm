<?php

	// Контроллер
	class SettingsController extends Controller
	{
		public $defaultAction = "vocations";

		// Папка вьюх
		protected $_viewsFolder	= "settings";
		
		public function beforeAction()
		{
			$this->addCss("bootstrap-select");
			$this->addJs("ng-settings-app, bootstrap-select");
		}
		
		public function actionVocations()
		{
			// не надо панель рисовать
			$this->_custom_panel = true;
			
			$id_group 	= $_GET['id'];
			$year		= Years::getCurrent();
			
			$Group = new Group([
				"id" 	=> 0,
				"year"	=> $year,
			]);
			
			$Group->Schedule = $Group->getSchedule();
			
			$ang_init_data = angInit([
				"Group" 		=> $Group,
				"Subjects"		=> Subjects::$three_letters,
				"current_year"	=> $year,
				"exam_days" 	=> ExamDay::getData($year),
			]);
			
			$this->render("vocations", [
				"Group"			=> $Group,
				"ang_init_data" => $ang_init_data,	
			]);
		}

		public function actionCabinet()
        {
            $this->setTabTitle("Загрузка кабинетов");
            $Cabinets = [];
            foreach (Branches::$all as $id_branch => $title) {
                foreach (Cabinet::getByBranch($id_branch) as $Cabinet) {
                    $Cabinet->bar = Freetime::getCabinetBar($Cabinet->id);
                    $Cabinets[$id_branch][] = $Cabinet;
                }
            }

            $ang_init_data = angInit([
                "Branches" => Branches::getBranches(),
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
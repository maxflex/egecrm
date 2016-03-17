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
			
			$id_group = $_GET['id'];		
			
			$Group = new Group([
				"id" => 0,
			]);
			
			$Group->Schedule = $Group->getSchedule();
			
			$ang_init_data = angInit([
				"Group" 	=> $Group,
				"Subjects"	=> Subjects::$three_letters,
				"exam_days" => ExamDay::getData(),
			]);
			
			$this->render("vocations", [
				"Group"			=> $Group,
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
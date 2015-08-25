<?php

	// Контроллер
	class SettingsController extends Controller
	{
		public $defaultAction = "vocations";

		// Папка вьюх
		protected $_viewsFolder	= "settings";
		
		public function beforeAction()
		{
			$this->addJs("ng-settings-app");
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
				"Group" => $Group,
			]);
			
			$this->render("vocations", [
				"Group"			=> $Group,
				"ang_init_data" => $ang_init_data,	
			]);
		}
	}
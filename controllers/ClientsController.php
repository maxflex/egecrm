<?php

	// Контроллер
	class ClientsController extends Controller
	{
		public $defaultAction = "list";
		
		// Папка вьюх
		protected $_viewsFolder	= "clients";
		
		public function beforeAction()
		{
			$this->addJs("ng-clients-app");
		}
		
		public function actionList()
		{
			// не надо панель рисовать
			$this->_custom_panel = true;
			
			$Students = Student::getWithContract();
			
			foreach ($Students as &$Student) {
				$Student->Contract 	= $Student->getLastContract();
				$Student->Contracts = $Student->getActiveContracts();
				
				$date_formatted = new DateTime($Student->Contract->date);
				$Student->date_formatted = $date_formatted->format("Y-m-d");
				
				$Student->groups_count = $Student->countGroups();
			}
			
			$without_contract = LOCAL_DEVELOPMENT ? Student::countWithoutContract() : memcached()->get("TotalStudentsWithNoContract");
			
			$ang_init_data = angInit([
				"Students" => $Students,
			]);
			
			$this->render("list", [
				"sort"		=> $_GET['sort'],
				"ang_init_data" => $ang_init_data,
				"Students" => $Students,
				"without_contract" => $without_contract,
			]);
		}
		
	}
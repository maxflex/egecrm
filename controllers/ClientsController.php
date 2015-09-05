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
			$this->setTabTitle("Клиенты с договорами");	
			
			$Students = Student::getWithContract();
			
			foreach ($Students as &$Student) {
				$Student->Contract 	= $Student->getLastContract();
				$Student->Contracts = $Student->getActiveContracts();
				
				$date_formatted = new DateTime($Student->Contract->date);
				$Student->date_formatted = $date_formatted->format("Y-m-d");
				
				$markers = $Student->getMarkers();
				$Student->markers_count = $markers === false ? '' : count($markers);
			}
			
			$without_contract = Student::countWithoutContract();
			
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
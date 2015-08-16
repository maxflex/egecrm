<?php

	// Контроллер
	class ClientsController extends Controller
	{
		public $defaultAction = "list";
		
		// Папка вьюх
		protected $_viewsFolder	= "clients";
		
		public function actionList()
		{
			$this->setTabTitle("Клиенты с договорами");	
			
			$Students = Student::getWithContract();
			
			foreach ($Students as &$Student) {
				$Student->Contract 	= $Student->getLastContract();
				$Student->Contracts = $Student->getActiveContracts();
			}
			
			$without_contract = Student::countWithoutContract();
			
			// сортировка по номеру договора
			usort($Students, function($a, $b) {
				return ($a->Contract->id < $b->Contract->id ? -1 : 1);
			});
			
//			$Students = array_reverse($Students);
			
			$this->render("list", [
				"Students" => $Students,
				"without_contract" => $without_contract,
			]);
		}
		
	}
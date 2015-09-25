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
				
				if (!empty($Student->login)) {
					$Student->login_count = User::getLoginCount($Student->id, Student::USER_TYPE);
				}
				
				$date_formatted = new DateTime($Student->Contract->date);
				$Student->date_formatted = $date_formatted->format("Y-m-d");
				
// 				$Student->groups_count = $Student->countGroups();
				$Student->Groups = $Student->getGroups();
				
				foreach ($Student->Groups as $index => $Group) {
					$freetime = $Student->getGroupFreetime($Group->id);
					$Student->Groups[$index]->freetime 			= $freetime["freetime"];
					$Student->Groups[$index]->student_agreed	= $Student->agreedToBeInGroup($Group->id);
/*
					$Student->Groups[$index]->freetime_red 		= $freetime["freetime_red"];
					$Student->Groups[$index]->freetime_red_half = $freetime["freetime_red_half"];	
*/
				}
			}
			
			$without_contract = LOCAL_DEVELOPMENT ? Student::countWithoutContract() : memcached()->get("TotalStudentsWithNoContract");
			
			$ang_init_data = angInit([
				"Students" 			=> $Students,
			]);
			
			$this->render("list", [
				"sort"		=> $_GET['sort'],
				"ang_init_data" => $ang_init_data,
				"Students" => $Students,
				"without_contract" => $without_contract,
			]);
		}
		
		
		public function actionPreCancelled()
		{
			// не надо панель рисовать
			$this->_custom_panel = true;
			
			$Students = Student::getWithContractPreCancelled();
			
			foreach ($Students as &$Student) {
				$Student->Contract 	= $Student->getLastContract();
				$Student->Contracts = $Student->getActiveContracts();
				
				if (!empty($Student->login)) {
					$Student->login_count = User::getLoginCount($Student->id, Student::USER_TYPE);
				}
				
				$date_formatted = new DateTime($Student->Contract->date);
				$Student->date_formatted = $date_formatted->format("Y-m-d");
				
// 				$Student->groups_count = $Student->countGroups();
				$Student->Groups = $Student->getGroups();
				
				foreach ($Student->Groups as $index => $Group) {
					$freetime = $Student->getGroupFreetime($Group->id);
					$Student->Groups[$index]->freetime 			= $freetime["freetime"];
					$Student->Groups[$index]->student_agreed	= $Student->agreedToBeInGroup($Group->id);
/*
					$Student->Groups[$index]->freetime_red 		= $freetime["freetime_red"];
					$Student->Groups[$index]->freetime_red_half = $freetime["freetime_red_half"];	
*/
				}
			}
			
			$without_contract = LOCAL_DEVELOPMENT ? Student::countWithoutContract() : memcached()->get("TotalStudentsWithNoContract");
			
			$ang_init_data = angInit([
				"Students" 			=> $Students,
			]);
			
			$this->render("list_precancelled", [
				"sort"		=> $_GET['sort'],
				"ang_init_data" => $ang_init_data,
				"Students" => $Students,
				"without_contract" => $without_contract,
			]);
		}
		
	}
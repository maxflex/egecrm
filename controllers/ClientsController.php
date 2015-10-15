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
		
		
		public function actionCancelled()
		{
			// не надо панель рисовать
			$this->_custom_panel = true;
			
			if ($_GET["type"] == 2) {
				$Students = Student::getWithContractCancelled();
			} else {
				$Students = Student::getWithContractPreCancelled();
			}
			
			foreach ($Students as &$Student) {
				$Student->Contract 	= $Student->getLastContract();
				$Student->Contracts = $Student->getActiveContracts();
				
				if (!empty($Student->login)) {
					$Student->login_count = User::getLoginCount($Student->id, Student::USER_TYPE);
				}
				
				$date_formatted = new DateTime($Student->Contract->date);
				$Student->date_formatted = $date_formatted->format("Y-m-d");
<<<<<<< HEAD
 			}
 			
 			returnJsonAng($Students);
		}
		
		public function actionAjaxGetErrorStudents()
		{
			extract($_POST);
			
			switch($mode) {
				case "?mode=layer": {
					$Response = Student::getLayerErrors();
					break;
				}
				case "?mode=duplicate": {
					$Response = Student::getSameSubjectErrors();
					break;
				}
				case "?mode=nogroup": {
					$Response = Student::getErrors();
					break;
				}
				case "?mode=phone": {
					$Response = Student::getPhoneErrors();
					break;
				}
				case "?mode=grouptime": {
					$Groups = Group::findAll();

					foreach ($Groups as &$Group) {
						if (!$Group->lessonDaysMatch()) {
							$r[] = $Group->id;	
						}
					}
					$Response = $r;
					
					break;
				}
				case "?mode=groupgrade": {
					$Groups = Group::findAll();

					foreach ($Groups as $Group) {
						if ($Group->students) {
							$Students = Student::findAll([
								"condition" => "id IN (". implode(",", $Group->students) .")"
							]);
							
							foreach ($Students as $Student) {
								if ($Student->grade != $Group->grade) {
									$r[] = [
										"Group"	=> $Group,
										"Student" 	=> $Student,	
									];
								}
							}
						}
					}
					$Response = $r;
					
					break;
				}
				case "?mode=cancelled": {
					$result = dbConnection()->query("
						select s.id, s.last_name, s.first_name, s.middle_name from students s
						left join groups g on (CONCAT(',', CONCAT(g.students, ',')) LIKE CONCAT('%,', s.id ,',%'))
						left join contracts c on c.id_student = s.id
						where g.id is not null and (c.id is null or c.cancelled = 1)
						group by s.id
					");
					
					while ($row = $result->fetch_object()) {
						$return[] = $row;	
					}
					
					$Response = $return;
					
					break;
=======
				
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
>>>>>>> parent of bb26286... Конец недели STABLE
				}
			}
			
			$without_contract = LOCAL_DEVELOPMENT ? Student::countWithoutContract() : memcached()->get("TotalStudentsWithNoContract");
			
			$ang_init_data = angInit([
				"Students" 			=> $Students,
			]);
			
			$this->render("list_precancelled", [
				"type"		=> $_GET["type"],
				"sort"		=> $_GET['sort'],
				"ang_init_data" => $ang_init_data,
				"Students" => $Students,
				"without_contract" => $without_contract,
			]);
		}
		
	}
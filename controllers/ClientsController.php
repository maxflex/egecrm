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
		
		public function actionErrors()
		{
			$this->setTabTitle("Ошибки");
			$this->render("errors");
		}
		
		public function actionAjaxGetStudents()
		{
			$Students = Student::getWithContract();
			
			foreach ($Students as &$Student) {
				$Student->Contract 	= $Student->getLastContract();
				$Student->User = User::find(["condition" => "id_entity=" . $Student->id]);
				
				$date_formatted = new DateTime($Student->Contract->date);
				$Student->date_formatted = $date_formatted->format("Y-m-d");
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
				}
			}
			
			returnJsonAng($Response);
		}
	}
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
			
/*
			$ang_init_data = angInit([
				"Students" 			=> $Students,
			]);
*/
			
			$this->render("list", [
				"sort"		=> $_GET['sort'],
//				"ang_init_data" => $ang_init_data,
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
				
				
				//
				foreach ($Student->Contract->subjects as $subject) {
					$Student->sc[$subject['status']] += $subject['count'] + $subject['count2'];
				}
				
				foreach (range(3, 1) as $status) {
					if ($Student->sc[$status]) {
						$Student->subject_count[] = [
							'status' 	=> $status,
							'count'		=> $Student->sc[$status],
						];
					}
				}
				//
				
				
				$Student->User = User::find(["condition" => "id_entity=" . $Student->id]);
				
				$Student->Remainder = PaymentRemainder::getByStudentId($Student->id);
				
				$date_formatted = new DateTime($Student->Contract->date);
				$Student->date_formatted = $date_formatted->format("Y-m-d");
 			}
 			
 			returnJsonAng($Students);
		}
		
		public function actionAjaxGetErrorStudents()
		{
			extract($_POST);
			
			switch($mode) {
				case "?mode=phone": {
					$Response = Student::getPhoneErrors();
					break;
				}
			}
			
			returnJsonAng($Response);
		}
	}
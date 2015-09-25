<?php

	// Контроллер
	class PaymentsController extends Controller
	{
		public $defaultAction = "list";
		
		// Папка вьюх
		protected $_viewsFolder	= "payments";
		
		public function beforeAction()
		{
			$this->addJs("ng-payments-app");
		}
		
		public function actionList()
		{
			$this->setTabTitle("Платежи");	
			
			$Payments = Payment::findAll();
			

			foreach ($Payments as &$Payment) {
				$Payment->Student = $Payment->getStudent();
			}
			
			$Payments = array_reverse($Payments);

/*			
			// сортировка по номеру договора
			usort($Students, function($a, $b) {
				return ($a->Contract->id < $b->Contract->id ? -1 : 1);
			});
			
			$Students = array_reverse($Students);
*/
			$ang_init_data = angInit([
				"payments" 			=> $Payments,
				"payment_types"		=> PaymentTypes::$all,
				"payment_statuses"	=> Payment::$all,
			]);
			
			$this->render("list", [
				"ang_init_data" => $ang_init_data
			]);
		}
		
	}
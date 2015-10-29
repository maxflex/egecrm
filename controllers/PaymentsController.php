<?php

	// Контроллер
	class PaymentsController extends Controller
	{
		public $defaultAction = "list";
		
		public static $allowed_users = [User::USER_TYPE, Teacher::USER_TYPE];
		
		// Папка вьюх
		protected $_viewsFolder	= "payments";
		
		public function beforeAction()
		{
			$this->addJs("ng-payments-app");
		}
		
		public function actionList()
		{
			$this->setRights([User::USER_TYPE]);
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
		
		public function actionTeacher()
		{
			$this->setTabTitle("Оплата");
			$this->setRights([Teacher::USER_TYPE]);
			
			$ang_init_data = angInit([
				"Subjects" => Subjects::$all,
				"Branches" => Branches::$all,
				"payment_statuses"	=> Payment::$all,
			]);
			
			$this->render("lk_teacher", [
				"ang_init_data" => $ang_init_data
			]);
		}
		
		
		public function actionAjaxLkTeacher()
		{
			# Данные по занятиям/выплатам
			$Data = VisitJournal::findAll([
						"condition" => "id_entity=". User::fromSession()->id_entity ." AND type_entity='TEACHER'",
						"order"		=> "lesson_date DESC, lesson_time DESC",
					]);
			# Добавляем группы к инфе					
			foreach ($Data as &$D) {
				$D->Group = Group::findById($D->id_group);
			}
			
			returnJsonAng([
				# Платежи
				"payments" 	=> 	TeacherPayment::findAll([
									"condition" => "id_teacher=" . User::fromSession()->id_entity
								]),
				# Данные по занятиям/выплатам
				"Data"		=> 	$Data,
			]);
		}
	}
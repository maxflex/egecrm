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

			$this->addJs("bootstrap-select");
			$this->addCss("bootstrap-select");

			$ang_init_data = angInit([
				'payment_types'		=> PaymentTypes::$all,
				'payment_statuses'	=> Payment::$all,
				'current_page'		=> $_GET['page'] ? $_GET['page'] : 1
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
		
		
		public function actionAjaxGetPayments()
		{
			extract($_POST);
			
/*
			ini_set('display_errors', 1);
			error_reporting(E_ALL);
*/
			
			$condition['confirmed'] = $search['confirmed'] != '' ? "confirmed = {$search['confirmed']}" : '1';
			$condition['id_status'] = $search['payment_type'] ? "id_status = {$search['payment_type']}" : '1';

			$query['limit'] = ($search['current_page'] - 1)*Payment::PER_PAGE.',30';
			$query['condition'] = implode(' and ', $condition);;
			$query['order'] = 'first_save_date desc';

			/* платежи */
			$payment_class = Payment::getEntityClass($search['mode']);

			$Payments = $payment_class::findAll($query);
			foreach ($Payments as $Payment) {
				$Payment->Entity = $Payment->getEntity();
			}

			/* каунтеры */
			$counts['mode'] = [
				'client'  => Payment::count(["condition" => implode(' and ', $condition)]),
				'teacher' => TeacherPayment::count(["condition" => implode(' and ', $condition)]),
			];

			foreach (array_keys(Payment::$all)  as $type) {
				$count_cond = $condition;
				$count_cond['id_status'] = "id_status = {$type}";
				$counts['payment_type'][$type] = $payment_class::count(["condition" => implode(' and ', $count_cond)]);
			}
			 $counts['payment_type']['all'] = array_sum($counts['payment_type']);

			foreach ([0,1] as $confirmed) {
				$count_cond = $condition;
				$count_cond['confirmed'] = "confirmed = {$confirmed}";
				$counts['confirmed'][$confirmed] = $payment_class::count(["condition" => implode(' and ', $count_cond)]);
			}
			$counts['confirmed']['all'] = array_sum($counts['confirmed']);
			
			
			foreach([1, 2] as $type) {
				$count_cond = $condition;
				$count_cond['id_type'] = "id_type = {$type}";
				$counts['type'][$type] = Payment::count(["condition" => implode(' and ', $count_cond)]);
			}
			$counts['type']['all'] = Payment::count(["condition" => implode(' and ', $condition)]);
			
			/* каунтеры */ 

			returnJsonAng([
				'payments'	=> $Payments,
				'counts'	=> $counts
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
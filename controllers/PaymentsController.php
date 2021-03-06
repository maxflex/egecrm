<?php

	// Контроллер
	class PaymentsController extends Controller
	{
		public $defaultAction = "list";

		public static $allowed_users = [Admin::USER_TYPE, Teacher::USER_TYPE];

		// Папка вьюх
		protected $_viewsFolder	= "payments";

		public function beforeAction()
		{
			$this->addJs("ng-payments-app");
		}

		public function actionList()
		{
			$this->_custom_panel = true;
            $this->checkRights(Shared\Rights::SHOW_PAYMENTS);
			$this->setRights([Admin::USER_TYPE]);
			$this->setTabTitle("Платежи");

			$ang_init_data = angInit([
				'payment_types'		=> PaymentTypes::$all,
				'payment_statuses'	=> Payment::$all,
				'Subjects'			=> Subjects::$all,
				'academic_year'		=> academicYear(),
                'user_rights'       => User::fromSession()->rights,
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
				"Subjects" => Subjects::$three_letters,
				"Branches" => Branches::getAll(),
				"payment_statuses"	=> Payment::$all,
                'payment_types'		=> PaymentTypes::$all,
				"id_teacher" => User::id(),
				'view_mode' => isset($_SESSION['view_mode_user_id']),
			]);

			$this->render("lk_teacher", [
				"ang_init_data" => $ang_init_data
			]);
		}

		public function actionAjaxGetPayments()
		{
			extract($_POST);

			$condition['confirmed'] = $search['confirmed'] != '' ? "confirmed = {$search['confirmed']}" : '1';
			$condition['id_status'] = $search['payment_type'] ? ($search['payment_type'] == -1 ? "(id_status = 6 AND entity_type='TEACHER' AND account_id IS NULL)" : "id_status = {$search['payment_type']}") : '1';

			$condition['id_type'] = $search['type'] ? "id_type = {$search['type']}" : '1';
			$condition['category'] = $search['category'] ? "category = {$search['category']}" : '1';
			$condition['year'] = $search['year'] ? "year = {$search['year']}" : '1';

			$mode_conditions = [];
			if ($search['mode']) {
				foreach($search['mode'] as $mode) {
					if ($mode == 'ANONYMOUS') {
						$mode_conditions[] = "(entity_type IS NULL OR entity_type='')";
					} else {
						$mode_conditions[] = "(entity_type = '{$mode}')";
					}
				}
			}
			$condition['mode'] = count($mode_conditions) ? "(" . implode(' OR ', $mode_conditions) . ")" : "1";
			debugLog($search['mode']);

			$query['limit'] = ($search['current_page'] - 1)*Payment::PER_PAGE.',' . Payment::PER_PAGE;
			$query['condition'] = implode(' and ', $condition);;
			$query['order'] = 'first_save_date desc';

			/* платежи */
			$Payments = Payment::findAll($query);
			foreach ($Payments as $Payment) {
				$Payment->Entity = $Payment->getEntity();
			}

			/* каунтеры */
			foreach ([Student::USER_TYPE, Teacher::USER_TYPE, 'ANONYMOUS'] as $entity_type) {
				$count_cond = $condition;
				$count_cond['entity_type'] = $entity_type == 'ANONYMOUS' ? "(entity_type is null or entity_type='')" : "entity_type = '".$entity_type."'";
				$counts['mode'][$entity_type] = Payment::count(['condition' => implode(' and ', $count_cond)]);
			}

            // -1 = неассоциированные взаимозачеты, можно удалить
			foreach (array_merge([-1], array_keys(Payment::$all)) as $type) {
				$count_cond = $condition;
                if ($type == -1) {
                    $count_cond['id_status'] = "(id_status = 6 AND entity_type='TEACHER' AND account_id IS NULL)";
    				$counts['payment_type'][$type] = Payment::count(["condition" => implode(' and ', $count_cond)]);
                } else {
                    $count_cond['id_status'] = "id_status = {$type}";
    				$counts['payment_type'][$type] = Payment::count(["condition" => implode(' and ', $count_cond)]);
                }
			}
			$counts['payment_type']['all'] = array_sum($counts['payment_type']);

			foreach ([0,1] as $confirmed) {
				$count_cond = $condition;
				$count_cond['confirmed'] = "confirmed = {$confirmed}";
				$counts['confirmed'][$confirmed] = Payment::count(["condition" => implode(' and ', $count_cond)]);
			}
			$counts['confirmed']['all'] = array_sum($counts['confirmed']);

			foreach([1, 2] as $type) {
				$count_cond = $condition;
				$count_cond['id_type'] = "id_type = {$type}";
				$counts['type'][$type] = Payment::count(["condition" => implode(' and ', $count_cond)]);
			}
			$counts['type']['all'] = array_sum($counts['type']);

			foreach([1, 2, 3] as $category) {
				$count_cond = $condition;
				$count_cond['category'] = "category = {$category}";
				$counts['category'][$category] = Payment::count(["condition" => implode(' and ', $count_cond)]);
			}
			$counts['category']['all'] = array_sum($counts['category']);

			foreach(Years::$all as $year) {
				$count_cond = $condition;
				$count_cond['year'] = "year = {$year}";
				$counts['year'][$year] = Payment::count(["condition" => implode(' and ', $count_cond)]);
			}
			$counts['year']['all'] = array_sum($counts['year']);
			/* каунтеры */

			returnJsonAng([
				'payments'	=> $Payments,
				'counts'	=> $counts
			]);
		}

		public function actionAjaxLkTeacher()
		{
            $id_teacher = User::id();
			$payments = Teacher::getPayments($id_teacher);
			$years = array_reverse(array_keys($payments));
			returnJsonAng([
				'Lessons' => $payments,
				'years' => $years,
				'selected_year' => end($years)
			]);
		}

		public function actionAjaxNewDocumentNumber()
        {
            returnJson(['document_number' => dbConnection()->query('select max(document_number) + 1 as doc_num from payments')->fetch_object()->doc_num]);
        }
	}

<?php

	// Контроллер
	class CallsController extends Controller
	{
		public $defaultAction = "missed";

		public static $allowed_users = [User::USER_TYPE];

		// Папка вьюх
		protected $_viewsFolder	= "calls";

		public function beforeAction()
		{
			$this->addJs('ng-calls-app');
		}

		public function actionMissed()
		{
			$this->setTabTitle("Пропущенные");

			$ang_init_data = angInit([
				'missed' => Call::missed()
			]);

			$this->render("missed", [
				"ang_init_data" => $ang_init_data,
			]);
		}

		public function actionRating()
		{
			$this->setTabTitle("Оценка качества обслуживания");

			$ang_init_data = angInit([
				'data' => SmsRating::findAll([
					'order' => 'call_date desc'
				]),
			]);

			$this->render("rating", [
				"ang_init_data" => $ang_init_data,
			]);
		}

		public function actionAjaxDelete()
        {
            extract($_POST);
            Call::excludeFromMissed($entry_id);
        }
	}

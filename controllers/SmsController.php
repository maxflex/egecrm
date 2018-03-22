<?php

	// Контроллер
	class SmsController extends Controller
	{
		public $defaultAction = 'history';

		// Папка вьюх
		protected $_viewsFolder	= 'sms';

		public function beforeAction()
		{
			$this->addJs('ng-sms-app');
		}

		// Страница входа
		public function actionHistory()
		{
			$this->setTabTitle('История СМС');

			$ang_init_data = angInit([
				'currentPage' => $_GET['page'],
			]);

			$this->render('history', [
				'ang_init_data' => $ang_init_data,
			]);
		}

        public function actionGet()
        {
            $number = filter_var($_GET['number'], FILTER_SANITIZE_NUMBER_INT);
            $result = [];

            if ($number) {
                if ($data = SMS::findAll([
                    'condition' => 'number = ' . $number,
                    'order' => 'date desc'
                ], true)) {
                    $result = $data;
                }
            }

            returnJsonAng($result);
        }

		public function actionAjaxHistory()
		{
			returnJsonAng([
				'data' 	=> SMS::getByPage($_POST['page'], $_POST['search']),
				'total'	=> SMS::pagesCount($_POST['search'])
			]);
		}
	}

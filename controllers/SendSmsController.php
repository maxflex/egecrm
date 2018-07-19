<?php

	// Контроллер
	class SendSmsController extends Controller
	{
		public $defaultAction = "main";

        public static $allowed_users = [Admin::USER_TYPE];

        // Папка вьюх
        protected $_viewsFolder	= "send-sms";

        public function beforeAction()
        {
            $this->addJs('ng-sms-app');
        }

        public function actionMain()
        {
            $this->setTabTitle("Отправка СМС");

            $page = $_GET['page'] ? $_GET['page'] : 1;

            $this->render("index", [
                "ang_init_data" => $ang_init_data,
            ]);
        }
	}
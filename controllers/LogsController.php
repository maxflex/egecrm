<?php

    class LogsController extends Controller
    {
        public $defaultAction = "list";

        public static $allowed_users = [Admin::USER_TYPE];

        // Папка вьюх
        protected $_viewsFolder	= "logs";

        public function beforeAction()
        {
            $this->checkRights(Shared\Rights::LOGS);
            $this->addJs('ng-logs-app');
        }

        public function actionList()
        {
            $this->setTabTitle("Логи");

            $page = $_GET['page'] ? $_GET['page'] : 1;
            $ang_init_data = angInit([
                'page'      => $page,
                'tables' => ($tables = Log::getTables())
            ]);


            $this->render("list", [
                "ang_init_data" => $ang_init_data,
            ]);
        }

        public function actionAjaxGetLogs()
        {
            extract($_POST);

            returnJsonAng(
                Log::getData($page)
            );
        }
    }

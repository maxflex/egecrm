<?php

    class LogsController extends Controller
    {
        public $defaultAction = "list";

        public static $allowed_users = [User::USER_TYPE];

        // Папка вьюх
        protected $_viewsFolder	= "logs";

        public function beforeAction()
        {
            $this->addJs('ng-logs-app');
        }

        public function actionList()
        {
            $this->addJs("bootstrap-select");
            $this->addCss("bootstrap-select");
            $this->setTabTitle("Логи");

            $page = $_GET['page'] ? $_GET['page'] : 1;
            $ang_init_data = angInit([
                'page'      => $page,
                'tables' => ($tables = Log::getTables()),
                'columns' => Log::getTableColumns($tables),
                'users' => User::getCached(true)
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

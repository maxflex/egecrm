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
			$ang_init_data = angInit([
				'currentPage'	=> $_GET['page'] ? $_GET['page'] : 1,
				'total_debt'	=> Student::getTotalDebt(),
				'user'	        => User::fromSession(),
			]);

			$this->render("list", [
				"sort"		=> $_GET['sort'],
				"ang_init_data" => $ang_init_data,
			]);
		}
		
		public function actionAjaxGetStudents()
		{
			extract($_POST);

			returnJsonAng(
				Student::getData($page)
			);
		}
	}

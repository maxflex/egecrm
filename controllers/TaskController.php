<?php

	// Контроллер
	class TaskController extends Controller
	{
		public $defaultAction = "list";
		
		public static $allowed_users = [User::USER_TYPE, User::SEO_TYPE];
		
		// Папка вьюх
		protected $_viewsFolder	= "tasks";
		
		public function beforeAction()
		{
			$this->addJs("ng-task-app");
		}
		
		public function actionList()
		{
			$list = $_GET["list"];
			$type = $_GET["type"];
			
			if ($type == 0 && User::fromSession()->type == User::SEO_TYPE) {
				$this->renderRestricted();
			}
			
			// не надо панель рисовать
			$this->_custom_panel = true;
			
			if ($list) {
				$Tasks = Task::findAll([
					"condition" => "type=$type AND id_status=" . $list,
					"order"		=> "id DESC",
					"limit"		=> 50,
				]);
			} else {
				$Tasks = Task::findAll([
					"condition" => "type=$type AND id_status!=" . TaskStatuses::CLOSED,
					"order"		=> "id DESC",
				]);
			}
			
			$ang_init_data = angInit([
				"type"	=> $type,
				"Tasks" => $Tasks,
				"task_statuses" => TaskStatuses::$all,
			]);
			
			$this->render("list", [
				"Tasks" => $Tasks,
				"ang_init_data" => $ang_init_data
			]);
		}
		
		public function actionAjaxSave()
		{
			extract($_POST);
			
			// если в задаче пустой html - удаляем ее
			if (empty(trim($Task['html']))) {
				Task::deleteById($Task['id']);
			} else {
				echo Task::updateById($Task['id'], $Task) ? 1 : 0;
			}			
		}
		
		public function actionAjaxAdd()
		{
			returnJSON(Task::add()->id);
		}
		
	}
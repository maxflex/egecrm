<?php

	// Контроллер
	class TaskController extends Controller
	{
		public $defaultAction = "list";

		// Папка вьюх
		protected $_viewsFolder	= "tasks";
		
		public function beforeAction()
		{
			$this->addJs("ng-task-app, jspdf");
		}
		
		public function actionList()
		{
			// не надо панель рисовать
			$this->_custom_panel = true;
			
			$Tasks = Task::findAll([
				"condition" => "id_status!=" . TaskStatuses::CLOSED,
			]);
			
			$ang_init_data = angInit([
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
				Task::updateById($Task['id'], $Task);				
			}			
		}
		
		public function actionAjaxAdd()
		{
			returnJSON(Task::add()->id);
		}
		
	}
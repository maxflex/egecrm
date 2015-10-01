<?php

	// Контроллер
	class PrintTasksController extends Controller
	{
		public $defaultAction = "list";
		
		// Папка вьюх
		protected $_viewsFolder	= "print";
		
		public static $allowed_users = [User::USER_TYPE, Teacher::USER_TYPE];
		
		public function beforeAction()
		{
			$this->addJs("ng-print-tasks-app, enjoyhint");
			$this->addCss("enjoyhint");
		}
		
		public function actionList()
		{
			switch (User::fromSession()->type) {
				case User::USER_TYPE: {
					self::forUsers();
					break;		
				}
				case Teacher::USER_TYPE: {
					self::forTeachers();
					break;
				}
			}
		}
		
		public function actionAdd()
		{
			$this->setRights([Teacher::USER_TYPE]);
			$this->setTabTitle("Добавить задание на печать");
			
			$Teacher = Teacher::findById(User::fromSession()->id_entity);
			
			$Groups = $Teacher->getGroups();
			
			foreach ($Groups as &$Group) {
				$Group->FutureSchedule =  $Group->getFutureSchedule();
			}
			
			$ang_init_data = angInit([
				"Groups" => $Groups,
			]);		
			
			$this->render("for_teachers_add", [
				"ang_init_data" => $ang_init_data,
			]);
		}
		
		private function forUsers()
		{
			$this->_custom_panel = true;
			
			$PrintTasks = PrintTask::findAll();
			
			
			$ang_init_data = angInit([
				"PrintTasks" 	=> $PrintTasks,
			]);
			
			$this->render("for_users_list", [
				"ang_init_data" => $ang_init_data,
			]);
		}
		
		private function forTeachers()
		{
			$this->_custom_panel = true;
			
			$PrintTasks = PrintTask::findAll([
				"condition" => "id_teacher=" . User::fromSession()->id_entity,
				"order"		=> "date_created DESC"
			]);
			
			$ang_init_data = angInit([
				"PrintTasks" 	=> $PrintTasks,
				"for_teachers"	=> true,
			]);
			
			$this->render("for_teachers_list", [
				"ang_init_data" => $ang_init_data,
			]);
		}
		
		
		public function actionAjaxAddTask()
		{
			PrintTask::add($_POST);
		}
		
		public function actionAjaxChangeStatus()
		{
			extract($_POST);
			
			$Task = PrintTask::findById($id_task);
			
			$Task->id_status = $id_status;
			$Task->save("id_status");
		}
	}
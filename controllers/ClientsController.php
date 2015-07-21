<?php

	// Контроллер
	class ClientsController extends Controller
	{
		public $defaultAction = "list";
		
		// Папка вьюх
		protected $_viewsFolder	= "clients";
		
		public function actionList()
		{
			$this->setTabTitle("Клиенты с договорами");	
			
			$Students = Student::getWithContract();
			
			foreach ($Students as &$Student) {
				$Student->Contract = $Student->getLastContract();
			}
			
			$this->render("list", [
				"Students" => $Students
			]);
		}
		
	}